<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('appointment_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
            $table->string('source')->default('walk-in')->after('is_vip')->index(); // 'walk-in' or 'appointment'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
            $table->dropColumn(['appointment_id', 'source']);
        });
    }
};
