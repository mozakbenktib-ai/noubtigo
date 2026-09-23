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
        // 1. Update Users Table
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_system_admin')->default(false)->after('password');
            $table->foreignId('company_id')->nullable()->change();
        });

        // 2. Update Roles Table
        Schema::table('roles', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            // Drop old unique constraint and add new one scoped by tenant
            $table->dropUnique(['slug']);
            $table->unique(['slug', 'company_id']);
        });

        // 3. Update Permissions Table
        Schema::table('permissions', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->dropUnique(['slug']);
            $table->unique(['slug', 'company_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropUnique(['slug', 'company_id']);
            $table->unique(['slug']);
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique(['slug', 'company_id']);
            $table->unique(['slug']);
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_system_admin');
            $table->foreignId('company_id')->nullable(false)->change();
        });
    }
};
