<?php

namespace App\Modules\Coupons\Services;

use App\Modules\Coupons\Models\Coupon;
use App\Modules\Coupons\Models\CouponRedemption;
use App\Modules\Coupons\Models\CouponUsage;
use App\Modules\Coupons\Models\CouponHistory;
use App\Modules\Coupons\Models\CouponLog;
use App\Modules\Companies\Models\Company;
use App\Modules\Subscriptions\Models\Plan;
use App\Modules\Payments\Models\Invoice;
use App\Modules\Payments\Models\Payment;
use App\Modules\Payments\Models\Subscription;
use App\Modules\Coupons\Enums\CouponType;
use App\Modules\Coupons\Enums\CouponStatus;
use App\Modules\Coupons\Enums\PromotionDuration;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CouponService
{
    /**
     * Validate a coupon against checkout parameters.
     */
    public function validateCoupon(string $code, Company $company, Plan $plan, string $billingCycle, float $currentAmount, ?int $userId = null): array
    {
        $coupon = Coupon::where('code', $code)->first();

        // 1. Check if coupon exists
        if (!$coupon) {
            $this->logHistory(null, $company->id, $userId, 'validate', 'failed', 'Coupon does not exist.');
            return ['valid' => false, 'message' => 'Coupon code does not exist.'];
        }

        // 2. Check if coupon is active
        if (!$coupon->isActive()) {
            $this->logHistory($coupon->id, $company->id, $userId, 'validate', 'failed', 'Coupon is disabled or archived.');
            return ['valid' => false, 'message' => 'Coupon is not active.'];
        }

        // 3. Timezone and Expiration date validation
        $now = Carbon::now($coupon->timezone ?: 'UTC');
        if ($coupon->starts_at && $now->lessThan($coupon->starts_at->timezone($coupon->timezone ?: 'UTC'))) {
            $this->logHistory($coupon->id, $company->id, $userId, 'validate', 'failed', 'Coupon validation period has not started.');
            return ['valid' => false, 'message' => 'Coupon is not yet active.'];
        }

        if ($coupon->expires_at && $now->greaterThan($coupon->expires_at->timezone($coupon->timezone ?: 'UTC'))) {
            $this->logHistory($coupon->id, $company->id, $userId, 'validate', 'failed', 'Coupon expired.');
            return ['valid' => false, 'message' => 'Coupon has expired.'];
        }

        // 4. Maximum uses limits validation
        if (!$coupon->is_unlimited) {
            if ($coupon->max_total_uses && $coupon->current_uses >= $coupon->max_total_uses) {
                $this->logHistory($coupon->id, $company->id, $userId, 'validate', 'failed', 'Coupon total usage limit reached.');
                return ['valid' => false, 'message' => 'Coupon usage limit has been reached.'];
            }
        }

        // 5. Tenant-specific usage limit
        if ($coupon->max_uses_per_tenant) {
            $tenantUses = CouponRedemption::where('coupon_id', $coupon->id)
                ->where('company_id', $company->id)
                ->count();
            if ($tenantUses >= $coupon->max_uses_per_tenant) {
                $this->logHistory($coupon->id, $company->id, $userId, 'validate', 'failed', 'Tenant usage limit reached.');
                return ['valid' => false, 'message' => 'Coupon usage limit reached for your company.'];
            }
        }

        // 6. User-specific usage limit
        if ($coupon->max_uses_per_user && $userId) {
            $userUses = CouponUsage::where('coupon_id', $coupon->id)
                ->where('user_id', $userId)
                ->count();
            if ($userUses >= $coupon->max_uses_per_user) {
                $this->logHistory($coupon->id, $company->id, $userId, 'validate', 'failed', 'User usage limit reached.');
                return ['valid' => false, 'message' => 'You have already used this coupon code the maximum number of times.'];
            }
        }

        // 7. Plan restrictions
        if ($coupon->plans()->exists()) {
            if (!$coupon->plans->contains($plan->id)) {
                $this->logHistory($coupon->id, $company->id, $userId, 'validate', 'failed', 'Coupon not valid for this plan.');
                return ['valid' => false, 'message' => 'Coupon is not valid for the selected plan.'];
            }
        }

        // 8. Billing cycle restrictions
        if ($coupon->billing_cycle && $coupon->billing_cycle !== 'both') {
            if ($coupon->billing_cycle !== $billingCycle) {
                $this->logHistory($coupon->id, $company->id, $userId, 'validate', 'failed', "Coupon only valid for {$coupon->billing_cycle} plans.");
                return ['valid' => false, 'message' => "Coupon is only valid for " . ucfirst($coupon->billing_cycle) . " plans."];
            }
        }

        // 9. Minimum purchase amount validation
        if ($coupon->min_purchase_amount && $currentAmount < $coupon->min_purchase_amount) {
            $this->logHistory($coupon->id, $company->id, $userId, 'validate', 'failed', 'Minimum purchase amount not met.');
            return ['valid' => false, 'message' => "Minimum purchase of {$coupon->min_purchase_amount} is required to use this coupon."];
        }

        // 10. Country & Currency constraints
        if ($coupon->currency && strtolower($coupon->currency) !== strtolower($plan->currency ?? 'USD')) {
            $this->logHistory($coupon->id, $company->id, $userId, 'validate', 'failed', 'Currency mismatch.');
            return ['valid' => false, 'message' => "Coupon is only valid for currency " . strtoupper($coupon->currency) . "."];
        }

        if ($coupon->country) {
            $companyCountry = $company->settings['country'] ?? $company->address ?? '';
            if (empty($companyCountry) || !str_contains(strtolower($companyCountry), strtolower($coupon->country))) {
                $this->logHistory($coupon->id, $company->id, $userId, 'validate', 'failed', 'Country restriction.');
                return ['valid' => false, 'message' => "Coupon is only valid for customers in " . strtoupper($coupon->country) . "."];
            }
        }

        // 11. Customer Type validation (new vs existing)
        $hasPaidHistory = Payment::where('company_id', $company->id)
            ->where('status', 'paid')
            ->exists() || Subscription::where('company_id', $company->id)->where('status', 'active')->exists();

        if ($coupon->customer_type === 'new_customers' && $hasPaidHistory) {
            $this->logHistory($coupon->id, $company->id, $userId, 'validate', 'failed', 'Coupon only valid for new customers.');
            return ['valid' => false, 'message' => 'Coupon is only valid for new customers.'];
        }
        if ($coupon->customer_type === 'existing_customers' && !$hasPaidHistory) {
            $this->logHistory($coupon->id, $company->id, $userId, 'validate', 'failed', 'Coupon only valid for existing customers.');
            return ['valid' => false, 'message' => 'Coupon is only valid for existing customers.'];
        }

        // 12. Payment Type validation (first payment vs renewal)
        if ($coupon->payment_type === 'first_payment' && $hasPaidHistory) {
            $this->logHistory($coupon->id, $company->id, $userId, 'validate', 'failed', 'Coupon only valid for the first payment.');
            return ['valid' => false, 'message' => 'Coupon is only valid for the first payment.'];
        }
        if ($coupon->payment_type === 'renewals' && !$hasPaidHistory) {
            $this->logHistory($coupon->id, $company->id, $userId, 'validate', 'failed', 'Coupon only valid for renewals.');
            return ['valid' => false, 'message' => 'Coupon is only valid for plan renewals.'];
        }

        // 13. Tenant whitelist/blacklist checks
        if ($coupon->tenant_restriction_type === 'whitelist') {
            if (!$coupon->companies->contains($company->id)) {
                $this->logHistory($coupon->id, $company->id, $userId, 'validate', 'failed', 'Tenant not in whitelist.');
                return ['valid' => false, 'message' => 'Your company is not authorized to use this coupon.'];
            }
        } elseif ($coupon->tenant_restriction_type === 'blacklist') {
            if ($coupon->companies->contains($company->id)) {
                $this->logHistory($coupon->id, $company->id, $userId, 'validate', 'failed', 'Tenant is blacklisted.');
                return ['valid' => false, 'message' => 'Your company is blacklisted from using this coupon.'];
            }
        }

        // 14. Whitelisted emails, domains and companies check
        if ($coupon->allowed_emails) {
            $userEmail = auth()->user()?->email ?? $company->email;
            if (!$userEmail || !in_array($userEmail, $coupon->allowed_emails)) {
                $this->logHistory($coupon->id, $company->id, $userId, 'validate', 'failed', 'User email not whitelisted.');
                return ['valid' => false, 'message' => 'Your email address is not whitelisted for this coupon.'];
            }
        }

        if ($coupon->allowed_domains) {
            $userEmail = auth()->user()?->email ?? $company->email;
            $domain = $userEmail ? substr(strrchr($userEmail, "@"), 1) : null;
            if (!$domain || !in_array($domain, $coupon->allowed_domains)) {
                $this->logHistory($coupon->id, $company->id, $userId, 'validate', 'failed', 'Domain not whitelisted.');
                return ['valid' => false, 'message' => 'Your email domain is not whitelisted for this coupon.'];
            }
        }

        if ($coupon->allowed_companies) {
            if (!in_array($company->name, $coupon->allowed_companies)) {
                $this->logHistory($coupon->id, $company->id, $userId, 'validate', 'failed', 'Company name not whitelisted.');
                return ['valid' => false, 'message' => 'Your company is not authorized to use this coupon.'];
            }
        }

        // Successful validation
        $this->logHistory($coupon->id, $company->id, $userId, 'validate', 'success');

        $preview = $this->generatePreviewDetails($coupon, $currentAmount, $plan, $billingCycle, $company);

        return [
            'valid' => true,
            'coupon' => $coupon,
            'preview' => $preview,
        ];
    }

    /**
     * Calculate discount amount.
     */
    public function calculateDiscount(string $code, float $amount, Company $company, Plan $plan, string $billingCycle): float
    {
        $coupon = Coupon::where('code', $code)->first();
        if (!$coupon) return 0.00;

        $promoType = $coupon->getPromotionTypeString();

        switch ($promoType) {
            case 'percentage_discount':
            case 'percentage':
                $discount = $amount * ($coupon->value / 100.0);
                if ($coupon->max_discount_amount) {
                    $discount = min($discount, $coupon->max_discount_amount);
                }
                return round(min($discount, $amount), 2);

            case 'fixed_discount':
            case 'fixed':
                return round(min($coupon->value, $amount), 2);

            case 'free_period':
            case 'free_subscription':
            case 'free_until_date':
                return round($amount, 2); // 100% off during the promotion period

            case 'extended_subscription':
            case 'trial_extension':
                return 0.00; // Extends expiration date rather than creating discounted invoices

            case 'custom_price':
                $discount = $amount - $coupon->value;
                return round(max(0, min($discount, $amount)), 2);

            case 'plan_upgrade':
                return 0.00;

            case 'lifetime':
                $discount = $amount * ($coupon->value / 100.0);
                return round(min($discount, $amount), 2);

            default:
                return 0.00;
        }
    }

    /**
     * Generate promotion preview details for checkout / UI.
     */
    public function generatePreviewDetails(Coupon $coupon, float $originalAmount, Plan $plan, string $billingCycle, Company $company): array
    {
        $promoType = $coupon->getPromotionTypeString();
        $discountAmount = $this->calculateDiscount($coupon->code, $originalAmount, $company, $plan, $billingCycle);
        $finalAmount = max(0.00, $originalAmount - $discountAmount);

        $preview = [
            'code' => $coupon->code,
            'name' => $coupon->name,
            'promotion_type' => $promoType,
            'original_amount' => $originalAmount,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'duration_description' => $coupon->getDurationDescription(),
            'promotion_label' => $coupon->getPromotionLabel(),
            'is_extension' => in_array($promoType, ['extended_subscription', 'trial_extension']),
            'is_free_period' => in_array($promoType, ['free_period', 'free_subscription', 'free_until_date']),
            'summary_lines' => [],
        ];

        // Construct human-readable summary lines
        if ($preview['is_extension']) {
            $extVal = $coupon->extension_value ?: $coupon->duration_in_months ?: (int)$coupon->value ?: 1;
            $extUnit = $coupon->extension_unit ?: 'months';

            $currentSub = $company->subscriptions()->where('status', 'active')->latest('ends_at')->first();
            $baseDate = ($currentSub && $currentSub->ends_at && $currentSub->ends_at->isFuture()) 
                ? $currentSub->ends_at->copy() 
                : Carbon::now();

            $newDate = $extUnit === 'days' ? $baseDate->copy()->addDays($extVal) : $baseDate->copy()->addMonths($extVal);

            $preview['summary_lines'][] = "Current Expiration: " . $baseDate->format('M d, Y');
            $preview['summary_lines'][] = "Promotion: +{$extVal} {$extUnit}";
            $preview['summary_lines'][] = "New Expiration: " . $newDate->format('M d, Y');
            $preview['extension_details'] = [
                'current_ends_at' => $baseDate->format('Y-m-d'),
                'new_ends_at' => $newDate->format('Y-m-d'),
                'extension_amount' => $extVal,
                'extension_unit' => $extUnit,
            ];
        } elseif ($preview['is_free_period']) {
            if ($promoType === 'free_until_date' && $coupon->free_until_date) {
                $preview['summary_lines'][] = "100% Discount — Total: 0.00 MAD";
                $preview['summary_lines'][] = "Free access until " . $coupon->free_until_date->format('M d, Y');
                $preview['summary_lines'][] = "Normal billing starts on " . $coupon->free_until_date->copy()->addDay()->format('M d, Y');
            } else {
                $durVal = $coupon->duration_value ?: $coupon->duration_in_months ?: (int)$coupon->value ?: 1;
                $durUnit = $coupon->duration_unit ?: 'months';
                $nextBillingDate = $durUnit === 'days' ? Carbon::now()->addDays($durVal) : Carbon::now()->addMonths($durVal);

                $preview['summary_lines'][] = "100% Discount — Total: 0.00 MAD";
                $preview['summary_lines'][] = "Free for {$durVal} {$durUnit}";
                $preview['summary_lines'][] = "Normal billing starts on: " . $nextBillingDate->format('M d, Y');
            }
        } else {
            // Percentage or Fixed Discount with duration
            $durType = $coupon->duration_type ?: 'one_time';
            $durVal = $coupon->duration_value ?: $coupon->duration_in_months ?: 1;

            if ($durType === 'one_time') {
                $preview['summary_lines'][] = "Normal price: " . number_format($originalAmount, 2) . " MAD";
                $preview['summary_lines'][] = "Discount: -" . number_format($discountAmount, 2) . " MAD (1st billing cycle)";
                $preview['summary_lines'][] = "You pay: " . number_format($finalAmount, 2) . " MAD now";
                $preview['summary_lines'][] = "Next cycle renews at: " . number_format($originalAmount, 2) . " MAD";
            } elseif ($durType === 'number_of_billing_cycles' || $durType === 'number_of_months') {
                $unitText = $durType === 'number_of_billing_cycles' ? 'billing cycles' : 'months';
                $preview['summary_lines'][] = "Normal price: " . number_format($originalAmount, 2) . " MAD / " . ($billingCycle === 'annual' ? 'yr' : 'mo');
                $preview['summary_lines'][] = "Discount: " . $coupon->getPromotionLabel() . " for the first {$durVal} {$unitText}";
                $preview['summary_lines'][] = "Customer pays: " . number_format($finalAmount, 2) . " MAD for {$durVal} {$unitText}";
                $preview['summary_lines'][] = "Then standard price: " . number_format($originalAmount, 2) . " MAD";
            } elseif ($durType === 'lifetime') {
                $preview['summary_lines'][] = "Normal price: " . number_format($originalAmount, 2) . " MAD";
                $preview['summary_lines'][] = "Lifetime discount: -" . number_format($discountAmount, 2) . " MAD";
                $preview['summary_lines'][] = "Recurring price: " . number_format($finalAmount, 2) . " MAD / " . ($billingCycle === 'annual' ? 'yr' : 'mo');
            }
        }

        return $preview;
    }

    /**
     * Apply coupon and store snapshot.
     */
    public function applyCoupon(string $code, Company $company, Plan $plan, string $billingCycle, float $amount, int $invoiceId, ?int $userId = null, ?int $subscriptionId = null): void
    {
        DB::transaction(function () use ($code, $company, $plan, $billingCycle, $amount, $invoiceId, $userId, $subscriptionId) {
            $coupon = Coupon::where('code', $code)->lockForUpdate()->first();
            if (!$coupon) return;

            // Increment usage count
            $coupon->increment('current_uses');

            $invoice = Invoice::find($invoiceId);
            $paymentId = $invoice?->payment_id;
            if (!$subscriptionId) {
                $subscriptionId = $invoice?->payment?->subscription_id;
            }

            $promoType = $coupon->getPromotionTypeString();
            $discount = $this->calculateDiscount($code, $amount, $company, $plan, $billingCycle);
            $finalAmount = max(0.00, $amount - $discount);

            // Compute promotion dates
            $startsAt = Carbon::now();
            $endsAt = null;
            $freeUntil = null;
            $maxCycles = null;

            if ($promoType === 'free_until_date') {
                $freeUntil = $coupon->free_until_date;
                $endsAt = $coupon->free_until_date;
            } elseif ($promoType === 'free_period' || $promoType === 'free_subscription') {
                $durVal = $coupon->duration_value ?: $coupon->duration_in_months ?: (int)$coupon->value ?: 1;
                $durUnit = $coupon->duration_unit ?: 'months';
                $endsAt = $durUnit === 'days' ? Carbon::now()->addDays($durVal) : Carbon::now()->addMonths($durVal);
            } elseif ($coupon->duration_type === 'number_of_days') {
                $endsAt = Carbon::now()->addDays($coupon->duration_value ?: 30);
            } elseif ($coupon->duration_type === 'number_of_months') {
                $endsAt = Carbon::now()->addMonths($coupon->duration_value ?: 1);
            } elseif ($coupon->duration_type === 'number_of_billing_cycles') {
                $maxCycles = $coupon->duration_value ?: 1;
            } elseif ($coupon->duration_type === 'one_time') {
                $maxCycles = 1;
            }

            // Snapshot promotion parameters into coupon_redemptions (Rule #12)
            $redemption = CouponRedemption::create([
                'coupon_id' => $coupon->id,
                'company_id' => $company->id,
                'subscription_id' => $subscriptionId,
                'promotion_type' => $promoType,
                'discount_type' => $coupon->type?->value ?? 'percentage',
                'discount_value' => $coupon->value,
                'duration_type' => $coupon->duration_type ?: 'one_time',
                'duration_value' => $coupon->duration_value ?: $coupon->duration_in_months,
                'duration_unit' => $coupon->duration_unit ?: 'months',
                'billing_cycles_discounted' => 1,
                'max_discounted_cycles' => $maxCycles,
                'original_price' => $amount,
                'discount_amount' => $discount,
                'final_amount' => $finalAmount,
                'free_until_date' => $freeUntil,
                'status' => 'active',
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'redeemed_at' => Carbon::now(),
            ]);

            // If only 1 cycle allowed, mark as completed
            if ($maxCycles === 1) {
                $redemption->update(['status' => 'completed']);
            }

            // Record usage log
            CouponUsage::create([
                'coupon_id' => $coupon->id,
                'company_id' => $company->id,
                'user_id' => $userId ?? auth()->id(),
                'invoice_id' => $invoiceId,
                'payment_id' => $paymentId,
                'subscription_id' => $subscriptionId,
                'discount_amount' => $discount,
                'ip_address' => request()->ip(),
            ]);

            // Handle trial extension directly if applicable
            if ($coupon->type === CouponType::TRIAL_EXTENSION || $promoType === 'extended_subscription') {
                $extVal = $coupon->extension_value ?: $coupon->duration_in_months ?: (int)$coupon->value ?: 30;
                $extUnit = $coupon->extension_unit ?: 'days';

                $currentTrial = $company->trial_ends_at ? Carbon::parse($company->trial_ends_at) : Carbon::now();
                if ($currentTrial->isPast()) {
                    $currentTrial = Carbon::now();
                }

                $newTrial = $extUnit === 'months' ? $currentTrial->addMonths($extVal) : $currentTrial->addDays($extVal);
                $company->update(['trial_ends_at' => $newTrial]);
            }

            $this->logHistory($coupon->id, $company->id, $userId, 'apply', 'success');

            // Limit warning event
            if ($coupon->max_total_uses && !$coupon->is_unlimited) {
                $percentUsed = ($coupon->current_uses / $coupon->max_total_uses) * 100;
                if ($percentUsed >= 90) {
                    event(new \App\Modules\Coupons\Events\CouponLimitAlmostReached($coupon));
                }
            }
        });
    }

    /**
     * Log coupon validations.
     */
    protected function logHistory(?int $couponId, ?int $companyId, ?int $userId, string $action, string $status, ?string $failureReason = null): void
    {
        try {
            CouponHistory::create([
                'coupon_id' => $couponId,
                'company_id' => $companyId,
                'user_id' => $userId ?? auth()->id(),
                'action' => $action,
                'status' => $status,
                'failure_reason' => $failureReason,
                'ip_address' => request()->ip(),
            ]);
        } catch (\Exception $e) {
            // Silently capture history errors
        }
    }

    /**
     * Admin tool: Generate a random promotional code.
     */
    public function generateRandomCode(array $options = []): string
    {
        $length = $options['length'] ?? 8;
        $prefix = $options['prefix'] ?? '';
        $suffix = $options['suffix'] ?? '';
        $excludeConfusing = $options['exclude_confusing'] ?? true;
        $numbers = $options['numbers'] ?? true;
        $uppercase = $options['uppercase'] ?? true;

        $chars = '';
        if ($uppercase) {
            $chars .= 'ABCDEFGHJKLMNPQRSTUVWXYZ';
            if (!$excludeConfusing) {
                $chars .= 'IO';
            }
        }
        if ($numbers) {
            $chars .= '23456789';
            if (!$excludeConfusing) {
                $chars .= '01';
            }
        }

        if (empty($chars)) {
            $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        }

        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[rand(0, strlen($chars) - 1)];
        }

        $finalCode = $prefix . $code . $suffix;

        if (Coupon::where('code', $finalCode)->exists()) {
            return $this->generateRandomCode($options);
        }

        return $finalCode;
    }
}
