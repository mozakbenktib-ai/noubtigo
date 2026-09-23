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
        // Drop legacy table if exists
        Schema::dropIfExists('queues');

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Operator
            
            $table->string('ticket_number')->index();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            
            $table->integer('priority_score')->default(0)->index();
            $table->boolean('is_vip')->default(false)->index();
            $table->integer('position')->default(0)->index(); // For manual reordering
            
            $table->enum('status', [
                'waiting', 
                'called', 
                'serving', 
                'completed', 
                'cancelled', 
                'delayed', 
                'skipped'
            ])->default('waiting')->index();
            
            $table->timestamp('waited_since')->useCurrent();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // Compound index for efficient dashboard queries
            $table->index(['company_id', 'status', 'priority_score', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
