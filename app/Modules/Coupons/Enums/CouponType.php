<?php

namespace App\Modules\Coupons\Enums;

enum CouponType: string
{
    // Modern Promotion Types
    case PERCENTAGE_DISCOUNT = 'percentage_discount';
    case FIXED_DISCOUNT = 'fixed_discount';
    case FREE_PERIOD = 'free_period';
    case EXTENDED_SUBSCRIPTION = 'extended_subscription';
    case FREE_UNTIL_DATE = 'free_until_date';

    // Legacy Types (Preserved for 100% backward compatibility)
    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';
    case FREE_SUBSCRIPTION = 'free_subscription';
    case TRIAL_EXTENSION = 'trial_extension';
    case CUSTOM_PRICE = 'custom_price';
    case PLAN_UPGRADE = 'plan_upgrade';
    case LIFETIME = 'lifetime';

    /**
     * Get user-friendly label.
     */
    public function label(): string
    {
        return match($this) {
            self::PERCENTAGE_DISCOUNT, self::PERCENTAGE => 'Percentage Discount',
            self::FIXED_DISCOUNT, self::FIXED => 'Fixed Discount',
            self::FREE_PERIOD, self::FREE_SUBSCRIPTION => 'Free Period (Trial/Promo)',
            self::EXTENDED_SUBSCRIPTION, self::TRIAL_EXTENSION => 'Subscription Extension',
            self::FREE_UNTIL_DATE => 'Free Until Date',
            self::CUSTOM_PRICE => 'Custom Price',
            self::PLAN_UPGRADE => 'Plan Upgrade',
            self::LIFETIME => 'Lifetime Discount',
        };
    }

    /**
     * Check if this coupon gives a 100% free period.
     */
    public function isFreePeriod(): bool
    {
        return in_array($this, [self::FREE_PERIOD, self::FREE_SUBSCRIPTION, self::FREE_UNTIL_DATE]);
    }

    /**
     * Check if this coupon is an extension.
     */
    public function isExtension(): bool
    {
        return in_array($this, [self::EXTENDED_SUBSCRIPTION, self::TRIAL_EXTENSION]);
    }
}
