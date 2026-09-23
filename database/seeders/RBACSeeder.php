<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Str;

class RBACSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function up(): void
    {
        $modules = [
            'users'         => ['view', 'create', 'edit', 'delete', 'manage-roles'],
            'appointments'       => ['view', 'create', 'edit', 'delete'],
            'appointment_slots'  => ['view', 'create', 'edit', 'delete'],
            'services'      => ['view', 'create', 'edit', 'delete'],
            'rooms'         => ['view', 'create', 'edit', 'delete'],
            'settings'      => ['view', 'edit'],
            'billing'       => ['view', 'create', 'edit', 'delete'],
            'displays'      => ['view', 'create', 'delete', 'authorize'],
            'display_content' => ['view', 'create', 'edit', 'delete'],
            'queue'         => ['view', 'create', 'edit', 'delete', 'call', 'advanced', 'history'],
            'activity_logs' => ['view'],
            'analytics'     => ['view'],
            'whatsapp'      => ['view', 'send'],
            'customers'     => ['view', 'create', 'edit', 'delete'],
            'dashboard'     => ['view'],
        ];

        $validSlugs = ['ticket_hold', 'ticket_resume'];
        $permissions = [];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $slug = "$module.$action";
                $validSlugs[] = $slug;
                
                $permissions[] = Permission::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => Str::title(str_replace('-', ' ', $action)) . ' ' . Str::title($module),
                        'module' => $module,
                        'is_global' => true,
                        'description' => "Allow user to $action $module",
                    ]
                );
            }
        }

        // Add custom hold/resume permissions
        $permissions[] = Permission::updateOrCreate(
            ['slug' => 'ticket_hold'],
            [
                'name' => 'Ticket Hold',
                'module' => 'queue',
                'is_global' => true,
                'description' => 'Allow placing tickets on hold',
            ]
        );

        $permissions[] = Permission::updateOrCreate(
            ['slug' => 'ticket_resume'],
            [
                'name' => 'Ticket Resume',
                'module' => 'queue',
                'is_global' => true,
                'description' => 'Allow resuming tickets on hold',
            ]
        );

        // --- CLEANUP: Delete any permissions that are NOT in our granular list ---
        // This removes umbrella permissions like 'managestaff', 'manageservices', etc.
        Permission::whereNotIn('slug', $validSlugs)
            ->where('is_global', true) // Only clean global ones to avoid touching custom ones if any
            ->delete();

        // Create Roles
        $superAdmin = Role::updateOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin', 'is_global' => true, 'description' => 'Full access to everything.']
        );

        $companyAdmin = Role::updateOrCreate(
            ['slug' => 'company-admin'],
            ['name' => 'Company Admin', 'is_global' => true, 'description' => 'Manage company users and settings.']
        );

        $staff = Role::updateOrCreate(
            ['slug' => 'staff'],
            ['name' => 'Staff', 'is_global' => true, 'description' => 'Handle daily operations (appointments, queues).']
        );

        $secretary = Role::updateOrCreate(
            ['slug' => 'secretary'],
            ['name' => 'Secretary', 'is_global' => true, 'description' => 'Manage appointments and customer check-ins.']
        );

        // Assign Permissions
        $allPermissionIds = Permission::pluck('id')->toArray();
        $superAdmin->permissions()->sync($allPermissionIds);
        $companyAdmin->permissions()->sync($allPermissionIds);

        // Staff: View and basic manage + hold/resume
        $staffPermissions = Permission::whereIn('slug', [
            'appointments.view', 'appointments.create', 
            'services.view', 
            'rooms.view',
            'customers.view', 'customers.create', 'customers.edit',
            'ticket_hold', 'ticket_resume'
        ])->pluck('id')->toArray();
        $staff->permissions()->sync($staffPermissions);

        // Secretary: Mostly appointments and customers
        $secretaryPermissions = Permission::where(function($q) {
            $q->where('module', 'appointments')
              ->orWhere('module', 'appointment_slots')
              ->orWhere('module', 'customers');
        })->pluck('id')->toArray();
        $secretary->permissions()->sync($secretaryPermissions);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->up();
    }
}
