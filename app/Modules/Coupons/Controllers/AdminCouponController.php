<?php

namespace App\Modules\Coupons\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Coupons\Models\Coupon;
use App\Modules\Coupons\Models\CouponUsage;
use App\Modules\Coupons\Models\CouponRedemption;
use App\Modules\Coupons\Models\CouponLog;
use App\Modules\Coupons\Models\CouponHistory;
use App\Modules\Coupons\Repositories\CouponRepositoryInterface;
use App\Modules\Coupons\Requests\SaveCouponRequest;
use App\Modules\Coupons\Enums\CouponType;
use App\Modules\Coupons\Enums\CouponStatus;
use App\Modules\Coupons\Enums\CouponCategory;
use App\Modules\Coupons\Enums\PromotionDuration;
use App\Modules\Subscriptions\Models\Plan;
use App\Modules\Companies\Models\Company;
use App\Modules\Coupons\Services\CouponService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminCouponController extends Controller
{
    public function __construct(
        protected CouponRepositoryInterface $couponRepository,
        protected CouponService $couponService
    ) {}

    /**
     * Display dashboard statistics and list of coupons.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'type', 'category']);
        $coupons = $this->couponRepository->paginate($filters, 15);

        // Calculate dashboard statistics
        $stats = [
            'total'               => Coupon::count(),
            'active'              => Coupon::where('status', CouponStatus::ACTIVE->value)->count(),
            'expired'             => Coupon::where('status', CouponStatus::ACTIVE->value)
                                         ->whereNotNull('expires_at')
                                         ->where('expires_at', '<', Carbon::now())
                                         ->count(),
            'disabled'            => Coupon::where('status', CouponStatus::DISABLED->value)->count(),
            'used_today'          => CouponUsage::whereDate('created_at', Carbon::today())->count(),
            'used_this_month'     => CouponUsage::whereMonth('created_at', Carbon::now()->month)
                                         ->whereYear('created_at', Carbon::now()->year)
                                         ->count(),
            'total_discounts'     => CouponUsage::sum('discount_amount'),
            'total_redemptions'   => CouponRedemption::count(),
        ];

        // Most used coupon
        $mostUsedStat = CouponUsage::select('coupon_id', DB::raw('count(*) as count'))
            ->groupBy('coupon_id')
            ->orderByDesc('count')
            ->first();
        $stats['most_used'] = $mostUsedStat ? Coupon::find($mostUsedStat->coupon_id) : null;
        $stats['most_used_count'] = $mostUsedStat?->count ?? 0;

        // Top coupons
        $stats['top_coupons'] = Coupon::withCount('usages')
            ->orderByDesc('usages_count')
            ->take(5)
            ->get();

        // Expiring soon
        $stats['expiring_soon'] = Coupon::where('status', CouponStatus::ACTIVE->value)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [Carbon::now(), Carbon::now()->addDays(7)])
            ->get();

        $types = CouponType::cases();
        $durations = PromotionDuration::cases();
        $statuses = CouponStatus::cases();
        $categories = CouponCategory::cases();

        return view('modules.coupons.admin.index', compact('coupons', 'stats', 'types', 'durations', 'statuses', 'categories'));
    }

    /**
     * Show coupon creation page.
     */
    public function create()
    {
        $plans = Plan::where('is_active', true)->get();
        $companies = Company::where('is_active', true)->get();
        $types = CouponType::cases();
        $durations = PromotionDuration::cases();
        $statuses = CouponStatus::cases();
        $categories = CouponCategory::cases();

        $randomCode = $this->couponService->generateRandomCode();

        return view('modules.coupons.admin.create', compact('plans', 'companies', 'types', 'durations', 'statuses', 'categories', 'randomCode'));
    }

    /**
     * Save a new coupon configuration.
     */
    public function store(SaveCouponRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            
            $data['allowed_emails'] = !empty($data['allowed_emails']) ? array_filter($data['allowed_emails']) : null;
            $data['allowed_domains'] = !empty($data['allowed_domains']) ? array_filter($data['allowed_domains']) : null;
            $data['allowed_companies'] = !empty($data['allowed_companies']) ? array_filter($data['allowed_companies']) : null;
            
            $data['created_by'] = auth()->id();
            $data['is_unlimited'] = $request->has('is_unlimited');
            $data['is_stackable'] = $request->has('is_stackable');
            $data['is_hidden'] = $request->has('is_hidden');

            // Sync promotion_type with type
            if (empty($data['promotion_type'])) {
                $data['promotion_type'] = is_string($data['type']) ? $data['type'] : $data['type']->value;
            }

            $coupon = $this->couponRepository->create($data);

            if ($request->filled('plan_ids')) {
                $coupon->plans()->sync($request->plan_ids);
            }

            if ($request->filled('company_ids') && $request->tenant_restriction_type !== 'none') {
                $coupon->companies()->sync($request->company_ids);
            }

            CouponLog::create([
                'coupon_id' => $coupon->id,
                'user_id'   => auth()->id(),
                'action'    => 'created',
                'changes'   => ['after' => $coupon->toArray()],
                'ip_address'=> $request->ip(),
            ]);

            return redirect()->route('admin.coupons.index')
                ->with('success', 'Coupon created successfully.');
        });
    }

    /**
     * Show coupon details, analytics, redemptions and timeline logs.
     */
    public function show(Coupon $coupon)
    {
        $coupon->load(['plans', 'companies', 'creator', 'targetPlan']);

        // Usage history
        $usages = CouponUsage::with(['company', 'user', 'invoice'])
            ->where('coupon_id', $coupon->id)
            ->latest()
            ->paginate(10, ['*'], 'usages_page');

        // Redemptions
        $redemptions = CouponRedemption::with(['company', 'subscription.plan'])
            ->where('coupon_id', $coupon->id)
            ->latest()
            ->paginate(10, ['*'], 'redemptions_page');

        // Audit log of updates
        $auditLogs = CouponLog::with('user')
            ->where('coupon_id', $coupon->id)
            ->latest()
            ->paginate(10, ['*'], 'logs_page');

        // Validation attempts
        $validationAttempts = CouponHistory::with(['company', 'user'])
            ->where('coupon_id', $coupon->id)
            ->latest()
            ->paginate(15, ['*'], 'attempts_page');

        // Financial & Promotion Analytics (Rule #18)
        $totalRedemptions = CouponRedemption::where('coupon_id', $coupon->id)->count();
        $successfulRedemptions = CouponUsage::where('coupon_id', $coupon->id)->count();
        $failedAttempts = CouponHistory::where('coupon_id', $coupon->id)->where('status', 'failed')->count();
        $activePromotions = CouponRedemption::where('coupon_id', $coupon->id)->where('status', 'active')->count();
        $expiredPromotions = CouponRedemption::where('coupon_id', $coupon->id)->whereIn('status', ['completed', 'cancelled'])->count();
        
        $discountAmount = CouponUsage::where('coupon_id', $coupon->id)->sum('discount_amount');
        
        // Calculate gross and net revenue from invoices attached to coupon
        $invoices = \App\Modules\Payments\Models\Invoice::where('coupon_id', $coupon->id)->get();
        $revenueBeforeDiscounts = $invoices->sum('subtotal');
        if ($revenueBeforeDiscounts == 0 && $discountAmount > 0) {
            $revenueBeforeDiscounts = $discountAmount;
        }
        $revenueAfterDiscounts = max(0, $revenueBeforeDiscounts - $discountAmount);

        $analytics = [
            'total_redemptions' => $totalRedemptions,
            'successful_redemptions' => $successfulRedemptions,
            'failed_attempts' => $failedAttempts,
            'active_promotions' => $activePromotions,
            'expired_promotions' => $expiredPromotions,
            'revenue_before_discounts' => $revenueBeforeDiscounts,
            'discount_amount' => $discountAmount,
            'revenue_after_discounts' => $revenueAfterDiscounts,
        ];

        return view('modules.coupons.admin.show', compact('coupon', 'usages', 'redemptions', 'auditLogs', 'validationAttempts', 'analytics'));
    }

    /**
     * Show coupon edit form.
     */
    public function edit(Coupon $coupon)
    {
        $coupon->load(['plans', 'companies']);
        $plans = Plan::where('is_active', true)->get();
        $companies = Company::where('is_active', true)->get();
        $types = CouponType::cases();
        $durations = PromotionDuration::cases();
        $statuses = CouponStatus::cases();
        $categories = CouponCategory::cases();

        return view('modules.coupons.admin.edit', compact('coupon', 'plans', 'companies', 'types', 'durations', 'statuses', 'categories'));
    }

    /**
     * Update an existing coupon configuration.
     */
    public function update(SaveCouponRequest $request, Coupon $coupon)
    {
        return DB::transaction(function () use ($request, $coupon) {
            $oldValues = $coupon->toArray();
            $data = $request->validated();

            $data['allowed_emails'] = !empty($data['allowed_emails']) ? array_filter($data['allowed_emails']) : null;
            $data['allowed_domains'] = !empty($data['allowed_domains']) ? array_filter($data['allowed_domains']) : null;
            $data['allowed_companies'] = !empty($data['allowed_companies']) ? array_filter($data['allowed_companies']) : null;

            $data['is_unlimited'] = $request->has('is_unlimited');
            $data['is_stackable'] = $request->has('is_stackable');
            $data['is_hidden'] = $request->has('is_hidden');

            if (empty($data['promotion_type'])) {
                $data['promotion_type'] = is_string($data['type']) ? $data['type'] : $data['type']->value;
            }

            $this->couponRepository->update($coupon, $data);

            if ($request->filled('plan_ids')) {
                $coupon->plans()->sync($request->plan_ids);
            } else {
                $coupon->plans()->detach();
            }

            if ($request->filled('company_ids') && $request->tenant_restriction_type !== 'none') {
                $coupon->companies()->sync($request->company_ids);
            } else {
                $coupon->companies()->detach();
            }

            CouponLog::create([
                'coupon_id' => $coupon->id,
                'user_id'   => auth()->id(),
                'action'    => 'updated',
                'changes'   => ['before' => $oldValues, 'after' => $coupon->fresh()->toArray()],
                'ip_address'=> $request->ip(),
            ]);

            return redirect()->route('admin.coupons.index')
                ->with('success', 'Coupon updated successfully.');
        });
    }

    /**
     * Duplicate a coupon.
     */
    public function duplicate(Coupon $coupon)
    {
        $newCoupon = $coupon->replicate();
        
        $newCoupon->code = $this->couponService->generateRandomCode();
        $newCoupon->uuid = (string) Str::uuid();
        $newCoupon->current_uses = 0;
        $newCoupon->save();

        $newCoupon->plans()->sync($coupon->plans->pluck('id')->toArray());
        $newCoupon->companies()->sync($coupon->companies->pluck('id')->toArray());

        CouponLog::create([
            'coupon_id' => $newCoupon->id,
            'user_id'   => auth()->id(),
            'action'    => 'created',
            'changes'   => ['after' => $newCoupon->toArray(), 'duplicated_from' => $coupon->code],
            'ip_address'=> request()->ip(),
        ]);

        return redirect()->route('admin.coupons.edit', $newCoupon->uuid)
            ->with('success', 'Coupon duplicated successfully. Please review its parameters.');
    }

    /**
     * Soft delete a coupon.
     */
    public function destroy(Coupon $coupon)
    {
        $oldValues = $coupon->toArray();
        $this->couponRepository->delete($coupon);

        CouponLog::create([
            'coupon_id' => null,
            'user_id'   => auth()->id(),
            'action'    => 'deleted',
            'changes'   => ['before' => $oldValues],
            'ip_address'=> request()->ip(),
        ]);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon deleted successfully.');
    }

    /**
     * Handle bulk actions.
     */
    public function bulkActions(Request $request)
    {
        $request->validate([
            'coupon_ids' => 'required|array',
            'coupon_ids.*' => 'exists:coupons,id',
            'bulk_action' => 'required|in:enable,disable,delete,archive',
        ]);

        $ids = $request->coupon_ids;
        $action = $request->bulk_action;

        DB::transaction(function () use ($ids, $action) {
            foreach ($ids as $id) {
                $coupon = Coupon::find($id);
                if (!$coupon) continue;

                $oldValues = $coupon->toArray();

                if ($action === 'enable') {
                    $coupon->update(['status' => CouponStatus::ACTIVE->value]);
                    $logAction = 'enabled';
                } elseif ($action === 'disable') {
                    $coupon->update(['status' => CouponStatus::DISABLED->value]);
                    $logAction = 'disabled';
                } elseif ($action === 'archive') {
                    $coupon->update(['status' => CouponStatus::ARCHIVED->value]);
                    $logAction = 'archived';
                } elseif ($action === 'delete') {
                    $coupon->delete();
                    $logAction = 'deleted';
                }

                CouponLog::create([
                    'coupon_id' => $action === 'delete' ? null : $coupon->id,
                    'user_id'   => auth()->id(),
                    'action'    => $logAction,
                    'changes'   => ['before' => $oldValues, 'after' => $action === 'delete' ? null : $coupon->fresh()->toArray()],
                    'ip_address'=> request()->ip(),
                ]);
            }
        });

        return redirect()->back()->with('success', 'Bulk action completed successfully.');
    }

    /**
     * Export Coupons list to CSV.
     */
    public function exportCsv()
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="coupons_export_' . date('Y-m-d') . '.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0'
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'ID', 'Code', 'Name', 'Promotion', 'Duration', 'Category', 'Status', 
                'Total Uses', 'Max Uses', 'Start Date', 'Expiration Date', 'Created At'
            ]);

            $coupons = Coupon::latest()->get();
            foreach ($coupons as $coupon) {
                fputcsv($file, [
                    $coupon->id,
                    $coupon->code,
                    $coupon->name,
                    $coupon->getPromotionLabel(),
                    $coupon->getDurationDescription(),
                    $coupon->coupon_category->value,
                    $coupon->status->value,
                    $coupon->current_uses,
                    $coupon->is_unlimited ? 'Unlimited' : ($coupon->max_total_uses ?? 'Unlimited'),
                    $coupon->starts_at?->toIso8601String() ?? 'N/A',
                    $coupon->expires_at?->toIso8601String() ?? 'N/A',
                    $coupon->created_at->toIso8601String()
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Render printable report dashboard (PDF layout).
     */
    public function exportReportPdf()
    {
        $stats = [
            'total'             => Coupon::count(),
            'active'            => Coupon::where('status', CouponStatus::ACTIVE->value)->count(),
            'total_discounts'   => CouponUsage::sum('discount_amount'),
            'total_redemptions' => CouponRedemption::count(),
        ];

        $coupons = Coupon::orderByDesc('created_at')->get();
        $usages = CouponUsage::with(['coupon', 'company'])
            ->latest()
            ->take(20)
            ->get();

        $monthlySavings = CouponUsage::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('YEAR(created_at) as year'),
            DB::raw('SUM(discount_amount) as total')
        )
        ->groupBy('year', 'month')
        ->orderByDesc('year')
        ->orderByDesc('month')
        ->take(12)
        ->get();

        $couponPerformance = Coupon::withCount('usages')
            ->withSum('usages as total_saved', 'discount_amount')
            ->orderByDesc('usages_count')
            ->take(10)
            ->get();

        $topTenants = Company::withCount('payments')
            ->get()
            ->map(function ($company) {
                $company->total_saved = CouponUsage::where('company_id', $company->id)->sum('discount_amount');
                $company->usages_count = CouponUsage::where('company_id', $company->id)->count();
                return $company;
            })
            ->sortByDesc('total_saved')
            ->take(10);

        $topPlans = Plan::all()->map(function ($plan) {
            $plan->usages_count = CouponUsage::whereHas('subscription', fn($q) => $q->where('plan_id', $plan->id))->count();
            $plan->total_saved = CouponUsage::whereHas('subscription', fn($q) => $q->where('plan_id', $plan->id))->sum('discount_amount');
            return $plan;
        })->sortByDesc('total_saved');

        return view('modules.coupons.admin.report', compact(
            'stats',
            'coupons',
            'usages',
            'monthlySavings',
            'couponPerformance',
            'topTenants',
            'topPlans'
        ));
    }

    /**
     * AJAX endpoint to generate a random code from the create page.
     */
    public function generateCodeAjax(Request $request)
    {
        $code = $this->couponService->generateRandomCode([
            'length' => $request->integer('length', 8),
            'prefix' => $request->string('prefix', ''),
            'suffix' => $request->string('suffix', ''),
            'exclude_confusing' => $request->boolean('exclude_confusing', true),
            'numbers' => $request->boolean('numbers', true),
            'uppercase' => $request->boolean('uppercase', true),
        ]);

        return response()->json(['code' => $code]);
    }
}
