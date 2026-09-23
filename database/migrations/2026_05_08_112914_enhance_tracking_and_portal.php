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
        // 1. Enhance Companies
        Schema::table('companies', function (Blueprint $table) {
            $table->string('secure_public_token')->nullable()->unique()->after('code');
        });

        // 2. Enhance Customers for Portal Access
        Schema::table('customers', function (Blueprint $table) {
            $table->string('password')->nullable()->after('email');
            $table->rememberToken()->after('password');
            $table->timestamp('email_verified_at')->nullable()->after('remember_token');
            $table->string('locale')->default('en')->after('email_verified_at');
            
            // If company_id is present, make it nullable if we want global customers
            // But for now, we keep it as "Origin Company"
            $table->foreignId('company_id')->nullable()->change();
        });

        // 3. Customer Favorites Pivot Table
        Schema::create('customer_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['customer_id', 'company_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_favorites');

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['password', 'remember_token', 'email_verified_at', 'locale']);
            $table->foreignId('company_id')->nullable(false)->change();
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('secure_public_token');
        });
    }
};
