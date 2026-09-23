<?php

namespace App\Modules\Coupons\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Coupons\Services\Processors\DiscountProcessorFactory;
use App\Modules\Coupons\Models\Coupon;
use App\Modules\Subscriptions\Models\Plan;
use App\Services\TenantManager;
use Illuminate\Http\Request;
use Exception;

class CompanyCouponController extends Controller
{
    public function __construct(protected TenantManager $tenantManager) {}

    /**
     * AJAX endpoint to validate a promo code and return rich preview calculations.
     */
    public function validateCoupon(Request $request)
    {
        $request->validate([
            'coupon_code'   => 'required|string',
            'plan_id'       => 'required|exists:plans,id',
            'billing_cycle' => 'required|in:monthly,annual',
        ]);

        $company = $this->tenantManager->getTenant();
        if (!$company) {
            return response()->json(['valid' => false, 'message' => 'No tenant context found.'], 403);
        }

        $code = trim($request->coupon_code);
        $plan = Plan::findOrFail($request->plan_id);
        $billingCycle = $request->billing_cycle;
        $originalAmount = $billingCycle === 'annual' ? $plan->annual_price : $plan->price;

        try {
            $processor = DiscountProcessorFactory::resolve($code);
            $validation = $processor->validate($code, $company, $plan, $billingCycle, $originalAmount);

            if (!$validation['valid']) {
                return response()->json([
                    'valid'   => false,
                    'message' => $validation['message'] ?? __('ui.coupon_invalid') ?? 'Coupon not valid.'
                ]);
            }

            $discount = $processor->calculate($code, $originalAmount, $company, $plan, $billingCycle);
            $finalAmount = max(0.00, $originalAmount - $discount);
            
            $coupon = Coupon::where('code', $code)->first();
            $couponType = $coupon?->type?->value;
            $promoType = $coupon ? $coupon->getPromotionTypeString() : null;

            return response()->json([
                'valid'           => true,
                'code'            => $coupon?->code,
                'name'            => $coupon?->name,
                'discount_amount' => $discount,
                'final_amount'    => $finalAmount,
                'original_amount' => $originalAmount,
                'coupon_type'     => $couponType,
                'promotion_type'  => $promoType,
                'preview'         => $validation['preview'] ?? null,
                'message'         => 'Promotion applied successfully!'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'valid'   => false,
                'message' => $e->getMessage() ?: (__('ui.coupon_invalid') ?? 'Coupon not valid.')
            ]);
        }
    }
}
