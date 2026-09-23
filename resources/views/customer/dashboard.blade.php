<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ __('ui.dashboard') }} - {{ __('ui.customer_portal') }}</title>

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
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
            --bg-soft: #f1f5f9;
            --glass-bg: rgba(255, 255, 255, 0.8);
            --glass-border: rgba(255, 255, 255, 0.5);
            --accent-color: #22c55e;
            --nav-height: 70px;
        }

        body {
            font-family: {{ app()->getLocale() == 'ar' ? "'Cairo', sans-serif" : "'Outfit', sans-serif" }};
            background: var(--bg-soft);
            background-attachment: fixed;
            color: #1e293b;
            padding-bottom: calc(var(--nav-height) + 30px);
        }

        /* Glassmorphism Classes */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        }

        /* Top Header */
        .header {
            padding: 30px 0;
            background: var(--primary-gradient);
            color: white;
            border-bottom-left-radius: 30px;
            border-bottom-right-radius: 30px;
            margin-bottom: -60px;
            position: relative;
            z-index: 1;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            background: rgba(255,255,255,0.2);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 700;
        }

        /* Bottom Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            height: var(--nav-height);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            display: flex;
            justify-content: space-around;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            z-index: 1000;
            border: 1px solid rgba(255,255,255,0.5);
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #64748b;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.3s;
            cursor: pointer;
        }

        .nav-item i {
            font-size: 22px;
            margin-bottom: 2px;
        }

        .nav-item.active {
            color: var(--accent-color);
        }

        .nav-item.active i {
            transform: translateY(-5px);
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Main Container */
        .main-container {
            position: relative;
            z-index: 2;
            padding-top: 0;
        }

        /* Section Tab Logic */
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }

        /* Ticket Cards */
        .ticket-card {
            padding: 20px;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }

        .ticket-card:active {
            transform: scale(0.98);
        }

        .status-pill {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 100px;
            letter-spacing: 0.5px;
        }

        .status-waiting { background: #fef3c7; color: #d97706; }
        .status-called { background: #dcfce7; color: #16a34a; }
        .status-serving { background: #f0fdf4; color: #15803d; border: 1px solid #16a34a; animation: pulse 2s infinite; }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(22, 163, 74, 0); }
            100% { box-shadow: 0 0 0 0 rgba(22, 163, 74, 0); }
        }

        /* Favorites UI */
        .fav-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 15px;
            padding: 10px 0;
        }

        .fav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: inherit;
            position: relative;
        }

        .fav-logo {
            width: 65px;
            height: 65px;
            background: white;
            border-radius: 20px;
            padding: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 8px;
            transition: all 0.2s;
        }

        .fav-item:hover .fav-logo {
            transform: rotate(5deg) scale(1.05);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .fav-remove {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 24px;
            height: 24px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            border: 2px solid white;
            opacity: 0;
            transition: all 0.2s;
            z-index: 5;
        }

        .fav-item:hover .fav-remove {
            opacity: 1;
        }

        /* Profile Forms */
        .form-floating > .form-control:focus, .form-floating > .form-control:not(:placeholder-shown) {
            padding-top: 1.625rem;
            padding-bottom: 0.625rem;
        }
        
        .form-control {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 12px 16px;
        }

        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.1);
            border-color: #22c55e;
        }

        .btn-premium {
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 14px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(34, 197, 94, 0.2);
            color: white;
        }

        /* RTL Adjustments */
        [dir="rtl"] .ms-auto { margin-right: auto !important; margin-left: 0 !important; }
        [dir="rtl"] .fav-remove { right: auto; left: -5px; }

    </style>
</head>

<body>

    <!-- Top Header -->
    <div class="header">
        <div class="container d-flex align-items-center justify-content-between">
            <div>
                <h4 class="fw-bold mb-0">{{ __('ui.welcome_back') }}, {{ $customer->first_name }}</h4>
                <p class="small mb-0 opacity-75"><i class="bi bi-geo-alt"></i> {{ __('ui.customer_portal') }}</p>
            </div>
            <div class="user-avatar">
                {{ substr($customer->first_name, 0, 1) }}
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-container container">
        
        <!-- Tab: Home -->
        <div id="tab-home" class="tab-content active animate__animated animate__fadeIn">
            <!-- Active Tickets Section -->
            <div class="section-title d-flex justify-content-between align-items-center mb-3 mt-5" style="margin-top: 70px !important;">
                <h6 class="fw-bold mb-0">{{ __('ui.current_active_tickets') }}</h6>
                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">{{ count($activeTickets) }}</span>
            </div>

            <div class="active-tickets mb-4">
                @forelse($activeTickets as $ticket)
                <div class="glass-card ticket-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $ticket->company->getLogoUrl() }}" alt="" style="width: 40px; height: 40px; border-radius: 10px; object-fit: contain;">
                            <div>
                                <h6 class="fw-bold mb-0">{{ $ticket->company->name }}</h6>
                                <p class="small text-muted mb-0">{{ $ticket->service?->name ?? __('ui.general_queue') }}</p>
                            </div>
                        </div>
                        <span class="status-pill status-{{ $ticket->status }}">{{ __('ui.status_' . $ticket->status) }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="ticket-number text-center p-2 rounded-3" style="background: #f8fafc; min-width: 80px;">
                            <div class="small text-muted">{{ __('ui.ticket') }}</div>
                            <div class="fw-bold fs-4">#{{ $ticket->ticket_number }}</div>
                        </div>
                        <a href="{{ route('customer.track-ticket', $ticket->id) }}" class="btn btn-premium btn-sm rounded-pill">
                            {{ __('ui.track_now') }} <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                @empty
                <div class="glass-card p-5 text-center text-muted">
                    <i class="bi bi-ticket-perforated fs-1 opacity-25 mb-3 d-block"></i>
                    <p class="small mb-0">{{ __('ui.no_active_tickets') }}</p>
                </div>
                @endforelse
            </div>

            <!-- Favorites Section -->
            <div class="section-title d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">{{ __('ui.favorite_companies') }}</h6>
                <button class="btn btn-sm btn-link text-success text-decoration-none p-0" data-bs-toggle="modal" data-bs-target="#addCompanyModal">
                    <i class="bi bi-plus-lg"></i> {{ __('ui.add_new') }}
                </button>
            </div>

            <div class="glass-card p-3">
                <div class="fav-grid">
                    @forelse($favorites as $fav)
                    <div class="fav-item">
                        <form action="{{ route('customer.favorites.remove', $fav->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="fav-remove" onclick="return confirm('{{ __('ui.confirm_remove_fav') ?? 'Remove from favorites?' }}')">
                                <i class="bi bi-x"></i>
                            </button>
                        </form>
                        <a href="{{ route('customer.track-company', $fav->id) }}" class="text-decoration-none text-center">
                            <img src="{{ $fav->getLogoUrl() }}" alt="" class="fav-logo">
                            <div class="small fw-semibold text-truncate" style="max-width: 70px;">{{ $fav->name }}</div>
                        </a>
                    </div>
                    @empty
                    <div class="w-100 text-center py-4 text-muted small">
                        {{ __('ui.no_favorites_yet') }}
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Tab: History -->
        <div id="tab-history" class="tab-content animate__animated animate__fadeIn">
            <h6 class="fw-bold mb-4 mt-5" style="margin-top: 70px !important;">{{ __('ui.visit_history') }}</h6>
            
            @forelse($history as $h)
            <div class="glass-card p-3 mb-3 d-flex gap-3 align-items-center">
                <img src="{{ $h->company->getLogoUrl() }}" alt="" style="width: 45px; height: 45px; border-radius: 12px; object-fit: contain;">
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <h6 class="fw-bold mb-0">{{ $h->company->name }}</h6>
                        <span class="small text-muted">{{ $h->created_at->format('d M, H:i') }}</span>
                    </div>
                    <div class="small text-muted">{{ $h->service?->name ?? __('ui.general_queue') }} • #{{ $h->ticket_number }}</div>
                </div>
                <div class="text-end">
                    <span class="badge bg-light text-muted small">{{ __('ui.status_' . $h->status) }}</span>
                </div>
            </div>
            @empty
            <div class="glass-card p-5 text-center text-muted">
                <p class="small mb-0">{{ __('ui.no_history_yet') }}</p>
            </div>
            @endforelse
        </div>

        <!-- Tab: Profile -->
        <div id="tab-profile" class="tab-content animate__animated animate__fadeIn">
            <h6 class="fw-bold mb-4 mt-5" style="margin-top: 70px !important;">{{ __('ui.profile_settings') ?? 'Account Settings' }}</h6>
            
            @if(session('success'))
            <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4">
                {{ session('success') }}
            </div>
            @endif

            <div class="glass-card p-4">
                <form action="{{ route('customer.profile.update') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">{{ __('ui.first_name') }}</label>
                            <input type="text" name="first_name" class="form-control" value="{{ $customer->first_name }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">{{ __('ui.last_name') }}</label>
                            <input type="text" name="last_name" class="form-control" value="{{ $customer->last_name }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">{{ __('ui.email') }}</label>
                            <input type="email" name="email" class="form-control" value="{{ $customer->email }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted d-block">{{ __('ui.phone_number') }}</label>
                            <input type="tel" id="customer_phone" class="form-control w-100" value="{{ $customer->phone }}" required>
                            <input type="hidden" name="phone" id="hidden_customer_phone" value="{{ $customer->phone }}">
                            <div id="customer_phone_error" class="text-danger small mt-1 d-none">Invalid phone number</div>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-premium w-100">
                                {{ __('ui.save_changes') ?? 'Save Changes' }}
                            </button>
                        </div>
                    </div>
                </form>

                <hr class="my-4 opacity-10">

                <form action="{{ route('customer.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-light text-danger w-100 rounded-4 py-3">
                        <i class="bi bi-box-arrow-right me-2"></i> {{ __('ui.logout') }}
                    </button>
                </form>
            </div>
        </div>

    </div>

    <!-- Bottom Navigation -->
    <div class="bottom-nav">
        <div class="nav-item active" onclick="showTab('home', this)">
            <i class="bi bi-house-door"></i>
            <span>{{ __('ui.home') ?? 'Home' }}</span>
        </div>
        <div class="nav-item" onclick="showTab('history', this)">
            <i class="bi bi-clock-history"></i>
            <span>{{ __('ui.history') ?? 'History' }}</span>
        </div>
        <div class="nav-item" onclick="showTab('profile', this)">
            <i class="bi bi-person"></i>
            <span>{{ __('ui.profile') ?? 'Profile' }}</span>
        </div>
    </div>

    <!-- Modals (Add Company) -->
    <div class="modal fade" id="addCompanyModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card border-0">
                <div class="modal-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">{{ __('ui.add_company_by_code') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('customer.add-by-code') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <input type="text" name="company_code" class="form-control form-control-lg text-center fw-bold" placeholder="NOUBTIGO" style="letter-spacing: 2px; text-transform: uppercase;" required>
                        </div>
                        <button type="submit" class="btn btn-premium w-100 py-3">
                            <i class="bi bi-plus-circle me-1"></i> {{ __('ui.add_to_favorites') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/intlTelInput.min.js"></script>
    <script>
        // Initialize intlTelInput for profile update
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
        function showTab(tabName, el) {
            // Update Nav
            document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
            el.classList.add('active');

            // Update Content
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.getElementById('tab-' + tabName).classList.add('active');

            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Handle direct links if needed (e.g. from redirect)
        @if(session('tab'))
            const targetTab = '{{ session('tab') }}';
            const targetEl = document.querySelector(`.nav-item[onclick*="${targetTab}"]`);
            if(targetEl) showTab(targetTab, targetEl);
        @endif
    </script>

</body>

</html>
