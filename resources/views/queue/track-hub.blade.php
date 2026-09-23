<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $company->name }} - {{ __('ui.queue_tracking') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Cairo:wght@400;600;700&display=swap"
        rel="stylesheet">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    @if(app()->getLocale() == 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    @endif

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #22c55e, #06b6d4);
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.4);
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        body {
            font-family:
                {{ app()->getLocale() == 'ar' ? "'Cairo', sans-serif" : "'Outfit', sans-serif" }}
            ;
            background: #f1f5f9;
            background-image:
                radial-gradient(at 0% 0%, rgba(34, 197, 94, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(6, 182, 212, 0.05) 0px, transparent 50%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: var(--text-main);
        }

        .hub-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .hub-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            overflow: hidden;
            padding: 40px 30px;
        }

        .company-logo-wrapper {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            padding: 12px;
        }

        .company-logo-wrapper img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .option-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 16px;
            position: relative;
            overflow: hidden;
        }

        .option-card:hover {
            transform: translateY(-4px);
            border-color: #22c55e;
            box-shadow: 0 12px 24px -10px rgba(34, 197, 94, 0.3);
            color: inherit;
        }

        .option-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .icon-ticket {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
        }

        .icon-whatsapp {
            background: rgba(37, 211, 102, 0.1);
            color: #25d366;
        }

        .icon-portal {
            background: rgba(6, 182, 212, 0.1);
            color: #06b6d4;
        }

        .option-info h4 {
            margin: 0;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .option-info p {
            margin: 0;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .badge-pro {
            background: var(--primary-gradient);
            color: white;
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 100px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-enterprise {
            background: linear-gradient(135deg, #7c3aed, #db2777);
            color: white;
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 100px;
            font-weight: 700;
            text-transform: uppercase;
        }

        footer {
            padding: 20px;
            text-align: center;
            opacity: 0.6;
            font-size: 0.85rem;
        }
    </style>
</head>

<body>

    <div class="hub-container">
        <div class="hub-card animate__animated animate__fadeInUp">
            <div class="text-center mb-4">
                <div class="company-logo-wrapper">
                    <img src="{{ $company->getLogoUrl() }}" alt="{{ $company->name }}">
                </div>
                <h3 class="fw-bold mb-1">{{ $company->name }}</h3>
                <p class="text-muted">{{ __('ui.welcome_to_tracking') }}</p>
            </div>

            @php
                session(['last_tracking_company_id' => $company->id]);
                $waNumber = preg_replace('/[^0-9]/', '', config('services.whatsapp.public_number', $company->phone));
            @endphp

            <div class="options-list">
                <!-- Option 1: Track by Ticket -->
                <a href="{{ route('queue.track.landing', $company->secure_public_token) }}"
                    class="option-card">
                    <div class="option-icon icon-ticket">
                        <i class="bi bi-ticket-perforated-fill"></i>
                    </div>
                    <div class="option-info">
                        <h4>{{ __('ui.track_by_ticket') }}</h4>
                        <p>{{ __('ui.track_by_ticket_desc') }}</p>
                    </div>
                    <i class="bi bi-chevron-right ms-auto text-muted"></i>
                </a>

                <!-- Option 2: WhatsApp Tracking (only if company has whatsapp feature) -->
                @if($company->hasFeature('whatsapp.send'))
                <a href="https://wa.me/{{ $waNumber }}?text={{ urlencode(__('ui.whatsapp_track_msg', ['code' => $company->code])) }}"
                    target="_blank" class="option-card">
                    <div class="option-icon icon-whatsapp">
                        <i class="bi bi-whatsapp"></i>
                    </div>
                    <div class="option-info">
                        <div class="d-flex align-items-center gap-2">
                            <h4>{{ __('ui.track_via_whatsapp') }}</h4>
                            <span class="badge-pro">PRO</span>
                        </div>
                        <p>{{ __('ui.track_via_whatsapp_desc') }}</p>
                    </div>
                    <i class="bi bi-chevron-right ms-auto text-muted"></i>
                </a>
                @endif

                <!-- Option 3: Customer Account Portal -->
                <a href="{{ route('customer.login') }}" class="option-card">
                    <div class="option-icon icon-portal">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <div class="option-info">
                        <div class="d-flex align-items-center gap-2">
                            <h4>{{ __('ui.customer_portal') }}</h4>
                            <span class="badge-enterprise">NEW</span>
                        </div>
                        <p>{{ __('ui.customer_portal_desc') }}</p>
                    </div>
                    <i class="bi bi-chevron-right ms-auto text-muted"></i>
                </a>
            </div>

            <div class="mt-4 pt-3 border-top text-center">
                <p class="small text-muted mb-0 d-flex align-items-center justify-content-center gap-2">
                    {{ __('ui.powered_by') }}
                    <img src="{{ asset('images/ng_logo.png') }}" alt="Noubtigo" style="height: 30px;">
                </p>
            </div>
        </div>
    </div>

    <footer>
        &copy; {{ date('Y') }} NoubtiGO. All rights reserved.
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
