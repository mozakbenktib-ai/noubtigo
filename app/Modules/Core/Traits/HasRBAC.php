<?php

namespace App\Modules\Core\Traits;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRBAC
{
    /**
     * Users belongs to many roles.
     * Filtered by global roles OR roles assigned to the user's company via pivot.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->where(function($query) {
                $query->where('roles.is_global', true)
                      ->orWhereHas('companies', function($q) {
                          $q->where('companies.id', $this->company_id);
                      });
            });
    }

    /**
     * Users belongs to many permissions (Direct Assignment).
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $roleSlug): bool
    {
        if ($this->is_system_admin) return true;
        return $this->roles->contains('slug', $roleSlug);
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->is_system_admin) return true;

        // 1. Check if user has direct permission
        if ($this->permissions->contains('slug', $permissionSlug)) {
            return true;
        }

        // 2. Check if the user's role has the permission
        $hasRolePermission = false;
        foreach ($this->roles as $role) {
            if ($role->permissions->contains('slug', $permissionSlug)) {
                $hasRolePermission = true;
                break;
            }
        }

        if (!$hasRolePermission) return false;

        // 3. Check if the company's plan allows this permission
        if ($this->company_id) {
            $subscriptionService = app(\App\Modules\Subscriptions\Services\SubscriptionService::class);
            return $subscriptionService->hasPermission($this->company, $permissionSlug);
        }

        return false;
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole(string $roleSlug): void
    {
        $role = Role::where('slug', $roleSlug)
            ->where(function($q) {
                $q->where('is_global', true)
                  ->orWhereHas('companies', function($qc) {
                      $qc->where('companies.id', $this->company_id);
                  });
            })->first();

        if ($role) {
            $this->roles()->syncWithoutDetaching([$role->id]);
        }
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole(string $roleSlug): void
    {
        $role = Role::where('slug', $roleSlug)->first();
        if ($role) {
            $this->roles()->detach($role->id);
        }
    }
}
