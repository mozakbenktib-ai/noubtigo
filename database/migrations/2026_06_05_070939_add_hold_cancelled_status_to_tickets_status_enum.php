<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('waiting', 'called', 'serving', 'done', 'cancelled', 'no_show', 'on_hold', 'hold_cancelled') NOT NULL DEFAULT 'waiting'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert any hold_cancelled statuses back to cancelled before rolling back enum
        DB::table('tickets')->where('status', 'hold_cancelled')->update(['status' => 'cancelled']);

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('waiting', 'called', 'serving', 'done', 'cancelled', 'no_show', 'on_hold') NOT NULL DEFAULT 'waiting'");
        }
    }
};
