<?php

namespace App\Modules\RBAC\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Modules\Companies\Models\Company;
use App\Modules\Rooms\Models\Room;
use App\Modules\Services\Models\Service;
use App\Modules\Queue\Models\Ticket;
use App\Modules\Subscriptions\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MasterController extends Controller
{
    /**
     * Display the System Dashboard.
     */
    public function index(Request $request)
    {
        // Search and Filters for Roles
        $rolesQuery = Role::with(['permissions', 'companies'])->withCount('users');
        if ($request->filled('search_roles')) {
            $s = $request->search_roles;
            $rolesQuery->where(function($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('description', 'like', "%$s%");
            });
        }
        if ($request->filled('role_scope')) {
            $rolesQuery->where('is_global', $request->role_scope);
        }
        $roles = $rolesQuery->paginate(10, ['*'], 'roles_page')->withQueryString();

        // Search and Filters for Permissions
        $permsQuery = Permission::with(['roles', 'companies']);
        if ($request->filled('search_perms')) {
            $s = $request->search_perms;
            $permsQuery->where(function($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('slug', 'like', "%$s%")
                  ->orWhere('module', 'like', "%$s%");
            });
        }
        if ($request->filled('perm_module')) {
            $permsQuery->where('module', $request->perm_module);
        }
        if ($request->filled('perm_scope')) {
            $permsQuery->where('is_global', $request->perm_scope);
        }
        $permissions = $permsQuery->paginate(15, ['*'], 'perms_page')->withQueryString();

        $companies = Company::with(['roles', 'plan'])
            ->withCount(['users', 'rooms', 'services'])
            ->get();
        $plans = Plan::with('visibleCompanies')->get();

        $rooms = Room::with('company')->get();
        $services = Service::with('company')->get();
        
        $recentTickets = Ticket::withoutGlobalScopes()
            ->with(['company', 'service'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $stats = [
            'total_users' => User::count(),
            'total_companies' => $companies->count(),
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'global_roles' => Role::where('is_global', true)->count(),
            'global_permissions' => Permission::where('is_global', true)->count(),
            'total_rooms' => Room::count(),
            'total_services' => Service::count(),
            'total_tickets' => Ticket::withoutGlobalScopes()->count(),
            'active_tenants' => $companies->where('users_count', '>', 0)->count(),
        ];

        $modules = Permission::distinct()->pluck('module')->filter()->values();
        $allPermissions = Permission::all()->groupBy('module');
        
        return view('modules.rbac.master.dashboard', compact(
            'stats', 'permissions', 'roles', 'companies', 'rooms', 
            'services', 'recentTickets', 'plans', 'modules', 'allPermissions'
        ));
    }

    /**
     * Store a new permission.
     */
    public function storePermission(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'is_global' => 'required|boolean',
            'company_ids' => 'required_if:is_global,0|array',
            'company_ids.*' => 'exists:companies,id',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id',
            'description' => 'nullable|string',
            'module' => 'nullable|string|max:255',
        ]);

        $slug = $request->slug ?: Str::slug($request->name);

        if (Permission::where('slug', $slug)->exists()) {
             $slug = $slug . '-' . Str::random(4);
        }

        $permission = Permission::create([
            'name' => $request->name,
            'slug' => $slug,
            'module' => $request->module,
            'is_global' => $request->is_global,
            'description' => $request->description,
        ]);

        if (!$request->is_global && $request->has('company_ids')) {
            $permission->companies()->sync($request->company_ids);
        }

        if ($request->has('role_ids')) {
            $permission->roles()->sync($request->role_ids);
        }

        return redirect()->back()->with('success', __('ui.permission_created_success'));
    }

    /**
     * Update an existing permission.
     */
    public function updatePermission(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_global' => 'required|boolean',
            'company_ids' => 'required_if:is_global,0|array',
            'company_ids.*' => 'exists:companies,id',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id',
            'description' => 'nullable|string',
            'module' => 'nullable|string|max:255',
        ]);

        $permission->update([
            'name' => $request->name,
            'module' => $request->module,
            'is_global' => $request->is_global,
            'description' => $request->description,
        ]);

        if ($request->is_global) {
            $permission->companies()->detach();
        } else {
            $permission->companies()->sync($request->company_ids ?? []);
        }

        if ($request->has('role_ids')) {
            $permission->roles()->sync($request->role_ids);
        }

        return redirect()->back()->with('success', __('ui.permission_updated_success'));
    }

    /**
     * Update an existing role.
     */
    public function updateRole(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_global' => 'required|boolean',
            'company_ids' => 'required_if:is_global,0|array',
            'company_ids.*' => 'exists:companies,id',
            'description' => 'nullable|string',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $request->name,
            'is_global' => $request->is_global,
            'description' => $request->description,
        ]);

        if ($request->is_global) {
            $role->companies()->detach();
        } else {
            $role->companies()->sync($request->company_ids ?? []);
        }

        if ($request->has('permission_ids')) {
            $role->permissions()->sync($request->permission_ids);
        }

        return redirect()->back()->with('success', __('ui.role_updated_success'));
    }

    /**
     * Create or update a role.
     */
    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'is_global' => 'required|boolean',
            'company_ids' => 'required_if:is_global,0|array',
            'company_ids.*' => 'exists:companies,id',
            'description' => 'nullable|string',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $slug = $request->slug ?: Str::slug($request->name);

        // Ensure unique slug
        if (Role::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . Str::random(4);
        }

        $role = Role::create([
            'name' => $request->name,
            'slug' => $slug,
            'is_global' => $request->is_global,
            'description' => $request->description,
        ]);

        if (!$request->is_global && $request->has('company_ids')) {
            $role->companies()->sync($request->company_ids);
        }

        if ($request->has('permission_ids')) {
            $role->permissions()->sync($request->permission_ids);
        }

        return redirect()->back()->with('success', __('ui.role_created_success'));
    }

    /**
     * Delete a role.
     */
    public function destroyRole(Role $role)
    {
        // Don't allow deleting system-critical roles
        $protectedRoles = ['super_admin', 'company_admin', 'staff'];
        if (in_array($role->slug, $protectedRoles)) {
            return redirect()->back()->with('error', 'Cannot delete system-protected role.');
        }

        $role->delete();
        return redirect()->back()->with('success', __('ui.role_deleted_success'));
    }

    /**
     * Clone an existing role.
     */
    public function cloneRole(Role $role)
    {
        $newRole = $role->replicate();
        $newRole->name = $role->name . ' (Copy)';
        $newRole->slug = $role->slug . '-copy-' . Str::random(4);
        $newRole->save();

        // Copy permissions
        $newRole->permissions()->sync($role->permissions->pluck('id'));
        
        // Copy company scopes
        $newRole->companies()->sync($role->companies->pluck('id'));

        return redirect()->back()->with('success', 'Role cloned successfully.');
    }

    /**
     * Sync permissions to a specific role.
     */
    public function syncRolePermissions(Request $request, Role $role)
    {
        $request->validate([
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->sync($request->permission_ids ?: []);

        return redirect()->back()->with('success', __('ui.permissions_updated_success'));
    }

    /**
     * List all users across all tenants.
     */
    public function users()
    {
        $users = User::with(['company', 'roles', 'permissions'])->paginate(20);
        $allPermissions = Permission::all()->groupBy('module');
        return view('modules.rbac.master.users', compact('users', 'allPermissions'));
    }

    /**
     * Update direct permissions for a user.
     */
    public function updateUserPermissions(Request $request, User $user)
    {
        $request->validate([
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $user->permissions()->sync($request->permission_ids ?: []);

        return redirect()->back()->with('success', 'User permissions updated successfully.');
    }

    /**
     * Store a new plan.
     */
    public function storePlan(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'annual_price' => 'nullable|numeric|min:0',
            'staff_limit' => 'required|integer',
            'room_limit' => 'required|integer',
            'display_limit' => 'required|integer',
            'ticket_limit_monthly' => 'required|integer',
            'customer_limit' => 'required|integer',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
            'visibility_scope' => 'required|in:all,selected',
            'company_ids' => 'required_if:visibility_scope,selected|array',
            'company_ids.*' => 'exists:companies,id',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $slug = $request->slug ?: Str::slug($request->name);
        if (Plan::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . Str::random(4);
        }

        $plan = Plan::create([
            'name' => $request->name,
            'slug' => $slug,
            'price' => $request->price,
            'annual_price' => $request->annual_price ?? 0,
            'description' => $request->description,
            'limits' => [
                'staff_limit' => (int) $request->staff_limit,
                'room_limit' => (int) $request->room_limit,
                'display_limit' => (int) $request->display_limit,
                'ticket_limit_monthly' => (int) $request->ticket_limit_monthly,
                'customer_limit' => (int) $request->customer_limit,
            ],
            'is_active' => $request->boolean('is_active'),
            'visibility_scope' => $request->visibility_scope,
        ]);

        if ($request->has('permission_ids')) {
            $plan->permissions()->sync($request->permission_ids);
        }

        $plan->visibleCompanies()->sync(
            $request->visibility_scope === 'selected' ? ($request->company_ids ?? []) : []
        );

        return redirect()->back()->with('success', __('ui.plan_created_success'));
    }

    /**
     * Update an existing plan.
     */
    public function updatePlan(Request $request, Plan $plan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'annual_price' => 'nullable|numeric|min:0',
            'staff_limit' => 'required|integer',
            'room_limit' => 'required|integer',
            'display_limit' => 'required|integer',
            'ticket_limit_monthly' => 'required|integer',
            'customer_limit' => 'required|integer',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
            'visibility_scope' => 'required|in:all,selected',
            'company_ids' => 'required_if:visibility_scope,selected|array',
            'company_ids.*' => 'exists:companies,id',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $plan->update([
            'name' => $request->name,
            'price' => $request->price,
            'annual_price' => $request->annual_price ?? $plan->annual_price,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
            'visibility_scope' => $request->visibility_scope,
            'limits' => [
                'staff_limit' => (int) $request->staff_limit,
                'room_limit' => (int) $request->room_limit,
                'display_limit' => (int) $request->display_limit,
                'ticket_limit_monthly' => (int) $request->ticket_limit_monthly,
                'customer_limit' => (int) $request->customer_limit,
            ],
        ]);

        if ($request->has('permission_ids')) {
            $plan->permissions()->sync($request->permission_ids);
        }

        $plan->visibleCompanies()->sync(
            $request->visibility_scope === 'selected' ? ($request->company_ids ?? []) : []
        );

        return redirect()->back()->with('success', __('ui.plan_updated_success'));
    }

    /**
     * Delete a plan.
     */
    public function destroyPlan(Plan $plan)
    {
        if ($plan->companies()->exists()) {
            return redirect()->back()->with('error', __('ui.cannot_delete_plan_in_use'));
        }

        $plan->delete();
        return redirect()->back()->with('success', __('ui.plan_deleted_success'));
    }

    /**
     * Update specific company (System Admin only).
     */
    public function updateCompany(Request $request, Company $company)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'plan_id' => 'nullable|exists:plans,id',
            'is_active' => 'required|boolean',
        ]);

        $company->update([
            'name' => $request->name,
            'plan_id' => $request->plan_id,
            'is_active' => $request->is_active,
        ]);

        return redirect()->back()->with('success', __('ui.company_updated_success'));
    }

    /**
     * Store bulk CRUD permissions for a module.
     */
    public function storeModulePermissions(Request $request)
    {
        $request->validate([
            'module_name' => 'required|string|max:255',
            'is_global' => 'required|boolean',
            'company_ids' => 'required_if:is_global,0|array',
            'company_ids.*' => 'exists:companies,id',
        ]);

        $module = Str::slug($request->module_name);
        $actions = ['view', 'create', 'edit', 'delete'];

        foreach ($actions as $action) {
            $slug = "$module.$action";
            $name = Str::title($action) . ' ' . Str::title(str_replace('-', ' ', $module));
            
            // Use updateOrCreate to handle existing or new
            $permission = Permission::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'module' => $module,
                    'is_global' => $request->is_global,
                    'description' => "Allow user to $action $module",
                ]
            );

            if (!$request->is_global && $request->has('company_ids')) {
                $permission->companies()->sync($request->company_ids);
            } else {
                $permission->companies()->detach();
            }
        }

        return redirect()->back()->with('success', __('ui.module_permissions_created', ['module' => $module]));
    }

    /**
     * Get role permissions for AJAX.
     */
    public function getRolePermissions(Role $role)
    {
        return response()->json([
            'permission_ids' => $role->permissions->pluck('id')
        ]);
    }
}
