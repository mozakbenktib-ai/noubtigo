@extends('layouts.dashboard')

@section('title', 'Noubtigo | ' . __('ui.manage_customers'))
@section('header_title', __('ui.customers'))
@section('header_subtitle', __('ui.client_list'))

@section('content')
    <!-- Quick Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="glass-card p-4 border-0 shadow-sm h-100 position-relative overflow-hidden" style="border-radius: 16px; background: white;">
                <div class="position-absolute top-0 end-0 p-3 opacity-10">
                    <i class="bi bi-people-fill display-4 text-primary"></i>
                </div>
                <h6 class="text-secondary small fw-bold text-uppercase mb-2">{{ __('ui.total_users') }}</h6>
                <h2 class="fw-bold mb-0" id="stats-total">{{ $stats['total'] }}</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 border-0 shadow-sm h-100 position-relative overflow-hidden" style="border-radius: 16px; background: white;">
                <div class="position-absolute top-0 end-0 p-3 opacity-10">
                    <i class="bi bi-calendar-check-fill display-4 text-success"></i>
                </div>
                <h6 class="text-secondary small fw-bold text-uppercase mb-2">{{ __('ui.today_visits') }}</h6>
                <h2 class="fw-bold mb-0 text-success" id="stats-today-visits">{{ $stats['today_visits'] }}</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 border-0 shadow-sm h-100 position-relative overflow-hidden" style="border-radius: 16px; background: white;">
                <div class="position-absolute top-0 end-0 p-3 opacity-10">
                    <i class="bi bi-clock-fill display-4 text-info"></i>
                </div>
                <h6 class="text-secondary small fw-bold text-uppercase mb-2">{{ __('ui.appointments_today') }}</h6>
                <h2 class="fw-bold mb-0 text-info" id="stats-appointments">{{ $stats['appointments_today'] }}</h2>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <div class="row g-2">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" id="filter-search" class="form-control border-start-0" placeholder="{{ __('ui.search_placeholder') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="button" id="clear-filters" class="btn btn-light w-100 d-none" title="{{ __('ui.reset_filters') }}">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> {{ __('ui.reset_filters') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">{{ __('ui.customers') }}</h5>
            @permission('customers.create')
            <button class="btn btn-gradient px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                <i class="bi bi-person-plus-fill me-2"></i> {{ __('ui.add_customer') }}
            </button>
            @endpermission
        </div>
        <div class="card-body p-0" id="customers-table-container">
            @include('pages.customers.partials.customers-table')
        </div>
    </div>

    <!-- Modals (Add, Edit) -->
    @include('pages.customers.partials.modals')

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
    .glass-card {
        transition: all 0.3s ease;
    }
    .glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.05) !important;
    }
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.9rem;
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
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/intlTelInput.min.js"></script>
<script>
    const csrfToken = '{{ csrf_token() }}';
    let itiAdd, itiEdit;
    const addPhoneEl = document.getElementById('add_phone');
    const editPhoneEl = document.getElementById('edit_phone');

    const state = {
        search: '',
        sort_by: 'created_at',
        sort_order: 'desc',
        page: 1
    };

    const tableContainer = document.getElementById('customers-table-container');
    const clearBtn = document.getElementById('clear-filters');

    // Filter Inputs
    const searchInput = document.getElementById('filter-search');

    let searchTimeout;

    document.addEventListener("DOMContentLoaded", function() {
        if (addPhoneEl) {
            itiAdd = window.intlTelInput(addPhoneEl, {
                initialCountry: "ma",
                preferredCountries: ["ma", "fr", "es"],
                utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/utils.js"
            });
        }
        if (editPhoneEl) {
            itiEdit = window.intlTelInput(editPhoneEl, {
                initialCountry: "ma",
                preferredCountries: ["ma", "fr", "es"],
                utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/utils.js"
            });
        }
        updateSortHeaders();
    });

    function updateClearButtonVisibility() {
        if (state.search) {
            clearBtn.classList.remove('d-none');
        } else {
            clearBtn.classList.add('d-none');
        }
    }

    function fetchCustomers() {
        tableContainer.style.opacity = '0.5';

        const queryParams = new URLSearchParams(state).toString();
        
        fetch(`{{ route('customers.index') }}?${queryParams}`, {
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
            document.getElementById('stats-today-visits').textContent = data.stats.today_visits;
            document.getElementById('stats-appointments').textContent = data.stats.appointments_today;

            updateSortHeaders();
            updateClearButtonVisibility();
        })
        .catch(err => {
            tableContainer.style.opacity = '1';
            console.error('Error fetching customers:', err);
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

    // Filter Inputs listeners
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        state.search = this.value;
        state.page = 1;
        searchTimeout = setTimeout(fetchCustomers, 300);
    });

    clearBtn.addEventListener('click', function() {
        state.search = '';
        state.page = 1;

        searchInput.value = '';

        fetchCustomers();
    });

    // Header sort click delegation
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
            fetchCustomers();
        }
    });

    // Pagination link delegation
    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#pagination-wrapper a');
        if (pageLink) {
            e.preventDefault();
            const url = new URL(pageLink.href);
            const pageVal = url.searchParams.get('page');
            if (pageVal) {
                state.page = parseInt(pageVal);
                fetchCustomers();
                tableContainer.scrollIntoView({ behavior: 'smooth' });
            }
        }
    });

    function openEditModal(customer) {
        document.getElementById('edit_customer_id').value = customer.id;
        document.getElementById('edit_first_name').value = customer.first_name;
        document.getElementById('edit_last_name').value = customer.last_name || '';
        document.getElementById('edit_identifier').value = customer.identifier || '';

        const errorEl = document.getElementById('edit_phone_error');
        errorEl.style.display = 'none';
        editPhoneEl.classList.remove('is-invalid');

        if (customer.phone) {
            let formattedPhone = customer.phone;
            if (!formattedPhone.startsWith('+') && /^\d+$/.test(formattedPhone)) {
                formattedPhone = '+' + formattedPhone;
            }
            if (itiEdit) {
                itiEdit.setNumber(formattedPhone);
            } else {
                editPhoneEl.value = customer.phone;
            }
        } else {
            if (itiEdit) itiEdit.setNumber('');
            else editPhoneEl.value = '';
        }

        const modalEl = document.getElementById('editCustomerModal');
        let modal = bootstrap.Modal.getInstance(modalEl);
        if (!modal) modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    function toggleVip(id, status) {
        fetch(`/customers/${id}/vip`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ is_vip: status })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) fetchCustomers();
        });
    }

    function deleteCustomer(id) {
        if(!confirm("{{ __('ui.confirm_delete_customer') }}")) return;

        fetch(`/customers/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) fetchCustomers();
        });
    }

    function handleCustomerStore(e) {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;

        const payload = Object.fromEntries(new FormData(form));

        const phoneVal = addPhoneEl.value.trim();
        const errorEl = document.getElementById('add_phone_error');
        errorEl.style.display = 'none';
        addPhoneEl.classList.remove('is-invalid');

        if (phoneVal) {
            if (itiAdd && !itiAdd.isValidNumber()) {
                errorEl.textContent = 'Invalid phone number for the selected country.';
                errorEl.style.display = 'block';
                addPhoneEl.classList.add('is-invalid');
                btn.disabled = false;
                return;
            }
            payload.phone = itiAdd ? itiAdd.getNumber().replace(/\D/g, '') : phoneVal.replace(/\D/g, '');
        } else {
            payload.phone = '';
        }

        fetch('{{ route("customers.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                form.reset();
                if (itiAdd) itiAdd.setNumber('');
                btn.disabled = false;

                const modalEl = document.getElementById('addCustomerModal');
                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.hide();

                fetchCustomers();
            } else {
                alert(data.message || 'Error occurred');
                btn.disabled = false;
            }
        });
    }

    function handleCustomerUpdate(e) {
        e.preventDefault();
        const form = e.target;
        const id = document.getElementById('edit_customer_id').value;
        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;

        const phoneVal = editPhoneEl.value.trim();
        const errorEl = document.getElementById('edit_phone_error');
        errorEl.style.display = 'none';
        editPhoneEl.classList.remove('is-invalid');

        let phoneResult = '';
        if (phoneVal) {
            if (itiEdit && !itiEdit.isValidNumber()) {
                errorEl.textContent = 'Invalid phone number for the selected country.';
                errorEl.style.display = 'block';
                editPhoneEl.classList.add('is-invalid');
                btn.disabled = false;
                return;
            }
            phoneResult = itiEdit ? itiEdit.getNumber().replace(/\D/g, '') : phoneVal.replace(/\D/g, '');
        }

        const payload = {
            first_name: document.getElementById('edit_first_name').value,
            last_name: document.getElementById('edit_last_name').value,
            identifier: document.getElementById('edit_identifier').value,
            phone: phoneResult
        };

        fetch(`/customers/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                btn.disabled = false;
                const modalEl = document.getElementById('editCustomerModal');
                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.hide();

                fetchCustomers();
            } else {
                alert(data.message || 'Error occurred');
                btn.disabled = false;
            }
        });
    }
</script>
@endpush
