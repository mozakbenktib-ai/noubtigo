<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Renames ticket statuses: completed→done, skipped→no_show, drops delayed.
     * Final enum: waiting, called, serving, done, cancelled, no_show
     */
    public function up(): void
    {
        // Step 1: Temporarily expand enum to include both old and new values to allow migration
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('waiting', 'called', 'serving', 'completed', 'cancelled', 'delayed', 'skipped', 'done', 'no_show') NOT NULL DEFAULT 'waiting'");
        }

        // Step 2: Update existing rows to new status names
        DB::table('tickets')->where('status', 'completed')->update(['status' => 'done']);
        DB::table('tickets')->where('status', 'skipped')->update(['status' => 'no_show']);
        DB::table('tickets')->where('status', 'delayed')->update(['status' => 'waiting']);

        // Step 3: Finalize the enum column (removing old values)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('waiting', 'called', 'serving', 'done', 'cancelled', 'no_show') NOT NULL DEFAULT 'waiting'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore old values
        DB::table('tickets')->where('status', 'done')->update(['status' => 'completed']);
        DB::table('tickets')->where('status', 'no_show')->update(['status' => 'skipped']);

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('waiting', 'called', 'serving', 'completed', 'cancelled', 'delayed', 'skipped') NOT NULL DEFAULT 'waiting'");
        }
    }
};
