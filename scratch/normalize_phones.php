<?php

use App\Modules\Customers\Models\Customer;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$customers = Customer::withoutGlobalScopes()->get();
echo "Normalizing " . $customers->count() . " customers...\n";

foreach ($customers as $customer) {
    $oldPhone = $customer->phone;
    $newPhone = Customer::normalizePhone($oldPhone);
    
    if ($oldPhone !== $newPhone) {
        // Update directly in DB to bypass potential issues with the model being loaded differently
        \Illuminate\Support\Facades\DB::table('customers')
            ->where('id', $customer->id)
            ->update(['phone' => $newPhone]);
        echo "Updated: {$oldPhone} -> {$newPhone}\n";
    }
}

echo "Done!\n";
