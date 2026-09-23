<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Premium Dashboard')</title>
    
    <!-- PWA Settings -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#22c55e">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Noubtigo">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-512x512.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    @if(app()->getLocale() == 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    @endif
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}">
    
    @vite(['resources/js/app.js'])
    
    <style>
        :root {
            @php
                $tenant = app(\App\Services\TenantManager::class)->getTenant();
                $branding = $tenant ? $tenant->getBrandingColors() : [
                    'primary' => '#22c55e',
                    'secondary' => '#06b6d4',
                    'gradient' => 'linear-gradient(135deg, #22c55e, #06b6d4)'
                ];
            @endphp
            --primary-color: {{ $branding['primary'] }};
            --secondary-color: {{ $branding['secondary'] }};
            --primary-gradient: {{ $branding['gradient'] }};
        }
        
        @if(app()->getLocale() == 'ar')
        body { font-family: 'Cairo', sans-serif; }
        [dir="rtl"] .sidebar { left: auto; right: 0; border-right: none; border-left: 1px solid rgba(255, 255, 255, 0.05); z-index: 1040 !important; }
        [dir="rtl"] .main-content { margin-left: 0; margin-right: var(--sidebar-width); }
        [dir="rtl"] .sidebar .nav-link i { margin-right: 0; margin-left: 12px; }
        [dir="rtl"] .navbar-search-wrapper { direction: rtl; }
        [dir="rtl"] .breadcrumb { direction: rtl; }
        [dir="rtl"] .offcanvas-end { left: 0 !important; right: auto !important; border-right: 1px solid rgba(0,0,0,0.1) !important; border-left: none !important; }
        @endif

        /* ── Navbar Search Bar ── */
        .search-bar {
            background: rgba(0,0,0,0.04);
            border-radius: 12px;
            padding: 7px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            max-width: 100%;
            border: 1px solid rgba(0,0,0,0.06);
            transition: var(--transition-base);
            cursor: text;
        }
        .search-bar:focus-within {
            background: white;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
        }
        .search-bar input {
            border: none;
            background: transparent;
            outline: none;
            width: 100%;
            font-size: 0.85rem;
            color: var(--text-main);
        }
        .search-bar input::placeholder { color: #94a3b8; }

        [data-bs-theme="dark"] .search-bar {
            background: rgba(255,255,255,0.06);
            border-color: rgba(255,255,255,0.08);
        }
        [data-bs-theme="dark"] .search-bar:focus-within {
            background: rgba(255,255,255,0.1);
            border-color: var(--primary-color);
        }

        /* Keyboard shortcut badge */
        .navbar-kbd {
            background: rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 6px;
            padding: 2px 7px;
            font-size: 0.65rem;
            font-family: 'Inter', monospace;
            color: #94a3b8;
            white-space: nowrap;
            line-height: 1.4;
            font-weight: 600;
        }
        [data-bs-theme="dark"] .navbar-kbd {
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.12);
            color: #64748b;
        }

        /* ── Search Results Dropdown ── */
        .search-results-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            background: white;
            border-radius: var(--radius-sm);
            box-shadow: 0 20px 60px rgba(0,0,0,0.15), 0 0 0 1px rgba(0,0,0,0.05);
            z-index: 9999;
            max-height: 380px;
            overflow-y: auto;
            animation: searchDropIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        [data-bs-theme="dark"] .search-results-dropdown {
            background: #1e293b;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.08);
        }
        @keyframes searchDropIn {
            from { opacity: 0; transform: translateY(-8px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .search-result-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            cursor: pointer;
            transition: background 0.15s;
            text-decoration: none;
            color: var(--text-main);
            border-left: 3px solid transparent;
        }
        .search-result-item:hover,
        .search-result-item.active {
            background: rgba(34, 197, 94, 0.06);
            border-left-color: var(--primary-color);
            color: var(--text-main);
        }
        .search-result-item .result-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }
        .search-result-item .result-text { flex: 1; min-width: 0; }
        .search-result-item .result-text .title { font-weight: 600; font-size: 0.85rem; }
        .search-result-item .result-text .subtitle { font-size: 0.72rem; color: #94a3b8; }
        .search-results-footer kbd {
            background: rgba(0,0,0,0.05);
            border-radius: 4px;
            padding: 1px 5px;
            font-size: 0.65rem;
            font-weight: 600;
            border: 1px solid rgba(0,0,0,0.08);
        }

        /* ── Quick Action Button ── */
        .btn-navbar-action {
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 6px 14px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: var(--transition-base);
            box-shadow: 0 2px 8px rgba(34, 197, 94, 0.25);
            white-space: nowrap;
        }
        .btn-navbar-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(34, 197, 94, 0.35);
            color: white;
        }
        .btn-navbar-action:active { transform: scale(0.97); }

        /* ── Navbar Icon Buttons ── */
        .navbar-icon-btn {
            width: 36px;
            height: 36px;
            padding: 0 !important;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            transition: var(--transition-base);
            position: relative;
        }
        .navbar-icon-btn:hover {
            background: rgba(0,0,0,0.06) !important;
            transform: scale(1.08);
        }
        [data-bs-theme="dark"] .navbar-icon-btn:hover {
            background: rgba(255,255,255,0.1) !important;
        }

        /* ── Breadcrumb in Navbar ── */
        .top-navbar .breadcrumb {
            font-size: 0.8rem;
            background: none;
            margin: 0;
            padding: 0;
        }
        .top-navbar .breadcrumb-item a { transition: color 0.2s; }
        .top-navbar .breadcrumb-item a:hover { color: var(--primary-color) !important; }
        .top-navbar .breadcrumb-item.active { color: var(--text-main); font-weight: 600; }
        .top-navbar .breadcrumb-item + .breadcrumb-item::before { color: #cbd5e1; }

        /* ── Notification Dot ── */
        .notification-dot {
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
            position: absolute;
            top: 4px;
            right: 4px;
            border: 2px solid white;
            animation: notifPulse 2s infinite;
        }
        @keyframes notifPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.4); }
            50% { box-shadow: 0 0 0 4px rgba(239,68,68,0); }
        }

        /* ── Mobile Search Overlay ── */
        .mobile-search-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            z-index: 9999;
            padding: 1rem;
        }
        .mobile-search-inner {
            background: white;
            border-radius: var(--radius-md);
            padding: 1rem;
            max-width: 500px;
            margin: 0 auto;
            box-shadow: var(--shadow-xl);
        }
        [data-bs-theme="dark"] .mobile-search-inner {
            background: #1e293b;
        }
    </style>
    @stack('styles')
</head>
<body>

    @php
        $tenant = app(\App\Services\TenantManager::class)->getTenant();
        $user = auth()->user();
        $hasValidBilling = true;

        if ($tenant && $user && !$user->is_system_admin) {
            $latestSub = $tenant->subscriptions()->where('status', 'active')->latest()->first();
            if (!$latestSub || ($latestSub->ends_at && $latestSub->ends_at->startOfDay()->isPast())) {
                $hasValidBilling = false;
            }
        }
    @endphp

    @if($hasValidBilling)
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="d-flex align-items-center justify-content-between mb-4 px-3" style="margin-top: 1rem; margin-bottom: 1rem;">
            <div class="d-flex align-items-center justify-content-center flex-grow-1 overflow-hidden">
                <img src="{{ asset('images/ng_logo.png') }}" alt="Noubtigo" height="70" class="logo-expanded" style="max-width: 170px; object-fit: contain; filter: brightness(0) invert(1);">
                <img src="{{ asset('images/ng_logo.png') }}" alt="Logo" height="70" class="logo-collapsed d-none" style="object-fit: contain; filter: brightness(0) invert(1);">
            </div>
            <button id="sidebarToggle" class="btn btn-sm text-white border-0 d-none d-lg-flex align-items-center justify-content-center p-1 rounded-2 hover-bg-white-10">
                <i class="bi bi-chevron-left"></i>
            </button>
        </div>

        <ul class="nav flex-column mt-4 pb-5">
            @if($user?->is_system_admin)
            <li class="nav-item mb-1">
                <span class="nav-link disabled text-uppercase fw-bold opacity-50 px-3" style="font-size: 0.65rem; letter-spacing: 1.5px; color: #94a3b8;">{{ __('ui.master_level') ?? 'System Master' }}</span>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rbac.master.index') ? 'active' : '' }}" href="{{ route('rbac.master.index') }}">
                    <i class="bi bi-cpu-fill"></i> <span>{{ __('ui.master_control') }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rbac.master.users') ? 'active' : '' }}" href="{{ route('rbac.master.users') }}">
                    <i class="bi bi-people-fill"></i> <span>{{ __('ui.global_directory') }}</span>
                </a>
            </li>
            <hr class="opacity-10 my-3 mx-3 border-white">
            @endif

            @php $isSimpleMode = $tenant && $tenant->isSimpleQueue(); @endphp


            @permission('dashboard.view')
            <li class="nav-item mb-1">
                <span class="nav-link disabled text-uppercase fw-bold opacity-50 px-3 sidebar-group-title" style="font-size: 0.65rem; letter-spacing: 1.5px; color: #94a3b8;">{{ __('ui.overview') ?? 'Overview' }}</span>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-grid-fill"></i> <span>{{ __('ui.dashboard') }}</span>
                </a>
            </li>
            @endpermission
            @if(!$isSimpleMode)
            @permission('analytics.view')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('analytics.index') ? 'active' : '' }}" href="{{ route('analytics.index') }}">
                    <i class="bi bi-graph-up-arrow"></i> <span>{{ __('ui.analytics') ?? 'Analytics' }}</span>
                </a>
            </li>
            @endpermission
            @endif

            <li class="nav-item mt-3 mb-1">
                <span class="nav-link disabled text-uppercase fw-bold opacity-50 px-3 sidebar-group-title" style="font-size: 0.65rem; letter-spacing: 1.5px; color: #94a3b8;">{{ __('ui.operations') ?? 'Operations' }}</span>
            </li>
            @permission('queue.view')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('queue.*') ? 'active' : '' }}" href="{{ route('queue.index') }}">
                    <i class="bi bi-ticket-perforated-fill"></i> <span>{{ __('ui.queue') ?? 'Queue' }}</span>
                    @if($isSimpleMode)
                        <span class="badge bg-success bg-opacity-20 text-white ms-auto rounded-pill sidebar-label-text" style="font-size:0.6rem; border: 1px solid rgba(34,197,94,0.2)">SIMPLE</span>
                    @endif
                </a>
            </li>
            @endpermission
            @if(!$isSimpleMode)
            @permission('appointments.view')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}" href="{{ route('appointments.index') }}">
                    <i class="bi bi-calendar-check-fill"></i> <span>{{ __('ui.appointments') ?? 'Appointments' }}</span>
                </a>
            </li>
            @endpermission
            @permission('customers.view')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                    <i class="bi bi-people-fill"></i> <span>{{ __('ui.customers') ?? 'Customers' }}</span>
                </a>
            </li>
            @endpermission
            @endif
            @permission('whatsapp.view')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('whatsapp.chat.*') ? 'active' : '' }}" href="{{ route('whatsapp.chat.index') }}">
                    <i class="bi bi-whatsapp"></i> <span>{{ __('ui.whatsapp_chat') ?? 'WhatsApp' }}</span>
                </a>
            </li>
            @endpermission

            <li class="nav-item mt-3 mb-1">
                <span class="nav-link disabled text-uppercase fw-bold opacity-50 px-3 sidebar-group-title" style="font-size: 0.65rem; letter-spacing: 1.5px; color: #94a3b8;">{{ __('ui.configuration') ?? 'Configuration' }}</span>
            </li>
            @permission('services.view')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}" href="{{ route('services.index') }}">
                    <i class="bi bi-briefcase-fill"></i> <span>{{ __('ui.services') ?? 'Services' }}</span>
                </a>
            </li>
            @endpermission
            @if(!$isSimpleMode)
            @permission('appointment_slots.view')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('appointment-slots.*') ? 'active' : '' }}" href="{{ route('appointment-slots.index') }}">
                    <i class="bi bi-clock-history"></i> <span>{{ __('ui.slot_settings') ?? 'Slot Settings' }}</span>
                </a>
            </li>
            @endpermission
            @endif
            @permission('rooms.view')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rooms.*') ? 'active' : '' }}" href="{{ route('rooms.index') }}">
                    <i class="bi bi-door-open-fill"></i> <span>{{ __('ui.rooms') ?? 'Rooms' }}</span>
                </a>
            </li>
            @endpermission
            @permission('displays.view')
            <li class="nav-item">
                <a class="nav-link {{ (request()->is('displays') && !request()->is('displays/contents*')) ? 'active' : '' }}" href="{{ route('displays.index') }}">
                    <i class="bi bi-display-fill"></i> <span>{{ __('ui.displays') ?? 'Displays' }}</span>
                </a>
            </li>
            @permission('display_content.view')
            <li class="nav-item">
                <a class="nav-link {{ request()->is('displays/contents*') ? 'active' : '' }}" href="{{ route('displays.contents.index') }}">
                    <i class="bi bi-file-earmark-richtext-fill"></i> <span>{{ __('ui.display_content') ?? 'Display Content' }}</span>
                </a>
            </li>
            @endpermission
            @endpermission
            @permission('queue.history')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('tickets.*') ? 'active' : '' }}" href="{{ route('tickets.index') }}">
                    <i class="bi bi-receipt"></i> <span>{{ __('ui.tickets_history') ?? 'Tickets' }}</span>
                </a>
            </li>
            @endpermission

            <li class="nav-item mt-3 mb-1">
                <span class="nav-link disabled text-uppercase fw-bold opacity-50 px-3 sidebar-group-title" style="font-size: 0.65rem; letter-spacing: 1.5px; color: #94a3b8;">{{ __('ui.administration') ?? 'Administration' }}</span>
            </li>
            @permission('users.view')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rbac.users.*') ? 'active' : '' }}" href="{{ route('rbac.users.index') }}">
                    <i class="bi bi-person-badge-fill"></i> <span>{{ __('ui.staff') ?? 'Staff' }}</span>
                </a>
            </li>
            @endpermission
            @permission('permission.manage')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rbac.roles.matrix') ? 'active' : '' }}" href="{{ route('rbac.roles.matrix') }}">
                    <i class="bi bi-shield-lock-fill"></i> <span>{{ __('ui.permissions') ?? 'Permissions' }}</span>
                </a>
            </li>
            @endpermission
            @permission('activity_logs.view')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}" href="{{ route('activity-logs.index') }}">
                    <i class="bi bi-journal-text"></i> <span>{{ __('ui.activity_logs') ?? 'Activity Logs' }}</span>
                </a>
            </li>
            @endpermission

            @if(auth()->user()?->is_system_admin)
            <li class="nav-item mt-3 mb-1">
                <span class="nav-link disabled text-uppercase fw-bold opacity-50 px-3 sidebar-group-title" style="font-size: 0.65rem; letter-spacing: 1.5px; color: #94a3b8;">{{ __('ui.billing_and_payments') ?? 'Billing & Payments' }}</span>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}" href="{{ route('admin.payments.index') }}">
                    <i class="bi bi-credit-card"></i> <span>{{ __('ui.payments') ?? 'Payments' }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}" href="{{ route('admin.subscriptions.index') }}">
                    <i class="bi bi-arrow-repeat"></i> <span>{{ __('ui.subscriptions') ?? 'Subscriptions' }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}" href="{{ route('admin.invoices.index') }}">
                    <i class="bi bi-receipt"></i> <span>{{ __('ui.invoices') ?? 'Invoices' }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}" href="{{ route('admin.coupons.index') }}">
                    <i class="bi bi-tag-fill"></i> <span>{{ __('ui.coupons') }}</span>
                </a>
            </li>
            @endif

            <li class="nav-item mt-2">
                <a class="nav-link {{ request()->routeIs('help.*') ? 'active' : '' }}" href="{{ route('help.index') }}">
                    <i class="bi bi-question-circle-fill text-warning"></i> <span>{{ __('ui.help_center') ?? 'Help Center' }}</span>
                </a>
            </li>
            @if(!auth()->user()?->is_system_admin)
            @permission('billing.view')
            <li class="nav-item mt-1">
                <a class="nav-link {{ request()->routeIs('billing.*') ? 'active' : '' }}" href="{{ route('billing.index') }}">
                    <i class="bi bi-credit-card-2-front-fill"></i> <span>{{ __('ui.billing_plan') ?? 'Billing & Plan' }}</span>
                </a>
            </li>
            @endpermission
            @permission('settings.view')
            <li class="nav-item mt-1">
                <a class="nav-link {{ request()->routeIs('settings.company') ? 'active' : '' }}" href="{{ route('settings.company') }}">
                    <i class="bi bi-gear-fill"></i> <span>{{ __('ui.settings') }}</span>
                </a>
            </li>
            @endpermission
            @endif

            @if(config('app.package_experiment', true) && (auth()->user()?->is_system_admin || auth()->user()?->can('billing.view')))
            <li class="nav-item mt-2">
                <a class="nav-link {{ request()->routeIs('experimental.*') ? 'active' : '' }}" href="{{ route('experimental.packages.index') }}" style="border-left: 2px solid #22c55e;">
                    <i class="bi bi-flask-fill text-success"></i> <span>Package Sandbox</span>
                    <span class="badge bg-success bg-opacity-25 text-success ms-auto" style="font-size:0.6rem; border: 1px solid rgba(34,197,94,0.3);">EXP</span>
                </a>
            </li>
            @endif
        </ul>
    </nav>
    @endif

    <!-- Main Content -->
    <main class="main-content" id="mainContent" style="{{ !$hasValidBilling ? 'margin-left: 0 !important; margin-right: 0 !important;' : '' }}">
        <!-- Modern Floating Navbar -->
        <header class="top-navbar d-flex align-items-center slide-up stagger-1">
            {{-- Left: Mobile toggle + Breadcrumb --}}
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                @if($hasValidBilling)
                <button id="mobileMenuToggle" class="btn btn-white border-0 shadow-none d-lg-none rounded-circle p-2">
                    <i class="bi bi-list fs-4"></i>
                </button>
                @endif
                {{-- Breadcrumb --}}
                <nav aria-label="breadcrumb" class="d-none d-md-block">
                    <ol class="breadcrumb mb-0 small" style="--bs-breadcrumb-divider: '›';">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted"><i class="bi bi-house-door"></i></a></li>
                        @hasSection('breadcrumb_parent')
                            <li class="breadcrumb-item"><a href="@yield('breadcrumb_parent_url', '#')" class="text-decoration-none text-muted">@yield('breadcrumb_parent')</a></li>
                        @endif
                        <li class="breadcrumb-item active fw-semibold" aria-current="page">@yield('header_title', __('ui.overview'))</li>
                    </ol>
                </nav>
            </div>

            {{-- Center: Global Search --}}
            @if($hasValidBilling)
            <div class="navbar-search-wrapper mx-auto d-none d-md-block" style="flex: 1; max-width: 420px;">
                <div class="navbar-search position-relative" id="navbarSearch">
                    <div class="search-bar" id="searchBar" role="button" tabindex="0">
                        <i class="bi bi-search text-muted"></i>
                        <input type="text" id="globalSearchInput" placeholder="{{ __('ui.search') ?? 'Search pages, tickets, customers...' }}" autocomplete="off">
                        <kbd class="navbar-kbd d-none d-lg-inline-block">Ctrl+K</kbd>
                    </div>
                    {{-- Search Results Dropdown --}}
                    <div class="search-results-dropdown" id="searchResults" style="display:none;">
                        <div class="search-results-header px-3 py-2">
                            <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px;">{{ __('ui.quick_navigation') }}</span>
                        </div>
                        <div class="search-results-list" id="searchResultsList">
                            {{-- Results injected by JS --}}
                        </div>
                        <div class="search-results-footer px-3 py-2 border-top d-flex align-items-center justify-content-between">
                            <span class="text-muted" style="font-size: 0.7rem;"><kbd>↑↓</kbd> {{ __('ui.navigate') }} <kbd>↵</kbd> {{ __('ui.open') }} <kbd>esc</kbd> {{ __('ui.close') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            {{-- Right: Actions + Controls --}}
            <div class="d-flex align-items-center gap-1 gap-md-4 flex-shrink-0 ms-auto">

                @if($hasValidBilling)
                {{-- Mobile Search Toggle --}}
                <button class="btn btn-light border-0 rounded-circle navbar-icon-btn d-md-none" id="mobileSearchToggle" title="Search">
                    <i class="bi bi-search"></i>
                </button>

                {{-- Help Center Button --}}
                <button class="btn btn-light border-0 rounded-circle navbar-icon-btn text-primary shadow-sm" id="helpCenterBtn" data-bs-toggle="modal" data-bs-target="#helpCenterModal" title="Help & Guides (❓)">
                    <i class="bi bi-question-circle-fill fs-5"></i>
                </button>
                @endif

                {{-- Fullscreen Toggle --}}
                <button class="btn btn-light border-0 rounded-circle navbar-icon-btn d-none d-lg-flex" id="fullscreenToggle" title="Fullscreen">
                    <i class="bi bi-arrows-fullscreen"></i>
                </button>

                {{-- Lang --}}
                <div class="dropdown lang-switcher d-none d-sm-block">
                    <button class="btn btn-light border-0 shadow-none rounded-circle p-0 navbar-icon-btn d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" type="button" data-bs-toggle="dropdown" title="Language">
                        @php 
                            $currentLangCode = app()->getLocale();
                            $btnFlag = $currentLangCode == 'en' ? 'gb' : ($currentLangCode == 'ar' ? 'sa' : 'fr');
                        @endphp
                        <img src="https://flagcdn.com/w20/{{ $btnFlag }}.png" width="20" alt="{{ $currentLangCode }}" class="rounded-1">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-xl border-0 p-2 animate__animated animate__fadeIn animate__faster" style="min-width: 140px;">
                        @foreach(\App\Models\Language::where('is_active', true)->get() as $lang)
                        @php
                            $listFlag = $lang->code == 'en' ? 'gb' : ($lang->code == 'ar' ? 'sa' : 'fr');
                        @endphp
                        <li>
                            <form action="{{ route('language.switch') }}" method="POST">
                                @csrf
                                <input type="hidden" name="locale" value="{{ $lang->code }}">
                                <button type="submit" class="dropdown-item rounded-3 {{ app()->getLocale() == $lang->code ? 'active' : '' }} py-2 d-flex align-items-center">
                                    <img src="https://flagcdn.com/w20/{{ $listFlag }}.png" width="20" class="me-2 rounded-1 shadow-sm" alt="{{ $lang->code }}">
                                    <span class="fw-medium small">{{ $lang->name }}</span>
                                </button>
                            </form>
                        </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Theme Toggle --}}
                <button id="themeToggle" class="btn btn-light border-0 rounded-circle navbar-icon-btn" title="Toggle Theme" style="display:none;">
                    <i class="bi bi-moon-stars"></i>
                </button>

                {{-- Notifications --}}
                <div class="dropdown position-relative d-none" style="display:none;">
                    <button class="btn btn-light border-0 rounded-circle navbar-icon-btn" type="button" id="notifBtn" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                        <i class="bi bi-bell"></i>
                        <span class="notification-dot"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-xl border-0 p-0 animate__animated animate__fadeIn animate__faster" aria-labelledby="notifBtn" style="width: 320px; border-radius: var(--radius-md); overflow: hidden; margin-top: 10px;">
                        <div class="p-3 border-bottom d-flex align-items-center justify-content-between" style="background: rgba(var(--bs-primary-rgb), 0.03);">
                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">Notifications</h6>
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1" style="font-size: 0.65rem;">New Updates</span>
                        </div>
                        <div class="list-group list-group-flush" style="max-height: 240px; overflow-y: auto;">
                            <div class="list-group-item list-group-item-action py-3 border-bottom hover-bg-light" style="border: 0; cursor: pointer;">
                                <div class="d-flex gap-3">
                                    <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; flex-shrink: 0; background: rgba(34, 197, 94, 0.1) !important;">
                                        <i class="bi bi-ticket-detailed"></i>
                                    </div>
                                    <div>
                                        <p class="mb-1 text-dark fw-semibold" style="font-size: 0.8rem; line-height: 1.2;">Queue updates active</p>
                                        <small class="text-secondary d-block" style="font-size: 0.72rem;">Dynamic DOM updates are now active in the room dashboard.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="list-group-item list-group-item-action py-3 hover-bg-light" style="border: 0; cursor: pointer;">
                                <div class="d-flex gap-3">
                                    <div class="bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; flex-shrink: 0; background: rgba(6, 182, 212, 0.1) !important;">
                                        <i class="bi bi-broadcast"></i>
                                    </div>
                                    <div>
                                        <p class="mb-1 text-dark fw-semibold" style="font-size: 0.8rem; line-height: 1.2;">Realtime online</p>
                                        <small class="text-secondary d-block" style="font-size: 0.72rem;">Laravel Reverb is listening for incoming actions.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-2 border-top text-center" style="background: rgba(0,0,0,0.01);">
                            <a href="#" class="text-primary fw-semibold text-decoration-none" style="font-size: 0.75rem;">Clear all</a>
                        </div>
                    </div>
                </div>

                <div class="vr mx-1 d-none d-md-block opacity-10" style="height: 28px;"></div>

                {{-- User Profile --}}
                <div class="dropdown">
                    <button class="btn btn-transparent border-0 dropdown-toggle d-flex align-items-center gap-2 p-1 pe-2 rounded-pill shadow-none hover-bg-light" type="button" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()?->full_name ?? 'User') }}&background=22c55e&color=fff" class="rounded-circle shadow-sm" width="36" height="36" alt="Profile">
                        <div class="text-start d-none d-md-block">
                            <p class="fw-bold mb-0 small" style="line-height: 1.1;">{{ auth()->user()?->full_name ?? 'Guest' }}</p>
                            <span class="text-muted" style="font-size: 0.65rem;">{{ auth()->user()?->role?->name ?? 'User' }}</span>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-xl border-0 p-2 animate__animated animate__fadeIn animate__faster" style="min-width: 220px;">
                        <li class="px-3 py-2 d-md-none border-bottom mb-2">
                            <p class="fw-bold mb-0">{{ auth()->user()?->full_name ?? 'Guest' }}</p>
                            <span class="text-muted small">{{ auth()->user()?->email ?? '' }}</span>
                        </li>
                        <li><a class="dropdown-item rounded-3 py-2" href="{{ route('settings.company') }}"><i class="bi bi-person me-2 text-primary"></i> {{ __('ui.profile') }}</a></li>

                        <li><hr class="dropdown-divider opacity-50"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger rounded-3 py-2 w-100 text-start">
                                    <i class="bi bi-box-arrow-right me-2"></i> {{ __('ui.logout') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        {{-- Mobile Search Overlay --}}
        <div class="mobile-search-overlay" id="mobileSearchOverlay" style="display:none;">
            <div class="mobile-search-inner slide-up">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="search-bar flex-grow-1">
                        <i class="bi bi-search text-muted"></i>
                        <input type="text" id="mobileSearchInput" placeholder="{{ __('ui.search') ?? 'Search...' }}" autocomplete="off" autofocus>
                    </div>
                    <button class="btn btn-light border-0 rounded-circle p-2" id="closeMobileSearch">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="search-results-list" id="mobileSearchResultsList"></div>
            </div>
        </div>

        {{-- Subscription Reminder Alert --}}
        @php
            $tenant = app(\App\Services\TenantManager::class)->getTenant();
            $subscriptionAlert = null;
            if ($tenant && auth()->user() && !auth()->user()->is_system_admin) {
                $activeSub = $tenant->subscriptions()->latest()->first();
                if ($activeSub && $activeSub->ends_at) {
                    $endsAt = $activeSub->ends_at->startOfDay();
                    $now = now()->startOfDay();
                    
                    if ($endsAt->isPast()) {
                        $daysExpired = $endsAt->diffInDays($now);
                        if ($daysExpired <= 3) {
                            $subscriptionAlert = [
                                'type' => 'danger',
                                'message' => 'Your subscription expired ' . $daysExpired . ' day(s) ago. You have ' . (3 - $daysExpired) . ' day(s) left to renew before access is restricted.'
                            ];
                        }
                    } else {
                        $daysLeft = $now->diffInDays($endsAt);
                        if ($daysLeft <= 3) {
                            $subscriptionAlert = [
                                'type' => 'warning',
                                'message' => 'Your subscription will expire in ' . $daysLeft . ' day(s). Please renew soon to avoid service interruption.'
                            ];
                        }
                    }
                }
            }
        @endphp

        @if($subscriptionAlert)
            <div class="alert alert-{{ $subscriptionAlert['type'] }} border-0 shadow-sm rounded-4 mb-4 slide-up d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="bg-{{ $subscriptionAlert['type'] }} text-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <span class="fw-medium">{{ $subscriptionAlert['message'] }}</span>
                </div>
                <a href="{{ route('billing.index') }}" class="btn btn-sm btn-{{ $subscriptionAlert['type'] }} rounded-pill px-3 fw-bold">Renew Now</a>
            </div>
        @endif

        <!-- Page Header -->
        <div class="mb-4">
            <h1 class="fw-bold h3 mb-1">@yield('header_title', __('ui.overview'))</h1>
            <p class="text-muted">@yield('header_subtitle', __('ui.welcome_back'))</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 slide-up stagger-2 d-flex align-items-center">
                <div class="bg-success text-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="bi bi-check-lg"></i>
                </div>
                <span class="fw-medium">{!! session('success') !!}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 slide-up stagger-2 d-flex align-items-center">
                <div class="bg-danger text-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="bi bi-x-lg"></i>
                </div>
                <span class="fw-medium">{{ session('error') }}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 slide-up stagger-2 d-flex align-items-center">
                <div class="bg-danger text-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="flex-grow-1">
                    <ul class="mb-0 ps-3 small fw-medium">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close ms-auto align-self-start" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="slide-up stagger-3">
            @yield('content')
        </div>
    </main>

    <!-- Mobile Bottom Navigation -->
    <nav class="bottom-nav slide-up">
        @permission('dashboard.view')
        <a href="{{ route('dashboard') }}" class="bottom-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-fill"></i>
            <span>{{ __('ui.dashboard') }}</span>
        </a>
        @endpermission
        <a href="{{ route('queue.index') }}" class="bottom-link {{ request()->routeIs('queue.*') ? 'active' : '' }}">
            <i class="bi bi-ticket-perforated-fill"></i>
            <span>{{ __('ui.queue') }}</span>
        </a>
        <a href="{{ route('whatsapp.chat.index') }}" class="bottom-link {{ request()->routeIs('whatsapp.chat.*') ? 'active' : '' }}">
            <i class="bi bi-whatsapp"></i>
            <span>Chat</span>
        </a>
        <button id="mobileMoreToggle" class="bottom-link border-0 bg-transparent ripple">
            <i class="bi bi-three-dots"></i>
            <span>{{ __('ui.more') ?? 'More' }}</span>
        </button>
    </nav>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Theme Toggle Logic with persistence
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;

        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            html.setAttribute('data-bs-theme', savedTheme);
            themeToggle.innerHTML = savedTheme === 'light' ? '<i class="bi bi-moon-stars"></i>' : '<i class="bi bi-sun"></i>';
        }
        
        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            themeToggle.innerHTML = newTheme === 'light' ? '<i class="bi bi-moon-stars"></i>' : '<i class="bi bi-sun"></i>';
        });

        // Sidebar Collapse Logic
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        const updateSidebarState = (isCollapsed) => {
            const logoExpanded = document.querySelector('.logo-expanded');
            const logoCollapsed = document.querySelector('.logo-collapsed');

            if (isCollapsed) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('sidebar-collapsed');
                if(sidebarToggle) sidebarToggle.innerHTML = '<i class="bi bi-chevron-right"></i>';
                if(logoExpanded) logoExpanded.classList.add('d-none');
                if(logoCollapsed) logoCollapsed.classList.remove('d-none');
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('sidebar-collapsed');
                if(sidebarToggle) sidebarToggle.innerHTML = '<i class="bi bi-chevron-left"></i>';
                if(logoExpanded) logoExpanded.classList.remove('d-none');
                if(logoCollapsed) logoCollapsed.classList.add('d-none');
            }
        };

        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        updateSidebarState(isCollapsed);

        if(sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                const nowCollapsed = !sidebar.classList.contains('collapsed');
                updateSidebarState(nowCollapsed);
                localStorage.setItem('sidebarCollapsed', nowCollapsed);
            });
        }

        // Mobile Sidebar
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const mobileMoreToggle = document.getElementById('mobileMoreToggle');
        
        if(mobileMenuToggle) mobileMenuToggle.addEventListener('click', () => sidebar.classList.toggle('show'));
        if(mobileMoreToggle) mobileMoreToggle.addEventListener('click', () => sidebar.classList.toggle('show'));

        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 992 && sidebar.classList.contains('show') && !sidebar.contains(e.target) && !mobileMenuToggle.contains(e.target) && (!mobileMoreToggle || !mobileMoreToggle.contains(e.target))) {
                sidebar.classList.remove('show');
            }
        });

        // ── Fullscreen Toggle ──
        const fullscreenBtn = document.getElementById('fullscreenToggle');
        if (fullscreenBtn) {
            fullscreenBtn.addEventListener('click', () => {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(() => {});
                    fullscreenBtn.innerHTML = '<i class="bi bi-fullscreen-exit"></i>';
                } else {
                    document.exitFullscreen();
                    fullscreenBtn.innerHTML = '<i class="bi bi-arrows-fullscreen"></i>';
                }
            });
            document.addEventListener('fullscreenchange', () => {
                fullscreenBtn.innerHTML = document.fullscreenElement
                    ? '<i class="bi bi-fullscreen-exit"></i>'
                    : '<i class="bi bi-arrows-fullscreen"></i>';
            });
        }

        // ── Global Command Palette Search ──
        (function() {
            // Build search index from sidebar links
            const searchIndex = [];
            const colorMap = {
                'dashboard': { bg: 'rgba(34,197,94,0.1)', color: '#22c55e' },
                'analytics': { bg: 'rgba(139,92,246,0.1)', color: '#8b5cf6' },
                'queue': { bg: 'rgba(6,182,212,0.1)', color: '#06b6d4' },
                'appointments': { bg: 'rgba(249,115,22,0.1)', color: '#f97316' },
                'customers': { bg: 'rgba(236,72,153,0.1)', color: '#ec4899' },
                'whatsapp': { bg: 'rgba(37,211,102,0.1)', color: '#25d366' },
                'services': { bg: 'rgba(59,130,246,0.1)', color: '#3b82f6' },
                'rooms': { bg: 'rgba(168,85,247,0.1)', color: '#a855f7' },
                'displays': { bg: 'rgba(14,165,233,0.1)', color: '#0ea5e9' },
                'tickets': { bg: 'rgba(245,158,11,0.1)', color: '#f59e0b' },
                'staff': { bg: 'rgba(99,102,241,0.1)', color: '#6366f1' },
                'permissions': { bg: 'rgba(239,68,68,0.1)', color: '#ef4444' },
                'activity': { bg: 'rgba(20,184,166,0.1)', color: '#14b8a6' },
                'settings': { bg: 'rgba(107,114,128,0.1)', color: '#6b7280' },
                'default': { bg: 'rgba(100,116,139,0.1)', color: '#64748b' }
            };

            document.querySelectorAll('.sidebar .nav-link:not(.disabled)').forEach(link => {
                const spanEl = link.querySelector('span');
                const iconEl = link.querySelector('i');
                if (!spanEl || !link.href) return;
                const title = spanEl.textContent.trim();
                const icon = iconEl ? iconEl.className : 'bi bi-circle';
                const href = link.href;
                // Find matching color
                let colors = colorMap.default;
                for (const [key, val] of Object.entries(colorMap)) {
                    if (title.toLowerCase().includes(key) || href.toLowerCase().includes(key)) {
                        colors = val;
                        break;
                    }
                }
                // Determine group
                let group = 'Navigation';
                const section = link.closest('ul')?.querySelectorAll('.sidebar-group-title');
                if (section) {
                    let lastGroup = '';
                    section.forEach(s => {
                        const sRect = s.getBoundingClientRect();
                        const lRect = link.getBoundingClientRect();
                        if (sRect.top < lRect.top) lastGroup = s.textContent.trim();
                    });
                    if (lastGroup) group = lastGroup;
                }
                searchIndex.push({ title, icon, href, colors, group });
            });

            const searchInput = document.getElementById('globalSearchInput');
            const searchResults = document.getElementById('searchResults');
            const searchResultsList = document.getElementById('searchResultsList');
            let activeIndex = -1;
            let currentSearchQuery = '';
            let debounceTimer;

            function debounce(func, delay) {
                return function(...args) {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => func.apply(this, args), delay);
                };
            }

            function highlightMatch(text, query) {
                if (!query) return text;
                const index = text.toLowerCase().indexOf(query.toLowerCase());
                if (index === -1) return text;
                return text.substring(0, index) + '<mark style="background:rgba(34,197,94,0.2);padding:0 2px;border-radius:3px;">' + text.substring(index, index + query.length) + '</mark>' + text.substring(index + query.length);
            }

            function navigateResults(direction, listEl) {
                const items = listEl.querySelectorAll('.search-result-item');
                if (!items.length) return;
                items.forEach(i => i.classList.remove('active'));
                activeIndex += direction;
                if (activeIndex < 0) activeIndex = items.length - 1;
                if (activeIndex >= items.length) activeIndex = 0;
                items[activeIndex].classList.add('active');
                items[activeIndex].scrollIntoView({ block: 'nearest' });
            }

            // Function to perform search (both local and database)
            function performSearch(query, targetList, targetDropdown) {
                currentSearchQuery = query.trim();
                const q = currentSearchQuery.toLowerCase();
                
                // 1. Get local matching navigation items
                let localItems = [];
                if (q) {
                    localItems = searchIndex.filter(item =>
                        item.title.toLowerCase().includes(q) ||
                        item.group.toLowerCase().includes(q)
                    );
                } else {
                    localItems = searchIndex; // Show all when empty
                }

                // Render immediately with local navigation items
                renderCombined(localItems, [], q, targetList, targetDropdown);

                // 2. Fetch database results if query is long enough
                if (currentSearchQuery.length >= 2) {
                    fetchDatabaseResults(currentSearchQuery, (dbItems) => {
                        // Only update if search query hasn't changed in the meantime
                        if (currentSearchQuery.toLowerCase() === q) {
                            renderCombined(localItems, dbItems, q, targetList, targetDropdown);
                        }
                    });
                }
            }

            // Fetch from Laravel backend
            function fetchDatabaseResults(query, callback) {
                fetch(`/global-search?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        callback(data || []);
                    })
                    .catch(err => {
                        console.error('Error fetching search results:', err);
                        callback([]);
                    });
            }

            // Render navigation + database results combined
            function renderCombined(navItems, dbItems, query, targetList, targetDropdown) {
                targetList.innerHTML = '';
                activeIndex = -1;

                if (navItems.length === 0 && dbItems.length === 0) {
                    targetList.innerHTML = `<div class="px-3 py-4 text-center text-muted"><i class="bi bi-search fs-4 d-block mb-2 opacity-50"></i><span class="small">{{ __('ui.no_results_found') }}</span></div>`;
                    if (targetDropdown) targetDropdown.style.display = 'block';
                    return;
                }

                let indexCounter = 0;

                // 1. Navigation items header & list
                if (navItems.length > 0) {
                    const header = document.createElement('div');
                    header.className = 'search-results-header px-3 py-2 text-muted small fw-semibold text-uppercase';
                    header.style.fontSize = '0.65rem';
                    header.style.letterSpacing = '1px';
                    header.style.background = 'var(--bs-light)';
                    header.innerText = @json(__('ui.navigation_links'));
                    targetList.appendChild(header);

                    navItems.forEach(item => {
                        const el = document.createElement('a');
                        el.href = item.href;
                        el.className = 'search-result-item';
                        el.dataset.index = indexCounter++;
                        el.innerHTML = `
                            <div class="result-icon" style="background:${item.colors.bg}; color:${item.colors.color};">
                                <i class="${item.icon}"></i>
                            </div>
                            <div class="result-text">
                                <div class="title">${highlightMatch(item.title, query)}</div>
                                <div class="subtitle">${item.group}</div>
                            </div>
                            <i class="bi bi-arrow-return-left text-muted small opacity-50"></i>
                        `;
                        targetList.appendChild(el);
                    });
                }

                // 2. Database results grouped or listed
                if (dbItems.length > 0) {
                    // Group db results by the 'group' property
                    const groups = {};
                    dbItems.forEach(item => {
                        if (!groups[item.group]) {
                            groups[item.group] = [];
                        }
                        groups[item.group].push(item);
                    });

                    for (const [groupName, items] of Object.entries(groups)) {
                        const header = document.createElement('div');
                        header.className = 'search-results-header px-3 py-2 text-muted small fw-semibold text-uppercase mt-2';
                        header.style.fontSize = '0.65rem';
                        header.style.letterSpacing = '1px';
                        header.style.background = 'var(--bs-light)';
                        const translatedGroups = {
                            Tickets: @json(__('ui.tickets')),
                            Customers: @json(__('ui.customers')),
                        };
                        header.innerText = translatedGroups[groupName] || groupName;
                        targetList.appendChild(header);

                        items.forEach(item => {
                            const el = document.createElement('a');
                            el.href = item.href;
                            el.className = 'search-result-item';
                            el.dataset.index = indexCounter++;
                            el.innerHTML = `
                                <div class="result-icon" style="background:${item.colors.bg}; color:${item.colors.color};">
                                    <i class="${item.icon}"></i>
                                </div>
                                <div class="result-text">
                                    <div class="title">${highlightMatch(item.title, query)}</div>
                                    <div class="subtitle">${item.subtitle}</div>
                                </div>
                                <i class="bi bi-arrow-return-left text-muted small opacity-50"></i>
                            `;
                            targetList.appendChild(el);
                        });
                    }
                }

                if (targetDropdown) targetDropdown.style.display = 'block';
            }

            const debouncedSearch = debounce((query, targetList, targetDropdown) => {
                performSearch(query, targetList, targetDropdown);
            }, 250);

            const searchBarWrapper = document.getElementById('searchBar');
            if (searchBarWrapper && searchInput) {
                searchBarWrapper.addEventListener('click', (e) => {
                    if (e.target !== searchInput) {
                        searchInput.focus();
                    }
                });
            }

            if (searchInput) {
                searchInput.addEventListener('focus', () => {
                    performSearch(searchInput.value, searchResultsList, searchResults);
                });
                searchInput.addEventListener('input', () => {
                    const val = searchInput.value;
                    if (val.trim().length >= 2) {
                        debouncedSearch(val, searchResultsList, searchResults);
                    } else {
                        performSearch(val, searchResultsList, searchResults);
                    }
                });
                searchInput.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowDown') { e.preventDefault(); navigateResults(1, searchResultsList); }
                    else if (e.key === 'ArrowUp') { e.preventDefault(); navigateResults(-1, searchResultsList); }
                    else if (e.key === 'Enter') {
                        e.preventDefault();
                        const active = searchResultsList.querySelector('.search-result-item.active');
                        if (active) window.location.href = active.href;
                        else {
                            const first = searchResultsList.querySelector('.search-result-item');
                            if (first) window.location.href = first.href;
                        }
                    }
                    else if (e.key === 'Escape') {
                        searchResults.style.display = 'none';
                        searchInput.blur();
                    }
                });

                // Close on outside click
                document.addEventListener('click', (e) => {
                    if (!e.target.closest('#navbarSearch')) {
                        searchResults.style.display = 'none';
                    }
                });
            }

            // Ctrl+K shortcut
            document.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                    e.preventDefault();
                    if (searchInput) {
                        searchInput.focus();
                        searchInput.select();
                    } else {
                        // Mobile: open overlay
                        const overlay = document.getElementById('mobileSearchOverlay');
                        if (overlay) {
                            overlay.style.display = 'block';
                            setTimeout(() => document.getElementById('mobileSearchInput')?.focus(), 100);
                        }
                    }
                }
            });

            // Mobile Search
            const mobileSearchToggle = document.getElementById('mobileSearchToggle');
            const mobileSearchOverlay = document.getElementById('mobileSearchOverlay');
            const closeMobileSearch = document.getElementById('closeMobileSearch');
            const mobileSearchInput = document.getElementById('mobileSearchInput');
            const mobileSearchResultsList = document.getElementById('mobileSearchResultsList');

            if (mobileSearchToggle && mobileSearchOverlay) {
                mobileSearchToggle.addEventListener('click', () => {
                    mobileSearchOverlay.style.display = 'block';
                    performSearch('', mobileSearchResultsList, null);
                    setTimeout(() => mobileSearchInput?.focus(), 100);
                });
            }
            if (closeMobileSearch) {
                closeMobileSearch.addEventListener('click', () => {
                    mobileSearchOverlay.style.display = 'none';
                });
            }
            
            const debouncedMobileSearch = debounce((query, targetList, targetDropdown) => {
                performSearch(query, targetList, targetDropdown);
            }, 250);

            if (mobileSearchInput) {
                mobileSearchInput.addEventListener('input', () => {
                    const val = mobileSearchInput.value;
                    if (val.trim().length >= 2) {
                        debouncedMobileSearch(val, mobileSearchResultsList, null);
                    } else {
                        performSearch(val, mobileSearchResultsList, null);
                    }
                });
                mobileSearchInput.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowDown') { e.preventDefault(); navigateResults(1, mobileSearchResultsList); }
                    else if (e.key === 'ArrowUp') { e.preventDefault(); navigateResults(-1, mobileSearchResultsList); }
                    else if (e.key === 'Enter') {
                        e.preventDefault();
                        const active = mobileSearchResultsList.querySelector('.search-result-item.active');
                        if (active) window.location.href = active.href;
                        else {
                            const first = mobileSearchResultsList.querySelector('.search-result-item');
                            if (first) window.location.href = first.href;
                        }
                    }
                    else if (e.key === 'Escape') {
                        mobileSearchOverlay.style.display = 'none';
                    }
                });
            }
            if (mobileSearchOverlay) {
                mobileSearchOverlay.addEventListener('click', (e) => {
                    if (e.target === mobileSearchOverlay) mobileSearchOverlay.style.display = 'none';
                });
            }
        })();
    </script>
    {{-- Help Center Modal --}}
    @if($hasValidBilling)
    <x-help-modal />
    @endif

    {{-- Shepherd JS Tour Library --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js@10.0.1/dist/css/shepherd.css"/>
    <script src="https://cdn.jsdelivr.net/npm/shepherd.js@10.0.1/dist/js/shepherd.min.js"></script>
    <script src="{{ asset('frontend/js/onboarding-tour.js') }}"></script>

    {{-- Quick Create Customer Modal (shared across all pages using x-customer-search) --}}
    <x-quick-create-modal />

    @stack('scripts')
</body>
</html>
