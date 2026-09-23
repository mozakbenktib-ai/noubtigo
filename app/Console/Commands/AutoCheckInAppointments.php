<?php

namespace App\Console\Commands;

use App\Modules\Appointments\Models\Appointment;
use App\Modules\Appointments\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoCheckInAppointments extends Command
{
    protected $signature = 'appointments:auto-checkin';
    protected $description = 'Automatically check in confirmed/pending appointments when their scheduled time arrives.';

    public function handle(AppointmentService $appointmentService)
    {
        $now = Carbon::now();

        // Find appointments whose time has arrived but haven't been checked in yet
        $appointments = Appointment::withoutGlobalScopes()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('appointment_date', '<=', $now)
            ->whereNull('checked_in_at')
            ->get();

        $count = 0;

        foreach ($appointments as $appointment) {
            try {
                $appointmentService->checkIn($appointment);
                $count++;
                $this->info("Auto checked-in appointment #{$appointment->id} ({$appointment->customer_name})");
            } catch (\Exception $e) {
                $this->warn("Failed to auto check-in appointment #{$appointment->id}: {$e->getMessage()}");
            }
        }

        $this->info("Auto check-in complete. {$count} appointment(s) processed.");

        return Command::SUCCESS;
    }
}
