<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Modules\Appointments\Services\AppointmentService;
use Carbon\Carbon;

$svc = app(AppointmentService::class);
$date = Carbon::today();
$companyId = 5;
$serviceId = 3; // Service 3 has slots on Fridays

$slots = $svc->getSlotsForDate($serviceId, $date, $companyId);

echo "Slots for Company $companyId:" . PHP_EOL;
foreach ($slots as $slot) {
    echo "ID: {$slot['id']} | Local Range: {$slot['time_range']} | Capacity: {$slot['capacity']}" . PHP_EOL;
}
