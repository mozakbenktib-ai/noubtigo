<?php

namespace App\Modules\Payments\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payments\Models\Payment;
use App\Modules\Payments\Models\Invoice;
use App\Modules\Payments\Models\Subscription;
use App\Modules\Payments\Services\PaymentLifecycleService;
use App\Modules\Subscriptions\Models\Plan;
use App\Modules\Coupons\Models\Coupon;
use App\Modules\Coupons\Enums\CouponType;
use App\Services\TenantManager;
use Illuminate\Http\Request;

class CompanyBillingController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected PaymentLifecycleService $lifecycle
    ) {}

    /**
     * Company Billing Dashboard - shows current plan, billing cycle,
     * subscription status, next renewal, payment history & invoices.
     */
    public function index()
    {
        $company = $this->tenantManager->getTenant();

        if (!$company) {
            if (auth()->user() && auth()->user()->is_system_admin) {
                return redirect()->route('dashboard');
            }
            abort(403, 'Unauthorized access. No company context.');
        }

        $activeSubscription = $company->subscriptions()
            ->with('plan')
            ->where('status', 'active')
            ->latest()
            ->first();

        $pendingSubscription = $company->subscriptions()
            ->with('plan')
            ->where('status', 'pending')
            ->latest()
            ->first();

        $payments = $company->payments()
            ->with('subscription.plan')
            ->latest()
            ->paginate(10);

        $invoices = $company->invoices()
            ->with('payment')
            ->latest()
            ->paginate(10);

        $plans = Plan::where('is_active', true)
            ->where(function ($query) use ($company) {
                $query->where('visibility_scope', 'all')
                    ->orWhereHas('visibleCompanies', fn ($visibleCompanies) =>
                        $visibleCompanies->whereKey($company->id)
                    );
            })
            ->get();

        return view('modules.payments.company.billing', compact(
            'company',
            'activeSubscription',
            'pendingSubscription',
            'payments',
            'invoices',
            'plans'
        ));
    }

    /**
     * Request a new subscription, promotional free period, or extension.
     */
    public function subscribe(Request $request)
    {
        $couponCode = trim((string)$request->input('coupon_code'));
        $company = $this->tenantManager->getTenant();
        if (!$company) {
            abort(403, 'Unauthorized access. No company context.');
        }

        $plan = Plan::findOrFail($request->plan_id);
        if (!$plan->isVisibleToCompany($company->id)) {
            abort(403, __('ui.plan_not_available'));
        }
        $billingCycle = $request->billing_cycle;
        $originalAmount = $billingCycle === 'annual' ? $plan->annual_price : $plan->price;
        $finalAmount = $originalAmount;
        $isExtension = false;

        if (!empty($couponCode)) {
            try {
                $processor = \App\Modules\Coupons\Services\Processors\DiscountProcessorFactory::resolve($couponCode);
                $validation = $processor->validate($couponCode, $company, $plan, $billingCycle, $originalAmount);
                if (!$validation['valid']) {
                    return redirect()->back()->withInput()->with('error', $validation['message']);
                }

                $coupon = Coupon::where('code', $couponCode)->first();
                if ($coupon) {
                    $promoType = $coupon->getPromotionTypeString();
                    if ($promoType === 'extended_subscription' || $coupon->type === CouponType::TRIAL_EXTENSION) {
                        $isExtension = true;
                    }
                }

                $discount = $processor->calculate($couponCode, $originalAmount, $company, $plan, $billingCycle);
                $finalAmount = max(0.00, $originalAmount - $discount);
            } catch (\Exception $e) {
                return redirect()->back()->withInput()->with('error', $e->getMessage());
            }
        }

        $receiptRequired = ($finalAmount > 0.00 && !$isExtension);

        $request->validate([
            'plan_id'        => 'required|exists:plans,id',
            'billing_cycle'  => 'required|in:monthly,annual',
            'payment_method' => 'required|in:virement,chari_online',
            'receipt'        => ($receiptRequired ? 'required|' : 'nullable|') . 'file|mimes:jpeg,png,jpg,pdf|max:5120', // Max 5MB
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        try {
            $result = $this->lifecycle->createSubscriptionRequest(
                $company,
                $plan,
                $request->billing_cycle,
                $request->payment_method,
                $receiptPath,
                $couponCode ?: null
            );

            if (isset($result['trial_extended']) && $result['trial_extended']) {
                return redirect()->route('billing.index')
                    ->with('success', $result['message']);
            }

            if (isset($result['auto_activated']) && $result['auto_activated']) {
                return redirect()->route('billing.index')
                    ->with('success', 'Promotion activated! Your subscription is now active.');
            }

            return redirect()->route('billing.index')
                ->with('success', __('ui.subscription_requested'));
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Show a specific invoice for the company (tenant-isolated).
     */
    public function showInvoice(Invoice $invoice)
    {
        $company = $this->tenantManager->getTenant();

        if (!$company) {
            abort(403, 'Unauthorized access. No company context.');
        }

        if ($invoice->company_id !== $company->id) {
            abort(403);
        }

        if ($invoice->status !== 'paid') {
            abort(404, 'Invoice not available until paid.');
        }

        $invoice->load(['items', 'payment', 'company']);

        return view('modules.payments.company.invoice-show', compact('invoice'));
    }

    /**
     * Upload a bank receipt for a pending payment.
     */
    public function uploadReceipt(Request $request)
    {
        $request->validate([
            'receipt' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        $company = $this->tenantManager->getTenant();
        if (!$company) {
            abort(403, 'Unauthorized access. No company context.');
        }

        $pendingPayment = $company->payments()->where('status', 'pending')->latest()->first();

        if (!$pendingPayment) {
            return redirect()->back()->with('error', 'No pending payment found.');
        }

        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
            $pendingPayment->update(['receipt_path' => $receiptPath]);
        }

        return redirect()->back()->with('success', __('ui.receipt_uploaded') ?? 'Bank receipt uploaded successfully. Please wait for approval.');
    }
}
