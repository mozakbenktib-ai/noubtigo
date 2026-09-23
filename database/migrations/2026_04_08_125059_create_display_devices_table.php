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
        Schema::create('display_devices', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $blueprint->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $blueprint->string('name');
            $blueprint->string('uid')->unique(); // Random pairing ID (e.g., UUID or short hash)
            $blueprint->string('pairing_code', 6)->nullable(); // 6-digit numeric code
            $blueprint->string('device_token')->nullable()->unique(); // Permanent auth token
            $blueprint->timestamp('paired_at')->nullable();
            $blueprint->boolean('is_active')->default(true);
            $blueprint->timestamp('last_seen_at')->nullable();
            $blueprint->timestamps();
            $blueprint->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('display_devices');
    }
};
