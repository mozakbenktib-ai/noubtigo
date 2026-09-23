<?php

namespace App\Modules\Appointments\Services;

use App\Modules\Appointments\Models\Appointment;
use App\Modules\Appointments\Models\AppointmentSlot;
use App\Modules\Customers\Models\Customer;
use App\Modules\Rooms\Models\Room;
use App\Services\TimezoneService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AppointmentService
{
    protected TimezoneService $tz;

    public function __construct()
    {
        $this->tz = app(TimezoneService::class);
    }

    /**
     * Resolve or create a customer profile for the given details.
     */
    public function resolveOrCreateCustomer(array $data, int $companyId): ?int
    {
        if (!empty($data['customer_id'])) {
            $customer = Customer::where('company_id', $companyId)->find($data['customer_id']);
            if ($customer) {
                return $customer->id;
            }
        }

        // Try lookup by phone
        if (!empty($data['customer_phone'])) {
            $normalizedPhone = Customer::normalizePhone($data['customer_phone']);
            $customer = Customer::where('company_id', $companyId)
                ->where('phone', $normalizedPhone)
                ->first();
            if ($customer) {
                return $customer->id;
            }
        }

        // Try lookup by email
        if (!empty($data['customer_email'])) {
            $customer = Customer::where('company_id', $companyId)
                ->where('email', $data['customer_email'])
                ->first();
            if ($customer) {
                return $customer->id;
            }
        }

        // Create new customer if name, phone, or email is provided
        if (!empty($data['customer_name']) || !empty($data['customer_phone']) || !empty($data['customer_email'])) {
            $name = trim($data['customer_name'] ?? 'Customer');
            $parts = explode(' ', $name, 2);
            $firstName = $parts[0] ?: 'Customer';
            $lastName = $parts[1] ?? '';

            $customer = Customer::create([
                'company_id' => $companyId,
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'phone'      => $data['customer_phone'] ?? null,
                'email'      => $data['customer_email'] ?? null,
            ]);

            return $customer->id;
        }

        return null;
    }

    // ─── Booking ────────────────────────────────────────────────────────────────

    /**
     * Book a new appointment with capacity, overbooking, and grace-period logic.
     */
    public function book(array $data, int $companyId): Appointment
    {
        $room = Room::where('company_id', $companyId)->find($data['room_id'] ?? null);
        if (!$room) {
            throw ValidationException::withMessages(['room_id' => ['Please select a valid room.']]);
        }

        $slot = null;
        $isOverbooked = false;

        $company = \App\Modules\Companies\Models\Company::find($companyId);
        $companyTz = $company->timezone ?? 'UTC';

        $appointmentDate = $this->tz->toUTC($data['appointment_date'], $companyTz);
        $this->assertRoomAvailability($room, $appointmentDate, $companyTz, $data['appointment_date'], null, (int) $data['service_id']);
        $appointmentDuration = $this->resolveAppointmentDuration($room, (int) $data['service_id'], $appointmentDate, $companyTz);

        $appointment = Appointment::create([
            'company_id' => $companyId,
            'user_id' => $data['user_id'] ?? null,
            'service_id' => $data['service_id'],
            'room_id' => $data['room_id'] ?? null,
            'slot_id' => $data['slot_id'] ?? null,
            'customer_id' => $data['customer_id'] ?? null,
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'] ?? null,
            'customer_phone' => $data['customer_phone'] ?? null,
            'appointment_date' => $appointmentDate,
            'duration_minutes' => $appointmentDuration,
            'status' => 'pending',
            'notes' => $data['notes'] ?? null,
            'is_overbooked' => $isOverbooked,
        ]);

        return $appointment->load(['service', 'room', 'staff', 'slot', 'customer']);
    }

    // ─── Reschedule ─────────────────────────────────────────────────────────────

    /**
     * Reschedule appointment to a new date/slot.
     */
    public function reschedule(Appointment $appointment, array $data): Appointment
    {
        $slot = null;
        $isOverbooked = false;
        $companyTz = $appointment->company->timezone ?? 'UTC';
        $room = Room::where('company_id', $appointment->company_id)->find($data['room_id'] ?? $appointment->room_id);
        if (!$room) {
            throw ValidationException::withMessages(['room_id' => ['Please select a valid room.']]);
        }

        $hasDateChanged = isset($data['appointment_date']) && 
            $this->tz->toUTC($data['appointment_date'], $companyTz)->format('Y-m-d H:i:s') !== $appointment->appointment_date->format('Y-m-d H:i:s');
        
        $hasSlotChanged = isset($data['slot_id']) && $data['slot_id'] != $appointment->slot_id;
        $hasServiceChanged = isset($data['service_id']) && $data['service_id'] != $appointment->service_id;

        $targetDate = $data['appointment_date'] ?? $appointment->appointment_date;
        $this->assertRoomAvailability($room, $this->tz->toUTC($targetDate, $companyTz), $companyTz, $targetDate, $appointment->id, (int) ($data['service_id'] ?? $appointment->service_id));
        $appointmentDuration = $this->resolveAppointmentDuration($room, (int) ($data['service_id'] ?? $appointment->service_id), $this->tz->toUTC($targetDate, $companyTz), $companyTz);
        
        $isCriticalChange = $hasDateChanged || $hasSlotChanged || $hasServiceChanged;

        $updateData = [
            'service_id'       => $data['service_id'] ?? $appointment->service_id,
            'customer_id'      => array_key_exists('customer_id', $data) ? $data['customer_id'] : $appointment->customer_id,
            'customer_name'    => $data['customer_name'] ?? $appointment->customer_name,
            'customer_email'   => array_key_exists('customer_email', $data) ? $data['customer_email'] : $appointment->customer_email,
            'customer_phone'   => array_key_exists('customer_phone', $data) ? $data['customer_phone'] : $appointment->customer_phone,
            'duration_minutes' => $appointmentDuration,

            'slot_id'          => array_key_exists('slot_id', $data) ? $data['slot_id'] : $appointment->slot_id,
            'appointment_date' => $hasDateChanged 
                ? $this->tz->toUTC($data['appointment_date'], $companyTz) 
                : $appointment->appointment_date,
            'room_id'          => array_key_exists('room_id', $data) ? $data['room_id'] : $appointment->room_id,
            'user_id'          => array_key_exists('user_id', $data) ? $data['user_id'] : $appointment->user_id,
            'notes'            => array_key_exists('notes', $data) ? $data['notes'] : $appointment->notes,
            'is_overbooked'    => $isOverbooked,
        ];


        // If it's a critical change, reset status and check-in info
        if ($isCriticalChange) {
            $updateData['status'] = $data['status'] ?? 'pending';
            $updateData['checked_in_at'] = null;
            $updateData['grace_until'] = null;
        } elseif (isset($data['status'])) {
            $updateData['status'] = $data['status'];
        }

        $appointment->update($updateData);

        return $appointment->fresh(['service', 'room', 'staff', 'slot', 'customer']);
    }

    // ─── Check-in ───────────────────────────────────────────────────────────────

    /**
     * Check in an appointment and optionally set a grace period.
     */
    public function checkIn(Appointment $appointment): Appointment
    {

        $graceMins = $appointment->slot?->grace_minutes ?? 10;

        // Ensure customer exists and is linked
        if (!$appointment->customer_id) {
            $customerId = $this->resolveOrCreateCustomer([
                'customer_id'    => $appointment->customer_id,
                'customer_name'  => $appointment->customer_name,
                'customer_phone' => $appointment->customer_phone,
                'customer_email' => $appointment->customer_email,
            ], $appointment->company_id);

            if ($customerId) {
                $appointment->customer_id = $customerId;
            }
        }

        $appointment->update([
            'customer_id'   => $appointment->customer_id,
            'status'        => 'checked_in',
            'checked_in_at' => now(),
            'grace_until'   => now()->addMinutes($graceMins),
        ]);

        // Auto-create queue ticket
        app(\App\Modules\Queue\Services\QueueService::class)->createTicket([
            'service_id'     => $appointment->service_id,
            'room_id'        => $appointment->room_id,
            'customer_id'    => $appointment->customer_id,
            'appointment_id' => $appointment->id,
            'source'         => 'appointment',
            'is_vip'         => false, // Could be determined by customer tier if applicable
        ], $appointment->company_id, $appointment->user_id);

        return $appointment->fresh(['service', 'slot', 'customer', 'room']);
    }

    // ─── Cancel ─────────────────────────────────────────────────────────────────

    public function cancel(Appointment $appointment, string $reason = ''): Appointment
    {
        $appointment->update([
            'status' => 'cancelled',
            'cancelled_reason' => $reason,
        ]);

        return $appointment;
    }

    // ─── Complete ───────────────────────────────────────────────────────────────

    public function complete(Appointment $appointment): Appointment
    {
        $appointment->update(['status' => 'completed']);
        return $appointment;
    }

    // ─── No-show ────────────────────────────────────────────────────────────────

    public function markNoShow(Appointment $appointment): Appointment
    {
        $appointment->update(['status' => 'no_show']);
        return $appointment;
    }

    // ─── Slot availability ──────────────────────────────────────────────────────

    /**
     * Return available slots for a given service + date.
     */
    public function getSlotsForDate(int $serviceId, Carbon $date, int $companyId, int $roomId): Collection
    {
        $generatedSlots = new Collection();
        $room = Room::where('company_id', $companyId)->find($roomId);
        if (!$room) return $generatedSlots;

        $company = \App\Modules\Companies\Models\Company::find($companyId);
        $companyTz = $company->timezone ?? 'UTC';
        $duration = (int) $room->appointment_duration_minutes;
        $interval = (int) $room->appointment_slot_interval_minutes;

        // New room schedule is preferred. Existing appointment-slots records are
        // supported as a room-scoped fallback so older configurations continue
        // to work after room scheduling was introduced.
        $ranges = $room->scheduleForDay($date->dayOfWeek);
        if (!$ranges) {
            $ranges = AppointmentSlot::where('company_id', $companyId)
                ->where('room_id', $room->id)
                ->where('service_id', $serviceId)
                ->where('day_of_week', $date->dayOfWeek)
                ->where('is_active', true)
                ->get()
                ->map(function (AppointmentSlot $slot) use ($companyTz, $duration) {
                    $start = $this->tz->toLocal(Carbon::parse($slot->start_time, 'UTC'), $companyTz)->format('H:i');
                    $end = $this->tz->toLocal(Carbon::parse($slot->end_time, 'UTC'), $companyTz)->format('H:i');
                    return [
                        'start' => $start,
                        'end' => $end,
                        'interval' => (int) ($slot->interval_minutes ?: $duration),
                        'duration' => (int) ($slot->interval_minutes ?: $duration),
                        'breaks' => [],
                    ];
                })->all();
        }

        if (!$ranges || (!$room->appointments_enabled && !AppointmentSlot::where('company_id', $companyId)->where('room_id', $room->id)->where('service_id', $serviceId)->where('day_of_week', $date->dayOfWeek)->where('is_active', true)->exists())) {
            return $generatedSlots;
        }

        // Determine current time in company timezone to filter past slots for today
        $nowLocal = Carbon::now($companyTz);
        $isToday = $date->copy()->setTimezone($companyTz)->isToday();

        foreach ($ranges as $range) {
            $start = Carbon::parse($date->format('Y-m-d') . ' ' . $range['start'], $companyTz);
            $end = Carbon::parse($date->format('Y-m-d') . ' ' . $range['end'], $companyTz);
            $rangeInterval = (int) ($range['interval'] ?? $interval);

            while ($start < $end) {
                $subEnd = clone $start;
                $subEnd->addMinutes((int) ($range['duration'] ?? $duration));

                if ($subEnd > $end) {
                    break;
                }

                // Skip slots that have already passed for today
                if ($isToday && $start->lt($nowLocal)) {
                    $start->addMinutes($rangeInterval);
                    continue;
                }

                $inBreak = collect($range['breaks'] ?? [])->contains(fn ($break) => $start->format('H:i') < $break['end'] && $subEnd->format('H:i') > $break['start']);
                $utcStart = $start->copy()->setTimezone('UTC');
                $utcEnd = $subEnd->copy()->setTimezone('UTC');
                $booked = Appointment::where('company_id', $companyId)->where('room_id', $room->id)
                    ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                    ->whereBetween('appointment_date', [$utcStart->copy()->subMinutes(480), $utcEnd])
                    ->get(['appointment_date', 'duration_minutes'])
                    ->contains(fn ($appointment) => $appointment->appointment_date < $utcEnd && $appointment->appointment_date->copy()->addMinutes($appointment->duration_minutes) > $utcStart);

                if (!$inBreak && !$booked) $generatedSlots->push([
                    'id' => null,
                    'start_time' => $start->format('H:i'), 'end_time' => $subEnd->format('H:i'),
                    'time_range' => $start->format('H:i') . ' – ' . $subEnd->format('H:i'),
                    'is_full' => false, 'is_overbooking' => false,
                ]);

                $start->addMinutes($rangeInterval);
            }
        }

        return $generatedSlots;
    }

    private function assertRoomAvailability(Room $room, Carbon $utcStart, string $timezone, string|Carbon $inputDate, ?int $excludeId, int $serviceId): void
    {
        $local = $utcStart->copy()->setTimezone($timezone);
        $nowLocal = Carbon::now($timezone);

        if ($local->lt($nowLocal)) {
            throw ValidationException::withMessages([
                'appointment_date' => ['This appointment slot is no longer available. Please select another time.']
            ]);
        }

        $ranges = $room->scheduleForDay($local->dayOfWeek);
        $usesLegacySlots = false;
        if (!$ranges) {
            $usesLegacySlots = true;
            $ranges = AppointmentSlot::where('company_id', $room->company_id)
                ->where('room_id', $room->id)
                ->where('service_id', $serviceId)
                ->where('day_of_week', $local->dayOfWeek)
                ->where('is_active', true)
                ->get()
                ->map(function (AppointmentSlot $slot) use ($timezone) {
                    return [
                        'start' => $this->tz->toLocal(Carbon::parse($slot->start_time, 'UTC'), $timezone)->format('H:i'),
                        'end' => $this->tz->toLocal(Carbon::parse($slot->end_time, 'UTC'), $timezone)->format('H:i'),
                        'duration' => (int) ($slot->interval_minutes ?: 30),
                        'breaks' => [],
                    ];
                })->all();
        }
        $defaultDuration = (int) $room->appointment_duration_minutes;
        $matchedRange = collect($ranges)->first(function ($range) use ($local, $defaultDuration) {
            $rangeDuration = (int) ($range['duration'] ?? $defaultDuration);
            $rangeEnd = $local->copy()->addMinutes($rangeDuration);
            if ($local->format('H:i') < $range['start'] || $rangeEnd->format('H:i') > $range['end']) return false;
            return !collect($range['breaks'] ?? [])->contains(fn ($break) => $local->format('H:i') < $break['end'] && $rangeEnd->format('H:i') > $break['start']);
        });
        if ((!$room->appointments_enabled && !$usesLegacySlots) || !$matchedRange) throw ValidationException::withMessages(['appointment_date' => ['This time is outside the selected room schedule.']]);
        $duration = (int) ($matchedRange['duration'] ?? $defaultDuration);

        $utcEnd = $utcStart->copy()->addMinutes($duration);
        $booked = Appointment::where('company_id', $room->company_id)->where('room_id', $room->id)
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->whereBetween('appointment_date', [$utcStart->copy()->subMinutes(480), $utcEnd])
            ->get(['id', 'appointment_date', 'duration_minutes'])
            ->reject(fn ($appointment) => $excludeId && $appointment->id === $excludeId)
            ->contains(fn ($appointment) => $appointment->appointment_date < $utcEnd && $appointment->appointment_date->copy()->addMinutes($appointment->duration_minutes) > $utcStart);
        if ($booked) throw ValidationException::withMessages(['appointment_date' => ['This time slot is already booked.']]);
    }

    private function resolveAppointmentDuration(Room $room, int $serviceId, Carbon $utcDate, string $timezone): int
    {
        if ($room->scheduleForDay($utcDate->copy()->setTimezone($timezone)->dayOfWeek)) {
            return (int) $room->appointment_duration_minutes;
        }

        return (int) (AppointmentSlot::where('company_id', $room->company_id)
            ->where('room_id', $room->id)
            ->where('service_id', $serviceId)
            ->where('day_of_week', $utcDate->copy()->setTimezone($timezone)->dayOfWeek)
            ->where('is_active', true)
            ->value('interval_minutes') ?: $room->appointment_duration_minutes);
    }

    // ─── Calendar events ────────────────────────────────────────────────────────

    /**
     * Return FullCalendar-compatible JSON events.
     * All dates are converted from UTC to the tenant's local timezone for display.
     */
    public function getCalendarEvents(int $companyId, Carbon $start, Carbon $end): Collection
    {
        return Appointment::with(['service', 'customer', 'staff', 'company'])
            ->where('company_id', $companyId)
            ->whereBetween('appointment_date', [$start, $end])
            // Keep terminal appointments visible in the calendar for history
            // and operational follow-up. Status rules remain unchanged.
            ->get()
            ->map(function (Appointment $appt) {
                $title = $appt->customer_name;
                $title .= $appt->service ? ' — ' . $appt->service->name : '';
                if ($appt->is_overbooked)
                    $title .= ' ⚡';
                if ($appt->isCheckedIn())
                    $title .= ' ✓';

                // Use TimezoneService to convert UTC → tenant local
                $companyTz = $this->tz->resolve();

                // Explicitly send the local time WITH its offset to the frontend
                $localDt = $this->tz->toLocal($appt->appointment_date, $companyTz);
                $endDt = (clone $localDt)->addMinutes($appt->duration_minutes);

                return [
                    'id' => $appt->id,
                    'title' => $title,
                    'start' => $localDt->toIso8601String(),
                    'end' => $endDt->toIso8601String(),
                    'backgroundColor' => $appt->calendarColor(),
                    'borderColor' => $appt->calendarColor(),
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'status' => $appt->status,
                        'customer_id' => $appt->customer_id,
                        'customer_name' => $appt->customer_name,
                        'customer_email' => $appt->customer_email,
                        'customer_phone' => $appt->customer_phone,
                        'service_id' => $appt->service_id,
                        'service_name' => $appt->service?->name,
                        'room_id' => $appt->room_id,
                        'room_name' => $appt->room?->name,
                        'user_id' => $appt->user_id,
                        'slot_id' => $appt->slot_id,
                        'duration_minutes' => $appt->duration_minutes,
                        'notes' => $appt->notes,
                        'is_overbooked' => $appt->is_overbooked,
                        'checked_in_at' => $appt->checked_in_at
                            ? $this->tz->toLocal($appt->checked_in_at, $companyTz)->toIso8601String()
                            : null,
                    ],
                ];
            });
    }
}
