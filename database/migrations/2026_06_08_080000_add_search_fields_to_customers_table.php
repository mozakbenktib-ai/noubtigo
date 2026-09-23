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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('cin')->nullable()->after('identifier')->index();
            $table->string('file_number')->nullable()->after('cin')->index();
            $table->string('plate_number')->nullable()->after('file_number')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['cin', 'file_number', 'plate_number']);
        });
    }
};
