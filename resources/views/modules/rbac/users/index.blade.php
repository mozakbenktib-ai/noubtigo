@extends('layouts.dashboard')

@section('title', __('ui.users_management') . ' | Noubtigo')
@section('header_title', __('ui.users'))
@section('header_subtitle', __('ui.manage_staff'))

@section('content')
    <!-- Quick Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="glass-card p-4 border-0 shadow-sm h-100 position-relative overflow-hidden" style="border-radius: 16px; background: white;">
                <div class="position-absolute top-0 end-0 p-3 opacity-10">
                    <i class="bi bi-people-fill display-4 text-primary"></i>
                </div>
                <h6 class="text-secondary small fw-bold text-uppercase mb-2">{{ __('ui.total_users') }}</h6>
                <h2 class="fw-bold mb-0" id="stats-total">{{ $stats['total'] }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card p-4 border-0 shadow-sm h-100 position-relative overflow-hidden" style="border-radius: 16px; background: white;">
                <div class="position-absolute top-0 end-0 p-3 opacity-10">
                    <i class="bi bi-person-check-fill display-4 text-success"></i>
                </div>
                <h6 class="text-secondary small fw-bold text-uppercase mb-2">{{ __('ui.active_staff') }}</h6>
                <h2 class="fw-bold mb-0 text-success" id="stats-active">{{ $stats['active'] }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card p-4 border-0 shadow-sm h-100 position-relative overflow-hidden" style="border-radius: 16px; background: white;">
                <div class="position-absolute top-0 end-0 p-3 opacity-10">
                    <i class="bi bi-person-x-fill display-4 text-danger"></i>
                </div>
                <h6 class="text-secondary small fw-bold text-uppercase mb-2">{{ __('ui.inactive_staff') }}</h6>
                <h2 class="fw-bold mb-0 text-danger" id="stats-inactive">{{ $stats['inactive'] }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card p-4 border-0 shadow-sm h-100 position-relative overflow-hidden" style="border-radius: 16px; background: white;">
                <div class="position-absolute top-0 end-0 p-3 opacity-10">
                    <i class="bi bi-shield-lock-fill display-4 text-warning"></i>
                </div>
                <h6 class="text-secondary small fw-bold text-uppercase mb-2">{{ __('ui.admins') }}</h6>
                <h2 class="fw-bold mb-0 text-warning" id="stats-admins">{{ $stats['admins'] }}</h2>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <div class="row g-2">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" id="filter-search" class="form-control border-start-0" placeholder="{{ __('ui.search_placeholder') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="filter-role" class="form-select">
                        <option value="">{{ __('ui.role') }}</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="filter-status" class="form-select">
                        <option value="">{{ __('ui.status') }}</option>
                        <option value="active">{{ __('ui.active') }}</option>
                        <option value="inactive">{{ __('ui.inactive') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" id="clear-filters" class="btn btn-light w-100 d-none" title="{{ __('ui.reset_filters') }}">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> {{ __('ui.reset_filters') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Staff Table -->
    <div class="card border-0 shadow-sm rounded-4 users-table-card">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">{{ __('ui.staff') }}</h5>
            <button class="btn btn-gradient px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                <i class="bi bi-person-plus-fill me-2"></i> {{ __('ui.add_user') }}
            </button>
        </div>
        <div class="card-body p-0" id="users-table-container">
            @include('modules.rbac.users.partials.users-table')
        </div>
    </div>

    <!-- Modals (Add, Edit, Delete, etc.) -->
    @include('modules.rbac.users.partials.modals', ['roles' => $roles, 'rooms' => $rooms, 'services' => $services])

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/css/intlTelInput.css">
<style>
    .btn-gradient {
        background: linear-gradient(135deg, #22c55e 0%, #06b6d4 100%);
        color: white;
        border: none;
        transition: all 0.3s ease;
    }
    .btn-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(34, 197, 94, 0.4);
        color: white;
    }
    .users-table-card,
    #users-table-container {
        overflow: visible;
    }
    #users-table-container .dropdown-menu {
        z-index: 1080;
    }
    @media (min-width: 992px) {
        #users-table-container .table-responsive {
            overflow: visible;
        }
    }
    .glass-card {
        transition: all 0.3s ease;
    }
    .glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.05) !important;
    }
    .form-select, .form-control {
        border-radius: 8px;
        border: 1px solid #eee;
        padding: 0.6rem 1rem;
    }
    .form-select:focus, .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(34, 197, 94, 0.1);
    }
    .dropdown-item {
        padding: 0.6rem 1rem;
        font-weight: 500;
        font-size: 0.9rem;
    }
    .dropdown-item:hover {
        background-color: #f8f9fa;
    }
    
    /* Force pagination icons to be reasonably sized */
    nav[role="navigation"] svg {
        width: 1.25rem !important;
        height: 1.25rem !important;
        display: inline-block;
        vertical-align: middle;
    }

    /* intl-tel-input styling overrides */
    .iti {
        width: 100% !important;
        display: block !important;
    }
    .iti__country-list {
        z-index: 1060 !important; /* ensure it is above bootstrap modals */
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border: 1px solid #eee;
    }
    .iti input {
        padding-left: 52px !important;
    }
    [dir="rtl"] .iti input {
        padding-left: 12px !important;
        padding-right: 52px !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/intlTelInput.min.js"></script>
<script>
    const state = {
        search: '',
        role: '',
        status: '',
        sort_by: 'updated_at',
        sort_order: 'desc',
        page: 1
    };

    const tableContainer = document.getElementById('users-table-container');
    const clearBtn = document.getElementById('clear-filters');

    // Filter Inputs
    const searchInput = document.getElementById('filter-search');
    const roleInput = document.getElementById('filter-role');
    const statusInput = document.getElementById('filter-status');

    let searchTimeout;

    function updateClearButtonVisibility() {
        if (state.search || state.role || state.status) {
            clearBtn.classList.remove('d-none');
        } else {
            clearBtn.classList.add('d-none');
        }
    }

    function fetchUsers() {
        tableContainer.style.opacity = '0.5';

        const queryParams = new URLSearchParams(state).toString();
        
        fetch(`{{ route('rbac.users.index') }}?${queryParams}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            tableContainer.style.opacity = '1';
            tableContainer.innerHTML = data.html;

            // Update Stats
            document.getElementById('stats-total').textContent = data.stats.total;
            document.getElementById('stats-active').textContent = data.stats.active;
            document.getElementById('stats-inactive').textContent = data.stats.inactive;
            document.getElementById('stats-admins').textContent = data.stats.admins;

            updateSortHeaders();
            updateClearButtonVisibility();
        })
        .catch(err => {
            tableContainer.style.opacity = '1';
            console.error('Error fetching users:', err);
        });
    }

    function updateSortHeaders() {
        document.querySelectorAll('.sortable').forEach(th => {
            const sortField = th.dataset.sort;
            const iconContainer = th.querySelector('.sort-icon');
            
            if (sortField === state.sort_by) {
                if (state.sort_order === 'asc') {
                    iconContainer.innerHTML = '<i class="bi bi-sort-up ms-1 text-primary small"></i>';
                } else {
                    iconContainer.innerHTML = '<i class="bi bi-sort-down ms-1 text-primary small"></i>';
                }
            } else {
                iconContainer.innerHTML = '<i class="bi bi-arrow-down-up ms-1 text-muted small"></i>';
            }
        });
    }

    // Input handlers
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        state.search = this.value;
        state.page = 1;
        searchTimeout = setTimeout(fetchUsers, 300);
    });

    roleInput.addEventListener('change', function() {
        state.role = this.value;
        state.page = 1;
        fetchUsers();
    });

    statusInput.addEventListener('change', function() {
        state.status = this.value;
        state.page = 1;
        fetchUsers();
    });

    clearBtn.addEventListener('click', function() {
        state.search = '';
        state.role = '';
        state.status = '';
        state.page = 1;

        searchInput.value = '';
        roleInput.value = '';
        statusInput.value = '';

        fetchUsers();
    });

    // Event delegation for table header sorting
    document.addEventListener('click', function(e) {
        const th = e.target.closest('.sortable');
        if (th) {
            const sortField = th.dataset.sort;
            if (state.sort_by === sortField) {
                state.sort_order = state.sort_order === 'asc' ? 'desc' : 'asc';
            } else {
                state.sort_by = sortField;
                state.sort_order = 'desc';
            }
            state.page = 1;
            fetchUsers();
        }
    });

    // Event delegation for pagination link clicks
    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#pagination-wrapper a');
        if (pageLink) {
            e.preventDefault();
            const url = new URL(pageLink.href);
            const pageVal = url.searchParams.get('page');
            if (pageVal) {
                state.page = parseInt(pageVal);
                fetchUsers();
                tableContainer.scrollIntoView({ behavior: 'smooth' });
            }
        }
    });

    function confirmDelete(userId) {
        if (confirm("{{ __('ui.confirm_delete_user') }}")) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/rbac/users/${userId}`;
            form.innerHTML = `
                @csrf
                @method('DELETE')
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function toggleStatus(userId, status) {
        fetch(`/rbac/users/${userId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ is_active: status })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                fetchUsers();
            } else if (data.reload) {
                window.location.reload();
            } else if (data.message) {
                alert(data.message);
            }
        })
        .catch(error => {
            alert("An error occurred while updating the status.");
        });
    }

    function openEditModal(userId) {
        const modal = new bootstrap.Modal(document.getElementById(`editUserModal${userId}`));
        modal.show();
    }

    // Initialize Header Sort Icons
    updateSortHeaders();
</script>
@endpush
