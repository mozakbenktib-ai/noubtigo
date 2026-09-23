<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Activity logs are immutable: no updated_at, no soft deletes.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('action', ['created', 'updated', 'deleted', 'status_changed']);
            $table->string('model_type', 100); // e.g. 'Ticket', 'Customer'
            $table->unsignedBigInteger('model_id');
            $table->text('description')->nullable();
            $table->json('changes')->nullable(); // { before: {...}, after: {...} }

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Indexes for performance
            $table->index('company_id');
            $table->index(['model_type', 'model_id']);
            $table->index('created_at');
            $table->index(['company_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
