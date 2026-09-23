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
        Schema::create('display_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type', 50)->default('information'); // information, promotion, qr_tracking, announcement, etc.
            $table->string('image_path')->nullable();
            $table->string('link_url', 500)->nullable();
            $table->string('qr_url', 500)->nullable();
            $table->string('target_type', 20)->default('all'); // all, selected
            $table->unsignedSmallInteger('duration')->default(10); // in seconds
            $table->integer('sort_order')->default(0);
            $table->string('priority', 20)->default('normal'); // normal, high, emergency
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_active', 'target_type'], 'dc_company_active_target_idx');
            $table->index(['company_id', 'sort_order'], 'dc_company_sort_idx');
        });

        Schema::create('display_content_device', function (Blueprint $table) {
            $table->id();
            $table->foreignId('display_content_id')->constrained('display_contents')->onDelete('cascade');
            $table->foreignId('display_device_id')->constrained('display_devices')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['display_content_id', 'display_device_id'], 'dcd_content_device_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('display_content_device');
        Schema::dropIfExists('display_contents');
    }
};
