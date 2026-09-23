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
        // 1. Upgrade Permissions Table
        Schema::table('permissions', function (Blueprint $table) {
            $table->boolean('is_global')->default(false)->after('slug');
        });

        // 2. Upgrade Roles Table
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('is_global')->default(false)->after('slug');
        });

        // 3. Create Permission-Company Pivot Table
        Schema::create('permission_company', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->primary(['permission_id', 'company_id']);
        });

        // 4. Data Migration
        // Set is_global = true where company_id is null
        DB::table('permissions')->whereNull('company_id')->update(['is_global' => true]);
        DB::table('roles')->whereNull('company_id')->update(['is_global' => true]);

        // Migrate permissions that have a specific company_id to the pivot table
        $permissionsWithCompany = DB::table('permissions')->whereNotNull('company_id')->get();
        foreach ($permissionsWithCompany as $permission) {
            DB::table('permission_company')->insert([
                'permission_id' => $permission->id,
                'company_id' => $permission->company_id,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_company');

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('is_global');
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('is_global');
        });
    }
};
