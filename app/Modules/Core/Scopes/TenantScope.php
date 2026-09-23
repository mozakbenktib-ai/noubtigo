<?php

namespace App\Modules\Core\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $tenantManager = app(\App\Services\TenantManager::class);

        // If a tenant is identified (via subdomain, auth, etc.), filter by it
        // Only apply if the tenant is set and we're not in a super-admin context (if applicable)
        if ($tenantManager->hasTenant()) {
            // Check if user is system admin - bypass scope
            if (auth()->check() && auth()->user()->is_system_admin) {
                return;
            }

            $builder->where($model->getTable() . '.company_id', $tenantManager->getTenantId());
        }
    }
}
