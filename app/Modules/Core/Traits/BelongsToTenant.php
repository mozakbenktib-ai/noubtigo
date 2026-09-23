<?php

namespace App\Modules\Core\Traits;

use App\Modules\Core\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    /**
     * The "booted" method of the model for this trait.
     *
     * @return void
     */
    protected static function bootedBelongsToTenant()
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model) {
            $tenantManager = app(\App\Services\TenantManager::class);
            if ($tenantManager->hasTenant()) {
                $model->company_id = $tenantManager->getTenantId();
            }
        });
    }

    /**
     * Get the company that owns the model.
     */
    public function company()
    {
        return $this->belongsTo('App\Modules\Companies\Models\Company', 'company_id');
    }
}
