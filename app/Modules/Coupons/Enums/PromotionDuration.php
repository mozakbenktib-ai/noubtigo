<?php

namespace App\Modules\Coupons\Enums;

enum PromotionDuration: string
{
    case ONE_TIME = 'one_time';
    case NUMBER_OF_DAYS = 'number_of_days';
    case NUMBER_OF_MONTHS = 'number_of_months';
    case NUMBER_OF_BILLING_CYCLES = 'number_of_billing_cycles';
    case UNTIL_DATE = 'until_date';
    case LIFETIME = 'lifetime';

    /**
     * Get user-friendly label.
     */
    public function label(): string
    {
        return match($this) {
            self::ONE_TIME => 'One Time (1st Invoice)',
            self::NUMBER_OF_DAYS => 'Number of Days',
            self::NUMBER_OF_MONTHS => 'Number of Months',
            self::NUMBER_OF_BILLING_CYCLES => 'Number of Billing Cycles',
            self::UNTIL_DATE => 'Until Specific Date',
            self::LIFETIME => 'Lifetime (Recurring)',
        };
    }
}
