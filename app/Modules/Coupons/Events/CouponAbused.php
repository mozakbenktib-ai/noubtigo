<?php

namespace App\Modules\Coupons\Events;

use App\Modules\Coupons\Models\Coupon;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CouponAbused
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Coupon $coupon,
        public string $reason,
        public ?int $companyId = null,
        public ?string $ipAddress = null
    ) {}
}
