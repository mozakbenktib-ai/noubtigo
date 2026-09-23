<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->boolean('appointments_enabled')->default(false)->after('is_active');
            $table->unsignedSmallInteger('appointment_duration_minutes')->default(30)->after('appointments_enabled');
            $table->unsignedSmallInteger('appointment_slot_interval_minutes')->default(30)->after('appointment_duration_minutes');
            $table->json('appointment_schedule')->nullable()->after('appointment_slot_interval_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn([
                'appointments_enabled',
                'appointment_duration_minutes',
                'appointment_slot_interval_minutes',
                'appointment_schedule',
            ]);
        });
    }
};
