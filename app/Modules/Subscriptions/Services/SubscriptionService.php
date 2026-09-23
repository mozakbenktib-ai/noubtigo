<?php

namespace App\Modules\Subscriptions\Services;

use App\Modules\Companies\Models\Company;
use App\Modules\Queue\Models\Ticket;
use App\Models\User;
use App\Modules\Rooms\Models\Room;
use App\Modules\Services\Models\Service;
use Carbon\Carbon;

class SubscriptionService
{
    /**
     * Check if a company can add more staff members.
     */
    public function canCreateStaff(Company $company): bool
    {
        $plan = $company->plan;
        if (!$plan) {
            return true; // No plan means no restriction by default, or handle as Free
        }

        $limit = $plan->getLimit('staff_limit');
        if ($limit === null || $limit === -1) {
            return true; // Unlimited
        }

        $currentStaffCount = User::where('company_id', $company->id)->count();

        return $currentStaffCount < $limit;
    }

    /**
     * Check if a company can create more tickets this month.
     */
    public function canCreateTicket(Company $company): bool
    {
        $plan = $company->plan;
        if (!$plan) {
            return true; // No plan means no restriction
        }

        $limit = $plan->getLimit('ticket_limit_monthly');
        if ($limit === null || $limit === -1) {
            return true; // Unlimited
        }

        $currentMonthTickets = Ticket::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        return $currentMonthTickets < $limit;
    }

    /**
     * Get the remaining tickets for the current month.
     */
    public function getRemainingTickets(Company $company): ?int
    {
        $plan = $company->plan;
        if (!$plan) return null;

        $limit = $plan->getLimit('ticket_limit_monthly');
        if ($limit === null || $limit === -1) return -1;

        $currentMonthTickets = Ticket::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        return max(0, $limit - $currentMonthTickets);
    }

    /**
     * Check if a company can add more rooms.
     */
    public function canCreateRoom(Company $company): bool
    {
        $plan = $company->plan;
        if (!$plan) {
            return true; // Default
        }

        $limit = $plan->getLimit('room_limit');
        if ($limit === null || $limit === -1) {
            return true; // Unlimited
        }

        $currentRoomCount = Room::where('company_id', $company->id)->count();

        return $currentRoomCount < $limit;
    }

    /**
     * Check if a company can add another service under its room allowance.
     */
    public function canCreateService(Company $company): bool
    {
        $plan = $company->plan;
        if (!$plan) {
            return true;
        }

        $limit = $plan->getLimit('room_limit');
        if ($limit === null || $limit === -1) {
            return true;
        }

        return Service::where('company_id', $company->id)->count() < $limit;
    }

    public function canCreateCustomer(Company $company): bool
    {
        $plan = $company->plan;
        if (!$plan) {
            return true; // Default
        }

        $limit = $plan->getLimit('customer_limit');
        if ($limit === null || $limit === -1) {
            return true; // Unlimited
        }

        $currentCustomerCount = \App\Modules\Customers\Models\Customer::where('company_id', $company->id)->count();

        return $currentCustomerCount < $limit;
    }

    /**
     * Check if a company can add more displays/screens.
     */
    public function canCreateDisplay(Company $company): bool
    {
        $plan = $company->plan;
        if (!$plan) {
            return true; // Default
        }

        $limit = $plan->getLimit('display_limit');
        if ($limit === null || $limit === -1) {
            return true; // Unlimited
        }

        $currentDisplayCount = \App\Modules\Displays\Models\DisplayDevice::where('company_id', $company->id)->count();

        return $currentDisplayCount < $limit;
    }

    /**
     * Get the remaining display quota for the company.
     */
    public function getRemainingDisplays(Company $company): ?int
    {
        $plan = $company->plan;
        if (!$plan) return null;

        $limit = $plan->getLimit('display_limit');
        if ($limit === null || $limit === -1) return -1;

        $currentDisplayCount = \App\Modules\Displays\Models\DisplayDevice::where('company_id', $company->id)->count();

        return max(0, $limit - $currentDisplayCount);
    }

    /**
     * Check if a company's plan allows a specific permission.
     */
    public function hasPermission(Company $company, string $permissionSlug): bool
    {
        $plan = $company->plan;
        if (!$plan) {
            return true; // If no plan, we assume all features (legacy/full access)
        }

        // We cache the plan permissions to avoid redundant queries in the same request
        static $planPermissions = [];
        
        if (!isset($planPermissions[$plan->id])) {
            $planPermissions[$plan->id] = $plan->permissions()->pluck('slug')->toArray();
        }

        return in_array($permissionSlug, $planPermissions[$plan->id]);
    }
}
