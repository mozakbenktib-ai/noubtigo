<?php

namespace App\Modules\Queue\Services;

use App\Modules\Queue\Models\Ticket;
use App\Modules\Queue\Services\ActivityLogService;
use App\Modules\Queue\Events\TicketCalled;
use App\Modules\Subscriptions\Services\SubscriptionService;
use Carbon\Carbon;

class QueueService
{
    protected ActivityLogService $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Create a new ticket in the queue.
     */
    public function createTicket(array $data, ?int $companyId = null, ?int $operatorId = null)
    {
        $companyId = $companyId ?? app(\App\Services\TenantManager::class)->getTenantId();
        $company = \App\Modules\Companies\Models\Company::find($companyId);

        // Limit Enforcement
        $subscriptionService = app(SubscriptionService::class);
        if (!$subscriptionService->canCreateTicket($company)) {
            throw new \Exception("Your monthly ticket limit has been reached. Please upgrade your plan.");
        }

        $ticket = new Ticket();
        $ticket->company_id = $companyId;
        $ticket->service_id = $data['service_id'];
        $ticket->room_id = $data['room_id'] ?? null;
        $ticket->user_id = $operatorId;
        $ticket->customer_id = $data['customer_id'] ?? null;
        $ticket->appointment_id = $data['appointment_id'] ?? null;
        $ticket->source = $data['source'] ?? 'walk-in';
        $ticket->is_vip = $data['is_vip'] ?? false;
        
        $ticket->priority_score = $this->calculatePriorityScore($ticket);
        
        $ticket->status = 'waiting';
        $ticket->save();
        $ticket->refresh(); // Load generated ticket_number and defaults

        // Audit log
        $this->activityLogService->logTicketCreated($ticket);

        event(new \App\Modules\Queue\Events\TicketCreated($ticket));

        return $ticket;
    }

    /**
     * Create a simple ticket (FIFO, no priority, no customer profile).
     * Used by Simple Queue mode.
     */
    public function createSimpleTicket(array $data, ?int $companyId = null, ?int $operatorId = null)
    {
        $companyId = $companyId ?? app(\App\Services\TenantManager::class)->getTenantId();
        $company = \App\Modules\Companies\Models\Company::find($companyId);

        // Limit Enforcement
        $subscriptionService = app(SubscriptionService::class);
        if (!$subscriptionService->canCreateTicket($company)) {
            throw new \Exception("Your monthly ticket limit has been reached. Please upgrade your plan.");
        }

        // Auto-assign the first active room for simple mode
        $roomId = $data['room_id'] ?? null;
        if (!$roomId) {
            $defaultRoom = \App\Modules\Rooms\Models\Room::where('company_id', $companyId)
                ->where('is_active', true)
                ->first();
            $roomId = $defaultRoom?->id;
        }

        $identifier = isset($data['customer_identifier'])
            ? trim((string) $data['customer_identifier'])
            : null;

        // In Simple Queue this is a ticket label only; it must not create or
        // modify a customer profile.
        $ticket = new Ticket();
        $ticket->company_id = $companyId;
        $ticket->service_id = $data['service_id'] ?? null;
        $ticket->room_id = $roomId;
        $ticket->user_id = $operatorId;
        $ticket->customer_id = null;
        $ticket->customer_identifier = $identifier ?: null;
        $ticket->source = 'walk-in';
        $ticket->is_vip = false;
        $ticket->priority_score = 0;
        $ticket->status = 'waiting';
        $ticket->save();
        $ticket->refresh();

        // Audit log
        $this->activityLogService->logTicketCreated($ticket);

        event(new \App\Modules\Queue\Events\TicketCreated($ticket));

        return $ticket;
    }

    /**
     * Calculate initial priority score.
     */
    protected function calculatePriorityScore(Ticket $ticket): int
    {
        $company = \App\Modules\Companies\Models\Company::find($ticket->company_id);
        $rules = $company ? $company->getQueueRules() : [
            'vip' => 1,
            'on_time_appointment' => 2,
            'in_grace_appointment' => 3,
            'walk_in' => 4,
        ];
        
        if ($ticket->is_vip) {
            $highestPriorityOrder = $rules['vip'] ?? 1;
        } elseif ($ticket->source === 'appointment' && $ticket->appointment_id) {
            $appointment = \App\Modules\Appointments\Models\Appointment::find($ticket->appointment_id);
            if ($appointment) {
                // If checked in late (after the appointment exact time) but before grace period ends
                if ($appointment->checked_in_at && $appointment->checked_in_at->gt($appointment->appointment_date)) {
                    $highestPriorityOrder = $rules['in_grace_appointment'] ?? 3;
                } else {
                    // On time or early
                    $highestPriorityOrder = $rules['on_time_appointment'] ?? 2;
                }
            } else {
                $highestPriorityOrder = $rules['walk_in'] ?? 4;
            }
        } else {
            $highestPriorityOrder = $rules['walk_in'] ?? 4;
        }

        // Map the order to a score so the database `ORDER BY priority_score DESC` still works perfectly.
        // Order 1 -> Score 99
        // Order 2 -> Score 98
        // ...
        // Order 5 -> Score 95
        $score = 100 - $highestPriorityOrder;
        
        return $score;
    }

    /**
     * Recalculate priority scores for all waiting tickets in a company.
     * This is useful when queue priority rules change in settings.
     */
    public function recalculateAllWaitingPriorities(int $companyId): void
    {
        $tickets = Ticket::waiting()->where('company_id', $companyId)->get();
        foreach ($tickets as $ticket) {
            $ticket->priority_score = $this->calculatePriorityScore($ticket);
            $ticket->save();
        }
    }

    /**
     * Reorder tickets in the queue.
     */
    public function reorderTickets(array $orderIds)
    {
        $tickets = Ticket::whereIn('id', $orderIds)->get()->keyBy('id');

        foreach ($orderIds as $index => $ticketId) {
            $ticket = $tickets->get($ticketId);
            $newPosition = $index + 1;

            if ($ticket && $ticket->position != $newPosition) {
                $oldPosition = $ticket->position;
                
                // Update via Eloquent to ensure any observers or future logic trigger
                $ticket->update(['position' => $newPosition]);

                // Log the change
                $this->activityLogService->logTicketReordered($ticket, $oldPosition, $newPosition);
            }
        }
    }

    /**
     * Call the next available ticket or a specific ticket.
     */
    public function callTicket(?int $ticketId = null, ?int $operatorId = null, ?int $roomId = null)
    {
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        $company = $tenantId ? \App\Modules\Companies\Models\Company::find($tenantId) : null;
        $isSimple = $company && $company->isSimpleQueue();

        // Auto-complete any currently serving or called ticket for this room
        $currentlyActive = Ticket::whereIn('status', ['called', 'serving']);
        if ($tenantId) {
            $currentlyActive->where('company_id', $tenantId);
        }
        if ($roomId) {
            $currentlyActive->where('room_id', $roomId);
        }
        $currentlyActive = $currentlyActive->get();

        foreach ($currentlyActive as $activeTicket) {
            $this->updateStatus($activeTicket, 'done');
        }

        // Find the next ticket
        if ($ticketId) {
            $ticket = Ticket::find($ticketId);
        } elseif ($isSimple) {
            // ── SIMPLE MODE: Pure FIFO by position ──
            $query = Ticket::waiting()
                ->orderBy('position', 'asc');

            if ($tenantId) {
                $query->where('company_id', $tenantId);
            }
            if ($roomId) {
                $query->where('room_id', $roomId);
            }

            $ticket = $query->first();
        } else {
            // ── ADVANCED MODE: Full appointment + priority logic ──
            // First, check-in ANY appointment whose time has arrived but is NOT checked in
            $appointmentQuery = \App\Modules\Appointments\Models\Appointment::withoutGlobalScopes()
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('appointment_date', '<=', Carbon::now());

            if ($tenantId) {
                $appointmentQuery->where('company_id', $tenantId);
            }
            if ($roomId) {
                $appointmentQuery->where(function($q) use ($roomId) {
                    $q->where('room_id', $roomId)->orWhereNull('room_id');
                });
            }
            
            $pendingAppointments = $appointmentQuery->get();
            if ($pendingAppointments->isNotEmpty()) {
                $appointmentService = app(\App\Modules\Appointments\Services\AppointmentService::class);
                foreach ($pendingAppointments as $pendingAppointment) {
                    $appointmentService->checkIn($pendingAppointment);
                }
            }

            // Now ALL arrived appointments are tickets in the queue.
            // We just grab the next ticket based purely on priority score!
            // We explicitly EXCLUDE appointments that haven't arrived yet (e.g. if they checked in early).
            $query = Ticket::waiting()
                ->where(function ($q) {
                    $q->where('source', '!=', 'appointment')
                      ->orWhereHas('appointment', function ($q2) {
                          $q2->where('appointment_date', '<=', Carbon::now());
                      })
                      ->orWhereNull('appointment_id');
                })
                ->orderByQueueOrder();

            if ($tenantId) {
                $query->where('company_id', $tenantId);
            }
            if ($roomId) {
                $query->where('room_id', $roomId);
            }

            $ticket = $query->first();
        }

        if ($ticket) {
            if ($ticket->source === 'appointment' && $ticket->appointment_id) {
                $linkedAppointment = \App\Modules\Appointments\Models\Appointment::find($ticket->appointment_id);
                if ($linkedAppointment && $linkedAppointment->appointment_date->isFuture()) {
                    throw new \Exception('This appointment cannot be called before its scheduled time.');
                }
            }

            $oldStatus = $ticket->status;

            $ticket->status = 'called';
            $ticket->called_at = Carbon::now();
            if ($operatorId) {
                $ticket->user_id = $operatorId;
            }
            $ticket->save();

            // Audit log
            $this->activityLogService->logStatusChanged($ticket, $oldStatus, 'called');

            // Fire TicketCalled event (triggers sound alert)
            event(new TicketCalled($ticket));

            // Sync linked appointment
            if ($ticket->appointment_id) {
                $appointment = \App\Modules\Appointments\Models\Appointment::find($ticket->appointment_id);
                if ($appointment) {
                    $appointment->update(['status' => 'checked_in']);
                }
            }
        }

        return $ticket;
    }

    /**
     * Update ticket status and sync linked appointment.
     */
    public function updateStatus(Ticket $ticket, string $status)
    {
        $oldStatus = $ticket->status;
        $ticket->status = $status;

        if ($status === 'serving') {
            $ticket->started_at = Carbon::now();
        } elseif (in_array($status, ['done', 'cancelled', 'no_show'])) {
            $ticket->finished_at = Carbon::now();
            
            // If it was never 'serving' (e.g. called -> done directly), set started_at now
            if (!$ticket->started_at && $status === 'done') {
                $ticket->started_at = $ticket->called_at ?? Carbon::now();
            }

            // Remove position so it doesn't break queue ordering
            $ticket->position = 0;
        }

        $ticket->save();

        // Audit log
        $this->activityLogService->logStatusChanged($ticket, $oldStatus, $status);

        // Broadcast update
        event(new \App\Modules\Queue\Events\TicketUpdated($ticket));

        // Fire TicketCalled if status is 'called'
        if ($status === 'called') {
            event(new TicketCalled($ticket));
        }

        // ── Sync status back to linked appointment ──
        if ($ticket->appointment_id) {
            $appointment = \App\Modules\Appointments\Models\Appointment::find($ticket->appointment_id);
            if ($appointment) {
                $appointmentStatus = match ($status) {
                    'serving'   => 'checked_in',
                    'done'      => 'completed',
                    'cancelled' => 'cancelled',
                    'no_show'   => 'no_show',
                    default     => null,
                };
                if ($appointmentStatus) {
                    $appointment->update(['status' => $appointmentStatus]);
                }
            }
        }
        
        return $ticket;
    }

    /**
     * Put a ticket on hold.
     */
    public function putOnHold(Ticket $ticket, string $reason, ?string $note, int $userId)
    {
        if ($ticket->status !== 'serving' && $ticket->status !== 'called') {
            throw new \Exception("Only tickets currently being served or called can be placed on hold.");
        }

        $ticket->status = 'on_hold';
        $ticket->hold_reason = $reason;
        $ticket->hold_note = $note;
        $ticket->hold_at = Carbon::now();
        $ticket->hold_by = $userId;
        
        // Remove position to avoid breaking waiting queue numbers/orders
        $ticket->position = 0;
        
        $ticket->save();

        // Audit log
        $this->activityLogService->logTicketOnHold($ticket, $reason, $note);

        // Broadcast update
        event(new \App\Modules\Queue\Events\TicketUpdated($ticket));

        return $ticket;
    }

    /**
     * Resume a ticket on hold.
     */
    public function resumeService(Ticket $ticket, int $userId)
    {
        if ($ticket->status !== 'on_hold') {
            throw new \Exception("Only tickets currently on hold can be resumed.");
        }

        // Auto-complete any currently serving or called ticket for this room
        $currentlyActive = Ticket::whereIn('status', ['called', 'serving'])
            ->where('company_id', $ticket->company_id);
        if ($ticket->room_id) {
            $currentlyActive->where('room_id', $ticket->room_id);
        }
        $currentlyActive = $currentlyActive->get();

        foreach ($currentlyActive as $activeTicket) {
            $this->updateStatus($activeTicket, 'done');
        }

        $ticket->status = 'serving';
        $ticket->resumed_at = Carbon::now();
        $ticket->resumed_by = $userId;
        $ticket->user_id = $userId; // Assign to resumer
        
        $ticket->save();

        // Audit log
        $this->activityLogService->logTicketResumed($ticket);

        // Broadcast update
        event(new \App\Modules\Queue\Events\TicketUpdated($ticket));

        return $ticket;
    }

    /**
     * Cancel a ticket that is on hold (customer did not return).
     */
    public function cancelHold(Ticket $ticket, int $userId)
    {
        if ($ticket->status !== 'on_hold') {
            throw new \Exception("Only tickets currently on hold can have their hold cancelled.");
        }

        $ticket->status = 'hold_cancelled';
        $ticket->save();

        // Audit log
        $this->activityLogService->logTicketHoldCancelled($ticket);

        // Broadcast update
        event(new \App\Modules\Queue\Events\TicketUpdated($ticket));

        return $ticket;
    }

    /**
     * Cancel a ticket (waiting, called, or on hold).
     */
    public function cancelTicket(Ticket $ticket, string $reason, ?string $note, int $userId)
    {
        if (!in_array($ticket->status, ['waiting', 'called', 'on_hold'])) {
            throw new \Exception("Only waiting, called, or on hold tickets can be cancelled.");
        }

        $oldStatus = $ticket->status;
        $ticket->status = 'cancelled';
        $ticket->cancellation_reason = $reason;
        $ticket->cancellation_note = $note;
        $ticket->cancelled_at = Carbon::now();
        $ticket->cancelled_by = $userId;
        $ticket->finished_at = Carbon::now();
        $ticket->position = 0; // Remove from active queue positions
        $ticket->save();

        // Audit log
        $this->activityLogService->logTicketCancelled($ticket, $reason, $note, $oldStatus);

        // Sync linked appointment
        if ($ticket->appointment_id) {
            $appointment = \App\Modules\Appointments\Models\Appointment::find($ticket->appointment_id);
            if ($appointment) {
                $appointment->update(['status' => 'cancelled']);
            }
        }

        // Broadcast update
        event(new \App\Modules\Queue\Events\TicketUpdated($ticket));

        return $ticket;
    }

    /**
     * Change ticket room and log the event.
     */
    public function changeRoom(Ticket $ticket, int $newRoomId)
    {
        $oldRoomId = $ticket->room_id;
        
        if ($oldRoomId === $newRoomId) {
            return $ticket;
        }

        $ticket->room_id = $newRoomId;
        $ticket->save();

        // Audit log
        $this->activityLogService->logRoomChanged($ticket, $oldRoomId, $newRoomId);

        // Broadcast update
        event(new \App\Modules\Queue\Events\TicketUpdated($ticket));

        return $ticket;
    }
}
