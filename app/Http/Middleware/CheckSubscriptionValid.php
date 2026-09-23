<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\TenantManager;

class CheckSubscriptionValid
{
    public function __construct(protected TenantManager $tenantManager) {}

    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // 1. Bypass for system admins (they manage everything)
        if ($user && $user->is_system_admin) {
            return $next($request);
        }

        // 2. Only enforce if there is a company context
        $company = $this->tenantManager->getTenant();
        if (!$company) {
            return $next($request);
        }

        // 3. Bypass routes that are always allowed (billing, logout, settings)
        // We use string matching to handle all sub-routes if needed, or check route names.
        $allowedRoutes = [
            'billing.index',
            'billing.subscribe',
            'billing.invoice.show',
            'logout',
            'settings.company',
            'settings.company.update'
        ];

        if ($request->route() && in_array($request->route()->getName(), $allowedRoutes)) {
            return $next($request);
        }

        // 4. Check if subscription exists and is suspended/cancelled
        $latestSubscription = $company->subscriptions()->latest()->first();
        
        if ($latestSubscription) {
            // If there is any active subscription that is still valid (not expired or within grace period), allow access
            $hasActiveValidSubscription = $company->subscriptions()
                ->where('status', 'active')
                ->where(function ($query) {
                    $query->whereNull('ends_at')
                          ->orWhere('ends_at', '>', now()->subDays(3));
                })
                ->exists();

            if ($hasActiveValidSubscription) {
                return $next($request);
            }

            // Block if explicitly suspended, cancelled, or pending
            if ($latestSubscription->status === 'pending') {
                return redirect()->route('billing.index')->with('error', __('ui.subscription_pending_alert') ?? 'Your subscription is pending payment approval. Please wait for confirmation.');
            }
            if ($latestSubscription->status === 'suspended') {
                return redirect()->route('billing.index')->with('error', __('ui.subscription_suspended_alert') ?? 'Your subscription has been suspended. Please contact support.');
            }
            if ($latestSubscription->status === 'cancelled') {
                return redirect()->route('billing.index')->with('error', __('ui.subscription_cancelled_alert') ?? 'Your subscription has been cancelled. Please renew to continue.');
            }

            // Block if expired beyond grace period (3 days)
            if ($latestSubscription->ends_at && $latestSubscription->ends_at->isPast()) {
                $daysExpired = $latestSubscription->ends_at->startOfDay()->diffInDays(now()->startOfDay());
                if ($daysExpired > 3) {
                    return redirect()->route('billing.index')->with('error', __('ui.subscription_blocked_alert') ?? 'Your subscription expired more than 3 days ago. Please renew to continue using the application.');
                }
            }
        } else {
            // Fallback for legacy companies without a subscription record
            if (is_null($company->plan_id)) {
                return redirect()->route('billing.index')->with('error', __('ui.subscription_required') ?? 'You must have an active subscription to access the dashboard.');
            }
        }

        return $next($request);
    }
}
