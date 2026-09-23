<?php

namespace App\Modules\Payments\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payments\Models\Payment;
use App\Modules\Payments\Models\Subscription;
use App\Modules\Payments\Models\Invoice;
use App\Modules\Payments\Services\PaymentLifecycleService;
use Illuminate\Http\Request;

class AdminPaymentController extends Controller
{
    public function __construct(protected PaymentLifecycleService $lifecycle) {}

    /**
     * List all payments across all tenants (System Admin only).
     */
    public function index(Request $request)
    {
        $paymentsQuery = Payment::with(['company', 'subscription.plan'])
            ->latest();

        if ($request->filled('status')) {
            $paymentsQuery->where('status', $request->status);
        }
        if ($request->filled('method')) {
            $paymentsQuery->where('payment_method', $request->method);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $paymentsQuery->whereHas('company', fn($q) => $q->where('name', 'like', "%$s%"));
        }

        $payments = $paymentsQuery->paginate(20)->withQueryString();
        $stats = [
            'total'      => Payment::count(),
            'pending'    => Payment::where('status', 'pending')->count(),
            'paid'       => Payment::where('status', 'paid')->count(),
            'failed'     => Payment::where('status', 'failed')->count(),
        ];

        return view('modules.payments.admin.payments', compact('payments', 'stats'));
    }

    /**
     * Approve a manual payment and activate the subscription.
     */
    public function approve(Payment $payment)
    {
        try {
            $this->lifecycle->approveManualPayment($payment);
            return redirect()->back()->with('success', __('ui.payment_approved'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject a payment.
     */
    public function reject(Request $request, Payment $payment)
    {
        if ($payment->status === 'paid') {
            return redirect()->back()->with('error', __('ui.payment_already_paid'));
        }

        $payment->update(['status' => 'failed']);

        // Also set the subscription back to cancelled
        if ($payment->subscription) {
            $payment->subscription->update(['status' => 'cancelled']);
        }

        return redirect()->back()->with('success', __('ui.payment_rejected'));
    }

    /**
     * Refund a payment.
     */
    public function refund(Payment $payment)
    {
        if ($payment->status !== 'paid') {
            return redirect()->back()->with('error', __('ui.payment_not_paid'));
        }

        $payment->update(['status' => 'refunded']);

        if ($payment->subscription) {
            $payment->subscription->update(['status' => 'cancelled']);
        }

        return redirect()->back()->with('success', __('ui.payment_refunded'));
    }

    /**
     * Download the uploaded bank receipt for a payment.
     */
    public function downloadReceipt(Payment $payment)
    {
        if (!$payment->receipt_path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($payment->receipt_path)) {
            return redirect()->back()->with('error', __('ui.receipt_not_found', ['default' => 'Receipt not found.']));
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download($payment->receipt_path);
    }
}
