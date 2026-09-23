<?php

namespace App\Modules\RBAC\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Modules\Subscriptions\Services\SubscriptionService;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    /**
     * Display a listing of users with roles.
     */
    protected $activityLogService;

    public function __construct(\App\Modules\Queue\Services\ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Display a listing of users with roles.
     */
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        
        $filters = $request->only(['search', 'role', 'status', 'room', 'service']);
        
        $usersQuery = \App\Models\User::with(['roles', 'assignedRoom', 'assignedService'])
            ->where('users.company_id', $companyId)
            ->filter($filters);

        // Sorting
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortOrder = $request->get('sort_order', 'desc') === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['first_name', 'last_name', 'email', 'is_active', 'updated_at'];
        if ($sortBy === 'full_name' || $sortBy === 'name') {
            $sortBy = 'last_name';
        }

        if (in_array($sortBy, $allowedSorts)) {
            if ($sortBy === 'last_name') {
                $usersQuery->orderBy('users.last_name', $sortOrder)
                           ->orderBy('users.first_name', $sortOrder);
            } else {
                $usersQuery->orderBy('users.' . $sortBy, $sortOrder);
            }
        } else {
            $usersQuery->orderBy('users.updated_at', 'desc');
        }

        $users = $usersQuery->paginate(10)->withQueryString();
        
        $roles = \App\Models\Role::where('is_global', true)
            ->orWhereHas('companies', function($query) use ($companyId) {
                $query->where('companies.id', $companyId);
            })
            ->get();

        $rooms = \App\Modules\Rooms\Models\Room::where('company_id', $companyId)->get();
        $services = \App\Modules\Services\Models\Service::where('company_id', $companyId)->get();

        // Quick Stats
        $stats = [
            'total' => \App\Models\User::where('company_id', $companyId)->count(),
            'active' => \App\Models\User::where('company_id', $companyId)->where('is_active', true)->count(),
            'inactive' => \App\Models\User::where('company_id', $companyId)->where('is_active', false)->count(),
            'admins' => \App\Models\User::where('company_id', $companyId)
                ->whereHas('roles', function($q) { $q->where('slug', 'admin')->orWhere('slug', 'super-admin'); })
                ->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('modules.rbac.users.partials.users-table', compact('users'))->render(),
                'stats' => $stats
            ]);
        }
        
        return view('modules.rbac.users.index', compact('users', 'roles', 'rooms', 'services', 'stats'));
    }

    /**
     * Store a new staff member.
     */
    public function store(Request $request)
    {
        $currentUser = auth()->user();
        $companyId = $currentUser->company_id;
        $company = \App\Modules\Companies\Models\Company::find($companyId);

        if (!$company) {
            return redirect()->back()->with('error', 'Your account is not linked to a company. Please contact an administrator.');
        }

        // Limit Enforcement
        $subscriptionService = app(SubscriptionService::class);
        if (!$subscriptionService->canCreateStaff($company)) {
            return redirect()->back()->with('error', "Your plan's staff limit has been reached. Please upgrade to add more staff.");
        }

        // Check if selected role is admin/company-admin to make email required
        $adminSlugs = ['admin', 'super-admin', 'company-admin'];
        $isAdminRole = false;
        $isCompanyAdminRole = false;
        if ($request->has('roles')) {
            $selectedRoles = Role::whereIn('id', $request->roles)->pluck('slug');
            $isAdminRole = $selectedRoles->intersect($adminSlugs)->isNotEmpty();
            $isCompanyAdminRole = $selectedRoles->contains('company-admin');
        }

        $emailRule = $isAdminRole ? 'required|email|unique:users,email' : 'nullable|email|unique:users,email';
        $usernameRule = $isCompanyAdminRole ? 'nullable|string|max:255|unique:users,username' : 'required|string|max:255|unique:users,username';

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => $usernameRule,
            'email' => $emailRule,
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'password' => 'required|string|min:8',
            'roles' => 'array',
            'roles.*' => [
                'exists:roles,id',
                function ($attribute, $value, $fail) use ($companyId) {
                    $role = Role::find($value);
                    if ($role && !$role->is_global && !$role->companies()->where('companies.id', $companyId)->exists()) {
                        $fail("The selected role is invalid for this company.");
                    }
                },
            ],
        ], [
            'email.required' => 'Email is required for Admin / Company Admin roles.',
        ]);

        $user = \App\Models\User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'requires_password_change' => true,
            'company_id' => $companyId,
            'assigned_room_id' => $request->assigned_room_id,
            'assigned_service_id' => $request->assigned_service_id,
        ]);

        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);
        }

        // Audit Log
        $this->activityLogService->logUserEvent($user, 'created', "Staff member {$user->full_name} created.");

        return redirect()->back()->with('success', "Staff member {$user->full_name} created successfully.");
    }

    /**
     * Display staff details.
     */
    public function show(\App\Models\User $user)
    {
        $companyId = auth()->user()->company_id;
        if ($user->company_id !== $companyId) {
            abort(403);
        }

        $user->load('roles');
        $timeline = \App\Modules\Queue\Models\ActivityLog::where('model_type', 'User')
            ->where('model_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $roles = \App\Models\Role::where('is_global', true)
            ->orWhereHas('companies', function($query) use ($companyId) {
                $query->where('companies.id', $companyId);
            })
            ->get();

        return view('modules.rbac.users.show', compact('user', 'timeline', 'roles'));
    }

    /**
     * Update user details.
     */
    public function update(Request $request, \App\Models\User $user)
    {
        $adminSlugs = ['admin', 'super-admin', 'company-admin'];
        $isAdmin = $user->roles->pluck('slug')->intersect($adminSlugs)->isNotEmpty();
        $isCompanyAdmin = $user->roles->pluck('slug')->contains('company-admin');
        $emailRule = $isAdmin ? 'required|email|unique:users,email,' . $user->id : 'nullable|email|unique:users,email,' . $user->id;
        $usernameRule = $isCompanyAdmin ? 'nullable|string|max:255|unique:users,username,' . $user->id : 'required|string|max:255|unique:users,username,' . $user->id;

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => $usernameRule,
            'email' => $emailRule,
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $user->id,
        ], [
            'email.required' => 'Email is required for Admin / Company Admin roles.',
        ]);

        $before = $user->only('first_name', 'last_name', 'username', 'email', 'phone', 'assigned_room_id', 'assigned_service_id');
        $user->update($request->only('first_name', 'last_name', 'username', 'email', 'phone', 'assigned_room_id', 'assigned_service_id'));
        $after = $user->only('first_name', 'last_name', 'username', 'email', 'phone', 'assigned_room_id', 'assigned_service_id');

        // Audit Log
        $this->activityLogService->logUserEvent($user, 'updated', "User profile updated.", [
            'before' => $before,
            'after' => $after
        ]);

        return redirect()->back()->with('success', "User {$user->full_name} updated successfully.");
    }

    /**
     * Regenerate user password (Admin action)
     */
    public function regeneratePassword(Request $request, \App\Models\User $user)
    {
        $companyId = auth()->user()->company_id;
        if ($user->company_id !== $companyId) {
            abort(403);
        }

        // Generate a random 8 character string for the temporary password
        $newPassword = \Illuminate\Support\Str::random(8);

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($newPassword),
            'requires_password_change' => true,
        ]);

        $this->activityLogService->logUserEvent($user, 'updated', "Password regenerated by Admin.");

        return redirect()->back()->with('success', "Password regenerated for {$user->full_name}. The new temporary password is: <strong>{$newPassword}</strong>");
    }

    /**
     * Remove the user.
     */
    public function destroy(\App\Models\User $user)
    {
        if (auth()->id() === $user->id) {
            return redirect()->back()->with('error', "You cannot delete your own account.");
        }

        // Prevent deleting the first admin of the company
        if ($user->roles->pluck('slug')->intersect(['admin', 'super-admin', 'company-admin'])->isNotEmpty()) {
            $firstAdmin = \App\Models\User::where('company_id', $user->company_id)
                ->whereHas('roles', function($q) {
                    $q->whereIn('slug', ['admin', 'super-admin', 'company-admin']);
                })
                ->orderBy('id', 'asc')
                ->first();

            if ($firstAdmin && $firstAdmin->id === $user->id) {
                return redirect()->back()->with('error', "You cannot delete the primary admin of the company.");
            }
        }

        $userName = $user->full_name;
        $userId = $user->id;
        $user->delete();

        // Audit Log
        $this->activityLogService->log('User', $userId, 'deleted', "Staff member {$userName} deleted.");

        return redirect()->back()->with('success', "User deleted successfully.");
    }

    /**
     * Update user roles.
     */
    public function updateRoles(Request $request, \App\Models\User $user)
    {
        $companyId = auth()->user()->company_id;
        $request->validate([
            'roles' => 'array',
            'roles.*' => [
                'exists:roles,id',
                function ($attribute, $value, $fail) use ($companyId) {
                    $role = Role::find($value);
                    if ($role && !$role->is_global && !$role->companies()->where('companies.id', $companyId)->exists()) {
                        $fail("The selected role is invalid for this company.");
                    }
                },
            ],
        ]);

        // Block admin/company-admin role assignment if user has no email
        $adminSlugs = ['admin', 'super-admin', 'company-admin'];
        if ($request->has('roles')) {
            $selectedRoles = Role::whereIn('id', $request->roles)->pluck('slug');
            if ($selectedRoles->intersect($adminSlugs)->isNotEmpty() && empty($user->email)) {
                return redirect()->back()->with('error', "Cannot assign Admin / Company Admin role to {$user->full_name}. The user must have an email address first. Please update their profile to add an email.");
            }
        }

        $oldRoles = $user->roles->pluck('name')->toArray();
        $user->roles()->sync($request->roles);
        $user->load('roles'); // Refresh roles
        $newRoles = $user->roles->pluck('name')->toArray();

        // Audit Log
        $this->activityLogService->logUserEvent($user, 'updated', "Roles updated for {$user->full_name}.", [
            'before' => ['roles' => $oldRoles],
            'after'  => ['roles' => $newRoles]
        ]);

        return redirect()->back()->with('success', "Roles updated for {$user->full_name}.");
    }
    public function toggleStatus(Request $request, \App\Models\User $user)
    {
        $companyId = auth()->user()->company_id;
        if ($user->company_id !== $companyId) {
            abort(403);
        }

        // Prevent deactivating the first admin of the company
        if (!$request->is_active && $user->roles->pluck('slug')->intersect(['admin', 'super-admin', 'company-admin'])->isNotEmpty()) {
            $firstAdmin = \App\Models\User::where('company_id', $user->company_id)
                ->whereHas('roles', function($q) {
                    $q->whereIn('slug', ['admin', 'super-admin', 'company-admin']);
                })
                ->orderBy('id', 'asc')
                ->first();

            if ($firstAdmin && $firstAdmin->id === $user->id) {
                session()->flash('error', 'You cannot deactivate the primary admin of the company.');
                return response()->json([
                    'success' => false,
                    'reload' => true
                ]);
            }
        }

        $user->update(['is_active' => $request->is_active]);

        // Audit Log
        $status = $request->is_active ? 'activated' : 'deactivated';
        $this->activityLogService->logUserEvent($user, 'updated', "User {$user->full_name} {$status}.");

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.'
        ]);
    }
}
