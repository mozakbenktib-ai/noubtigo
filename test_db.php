<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$t = App\Modules\Queue\Models\Ticket::latest('id')->first();
echo "Ticket ID: " . $t->id . "\n";
echo "Ticket created_at (Raw): " . $t->getRawOriginal('created_at') . "\n";
if ($t->appointment_id) {
    $a = App\Modules\Appointments\Models\Appointment::find($t->appointment_id);
    echo "Appt ID: " . $a->id . "\n";
    echo "Appt date: " . $a->getRawOriginal('appointment_date') . "\n";
    echo "Checked in at: " . $a->getRawOriginal('checked_in_at') . "\n";
}
