<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Str;

// Fix paths to point to project root
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$module = 'displays';
$actions = ['view', 'create', 'delete', 'authorize'];

$newPermissionIds = [];

foreach ($actions as $action) {
    $permission = Permission::updateOrCreate(
        ['slug' => "$module.$action"],
        [
            'name' => Str::title(str_replace('-', ' ', $action)) . ' ' . Str::title($module),
            'module' => $module,
            'description' => "Allow user to $action $module",
        ]
    );
    $newPermissionIds[] = $permission->id;
}

// Automatically give to Super Admin and Company Admin for convenience
$adminRoles = Role::whereIn('slug', ['super-admin', 'company-admin'])->get();
foreach ($adminRoles as $role) {
    $role->permissions()->syncWithoutDetaching($newPermissionIds);
}

echo "Displays permissions seeded successfully.\n";
