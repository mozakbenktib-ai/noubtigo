<?php

namespace App\Modules\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'annual_price',
        'limits',
        'is_active',
        'visibility_scope',
    ];

    protected $casts = [
        'limits' => 'array',
        'is_active' => 'boolean',
        'visibility_scope' => 'string',
        'price' => 'decimal:2',
        'annual_price' => 'decimal:2',
        'id' => 'integer',
    ];

    /**
     * Get the companies assigned to this plan.
     */
    public function companies()
    {
        return $this->hasMany('App\Modules\Companies\Models\Company');
    }

    /**
     * Companies that can see this plan when its scope is selected companies.
     */
    public function visibleCompanies()
    {
        return $this->belongsToMany(
            'App\Modules\Companies\Models\Company',
            'plan_company_visibility'
        );
    }

    public function isVisibleToCompany(int $companyId): bool
    {
        return $this->is_active && (
            $this->visibility_scope === 'all'
            || $this->visibleCompanies()->whereKey($companyId)->exists()
        );
    }

    /**
     * Get the subscriptions for this plan.
     */
    public function subscriptions()
    {
        return $this->hasMany(\App\Modules\Payments\Models\Subscription::class);
    }

    /**
     * Get the permissions allowed by this plan.
     */
    public function permissions()
    {
        return $this->belongsToMany('App\Models\Permission', 'plan_permission');
    }

    /**
     * Helper to get a specific limit value.
     */
    public function getLimit(string $key, $default = null)
    {
        return $this->limits[$key] ?? $default;
    }

    /**
     * Check if the plan has a specific feature permission.
     */
    public function hasFeature(string $slug): bool
    {
        return $this->permissions()->where('slug', $slug)->exists();
    }
}
