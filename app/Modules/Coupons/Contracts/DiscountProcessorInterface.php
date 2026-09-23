<?php

namespace App\Modules\Coupons\Contracts;

use App\Modules\Companies\Models\Company;
use App\Modules\Subscriptions\Models\Plan;

interface DiscountProcessorInterface
{
    /**
     * Validate the discount source against the checkout parameters.
     * Must return an array: ['valid' => bool, 'message' => string, 'details' => array]
     */
    public function validate(string $code, Company $company, Plan $plan, string $billingCycle, float $currentAmount): array;

    /**
     * Calculate the new total amount.
     */
    public function calculate(string $code, float $amount, Company $company, Plan $plan, string $billingCycle): float;

    /**
     * Apply the discount source to the created transaction/invoice.
     */
    public function apply(string $code, Company $company, Plan $plan, string $billingCycle, float $amount, int $invoiceId): void;
}
