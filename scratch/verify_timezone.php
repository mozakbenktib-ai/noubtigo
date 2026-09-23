<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Modules\Appointments\Models\AppointmentSlot;
use App\Services\TimezoneService;

$slot = AppointmentSlot::where('company_id', 5)->first();
if ($slot) {
    echo "DB Start: " . $slot->getRawOriginal('start_time') . PHP_EOL;
    echo "Display Range: " . $slot->time_range . PHP_EOL;
} else {
    echo "No slot found" . PHP_EOL;
}
