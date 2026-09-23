<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ __('ui.track_ticket') }} #{{ $ticket->ticket_number }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    @if(app()->getLocale() == 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    @endif
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            @php
                $branding = $ticket->company ? $ticket->company->getBrandingColors() : [
                    'primary' => '#22c55e',
                    'secondary' => '#06b6d4',
                    'gradient' => 'linear-gradient(135deg, #22c55e, #06b6d4)'
                ];
            @endphp
            --primary-color: {{ $branding['primary'] }};
            --secondary-color: {{ $branding['secondary'] }};
            --primary-gradient: {{ $branding['gradient'] }};
            --bg-color: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }
        
        body {
            font-family: {{ app()->getLocale() == 'ar' ? "'Cairo', sans-serif" : "'Inter', sans-serif" }};
            background: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Top Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            background: transparent;
            z-index: 10;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-logo img {
            height: 36px;
            object-fit: contain;
        }
        
        .brand-name {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text-main);
            margin: 0;
            letter-spacing: -0.5px;
        }

        /* Language Switcher */
        .lang-btn {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 8px 12px;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-main);
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            transition: all 0.2s;
        }
        
        .lang-btn:active {
            transform: scale(0.97);
        }

        /* Main Container */
        .main-container {
            flex: 1;
            padding: 10px 24px 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 500px;
            margin: 0 auto;
            width: 100%;
        }

        /* Ticket Card */
        .ticket-card {
            background: white;
            border-radius: 32px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0,0,0,0.02);
            width: 100%;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.03);
            margin-bottom: 30px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .ticket-number {
            font-size: 4rem;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -2px;
            margin-bottom: 5px;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        .ticket-service {
            font-size: 1rem;
            color: var(--text-muted);
            font-weight: 500;
            margin-bottom: 25px;
        }

        /* Status Displays */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 100px;
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 20px;
        }

        .status-waiting {
            background: #f1f5f9;
            color: var(--text-muted);
        }

        .status-almost {
            background: rgba(34, 197, 94, 0.1);
            color: var(--primary-color);
            animation: pulse-soft 2s infinite;
        }

        .status-called {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 10px 20px rgba(34, 197, 94, 0.25);
            animation: bounce-subtle 2s infinite;
            padding: 14px 28px;
            font-size: 1.1rem;
        }

        .status-serving {
            background: #eff6ff;
            color: #3b82f6;
        }

        .status-done {
            background: #ecfdf5;
            color: #10b981;
        }

        /* Stats & Position */
        .position-text {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 0;
            line-height: 1.2;
        }

        .position-label {
            font-size: 1.1rem;
            color: var(--text-muted);
            font-weight: 600;
        }

        /* Visual Timeline */
        .queue-timeline {
            margin-top: 35px;
            position: relative;
            padding: 10px 0;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .queue-timeline::before {
            content: '';
            position: absolute;
            top: 20px;
            bottom: 20px;
            left: 24px;
            width: 2px;
            background: #e2e8f0;
            border-radius: 2px;
            z-index: 1;
        }

        [dir="rtl"] .queue-timeline::before {
            left: auto;
            right: 24px;
        }

        .timeline-node {
            display: flex;
            align-items: center;
            gap: 20px;
            position: relative;
            z-index: 2;
            padding-left: 10px;
        }

        [dir="rtl"] .timeline-node {
            padding-left: 0;
            padding-right: 10px;
        }

        .node-dot {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: white;
            border: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 700;
            transition: all 0.3s;
        }

        .node-content {
            flex: 1;
            text-align: left;
            font-weight: 600;
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        [dir="rtl"] .node-content {
            text-align: right;
        }

        .timeline-node.active .node-dot {
            background: var(--primary-gradient);
            border-color: transparent;
            color: white;
            transform: scale(1.2);
            box-shadow: 0 4px 10px rgba(34, 197, 94, 0.3);
        }

        .timeline-node.active .node-content {
            color: var(--primary-color);
            font-weight: 800;
            font-size: 1.1rem;
        }

        .timeline-node.completed .node-dot {
            background: #f1f5f9;
            color: #cbd5e1;
            border-color: #f1f5f9;
        }

        /* Room Display (When Called) */
        .room-display {
            background: var(--text-main);
            color: white;
            border-radius: 24px;
            padding: 25px;
            margin-top: 25px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(15, 23, 42, 0.15);
        }

        .room-display::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 150px;
            height: 150px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }

        /* Actions */
        .action-container {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn-action {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 16px;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.2s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
            text-decoration: none;
        }
        
        .btn-action:active {
            transform: scale(0.98);
        }

        .btn-action.btn-favorite {
            color: #ef4444;
            background: #fef2f2;
            border-color: #fecaca;
        }

        /* Animations */
        @keyframes pulse-soft {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.2); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 15px rgba(34, 197, 94, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }

        @keyframes bounce-subtle {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        /* Live Indicator */
        .live-indicator {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-top: 15px;
            opacity: 0.8;
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background: var(--primary-color);
            border-radius: 50%;
            animation: blink 1.5s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="page-header">
        <div class="brand-logo">
            <img src="{{ asset('images/ng_logo.png') }}" alt="Noubtigo">
            @if($ticket->company)
                <h1 class="brand-name">{{ $ticket->company->name }}</h1>
            @endif
        </div>

        <!-- Language Dropdown -->
        <div class="dropdown">
            <button class="lang-btn d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                @php
                    $currentLang = \App\Models\Language::where('code', app()->getLocale())->first();
                @endphp
                <span>{{ $currentLang->flag ?? '🌐' }}</span>
                <span class="d-none d-sm-inline">{{ strtoupper(app()->getLocale()) }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 p-2" style="border-radius: 16px;">
                @foreach(\App\Models\Language::where('is_active', true)->get() as $lang)
                    <li>
                        <form action="{{ route('language.switch') }}" method="POST">
                            @csrf
                            <input type="hidden" name="locale" value="{{ $lang->code }}">
                            <button type="submit" class="dropdown-item rounded-3 {{ app()->getLocale() == $lang->code ? 'active bg-light text-dark fw-bold' : '' }} small">
                                <span class="me-2">{{ $lang->flag }}</span> {{ $lang->name }}
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    </header>

    <main class="main-container">
        
        <!-- Main Ticket Card -->
        <div class="ticket-card">
            
            <div class="ticket-service">
                {{ $ticket->service?->name ?? __('ui.general_queue') }}
            </div>
            
            <div class="ticket-number">
                {{ $ticket->ticket_number }}
            </div>

            @php
                $peopleAhead = max(0, $position - 1);
            @endphp

            <!-- Status Logic -->
            @if($ticket->status === 'waiting')
                
                @if($peopleAhead <= 1)
                    <!-- Almost Next -->
                    <div class="status-badge status-almost mt-3">
                        <i class="bi bi-stars"></i> {{ __('ui.you_are_next') }}
                    </div>
                    <p class="position-label mt-2">{{ __('ui.please_stay_nearby') }}</p>
                @else
                    <!-- Waiting normal -->
                    <div class="status-badge status-waiting mt-3">
                        <i class="bi bi-hourglass-split"></i> {{ __('ui.status_waiting') }}
                    </div>
                    
                    <div class="mt-4 mb-2">
                        <div class="position-text">{{ $peopleAhead }}</div>
                        <div class="position-label">{{ __('ui.customers_before') }}</div>
                    </div>
                @endif

                <!-- Queue Visualization -->
                <div class="queue-timeline text-start">
                    @if($peopleAhead > 1)
                        <div class="timeline-node completed">
                            <div class="node-dot"><i class="bi bi-check"></i></div>
                            <div class="node-content">...</div>
                        </div>
                        <div class="timeline-node">
                            <div class="node-dot"><i class="bi bi-person-fill"></i></div>
                            <div class="node-content">{{ $peopleAhead }} {{ __('ui.customers_before') }}</div>
                        </div>
                    @elseif($peopleAhead == 1)
                        <div class="timeline-node">
                            <div class="node-dot"><i class="bi bi-person-fill"></i></div>
                        <div class="node-content">1 {{ __('ui.customer_before') }}</div>
                        </div>
                    @endif
                    
                    <div class="timeline-node active">
                        <div class="node-dot"><i class="bi bi-person-bounding-box"></i></div>
                        <div class="node-content">{{ __('ui.you') }}</div>
                    </div>
                </div>

            @elseif(in_array($ticket->status, ['called']))
                
                <div class="status-badge status-called mt-4 mb-2">
                    <i class="bi bi-bell-fill mb-1 me-2"></i> {{ __('ui.its_your_turn') }}
                </div>
                
                <div class="room-display">
                    <p class="small text-uppercase fw-bold text-white-50 mb-1">{{ __('ui.please_proceed_to') }}</p>
                    <h2 class="fw-bold mb-0">
                        {{ $ticket->room->name ?? __('ui.service_desk') }}
                    </h2>
                </div>

            @elseif($ticket->status === 'serving')
                
                <div class="status-badge status-serving mt-4">
                    <i class="bi bi-person-video2"></i> {{ __('ui.status_serving') }}
                </div>
                
                @if($ticket->room)
                <div class="mt-4">
                    <p class="position-label mb-1">{{ __('ui.at_counter') }}</p>
                    <h3 class="fw-bold">{{ $ticket->room->name }}</h3>
                </div>
                @endif

            @elseif($ticket->status === 'hold')
                
                <div class="status-badge bg-warning bg-opacity-10 text-warning-emphasis mt-4">
                    <i class="bi bi-pause-circle-fill"></i> {{ __('ui.status_on_hold') }}
                </div>
                <p class="mt-3 text-muted fw-medium">{{ $ticket->hold_reason ? __('ui.reason_' . $ticket->hold_reason) : __('ui.reason_other') }}</p>

            @elseif(in_array($ticket->status, ['completed', 'done']))
                
                <div class="status-badge status-done mt-4">
                    <i class="bi bi-check-circle-fill"></i> {{ __('ui.service_completed') }}
                </div>
                
            @elseif(in_array($ticket->status, ['cancelled', 'no_show']))
                
                <div class="status-badge bg-danger bg-opacity-10 text-danger mt-4">
                    <i class="bi bi-x-circle-fill"></i> {{ __('ui.ticket_cancelled') }}
                </div>
                
            @else
                <div class="status-badge status-waiting mt-4">
                    {{ ucfirst($ticket->status) }}
                </div>
            @endif

        </div>

        <div class="live-indicator mb-4">
            <div class="live-dot"></div>
            {{ __('ui.live_update') }}
        </div>



    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Auto-refresh logic (mimicking real-time for now) -->
    <script>
        // Use a gentle auto-refresh that won't disrupt animations
        // In a real PWA/Livewire setup, this would be WebSockets or Livewire polling
        setTimeout(function() {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>
