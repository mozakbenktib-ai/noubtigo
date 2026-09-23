<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('visibility_scope')->default('all')->after('is_active');
        });

        Schema::create('plan_company_visibility', function (Blueprint $table) {
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->primary(['plan_id', 'company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_company_visibility');

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('visibility_scope');
        });
    }
};
