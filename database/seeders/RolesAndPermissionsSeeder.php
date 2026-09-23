<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Str;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            'manage_queue' => ['module' => 'Queue', 'desc' => 'Can access and manage the queue dashboard'],
            'manage_staff' => ['module' => 'Staff', 'desc' => 'Can manage staff members'],
            'manage_services' => ['module' => 'Services', 'desc' => 'Can manage services'],
            'manage_rooms' => ['module' => 'Rooms', 'desc' => 'Can manage physical rooms'],
            'view_reports' => ['module' => 'Reports', 'desc' => 'Can view analytical reports'],
            'manage_settings' => ['module' => 'Settings', 'desc' => 'Can manage company settings'],
            
            // Simple Queue Mode Permissions
            'simple_queue.access' => ['module' => 'Queue', 'desc' => 'Access to the Simple Queue interface'],
            'advanced_queue.access' => ['module' => 'Queue', 'desc' => 'Access to the Advanced Queue interface'],
            'queue.reorder' => ['module' => 'Queue', 'desc' => 'Can drag and drop tickets to reorder'],
            'queue.edit_ticket' => ['module' => 'Queue', 'desc' => 'Can edit ticket details'],
            'queue.manage_vip' => ['module' => 'Queue', 'desc' => 'Can mark tickets as VIP'],
            'queue.manage_priority' => ['module' => 'Queue', 'desc' => 'Can manually adjust priority scores'],
            'queue.transfer' => ['module' => 'Queue', 'desc' => 'Can transfer tickets between services/rooms'],
        ];

        foreach ($permissions as $name => $data) {
            Permission::updateOrCreate(
                ['slug' => Str::slug($name, '.')],
                [
                    'name' => str_replace(['_', '.'], ' ', ucfirst($name)),
                    'module' => $data['module'],
                    'description' => $data['desc'],
                    'is_global' => true
                ]
            );
        }

        // Create Roles and Assign Permissions
        
        // 1. Super Admin (Cross-company access / Platform Owner)
        $superAdmin = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin', 'is_global' => true]
        );

        // 2. Company Admin (Tenant Owner)
        $companyAdmin = Role::firstOrCreate(
            ['slug' => 'company-admin'],
            ['name' => 'Company Admin', 'is_global' => true]
        );
        $companyAdmin->permissions()->sync(Permission::all());

        // 3. Secretary
        $secretary = Role::firstOrCreate(
            ['slug' => 'secretary'],
            ['name' => 'Secretary', 'is_global' => true]
        );
        $secretaryPerms = Permission::whereIn('slug', [
            'manage_queue', 
            'view_reports', 
            'simple_queue.access',
            'advanced_queue.access'
        ])->pluck('id');
        $secretary->permissions()->sync($secretaryPerms);

        // 4. Staff / Operator
        $staff = Role::firstOrCreate(
            ['slug' => 'staff-operator'],
            ['name' => 'Staff / Operator', 'is_global' => true]
        );
        $staffPerms = Permission::whereIn('slug', [
            'manage_queue', 
            'simple_queue.access'
        ])->pluck('id');
        $staff->permissions()->sync($staffPerms);
    }
}
