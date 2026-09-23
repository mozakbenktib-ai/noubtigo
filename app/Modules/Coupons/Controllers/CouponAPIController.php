<?php

namespace App\Modules\Coupons\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Coupons\Models\Coupon;
use App\Modules\Coupons\Models\CouponUsage;
use App\Modules\Coupons\Models\CouponRedemption;
use App\Modules\Coupons\Services\CouponService;
use App\Modules\Coupons\Services\Processors\DiscountProcessorFactory;
use App\Modules\Companies\Models\Company;
use App\Modules\Subscriptions\Models\Plan;
use App\Modules\Coupons\Enums\CouponStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;

class CouponAPIController extends Controller
{
    protected $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    /**
     * GET /api/v1/coupons
     */
    public function index(Request $request)
    {
        if (auth()->user() && !auth()->user()->is_system_admin) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $coupons = Coupon::with('plans')->latest()->paginate(15);
        return response()->json($coupons);
    }

    /**
     * POST /api/v1/coupons/validate
     */
    public function validateCoupon(Request $request)
    {
        $request->validate([
            'code'           => 'required|string',
            'company_uuid'   => 'required|string',
            'plan_id'        => 'required|integer',
            'billing_cycle'  => 'required|in:monthly,annual',
            'current_amount' => 'required|numeric',
        ]);

        $company = Company::resolveRouteBinding($request->company_uuid);
        if (!$company) {
            return response()->json(['valid' => false, 'message' => 'Company not found.'], 404);
        }

        $plan = Plan::find($request->plan_id);
        if (!$plan) {
            return response()->json(['valid' => false, 'message' => 'Plan not found.'], 404);
        }

        try {
            $processor = DiscountProcessorFactory::resolve($request->code);
            $validation = $processor->validate(
                $request->code,
                $company,
                $plan,
                $request->billing_cycle,
                $request->current_amount
            );

            if (!$validation['valid']) {
                return response()->json(['valid' => false, 'message' => $validation['message']]);
            }

            $discount = $processor->calculate(
                $request->code,
                $request->current_amount,
                $company,
                $plan,
                $request->billing_cycle
            );

            return response()->json([
                'valid'           => true,
                'discount_amount' => $discount,
                'final_amount'    => max(0.00, $request->current_amount - $discount),
                'preview'         => $validation['preview'] ?? null,
                'message'         => 'Coupon code is valid.'
            ]);
        } catch (Exception $e) {
            return response()->json(['valid' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/v1/coupons/apply
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code'          => 'required|string',
            'company_uuid'  => 'required|string',
            'plan_id'       => 'required|integer',
            'billing_cycle' => 'required|in:monthly,annual',
            'amount'        => 'required|numeric',
            'invoice_id'    => 'required|integer',
        ]);

        $company = Company::resolveRouteBinding($request->company_uuid);
        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Company not found.'], 404);
        }

        $plan = Plan::find($request->plan_id);
        if (!$plan) {
            return response()->json(['success' => false, 'message' => 'Plan not found.'], 404);
        }

        try {
            $processor = DiscountProcessorFactory::resolve($request->code);
            $validation = $processor->validate($request->code, $company, $plan, $request->billing_cycle, $request->amount);

            if (!$validation['valid']) {
                return response()->json(['success' => false, 'message' => $validation['message']]);
            }

            $processor->apply($request->code, $company, $plan, $request->billing_cycle, $request->amount, $request->invoice_id);

            return response()->json([
                'success' => true,
                'message' => 'Coupon code applied successfully.'
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * GET /api/v1/coupons/{code}/history
     */
    public function history(string $code)
    {
        if (auth()->user() && !auth()->user()->is_system_admin) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $coupon = Coupon::where('code', $code)->firstOrFail();
        $usages = CouponUsage::with(['company', 'user'])->where('coupon_id', $coupon->id)->latest()->paginate(15);
        $redemptions = CouponRedemption::with(['company'])->where('coupon_id', $coupon->id)->latest()->paginate(15);

        return response()->json([
            'coupon' => $coupon,
            'usages' => $usages,
            'redemptions' => $redemptions,
        ]);
    }

    /**
     * GET /api/v1/coupons/statistics
     */
    public function statistics()
    {
        if (auth()->user() && !auth()->user()->is_system_admin) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $total = Coupon::count();
        $active = Coupon::where('status', CouponStatus::ACTIVE->value)->count();
        $expired = Coupon::where('status', CouponStatus::ACTIVE->value)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', Carbon::now())
            ->count();
        $disabled = Coupon::where('status', CouponStatus::DISABLED->value)->count();

        $totalDiscounts = CouponUsage::sum('discount_amount');
        $totalRedemptions = CouponRedemption::count();

        return response()->json([
            'total_coupons' => $total,
            'active_coupons' => $active,
            'expired_coupons' => $expired,
            'disabled_coupons' => $disabled,
            'total_discounts_given' => $totalDiscounts,
            'total_redemptions' => $totalRedemptions,
        ]);
    }
}
