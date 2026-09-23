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
            $table->timestamp('last_user_message_at')->nullable()->after('email');
            $table->foreignId('last_company_id')->nullable()->after('last_user_message_at')->constrained('companies')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['last_company_id']);
            $table->dropColumn(['last_user_message_at', 'last_company_id']);
        });
    }
};
