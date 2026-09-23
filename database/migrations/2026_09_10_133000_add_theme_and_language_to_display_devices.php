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
        Schema::table('display_devices', function (Blueprint $table) {
            $table->string('theme', 20)->default('dark')->after('show_type');
            $table->string('language', 10)->nullable()->after('theme');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('display_devices', function (Blueprint $table) {
            $table->dropColumn(['theme', 'language']);
        });
    }
};
