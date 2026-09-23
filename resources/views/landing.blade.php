<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- SEO Meta Tags -->
    <title>{{ __('landing.meta.title') }}</title>
    <meta name="description" content="{{ __('landing.meta.description') }}">
    <meta name="keywords" content="{{ __('landing.meta.keywords') }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @if(app()->getLocale() == 'ar')
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    @endif

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" crossorigin="anonymous">
    
    <!-- Bootstrap 5.3 CSS -->
    @if(app()->getLocale() == 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    @else
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    @endif

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top py-3" id="navbar">
        <div class="container">
            <a href="/" class="navbar-brand d-flex align-items-center">
                <img src="{{ asset('images/ng_logo.png') }}" alt="Noubtigo Logo" height="42" class="d-inline-block align-text-top logo-img">
            </a>
            
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navContent" aria-controls="navContent" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi bi-list fs-1 text-dark"></i>
            </button>

            <div class="collapse navbar-collapse" id="navContent">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-3">
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="#features">{{ __('landing.footer.links.features') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="#use-cases">{{ __('landing.navbar.use_cases') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="#how-it-works">{{ __('landing.navbar.how_it_works') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="#pricing">{{ __('landing.footer.links.pricing') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="#faq">{{ __('landing.navbar.faq') }}</a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center gap-3 nav-buttons">
                    <!-- Language Selector -->
                    <form action="{{ route('language.switch') }}" method="POST" class="me-2">
                        @csrf
                        <select name="locale" onchange="this.form.submit()" class="form-select form-select-sm border-0 bg-transparent fw-semibold cursor-pointer select-lang">
                            <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>EN</option>
                            <option value="fr" {{ app()->getLocale() == 'fr' ? 'selected' : '' }}>FR</option>
                            <option value="ar" {{ app()->getLocale() == 'ar' ? 'selected' : '' }}>AR</option>
                        </select>
                    </form>
                    
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary rounded-pill px-4">{{ __('ui.dashboard') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-link nav-link fw-semibold text-decoration-none px-0">{{ __('ui.login') }}</a>
                        <a href="{{ route('register') }}" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm text-white">{{ __('ui.signup') }}</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- 1. Hero Section -->
    <header class="hero-section position-relative overflow-hidden d-flex align-items-center">
        <div class="container position-relative z-index-2">
            <div class="row align-items-center justify-content-center text-center">
                <div class="col-lg-10 col-xl-9 hero-content-box">
                    <span class="badge badge-brand mb-3 py-2 px-3 rounded-pill text-uppercase tracking-wider">
                        <i class="bi bi-stars me-2 text-brand"></i>{{ __('landing.hero.badge') }}
                    </span>
                    <h1 class="display-3 fw-extrabold mb-4 lh-sm">
                        {!! __('landing.hero.title') !!}
                    </h1>
                    <p class="lead text-muted mx-auto mb-5 max-w-700">
                        {{ __('landing.hero.subtitle') }}
                    </p>
                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mb-5 pb-3">
                        <a href="{{ route('register') }}" class="btn btn-primary btn-lg rounded-pill px-5 py-3 shadow-lg hover-up">
                            {{ __('landing.hero.cta_trial') }} <i class="bi bi-arrow-right ms-2 fs-6"></i>
                        </a>
                        <a href="#how-it-works" class="btn btn-outline-secondary btn-lg rounded-pill px-5 py-3 hover-up">
                            {{ __('landing.hero.cta_demo') }} <i class="bi bi-play-circle ms-2 fs-6"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Hero Ecosystem Visual Showcase (Replaces old dashboard table mockup) -->
                <div class="col-12 mt-4 hero-ecosystem-container">
                    <div class="hero-ecosystem-card shadow-2xl rounded-4 bg-white p-3 p-lg-4 position-relative">
                        
                        <!-- Interactive Top Stage Navigation Pills -->
                        <div class="hero-stage-nav d-flex flex-wrap align-items-center justify-content-center gap-2 mb-3">
                            <button type="button" class="hero-stage-tab active" data-stage="1">
                                <span class="stage-step-num">1</span>
                                <span class="stage-step-text">{{ __('landing.hero.stage1_title') }}</span>
                            </button>
                            <span class="stage-flow-arrow d-none d-md-inline"><i class="bi bi-chevron-right"></i></span>
                            <button type="button" class="hero-stage-tab" data-stage="2">
                                <span class="stage-step-num">2</span>
                                <span class="stage-step-text">{{ __('landing.hero.stage2_title') }}</span>
                            </button>
                            <span class="stage-flow-arrow d-none d-md-inline"><i class="bi bi-chevron-right"></i></span>
                            <button type="button" class="hero-stage-tab" data-stage="3">
                                <span class="stage-step-num">3</span>
                                <span class="stage-step-text">{{ __('landing.hero.stage3_title') }}</span>
                            </button>
                        </div>

                        <!-- Main Hero Image Frame with Interactive Hotspots & Dynamic Focus -->
                        <div class="hero-image-stage position-relative rounded-4 overflow-hidden">
                            <img src="{{ asset('images/landing/hero-flow.png') }}" 
                                 alt="NoubtiGO Smart Queue Ecosystem: Staff Reception Check-in, Mobile QR Tracking, and Smooth Service Counter" 
                                 class="hero-flow-img img-fluid w-100" 
                                 id="heroFlowImg">
                            
                            <!-- Floating Overlay Callout Cards (Contextual info for active stage) -->
                            <div class="hero-active-callout d-flex align-items-center gap-3 p-3 rounded-3 shadow-lg" id="heroActiveCallout">
                                <div class="callout-icon-circle bg-brand text-white">
                                    <i class="bi bi-person-workspace" id="calloutIcon"></i>
                                </div>
                                <div class="text-start">
                                    <h6 class="fw-bold mb-1 text-dark" id="calloutTitle">{{ __('landing.hero.stage1_title') }}</h6>
                                    <p class="text-muted text-xs mb-0" id="calloutDesc">{{ __('landing.hero.stage1_desc') }}</p>
                                </div>
                            </div>

                            <!-- Interactive Pulsing Hotspot Markers on the Image -->
                            <!-- Hotspot 1: Staff Desk (Left) -->
                            <div class="hero-hotspot hotspot-1 active" data-stage="1" style="left: 17%; top: 35%;">
                                <div class="hotspot-pulse-ring"></div>
                                <div class="hotspot-dot"><i class="bi bi-person-workspace"></i></div>
                                <div class="hotspot-popover text-start">
                                    <span class="badge bg-success-subtle text-success text-xs fw-bold mb-1">{{ __('landing.hero.step', ['num' => 1]) }}</span>
                                    <div class="fw-bold text-dark text-sm">{{ __('landing.hero.stage1_title') }}</div>
                                    <div class="text-muted text-xs mt-1">{{ __('landing.hero.stage1_desc') }}</div>
                                </div>
                            </div>

                            <!-- Hotspot 2: QR Scan & Mobile Track (Middle) -->
                            <div class="hero-hotspot hotspot-2" data-stage="2" style="left: 51%; top: 46%;">
                                <div class="hotspot-pulse-ring"></div>
                                <div class="hotspot-dot"><i class="bi bi-phone"></i></div>
                                <div class="hotspot-popover text-start">
                                    <span class="badge bg-info-subtle text-info text-xs fw-bold mb-1">{{ __('landing.hero.step', ['num' => 2]) }}</span>
                                    <div class="fw-bold text-dark text-sm">{{ __('landing.hero.stage2_title') }}</div>
                                    <div class="text-muted text-xs mt-1">{{ __('landing.hero.stage2_desc') }}</div>
                                </div>
                            </div>

                            <!-- Hotspot 3: Service Counter (Right) -->
                            <div class="hero-hotspot hotspot-3" data-stage="3" style="left: 84%; top: 28%;">
                                <div class="hotspot-pulse-ring"></div>
                                <div class="hotspot-dot"><i class="bi bi-check2-all"></i></div>
                                <div class="hotspot-popover text-start">
                                    <span class="badge bg-primary-subtle text-primary text-xs fw-bold mb-1">{{ __('landing.hero.step', ['num' => 3]) }}</span>
                                    <div class="fw-bold text-dark text-sm">{{ __('landing.hero.stage3_title') }}</div>
                                    <div class="text-muted text-xs mt-1">{{ __('landing.hero.stage3_desc') }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Feature Badges / Highlights Bar -->
                        <div class="hero-ecosystem-footer mt-3 pt-3 border-top d-flex flex-wrap align-items-center justify-content-between gap-3 text-start">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success rounded-circle p-2">
                                    <i class="bi bi-check-lg fs-6"></i>
                                </span>
                                <span class="text-sm fw-semibold text-secondary">{{ __('landing.hero.badge_no_hardware') }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-info-subtle text-info rounded-circle p-2">
                                    <i class="bi bi-phone fs-6"></i>
                                </span>
                                <span class="text-sm fw-semibold text-secondary">{{ __('landing.hero.badge_mobile') }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-warning-subtle text-warning rounded-circle p-2">
                                    <i class="bi bi-lightning-charge-fill fs-6"></i>
                                </span>
                                <span class="text-sm fw-semibold text-secondary">{{ __('landing.hero.badge_faster_flow') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hero Background shapes -->
        <div class="hero-bg-shapes">
            <span class="shape shape-1"></span>
            <span class="shape shape-2"></span>
        </div>
    </header>

    </section>

    <!-- 3. Problem / Solution Section -->
    <section class="problem-solution-section py-6 bg-white" id="problem-solution">
        <div class="container">
            <div class="text-center mb-5 pb-3">
                <span class="text-brand fw-bold text-uppercase tracking-wide text-xs d-block mb-2">{{ __('landing.challenge_cure.tag') }}</span>
                <h2 class="section-title fw-bold">{{ __('landing.challenge_cure.title') }}</h2>
                <p class="section-subtitle text-muted mx-auto max-w-600">{{ __('landing.challenge_cure.subtitle') }}</p>
            </div>
            
            <div class="row g-5 align-items-stretch">
                <!-- Left: Problems -->
                <div class="col-lg-6">
                    <div class="card h-100 border-0 card-problem p-4 p-md-5 rounded-4 shadow-sm">
                        <div class="d-flex align-items-center mb-4">
                            <span class="icon-box bg-danger bg-opacity-10 text-danger me-3">
                                <i class="bi bi-x-circle-fill fs-4"></i>
                            </span>
                            <h4 class="mb-0 fw-bold text-danger-emphasis">{{ __('landing.challenge_cure.traditional.title') }}</h4>
                        </div>
                        <ul class="list-unstyled d-flex flex-column gap-4 problem-list mb-0">
                            <li class="d-flex align-items-start gap-3">
                                <i class="bi bi-exclamation-triangle-fill text-danger mt-1"></i>
                                <div>
                                    <h6 class="fw-bold mb-1 text-danger-emphasis">{{ __('landing.challenge_cure.traditional.lines_title') }}</h6>
                                    <p class="text-muted-dark mb-0">{{ __('landing.challenge_cure.traditional.lines_desc') }}</p>
                                </div>
                            </li>
                            <li class="d-flex align-items-start gap-3">
                                <i class="bi bi-exclamation-triangle-fill text-danger mt-1"></i>
                                <div>
                                    <h6 class="fw-bold mb-1 text-danger-emphasis">{{ __('landing.challenge_cure.traditional.frustration_title') }}</h6>
                                    <p class="text-muted-dark mb-0">{{ __('landing.challenge_cure.traditional.frustration_desc') }}</p>
                                </div>
                            </li>
                            <li class="d-flex align-items-start gap-3">
                                <i class="bi bi-exclamation-triangle-fill text-danger mt-1"></i>
                                <div>
                                    <h6 class="fw-bold mb-1 text-danger-emphasis">{{ __('landing.challenge_cure.traditional.missed_title') }}</h6>
                                    <p class="text-muted-dark mb-0">{{ __('landing.challenge_cure.traditional.missed_desc') }}</p>
                                </div>
                            </li>
                            <li class="d-flex align-items-start gap-3">
                                <i class="bi bi-exclamation-triangle-fill text-danger mt-1"></i>
                                <div>
                                    <h6 class="fw-bold mb-1 text-danger-emphasis">{{ __('landing.challenge_cure.traditional.manual_title') }}</h6>
                                    <p class="text-muted-dark mb-0">{{ __('landing.challenge_cure.traditional.manual_desc') }}</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Right: Solutions -->
                <div class="col-lg-6">
                    <div class="card h-100 border-0 card-solution p-4 p-md-5 rounded-4 shadow-sm">
                        <div class="d-flex align-items-center mb-4">
                            <span class="icon-box bg-success bg-opacity-10 text-success me-3">
                                <i class="bi bi-check-circle-fill fs-4"></i>
                            </span>
                            <h4 class="mb-0 fw-bold text-success-emphasis">{{ __('landing.challenge_cure.solution.title') }}</h4>
                        </div>
                        <ul class="list-unstyled d-flex flex-column gap-4 solution-list mb-0">
                            <li class="d-flex align-items-start gap-3">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <h6 class="fw-bold mb-1 text-success-emphasis">{{ __('landing.challenge_cure.solution.queue_title') }}</h6>
                                    <p class="text-muted-dark mb-0">{{ __('landing.challenge_cure.solution.queue_desc') }}</p>
                                </div>
                            </li>
                            <li class="d-flex align-items-start gap-3">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <h6 class="fw-bold mb-1 text-success-emphasis">{{ __('landing.challenge_cure.solution.scheduling_title') }}</h6>
                                    <p class="text-muted-dark mb-0">{{ __('landing.challenge_cure.solution.scheduling_desc') }}</p>
                                </div>
                            </li>
                            <li class="d-flex align-items-start gap-3">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <h6 class="fw-bold mb-1 text-success-emphasis">{{ __('landing.challenge_cure.solution.tracking_title') }}</h6>
                                    <p class="text-muted-dark mb-0">{{ __('landing.challenge_cure.solution.tracking_desc') }}</p>
                                </div>
                            </li>
                            <li class="d-flex align-items-start gap-3">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <h6 class="fw-bold mb-1 text-success-emphasis">{{ __('landing.challenge_cure.solution.notifications_title') }}</h6>
                                    <p class="text-muted-dark mb-0">{{ __('landing.challenge_cure.solution.notifications_desc') }}</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. Features Section -->
    <section class="features-section py-6 bg-light" id="features">
        <div class="container">
            <div class="text-center mb-5 pb-3">
                <span class="text-brand fw-bold text-uppercase tracking-wide text-xs d-block mb-2">{{ __('landing.features.tag') }}</span>
                <h2 class="section-title fw-bold">{{ __('landing.features.title') }}</h2>
                <p class="section-subtitle text-muted mx-auto max-w-600">{{ __('landing.features.subtitle') }}</p>
            </div>
            
            <div class="row g-4 bento-grid">
                <!-- 1. Walk-In Queue Management -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 rounded-4 p-4 shadow-sm feature-card bg-white">
                        <div class="feature-icon mb-3">
                            <i class="bi bi-ticket-detailed-fill"></i>
                        </div>
                        <h5 class="fw-bold mb-2">{{ __('landing.features.items.walkin.title') }}</h5>
                        <p class="text-muted text-sm mb-0">{{ __('landing.features.items.walkin.desc') }}</p>
                    </div>
                </div>
                
                <!-- 2. Appointment Scheduling -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 rounded-4 p-4 shadow-sm feature-card bg-white">
                        <div class="feature-icon mb-3">
                            <i class="bi bi-calendar2-check-fill"></i>
                        </div>
                        <h5 class="fw-bold mb-2">{{ __('landing.features.items.appointment.title') }}</h5>
                        <p class="text-muted text-sm mb-0">{{ __('landing.features.items.appointment.desc') }}</p>
                    </div>
                </div>
                
                <!-- 3. Real-Time Queue Tracking -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 rounded-4 p-4 shadow-sm feature-card bg-white">
                        <div class="feature-icon mb-3">
                            <i class="bi bi-phone-vibrate"></i>
                        </div>
                        <h5 class="fw-bold mb-2">{{ __('landing.features.items.tracking.title') }}</h5>
                        <p class="text-muted text-sm mb-0">{{ __('landing.features.items.tracking.desc') }}</p>
                    </div>
                </div>
                
                <!-- 4. Customer Database -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 rounded-4 p-4 shadow-sm feature-card bg-white">
                        <div class="feature-icon mb-3">
                            <i class="bi bi-person-lines-fill"></i>
                        </div>
                        <h5 class="fw-bold mb-2">{{ __('landing.features.items.crm.title') }}</h5>
                        <p class="text-muted text-sm mb-0">{{ __('landing.features.items.crm.desc') }}</p>
                    </div>
                </div>
                
                <!-- 5. Multi-Branch Support -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 rounded-4 p-4 shadow-sm feature-card bg-white">
                        <div class="feature-icon mb-3">
                            <i class="bi bi-diagram-3-fill"></i>
                        </div>
                        <h5 class="fw-bold mb-2">{{ __('landing.features.items.multibranch.title') }}</h5>
                        <p class="text-muted text-sm mb-0">{{ __('landing.features.items.multibranch.desc') }}</p>
                    </div>
                </div>
                
                <!-- 6. Statistics & Analytics -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 rounded-4 p-4 shadow-sm feature-card bg-white">
                        <div class="feature-icon mb-3">
                            <i class="bi bi-bar-chart-line-fill"></i>
                        </div>
                        <h5 class="fw-bold mb-2">{{ __('landing.features.items.stats.title') }}</h5>
                        <p class="text-muted text-sm mb-0">{{ __('landing.features.items.stats.desc') }}</p>
                    </div>
                </div>
                
                <!-- 7. User Roles & Permissions -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 rounded-4 p-4 shadow-sm feature-card bg-white">
                        <div class="feature-icon mb-3">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <h5 class="fw-bold mb-2">{{ __('landing.features.items.rbac.title') }}</h5>
                        <p class="text-muted text-sm mb-0">{{ __('landing.features.items.rbac.desc') }}</p>
                    </div>
                </div>
                
                <!-- 8. Multi-Language Support -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 rounded-4 p-4 shadow-sm feature-card bg-white">
                        <div class="feature-icon mb-3">
                            <i class="bi bi-translate"></i>
                        </div>
                        <h5 class="fw-bold mb-2">{{ __('landing.features.items.lang.title') }}</h5>
                        <p class="text-muted text-sm mb-0">{{ __('landing.features.items.lang.desc') }}</p>
                    </div>
                </div>
                
                <!-- 9. And Much More -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 rounded-4 p-4 shadow-sm feature-card bg-white">
                        <div class="feature-icon mb-3">
                            <i class="fa-solid fa-ellipsis"></i>
                        </div>
                        <h5 class="fw-bold mb-2">{{ __('landing.features.items.more.title') }}</h5>
                        <p class="text-muted text-sm mb-0">{{ __('landing.features.items.more.desc') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. How It Works -->
    <section class="how-it-works py-6" id="how-it-works">
        <div class="container">
            <div class="text-center mb-5 pb-2">
                <span class="badge badge-brand mb-3 py-2 px-3 rounded-pill text-uppercase tracking-wider">
                    <i class="bi bi-diagram-3-fill me-1"></i>{{ __('landing.how_it_works.tag') }}
                </span>
                <h2 class="section-title fw-extrabold display-5 mb-3">{{ __('landing.how_it_works.title') }}</h2>
                <p class="section-subtitle text-muted mx-auto max-w-700 fs-5">{{ __('landing.how_it_works.subtitle') }}</p>
            </div>
            
            <div class="position-relative process-grid mb-5">
                <!-- Desktop Connector Arrows -->
                <div class="step-connector-col step-connector-1">
                    <div class="connector-circle">
                        <i class="bi bi-arrow-right"></i>
                    </div>
                </div>
                <div class="step-connector-col step-connector-2">
                    <div class="connector-circle">
                        <i class="bi bi-arrow-right"></i>
                    </div>
                </div>

                <div class="row g-4 justify-content-center">
                    <!-- Step 1 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="process-card">
                            <div class="process-card-header">
                                <span class="step-num-pill">01</span>
                                <span class="badge rounded-pill step-role-tag staff-tag">
                                    <i class="bi bi-person-workspace me-1"></i>{{ __('landing.how_it_works.steps.step1_role') }}
                                </span>
                            </div>

                            <div class="step-illustration-wrapper">
                                <div class="step-illustration-bg"></div>
                                <img src="{{ asset('images/landing/step-1-queue.png') }}" alt="{{ __('landing.how_it_works.steps.step1_title') }}" class="step-illustration img-fluid">
                            </div>

                            <h4 class="step-title fw-bold mb-2">{{ __('landing.how_it_works.steps.step1_title') }}</h4>
                            <p class="step-desc text-muted mb-3">{{ __('landing.how_it_works.steps.step1_desc') }}</p>

                            <ul class="step-checklist list-unstyled mb-0 text-start">
                                <li class="d-flex align-items-start gap-2 mb-2">
                                    <i class="bi bi-check-circle-fill fs-6 mt-0.5"></i>
                                    <span>{{ __('landing.how_it_works.steps.step1_point1') }}</span>
                                </li>
                                <li class="d-flex align-items-start gap-2 mb-2">
                                    <i class="bi bi-check-circle-fill fs-6 mt-0.5"></i>
                                    <span>{{ __('landing.how_it_works.steps.step1_point2') }}</span>
                                </li>
                                <li class="d-flex align-items-start gap-2">
                                    <i class="bi bi-check-circle-fill fs-6 mt-0.5"></i>
                                    <span>{{ __('landing.how_it_works.steps.step1_point3') }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Step 2 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="process-card">
                            <div class="process-card-header">
                                <span class="step-num-pill">02</span>
                                <span class="badge rounded-pill step-role-tag customer-tag">
                                    <i class="bi bi-phone me-1"></i>{{ __('landing.how_it_works.steps.step2_role') }}
                                </span>
                            </div>

                            <div class="step-illustration-wrapper">
                                <div class="step-illustration-bg"></div>
                                <img src="{{ asset('images/landing/step-2-scan.png') }}" alt="{{ __('landing.how_it_works.steps.step2_title') }}" class="step-illustration img-fluid">
                            </div>

                            <h4 class="step-title fw-bold mb-2">{{ __('landing.how_it_works.steps.step2_title') }}</h4>
                            <p class="step-desc text-muted mb-3">{{ __('landing.how_it_works.steps.step2_desc') }}</p>

                            <ul class="step-checklist list-unstyled mb-0 text-start">
                                <li class="d-flex align-items-start gap-2 mb-2">
                                    <i class="bi bi-check-circle-fill fs-6 mt-0.5"></i>
                                    <span>{{ __('landing.how_it_works.steps.step2_point1') }}</span>
                                </li>
                                <li class="d-flex align-items-start gap-2 mb-2">
                                    <i class="bi bi-check-circle-fill fs-6 mt-0.5"></i>
                                    <span>{{ __('landing.how_it_works.steps.step2_point2') }}</span>
                                </li>
                                <li class="d-flex align-items-start gap-2">
                                    <i class="bi bi-check-circle-fill fs-6 mt-0.5"></i>
                                    <span>{{ __('landing.how_it_works.steps.step2_point3') }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Step 3 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="process-card">
                            <div class="process-card-header">
                                <span class="step-num-pill">03</span>
                                <span class="badge rounded-pill step-role-tag result-tag">
                                    <i class="bi bi-check2-all me-1"></i>{{ __('landing.how_it_works.steps.step3_role') }}
                                </span>
                            </div>

                            <div class="step-illustration-wrapper">
                                <div class="step-illustration-bg"></div>
                                <img src="{{ asset('images/landing/step-3-serve.png') }}" alt="{{ __('landing.how_it_works.steps.step3_title') }}" class="step-illustration img-fluid">
                            </div>

                            <h4 class="step-title fw-bold mb-2">{{ __('landing.how_it_works.steps.step3_title') }}</h4>
                            <p class="step-desc text-muted mb-3">{{ __('landing.how_it_works.steps.step3_desc') }}</p>

                            <ul class="step-checklist list-unstyled mb-0 text-start">
                                <li class="d-flex align-items-start gap-2 mb-2">
                                    <i class="bi bi-check-circle-fill fs-6 mt-0.5"></i>
                                    <span>{{ __('landing.how_it_works.steps.step3_point1') }}</span>
                                </li>
                                <li class="d-flex align-items-start gap-2 mb-2">
                                    <i class="bi bi-check-circle-fill fs-6 mt-0.5"></i>
                                    <span>{{ __('landing.how_it_works.steps.step3_point2') }}</span>
                                </li>
                                <li class="d-flex align-items-start gap-2">
                                    <i class="bi bi-check-circle-fill fs-6 mt-0.5"></i>
                                    <span>{{ __('landing.how_it_works.steps.step3_point3') }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Double-sided value comparison banner -->
            <div class="process-summary-banner mt-4">
                <div class="row g-4 align-items-center">
                    <div class="col-md-6">
                        <div class="summary-block">
                            <div class="summary-icon-box customer-icon">
                                <i class="bi bi-phone-vibrate"></i>
                            </div>
                            <div>
                                <span class="badge bg-success-subtle text-success fw-bold text-xs uppercase mb-1 d-inline-block">{{ __('landing.how_it_works.summary.customer_label') }}</span>
                                <h5 class="fw-bold mb-1 text-dark">{{ __('landing.how_it_works.summary.customer_title') }}</h5>
                                <p class="text-muted text-sm mb-0">{{ __('landing.how_it_works.summary.customer_desc') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 summary-divider">
                        <div class="summary-block">
                            <div class="summary-icon-box staff-icon">
                                <i class="bi bi-speedometer2"></i>
                            </div>
                            <div>
                                <span class="badge bg-info-subtle text-info fw-bold text-xs uppercase mb-1 d-inline-block">{{ __('landing.how_it_works.summary.staff_label') }}</span>
                                <h5 class="fw-bold mb-1 text-dark">{{ __('landing.how_it_works.summary.staff_title') }}</h5>
                                <p class="text-muted text-sm mb-0">{{ __('landing.how_it_works.summary.staff_desc') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. Screenshots Section -->
    <section class="screenshots-section py-6 bg-light" id="screenshots">
        <div class="container">
            <div class="text-center mb-5 pb-3">
                <span class="text-brand fw-bold text-uppercase tracking-wide text-xs d-block mb-2">{{ __('landing.walkthrough.tag') }}</span>
                <h2 class="section-title fw-bold">{{ __('landing.walkthrough.title') }}</h2>
                <p class="section-subtitle text-muted mx-auto max-w-600">{{ __('landing.walkthrough.subtitle') }}</p>
            </div>
            
            <div class="screenshot-tabs">
                <ul class="nav nav-pills justify-content-center gap-2 mb-5 pills-custom" id="screenshotTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill px-4 py-2 fw-semibold d-flex align-items-center gap-2" id="queue-tab" data-bs-toggle="pill" data-bs-target="#queue-pane" type="button" role="tab">
                            <i class="bi bi-ticket-perforated-fill"></i> {{ __('landing.walkthrough.tabs.queue') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-4 py-2 fw-semibold d-flex align-items-center gap-2" id="calendar-tab" data-bs-toggle="pill" data-bs-target="#calendar-pane" type="button" role="tab">
                            <i class="bi bi-calendar-event-fill"></i> {{ __('landing.walkthrough.tabs.calendar') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-4 py-2 fw-semibold d-flex align-items-center gap-2" id="customer-tab" data-bs-toggle="pill" data-bs-target="#customer-pane" type="button" role="tab">
                            <i class="bi bi-person-fill-gear"></i> {{ __('landing.walkthrough.tabs.customer') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-4 py-2 fw-semibold d-flex align-items-center gap-2" id="stats-tab" data-bs-toggle="pill" data-bs-target="#stats-pane" type="button" role="tab">
                            <i class="bi bi-bar-chart-fill"></i> {{ __('landing.walkthrough.tabs.stats') }}
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="screenshotTabsContent">
                    <!-- Tab 1: Queue Dashboard -->
                    <div class="tab-pane fade show active" id="queue-pane" role="tabpanel">
                        <div class="row align-items-center g-5">
                            <div class="col-lg-6">
                                <h4 class="fw-bold mb-3">{{ __('landing.walkthrough.queue.title') }}</h4>
                                <p class="text-muted mb-4">{{ __('landing.walkthrough.queue.desc') }}</p>
                                <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
                                    <li><i class="bi bi-check-circle-fill text-brand me-2"></i> {{ __('landing.walkthrough.queue.li1') }}</li>
                                    <li><i class="bi bi-check-circle-fill text-brand me-2"></i> {{ __('landing.walkthrough.queue.li2') }}</li>
                                    <li><i class="bi bi-check-circle-fill text-brand me-2"></i> {{ __('landing.walkthrough.queue.li3') }}</li>
                                </ul>
                            </div>
                            <div class="col-lg-6">
                                <div class="screenshot-box shadow rounded-4 p-2 bg-white">
                                    <div class="browser-bar d-flex gap-1.5 mb-2 px-2">
                                        <span class="browser-dot bg-danger"></span>
                                        <span class="browser-dot bg-warning"></span>
                                        <span class="browser-dot bg-success"></span>
                                    </div>
                                    <div class="p-3 bg-light rounded text-center py-5">
                                        <i class="bi bi-ticket-perforated-fill text-brand display-2 mb-3"></i>
                                        <h5 class="fw-bold">{{ __('landing.walkthrough.queue.box_title') }}</h5>
                                        <p class="text-muted max-w-400 mx-auto text-sm">{{ __('landing.walkthrough.queue.box_desc') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tab 2: Appointment Calendar -->
                    <div class="tab-pane fade" id="calendar-pane" role="tabpanel">
                        <div class="row align-items-center g-5">
                            <div class="col-lg-6">
                                <h4 class="fw-bold mb-3">{{ __('landing.walkthrough.calendar.title') }}</h4>
                                <p class="text-muted mb-4">{{ __('landing.walkthrough.calendar.desc') }}</p>
                                <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
                                    <li><i class="bi bi-check-circle-fill text-brand me-2"></i> {{ __('landing.walkthrough.calendar.li1') }}</li>
                                    <li><i class="bi bi-check-circle-fill text-brand me-2"></i> {{ __('landing.walkthrough.calendar.li2') }}</li>
                                    <li><i class="bi bi-check-circle-fill text-brand me-2"></i> {{ __('landing.walkthrough.calendar.li3') }}</li>
                                </ul>
                            </div>
                            <div class="col-lg-6">
                                <div class="screenshot-box shadow rounded-4 p-2 bg-white">
                                    <div class="browser-bar d-flex gap-1.5 mb-2 px-2">
                                        <span class="browser-dot bg-danger"></span>
                                        <span class="browser-dot bg-warning"></span>
                                        <span class="browser-dot bg-success"></span>
                                    </div>
                                    <div class="p-3 bg-light rounded text-center py-5">
                                        <i class="bi bi-calendar2-week-fill text-brand display-2 mb-3"></i>
                                        <h5 class="fw-bold">{{ __('landing.walkthrough.calendar.box_title') }}</h5>
                                        <p class="text-muted max-w-400 mx-auto text-sm">{{ __('landing.walkthrough.calendar.box_desc') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tab 3: Customer Management -->
                    <div class="tab-pane fade" id="customer-pane" role="tabpanel">
                        <div class="row align-items-center g-5">
                            <div class="col-lg-6">
                                <h4 class="fw-bold mb-3">{{ __('landing.walkthrough.customer.title') }}</h4>
                                <p class="text-muted mb-4">{{ __('landing.walkthrough.customer.desc') }}</p>
                                <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
                                    <li><i class="bi bi-check-circle-fill text-brand me-2"></i> {{ __('landing.walkthrough.customer.li1') }}</li>
                                    <li><i class="bi bi-check-circle-fill text-brand me-2"></i> {{ __('landing.walkthrough.customer.li2') }}</li>
                                    <li><i class="bi bi-check-circle-fill text-brand me-2"></i> {{ __('landing.walkthrough.customer.li3') }}</li>
                                </ul>
                            </div>
                            <div class="col-lg-6">
                                <div class="screenshot-box shadow rounded-4 p-2 bg-white">
                                    <div class="browser-bar d-flex gap-1.5 mb-2 px-2">
                                        <span class="browser-dot bg-danger"></span>
                                        <span class="browser-dot bg-warning"></span>
                                        <span class="browser-dot bg-success"></span>
                                    </div>
                                    <div class="p-3 bg-light rounded text-center py-5">
                                        <i class="bi bi-person-video2 text-brand display-2 mb-3"></i>
                                        <h5 class="fw-bold">{{ __('landing.walkthrough.customer.box_title') }}</h5>
                                        <p class="text-muted max-w-400 mx-auto text-sm">{{ __('landing.walkthrough.customer.box_desc') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tab 4: Statistics Page -->
                    <div class="tab-pane fade" id="stats-pane" role="tabpanel">
                        <div class="row align-items-center g-5">
                            <div class="col-lg-6">
                                <h4 class="fw-bold mb-3">{{ __('landing.walkthrough.stats.title') }}</h4>
                                <p class="text-muted mb-4">{{ __('landing.walkthrough.stats.desc') }}</p>
                                <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
                                    <li><i class="bi bi-check-circle-fill text-brand me-2"></i> {{ __('landing.walkthrough.stats.li1') }}</li>
                                    <li><i class="bi bi-check-circle-fill text-brand me-2"></i> {{ __('landing.walkthrough.stats.li2') }}</li>
                                    <li><i class="bi bi-check-circle-fill text-brand me-2"></i> {{ __('landing.walkthrough.stats.li3') }}</li>
                                </ul>
                            </div>
                            <div class="col-lg-6">
                                <div class="screenshot-box shadow rounded-4 p-2 bg-white">
                                    <div class="browser-bar d-flex gap-1.5 mb-2 px-2">
                                        <span class="browser-dot bg-danger"></span>
                                        <span class="browser-dot bg-warning"></span>
                                        <span class="browser-dot bg-success"></span>
                                    </div>
                                    <div class="p-3 bg-light rounded text-center py-5">
                                        <i class="bi bi-graph-up-arrow text-brand display-2 mb-3"></i>
                                        <h5 class="fw-bold">{{ __('landing.walkthrough.stats.box_title') }}</h5>
                                        <p class="text-muted max-w-400 mx-auto text-sm">{{ __('landing.walkthrough.stats.box_desc') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- 9. Pricing Section -->
    @php
        $currentPlan = null;
        if (auth()->check() && auth()->user()->company && auth()->user()->company->subscription) {
            $currentPlan = auth()->user()->company->subscription->plan ?? null;
        }

        $plans = [
            [
                'id' => 'starter',
                'icon' => 'bi-send-fill',
                'featured' => false,
                'monthly' => __('pricing.plans.starter.price_monthly'),
                'yearly' => __('pricing.plans.starter.price_yearly'),
            ],
            [
                'id' => 'professional',
                'icon' => 'bi-rocket-takeoff-fill',
                'featured' => true,
                'monthly' => __('pricing.plans.professional.price_monthly'),
                'yearly' => __('pricing.plans.professional.price_yearly'),
            ],
            [
                'id' => 'business',
                'icon' => 'bi-building-fill',
                'featured' => false,
                'monthly' => __('pricing.plans.business.price_monthly'),
                'yearly' => __('pricing.plans.business.price_yearly'),
            ],
            [
                'id' => 'premium',
                'icon' => 'bi-award-fill',
                'featured' => false,
                'monthly' => __('pricing.plans.premium.price_monthly'),
                'yearly' => __('pricing.plans.premium.price_yearly'),
            ],
        ];
    @endphp
    <section class="pricing-section py-6 bg-light" id="pricing">
        <div class="container">
            <div class="text-center mb-5 pb-3">
                <span class="text-brand fw-bold text-uppercase tracking-wide text-xs d-block mb-2">{{ __('landing.footer.links.pricing') }}</span>
                <h2 class="section-title fw-bold">{{ __('pricing.title') }}</h2>
                <p class="section-subtitle text-muted mx-auto max-w-600">{{ __('pricing.subtitle') }}</p>
                
                <!-- Billing Period Toggle -->
                <div class="d-inline-flex align-items-center gap-3 bg-white p-2 rounded-pill shadow-sm border mt-3">
                    <span class="fw-semibold text-sm px-3 py-1 cursor-pointer toggle-label active" data-billing="monthly">{{ __('pricing.monthly') }}</span>
                    <div class="form-check form-switch p-0 m-0 d-flex align-items-center toggle-switch-box">
                        <input class="form-check-input ms-0 cursor-pointer" type="checkbox" id="billingPeriodToggle" style="width: 50px; height: 26px;">
                    </div>
                    <span class="fw-semibold text-sm px-3 py-1 cursor-pointer toggle-label" data-billing="yearly">
                        {{ __('pricing.yearly') }} <span class="badge bg-success-subtle text-success rounded-pill text-xs ms-1">{{ __('pricing.save_badge') }}</span>
                    </span>
                </div>
            </div>
            
            <div class="row g-4 align-items-stretch justify-content-center">
                @foreach($plans as $plan)
                    @php
                        $planId = $plan['id'];
                        $isCurrentPlan = $currentPlan === $planId;
                        $isFeatured = $plan['featured'];
                    @endphp
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 rounded-4 p-4 shadow-sm pricing-card bg-white position-relative {{ $isFeatured ? 'border-brand border-2' : 'border-0' }} {{ $isCurrentPlan ? 'plan-card--current' : '' }}">
                            
                            @if($isFeatured)
                                <div class="popular-tag text-uppercase fw-bold text-white bg-brand py-1 px-3 text-center" style="font-size:0.65rem;">
                                    {{ __('pricing.most_popular') }}
                                </div>
                            @endif
 
                            @if($isCurrentPlan)
                                <div class="current-badge-custom text-uppercase fw-bold text-white bg-dark py-1 px-3 text-center" style="position: absolute; top: -14px; left: 50%; transform: translateX(-50%); border-radius: 99px; font-size: 0.65rem;">
                                    {{ __('pricing.current_plan') }}
                                </div>
                            @endif
 
                            <div class="plan-icon-wrapper mb-3">
                                <i class="bi {{ $plan['icon'] }}"></i>
                            </div>

                            <div class="mb-3">
                                <h4 class="fw-bold mb-1 plan-title">{{ __("pricing.plans.{$planId}.name") }}</h4>
                                <p class="text-muted text-xs mb-0">{{ __("pricing.plans.{$planId}.description") }}</p>
                            </div>

                            <div class="mb-4 d-flex align-items-baseline">
                                <span class="fs-2 fw-bold text-dark font-brand price-amount" data-monthly="{{ $plan['monthly'] }}" data-yearly="{{ $plan['yearly'] }}">{{ $plan['monthly'] }}</span>
                                <span class="text-muted ms-1 text-sm">{{ __('pricing.currency') }} {{ __('pricing.per_month') }}</span>
                            </div>
                            
                            <hr class="opacity-10 my-3">
                            
                            <div class="plan-limits-simple mb-4 small text-dark">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-people-fill text-brand"></i>
                                    <span><strong>{{ __("pricing.plans.{$planId}.staff") }}</strong> {{ __('pricing.staff') }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-door-open-fill text-brand"></i>
                                    <span><strong>{{ __("pricing.plans.{$planId}.rooms") }}</strong> {{ __('pricing.rooms') }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-ticket-perforated-fill text-brand"></i>
                                    <span><strong>{{ __("pricing.plans.{$planId}.tickets") }}</strong> {{ __('pricing.tickets') }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-person-fill-check text-brand"></i>
                                    <span><strong>{{ __("pricing.plans.{$planId}.customers") }}</strong> {{ __('pricing.customers') }}</span>
                                </div>
                            </div>
                            
                            <hr class="opacity-10 my-3">
                            
                            <ul class="list-unstyled d-flex flex-column gap-2 mb-4 plan-features small">
                                @foreach(__("pricing.plans.{$planId}.includes") as $feature)
                                    <li class="d-flex align-items-start gap-2">
                                        <i class="bi bi-check-circle-fill text-brand mt-0.5" style="font-size:0.85rem;"></i>
                                        <span class="text-muted">{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            
                            <div class="mt-auto">
                                @if($isCurrentPlan)
                                    <button class="btn btn-secondary w-100 rounded-pill py-2.5 fw-bold" disabled>
                                        {{ __('pricing.current_plan') }}
                                    </button>
                                @else
                                    <a href="{{ auth()->check() ? route('billing.index', ['plan' => $planId]) : route('register', ['plan' => $planId]) }}" 
                                       class="btn {{ $isFeatured ? 'btn-primary text-white' : 'btn-outline-primary' }} w-100 rounded-pill py-2.5 fw-bold">
                                        {{ __("pricing.plans.{$planId}.cta") }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="text-center mt-5 text-muted small">
                <i class="bi bi-shield-lock-fill text-brand me-1"></i> {{ __('pricing.no_hidden_fees') }}
            </div>
        </div>
    </section>

    <!-- 10. FAQ Section -->
    <section class="faq-section py-6 bg-white" id="faq">
        <div class="container">
            <div class="text-center mb-5 pb-3">
                <span class="text-brand fw-bold text-uppercase tracking-wide text-xs d-block mb-2">{{ __('landing.faq.tag') }}</span>
                <h2 class="section-title fw-bold">{{ __('landing.faq.title') }}</h2>
                <p class="section-subtitle text-muted mx-auto max-w-600">{{ __('landing.faq.subtitle') }}</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-9 col-xl-8">
                    <div class="accordion accordion-custom" id="faqAccordion">
                        <!-- Q1 -->
                        <div class="accordion-item border-0 mb-3 shadow-sm rounded-4 overflow-hidden">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button fw-bold py-3 px-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    {{ __('landing.faq.items.q1') }}
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                <div class="accordion-body px-4 pb-4 pt-0 text-muted text-sm">
                                    {{ __('landing.faq.items.a1') }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Q2 -->
                        <div class="accordion-item border-0 mb-3 shadow-sm rounded-4 overflow-hidden">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed fw-bold py-3 px-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    {{ __('landing.faq.items.q2') }}
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                <div class="accordion-body px-4 pb-4 pt-0 text-muted text-sm">
                                    {{ __('landing.faq.items.a2') }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Q3 -->
                        <div class="accordion-item border-0 mb-3 shadow-sm rounded-4 overflow-hidden">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed fw-bold py-3 px-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    {{ __('landing.faq.items.q3') }}
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                                <div class="accordion-body px-4 pb-4 pt-0 text-muted text-sm">
                                    {{ __('landing.faq.items.a3') }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Q4 -->
                        <div class="accordion-item border-0 mb-3 shadow-sm rounded-4 overflow-hidden">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button collapsed fw-bold py-3 px-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    {{ __('landing.faq.items.q4') }}
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                                <div class="accordion-body px-4 pb-4 pt-0 text-muted text-sm">
                                    {{ __('landing.faq.items.a4') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- 12. Footer -->
    <footer class="footer-section py-5 text-white">
        <div class="container py-4">
            <div class="row g-4 justify-content-between mb-5">
                <div class="col-lg-4">
                    <img src="{{ asset('images/ng_logo.png') }}" alt="Noubtigo Logo" height="42" class="mb-3 brightness-invert">
                    <p class="text-footer-muted text-sm max-w-300">{{ __('landing.footer.desc') }}</p>
                    <div class="d-flex gap-3 mt-4 social-links">
                        <a href="#" class="text-footer-muted hover-brand"><i class="bi bi-twitter-x fs-5"></i></a>
                        <a href="#" class="text-footer-muted hover-brand"><i class="bi bi-linkedin fs-5"></i></a>
                        <a href="#" class="text-footer-muted hover-brand"><i class="bi bi-facebook fs-5"></i></a>
                        <a href="#" class="text-footer-muted hover-brand"><i class="bi bi-instagram fs-5"></i></a>
                    </div>
                </div>
                
                <div class="col-6 col-md-3 col-lg-2">
                    <h6 class="fw-bold text-uppercase tracking-wider text-xs mb-3 text-white">{{ __('landing.footer.col_product') }}</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 footer-links text-sm">
                        <li><a href="#features" class="text-footer-muted text-decoration-none hover-white">{{ __('landing.footer.links.features') }}</a></li>
                        <li><a href="#pricing" class="text-footer-muted text-decoration-none hover-white">{{ __('landing.footer.links.pricing') }}</a></li>
                        <li><a href="#use-cases" class="text-footer-muted text-decoration-none hover-white">{{ __('landing.footer.links.use_cases') }}</a></li>
                    </ul>
                </div>
                
                <div class="col-6 col-md-3 col-lg-2">
                    <h6 class="fw-bold text-uppercase tracking-wider text-xs mb-3 text-white">{{ __('landing.footer.col_resources') }}</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 footer-links text-sm">
                        <li><a href="#" class="text-footer-muted text-decoration-none hover-white">{{ __('landing.footer.links.blog') }}</a></li>
                        <li><a href="#" class="text-footer-muted text-decoration-none hover-white">{{ __('landing.footer.links.docs') }}</a></li>
                        <li><a href="#" class="text-footer-muted text-decoration-none hover-white">{{ __('landing.footer.links.status') }}</a></li>
                    </ul>
                </div>
                
                <div class="col-6 col-md-3 col-lg-2">
                    <h6 class="fw-bold text-uppercase tracking-wider text-xs mb-3 text-white">{{ __('landing.footer.col_support') }}</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 footer-links text-sm">
                        <li><a href="#" class="text-footer-muted text-decoration-none hover-white">{{ __('landing.footer.links.contact') }}</a></li>
                        <li><a href="#" class="text-footer-muted text-decoration-none hover-white">{{ __('landing.footer.links.privacy') }}</a></li>
                        <li><a href="#" class="text-footer-muted text-decoration-none hover-white">{{ __('landing.footer.links.terms') }}</a></li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4" style="border-color: rgba(148, 163, 184, 0.2);">
            
            <div class="row align-items-center g-3 text-center text-md-start">
                <div class="col-md-6">
                    <p class="text-footer-muted text-sm mb-0">&copy; {{ date('Y') }} NoubtiGO. {{ __('landing.footer.all_rights_reserved') }}</p>
                </div>
                <div class="col-md-6 text-md-end text-footer-muted text-sm">
                    <span class="me-3 cursor-pointer lang-switcher-bottom" data-locale="en">EN</span> | 
                    <span class="mx-3 cursor-pointer lang-switcher-bottom" data-locale="fr">FR</span> | 
                    <span class="ms-3 cursor-pointer lang-switcher-bottom" data-locale="ar">AR</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle with Popper JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Scripts -->
    <script>
        // Navbar Scrolled Effect
        window.addEventListener('scroll', function() {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 40) {
                nav.classList.add('navbar-scrolled');
            } else {
                nav.classList.remove('navbar-scrolled');
            }
        });

        // Pricing Monthly / Yearly Switch
        const billingToggle = document.getElementById('billingPeriodToggle');
        const priceEls = document.querySelectorAll('.price-amount');
        const labels = document.querySelectorAll('.toggle-label');

        function updatePrices(isYearly) {
            priceEls.forEach(el => {
                el.classList.add('price-updating');
                setTimeout(() => {
                    const price = isYearly ? el.getAttribute('data-yearly') : el.getAttribute('data-monthly');
                    el.textContent = price;
                    el.classList.remove('price-updating');
                }, 150);
            });
            
            labels.forEach(label => {
                const targetType = isYearly ? 'yearly' : 'monthly';
                if (label.getAttribute('data-billing') === targetType) {
                    label.classList.add('active');
                } else {
                    label.classList.remove('active');
                }
            });
        }

        if (billingToggle) {
            billingToggle.addEventListener('change', function() {
                updatePrices(this.checked);
            });
        }

        labels.forEach(label => {
            label.addEventListener('click', function() {
                const targetPeriod = this.getAttribute('data-billing');
                const isYearly = targetPeriod === 'yearly';
                if (billingToggle) {
                    billingToggle.checked = isYearly;
                    updatePrices(isYearly);
                }
            });
        });

        // Bottom Language Switcher
        document.querySelectorAll('.lang-switcher-bottom').forEach(span => {
            span.addEventListener('click', function() {
                const locale = this.getAttribute('data-locale');
                const select = document.querySelector('.select-lang');
                if (select) {
                    select.value = locale;
                    select.dispatchEvent(new Event('change'));
                }
            });
        });

        // Hero Ecosystem Interactive Showcase
        const heroStages = [
            {
                id: 1,
                title: @json(__('landing.hero.stage1_title')),
                desc: @json(__('landing.hero.stage1_desc')),
                icon: 'bi-person-workspace',
                iconBg: 'bg-brand'
            },
            {
                id: 2,
                title: @json(__('landing.hero.stage2_title')),
                desc: @json(__('landing.hero.stage2_desc')),
                icon: 'bi-phone',
                iconBg: 'bg-info'
            },
            {
                id: 3,
                title: @json(__('landing.hero.stage3_title')),
                desc: @json(__('landing.hero.stage3_desc')),
                icon: 'bi-check2-all',
                iconBg: 'bg-primary'
            }
        ];

        const stageTabs = document.querySelectorAll('.hero-stage-tab');
        const hotspots = document.querySelectorAll('.hero-hotspot');
        const calloutTitle = document.getElementById('calloutTitle');
        const calloutDesc = document.getElementById('calloutDesc');
        const calloutIcon = document.getElementById('calloutIcon');
        const calloutCircle = document.querySelector('.callout-icon-circle');

        let currentHeroStage = 1;
        let heroAutoTimer = null;

        function setHeroStage(stageNum, pauseAuto = false) {
            currentHeroStage = parseInt(stageNum);
            const data = heroStages.find(s => s.id === currentHeroStage) || heroStages[0];

            stageTabs.forEach(tab => {
                tab.classList.toggle('active', parseInt(tab.getAttribute('data-stage')) === currentHeroStage);
            });

            hotspots.forEach(spot => {
                spot.classList.toggle('active', parseInt(spot.getAttribute('data-stage')) === currentHeroStage);
            });

            if (calloutTitle && calloutDesc && calloutIcon) {
                calloutTitle.textContent = data.title;
                calloutDesc.textContent = data.desc;
                calloutIcon.className = `bi ${data.icon}`;
                if (calloutCircle) {
                    calloutCircle.className = `callout-icon-circle ${data.iconBg} text-white`;
                }
            }

            if (pauseAuto && heroAutoTimer) {
                clearInterval(heroAutoTimer);
            }
        }

        stageTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                setHeroStage(this.getAttribute('data-stage'), true);
            });
        });

        hotspots.forEach(spot => {
            spot.addEventListener('click', function() {
                setHeroStage(this.getAttribute('data-stage'), true);
            });
            spot.addEventListener('mouseenter', function() {
                setHeroStage(this.getAttribute('data-stage'), true);
            });
        });

        heroAutoTimer = setInterval(() => {
            let nextStage = (currentHeroStage % 3) + 1;
            setHeroStage(nextStage);
        }, 6000);
    </script>
</body>
</html>
