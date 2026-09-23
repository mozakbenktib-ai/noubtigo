<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.signup_to_portal') }} - Noubtigo</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    @if(app()->getLocale() == 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    @endif

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/css/intlTelInput.css">

    <style>
        .iti {
            width: 100% !important;
            display: block !important;
        }
        .iti__country-list {
            z-index: 1060 !important;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 1px solid #eee;
        }
        :root {
            --primary-gradient: linear-gradient(135deg, #22c55e, #06b6d4);
            --text-main: #1e293b;
        }

        body {
            font-family: {{ app()->getLocale() == 'ar' ? "'Cairo', sans-serif" : "'Outfit', sans-serif" }};
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 480px;
            overflow: hidden;
        }

        .auth-header {
            background: var(--primary-gradient);
            padding: 30px;
            text-align: center;
            color: white;
        }

        .auth-body {
            padding: 30px;
        }

        .form-control {
            border-radius: 12px;
            padding: 10px 16px;
            border: 1px solid #e2e8f0;
            font-weight: 500;
        }

        .form-control:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.1);
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(34, 197, 94, 0.2);
        }

        .auth-footer {
            text-align: center;
            padding-bottom: 30px;
        }
    </style>
</head>

<body>

    <div class="auth-card">
        <div class="auth-header">
            <div class="mb-2">
                <img src="{{ asset('images/ng_logo.png') }}" alt="Logo" height="40" style="filter: brightness(0) invert(1);">
            </div>
            <h4 class="fw-bold mb-0">{{ __('ui.signup_to_portal') }}</h4>
        </div>

        <div class="auth-body">
            @if($errors->any())
                <div class="alert alert-danger border-0 rounded-3 small">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('customer.register') }}" method="POST">
                @csrf
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">{{ __('ui.first_name') }}</label>
                        <input type="text" name="first_name" class="form-control" placeholder="John" value="{{ old('first_name') }}" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">{{ __('ui.last_name') }}</label>
                        <input type="text" name="last_name" class="form-control" placeholder="Doe" value="{{ old('last_name') }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">{{ __('ui.email') }}</label>
                    <input type="email" name="email" class="form-control" placeholder="name@example.com" value="{{ old('email') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold d-block">{{ __('ui.phone_number') }}</label>
                    <input type="tel" id="customer_phone" class="form-control w-100" placeholder="+212 ..." value="{{ old('phone') }}" required>
                    <input type="hidden" name="phone" id="hidden_customer_phone" value="{{ old('phone') }}">
                    <div id="customer_phone_error" class="text-danger small mt-1 d-none">Invalid phone number</div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">{{ __('ui.password') }}</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">{{ __('ui.confirm_password') }}</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    {{ __('ui.signup') }}
                </button>
            </form>
        </div>

        <div class="auth-footer">
            <p class="small text-muted mb-0">
                {{ __('ui.already_have_account') }} 
                <a href="{{ route('customer.login') }}" class="text-success fw-bold text-decoration-none">{{ __('ui.login') }}</a>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/intlTelInput.min.js"></script>
    <script>
        let itiCustomer;
        const customerPhoneEl = document.getElementById('customer_phone');
        const hiddenCustomerPhoneEl = document.getElementById('hidden_customer_phone');

        document.addEventListener("DOMContentLoaded", function() {
            if (customerPhoneEl) {
                let phoneVal = customerPhoneEl.value.trim();
                if (phoneVal && !phoneVal.startsWith('+') && /^\d+$/.test(phoneVal)) {
                    phoneVal = '+' + phoneVal;
                    customerPhoneEl.value = phoneVal;
                }

                itiCustomer = window.intlTelInput(customerPhoneEl, {
                    initialCountry: "ma",
                    preferredCountries: ["ma", "fr", "es"],
                    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/utils.js"
                });

                const form = customerPhoneEl.closest('form');
                form.addEventListener('submit', function(e) {
                    const errorEl = document.getElementById('customer_phone_error');
                    errorEl.classList.add('d-none');
                    customerPhoneEl.classList.remove('is-invalid');

                    const val = customerPhoneEl.value.trim();
                    if (val) {
                        if (itiCustomer && !itiCustomer.isValidNumber()) {
                            e.preventDefault();
                            errorEl.textContent = 'Invalid phone number for the selected country.';
                            errorEl.classList.remove('d-none');
                            customerPhoneEl.classList.add('is-invalid');
                            return;
                        }
                        hiddenCustomerPhoneEl.value = itiCustomer ? itiCustomer.getNumber().replace(/\D/g, '') : val.replace(/\D/g, '');
                    } else {
                        hiddenCustomerPhoneEl.value = '';
                    }
                });
            }
        });
    </script>
</body>

</html>
