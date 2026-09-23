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
        Schema::create('portal_users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->integer('points')->default(0);
            $table->string('locale', 5)->default('en');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('portal_user_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portal_user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->unique(['portal_user_id', 'company_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_user_favorites');
        Schema::dropIfExists('portal_users');
    }
};
