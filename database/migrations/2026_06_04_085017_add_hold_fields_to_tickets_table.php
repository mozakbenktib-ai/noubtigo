<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permission;
use App\Models\Role;
use App\Modules\Subscriptions\Models\Plan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('hold_reason')->nullable()->after('finished_at');
            $table->text('hold_note')->nullable()->after('hold_reason');
            $table->timestamp('hold_at')->nullable()->after('hold_note');
            $table->unsignedBigInteger('hold_by')->nullable()->after('hold_at');
            $table->timestamp('resumed_at')->nullable()->after('hold_by');
            $table->unsignedBigInteger('resumed_by')->nullable()->after('resumed_at');

            $table->foreign('hold_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('resumed_by')->references('id')->on('users')->onDelete('set null');
        });

        // Seed new permissions
        $hold = Permission::updateOrCreate(
            ['slug' => 'ticket_hold'],
            [
                'name' => 'Ticket Hold',
                'module' => 'queue',
                'description' => 'Allow placing tickets on hold',
                'is_global' => true,
            ]
        );

        $resume = Permission::updateOrCreate(
            ['slug' => 'ticket_resume'],
            [
                'name' => 'Ticket Resume',
                'module' => 'queue',
                'description' => 'Allow resuming tickets on hold',
                'is_global' => true,
            ]
        );

        // Assign to Roles (Super Admin, Company Admin, Staff)
        $rolesToSync = Role::whereIn('slug', ['super-admin', 'company-admin', 'staff', 'staff-operator'])->get();
        foreach ($rolesToSync as $role) {
            $role->permissions()->syncWithoutDetaching([$hold->id, $resume->id]);
        }

        // Assign to all existing plans so SaaS companies can access it
        $plans = Plan::all();
        foreach ($plans as $plan) {
            $plan->permissions()->syncWithoutDetaching([$hold->id, $resume->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['hold_by']);
            $table->dropForeign(['resumed_by']);
            
            $table->dropColumn([
                'hold_reason',
                'hold_note',
                'hold_at',
                'hold_by',
                'resumed_at',
                'resumed_by',
            ]);
        });

        Permission::whereIn('slug', ['ticket_hold', 'ticket_resume'])->delete();
    }
};
