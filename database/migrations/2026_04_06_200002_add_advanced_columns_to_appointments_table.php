<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Only add columns that don't exist yet
            if (!Schema::hasColumn('appointments', 'slot_id')) {
                $table->foreignId('slot_id')->nullable()->after('room_id')
                      ->constrained('appointment_slots')->nullOnDelete();
            }
            if (!Schema::hasColumn('appointments', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('slot_id')
                      ->constrained('customers')->nullOnDelete();
            }
            if (!Schema::hasColumn('appointments', 'checked_in_at')) {
                $table->timestamp('checked_in_at')->nullable()->after('duration_minutes');
            }
            if (!Schema::hasColumn('appointments', 'grace_until')) {
                $table->timestamp('grace_until')->nullable()->after('checked_in_at');
            }
            if (!Schema::hasColumn('appointments', 'is_overbooked')) {
                $table->boolean('is_overbooked')->default(false)->after('grace_until');
            }
            if (!Schema::hasColumn('appointments', 'cancelled_reason')) {
                $table->string('cancelled_reason')->nullable()->after('notes');
            }
        });

        // Update status enum to include 'no_show'
        // SQLite doesn't support ALTER COLUMN, so we guard
        if (config('database.default') !== 'sqlite') {
            Schema::table('appointments', function (Blueprint $table) {
                $table->enum('status', ['pending','confirmed','cancelled','completed','no_show','checked_in'])
                      ->default('pending')->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['slot_id','customer_id','checked_in_at','grace_until','is_overbooked','cancelled_reason']);
        });
    }
};
