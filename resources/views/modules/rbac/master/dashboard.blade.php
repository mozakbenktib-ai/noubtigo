@extends('layouts.dashboard')

@section('title', __('ui.master_control_center') . ' | Noubtigo')
@section('header_title', __('ui.master_control_center'))
@section('header_subtitle', __('ui.rbac_management_subtitle'))

@push('styles')
    <style>
        :root {
            --noubtigo-green: #22c55e;
            --noubtigo-cyan: #06b6d4;
            --noubtigo-gradient: linear-gradient(135deg, var(--noubtigo-green), var(--noubtigo-cyan));
            --noubtigo-soft-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        }

        .bg-noubtigo-gradient { background: var(--noubtigo-gradient) !important; }
        .text-noubtigo-gradient {
            background: var(--noubtigo-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .card-premium {
            border: none !important;
            border-radius: 1.25rem !important; /* 20px for extra premium feel */
            box-shadow: var(--noubtigo-soft-shadow) !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
        }

        .nav-pills-premium .nav-link {
            color: #64748b;
            font-weight: 600;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }

        .nav-pills-premium .nav-link.active {
            background: white !important;
            color: var(--noubtigo-green) !important;
            border-color: #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .table-premium thead th {
            background-color: #f8fafc;
            color: #475569;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 1.25rem 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .table-premium tbody td {
            padding: 1.25rem 1rem;
            vertical-align: middle;
            color: #1e293b;
            border-bottom: 1px solid #f1f5f9;
        }

        .btn-premium-primary {
            background: var(--noubtigo-gradient) !important;
            border: none !important;
            color: white !important;
            font-weight: 600;
            border-radius: 12px;
            padding: 0.625rem 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(34, 197, 94, 0.2);
            transition: all 0.2s ease;
        }

        .btn-premium-primary:hover {
            box-shadow: 0 10px 15px -3px rgba(34, 197, 94, 0.3);
            transform: scale(1.02);
        }

        .badge-premium {
            padding: 0.5em 1em;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .permission-module-card {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 1.5rem;
            background: #fff;
            height: 100%;
        }

        .switch-premium {
            width: 3rem;
            height: 1.5rem;
        }

        .badge-global {
            background-color: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #dbeafe;
            padding: 0.4rem 0.8rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.7rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .badge-scoped {
            background-color: #fffbeb;
            color: #b45309;
            border: 1px solid #fef3c7;
            padding: 0.4rem 0.8rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.7rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        /* RTL adjustments */
        [dir="rtl"] .rounded-start-3 { border-top-right-radius: 1rem !important; border-bottom-right-radius: 1rem !important; border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important; }
        [dir="rtl"] .rounded-end-3 { border-top-left-radius: 1rem !important; border-bottom-left-radius: 1rem !important; border-top-right-radius: 0 !important; border-bottom-right-radius: 0 !important; }
    </style>
@endpush

@section('content')
<!-- Stats Overview -->
<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="card card-premium border-0 h-100 bg-white">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="bg-noubtigo-gradient rounded-3 p-3 text-white shadow-sm">
                        <i class="bi bi-shield-lock fs-3"></i>
                    </div>
                    <span class="badge bg-light text-muted rounded-pill px-3 py-2">{{ __('ui.active_roles') }}</span>
                </div>
                <div>
                    <h2 class="fw-bold mb-0 text-dark">{{ $stats['total_roles'] }}</h2>
                    <p class="mb-0 text-secondary small">{{ __('ui.roles_management') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-premium border-0 h-100 bg-white">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="bg-light rounded-3 p-3 text-noubtigo-gradient">
                        <i class="bi bi-key fs-3"></i>
                    </div>
                    <span class="badge bg-light text-muted rounded-pill">{{ __('ui.global_permissions') }}</span>
                </div>
                <div>
                    <h2 class="fw-bold mb-0 text-dark">{{ $stats['total_permissions'] }}</h2>
                    <p class="mb-0 text-secondary small">{{ __('ui.permissions_management') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-premium border-0 h-100 bg-white">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="bg-light rounded-3 p-3 text-primary">
                        <i class="bi bi-people fs-3"></i>
                    </div>
                    <span class="badge bg-light text-muted rounded-pill">{{ __('ui.active_tenants') }}</span>
                </div>
                <div>
                    <h2 class="fw-bold mb-0 text-dark">{{ $stats['total_companies'] }}</h2>
                    <p class="mb-0 text-secondary small">{{ __('ui.companies') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-premium border-0 h-100 bg-white">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="bg-light rounded-3 p-3 text-warning">
                        <i class="bi bi-ticket-perforated fs-3"></i>
                    </div>
                </div>
                <div>
                    <h2 class="fw-bold mb-0 text-dark">{{ number_format($stats['total_tickets']) }}</h2>
                    <p class="mb-0 text-secondary small">{{ __('ui.total_tickets_processed') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Management Hub -->
<div class="card card-premium bg-light p-3 mb-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
        <ul class="nav nav-pills nav-pills-premium" id="rbacTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link @if(!request('roles_page') && !request('perms_page') && !request('assignments_page')) active @endif" id="roles-tab" data-bs-toggle="tab" data-bs-target="#roles-section" type="button">
                    <i class="bi bi-shield-shaded me-2"></i>{{ __('ui.roles_management') }}
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link @if(request('perms_page')) active @endif" id="perms-tab" data-bs-toggle="tab" data-bs-target="#perms-section" type="button">
                    <i class="bi bi-key-fill me-2"></i>{{ __('ui.permissions_management') }}
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link @if(request('assignments_page')) active @endif" id="assignments-tab" data-bs-toggle="tab" data-bs-target="#assignments-section" type="button">
                    <i class="bi bi-diagram-3-fill me-2"></i>{{ __('ui.role_assignment') }}
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="plans-tab" data-bs-toggle="tab" data-bs-target="#plans-section" type="button">
                    <i class="bi bi-gem me-2"></i>{{ __('ui.app_plans') }}
                </button>
            </li>
            <li class="nav-item d-none d-lg-block">
                <div class="vr h-100 mx-2 text-muted opacity-25"></div>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="companies-tab" data-bs-toggle="tab" data-bs-target="#companies-section" type="button">
                    <i class="bi bi-building me-2"></i>{{ __('ui.companies') }}
                </button>
            </li>
        </ul>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-dark rounded-pill px-4 fw-bold shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#createModuleCrudModal">
                <i class="bi bi-grid-3x3-gap me-2"></i>{{ __('ui.new_module_crud') }}
            </button>
            <button type="button" class="btn btn-premium-primary rounded-pill px-4 btn-sm" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                <i class="bi bi-plus-lg me-2"></i>{{ __('ui.new_role') }}
            </button>
        </div>
    </div>
</div>

<div class="tab-content" id="rbacTabsContent">
    <!-- ROLES MANAGEMENT -->
    <div class="tab-pane fade @if(!request('roles_page') && !request('perms_page') && !request('assignments_page')) show active @endif" id="roles-section" role="tabpanel">
        <div class="card card-premium overflow-hidden">
            <div class="card-header bg-white p-4 border-bottom">
                <form action="{{ route('rbac.master.index') }}" method="GET" class="row g-3 align-items-center">
                    <input type="hidden" name="active_tab" value="roles">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" name="search_roles" class="form-control border-start-0 rounded-end-pill py-2" placeholder="{{ __('ui.search_placeholder_roles') }}" value="{{ request('search_roles') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="role_scope" class="form-select rounded-pill py-2" onchange="this.form.submit()">
                            <option value="">{{ __('ui.all_scopes') }}</option>
                            <option value="1" {{ request('role_scope') === '1' ? 'selected' : '' }}>{{ __('ui.global') }}</option>
                            <option value="0" {{ request('role_scope') === '0' ? 'selected' : '' }}>{{ __('ui.scoped') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3 text-end">
                        <a href="{{ route('rbac.master.index') }}" class="btn btn-link text-secondary text-decoration-none small">{{ __('ui.reset_filters') }}</a>
                    </div>
                </form>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-premium table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('ui.role_name') }}</th>
                            <th>{{ __('ui.description') }}</th>
                            <th class="text-center">{{ __('ui.users') }}</th>
                            <th class="text-center">{{ __('ui.permissions') }}</th>
                            <th>{{ __('ui.scope_visibility') }}</th>
                            <th class="text-end">{{ __('ui.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-shield-check text-noubtigo-gradient fs-5"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold d-block">{{ $role->name }}</span>
                                        <code class="x-small text-muted">{{ $role->slug }}</code>
                                    </div>
                                </div>
                            </td>
                            <td class="small text-muted" style="max-width: 250px;">{{ Str::limit($role->description, 60) }}</td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark rounded-pill px-3">{{ $role->users_count }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-primary rounded-pill px-3">{{ $role->permissions_count }}</span>
                            </td>
                            <td>
                                @if($role->is_global)
                                    <span class="badge-global">
                                        <i class="bi bi-globe2"></i>{{ __('ui.system_role') }}
                                    </span>
                                @else
                                    <span class="badge-scoped">
                                        <i class="bi bi-building"></i>{{ __('ui.custom_role') }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm rounded-circle shadow-sm p-0 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                                        <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#editRoleModal-{{ $role->id }}"><i class="bi bi-pencil me-2 text-primary"></i>{{ __('ui.edit') }}</a></li>
                                        <li>
                                            <form action="{{ route('rbac.master.clone-role', $role->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item py-2"><i class="bi bi-copy me-2 text-info"></i>{{ __('ui.clone_role') }}</button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('rbac.master.destroy-role', $role->id) }}" method="POST" onsubmit="return confirm('{{ __('ui.delete_role_confirm') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-trash me-2"></i>{{ __('ui.delete') }}</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <img src="https://illustrations.popsy.co/gray/searching.svg" alt="No data" style="width: 150px;" class="mb-3">
                                <p class="text-muted">{{ __('ui.no_roles') }}</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($roles->hasPages())
            <div class="card-footer bg-white p-4">
                {{ $roles->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- PERMISSIONS MANAGEMENT -->
    <div class="tab-pane fade @if(request('perms_page')) show active @endif" id="perms-section" role="tabpanel">
        <div class="card card-premium overflow-hidden">
            <div class="card-header bg-white p-4 border-bottom">
                <form action="{{ route('rbac.master.index') }}" method="GET" class="row g-3">
                    <input type="hidden" name="active_tab" value="perms">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" name="search_perms" class="form-control border-start-0 rounded-end-pill py-2" placeholder="{{ __('ui.search_placeholder_perms') }}" value="{{ request('search_perms') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="perm_module" class="form-select rounded-pill py-2" onchange="this.form.submit()">
                            <option value="">{{ __('ui.all_modules') }}</option>
                            @foreach($modules as $mod)
                                <option value="{{ $mod }}" {{ request('perm_module') === $mod ? 'selected' : '' }}>{{ Str::title(str_replace('_', ' ', $mod)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="perm_scope" class="form-select rounded-pill py-2" onchange="this.form.submit()">
                            <option value="">{{ __('ui.all_scopes') }}</option>
                            <option value="1" {{ request('perm_scope') === '1' ? 'selected' : '' }}>{{ __('ui.global') }}</option>
                            <option value="0" {{ request('perm_scope') === '0' ? 'selected' : '' }}>{{ __('ui.scoped') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2 text-end">
                        <button type="button" class="btn btn-premium-primary rounded-pill w-100 py-2" data-bs-toggle="modal" data-bs-target="#createPermissionModal">
                            <i class="bi bi-plus-lg me-1"></i>{{ __('ui.new_permission') }}
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-premium table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('ui.permission_name') }}</th>
                            <th>{{ __('ui.module') }}</th>
                            <th>{{ __('ui.assigned_roles_count') }}</th>
                            <th>{{ __('ui.scope_visibility') }}</th>
                            <th class="text-end">{{ __('ui.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permissions as $permission)
                        <tr>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark">{{ $permission->name }}</span>
                                    <code class="x-small text-muted">{{ $permission->slug }}</code>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1 small fw-bold text-uppercase">
                                    {{ $permission->module ?: 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($permission->roles->take(3) as $role)
                                        <span class="badge bg-light text-muted small rounded-pill px-2">{{ $role->name }}</span>
                                    @endforeach
                                    @if($permission->roles->count() > 3)
                                        <span class="badge bg-light text-muted small rounded-pill px-2">+{{ $permission->roles->count() - 3 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($permission->is_global)
                                    <span class="badge-global">
                                        <i class="bi bi-shield-lock"></i>{{ __('ui.global_all_companies') }}
                                    </span>
                                @else
                                    <span class="badge-scoped">
                                        <i class="bi bi-building-lock"></i>{{ __('ui.scoped_x_companies', ['count' => $permission->companies->count()]) }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button class="btn btn-light btn-sm rounded-circle shadow-sm" data-bs-toggle="modal" data-bs-target="#editPermissionModal-{{ $permission->id }}">
                                    <i class="bi bi-pencil text-primary"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <p class="text-muted">{{ __('ui.no_permissions_found') }}</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($permissions->hasPages())
            <div class="card-footer bg-white p-4">
                {{ $permissions->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- ROLE -> PERMISSION ASSIGNMENT -->
    <div class="tab-pane fade @if(request('assignments_page')) show active @endif" id="assignments-section" role="tabpanel">
        <div class="row">
            <div class="col-lg-4">
                <div class="card card-premium border-0 mb-4 position-sticky" style="top: 100px;">
                    <div class="card-header bg-white p-4 border-bottom">
                        <h5 class="fw-bold mb-0 text-noubtigo-gradient">{{ __('ui.select_role_to_manage') }}</h5>
                    </div>
                    <div class="card-body p-0" style="max-height: 60vh; overflow-y: auto;">
                        <div class="list-group list-group-flush" id="assignmentRoleList">
                            @foreach($roles as $role)
                            <button type="button" class="list-group-item list-group-item-action p-4 border-0 role-assignment-btn @if($loop->first) active bg-noubtigo-gradient text-white @endif" 
                                    data-role-id="{{ $role->id }}" 
                                    data-role-name="{{ $role->name }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold d-block">{{ $role->name }}</span>
                                        <span class="small @if($loop->first) text-white-50 @else text-muted @endif">{{ $role->permissions_count }} {{ __('ui.permissions') }}</span>
                                    </div>
                                    <i class="bi bi-chevron-right @if($loop->first) text-white @else text-muted @endif"></i>
                                </div>
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div id="assignment-loading" class="text-center py-5 d-none">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <div id="assignment-content">
                    <form id="assignmentForm" action="{{ route('rbac.master.sync-role-permissions', ['role' => $roles->first()->id ?? 0]) }}" method="POST">
                        @csrf
                        <div class="card card-premium mb-4 border-0 overflow-hidden">
                            <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="fw-bold mb-0">{{ __('ui.permissions_matrix') }}</h5>
                                    <p class="mb-0 text-muted small" id="activeRoleDisplay">{{ $roles->first()->name ?? '' }}</p>
                                </div>
                                <button type="submit" class="btn btn-premium-primary rounded-pill px-4">
                                    <i class="bi bi-save me-2"></i>{{ __('ui.save_changes') }}
                                </button>
                            </div>
                            <div class="card-body p-4 bg-light bg-opacity-50">
                                @php
                                    $allPermissions = \App\Models\Permission::all()->groupBy('module');
                                    $activeRole = $roles->first();
                                    $activePermIds = $activeRole ? $activeRole->permissions->pluck('id')->toArray() : [];
                                @endphp
                                
                                <div class="row g-4">
                                    @foreach($allPermissions as $module => $modulePerms)
                                    <div class="col-md-6 col-xl-4">
                                        <div class="card card-premium h-100 border shadow-none">
                                            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                                                <h6 class="fw-bold mb-0 text-uppercase small tracking-wider text-muted">
                                                    <i class="bi bi-folder2-open me-2 text-primary"></i>{{ Str::title(str_replace('_', ' ', $module ?: 'Core')) }}
                                                </h6>
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input select-all-module" type="checkbox" data-module="{{ $module }}">
                                                </div>
                                            </div>
                                            <div class="card-body p-3">
                                                @foreach($modulePerms as $perm)
                                                <div class="d-flex justify-content-between align-items-center p-2 mb-2 rounded-3 hover-bg-light transition-all">
                                                    <div>
                                                        <span class="d-block small fw-bold">{{ $perm->name }}</span>
                                                        <code class="x-small text-muted">{{ $perm->slug }}</code>
                                                    </div>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input switch-premium perm-switch" type="checkbox" name="permission_ids[]" value="{{ $perm->id }}" 
                                                            data-module="{{ $module }}"
                                                            @if(in_array($perm->id, $activePermIds)) checked @endif>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- PLANS MANAGEMENT -->
    <div class="tab-pane fade" id="plans-section" role="tabpanel">
        <div class="card card-premium overflow-hidden">
            <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">{{ __('ui.manage_plans') }}</h5>
                <button type="button" class="btn btn-premium-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createPlanModal">
                    <i class="bi bi-plus-lg me-2"></i>{{ __('ui.new_plan') }}
                </button>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-premium table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('ui.plan_name') }}</th>
                            <th>{{ __('ui.price') }} ({{ __('pricing.currency') }})</th>
                            <th>{{ __('ui.price') }} ({{ __('pricing.yearly') }})</th>
                            <th class="text-center">{{ __('ui.staff_limit') }}</th>
                            <th class="text-center">{{ __('ui.room_limit') }}</th>
                            <th class="text-center">{{ __('ui.display_limit') }}</th>
                            <th class="text-center">{{ __('ui.ticket_limit_monthly') }}</th>
                            <th class="text-center">{{ __('ui.customer_limit') ?? 'Customer Limit' }}</th>
                            <th class="text-center">{{ __('ui.status') }}</th>
                            <th class="text-center">{{ __('ui.scope_visibility') }}</th>
                            <th class="text-end">{{ __('ui.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plans as $plan)
                        <tr>
                            <td><span class="fw-bold text-noubtigo-gradient">{{ $plan->name }}</span></td>
                            <td><span class="fw-bold">{{ number_format($plan->price, 2) }}</span></td>
                            <td><span class="text-muted">{{ $plan->annual_price > 0 ? number_format($plan->annual_price, 2) : '—' }}</span></td>
                            <td class="text-center">{{ $plan->limits['staff_limit'] ?? 'N/A' }}</td>
                            <td class="text-center">{{ $plan->limits['room_limit'] ?? 'N/A' }}</td>
                            <td class="text-center">{{ $plan->limits['display_limit'] ?? 'N/A' }}</td>
                            <td class="text-center">{{ $plan->limits['ticket_limit_monthly'] ?? 'N/A' }}</td>
                            <td class="text-center">{{ $plan->limits['customer_limit'] ?? 'N/A' }}</td>
                            <td class="text-center">
                                <span class="badge {{ $plan->is_active ? 'bg-success' : 'bg-secondary' }}-subtle text-{{ $plan->is_active ? 'success' : 'secondary' }}">
                                    {{ $plan->is_active ? __('ui.active') : __('ui.inactive') }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info-subtle text-info">
                                    {{ $plan->visibility_scope === 'all' ? __('ui.all_companies') : __('ui.plan_selected_companies') }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <button class="btn btn-light btn-sm rounded-circle me-2" data-bs-toggle="modal" data-bs-target="#editPlanModal-{{ $plan->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('rbac.master.destroy-plan', $plan->id) }}" method="POST" onsubmit="return confirm('{{ __('ui.delete_plan_confirm') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-light btn-sm rounded-circle text-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- COMPANIES & OTHER TABS (Simplified) -->
    <div class="tab-pane fade" id="companies-section" role="tabpanel">
        <div class="card card-premium border-0 overflow-hidden">
            <div class="card-body p-0 table-responsive">
                <table class="table table-premium table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('ui.company_name') }}</th>
                            <th>{{ __('ui.plan') }}</th>
                            <th class="text-center">{{ __('ui.staff_size') }}</th>
                            <th class="text-center">{{ __('ui.status') }}</th>
                            <th class="text-end">{{ __('ui.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($companies as $company)
                        <tr>
                            <td>
                                <span class="fw-bold">{{ $company->name }}</span>
                                <br><small class="text-muted">{{ $company->subdomain }}.noubtigo.com</small>
                            </td>
                            <td>
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">{{ $company->plan->name ?? __('ui.no_plan') }}</span>
                            </td>
                            <td class="text-center">{{ $company->users_count }}</td>
                            <td class="text-center">
                                @if($company->is_active)
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">{{ __('ui.active') }}</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">{{ __('ui.inactive') }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button class="btn btn-light btn-sm rounded-circle" data-bs-toggle="modal" data-bs-target="#editCompanyModal-{{ $company->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODALS -->

<!-- CREATE ROLE MODAL -->
<div class="modal fade" id="createRoleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('rbac.master.store-role') }}" method="POST">
                @csrf
                <div class="modal-header border-0 bg-noubtigo-gradient text-white p-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-shield-plus me-2"></i>{{ __('ui.create_new_role') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.role_name') }}</label>
                        <input type="text" name="name" class="form-control form-control-lg rounded-3 fs-6" placeholder="e.g. Sales Manager" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.global_scope') }}</label>
                        <select name="is_global" id="is_global_role" class="form-select rounded-3" required>
                            <option value="1">{{ __('ui.global_role_system') }}</option>
                            <option value="0">{{ __('ui.specific_companies_scoped') }}</option>
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="company_role_selector">
                        <label class="form-label small fw-bold">{{ __('ui.select_companies') }}</label>
                        <select name="company_ids[]" class="form-select rounded-3 h-auto" multiple style="min-height: 120px;">
                            @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold">{{ __('ui.description') }}</label>
                        <textarea name="description" class="form-control rounded-3" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ __('ui.cancel') }}</button>
                    <button type="submit" class="btn btn-premium-primary rounded-pill px-5">{{ __('ui.create_role') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT ROLE MODALS -->
@foreach($roles as $role)
<div class="modal fade" id="editRoleModal-{{ $role->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('rbac.master.update-role', $role->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header border-0 bg-dark text-white p-4">
                    <h5 class="fw-bold mb-0">{{ __('ui.edit') }}: {{ $role->name }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.role_name') }}</label>
                        <input type="text" name="name" value="{{ $role->name }}" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.global_scope') }}</label>
                        <select name="is_global" class="form-select rounded-3 is-global-toggle" data-target="edit-role-company-wrapper-{{ $role->id }}" required>
                            <option value="1" {{ $role->is_global ? 'selected' : '' }}>{{ __('ui.global_role_system') }}</option>
                            <option value="0" {{ !$role->is_global ? 'selected' : '' }}>{{ __('ui.specific_companies_scoped') }}</option>
                        </select>
                    </div>
                    <div class="mb-3 {{ $role->is_global ? 'd-none' : '' }}" id="edit-role-company-wrapper-{{ $role->id }}">
                        <label class="form-label small fw-bold">{{ __('ui.select_companies') }}</label>
                        <select name="company_ids[]" class="form-select rounded-3 h-auto" multiple style="min-height: 150px;">
                            @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ $role->companies->contains($company->id) ? 'selected' : '' }}>{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold">{{ __('ui.description') }}</label>
                        <textarea name="description" class="form-control rounded-3" rows="3">{{ $role->description }}</textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ __('ui.cancel') }}</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-5 fw-bold">{{ __('ui.save_changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- CREATE PERMISSION MODAL -->
<div class="modal fade" id="createPermissionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('rbac.master.store-permission') }}" method="POST">
                @csrf
                <div class="modal-header border-0 bg-primary text-white p-4">
                    <h5 class="fw-bold mb-0">{{ __('ui.create_new_permission') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.permission_name') }}</label>
                        <input type="text" name="name" id="perm_name" class="form-control form-control-lg rounded-3 fs-6" placeholder="e.g. Publish Documents" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.slug_auto') }}</label>
                        <input type="text" name="slug" id="perm_slug" class="form-control bg-light rounded-3" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.module') }}</label>
                        <div class="input-group">
                            <input type="text" name="module" id="perm_module_input" class="form-control rounded-3" placeholder="e.g. users, products...">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"></button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3" style="max-height: 200px; overflow-y: auto;">
                                @foreach($modules as $mod)
                                    <li><a class="dropdown-item small py-2 module-selector-item" href="#" data-value="{{ $mod }}">{{ $mod }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.global_scope') }}</label>
                        <select name="is_global" id="is_global_perm" class="form-select rounded-3" required>
                            <option value="1">{{ __('ui.all_companies_global') }}</option>
                            <option value="0">{{ __('ui.specific_companies_scoped') }}</option>
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="company_selector_wrapper">
                        <label class="form-label small fw-bold">{{ __('ui.select_companies') }}</label>
                        <select name="company_ids[]" class="form-select rounded-3 h-auto" multiple style="min-height: 120px;">
                            @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ __('ui.cancel') }}</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">{{ __('ui.create_permission') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT PERMISSION MODALS -->
@foreach($permissions as $permission)
<div class="modal fade" id="editPermissionModal-{{ $permission->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('rbac.master.update-permission', $permission->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header border-0 bg-dark text-white p-4">
                    <h5 class="fw-bold mb-0">{{ __('ui.edit_permission') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.permission_name') }}</label>
                        <input type="text" name="name" value="{{ $permission->name }}" class="form-control form-control-lg rounded-3 fs-6" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.module') }}</label>
                        <div class="input-group">
                            <input type="text" name="module" value="{{ $permission->module }}" class="form-control rounded-3">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"></button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3" style="max-height: 200px; overflow-y: auto;">
                                @foreach($modules as $mod)
                                    <li><a class="dropdown-item small py-2 module-selector-item" href="#" data-value="{{ $mod }}">{{ $mod }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.global_scope') }}</label>
                        <select name="is_global" class="form-select rounded-3 is-global-toggle" data-target="edit-company-wrapper-{{ $permission->id }}" required>
                            <option value="1" {{ $permission->is_global ? 'selected' : '' }}>{{ __('ui.all_companies_global') }}</option>
                            <option value="0" {{ !$permission->is_global ? 'selected' : '' }}>{{ __('ui.specific_companies_scoped') }}</option>
                        </select>
                    </div>
                    <div class="mb-3 {{ $permission->is_global ? 'd-none' : '' }}" id="edit-company-wrapper-{{ $permission->id }}">
                        <label class="form-label small fw-bold">{{ __('ui.select_companies') }}</label>
                        <select name="company_ids[]" class="form-select rounded-3 h-auto" multiple style="min-height: 120px;">
                            @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ $permission->companies->contains($company->id) ? 'selected' : '' }}>{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ __('ui.cancel') }}</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-5 fw-bold shadow-sm">{{ __('ui.save_changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- EDIT COMPANY MODALS -->
@foreach($companies as $company)
<div class="modal fade" id="editCompanyModal-{{ $company->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('rbac.master.update-company', $company->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header border-0 bg-dark text-white p-4">
                    <h5 class="fw-bold mb-0">{{ __('ui.edit_company') }}: {{ $company->name }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.company_name') }}</label>
                        <input type="text" name="name" value="{{ $company->name }}" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.plan') }}</label>
                        <select name="plan_id" class="form-select rounded-3">
                            <option value="">{{ __('ui.no_plan') }}</option>
                            @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ ($company->plan_id == $plan->id) ? 'selected' : '' }}>{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold">{{ __('ui.status') }}</label>
                        <select name="is_active" class="form-select rounded-3">
                            <option value="1" {{ $company->is_active ? 'selected' : '' }}>{{ __('ui.active') }}</option>
                            <option value="0" {{ !$company->is_active ? 'selected' : '' }}>{{ __('ui.inactive') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ __('ui.cancel') }}</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-5 fw-bold shadow-sm">{{ __('ui.save_changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- CREATE PLAN MODAL -->
<div class="modal fade" id="createPlanModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('rbac.master.store-plan') }}" method="POST">
                @csrf
                <div class="modal-header border-0 bg-primary text-white p-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-gem me-2"></i>{{ __('ui.create_new_plan') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">{{ __('ui.plan_name') }}</label>
                            <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. Professional" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">{{ __('ui.price') }} ({{ __('pricing.monthly') }})</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ __('pricing.currency') }}</span>
                                <input type="number" step="0.01" name="price" class="form-control rounded-3" value="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">{{ __('ui.price') }} ({{ __('pricing.yearly') }})</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ __('pricing.currency') }}</span>
                                <input type="number" step="0.01" name="annual_price" class="form-control rounded-3" value="0.00">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">{{ __('ui.staff_limit') }}</label>
                            <input type="number" name="staff_limit" class="form-control rounded-3" value="5" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">{{ __('ui.room_limit') }}</label>
                            <input type="number" name="room_limit" class="form-control rounded-3" value="2" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">{{ __('ui.display_limit') }}</label>
                            <input type="number" name="display_limit" class="form-control rounded-3" value="2" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">{{ __('ui.ticket_limit_monthly') }}</label>
                            <input type="number" name="ticket_limit_monthly" class="form-control rounded-3" value="500" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">{{ __('ui.customer_limit') ?? 'Customer Limit' }}</label>
                            <input type="number" name="customer_limit" class="form-control rounded-3" value="100" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">{{ __('ui.status') }}</label>
                            <select name="is_active" class="form-select rounded-3">
                                <option value="1" selected>{{ __('ui.active') }}</option>
                                <option value="0">{{ __('ui.inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">{{ __('ui.scope_visibility') }}</label>
                            <select name="visibility_scope" class="form-select rounded-3 plan-visibility-scope" data-company-target="createPlanCompanies" required>
                                <option value="all">{{ __('ui.all_companies') }}</option>
                                <option value="selected">{{ __('ui.plan_selected_companies') }}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">{{ __('ui.plan_companies') }}</label>
                            <select name="company_ids[]" id="createPlanCompanies" class="form-select rounded-3" multiple size="3" disabled>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold mb-3 d-block">{{ __('ui.included_features_permissions') }}</label>
                            <div class="bg-light rounded-4 p-4" style="max-height: 600px; overflow-y: auto;">
                                <div class="row g-4">
                                    @foreach($allPermissions as $module => $perms)
                                    @php $moduleKey = $module ?: 'unassigned'; @endphp
                                    <div class="col-md-6 col-xl-4">
                                        <div class="card card-premium h-100 border shadow-none bg-white">
                                            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                                                <h6 class="fw-bold mb-0 text-uppercase small tracking-wider text-muted">
                                                    <i class="bi bi-folder2-open me-2 text-primary"></i>{{ $module ? Str::title(str_replace('_', ' ', $module)) : __('ui.unassigned_module') }}
                                                </h6>
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input select-all-modal-module" type="checkbox" data-module-group="plan-{{ Str::slug($moduleKey) }}">
                                                </div>
                                            </div>
                                            <div class="card-body p-3">
                                                @foreach($perms as $perm)
                                                <div class="d-flex justify-content-between align-items-center p-2 mb-2 rounded-3 hover-bg-light transition-all">
                                                    <div>
                                                        <span class="d-block small fw-bold">{{ $perm->name }}</span>
                                                        <code class="x-small text-muted">{{ $perm->slug }}</code>
                                                    </div>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input modal-perm-check" type="checkbox" name="permission_ids[]" value="{{ $perm->id }}" id="planPerm{{ $perm->id }}" data-module-group="plan-{{ Str::slug($moduleKey) }}">
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ __('ui.cancel') }}</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">{{ __('ui.create_plan') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT PLAN MODALS -->
@foreach($plans as $plan)
<div class="modal fade" id="editPlanModal-{{ $plan->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('rbac.master.update-plan', $plan->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header border-0 bg-dark text-white p-4">
                    <h5 class="fw-bold mb-0">{{ __('ui.edit_plan') }}: {{ $plan->name }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">{{ __('ui.plan_name') }}</label>
                            <input type="text" name="name" value="{{ $plan->name }}" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">{{ __('ui.price') }} ({{ __('pricing.monthly') }})</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ __('pricing.currency') }}</span>
                                <input type="number" step="0.01" name="price" class="form-control rounded-3" value="{{ $plan->price }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">{{ __('ui.price') }} ({{ __('pricing.yearly') }})</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ __('pricing.currency') }}</span>
                                <input type="number" step="0.01" name="annual_price" class="form-control rounded-3" value="{{ $plan->annual_price }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">{{ __('ui.staff_limit') }}</label>
                            <input type="number" name="staff_limit" class="form-control rounded-3" value="{{ $plan->limits['staff_limit'] ?? 0 }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">{{ __('ui.room_limit') }}</label>
                            <input type="number" name="room_limit" class="form-control rounded-3" value="{{ $plan->limits['room_limit'] ?? 0 }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">{{ __('ui.display_limit') }}</label>
                            <input type="number" name="display_limit" class="form-control rounded-3" value="{{ $plan->limits['display_limit'] ?? 0 }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">{{ __('ui.ticket_limit_monthly') }}</label>
                            <input type="number" name="ticket_limit_monthly" class="form-control rounded-3" value="{{ $plan->limits['ticket_limit_monthly'] ?? 0 }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">{{ __('ui.customer_limit') ?? 'Customer Limit' }}</label>
                            <input type="number" name="customer_limit" class="form-control rounded-3" value="{{ $plan->limits['customer_limit'] ?? 0 }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">{{ __('ui.status') }}</label>
                            <select name="is_active" class="form-select rounded-3">
                                <option value="1" {{ $plan->is_active ? 'selected' : '' }}>{{ __('ui.active') }}</option>
                                <option value="0" {{ !$plan->is_active ? 'selected' : '' }}>{{ __('ui.inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">{{ __('ui.scope_visibility') }}</label>
                            <select name="visibility_scope" class="form-select rounded-3 plan-visibility-scope" data-company-target="editPlanCompanies-{{ $plan->id }}" required>
                                <option value="all" {{ $plan->visibility_scope === 'all' ? 'selected' : '' }}>{{ __('ui.all_companies') }}</option>
                                <option value="selected" {{ $plan->visibility_scope === 'selected' ? 'selected' : '' }}>{{ __('ui.plan_selected_companies') }}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">{{ __('ui.plan_companies') }}</label>
                            <select name="company_ids[]" id="editPlanCompanies-{{ $plan->id }}" class="form-select rounded-3" multiple size="3" {{ $plan->visibility_scope === 'selected' ? '' : 'disabled' }}>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ $plan->visibleCompanies->contains($company->id) ? 'selected' : '' }}>{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold mb-3 d-block">{{ __('ui.included_features_permissions') }}</label>
                            <div class="bg-light rounded-4 p-4" style="max-height: 600px; overflow-y: auto;">
                                <div class="row g-4">
                                    @foreach($allPermissions as $module => $perms)
                                    @php $moduleKey = $module ?: 'unassigned'; @endphp
                                    <div class="col-md-6 col-xl-4">
                                        <div class="card card-premium h-100 border shadow-none bg-white">
                                            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                                                <h6 class="fw-bold mb-0 text-uppercase small tracking-wider text-muted">
                                                    <i class="bi bi-folder2-open me-2 text-primary"></i>{{ $module ? Str::title(str_replace('_', ' ', $module)) : __('ui.unassigned_module') }}
                                                </h6>
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input select-all-modal-module" type="checkbox" data-module-group="edit-plan-{{ $plan->id }}-{{ Str::slug($moduleKey) }}" {{ $perms->every(fn($p) => $plan->permissions->contains($p->id)) ? 'checked' : '' }}>
                                                </div>
                                            </div>
                                            <div class="card-body p-3">
                                                @foreach($perms as $perm)
                                                <div class="d-flex justify-content-between align-items-center p-2 mb-2 rounded-3 hover-bg-light transition-all">
                                                    <div>
                                                        <span class="d-block small fw-bold">{{ $perm->name }}</span>
                                                        <code class="x-small text-muted">{{ $perm->slug }}</code>
                                                    </div>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input modal-perm-check" type="checkbox" name="permission_ids[]" value="{{ $perm->id }}" id="editPlanPerm-{{ $plan->id }}-{{ $perm->id }}" data-module-group="edit-plan-{{ $plan->id }}-{{ Str::slug($moduleKey) }}" {{ $plan->permissions->contains($perm->id) ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ __('ui.cancel') }}</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-5 fw-bold shadow-sm">{{ __('ui.save_changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<script>
    document.querySelectorAll('.plan-visibility-scope').forEach(scope => {
        const syncCompanies = () => {
            const companies = document.getElementById(scope.dataset.companyTarget);
            if (companies) companies.disabled = scope.value !== 'selected';
        };
        scope.addEventListener('change', syncCompanies);
        syncCompanies();
    });
</script>

<!-- BULK CRUD MODAL -->
<div class="modal fade" id="createModuleCrudModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('rbac.master.store-module-permissions') }}" method="POST">
                @csrf
                <div class="modal-header border-0 bg-dark text-white p-4">
                    <h5 class="fw-bold mb-0">{{ __('ui.new_module_crud') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-4">{{ __('ui.module_crud_subtitle') }}</p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.module_name') }}</label>
                        <div class="input-group">
                            <input type="text" name="module_name" id="module_name_input" class="form-control form-control-lg rounded-start-3 fs-6" placeholder="{{ __('ui.module_name_placeholder') }}" required>
                            <button class="btn btn-outline-secondary dropdown-toggle rounded-end-3" type="button" data-bs-toggle="dropdown" aria-expanded="false"></button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3" style="max-height: 200px; overflow-y: auto;">
                                @foreach($modules as $mod)
                                    <li><a class="dropdown-item small py-2 module-selector-item" href="#" data-value="{{ $mod }}">{{ $mod }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.global_scope') }}</label>
                        <select name="is_global" id="is_global_module" class="form-select rounded-3" required>
                            <option value="1">{{ __('ui.all_companies_global') }}</option>
                            <option value="0">{{ __('ui.specific_companies_scoped') }}</option>
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="module_company_selector">
                        <label class="form-label small fw-bold">{{ __('ui.select_companies') }}</label>
                        <select name="company_ids[]" class="form-select rounded-3 h-auto" multiple style="min-height: 120px;">
                            @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ __('ui.cancel') }}</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-5 fw-bold shadow-sm">{{ __('ui.generate_permissions') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
    $(function() {
        // Handle Role Assignment Selection
        $('.role-assignment-btn').on('click', function() {
            const roleId = $(this).data('role-id');
            const roleName = $(this).data('role-name');
            
            // Update UI
            $('.role-assignment-btn').removeClass('active bg-noubtigo-gradient text-white');
            $('.role-assignment-btn .small').removeClass('text-white-50').addClass('text-muted');
            $('.role-assignment-btn i').removeClass('text-white').addClass('text-muted');
            
            $(this).addClass('active bg-noubtigo-gradient text-white');
            $(this).find('.small').removeClass('text-muted').addClass('text-white-50');
            $(this).find('i').removeClass('text-muted').addClass('text-white');
            
            $('#activeRoleDisplay').text(roleName);
            $('#assignmentForm').attr('action', `/rbac/master/roles/${roleId}/sync`);
            
            // Load Permissions for this role
            loadRolePermissions(roleId);
        });

        function loadRolePermissions(roleId) {
            $('#assignment-content').addClass('opacity-50');
            $('#assignment-loading').removeClass('d-none');
            
            $.get(`/rbac/master/roles/${roleId}/permissions`, function(data) {
                // Reset all switches
                $('.perm-switch').prop('checked', false);
                
                // Check permissions that the role has
                if (data.permission_ids) {
                    data.permission_ids.forEach(id => {
                        $(`.perm-switch[value="${id}"]`).prop('checked', true);
                    });
                }
                
                $('#assignment-content').removeClass('opacity-50');
                $('#assignment-loading').addClass('d-none');
            });
        }

        // Select All per module (Role Assignment Tab)
        $('.select-all-module').on('change', function() {
            const module = $(this).data('module');
            const checked = $(this).is(':checked');
            $(`.perm-switch[data-module="${module}"]`).prop('checked', checked);
        });

        // Select All per module (Modals)
        $('.select-all-modal-module').on('change', function() {
            const group = $(this).data('module-group');
            const checked = $(this).is(':checked');
            $(`.modal-perm-check[data-module-group="${group}"]`).prop('checked', checked);
        });

        // Slug generation
        $('#perm_name').on('input', function() {
            const name = $(this).val();
            const slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '.').replace(/(^\.|\.$)/g, '');
            $('#perm_slug').val(slug);
        });

        // Toggle Company Selectors
        $('#is_global_role').on('change', function() {
            $('#company_role_selector').toggleClass('d-none', $(this).val() === '1');
        });
        
        $('#is_global_perm').on('change', function() {
            $('#company_selector_wrapper').toggleClass('d-none', $(this).val() === '1');
        });

        $('#is_global_module').on('change', function() {
            $('#module_company_selector').toggleClass('d-none', $(this).val() === '1');
        });

        $('.is-global-toggle').on('change', function() {
            const targetId = $(this).data('target');
            $(`#${targetId}`).toggleClass('d-none', $(this).val() === '1');
        });

        // Module Selector items
        $(document).on('click', '.module-selector-item', function(e) {
            e.preventDefault();
            const val = $(this).data('value');
            $(this).closest('.input-group').find('input').val(val).trigger('input');
        });
    });
</script>
@endpush
