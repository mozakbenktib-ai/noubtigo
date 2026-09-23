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
        Schema::table('users', function (Blueprint $table) {
            $table->string('timezone')->nullable()->after('locale');
        });

        // Pre-populate existing users with their company's timezone
        if (DB::getDriverName() === 'sqlite') {
            DB::statement("
                UPDATE users
                SET timezone = (SELECT timezone FROM companies WHERE companies.id = users.company_id)
                WHERE EXISTS (
                    SELECT 1 FROM companies
                    WHERE companies.id = users.company_id
                      AND companies.timezone IS NOT NULL
                      AND companies.timezone != ''
                )
            ");
        } else {
            DB::statement("
                UPDATE users
                INNER JOIN companies ON users.company_id = companies.id
                SET users.timezone = companies.timezone
                WHERE companies.timezone IS NOT NULL
                  AND companies.timezone != ''
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('timezone');
        });
    }
};
