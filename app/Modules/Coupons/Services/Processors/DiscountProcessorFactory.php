<?php

namespace App\Modules\Coupons\Services\Processors;

use App\Modules\Coupons\Contracts\DiscountProcessorInterface;
use App\Modules\Coupons\Models\Coupon;
use Exception;

class DiscountProcessorFactory
{
    /**
     * Resolve the appropriate discount processor for the given code.
     */
    public static function resolve(string $code): DiscountProcessorInterface
    {
        // Check if the code represents a Coupon
        $couponExists = Coupon::where('code', $code)->exists();
        if ($couponExists) {
            return app(CouponDiscountProcessor::class);
        }

        // Future extension hook:
        // if (GiftCard::where('code', $code)->exists()) {
        //     return app(GiftCardDiscountProcessor::class);
        // }

        throw new Exception('Invalid promo code.');
    }
}
