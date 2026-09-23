<?php

namespace App\Modules\Coupons\Enums;

enum CouponCategory: string
{
    case GENERAL = 'general';
    case REFERRAL = 'referral';
    case AFFILIATE = 'affiliate';
    case EMAIL_CAMPAIGN = 'email_campaign';
    case SEASONAL = 'seasonal';
    case FLASH_SALE = 'flash_sale';
    case EARLY_BIRD = 'early_bird';
    case BIRTHDAY = 'birthday';

    /**
     * Get user-friendly label.
     */
    public function label(): string
    {
        return match($this) {
            self::GENERAL => 'General Promotion',
            self::REFERRAL => 'Referral Reward',
            self::AFFILIATE => 'Affiliate Discount',
            self::EMAIL_CAMPAIGN => 'Email Campaign',
            self::SEASONAL => 'Seasonal / Holiday Promo',
            self::FLASH_SALE => 'Flash Sale',
            self::EARLY_BIRD => 'Early Bird Promo',
            self::BIRTHDAY => 'Birthday Discount',
        };
    }
}
