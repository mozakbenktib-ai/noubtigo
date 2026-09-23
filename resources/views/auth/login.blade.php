<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.login') }} | NoubtiGO</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    @if(app()->getLocale() == 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    @endif

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        :root {
            --primary: #22c55e;
            --primary-dark: #16a34a;
            --primary-light: #4ade80;
            --secondary: #06b6d4;
            --primary-gradient: linear-gradient(135deg, #22c55e, #06b6d4);
            --font: '{{ app()->getLocale() == "ar" ? "Cairo" : "Inter" }}', sans-serif;
        }

        * { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; font-family: var(--font); }

        body {
            background: #F1F5F9;
            display: flex;
            align-items: stretch;
            min-height: 100vh;
        }

        /* ─── AUTH WRAPPER ─────────────────────────────── */
        .auth-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
            animation: fadeInPage 0.55s ease both;
        }

        @keyframes fadeInPage {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ─── LEFT PANEL ───────────────────────────────── */
        .auth-left {
            flex: 0 0 45%;
            max-width: 45%;
            background: linear-gradient(145deg, #15803d 0%, #22c55e 40%, #06b6d4 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 56px 52px;
            position: relative;
            overflow: hidden;
            color: #fff;
        }

        .auth-left::before {
            content: '';
            position: absolute;
            top: -120px; right: -120px;
            width: 420px; height: 420px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
            pointer-events: none;
        }
        .auth-left::after {
            content: '';
            position: absolute;
            bottom: -80px; left: -80px;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            pointer-events: none;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 48px;
            position: relative; z-index: 1;
        }
        .brand-logo img { height: 44px; filter: brightness(0) invert(1); }
        .brand-logo .brand-name {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #fff;
        }

        .left-headline {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: -0.5px;
            margin-bottom: 16px;
            position: relative; z-index: 1;
        }
        .left-desc {
            font-size: 1rem;
            opacity: 0.82;
            line-height: 1.65;
            margin-bottom: 36px;
            position: relative; z-index: 1;
        }

        /* Stat cards */
        .stat-cards {
            display: flex; gap: 10px;
            margin-bottom: 36px;
            position: relative; z-index: 1;
        }
        .stat-card {
            flex: 1;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 12px;
            padding: 14px 12px;
            text-align: center;
        }
        .stat-card .sc-val {
            font-size: 1.4rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 4px;
        }
        .stat-card .sc-label {
            font-size: 0.7rem;
            opacity: 0.7;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        /* Benefits */
        .benefit-list { list-style: none; padding: 0; margin: 0; position: relative; z-index: 1; }
        .benefit-list li {
            display: flex; align-items: center; gap: 12px;
            padding: 9px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            font-size: 0.92rem;
            font-weight: 500;
        }
        .benefit-list li:last-child { border-bottom: none; }
        .benefit-icon {
            width: 26px; height: 26px; border-radius: 50%;
            background: rgba(255,255,255,0.18);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: 0.75rem;
        }

        /* ─── RIGHT PANEL ──────────────────────────────── */
        .auth-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 32px;
            background: #fff;
        }

        .auth-form-wrap {
            width: 100%;
            max-width: 420px;
        }

        .form-header { margin-bottom: 28px; }
        .form-title {
            font-size: 1.65rem;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: -0.4px;
            margin-bottom: 6px;
        }
        .form-subtitle { color: #64748B; font-size: 0.9rem; }

        /* Tabs */
        .auth-tabs {
            display: flex;
            background: #F1F5F9;
            border-radius: 12px;
            padding: 4px;
            margin-bottom: 28px;
        }
        .auth-tab {
            flex: 1;
            text-align: center;
            padding: 9px 12px;
            border-radius: 9px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            color: #64748B;
            text-decoration: none;
            transition: all 0.2s;
        }
        .auth-tab.active {
            background: #fff;
            color: var(--primary-dark);
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        }
        .auth-tab:hover:not(.active) { color: var(--primary-dark); }

        /* Form fields */
        .form-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-control {
            border-radius: 10px;
            padding: 11px 14px;
            border: 1.5px solid #E2E8F0;
            background: #F8FAFC;
            font-size: 0.9rem;
            color: #0F172A;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(34,197,94,0.12);
            background: #fff;
            outline: none;
        }
        .form-control::placeholder { color: #94A3B8; }

        .input-group .input-group-text {
            background: #F8FAFC;
            border: 1.5px solid #E2E8F0;
            border-radius: 10px 0 0 10px;
            color: #94A3B8;
            font-size: 0.9rem;
        }
        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        .input-group:focus-within .input-group-text {
            border-color: var(--primary);
            color: var(--primary);
        }

        /* Password toggle */
        .pwd-wrap { position: relative; }
        .pwd-toggle {
            position: absolute; top: 50%; right: 12px;
            transform: translateY(-50%);
            cursor: pointer; color: #94A3B8; font-size: 0.95rem; z-index: 5;
            transition: color 0.2s;
        }
        .pwd-toggle:hover { color: var(--primary-dark); }
        .pwd-wrap .form-control { padding-right: 38px; }

        /* Remember row */
        .remember-row {
            display: flex; align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .remember-row .form-check-label { font-size: 0.82rem; color: #64748B; }
        .remember-row .form-check-input {
            width: 15px; height: 15px;
            border: 1.5px solid #CBD5E1;
            border-radius: 4px; cursor: pointer;
        }
        .remember-row .form-check-input:checked {
            background-color: var(--primary); border-color: var(--primary);
        }
        .forgot-link {
            font-size: 0.82rem; color: var(--primary-dark);
            font-weight: 600; text-decoration: none;
        }
        .forgot-link:hover { text-decoration: underline; }

        /* CTA button */
        .btn-cta {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%;
            background: var(--primary-gradient);
            color: #fff; border: none; border-radius: 11px;
            padding: 13px 20px; font-size: 0.95rem; font-weight: 700;
            letter-spacing: 0.2px; cursor: pointer;
            transition: transform 0.18s, box-shadow 0.18s;
            box-shadow: 0 4px 14px rgba(34,197,94,0.35);
        }
        .btn-cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(34,197,94,0.45);
        }
        .btn-cta:active { transform: translateY(0); }

        /* Google button */
        .btn-google {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            width: 100%; background: #fff; color: #374151;
            border: 1.5px solid #E2E8F0; border-radius: 11px;
            padding: 11px 20px; font-size: 0.9rem; font-weight: 600;
            cursor: pointer; text-decoration: none;
            transition: background 0.18s, border-color 0.18s, box-shadow 0.18s;
        }
        .btn-google:hover {
            background: #F8FAFC; border-color: #CBD5E1;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06); color: #374151;
        }
        .btn-google svg { width: 18px; height: 18px; }

        /* Divider */
        .or-divider {
            display: flex; align-items: center; gap: 12px;
            color: #94A3B8; font-size: 0.8rem; font-weight: 500;
            margin: 16px 0;
        }
        .or-divider::before, .or-divider::after {
            content: ''; flex: 1; height: 1px; background: #E2E8F0;
        }

        /* Customer portal */
        .portal-card {
            background: #F8FAFC;
            border: 1.5px solid #E2E8F0;
            border-radius: 11px;
            padding: 14px 16px;
            display: flex; align-items: center; gap: 12px;
            margin-top: 16px;
            text-decoration: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .portal-card:hover {
            border-color: var(--primary);
            box-shadow: 0 2px 8px rgba(34,197,94,0.12);
        }
        .portal-card-icon {
            width: 36px; height: 36px; border-radius: 9px;
            background: #f0fdf4; color: var(--primary-dark);
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; flex-shrink: 0;
        }
        .portal-card-text .pct-label {
            font-size: 0.78rem; color: #94A3B8; margin-bottom: 1px;
        }
        .portal-card-text .pct-action {
            font-size: 0.88rem; font-weight: 700; color: var(--primary-dark);
        }
        .portal-card-arrow { margin-left: auto; color: #CBD5E1; font-size: 0.9rem; }

        /* Sign-up link */
        .signup-link {
            text-align: center; font-size: 0.85rem;
            color: #64748B; margin-top: 20px;
        }
        .signup-link a {
            color: var(--primary-dark); font-weight: 700; text-decoration: none;
        }
        .signup-link a:hover { text-decoration: underline; }

        /* Alert */
        .alert {
            border-radius: 10px; font-size: 0.86rem;
            padding: 12px 14px; margin-bottom: 18px;
        }

        /* ─── RESPONSIVE ───────────────────────────────── */
        @media (max-width: 991px) {
            .auth-left { display: none; }
            .auth-right { padding: 40px 20px; }
        }
        @media (max-width: 480px) {
            .auth-right { padding: 28px 16px; }
        }

        /* RTL support */
        [dir="rtl"] .pwd-toggle { right: auto; left: 12px; }
        [dir="rtl"] .pwd-wrap .form-control { padding-right: 14px; padding-left: 38px !important; }
        [dir="rtl"] .input-group .input-group-text { border-radius: 0 10px 10px 0; }
        [dir="rtl"] .input-group .form-control { border-left: 1.5px solid #E2E8F0; border-right: none; border-radius: 10px 0 0 10px; }
        [dir="rtl"] .portal-card-arrow { margin-left: 0; margin-right: auto; transform: scaleX(-1); }
    </style>
</head>

<body>
<div class="auth-wrapper">

    <!-- ══════════════ LEFT PANEL ══════════════ -->
    <div class="auth-left">

        <div class="brand-logo">
            <img src="{{ asset('images/ng_logo.png') }}" alt="NoubtiGO Logo">
            <span class="brand-name">NoubtiGO</span>
        </div>

        <h1 class="left-headline">{!! __('ui.auth_welcome_back_title') !!}</h1>
        <p class="left-desc">{{ __('ui.auth_welcome_back_desc') }}</p>

        <!-- Stats -->
        <div class="stat-cards">
            <div class="stat-card">
                <div class="sc-val">500+</div>
                <div class="sc-label">{{ __('ui.businesses') }}</div>
            </div>
            <div class="stat-card">
                <div class="sc-val">2M+</div>
                <div class="sc-label">{{ __('ui.tickets_served') }}</div>
            </div>
            <div class="stat-card">
                <div class="sc-val">98%</div>
                <div class="sc-label">{{ __('ui.satisfaction') }}</div>
            </div>
        </div>

        <!-- Benefits -->
        <ul class="benefit-list">
            <li>
                <div class="benefit-icon"><i class="bi bi-people-fill"></i></div>
                {{ __('ui.smart_queue_management') }}
            </li>
            <li>
                <div class="benefit-icon"><i class="bi bi-calendar-check-fill"></i></div>
                {{ __('ui.appointment_scheduling') }}
            </li>
            <li>
                <div class="benefit-icon"><i class="bi bi-graph-up-arrow"></i></div>
                {{ __('ui.real_time_tracking') }}
            </li>
            <li>
                <div class="benefit-icon"><i class="bi bi-translate"></i></div>
                {{ __('ui.multi_language_support') }}
            </li>
        </ul>

    </div>

    <!-- ══════════════ RIGHT PANEL ══════════════ -->
    <div class="auth-right">
        <div class="auth-form-wrap">

            <!-- Top bar: Mobile logo & Language Switcher -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex d-lg-none align-items-center gap-2">
                    <img src="{{ asset('images/ng_logo.png') }}" alt="NoubtiGO" style="height:36px;">
                    <span style="font-size:1.15rem;font-weight:800;color:#0F172A;">NoubtiGO</span>
                </div>
                <div class="ms-auto">
                    <form action="{{ route('language.switch') }}" method="POST">
                        @csrf
                        <div class="d-flex align-items-center gap-1 bg-light border rounded-pill px-2 py-1 shadow-none">
                            <i class="bi bi-globe text-muted small ms-1"></i>
                            <select name="locale" onchange="this.form.submit()" class="form-select form-select-sm border-0 bg-transparent fw-semibold py-0 ps-1 pe-4" style="font-size: 0.8rem; cursor: pointer; box-shadow: none;">
                                <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>EN</option>
                                <option value="fr" {{ app()->getLocale() == 'fr' ? 'selected' : '' }}>FR</option>
                                <option value="ar" {{ app()->getLocale() == 'ar' ? 'selected' : '' }}>العربية</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tab switcher -->
            <div class="auth-tabs">
                <a href="{{ route('login') }}" class="auth-tab active">{{ __('ui.sign_in') }}</a>
                <a href="{{ route('register') }}" class="auth-tab">{{ __('ui.create_account') }}</a>
            </div>

            <!-- Header -->
            <div class="form-header">
                <h2 class="form-title">{{ __('ui.welcome_back_title') }}</h2>
                <p class="form-subtitle">{{ __('ui.login_to_dashboard') }}</p>
            </div>

            <!-- Alerts -->
            @if($errors->any())
                <div class="alert alert-danger border-0">
                    <ul class="mb-0 ps-3 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success border-0">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('login') }}" method="POST">
                @csrf

                <!-- Username / Email -->
                <div class="mb-3">
                    <label class="form-label">{{ __('ui.username_or_email') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="login" class="form-control"
                               placeholder="{{ __('ui.username_or_email_placeholder') }}"
                               value="{{ old('login') }}" required>
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0">{{ __('ui.password') }}</label>
                        <a href="#forgotPasswordModal" class="forgot-link" data-bs-toggle="modal">{{ __('ui.forgot_password') }}</a>
                    </div>
                    <div class="pwd-wrap">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="loginPwd"
                                   class="form-control" placeholder="••••••••" required
                                   style="border-radius: 0 10px 10px 0; padding-right: 38px;">
                        </div>
                        <span class="pwd-toggle" onclick="togglePwd('loginPwd', this)" style="z-index: 10;">
                            <i class="bi bi-eye"></i>
                        </span>
                    </div>
                </div>

                <!-- Remember -->
                <div class="remember-row">
                    <div class="form-check d-flex align-items-center gap-2 mb-0">
                        <input type="checkbox" name="remember" id="remember" class="form-check-input mt-0">
                        <label for="remember" class="form-check-label">{{ __('ui.stay_logged_in') }}</label>
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-cta mb-3">
                    <i class="bi bi-box-arrow-in-right"></i>
                    {{ __('ui.login') }}
                </button>

                <!-- Or divider -->
                <div class="or-divider">{{ __('ui.or_continue_with') }}</div>

                <!-- Google -->
                <a href="{{ route('auth.google') }}" class="btn-google">
                    <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.31-8.16 2.31-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                    </svg>
                    {{ __('ui.continue_with_google') }}
                </a>

            </form>

            <!-- Customer portal link -->
            <a href="{{ route('customer.login') }}" class="portal-card">
                <div class="portal-card-icon"><i class="bi bi-person-circle"></i></div>
                <div class="portal-card-text">
                    <div class="pct-label">{{ __('ui.looking_for_customer_portal') }}</div>
                    <div class="pct-action">{{ __('ui.login_as_customer') }}</div>
                </div>
                <div class="portal-card-arrow"><i class="bi bi-chevron-right"></i></div>
            </a>

            <!-- Sign-up link -->
            <p class="signup-link">
                {{ __('ui.dont_have_account') }}
                <a href="{{ route('register') }}">{{ __('ui.create_one_free') }}</a>
            </p>

        </div>
    </div>

</div>

<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
            <form action="{{ route('password.email') }}" method="POST">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="forgotPasswordModalLabel">{{ __('ui.reset_your_password') }}</h5>
                        <p class="text-muted small mb-0">{{ __('ui.enter_email_reset_link') }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <label for="resetEmail" class="form-label">{{ __('ui.email_address') }}</label>
                    <input type="email" class="form-control" id="resetEmail" name="email" value="{{ old('email') }}" required autocomplete="email">
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('ui.cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ __('ui.send_reset_link') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function togglePwd(id, el) {
        const input = document.getElementById(id);
        const icon  = el.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    }
</script>
</body>
</html>
