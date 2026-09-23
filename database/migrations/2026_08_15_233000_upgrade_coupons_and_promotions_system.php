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
        // 1. Upgrade coupons table
        Schema::table('coupons', function (Blueprint $table) {
            if (!Schema::hasColumn('coupons', 'promotion_type')) {
                $table->string('promotion_type')->nullable()->after('type'); // percentage_discount, fixed_discount, free_period, extended_subscription, free_until_date
            }
            if (!Schema::hasColumn('coupons', 'duration_type')) {
                $table->string('duration_type')->default('one_time')->after('promotion_type'); // one_time, number_of_days, number_of_months, number_of_billing_cycles, until_date, lifetime
            }
            if (!Schema::hasColumn('coupons', 'duration_value')) {
                $table->integer('duration_value')->nullable()->after('duration_type');
            }
            if (!Schema::hasColumn('coupons', 'duration_unit')) {
                $table->string('duration_unit')->nullable()->after('duration_value'); // days, months, cycles
            }
            if (!Schema::hasColumn('coupons', 'free_until_date')) {
                $table->timestamp('free_until_date')->nullable()->after('duration_unit');
            }
            if (!Schema::hasColumn('coupons', 'extension_value')) {
                $table->integer('extension_value')->nullable()->after('free_until_date');
            }
            if (!Schema::hasColumn('coupons', 'extension_unit')) {
                $table->string('extension_unit')->default('months')->after('extension_value'); // days, months
            }
        });

        // 2. Upgrade coupon_redemptions table to store promotion snapshot
        Schema::table('coupon_redemptions', function (Blueprint $table) {
            if (!Schema::hasColumn('coupon_redemptions', 'promotion_type')) {
                $table->string('promotion_type')->nullable()->after('subscription_id');
            }
            if (!Schema::hasColumn('coupon_redemptions', 'discount_type')) {
                $table->string('discount_type')->nullable()->after('promotion_type');
            }
            if (!Schema::hasColumn('coupon_redemptions', 'discount_value')) {
                $table->decimal('discount_value', 10, 2)->nullable()->after('discount_type');
            }
            if (!Schema::hasColumn('coupon_redemptions', 'duration_type')) {
                $table->string('duration_type')->default('one_time')->after('discount_value');
            }
            if (!Schema::hasColumn('coupon_redemptions', 'duration_value')) {
                $table->integer('duration_value')->nullable()->after('duration_type');
            }
            if (!Schema::hasColumn('coupon_redemptions', 'duration_unit')) {
                $table->string('duration_unit')->nullable()->after('duration_value');
            }
            if (!Schema::hasColumn('coupon_redemptions', 'billing_cycles_discounted')) {
                $table->integer('billing_cycles_discounted')->default(0)->after('duration_unit');
            }
            if (!Schema::hasColumn('coupon_redemptions', 'max_discounted_cycles')) {
                $table->integer('max_discounted_cycles')->nullable()->after('billing_cycles_discounted');
            }
            if (!Schema::hasColumn('coupon_redemptions', 'original_price')) {
                $table->decimal('original_price', 10, 2)->default(0)->after('max_discounted_cycles');
            }
            if (!Schema::hasColumn('coupon_redemptions', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('original_price');
            }
            if (!Schema::hasColumn('coupon_redemptions', 'final_amount')) {
                $table->decimal('final_amount', 10, 2)->default(0)->after('discount_amount');
            }
            if (!Schema::hasColumn('coupon_redemptions', 'free_until_date')) {
                $table->timestamp('free_until_date')->nullable()->after('final_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupon_redemptions', function (Blueprint $table) {
            $table->dropColumn([
                'promotion_type',
                'discount_type',
                'discount_value',
                'duration_type',
                'duration_value',
                'duration_unit',
                'billing_cycles_discounted',
                'max_discounted_cycles',
                'original_price',
                'discount_amount',
                'final_amount',
                'free_until_date',
            ]);
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn([
                'promotion_type',
                'duration_type',
                'duration_value',
                'duration_unit',
                'free_until_date',
                'extension_value',
                'extension_unit',
            ]);
        });
    }
};
