<?php

namespace App\Modules\Coupons\Services\Processors;

use App\Modules\Coupons\Contracts\DiscountProcessorInterface;
use App\Modules\Coupons\Services\CouponService;
use App\Modules\Companies\Models\Company;
use App\Modules\Subscriptions\Models\Plan;

class CouponDiscountProcessor implements DiscountProcessorInterface
{
    protected $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    /**
     * Validate coupon settings.
     */
    public function validate(string $code, Company $company, Plan $plan, string $billingCycle, float $currentAmount): array
    {
        return $this->couponService->validateCoupon($code, $company, $plan, $billingCycle, $currentAmount);
    }

    /**
     * Compute new checkout totals.
     */
    public function calculate(string $code, float $amount, Company $company, Plan $plan, string $billingCycle): float
    {
        return $this->couponService->calculateDiscount($code, $amount, $company, $plan, $billingCycle);
    }

    /**
     * Apply coupon application details to records.
     */
    public function apply(string $code, Company $company, Plan $plan, string $billingCycle, float $amount, int $invoiceId): void
    {
        $this->couponService->applyCoupon($code, $company, $plan, $billingCycle, $amount, $invoiceId);
    }
}
