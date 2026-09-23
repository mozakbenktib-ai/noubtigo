<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->char('uuid', 36)->unique()->nullable()->after('id');
        });
        // Backfill existing rows with UUIDs
        DB::statement('UPDATE rooms SET uuid = UUID() WHERE uuid IS NULL');
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
