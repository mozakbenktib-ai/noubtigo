<?php

namespace App\Modules\Payments\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payments\Models\Subscription;
use App\Modules\Payments\Models\Invoice;
use Illuminate\Http\Request;
use App\Services\TenantManager;

class AdminSubscriptionController extends Controller
{
    /**
     * List all subscriptions across all tenants (System Admin only).
     */
    public function index(Request $request)
    {
        $query = Subscription::with(['company', 'plan'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $subscriptions = $query->paginate(20)->withQueryString();

        return view('modules.payments.admin.subscriptions', compact('subscriptions'));
    }

    /**
     * Activate a subscription manually (System Admin override).
     */
    public function activate(Subscription $subscription)
    {
        $subscription->update(['status' => 'active']);
        $subscription->company->update(['plan_id' => $subscription->plan_id]);

        return redirect()->back()->with('success', __('ui.subscription_activated'));
    }

    /**
     * Suspend a subscription (System Admin).
     */
    public function suspend(Subscription $subscription)
    {
        $subscription->update(['status' => 'suspended']);

        return redirect()->back()->with('success', __('ui.subscription_suspended'));
    }
}
