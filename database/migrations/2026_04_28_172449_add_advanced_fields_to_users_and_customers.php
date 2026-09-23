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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('email');
            $table->string('avatar')->nullable()->after('last_name');
            $table->foreignId('assigned_room_id')->nullable()->after('company_id')->constrained('rooms')->nullOnDelete();
            $table->foreignId('assigned_service_id')->nullable()->after('assigned_room_id')->constrained('services')->nullOnDelete();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('identifier')->nullable()->after('last_name')->index();
            $table->boolean('is_vip')->default(false)->after('identifier')->index();
            $table->string('avatar')->nullable()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['assigned_room_id']);
            $table->dropForeign(['assigned_service_id']);
            $table->dropColumn(['is_active', 'avatar', 'assigned_room_id', 'assigned_service_id']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['identifier', 'is_vip', 'avatar']);
        });
    }
};
