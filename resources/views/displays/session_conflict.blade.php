<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.display_session_in_use') ?? 'Display In Use' }} | {{ $device->name }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @if(app()->getLocale() == 'ar')
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @endif
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --bg-body: #060b18;
            --bg-card: rgba(15, 23, 42, 0.85);
            --border-glass: rgba(255, 255, 255, 0.1);
            --border-glass-strong: rgba(255, 255, 255, 0.2);
            --brand-green: #22c55e;
            --brand-cyan: #06b6d4;
            --brand-amber: #f59e0b;
            --glow-amber: 0 0 35px rgba(245, 158, 11, 0.35);
        }

        body {
            margin: 0;
            padding: 0;
            background-color: var(--bg-body);
            color: #ffffff;
            font-family: 'Outfit', 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
        }

        [dir="rtl"] {
            font-family: 'Noto Sans Arabic', 'Outfit', sans-serif;
        }

        .ambient-glow {
            position: absolute;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background: 
                radial-gradient(circle at 20% 25%, rgba(245, 158, 11, 0.12) 0%, transparent 45%),
                radial-gradient(circle at 80% 75%, rgba(6, 182, 212, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(15, 23, 42, 0.9) 0%, #060b18 100%);
        }

        .conflict-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 680px;
            padding: 2rem;
        }

        .conflict-card {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-glass);
            border-radius: 2rem;
            padding: 3rem 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            text-align: center;
        }

        .conflict-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.25rem;
            border-radius: 9999px;
            background: rgba(245, 158, 11, 0.15);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: #fbbf24;
            font-size: 0.875rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }

        .icon-circle {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            background: rgba(245, 158, 11, 0.12);
            border: 2px solid rgba(245, 158, 11, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.75rem auto;
            color: #fbbf24;
            font-size: 2.5rem;
            box-shadow: var(--glow-amber);
            animation: pulse-glow 3s infinite ease-in-out;
        }

        @keyframes pulse-glow {
            0%, 100% { transform: scale(1); box-shadow: 0 0 25px rgba(245, 158, 11, 0.25); }
            50% { transform: scale(1.04); box-shadow: 0 0 45px rgba(245, 158, 11, 0.45); }
        }

        .conflict-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.75rem;
            color: #ffffff;
        }

        .conflict-subtitle {
            color: #94a3b8;
            font-size: 1.05rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .device-info-box {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-glass);
            border-radius: 1.25rem;
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        [dir="rtl"] .device-info-box {
            text-align: right;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.4rem 0;
            font-size: 0.95rem;
        }

        .info-row:not(:last-child) {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .info-label {
            color: #94a3b8;
        }

        .info-value {
            color: #f1f5f9;
            font-weight: 600;
        }

        .btn-takeover {
            background: linear-gradient(135deg, #22c55e 0%, #06b6d4 100%);
            color: #ffffff;
            border: none;
            padding: 1rem 2rem;
            font-size: 1.15rem;
            font-weight: 700;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            width: 100%;
            cursor: pointer;
            box-shadow: 0 10px 25px -5px rgba(34, 197, 94, 0.4);
            transition: all 0.25s ease;
        }

        .btn-takeover:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(34, 197, 94, 0.5);
            color: #ffffff;
        }

        .btn-retry {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--border-glass);
            color: #cbd5e1;
            padding: 0.85rem 1.75rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            margin-top: 0.75rem;
        }

        .btn-retry:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="ambient-glow"></div>

    <div class="conflict-container">
        <div class="conflict-card">
            <div class="conflict-badge">
                <i class="bi bi-shield-lock-fill"></i>
                {{ __('ui.single_session_restricted') ?? '1 Display = 1 Screen Session' }}
            </div>

            <div class="icon-circle">
                <i class="bi bi-display"></i>
            </div>

            <h1 class="conflict-title">
                {{ __('ui.display_session_in_use') ?? 'Display Already Active on Another Screen' }}
            </h1>

            <p class="conflict-subtitle">
                {{ __('ui.display_session_in_use_desc') ?? 'This display link is already running on another screen or device. To maintain consistent ticket calling and queue accuracy, each display can only be open on one screen at a time.' }}
            </p>

            <div class="device-info-box">
                <div class="info-row">
                    <span class="info-label">{{ __('ui.display_name') ?? 'Display Name' }}:</span>
                    <span class="info-value">{{ $device->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">{{ __('ui.assigned_room') ?? 'Assigned Room' }}:</span>
                    <span class="info-value">{{ $room ? $room->name : __('ui.global') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">{{ __('ui.status') ?? 'Status' }}:</span>
                    <span class="info-value text-warning">
                        <i class="bi bi-broadcast me-1"></i> {{ __('ui.display_session_active_now') ?? 'Active session online' }}
                    </span>
                </div>
                @if($device->last_seen_at)
                <div class="info-row">
                    <span class="info-label">{{ __('ui.last_activity') ?? 'Last Heartbeat' }}:</span>
                    <span class="info-value text-white-50">{{ $device->last_seen_at->diffForHumans() }}</span>
                </div>
                @endif
            </div>

            <!-- Take Over Form -->
            <form action="{{ route('queue.display.takeover', $device->device_token) }}" method="POST" id="takeover-form">
                @csrf
                <input type="hidden" name="session_id" id="client-session-id" value="">
                
                <button type="submit" class="btn-takeover">
                    <i class="bi bi-box-arrow-in-up-right fs-5"></i>
                    {{ __('ui.take_over_display') ?? 'Take Over Display' }}
                </button>
            </form>

            <button type="button" onclick="window.location.reload();" class="btn-retry">
                <i class="bi bi-arrow-clockwise"></i>
                {{ __('ui.check_again') ?? 'Check Again / Refresh' }}
            </button>

            @if(auth()->check())
            <div class="mt-4 pt-3 border-top border-white border-opacity-10">
                <a href="{{ route('displays.index') }}" class="text-secondary small text-decoration-none d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i> {{ __('ui.back_to_dashboard') ?? 'Back to Displays Dashboard' }}
                </a>
            </div>
            @endif
        </div>
    </div>

    <script>
        // Ensure this client tab has its unique session id in sessionStorage
        document.addEventListener('DOMContentLoaded', () => {
            let mySessionId = sessionStorage.getItem('display_client_session_id');
            if (!mySessionId) {
                mySessionId = 'sess_' + Math.random().toString(36).substring(2, 12) + '_' + Date.now().toString(36);
                sessionStorage.setItem('display_client_session_id', mySessionId);
            }
            const inputEl = document.getElementById('client-session-id');
            if (inputEl) {
                inputEl.value = mySessionId;
            }
        });
    </script>
</body>
</html>
