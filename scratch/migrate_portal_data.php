<?php

use App\Modules\Customers\Models\Customer;
use App\Modules\Customers\Models\PortalUser;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting migration of portal data...\n";

// 1. Find all customers with company_id IS NULL (old portal users)
$oldPortalUsers = DB::table('customers')->whereNull('company_id')->get();

foreach ($oldPortalUsers as $oldUser) {
    echo "Migrating: {$oldUser->email}\n";
    
    // Create in portal_users
    $newPortalUserId = DB::table('portal_users')->insertGetId([
        'first_name' => $oldUser->first_name,
        'last_name' => $oldUser->last_name,
        'email' => $oldUser->email,
        'phone' => $oldUser->phone,
        'password' => $oldUser->password,
        'avatar' => $oldUser->avatar,
        'points' => $oldUser->points,
        'locale' => $oldUser->locale ?? 'en',
        'created_at' => $oldUser->created_at,
        'updated_at' => $oldUser->updated_at,
    ]);

    // 2. Move favorites
    $favorites = DB::table('customer_favorites')->where('customer_id', $oldUser->id)->get();
    foreach ($favorites as $fav) {
        DB::table('portal_user_favorites')->insert([
            'portal_user_id' => $newPortalUserId,
            'company_id' => $fav->company_id,
            'created_at' => $fav->created_at,
            'updated_at' => $fav->updated_at,
        ]);
    }

    // 3. Delete old customer record
    DB::table('customer_favorites')->where('customer_id', $oldUser->id)->delete();
    DB::table('customers')->where('id', $oldUser->id)->delete();
}

echo "Migration complete!\n";
