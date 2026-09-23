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
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('waiting', 'called', 'serving', 'done', 'cancelled', 'no_show', 'on_hold') NOT NULL DEFAULT 'waiting'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert any on_hold statuses back to serving before rolling back enum
        DB::table('tickets')->where('status', 'on_hold')->update(['status' => 'serving']);

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('waiting', 'called', 'serving', 'done', 'cancelled', 'no_show') NOT NULL DEFAULT 'waiting'");
        }
    }
};
