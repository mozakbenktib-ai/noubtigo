<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.access_denied') ?? 'Access Denied' }} | Noubtigo</title>

    <!-- Google Fonts (matching landing page) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    @if(app()->getLocale() == 'ar')
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    @endif

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --brand-primary: #22c55e;
            --brand-secondary: #06b6d4;
            --brand-gradient: linear-gradient(135deg, #22c55e 0%, #06b6d4 100%);
            --bg-light: #ffffff;
            --bg-subtle: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-light: rgba(15, 23, 42, 0.06);
            --shadow-glow: 0 0 40px rgba(34, 197, 90, 0.15);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: {{ app()->getLocale() == 'ar' ? "'Noto Sans Arabic', sans-serif" : "'Inter', -apple-system, BlinkMacSystemFont, sans-serif" }};
            background: var(--bg-subtle);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, .font-brand {
            font-family: {{ app()->getLocale() == 'ar' ? "'Noto Sans Arabic', sans-serif" : "'Outfit', sans-serif" }};
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        /* ── Subtle Background Pattern (like landing hero) ── */
        .bg-pattern {
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(circle at 1px 1px, rgba(34,197,94,0.06) 1px, transparent 0);
            background-size: 40px 40px;
            pointer-events: none;
        }

        .bg-blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.12;
            pointer-events: none;
            animation: blobFloat 12s ease-in-out infinite;
        }
        .bg-blob-1 {
            width: 500px; height: 500px;
            top: -150px; right: -100px;
            background: var(--brand-primary);
        }
        .bg-blob-2 {
            width: 400px; height: 400px;
            bottom: -120px; left: -80px;
            background: var(--brand-secondary);
            animation-delay: -5s;
        }
        @keyframes blobFloat {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-20px, 15px) scale(1.05); }
        }

        /* ── Main Container ── */
        .error-container {
            position: relative;
            z-index: 10;
            text-align: center;
            padding: 1.5rem;
            max-width: 520px;
            width: 100%;
        }

        /* ── Brand Logo ── */
        .brand-header {
            margin-bottom: 2.5rem;
            animation: fadeSlide 0.6s ease forwards;
        }
        .brand-header img {
            height: 48px;
            object-fit: contain;
        }

        /* ── Card ── */
        .error-card {
            background: white;
            border: 1px solid var(--border-light);
            border-radius: 24px;
            padding: 3rem 2.5rem 2.5rem;
            box-shadow:
                0 1px 3px rgba(0,0,0,0.04),
                0 10px 30px rgba(0,0,0,0.06);
            animation: fadeSlide 0.6s 0.1s ease forwards;
            opacity: 0;
            transform: translateY(20px);
        }
        @keyframes fadeSlide {
            to { opacity: 1; transform: translateY(0); }
        }

        /* ── Shield Badge ── */
        .shield-badge {
            width: 88px;
            height: 88px;
            border-radius: 22px;
            background: linear-gradient(135deg, rgba(239,68,68,0.08), rgba(249,115,22,0.08));
            border: 1px solid rgba(239,68,68,0.1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            position: relative;
        }
        .shield-badge i {
            font-size: 2.2rem;
            color: #ef4444;
        }
        .shield-badge::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 26px;
            border: 1.5px dashed rgba(239,68,68,0.12);
            animation: spinSlow 20s linear infinite;
        }
        @keyframes spinSlow {
            to { transform: rotate(360deg); }
        }

        /* ── Error Code ── */
        .error-code {
            font-family: 'Outfit', sans-serif;
            font-size: 4rem;
            font-weight: 800;
            letter-spacing: -3px;
            background: linear-gradient(135deg, #ef4444, #f97316);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .error-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.75rem;
        }

        .error-description {
            color: var(--text-muted);
            font-size: 0.92rem;
            line-height: 1.7;
            margin-bottom: 1.5rem;
            max-width: 380px;
            margin-left: auto;
            margin-right: auto;
        }

        /* ── Permission Tag ── */
        .permission-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(239, 68, 68, 0.06);
            border: 1px solid rgba(239, 68, 68, 0.1);
            color: #dc2626;
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 0.78rem;
            font-weight: 600;
            margin-bottom: 2rem;
        }

        /* ── Buttons (matching landing page style) ── */
        .actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-brand {
            background: var(--brand-gradient);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 14px rgba(34, 197, 94, 0.2);
        }
        .btn-brand:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.3);
            color: white;
        }
        .btn-brand:active { transform: scale(0.97); }

        .btn-outline {
            background: white;
            color: var(--text-main);
            border: 1.5px solid rgba(15, 23, 42, 0.1);
            padding: 12px 28px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-outline:hover {
            background: var(--bg-subtle);
            border-color: rgba(15, 23, 42, 0.15);
            transform: translateY(-2px);
            color: var(--text-main);
        }

        /* ── Footer ── */
        .error-footer {
            margin-top: 2rem;
            color: var(--text-muted);
            font-size: 0.78rem;
            animation: fadeSlide 0.6s 0.3s ease forwards;
            opacity: 0;
        }
        .error-footer a {
            color: var(--brand-primary);
            text-decoration: none;
            font-weight: 600;
        }
        .error-footer a:hover {
            text-decoration: underline;
        }

        /* ── Responsive ── */
        @media (max-width: 480px) {
            .error-card { padding: 2rem 1.5rem 2rem; border-radius: 20px; }
            .error-code { font-size: 3.2rem; }
            .error-title { font-size: 1.15rem; }
            .shield-badge { width: 72px; height: 72px; border-radius: 18px; }
            .shield-badge i { font-size: 1.8rem; }
            .actions { flex-direction: column; }
            .btn-brand, .btn-outline { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
    <!-- Background Effects -->
    <div class="bg-pattern"></div>
    <div class="bg-blob bg-blob-1"></div>
    <div class="bg-blob bg-blob-2"></div>

    <div class="error-container">
        <!-- Brand -->
        <div class="brand-header">
            <a href="{{ url('/') }}">
                <img src="{{ asset('images/ng_logo.png') }}" alt="Noubtigo">
            </a>
        </div>

        <!-- Card -->
        <div class="error-card">
            <div class="shield-badge">
                <i class="bi bi-shield-lock-fill"></i>
            </div>

            <div class="error-code">403</div>
            <h1 class="error-title">{{ __('ui.access_denied') ?? 'Access Denied' }}</h1>
            <p class="error-description">
                {{ __('ui.access_denied_message') ?? "You don't have the required permission to view this page. Please contact your administrator if you believe this is an error." }}
            </p>

            <div class="permission-tag">
                <i class="bi bi-lock-fill"></i>
                {{ __('ui.insufficient_permissions') ?? 'Insufficient Permissions' }}
            </div>

            <div class="actions">
                @auth
                <a href="{{ route('dashboard') }}" class="btn-brand">
                    <i class="bi bi-grid-fill"></i>
                    {{ __('ui.go_to_dashboard') ?? 'Go to Dashboard' }}
                </a>
                @else
                <a href="{{ url('/') }}" class="btn-brand">
                    <i class="bi bi-house-fill"></i>
                    {{ __('ui.go_home') ?? 'Go Home' }}
                </a>
                @endauth
                <button onclick="history.back()" class="btn-outline">
                    <i class="bi bi-arrow-left"></i>
                    {{ __('ui.go_back') ?? 'Go Back' }}
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div class="error-footer">
            {{ __('ui.need_help') ?? 'Need help?' }}
            <a href="{{ url('/') }}">{{ __('ui.contact_support') ?? 'Contact Support' }}</a>
        </div>
    </div>
</body>
</html>
