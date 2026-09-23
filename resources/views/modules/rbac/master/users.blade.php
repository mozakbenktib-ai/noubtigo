@extends('layouts.dashboard')

@section('title', __('ui.global_user_directory') . ' | Noubtigo')
@section('header_title', __('ui.system_user_directory'))
@section('header_subtitle', __('ui.visibility_subtitle'))

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <style>
        .datatable td { padding-top: 1.25rem; padding-bottom: 1.25rem; }
        .badge { font-weight: 500; letter-spacing: 0.025em; }
        .avatar-group { display: flex; align-items: center; }
        .avatar { position: relative; width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; }
        .company-logo { width: 24px; height: 24px; border-radius: 4px; object-fit: cover; }
        .hover-shadow-sm:hover { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important; transition: all 0.2s; }
    </style>
@endpush

@section('content')
<!-- Search & Filter Bar (Optional) -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-0">
            <div>
                <h5 class="fw-bold mb-1 text-dark">{{ __('ui.global_records') }}</h5>
                <p class="text-muted small mb-0">{{ __('ui.browsing_x_users', ['total' => $users->total()]) }}</p>
            </div>
            <div class="d-flex gap-2">
                 <button class="btn btn-light rounded-pill px-4 fw-bold shadow-sm btn-sm border">
                    <i class="bi bi-download me-1"></i> {{ __('ui.export_data') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Main User Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable" id="usersTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">{{ __('ui.user_identity') }}</th>
                        <th>{{ __('ui.email_contact') }}</th>
                        <th>{{ __('ui.tenant_assignment') }}</th>
                        <th>{{ __('ui.access_level') }}</th>
                        <th>{{ __('ui.active_roles') }}</th>
                        <th class="text-end pe-4">{{ __('ui.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr class="hover-shadow-sm">
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->full_name) }}&background=6366f1&color=fff&bold=true" 
                                     class="avatar shadow-sm" alt="Avatar">
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark">{{ $user->full_name }}</span>
                                    <span class="x-small text-muted text-uppercase fw-semibold" style="font-size: 0.65rem;">UID: {{ Str::limit($user->id, 8, '') }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="text-dark small"><i class="bi bi-envelope-at me-1 text-muted"></i> {{ $user->email }}</span>
                                @if($user->phone)
                                <span class="text-muted extra-small"><i class="bi bi-telephone me-1"></i> {{ $user->phone }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($user->company)
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $user->company->getLogoUrl() }}" class="company-logo shadow-sm border">
                                <span class="badge rounded-pill bg-light text-dark border px-3">{{ $user->company->name }}</span>
                            </div>
                            @else
                            <span class="badge rounded-pill bg-dark text-white px-3"><i class="bi bi-cpu me-1"></i> {{ __('ui.system_root') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($user->is_system_admin)
                            <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-1">
                                <i class="bi bi-shield-fill-check me-1"></i> {{ __('ui.global_admin') }}
                            </span>
                            @else
                            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1">
                                <i class="bi bi-person-check me-1"></i> {{ __('ui.staff_member') }}
                            </span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @forelse($user->roles as $role)
                                    <span class="badge rounded-pill bg-light text-secondary border x-small px-2 py-1" style="font-size: 0.7rem;">
                                        {{ $role->name }}
                                    </span>
                                @empty
                                    <span class="text-muted small italic">{{ __('ui.default') }}</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex gap-2 justify-content-end">
                                <button class="btn btn-light btn-sm rounded-circle shadow-sm border" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#viewUserModal-{{ $user->id }}"
                                        title="{{ __('ui.view_details') }}">
                                    <i class="bi bi-eye text-primary"></i>
                                </button>
                                <button class="btn btn-light btn-sm rounded-circle shadow-sm border" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#managePermissionsModal-{{ $user->id }}"
                                        title="{{ __('ui.manage_permissions') }}">
                                    <i class="bi bi-shield-lock text-warning"></i>
                                </button>
                                @if(!$user->is_system_admin)
                                <button class="btn btn-light btn-sm rounded-circle shadow-sm border" title="{{ __('ui.login_as_user') }}">
                                    <i class="bi bi-incognito text-success"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach

<!-- VIEW USER MODAL -->
@foreach($users as $user)
<div class="modal fade" id="viewUserModal-{{ $user->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-light p-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; font-size: 1.2rem;">
                        {{ strtoupper(substr($user->first_name, 0, 1)) }}
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">{{ $user->full_name }}</h5>
                        <p class="text-muted small mb-0">{{ $user->email }}</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="text-uppercase x-small fw-bold text-muted mb-1 d-block">{{ __('ui.tenant_assignment') }}</label>
                        <p class="mb-0 fw-medium">
                            @if($user->company)
                                <i class="bi bi-building me-1 text-primary"></i> {{ $user->company->name }}
                            @else
                                <i class="bi bi-cpu me-1 text-danger"></i> {{ __('ui.system_root') }}
                            @endif
                        </p>
                    </div>
                    <div class="col-6">
                        <label class="text-uppercase x-small fw-bold text-muted mb-1 d-block">{{ __('ui.access_level') }}</label>
                        <p class="mb-0">
                            @if($user->is_system_admin)
                                <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2">{{ __('ui.global_admin') }}</span>
                            @else
                                <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2">{{ __('ui.staff_member') }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-12 mt-4">
                        <label class="text-uppercase x-small fw-bold text-muted mb-1 d-block">{{ __('ui.active_roles') }}</label>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @forelse($user->roles as $role)
                                <span class="badge bg-light text-dark border px-3 py-2 fw-medium">
                                    <i class="bi bi-shield-check me-1 text-success"></i> {{ $role->name }}
                                </span>
                            @empty
                                <span class="text-muted italic small">{{ __('ui.no_roles_assigned') }}</span>
                            @endforelse
                        </div>

                        <label class="text-uppercase x-small fw-bold text-muted mb-1 d-block">Direct Permissions</label>
                        <div class="d-flex flex-wrap gap-2">
                            @forelse($user->permissions as $perm)
                                <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 px-3 py-2 fw-medium">
                                    <i class="bi bi-key me-1"></i> {{ $perm->name }}
                                </span>
                            @empty
                                <span class="text-muted italic small">No direct permissions</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ __('ui.close') }}</button>
                <div class="ms-auto d-flex gap-2">
                    <a href="#" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-pencil-square me-1"></i> {{ __('ui.edit_user') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MANAGE PERMISSIONS MODAL -->
<div class="modal fade" id="managePermissionsModal-{{ $user->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('rbac.master.update-user-permissions', $user->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header border-0 bg-warning bg-opacity-10 p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
                            <i class="bi bi-shield-lock fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Manage Permissions</h5>
                            <p class="text-muted small mb-0">{{ $user->full_name }} (Direct Assignment)</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-4">
                        <i class="bi bi-info-circle me-1"></i> 
                        These permissions are assigned <strong>directly</strong> to the user, bypassing their roles. User will still inherit permissions from their roles.
                    </p>

                    <div class="row g-4">
                        @foreach($allPermissions as $module => $perms)
                            <div class="col-md-6 col-lg-4">
                                <div class="p-3 rounded-3 bg-light h-100">
                                    <h6 class="fw-bold text-uppercase x-small text-primary mb-3 border-bottom pb-2">
                                        <i class="bi bi-grid-fill me-1"></i> {{ $module ?: 'General' }}
                                    </h6>
                                    @foreach($perms as $perm)
                                        @php
                                            $hasDirect = $user->permissions->contains('id', $perm->id);
                                            $hasViaRole = $user->hasPermission($perm->slug) && !$hasDirect;
                                        @endphp
                                        <div class="form-check mb-2 d-flex align-items-center justify-content-between">
                                            <div>
                                                <input class="form-check-input" type="checkbox" name="permission_ids[]" 
                                                       value="{{ $perm->id }}" id="perm-{{ $user->id }}-{{ $perm->id }}"
                                                       {{ $hasDirect ? 'checked' : '' }}>
                                                <label class="form-check-label small fw-medium {{ $hasViaRole ? 'text-primary' : 'text-dark' }}" for="perm-{{ $user->id }}-{{ $perm->id }}">
                                                    {{ $perm->name }}
                                                </label>
                                            </div>
                                            @if($hasViaRole)
                                                <span class="badge bg-primary bg-opacity-10 text-primary border-0 x-small px-2 py-0 ms-2" style="font-size: 0.6rem;" title="Inherited from Role">
                                                    ROLE
                                                </span>
                                            @endif
                                            @if($perm->is_global)
                                                <i class="bi bi-globe2 x-small text-muted ms-1" title="Global Permission"></i>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ __('ui.cancel') }}</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Save Direct Permissions
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-top">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted small">{{ __('ui.showing_x_to_y', ['first' => $users->firstItem(), 'last' => $users->lastItem(), 'total' => $users->total()]) }}</span>
                <div>{{ $users->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- jQuery & DataTables -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#usersTable').DataTable({
        responsive: true,
        paging: false, // Use Laravel pagination for the dataset
        info: false,
        dom: '<"p-3"f>rt',
        language: {
            search: "_INPUT_",
            searchPlaceholder: "{{ __('ui.search_global_directory') }}"
        }
    });

    // Initialize Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});
</script>
@endpush
