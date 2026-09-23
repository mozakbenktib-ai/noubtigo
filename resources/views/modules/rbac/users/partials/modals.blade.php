@foreach($users as $user)
@php
    $userRoleSlugs = $user->roles->pluck('slug');
    $isCompanyAdmin = $userRoleSlugs->contains('company-admin');
    $isAdmin = $userRoleSlugs->intersect(['admin', 'super-admin', 'company-admin'])->isNotEmpty();
@endphp
<!-- Modals for User: {{ $user->full_name }} -->

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius: 24px; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(10px);">
            <form action="{{ route('rbac.users.update', $user) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="modal-title fw-bold text-primary mb-1">{{ __('ui.edit_user_details') }}</h4>
                        <p class="text-muted small mb-0">{{ __('ui.update_information_for', ['name' => $user->full_name]) }}</p>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" style="background-color: #f8fafc; padding: 0.8rem; border-radius: 50%;"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Left Column: Personal Info -->
                        <div class="col-md-7">
                            <h6 class="fw-bold text-uppercase small text-muted mb-3"><i class="bi bi-person-badge me-2"></i>{{ __('ui.personal_details') }}</h6>
                            <div class="row g-3">
                                <div class="col-md-6 text-start">
                                    <label class="form-label small fw-bold">{{ __('ui.first_name') }}</label>
                                    <input type="text" name="first_name" class="form-control form-control-lg border-0 bg-light rounded-4 px-3 shadow-none" style="font-size: 0.9rem;" value="{{ $user->first_name }}" required>
                                </div>
                                <div class="col-md-6 text-start">
                                    <label class="form-label small fw-bold">{{ __('ui.last_name') }}</label>
                                    <input type="text" name="last_name" class="form-control form-control-lg border-0 bg-light rounded-4 px-3 shadow-none" style="font-size: 0.9rem;" value="{{ $user->last_name }}" required>
                                </div>
                                <div class="col-12 text-start">
                                    <label class="form-label small fw-bold">{{ __('ui.username') }} @if($isCompanyAdmin)<span class="text-muted fw-normal">({{ __('ui.optional') }})</span>@endif</label>
                                    <input type="text" name="username" class="form-control form-control-lg border-0 bg-light rounded-4 px-3 shadow-none" style="font-size: 0.9rem;" value="{{ $user->username }}" {{ $isCompanyAdmin ? '' : 'required' }}>
                                </div>
                                <div class="col-12 text-start">
                                    @if($isAdmin)
                                        <label class="form-label small fw-bold">{{ __('ui.email_address') }} <span class="text-danger fw-normal">({{ __('ui.required_for_admin') }})</span></label>
                                        <input type="email" name="email" class="form-control form-control-lg border-0 bg-light rounded-4 px-3 shadow-none @error('email') is-invalid @enderror" style="font-size: 0.9rem;" value="{{ old('email', $user->email) }}" required>
                                    @else
                                        <label class="form-label small fw-bold">{{ __('ui.email_address') }} ({{ __('ui.optional') }})</label>
                                        <input type="email" name="email" class="form-control form-control-lg border-0 bg-light rounded-4 px-3 shadow-none @error('email') is-invalid @enderror" style="font-size: 0.9rem;" value="{{ old('email', $user->email) }}">
                                    @endif
                                </div>
                                <div class="col-12 text-start d-flex flex-column">
                                    <label class="form-label small fw-bold">{{ __('ui.phone_number') }}</label>
                                    <input type="tel" id="staff_edit_phone_{{ $user->id }}" class="form-control form-control-lg border-0 bg-light rounded-4 shadow-none staff-edit-phone" style="font-size: 0.9rem; padding-left: 52px !important;" data-user-id="{{ $user->id }}" value="{{ $user->phone }}">
                                    <input type="hidden" name="phone" id="hidden_staff_edit_phone_{{ $user->id }}">
                                    <div class="invalid-feedback text-danger small mt-1" id="staff_edit_phone_error_{{ $user->id }}" style="display: none;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Roles Summary -->
                        <div class="col-md-5">
                            <div class="p-4 rounded-4 h-100 d-flex flex-column justify-content-center text-center" style="background: linear-gradient(135deg, rgba(6, 182, 212, 0.05) 0%, rgba(34, 197, 94, 0.05) 100%); border: 1px dashed rgba(6, 182, 212, 0.2);">
                                <div class="avatar-wrapper mb-3 mx-auto">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->full_name) }}&background=06b6d4&color=fff" class="rounded-circle shadow-sm" width="64" height="64">
                                </div>
                                <h6 class="fw-bold mb-1">{{ $user->full_name }}</h6>
                                <p class="text-muted small mb-3">{{ $user->email }}</p>
                                <div class="d-flex flex-wrap justify-content-center gap-1">
                                    @foreach($user->roles as $role)
                                        <span class="badge bg-white text-primary border rounded-pill px-2 py-1 small fw-medium shadow-sm">{{ $role->name }}</span>
                                    @endforeach
                                </div>
                                <div class="d-flex flex-column gap-2 mt-4">
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#editRolesModal{{ $user->id }}">
                                        <i class="bi bi-shield-lock me-1"></i> {{ __('ui.manage_roles') }}
                                    </button>
                                    <button type="submit" form="regeneratePasswordForm{{ $user->id }}" class="btn btn-outline-warning btn-sm rounded-pill px-3 w-100" onclick="return confirm('{{ __('ui.regenerate_password_confirm') }}')">
                                        <i class="bi bi-key me-1"></i> {{ __('ui.regenerate_password') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 gap-2">
                    <button type="button" class="btn btn-light border-0 rounded-4 px-4 py-2 fw-semibold text-secondary" data-bs-dismiss="modal" style="background: #f1f5f9;">{{ __('ui.cancel') }}</button>
                    <button type="submit" class="btn btn-primary border-0 rounded-4 px-5 py-2 fw-bold shadow-sm flex-grow-1" style="background: var(--primary-gradient); font-size: 1rem;">
                        {{ __('ui.update_details') }}
                    </button>
                </div>
            </form>
            <form id="regeneratePasswordForm{{ $user->id }}" action="{{ route('rbac.users.regenerate-password', $user) }}" method="POST">
                @csrf
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteUserModal{{ $user->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="{{ route('rbac.users.destroy', $user) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body p-4 text-center">
                    <div class="bg-light-danger rounded-circle p-3 mb-3 d-inline-block">
                        <i class="bi bi-exclamation-triangle fs-1 text-danger"></i>
                    </div>
                    <h5 class="fw-bold mb-2">{{ __('ui.delete_user_question') }}</h5>
                    <p class="text-secondary small mb-0">{{ __('ui.remove_user_confirm', ['name' => $user->full_name]) }}</p>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 flex-column gap-2">
                    <button type="submit" class="btn btn-danger w-100 rounded-3 py-2 fw-bold shadow-sm">{{ __('ui.confirm_delete') }}</button>
                    <button type="button" class="btn btn-light w-100 rounded-3 py-2 fw-bold" data-bs-dismiss="modal">{{ __('ui.cancel') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Roles Modal -->
<div class="modal fade" id="editRolesModal{{ $user->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="{{ route('rbac.users.update-roles', $user) }}" method="POST" class="edit-roles-form" data-user-email="{{ $user->email }}">
                @csrf
                @method('PATCH')
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">{{ __('ui.manage_roles') }}: {{ $user->full_name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    {{-- Warning: shown when admin role selected but user has no email --}}
                    @if(empty($user->email))
                    <div class="alert alert-warning border-0 rounded-3 mb-3 py-2 px-3 d-none email-required-warning" style="background: rgba(245, 158, 11, 0.1);">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                        <span class="small fw-medium">{{ __('ui.admin_email_required') }}</span>
                    </div>
                    @endif
                    <div class="list-group list-group-flush border rounded-3 overflow-hidden">
                        @foreach($roles ?? $user->company->roles ?? [] as $role)
                        <label class="list-group-item d-flex align-items-center gap-3 py-3 cursor-pointer">
                            <input class="form-check-input flex-shrink-0 role-radio-edit" type="radio" name="roles[]" 
                                   value="{{ $role->id }}"
                                   data-slug="{{ $role->slug }}"
                                   {{ $user->roles->contains($role->id) ? 'checked' : '' }}>
                            <div>
                                <strong class="d-block">{{ $role->name }}</strong>
                                <small class="text-muted">{{ $role->description }}</small>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">{{ __('ui.cancel') }}</button>
                    <button type="submit" class="btn text-white rounded-3 px-4 save-roles-btn" style="background: var(--primary-gradient)">
                        {{ __('ui.save_roles') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius: 24px; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(10px);">
            <form action="{{ route('rbac.users.store') }}" method="POST">
                @csrf
                <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="modal-title fw-bold text-success mb-1">Add New Staff Member</h4>
                        <p class="text-muted small mb-0">Create a new account for your team member</p>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" style="background-color: #f8fafc; padding: 0.8rem; border-radius: 50%;"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- Inline validation errors summary --}}
                    @if($errors->any())
                    <div class="alert alert-danger border-0 rounded-4 mb-3 py-2 px-3 d-flex align-items-start gap-2" style="background: rgba(220, 38, 38, 0.08);">
                        <i class="bi bi-exclamation-triangle-fill text-danger mt-1"></i>
                        <div>
                            <ul class="mb-0 ps-3 small fw-medium">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif

                    <div class="row g-4">
                        <!-- Left Column: Personal Info -->
                        <div class="col-md-7">
                            <h6 class="fw-bold text-uppercase small text-muted mb-3"><i class="bi bi-person-badge me-2"></i>{{ __('ui.personal_details') }}</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">{{ __('ui.first_name') }}</label>
                                    <input type="text" name="first_name" class="form-control form-control-lg border-0 bg-light rounded-4 px-3 shadow-none @error('first_name') is-invalid @enderror" style="font-size: 0.9rem;" required placeholder="John" value="{{ old('first_name') }}">
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">{{ __('ui.last_name') }}</label>
                                    <input type="text" name="last_name" class="form-control form-control-lg border-0 bg-light rounded-4 px-3 shadow-none @error('last_name') is-invalid @enderror" style="font-size: 0.9rem;" required placeholder="Doe" value="{{ old('last_name') }}">
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold" id="addStaffUsernameLabel">Username</label>
                                    <input type="text" name="username" id="addStaffUsernameInput" class="form-control form-control-lg border-0 bg-light rounded-4 px-3 shadow-none @error('username') is-invalid @enderror" style="font-size: 0.9rem;" required placeholder="johndoe" value="{{ old('username') }}">
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold" id="addStaffEmailLabel">{{ __('ui.email_address') }} <span class="text-muted fw-normal">({{ __('ui.optional') }})</span></label>
                                    <input type="email" name="email" id="addStaffEmailInput" class="form-control form-control-lg border-0 bg-light rounded-4 px-3 shadow-none @error('email') is-invalid @enderror" style="font-size: 0.9rem;" placeholder="john.doe@example.com" value="{{ old('email') }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 text-start d-flex flex-column">
                                    <label class="form-label small fw-bold">Phone Number</label>
                                    <input type="tel" id="staff_add_phone" class="form-control form-control-lg border-0 bg-light rounded-4 shadow-none" style="font-size: 0.9rem; padding-left: 52px !important;">
                                    <input type="hidden" name="phone" id="hidden_staff_add_phone">
                                    <div class="invalid-feedback text-danger small mt-1" id="staff_add_phone_error" style="display: none;"></div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">Temporary Password</label>
                                    <div class="input-group">
                                        <input type="text" name="password" id="tempPassword" class="form-control form-control-lg border-0 bg-light rounded-start-4 px-3 shadow-none @error('password') is-invalid @enderror" style="font-size: 0.9rem;" required minlength="8">
                                        <button type="button" class="btn btn-light border-0 bg-light rounded-end-4 px-3" onclick="generatePassword()">
                                            <i class="bi bi-arrow-clockwise text-success fs-5"></i>
                                        </button>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Roles -->
                        <div class="col-md-5">
                            <h6 class="fw-bold text-uppercase small text-muted mb-3"><i class="bi bi-shield-check me-2"></i>Assign Roles</h6>
                            <div class="role-grid custom-scrollbar" style="max-height: 300px; overflow-y: auto; padding-right: 5px;">
                                @foreach($roles as $role)
                                <div class="role-card mb-2">
                                    <input class="btn-check role-radio-add" type="radio" name="roles[]" value="{{ $role->id }}" id="roleAdd{{ $role->id }}" data-slug="{{ $role->slug }}" {{ collect(old('roles'))->contains($role->id) ? 'checked' : '' }}>
                                    <label class="btn btn-outline-light text-start w-100 p-3 rounded-4 role-label border-0" for="roleAdd{{ $role->id }}">
                                        <div class="d-flex align-items-center">
                                            <div class="role-icon-box me-3 rounded-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: rgba(34, 197, 94, 0.1); color: #22c55e;">
                                                <i class="bi bi-person-gear"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold text-dark small">{{ $role->name }}</div>
                                            </div>
                                            <div class="check-icon opacity-0">
                                                <i class="bi bi-check-circle-fill text-success"></i>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 gap-2">
                    <button type="button" class="btn btn-light border-0 rounded-4 px-4 py-2 fw-semibold text-secondary" data-bs-dismiss="modal" style="background: #f1f5f9;">Cancel</button>
                    <button type="submit" class="btn btn-success border-0 rounded-4 px-4 py-2 fw-bold shadow-sm flex-grow-1" style="background: var(--primary-gradient); font-size: 1rem;">
                        Create Staff Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .role-label {
        background: #f8fafc;
        transition: all 0.2s ease;
        border: 1px solid transparent !important;
    }
    .role-label:hover {
        background: #f1f5f9;
        transform: translateX(4px);
    }
    .btn-check:checked + .role-label {
        background: rgba(34, 197, 94, 0.08) !important;
        border: 1px solid rgba(34, 197, 94, 0.2) !important;
    }
    .btn-check:checked + .role-label .check-icon {
        opacity: 1 !important;
    }
    .btn-check:checked + .role-label .role-icon-box {
        background: #22c55e !important;
        color: white !important;
    }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .is-invalid {
        border: 1px solid #dc3545 !important;
    }
</style>

<script>
    function generatePassword() {
        const length = 12;
        const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()";
        let retVal = "";
        for (let i = 0, n = charset.length; i < length; ++i) {
            retVal += charset.charAt(Math.floor(Math.random() * n));
        }
        document.getElementById('tempPassword').value = retVal;
    }

    const ADMIN_SLUGS = ['admin', 'super-admin', 'company-admin'];

    // Toggle email required/optional in Add Staff modal based on role selection
    function handleAddStaffRoleChange() {
        const emailLabel = document.getElementById('addStaffEmailLabel');
        const emailInput = document.getElementById('addStaffEmailInput');
        const usernameLabel = document.getElementById('addStaffUsernameLabel');
        const usernameInput = document.getElementById('addStaffUsernameInput');
        const checked = document.querySelector('.role-radio-add:checked');

        if (checked && ADMIN_SLUGS.includes(checked.dataset.slug)) {
            emailLabel.innerHTML = '{{ __("ui.email_address") }} <span class="text-danger fw-normal">({{ __("ui.required_for_admin") }})</span>';
            emailInput.setAttribute('required', 'required');
        } else {
            emailLabel.innerHTML = '{{ __("ui.email_address") }} <span class="text-muted fw-normal">({{ __("ui.optional") }})</span>';
            emailInput.removeAttribute('required');
        }

        if (checked && checked.dataset.slug === 'company-admin') {
            usernameLabel.innerHTML = '{{ __("ui.username") }} <span class="text-muted fw-normal">({{ __("ui.optional") }})</span>';
            usernameInput.removeAttribute('required');
        } else {
            usernameLabel.textContent = 'Username';
            usernameInput.setAttribute('required', 'required');
        }
    }

    // Show/hide email warning in Edit Roles modal
    function handleEditRolesChange(form) {
        const userEmail = form.dataset.userEmail;
        const warning = form.querySelector('.email-required-warning');
        const submitBtn = form.querySelector('.save-roles-btn');
        const checked = form.querySelector('.role-radio-edit:checked');

        if (!warning) return; // User already has email, no warning element

        if (checked && ADMIN_SLUGS.includes(checked.dataset.slug) && !userEmail) {
            warning.classList.remove('d-none');
            submitBtn.setAttribute('disabled', 'disabled');
            submitBtn.style.opacity = '0.5';
        } else {
            warning.classList.add('d-none');
            submitBtn.removeAttribute('disabled');
            submitBtn.style.opacity = '1';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        generatePassword();

        // Initialize Add Staff Phone
        const addPhoneEl = document.getElementById('staff_add_phone');
        let itiAdd;
        if (addPhoneEl) {
            itiAdd = window.intlTelInput(addPhoneEl, {
                initialCountry: "ma",
                preferredCountries: ["ma", "fr", "es"],
                utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/utils.js"
            });
        }

        // Initialize Edit Staff Phone for each modal
        const editItis = {};
        document.querySelectorAll('.staff-edit-phone').forEach(el => {
            const userId = el.dataset.userId;
            const itiEdit = window.intlTelInput(el, {
                initialCountry: "ma",
                preferredCountries: ["ma", "fr", "es"],
                utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/utils.js"
            });
            editItis[userId] = itiEdit;

            // Set default formatted value on load
            const currentVal = el.value.trim();
            if (currentVal) {
                let formattedVal = currentVal;
                if (!formattedVal.startsWith('+') && /^\d+$/.test(formattedVal)) {
                    formattedVal = '+' + formattedVal;
                }
                itiEdit.setNumber(formattedVal);
            }
        });

        // Add submit validator for Add Staff
        const addForm = document.querySelector('#addStaffModal form');
        if (addForm) {
            addForm.addEventListener('submit', function(e) {
                if (addPhoneEl && itiAdd) {
                    const val = addPhoneEl.value.trim();
                    const errorEl = document.getElementById('staff_add_phone_error');
                    errorEl.style.display = 'none';
                    addPhoneEl.classList.remove('is-invalid');

                    if (val) {
                        if (!itiAdd.isValidNumber()) {
                            e.preventDefault();
                            errorEl.textContent = 'Invalid phone number for the selected country.';
                            errorEl.style.display = 'block';
                            addPhoneEl.classList.add('is-invalid');
                            return false;
                        }
                        document.getElementById('hidden_staff_add_phone').value = itiAdd.getNumber().replace(/\D/g, '');
                    } else {
                        document.getElementById('hidden_staff_add_phone').value = '';
                    }
                }
            });
        }

        // Add submit validator for Edit Staff forms
        document.querySelectorAll('[id^="editUserModal"]').forEach(modalEl => {
            const formEl = modalEl.querySelector('form');
            if (formEl) {
                formEl.addEventListener('submit', function(e) {
                    const phoneInput = formEl.querySelector('.staff-edit-phone');
                    if (phoneInput) {
                        const userId = phoneInput.dataset.userId;
                        const itiEdit = editItis[userId];
                        const val = phoneInput.value.trim();
                        const errorEl = document.getElementById(`staff_edit_phone_error_${userId}`);
                        errorEl.style.display = 'none';
                        phoneInput.classList.remove('is-invalid');

                        if (val) {
                            if (!itiEdit.isValidNumber()) {
                                e.preventDefault();
                                errorEl.textContent = 'Invalid phone number for the selected country.';
                                errorEl.style.display = 'block';
                                phoneInput.classList.add('is-invalid');
                                return false;
                            }
                            document.getElementById(`hidden_staff_edit_phone_${userId}`).value = itiEdit.getNumber().replace(/\D/g, '');
                        } else {
                            document.getElementById(`hidden_staff_edit_phone_${userId}`).value = '';
                        }
                    }
                });
            }
        });

        // Listen for role changes in Add Staff modal
        document.querySelectorAll('.role-radio-add').forEach(radio => {
            radio.addEventListener('change', handleAddStaffRoleChange);
        });
        // Run once on load in case old() pre-selected a role
        handleAddStaffRoleChange();

        // Listen for role changes in all Edit Roles modals
        document.querySelectorAll('.edit-roles-form').forEach(form => {
            form.querySelectorAll('.role-radio-edit').forEach(radio => {
                radio.addEventListener('change', () => handleEditRolesChange(form));
            });
            // Run once on load for initial state
            handleEditRolesChange(form);
        });

        // Auto-open the Add Staff modal if there are validation errors
        @if($errors->any())
            const addStaffModal = new bootstrap.Modal(document.getElementById('addStaffModal'));
            addStaffModal.show();
        @endif
    });
</script>
