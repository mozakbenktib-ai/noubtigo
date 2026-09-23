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
        DB::table('plans')
            ->where('slug', 'pro')
            ->update(['slug' => 'professional']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('plans')
            ->where('slug', 'professional')
            ->update(['slug' => 'pro']);
    }
};
