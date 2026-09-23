<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove the 'ON UPDATE current_timestamp()' from appointment_date
        // Using raw SQL because the schema builder might not clear the ON UPDATE attribute properly
        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE appointments MODIFY appointment_date DATETIME NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-adding it if needed (not recommended, but for rollback consistency)
        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE appointments MODIFY appointment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        }
    }
};
