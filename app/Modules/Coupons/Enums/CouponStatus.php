<?php

namespace App\Modules\Coupons\Enums;

enum CouponStatus: string
{
    case ACTIVE = 'active';
    case DISABLED = 'disabled';
    case ARCHIVED = 'archived';

    /**
     * Get user-friendly label.
     */
    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::DISABLED => 'Disabled',
            self::ARCHIVED => 'Archived',
        };
    }
}
