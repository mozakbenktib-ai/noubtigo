<?php

namespace App\Services;

use App\Modules\Companies\Models\Company;

class FeatureFlag
{
    /**
     * Check if a feature is enabled for a company.
     *
     * Features gated behind Advanced Queue mode:
     * - advanced_queue, vip_management, drag_drop_reorder
     * - advanced_analytics, appointments, customer_profiles
     */
    public static function enabled(string $feature, ?Company $company = null): bool
    {
        $company = $company ?? app(TenantManager::class)->getTenant();
        if (!$company) return false;

        $isAdvanced = $company->isAdvancedQueue();

        return match ($feature) {
            'advanced_queue'      => $isAdvanced,
            'vip_management'      => $isAdvanced,
            'drag_drop_reorder'   => $isAdvanced,
            'priority_management' => $isAdvanced,
            'multi_room'          => $isAdvanced,
            'ticket_edit'         => $isAdvanced,
            'transfer'            => $isAdvanced,

            // Specific Plan-based features (Mapped to modular permissions)
            'advanced_analytics'  => $isAdvanced && $company->hasFeature('analytics.view'),
            'appointments'        => $isAdvanced && $company->hasFeature('appointments.view'),
            'customer_profiles'   => $isAdvanced, // Keep simple for now
            default               => true,
        };
    }

    /**
     * Check if a feature is disabled (convenience inverse).
     */
    public static function disabled(string $feature, ?Company $company = null): bool
    {
        return !static::enabled($feature, $company);
    }
}
