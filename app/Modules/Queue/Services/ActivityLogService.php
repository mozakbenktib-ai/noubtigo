<?php

namespace App\Modules\Queue\Services;

use App\Modules\Queue\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Sensitive fields that should never be stored in changes.
     */
    protected array $sensitiveFields = [
        'password', 'remember_token', 'api_token', 'secret',
        'whatsapp_token', 'credit_card', 'ssn',
    ];

    /**
     * Log an activity event.
     *
     * @param string $modelType  Short model name (e.g. 'Ticket', 'Customer')
     * @param int    $modelId    The model's primary key
     * @param string $action     One of: created, updated, deleted, status_changed
     * @param string|null $description  Human-readable description
     * @param array|null  $changes      [before => [...], after => [...]]
     */
    public function log(
        string $modelType,
        int $modelId,
        string $action,
        ?string $description = null,
        ?array $changes = null,
        ?string $modelLabel = null
    ): ActivityLog {
        // Filter sensitive data from changes
        if ($changes) {
            $changes = $this->filterSensitive($changes);
        }

        return ActivityLog::create([
            'user_id'    => Auth::id(),
            'action'     => $action,
            'model_type' => $modelType,
            'model_id'   => $modelId,
            'model_label' => $modelLabel,
            'description' => $description,
            'changes'    => $changes,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log a ticket creation event.
     */
    public function logTicketCreated(\App\Modules\Queue\Models\Ticket $ticket): ActivityLog
    {
        return $this->log(
            'Ticket',
            $ticket->id,
            'created',
            "Ticket {$ticket->ticket_number} created (status: {$ticket->status})",
            [
                'after' => [
                    'ticket_number' => $ticket->ticket_number,
                    'status'        => $ticket->status,
                    'service'       => $ticket->service?->name ?? '—',
                    'room'          => $ticket->room?->name ?? '—',
                    'customer'      => $ticket->customer?->full_name ?? __('ui.guest_customer'),
                    'source'        => $ticket->source,
                    'is_vip'        => $ticket->is_vip,
                ],
            ],
            $ticket->ticket_number
        );
    }

    /**
     * Log a ticket status change event.
     */
    public function logStatusChanged(
        \App\Modules\Queue\Models\Ticket $ticket,
        string $oldStatus,
        string $newStatus
    ): ActivityLog {
        return $this->log(
            'Ticket',
            $ticket->id,
            'status_changed',
            "Status changed: {$oldStatus} → {$newStatus}",
            [
                'before' => ['status' => $oldStatus],
                'after'  => ['status' => $newStatus],
            ],
            $ticket->ticket_number
        );
    }

    /**
     * Log a ticket room change event.
     */
    public function logRoomChanged(
        \App\Modules\Queue\Models\Ticket $ticket,
        ?int $oldRoomId,
        int $newRoomId
    ): ActivityLog {
        $oldRoom = \App\Modules\Rooms\Models\Room::find($oldRoomId);
        $newRoom = \App\Modules\Rooms\Models\Room::find($newRoomId);

        return $this->log(
            'Ticket',
            $ticket->id,
            'room_changed',
            "Room changed: " . ($oldRoom?->name ?? '—') . " → " . ($newRoom?->name ?? '—'),
            [
                'before' => ['room_id' => $oldRoomId, 'room_name' => $oldRoom?->name ?? '—'],
                'after'  => ['room_id' => $newRoomId, 'room_name' => $newRoom?->name ?? '—'],
            ],
            $ticket->ticket_number
        );
    }

    /**
     * Log a ticket reordered event.
     */
    public function logTicketReordered(
        \App\Modules\Queue\Models\Ticket $ticket,
        int $oldPosition,
        int $newPosition
    ): ActivityLog {
        return $this->log(
            'Ticket',
            $ticket->id,
            'reordered',
            "Position in queue changed: #{$oldPosition} → #{$newPosition}",
            [
                'before' => ['position' => $oldPosition],
                'after'  => ['position' => $newPosition],
            ],
            $ticket->ticket_number
        );
    }

    /**
     * Log a ticket cancellation event.
     */
    public function logTicketCancelled(\App\Modules\Queue\Models\Ticket $ticket, ?string $reason = null, ?string $note = null, ?string $oldStatus = null): ActivityLog
    {
        $localizedReason = $reason ? __('ui.cancel_reason_' . \Illuminate\Support\Str::snake($reason)) : null;
        if ($reason && $localizedReason === 'ui.cancel_reason_' . \Illuminate\Support\Str::snake($reason)) {
            $localizedReason = $reason;
        }

        $description = "Ticket {$ticket->ticket_number} cancelled";
        if ($localizedReason) {
            $description .= ". Reason: {$localizedReason}";
        }
        if ($note) {
            $description .= " ({$note})";
        }

        return $this->log(
            'Ticket',
            $ticket->id,
            'status_changed',
            $description,
            [
                'before' => ['status' => $oldStatus ?? 'waiting'],
                'after'  => [
                    'status' => 'cancelled',
                    'cancellation_reason' => $reason,
                    'cancellation_note' => $note
                ],
            ],
            $ticket->ticket_number
        );
    }

    /**
     * Log a user (staff) event.
     */
    public function logUserEvent(\App\Models\User $user, string $action, ?string $description = null, ?array $changes = null): ActivityLog
    {
        return $this->log('User', $user->id, $action, $description, $changes, $user->full_name);
    }

    /**
     * Log a customer event.
     */
    public function logCustomerEvent(\App\Modules\Customers\Models\Customer $customer, string $action, ?string $description = null, ?array $changes = null): ActivityLog
    {
        return $this->log('Customer', $customer->id, $action, $description, $changes, $customer->full_name);
    }

    /**
     * Log a ticket on hold event.
     */
    public function logTicketOnHold(\App\Modules\Queue\Models\Ticket $ticket, string $reason, ?string $note): ActivityLog
    {
        $localizedReason = __('ui.reason_' . \Illuminate\Support\Str::snake($reason));
        if ($localizedReason === 'ui.reason_' . \Illuminate\Support\Str::snake($reason)) {
            $localizedReason = $reason;
        }

        return $this->log(
            'Ticket',
            $ticket->id,
            'status_changed',
            "Ticket {$ticket->ticket_number} placed on hold. Reason: {$localizedReason}" . ($note ? " ({$note})" : ""),
            [
                'before' => ['status' => 'serving'],
                'after'  => [
                    'status' => 'on_hold',
                    'hold_reason' => $reason,
                    'hold_note' => $note
                ],
            ],
            $ticket->ticket_number
        );
    }

    /**
     * Log a ticket resumed event.
     */
    public function logTicketResumed(\App\Modules\Queue\Models\Ticket $ticket): ActivityLog
    {
        return $this->log(
            'Ticket',
            $ticket->id,
            'status_changed',
            "Ticket {$ticket->ticket_number} resumed",
            [
                'before' => ['status' => 'on_hold'],
                'after'  => ['status' => 'serving'],
            ],
            $ticket->ticket_number
        );
    }

    /**
     * Log a ticket hold cancelled event.
     */
    public function logTicketHoldCancelled(\App\Modules\Queue\Models\Ticket $ticket): ActivityLog
    {
        return $this->log(
            'Ticket',
            $ticket->id,
            'status_changed',
            "Ticket {$ticket->ticket_number} hold cancelled",
            [
                'before' => ['status' => 'on_hold'],
                'after'  => ['status' => 'hold_cancelled'],
            ],
            $ticket->ticket_number
        );
    }

    /**
     * Remove sensitive fields from changes arrays.
     */
    protected function filterSensitive(array $changes): array
    {
        foreach (['before', 'after'] as $key) {
            if (isset($changes[$key]) && is_array($changes[$key])) {
                foreach ($this->sensitiveFields as $field) {
                    unset($changes[$key][$field]);
                }
            }
        }

        return $changes;
    }
}
