<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointment_slots', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->after('service_id')->constrained('rooms')->nullOnDelete();
            $table->index(['company_id', 'room_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::table('appointment_slots', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropIndex(['company_id', 'room_id', 'day_of_week']);
            $table->dropColumn('room_id');
        });
    }
};
