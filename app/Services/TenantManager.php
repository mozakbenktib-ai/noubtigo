<?php

namespace App\Services;

use App\Modules\Companies\Models\Company;

class TenantManager
{
    /**
     * The current tenant company.
     */
    protected ?Company $tenant = null;

    /**
     * Set the current tenant.
     */
    public function setTenant(?Company $tenant): void
    {
        $this->tenant = $tenant;
    }

    /**
     * Get the current tenant.
     */
    public function getTenant(): ?Company
    {
        return $this->tenant;
    }

    /**
     * Get the current tenant ID.
     */
    public function getTenantId(): ?int
    {
        return $this->tenant?->id;
    }

    /**
     * Check if a tenant is set.
     */
    public function hasTenant(): bool
    {
        return !is_null($this->tenant);
    }
}
