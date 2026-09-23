<?php

namespace App\Modules\RBAC\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    /**
     * Display the permission matrix.
     */
    public function matrix()
    {
        $user = auth()->user();
        
        if ($user->is_system_admin) {
            // System Admin sees everything
            $roles = Role::with('permissions')->get();
            $permissions = Permission::all()->groupBy('module');
        } else {
            $companyId = $user->company_id;
            
            // Tenants see their own roles + Global roles
            $roles = Role::where('is_global', true)
                ->orWhereHas('companies', function ($q) use ($companyId) {
                    $q->where('companies.id', $companyId);
                })
                ->with('permissions')
                ->get();

            // Tenants see Global permissions + Permissions explicitly assigned to them
            $permissions = Permission::where('is_global', true)
                ->orWhereHas('companies', function ($q) use ($companyId) {
                    $q->where('companies.id', $companyId);
                })
                ->get()
                ->groupBy('module');
        }

        return view('modules.rbac.roles.matrix', compact('roles', 'permissions'));
    }

    /**
     * Update role-permission mapping.
     */
    public function updateMatrix(Request $request)
    {
        $user = auth()->user();
        $data = $request->input('matrix', []);
        
        // Define which roles the user is allowed to update
        $rolesQuery = Role::query();
        if (!$user->is_system_admin) {
            // Scoped update: Company admins can only update their company's roles.
            // Global roles can only be updated by the System Admin.
            $rolesQuery->where('is_global', false)
                ->whereHas('companies', function ($q) use ($user) {
                    $q->where('companies.id', $user->company_id);
                });
        }
        
        $roles = $rolesQuery->get();

        foreach ($roles as $role) {
            $permissionIds = $data[$role->id] ?? [];
            // For security, if not system admin, we could also filter $permissionIds 
            // to ensure they only sync permissions they are allowed to see.
            if (!$user->is_system_admin) {
                $allowedPermIds = Permission::where('is_global', true)
                    ->orWhereHas('companies', function ($q) use ($user) {
                        $q->where('companies.id', $user->company_id);
                    })
                    ->pluck('id')
                    ->toArray();
                
                $permissionIds = array_intersect($permissionIds, $allowedPermIds);
            }

            $role->permissions()->sync($permissionIds);
        }

        return redirect()->back()->with('success', 'Permission matrix updated successfully.');
    }
}
