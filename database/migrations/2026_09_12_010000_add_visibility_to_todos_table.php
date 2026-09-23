<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('todos', function (Blueprint $table) {
            $table->string('visibility', 10)->default('private')->after('priority');
            $table->index(['company_id', 'visibility', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::table('todos', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'visibility', 'due_date']);
            $table->dropColumn('visibility');
        });
    }
};
