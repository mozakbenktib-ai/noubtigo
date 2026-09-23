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
        Schema::table('display_devices', function (Blueprint $table) {
            $table->string('current_session_id')->nullable()->after('last_seen_at');
            $table->timestamp('session_started_at')->nullable()->after('current_session_id');
            $table->string('session_ip', 45)->nullable()->after('session_started_at');
            $table->text('session_user_agent')->nullable()->after('session_ip');
        });

        // Backfill display_limit in existing plans if not already present
        $plans = DB::table('plans')->get();
        foreach ($plans as $plan) {
            $limits = json_decode($plan->limits, true) ?: [];
            if (!isset($limits['display_limit'])) {
                // Infer appropriate default based on slug or room_limit
                if ($plan->slug === 'starter') {
                    $limits['display_limit'] = 1;
                } elseif ($plan->slug === 'professional') {
                    $limits['display_limit'] = 3;
                } elseif ($plan->slug === 'business') {
                    $limits['display_limit'] = 5;
                } elseif ($plan->slug === 'premium') {
                    $limits['display_limit'] = -1;
                } else {
                    $limits['display_limit'] = $limits['room_limit'] ?? 2;
                }

                DB::table('plans')->where('id', $plan->id)->update([
                    'limits' => json_encode($limits)
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('display_devices', function (Blueprint $table) {
            $table->dropColumn([
                'current_session_id',
                'session_started_at',
                'session_ip',
                'session_user_agent',
            ]);
        });
    }
};
