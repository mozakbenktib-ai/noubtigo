@extends('layouts.dashboard')

@section('title', __('ui.users_management') . ' | Noubtigo')
@section('header_title', __('ui.users_management'))
@section('header_subtitle', __('ui.manage_staff'))

@section('content')
<!-- Topbar Action -->
@push('scripts')
<script>
    // Placeholder for search or filter if needed
</script>
@endpush

<div class="d-flex justify-content-end mb-4">
    <button class="btn btn-gradient px-4" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="bi bi-person-plus me-2"></i> {{ __('ui.add_user') }}
    </button>
</div>

<!-- Users Table -->
<div class="glass-card p-4 fade-in">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="text-secondary small text-uppercase">
                <tr>
                    <th>{{ __('ui.users') }}</th>
                    <th>{{ __('ui.email') }}</th>
                    <th>{{ __('ui.role') }}</th>
                    <th>{{ __('ui.status') }}</th>
                    <th>{{ __('ui.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name=Ali+Hassan&background=22c55e&color=fff" class="rounded-circle" width="40">
                            <div>
                                <h6 class="fw-bold mb-0">Ali Hassan</h6>
                                <small class="text-secondary">{{ __('ui.added_x_ago', ['time' => '2 days']) }}</small>
                            </div>
                        </div>
                    </td>
                    <td>ali.h@example.com</td>
                    <td><span class="badge bg-light text-dark px-3 rounded-pill border">{{ __('ui.admin') }}</span></td>
                    <td><span class="badge bg-success-subtle text-success px-3 rounded-pill">{{ __('ui.active') }}</span></td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-transparent border-0 shadow-none p-2" data-bs-toggle="dropdown"><i class="bi bi-three-dots text-secondary fs-5"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><a class="dropdown-item" href="#">{{ __('ui.edit') }}</a></li>
                                <li><a class="dropdown-item text-danger" href="#">{{ __('ui.deactivate') }}</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add User -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">{{ __('ui.add_new_staff') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="addUserForm">
                    <div class="mb-3">
                        <label class="form-label small fw-medium">{{ __('ui.full_name') }}</label>
                        <input type="text" class="form-control" placeholder="John Doe" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">{{ __('ui.email_address') }}</label>
                        <input type="email" class="form-control" placeholder="john@example.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">{{ __('ui.role') }}</label>
                        <select class="form-select">
                            <option>{{ __('ui.admin') }}</option>
                            <option>{{ __('ui.secretary') }}</option>
                            <option>{{ __('ui.staff_operator') }}</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-gradient w-100 py-2 mt-3">{{ __('ui.create_user') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
