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
            $table->string('cancellation_reason')->nullable()->after('resumed_by');
            $table->text('cancellation_note')->nullable()->after('cancellation_reason');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_note');
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancelled_at');

            $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');
        });

        // Seed new permissions
        $cancel = Permission::updateOrCreate(
            ['slug' => 'ticket_cancel'],
            [
                'name' => 'Ticket Cancel',
                'module' => 'queue',
                'description' => 'Allow cancelling tickets in the queue',
                'is_global' => true,
            ]
        );

        // Assign to Roles (Super Admin, Company Admin, Staff)
        $rolesToSync = Role::whereIn('slug', ['super-admin', 'company-admin', 'staff', 'staff-operator'])->get();
        foreach ($rolesToSync as $role) {
            $role->permissions()->syncWithoutDetaching([$cancel->id]);
        }

        // Assign to all existing plans so SaaS companies can access it
        $plans = Plan::all();
        foreach ($plans as $plan) {
            $plan->permissions()->syncWithoutDetaching([$cancel->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn([
                'cancellation_reason',
                'cancellation_note',
                'cancelled_at',
                'cancelled_by',
            ]);
        });

        Permission::whereIn('slug', ['ticket_cancel'])->delete();
    }
};
