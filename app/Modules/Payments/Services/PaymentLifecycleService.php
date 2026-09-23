<?php

namespace App\Modules\Payments\Services;

use App\Modules\Companies\Models\Company;
use App\Modules\Subscriptions\Models\Plan;
use App\Modules\Payments\Models\Subscription;
use App\Modules\Payments\Models\Payment;
use App\Modules\Payments\Models\Invoice;
use App\Modules\Payments\Models\InvoiceItem;
use App\Modules\Payments\Managers\PaymentManager;
use App\Modules\Coupons\Models\Coupon;
use App\Modules\Coupons\Models\CouponRedemption;
use App\Modules\Coupons\Models\CouponUsage;
use App\Modules\Coupons\Models\CouponHistory;
use App\Modules\Coupons\Enums\CouponType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentLifecycleService
{
    protected $paymentManager;

    public function __construct(PaymentManager $paymentManager)
    {
        $this->paymentManager = $paymentManager;
    }

    /**
     * Initialize a new subscription and payment request.
     */
    public function createSubscriptionRequest(Company $company, Plan $plan, string $billingCycle, string $paymentMethod, ?string $receiptPath = null, ?string $couponCode = null): array
    {
        return DB::transaction(function () use ($company, $plan, $billingCycle, $paymentMethod, $receiptPath, $couponCode) {
            $originalAmount = (float) ($billingCycle === 'annual' ? ($plan->annual_price ?? $plan->price ?? 0.0) : ($plan->price ?? 0.0));
            $amount = $originalAmount;
            $discountAmount = 0.00;
            $coupon = null;
            $targetPlan = $plan;

            // Handle coupon validation and calculations
            if (!empty($couponCode)) {
                $processor = \App\Modules\Coupons\Services\Processors\DiscountProcessorFactory::resolve($couponCode);
                $validation = $processor->validate($couponCode, $company, $plan, $billingCycle, $originalAmount);
                
                if (!$validation['valid']) {
                    throw new \Exception($validation['message']);
                }

                $coupon = Coupon::where('code', $couponCode)->lockForUpdate()->first();
                $promoType = $coupon ? $coupon->getPromotionTypeString() : null;

                // 1. Subscription Extension / Trial Extension: Apply directly without creating unnecessary invoice
                if ($coupon && ($promoType === 'extended_subscription' || $coupon->type === CouponType::TRIAL_EXTENSION)) {
                    $extVal = $coupon->extension_value ?: $coupon->duration_in_months ?: (int)$coupon->value ?: 1;
                    $extUnit = $coupon->extension_unit ?: 'months';

                    // Check if company has an active subscription
                    $activeSub = $company->subscriptions()->where('status', 'active')->latest('ends_at')->first();

                    if ($activeSub) {
                        $currentEnd = ($activeSub->ends_at && $activeSub->ends_at->isFuture()) 
                            ? $activeSub->ends_at->copy() 
                            : Carbon::now();

                        $newEnd = $extUnit === 'days' ? $currentEnd->addDays($extVal) : $currentEnd->addMonths($extVal);
                        $activeSub->update(['ends_at' => $newEnd]);

                        $message = "Subscription extended successfully by {$extVal} {$extUnit}. New expiration date: " . $newEnd->format('M d, Y') . ".";
                    } else {
                        // Extend company trial
                        $currentTrial = $company->trial_ends_at ? Carbon::parse($company->trial_ends_at) : Carbon::now();
                        if ($currentTrial->isPast()) {
                            $currentTrial = Carbon::now();
                        }

                        $newTrial = $extUnit === 'days' ? $currentTrial->addDays($extVal) : $currentTrial->addMonths($extVal);
                        $company->update(['trial_ends_at' => $newTrial]);

                        $message = "Trial extended successfully by {$extVal} {$extUnit}. New trial expiration: " . $newTrial->format('M d, Y') . ".";
                    }

                    // Record snapshot redemption
                    CouponRedemption::create([
                        'coupon_id' => $coupon->id,
                        'company_id' => $company->id,
                        'subscription_id' => $activeSub?->id,
                        'promotion_type' => 'extended_subscription',
                        'discount_type' => $coupon->type?->value ?? 'fixed',
                        'discount_value' => $coupon->value,
                        'duration_type' => 'one_time',
                        'duration_value' => $extVal,
                        'duration_unit' => $extUnit,
                        'billing_cycles_discounted' => 0,
                        'original_price' => 0,
                        'discount_amount' => 0,
                        'final_amount' => 0,
                        'status' => 'completed',
                        'starts_at' => Carbon::now(),
                        'ends_at' => Carbon::now(),
                        'redeemed_at' => Carbon::now(),
                    ]);

                    // Record audit history
                    CouponHistory::create([
                        'coupon_id' => $coupon->id,
                        'company_id' => $company->id,
                        'user_id' => auth()->id(),
                        'action' => 'apply',
                        'status' => 'success',
                        'ip_address' => request()->ip(),
                    ]);

                    $coupon->increment('current_uses');

                    return [
                        'trial_extended' => true,
                        'message' => $message,
                    ];
                }

                // If plan upgrade coupon, customer pays chosen plan price but receives upgraded plan_id
                if ($coupon && $coupon->type === CouponType::PLAN_UPGRADE && $coupon->target_plan_id) {
                    $targetPlan = Plan::findOrFail($coupon->target_plan_id);
                }

                $discountAmount = $processor->calculate($couponCode, $originalAmount, $company, $plan, $billingCycle);
                $amount = max(0.00, $originalAmount - $discountAmount);
            }

            // 1. Create Pending Subscription
            $subscription = Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $targetPlan->id,
                'billing_cycle' => $billingCycle,
                'status' => 'pending',
                'coupon_id' => $coupon ? $coupon->id : null,
            ]);

            // 2. Create Pending Payment
            $payment = Payment::create([
                'company_id' => $company->id,
                'subscription_id' => $subscription->id,
                'amount' => $amount,
                'discount' => $discountAmount,
                'coupon_id' => $coupon ? $coupon->id : null,
                'currency' => 'USD',
                'payment_method' => $paymentMethod,
                'receipt_path' => $receiptPath,
                'status' => 'pending',
            ]);

            // 3. Generate Open Invoice
            $invoice = Invoice::create([
                'company_id' => $company->id,
                'payment_id' => $payment->id,
                'invoice_number' => 'INV-' . strtoupper(uniqid()),
                'subtotal' => $originalAmount,
                'discount' => $discountAmount,
                'tax' => 0,
                'total' => $amount,
                'currency' => 'USD',
                'status' => 'open',
                'coupon_id' => $coupon ? $coupon->id : null,
                'due_date' => Carbon::now()->addDays(7),
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $targetPlan->name . ' - ' . ucfirst($billingCycle),
                'quantity' => 1,
                'unit_price' => $originalAmount,
                'total' => $originalAmount,
            ]);

            // Negative line item for discount
            if ($discountAmount > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => "Promo Discount ({$couponCode})",
                    'quantity' => 1,
                    'unit_price' => -$discountAmount,
                    'total' => -$discountAmount,
                ]);
            }

            // Apply coupon logs / usages and snapshot
            if ($coupon) {
                $couponService = app(\App\Modules\Coupons\Services\CouponService::class);
                $couponService->applyCoupon($couponCode, $company, $plan, $billingCycle, $originalAmount, $invoice->id, auth()->id(), $subscription->id);
            }

            // 4. Auto-activate if total amount is 0 (Free period / 100% discount)
            if ($amount <= 0.00) {
                $payment->update(['status' => 'paid']);
                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => Carbon::now(),
                ]);

                $startsAt = Carbon::now();
                $previousSub = $company->subscriptions()
                    ->where('id', '!=', $subscription->id)
                    ->whereNotNull('ends_at')
                    ->latest('ends_at')
                    ->first();

                if ($previousSub && $previousSub->ends_at && $previousSub->ends_at->greaterThan(Carbon::now()->subDays(30))) {
                    $startsAt = $previousSub->ends_at->copy();
                }

                $endsAt = $billingCycle === 'annual' 
                    ? $startsAt->copy()->addYear() 
                    : $startsAt->copy()->addMonth();

                // Compute exact ends_at for free periods or custom durations
                if ($coupon) {
                    $promoType = $coupon->getPromotionTypeString();
                    if ($promoType === 'free_until_date' && $coupon->free_until_date) {
                        $endsAt = $coupon->free_until_date->copy();
                    } elseif ($coupon->duration_type === 'number_of_days') {
                        $endsAt = $startsAt->copy()->addDays($coupon->duration_value ?: 30);
                    } elseif ($coupon->duration_type === 'number_of_months') {
                        $endsAt = $startsAt->copy()->addMonths($coupon->duration_value ?: 1);
                    } elseif ($promoType === 'free_period' || $coupon->type === CouponType::FREE_SUBSCRIPTION) {
                        $durVal = $coupon->duration_value ?: $coupon->duration_in_months ?: (int)$coupon->value ?: 1;
                        $durUnit = $coupon->duration_unit ?: 'months';
                        $endsAt = $durUnit === 'days' ? $startsAt->copy()->addDays($durVal) : $startsAt->copy()->addMonths($durVal);
                    }
                }

                $subscription->update([
                    'status' => 'active',
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                ]);

                $company->update(['plan_id' => $subscription->plan_id]);

                return [
                    'subscription' => $subscription,
                    'payment' => $payment,
                    'invoice' => $invoice,
                    'provider_data' => [
                        'status' => 'paid',
                        'message' => 'Free subscription activated immediately.',
                        'reference' => 'free_' . time()
                    ],
                    'auto_activated' => true,
                ];
            }

            // 5. Initialize Payment via Provider
            $provider = $this->paymentManager->resolve($paymentMethod);
            $providerData = $provider->initializePayment($payment);

            return [
                'subscription' => $subscription,
                'payment' => $payment,
                'invoice' => $invoice,
                'provider_data' => $providerData,
            ];
        });
    }

    /**
     * Activate the subscription after a successful payment.
     */
    public function activateSubscription(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $payment->update(['status' => 'paid']);

            if ($payment->invoice) {
                $payment->invoice->update([
                    'status' => 'paid',
                    'paid_at' => Carbon::now(),
                ]);
            }

            $subscription = $payment->subscription;
            if ($subscription) {
                $company = $subscription->company;

                $previousSub = $company->subscriptions()
                    ->where('id', '!=', $subscription->id)
                    ->whereNotNull('ends_at')
                    ->latest('ends_at')
                    ->first();

                $startsAt = Carbon::now();
                if ($previousSub && $previousSub->ends_at && $previousSub->ends_at->greaterThan(Carbon::now()->subDays(30))) {
                    $startsAt = $previousSub->ends_at->copy();
                }

                $endsAt = $subscription->billing_cycle === 'annual' 
                    ? $startsAt->copy()->addYear() 
                    : $startsAt->copy()->addMonth();

                $coupon = $subscription->coupon;
                if ($coupon) {
                    $promoType = $coupon->getPromotionTypeString();
                    if ($promoType === 'free_until_date' && $coupon->free_until_date) {
                        $endsAt = $coupon->free_until_date->copy();
                    } elseif ($coupon->duration_type === 'number_of_days') {
                        $endsAt = $startsAt->copy()->addDays($coupon->duration_value ?: 30);
                    } elseif ($coupon->duration_type === 'number_of_months') {
                        $endsAt = $startsAt->copy()->addMonths($coupon->duration_value ?: 1);
                    } elseif ($promoType === 'free_period' || $coupon->type === CouponType::FREE_SUBSCRIPTION) {
                        $durVal = $coupon->duration_value ?: $coupon->duration_in_months ?: (int)$coupon->value ?: 1;
                        $durUnit = $coupon->duration_unit ?: 'months';
                        $endsAt = $durUnit === 'days' ? $startsAt->copy()->addDays($durVal) : $startsAt->copy()->addMonths($durVal);
                    }
                }

                $subscription->update([
                    'status' => 'active',
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                ]);

                $company->update(['plan_id' => $subscription->plan_id]);
            }
        });
    }

    /**
     * Process an admin approving a manual payment.
     */
    public function approveManualPayment(Payment $payment): void
    {
        if (!in_array($payment->payment_method, ['manual', 'virement', 'chari_online'])) {
            throw new \Exception('Payment is not a manual/bank/chari payment.');
        }

        if ($payment->status === 'paid') {
            return;
        }

        $this->activateSubscription($payment);
    }
}
