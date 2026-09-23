<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.signup') }} | NoubtiGO</title>

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

    <!-- intl-tel-input -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/css/intlTelInput.css">

    <style>
        :root {
            --primary: #22c55e;
            --primary-dark: #16a34a;
            --primary-light: #4ade80;
            --primary-xlight: #f0fdf4;
            --secondary: #06b6d4;
            --primary-gradient: linear-gradient(135deg, #22c55e, #06b6d4);
            --font: '{{ app()->getLocale() == "ar" ? "Cairo" : "Inter" }}', sans-serif;
        }

        * { box-sizing: border-box; }

        html, body {
            height: 100%;
            margin: 0;
            font-family: var(--font);
        }

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
            position: relative;
            z-index: 1;
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

        /* Dashboard mockup */
        .dashboard-mockup {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 36px;
            backdrop-filter: blur(6px);
            position: relative; z-index: 1;
        }
        .mockup-bar {
            display: flex; align-items: center; gap: 6px; margin-bottom: 12px;
        }
        .mockup-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: rgba(255,255,255,0.4);
        }
        .mockup-dot.active { background: rgba(255,255,255,0.9); }
        .mockup-row {
            display: flex; gap: 8px; margin-bottom: 8px;
        }
        .mockup-card {
            flex: 1;
            background: rgba(255,255,255,0.15);
            border-radius: 8px;
            padding: 10px 12px;
        }
        .mockup-card .mc-label {
            font-size: 0.62rem; opacity: 0.7; text-transform: uppercase; letter-spacing: .5px;
            margin-bottom: 4px;
        }
        .mockup-card .mc-val {
            font-size: 1.05rem; font-weight: 700;
        }
        .mockup-bar-chart {
            display: flex; align-items: flex-end; gap: 4px; height: 40px;
            background: rgba(255,255,255,0.08); border-radius: 8px; padding: 6px 8px;
        }
        .mbc-bar {
            flex: 1; border-radius: 3px 3px 0 0;
            background: rgba(255,255,255,0.35);
            transition: height 0.3s;
        }
        .mbc-bar.highlight { background: rgba(255,255,255,0.85); }

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
            max-width: 480px;
        }

        .auth-form-wrap .form-header {
            margin-bottom: 28px;
        }
        .form-header .form-title {
            font-size: 1.65rem;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: -0.4px;
            margin-bottom: 6px;
        }
        .form-header .form-subtitle {
            color: #64748B;
            font-size: 0.9rem;
        }

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
            position: absolute;
            top: 50%; right: 12px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #94A3B8;
            font-size: 0.95rem;
            z-index: 5;
            transition: color 0.2s;
        }
        .pwd-toggle:hover { color: var(--primary-dark); }
        .pwd-wrap .form-control { padding-right: 38px; }

        /* Row split */
        .field-row { display: flex; gap: 14px; }
        .field-row > div { flex: 1; }

        /* Terms checkbox */
        .terms-check { display: flex; align-items: flex-start; gap: 10px; }
        .terms-check .form-check-input {
            width: 16px; height: 16px; margin-top: 2px;
            border: 1.5px solid #CBD5E1;
            border-radius: 4px; flex-shrink: 0;
            cursor: pointer;
        }
        .terms-check .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        .terms-check label { font-size: 0.8rem; color: #64748B; cursor: pointer; }
        .terms-check a { color: var(--primary-dark); text-decoration: none; font-weight: 600; }

        /* CTA button */
        .btn-cta {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%;
            background: var(--primary-gradient);
            color: #fff;
            border: none;
            border-radius: 11px;
            padding: 13px 20px;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.2px;
            cursor: pointer;
            transition: transform 0.18s, box-shadow 0.18s, opacity 0.18s;
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
            width: 100%;
            background: #fff;
            color: #374151;
            border: 1.5px solid #E2E8F0;
            border-radius: 11px;
            padding: 11px 20px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.18s, border-color 0.18s, box-shadow 0.18s;
            text-decoration: none;
        }
        .btn-google:hover {
            background: #F8FAFC;
            border-color: #CBD5E1;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            color: #374151;
        }
        .btn-google svg { width: 18px; height: 18px; }

        /* Divider */
        .or-divider {
            display: flex; align-items: center; gap: 12px;
            color: #94A3B8; font-size: 0.8rem; font-weight: 500;
            margin: 16px 0;
        }
        .or-divider::before, .or-divider::after {
            content: ''; flex: 1;
            height: 1px; background: #E2E8F0;
        }

        /* Sign-in link */
        .signin-link {
            text-align: center;
            font-size: 0.85rem;
            color: #64748B;
            margin-top: 20px;
        }
        .signin-link a {
            color: var(--primary-dark);
            font-weight: 700;
            text-decoration: none;
        }
        .signin-link a:hover { text-decoration: underline; }

        /* intl-tel-input overrides */
        .iti { width: 100%; }
        .iti__country-list {
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 1px solid #E2E8F0;
        }
        .iti__selected-flag {
            background: #F8FAFC;
            border-radius: 10px 0 0 10px;
        }

        /* Alert */
        .alert {
            border-radius: 10px;
            font-size: 0.86rem;
            padding: 12px 14px;
            margin-bottom: 18px;
        }

        /* Plan badge */
        .plan-badge {
            background: var(--primary-xlight);
            border: 1.5px solid rgba(34,197,94,0.3);
            border-radius: 10px;
            padding: 12px 14px;
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 18px;
        }
        .plan-badge-icon {
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--primary); color: #fff;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: 0.85rem;
        }
        .plan-badge-text h6 { font-size: 0.85rem; font-weight: 700; color: var(--primary-dark); margin: 0 0 2px; }
        .plan-badge-text p  { font-size: 0.75rem; color: var(--secondary); margin: 0; }

        /* ─── RESPONSIVE ───────────────────────────────── */
        @media (max-width: 991px) {
            .auth-left {
                display: none;
            }
            .auth-right {
                padding: 40px 20px;
            }
        }

        @media (max-width: 480px) {
            .auth-right { padding: 28px 16px; }
            .left-headline { font-size: 1.6rem; }
        }

        /* RTL support */
        [dir="rtl"] .pwd-toggle { right: auto; left: 12px; }
        [dir="rtl"] .pwd-wrap .form-control { padding-right: 14px; padding-left: 38px !important; }
        [dir="rtl"] .input-group .input-group-text { border-radius: 0 10px 10px 0; }
        [dir="rtl"] .input-group .form-control { border-left: 1.5px solid #E2E8F0; border-right: none; border-radius: 10px 0 0 10px; }
        [dir="rtl"] .iti__selected-flag { border-radius: 0 10px 10px 0; }
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

        <h1 class="left-headline">{!! __('ui.auth_register_headline') !!}</h1>
        <p class="left-desc">{{ __('ui.auth_register_desc') }}</p>

        <!-- Dashboard Mockup -->
        <div class="dashboard-mockup">
            <div class="mockup-bar">
                <div class="mockup-dot active"></div>
                <div class="mockup-dot"></div>
                <div class="mockup-dot"></div>
                <span style="font-size:.65rem;opacity:.6;margin-left:6px;">NoubtiGO {{ __('ui.dashboard') }}</span>
            </div>
            <div class="mockup-row">
                <div class="mockup-card">
                    <div class="mc-label">{{ __('ui.queue') }}</div>
                    <div class="mc-val">24 <small style="font-size:.7rem;opacity:.7">{{ __('ui.waiting') }}</small></div>
                </div>
                <div class="mockup-card">
                    <div class="mc-label">{{ __('ui.served') }}</div>
                    <div class="mc-val">127</div>
                </div>
                <div class="mockup-card">
                    <div class="mc-label">{{ __('ui.avg_wait') }}</div>
                    <div class="mc-val">8<small style="font-size:.7rem;opacity:.7">m</small></div>
                </div>
            </div>
            <div class="mockup-bar-chart">
                <div class="mbc-bar" style="height:45%"></div>
                <div class="mbc-bar" style="height:65%"></div>
                <div class="mbc-bar" style="height:55%"></div>
                <div class="mbc-bar highlight" style="height:90%"></div>
                <div class="mbc-bar" style="height:70%"></div>
                <div class="mbc-bar" style="height:50%"></div>
                <div class="mbc-bar" style="height:80%"></div>
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
                <a href="{{ route('login') }}" class="auth-tab">{{ __('ui.sign_in') }}</a>
                <a href="{{ route('register') }}" class="auth-tab active">{{ __('ui.create_account') }}</a>
            </div>

            <!-- Header -->
            <div class="form-header">
                <h2 class="form-title">{{ __('ui.create_account') }}</h2>
                <p class="form-subtitle">{{ __('ui.register_subtitle') }}</p>
            </div>

            <!-- Alerts -->
            @if(session('error'))
                <div class="alert alert-danger border-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger border-0">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Plan badge -->
            @if(request('plan'))
                <div class="plan-badge">
                    <div class="plan-badge-icon"><i class="bi bi-star-fill"></i></div>
                    <div class="plan-badge-text">
                        <h6>{{ __('ui.selected_plan', ['plan' => ucfirst(request('plan'))]) }}</h6>
                        <p>{{ __('ui.selected_plan_help') }}</p>
                    </div>
                </div>
                <input type="hidden" name="plan" value="{{ request('plan') }}">
            @endif

            <!-- Form -->
            <form action="{{ route('register') }}" method="POST" id="registerForm">
                @csrf

                @php
                    $promoCode = request('promo', request('promo_code', request('coupon', request('coupon_code', request('code')))));
                @endphp
                @if($promoCode)
                    <input type="hidden" name="promo_code" value="{{ $promoCode }}">
                @endif

                <!-- Name row -->
                <div class="field-row mb-3">
                    <div>
                        <label class="form-label">{{ __('ui.first_name') }}</label>
                        <input type="text" name="first_name" class="form-control"
                               placeholder="John" value="{{ old('first_name') }}" required>
                    </div>
                    <div>
                        <label class="form-label">{{ __('ui.last_name') }}</label>
                        <input type="text" name="last_name" class="form-control"
                               placeholder="Doe" value="{{ old('last_name') }}" required>
                    </div>
                </div>

                <!-- Company -->
                <div class="mb-3">
                    <label class="form-label">{{ __('ui.company_name') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-building"></i></span>
                        <input type="text" name="company_name" class="form-control"
                               placeholder="Acme Corp" value="{{ old('company_name') }}" required>
                    </div>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label class="form-label">{{ __('ui.email_address') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control"
                               placeholder="you@company.com" value="{{ old('email') }}" required>
                    </div>
                </div>

                <!-- Phone -->
                <div class="mb-3">
                    <label class="form-label">{{ __('ui.phone_number') }}</label>
                    <input type="tel" id="phone" name="phone_input" class="form-control" required>
                    <input type="hidden" name="phone" id="hidden_phone">
                    <div class="text-danger small mt-1" id="phone_error" style="display:none;"></div>
                </div>

                <!-- Password row -->
                <div class="field-row mb-3">
                    <div>
                        <label class="form-label">{{ __('ui.password') }}</label>
                        <div class="pwd-wrap">
                            <input type="password" name="password" id="pwd1"
                                   class="form-control" placeholder="••••••••" required>
                            <span class="pwd-toggle" onclick="togglePwd('pwd1', this)">
                                <i class="bi bi-eye"></i>
                            </span>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">{{ __('ui.confirm_password') }}</label>
                        <div class="pwd-wrap">
                            <input type="password" name="password_confirmation" id="pwd2"
                                   class="form-control" placeholder="••••••••" required>
                            <span class="pwd-toggle" onclick="togglePwd('pwd2', this)">
                                <i class="bi bi-eye"></i>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Terms -->
                <div class="terms-check mb-4">
                    <input type="checkbox" class="form-check-input" id="terms" required>
                    <label for="terms">
                        {!! __('ui.i_agree_terms', ['terms' => '#', 'privacy' => '#']) !!}
                    </label>
                </div>

                <input type="hidden" name="timezone" id="timezoneInput">

                <!-- Submit -->
                <button type="submit" class="btn-cta mb-3">
                    <i class="bi bi-rocket-takeoff-fill"></i>
                    {{ __('ui.get_started') }}
                </button>

                <!-- Or divider -->
                <div class="or-divider">{{ __('ui.or_sign_up_with') }}</div>

                <!-- Google -->
                <a href="{{ route('auth.google') }}" class="btn-google mb-2">
                    <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.31-8.16 2.31-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                    </svg>
                    {{ __('ui.continue_with_google') }}
                </a>

            </form>

            <!-- Sign-in link -->
            <p class="signin-link">
                {{ __('ui.already_have_account') }}
                <a href="{{ route('login') }}">{{ __('ui.login') }}</a>
            </p>

        </div>
    </div>

</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/intlTelInput.min.js"></script>
<script>
    // Password toggle
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

    // intlTelInput
    let iti;
    const phoneEl      = document.getElementById('phone');
    const hiddenPhone  = document.getElementById('hidden_phone');
    const phoneErrorEl = document.getElementById('phone_error');
    const form         = document.getElementById('registerForm');

    document.addEventListener('DOMContentLoaded', function () {
        if (phoneEl) {
            iti = window.intlTelInput(phoneEl, {
                initialCountry: 'ma',
                preferredCountries: ['ma', 'fr', 'es'],
                utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/utils.js'
            });
        }
    });

    if (form) {
        form.addEventListener('submit', function (e) {
            if (phoneEl && iti) {
                phoneErrorEl.style.display = 'none';
                phoneEl.classList.remove('is-invalid');
                if (!iti.isValidNumber()) {
                    e.preventDefault();
                    phoneErrorEl.textContent = '{{ __("ui.phone_error_invalid") }}';
                    phoneErrorEl.style.display = 'block';
                    phoneEl.classList.add('is-invalid');
                    return false;
                }
                hiddenPhone.value = iti.getNumber().replace(/\D/g, '');
            }
        });
    }

    // Timezone auto-detect
    try {
        const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
        if (tz) document.getElementById('timezoneInput').value = tz;
    } catch(e) {
        document.getElementById('timezoneInput').value = 'UTC';
    }
</script>
</body>
</html>
