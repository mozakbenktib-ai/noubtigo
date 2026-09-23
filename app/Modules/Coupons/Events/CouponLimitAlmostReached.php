<?php

namespace App\Modules\Coupons\Events;

use App\Modules\Coupons\Models\Coupon;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CouponLimitAlmostReached
{
    use Dispatchable, SerializesModels;

    public function __construct(public Coupon $coupon) {}
}
