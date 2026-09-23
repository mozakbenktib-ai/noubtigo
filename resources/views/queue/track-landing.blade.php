<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.track_ticket') }}</title>

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

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #22c55e, #06b6d4);
            --primary-color: #22c55e;
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

        .tracker-card {
            background: white;
            border-radius: 28px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .tracker-header {
            background: var(--primary-gradient);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .company-badge {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 8px 16px;
            border-radius: 100px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .company-badge img {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            object-fit: contain;
        }

        .tracker-body {
            padding: 35px 30px;
        }

        .form-label {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 8px;
            padding-left: 4px;
        }

        .form-control {
            border-radius: 14px;
            padding: 14px 18px;
            border: 1.5px solid #e2e8f0;
            background: #fdfdfd;
            font-weight: 600;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.1);
            background: white;
        }

        .btn-gradient {
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 14px;
            padding: 16px;
            font-weight: 700;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(34, 197, 94, 0.2);
            margin-top: 10px;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px rgba(34, 197, 94, 0.3);
            color: white;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 500;
            margin-top: 20px;
            transition: color 0.2s;
        }

        .back-btn:hover {
            color: var(--primary-color);
        }
    </style>
</head>

<body>

    <div class="tracker-card">
        <div class="tracker-header">
            @if($company)
            <div class="company-badge">
                <img src="{{ $company->getLogoUrl() }}" alt="">
                <span>{{ $company->name }}</span>
            </div>
            @else
            <div class="mb-4">
                <img src="{{ asset('images/ng_logo.png') }}" alt="Noubtigo" height="40" style="filter: brightness(0) invert(1);">
            </div>
            @endif

            <h3 class="fw-bold mb-1">{{ __('ui.track_ticket') }}</h3>
            <p class="mb-0 opacity-75 small">{{ __('ui.enter_ticket_details') }}</p>
        </div>

        <div class="tracker-body">
            @if($errors->any())
                <div class="alert alert-danger border-0 rounded-4 small mb-4 d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <!-- Navigation Tabs for Search Mode -->
            <ul class="nav nav-pills nav-fill mb-4 p-1 bg-light rounded-4" id="trackTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-3 py-2 fw-semibold small" id="ticket-tab" data-bs-toggle="pill" data-bs-target="#ticket-pane" type="button" role="tab">
                        <i class="bi bi-ticket-perforated me-1"></i> {{ __('ui.ticket_number') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-3 py-2 fw-semibold small" id="phone-tab" data-bs-toggle="pill" data-bs-target="#phone-pane" type="button" role="tab">
                        <i class="bi bi-telephone me-1"></i> {{ __('ui.phone_number') }}
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="trackTabContent">
                <!-- Track by Ticket -->
                <div class="tab-pane fade show active" id="ticket-pane" role="tabpanel">
                    <form action="{{ route('queue.track.verify') }}" method="POST">
                        @csrf
                        @if($company)
                            <input type="hidden" name="c" value="{{ $company->secure_public_token }}">
                        @endif

                        <div class="mb-4">
                            <label class="form-label">{{ __('ui.ticket_number') }}</label>
                            <input type="text" name="ticket_number" class="form-control" placeholder="e.g. A-001"
                                value="{{ old('ticket_number') }}" autofocus style="text-transform: uppercase;">
                        </div>

                        <button type="submit" class="btn btn-gradient">
                            {{ __('ui.verify_and_track') }} <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </form>
                </div>

                <!-- Track by Phone -->
                <div class="tab-pane fade" id="phone-pane" role="tabpanel">
                    <form action="{{ route('queue.track.verify') }}" method="POST">
                        @csrf
                        @if($company)
                            <input type="hidden" name="c" value="{{ $company->secure_public_token }}">
                        @endif

                        <div class="mb-4">
                            <label class="form-label">{{ __('ui.phone_number') }}</label>
                            <input type="tel" name="phone" class="form-control" placeholder="e.g. 0612345678 or +212612345678"
                                value="{{ old('phone') }}">
                        </div>

                        <button type="submit" class="btn btn-gradient">
                            {{ __('ui.verify_and_track') }} <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="text-center">
                @if($company)
                <a href="{{ route('queue.track.hub', $company->secure_public_token) }}" class="back-btn">
                    <i class="bi bi-arrow-left"></i> {{ __('ui.back_to_hub') ?? 'Back to Options' }}
                </a>
                @else
                <a href="{{ route('landing') }}" class="back-btn">
                    <i class="bi bi-house"></i> {{ __('ui.back_to_home') ?? 'Back to Home' }}
                </a>
                @endif
            </div>
        </div>

        <div class="text-center pb-4">
            <p class="small text-secondary mb-0">
                {{ __('ui.powered_by') }} 
                <span class="fw-bold" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Noubtigo</span>
            </p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>