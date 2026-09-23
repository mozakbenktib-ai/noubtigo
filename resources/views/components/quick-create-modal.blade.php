<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/css/intlTelInput.css">
<style>
    .iti {
        display: block;
        width: 100%;
    }
    .iti__country-list {
        z-index: 1060 !important;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/intlTelInput.min.js"></script>

<!-- Quick Create Customer Modal -->
<div class="modal fade" id="quickCreateCustomerModal" tabindex="-1" aria-labelledby="quickCreateCustomerModalLabel" aria-hidden="true" style="z-index: 1056;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                        <i class="bi bi-person-plus-fill fs-5"></i>
                    </div>
                    <h5 class="modal-title fw-bold" id="quickCreateCustomerModalLabel">{{ __('ui.add_customer') }}</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-danger d-none" id="qc-error-alert"></div>
                <form id="quick-create-customer-form">
                    @csrf
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('ui.first_name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('ui.last_name') }}</label>
                            <input type="text" class="form-control" name="last_name">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">{{ __('ui.phone_number') }}</label>
                            <input type="tel" class="form-control" name="phone" id="qc-phone">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">{{ __('ui.customer_identifier') ?? 'Unique Identifier' }}</label>
                            <input type="text" class="form-control" name="identifier" placeholder="{{ __('ui.customer_identifier_placeholder') ?? 'ID...' }}">
                        </div>
                        <div class="col-12">
                            <div class="alert alert-warning border-0 rounded-3 small p-2 mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-exclamation-triangle-fill flex-shrink-0 text-warning"></i>
                                <span>{{ __('ui.no_phone_warning') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm border-0 text-white" id="qc-submit-btn" style="background: var(--primary-gradient, linear-gradient(135deg, #3b82f6, #2563eb));">
                            <i class="bi bi-save me-2"></i> {{ __('ui.save_customer') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('quickCreateCustomerModal');
    const modal = new bootstrap.Modal(modalEl);
    const form = document.getElementById('quick-create-customer-form');
    const errorAlert = document.getElementById('qc-error-alert');
    const submitBtn = document.getElementById('qc-submit-btn');
    const phoneInput = document.getElementById('qc-phone');
    
    let activeCallback = null;
    let iti = null;

    if (typeof window.intlTelInput !== 'undefined' && phoneInput) {
        iti = window.intlTelInput(phoneInput, {
            initialCountry: "ma",
            preferredCountries: ["ma", "fr", "us"],
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/utils.js"
        });
    }

    // Make trigger open function global
    window.openQuickCreateModal = function(callback) {
        activeCallback = callback;
        form.reset();
        if (iti) {
            iti.setCountry("ma");
        }
        errorAlert.classList.add('d-none');
        modal.show();
    };

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        submitBtn.disabled = true;
        const origHTML = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';
        errorAlert.classList.add('d-none');

        const formData = new FormData(form);
        const dataObj = Object.fromEntries(formData);
        if (!dataObj.is_vip) dataObj.is_vip = 0;

        if (iti) {
            const rawPhone = phoneInput.value.trim();
            if (rawPhone) {
                if (!iti.isValidNumber()) {
                    errorAlert.textContent = 'Please enter a valid phone number.';
                    errorAlert.classList.remove('d-none');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = origHTML;
                    return;
                }
                dataObj.phone = iti.getNumber();
            } else {
                dataObj.phone = '';
            }
        }

        fetch('{{ route("customers.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(dataObj)
        })
        .then(response => response.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = origHTML;

            if (data.success && data.customer) {
                modal.hide();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Success!',
                        text: '{{ __("ui.customer_added_success") }}',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
                
                if (activeCallback) {
                    activeCallback(data.customer);
                }
            } else {
                errorAlert.textContent = data.message || 'Error creating customer';
                errorAlert.classList.remove('d-none');
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = origHTML;
            errorAlert.textContent = 'A server error occurred. Please verify your fields.';
            errorAlert.classList.remove('d-none');
            console.error('Quick create error:', err);
        });
    });
});
</script>
