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
        // 0. Cleanup from potential failed runs
        Schema::dropIfExists('role_company');

        // 1. Create the pivot table for Roles <-> Companies
        Schema::create('role_company', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['role_id', 'company_id']);
        });

        // 2. Migrate existing data from roles table to pivot table
        $existingRoles = DB::table('roles')->whereNotNull('company_id')->get();
        
        foreach ($existingRoles as $role) {
            DB::table('role_company')->insert([
                'role_id' => $role->id,
                'company_id' => $role->company_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Update roles table: company_id is now handled by pivot
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('roles', function (Blueprint $table) {
                // Drop unique index that includes company_id
                // Based on investigation, the constraint name is 'roles_slug_company_id_unique'
                $table->dropUnique('roles_slug_company_id_unique');
                
                // Drop the column itself
                $table->dropColumn('company_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('slug');
            $table->unique(['slug', 'company_id'], 'roles_slug_company_id_unique');
        });

        // Inverse migration logic
        $pivots = DB::table('role_company')->get();
        foreach ($pivots as $pivot) {
            DB::table('roles')->where('id', $pivot->role_id)->update(['company_id' => $pivot->company_id]);
        }

        Schema::dropIfExists('role_company');
    }
};
