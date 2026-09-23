<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('day_of_week')->comment('0=Sunday, 6=Saturday');
            $table->time('start_time');
            $table->time('end_time');
            $table->tinyInteger('capacity')->default(1)->comment('Max confirmed bookings per slot');
            $table->tinyInteger('overbooking_limit')->default(0)->comment('Extra bookings allowed beyond capacity');
            $table->tinyInteger('grace_minutes')->default(10)->comment('Minutes to hold slot after no-show');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'service_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_slots');
    }
};
