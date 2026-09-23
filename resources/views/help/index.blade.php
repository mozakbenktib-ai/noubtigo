@extends('layouts.dashboard')

@section('title', __('ui.help_center') ?? 'Help Center & Documentation')
@section('header_title', __('ui.help_center') ?? 'Help Center')
@section('header_subtitle', __('ui.help_center_subtitle') ?? 'Interactive User Documentation & Feature Guides')

@section('content')
<div class="container-fluid py-4">

    {{-- Hero Search Banner --}}
    <div class="card border-0 text-white rounded-4 mb-5 shadow-lg overflow-hidden position-relative" style="background: var(--primary-gradient);">
        <div class="card-body p-4 p-md-5 text-center position-relative" style="z-index: 2;">
            <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-2 mb-3 text-uppercase fw-semibold" style="letter-spacing: 1.5px; font-size: 0.75rem;">
                <i class="bi bi-book-half me-1"></i> {{ __('ui.user_documentation_hub') ?? 'User Documentation Hub' }}
            </span>
            <h1 class="display-6 fw-bold mb-3">{{ __('ui.how_can_we_help') ?? 'How can we help you today?' }}</h1>
            <p class="lead text-white-50 mb-4 mx-auto" style="max-width: 600px; font-size: 1.05rem;">
                {{ __('ui.search_guides_subtitle') ?? 'Search guides, step-by-step business workflows, video tutorials, and answers to common questions.' }}
            </p>

            {{-- Live Search Form --}}
            <form action="{{ route('help.index') }}" method="GET" class="mx-auto" style="max-width: 650px;">
                <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden bg-body p-1">
                    <span class="input-group-text bg-transparent border-0 text-muted ps-3 pe-3">
                        <i class="bi bi-search fs-5"></i>
                    </span>
                    <input type="text" name="q" value="{{ $searchQuery }}" class="form-control border-0 shadow-none text-body bg-transparent fs-6" placeholder="{{ __('ui.search_help') ?? 'Search by topic (e.g. ticket, appointment, customer, room, hold)...' }}" autocomplete="off">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold text-white">{{ __('ui.search') ?? 'Search' }}</button>
                </div>
            </form>
        </div>
        {{-- Background decorative shapes --}}
        <div class="position-absolute rounded-circle bg-white opacity-10" style="width: 300px; height: 300px; top: -100px; right: -50px; pointer-events:none;"></div>
        <div class="position-absolute rounded-circle bg-white opacity-10" style="width: 200px; height: 200px; bottom: -80px; left: -40px; pointer-events:none;"></div>
    </div>

    {{-- Search Results View --}}
    @if($searchQuery)
    <div class="mb-5">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="fw-bold mb-0">{{ __('ui.search_results_for') ?? 'Search Results for' }} "<span class="text-primary">{{ $searchQuery }}</span>"</h4>
            <a href="{{ route('help.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">{{ __('ui.clear_search') ?? 'Clear Search' }}</a>
        </div>

        @if(count($searchResults) > 0)
        <div class="row g-4">
            @foreach($searchResults as $article)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift transition-all">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 font-monospace" style="font-size: 0.7rem;">
                                {{ $article['category'] }}
                            </span>
                            <i class="bi bi-journal-text text-muted"></i>
                        </div>
                        <h5 class="fw-bold mb-2">
                            <a href="{{ route('help.show', ['category' => $article['category_slug'], 'slug' => $article['slug']]) }}" class="text-decoration-none text-body">
                                {{ $article['title'] }}
                            </a>
                        </h5>
                        <p class="text-muted small mb-4 flex-grow-1" style="line-height: 1.5;">
                            {{ Str::limit($article['description'], 120) }}
                        </p>
                        <a href="{{ route('help.show', ['category' => $article['category_slug'], 'slug' => $article['slug']]) }}" class="btn btn-link text-primary p-0 fw-semibold text-decoration-none d-flex align-items-center gap-1">
                            {{ __('ui.read_guide') ?? 'Read Guide' }} <i class="bi {{ app()->getLocale() == 'ar' ? 'bi-arrow-left' : 'bi-arrow-right' }}"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="card border-0 shadow-sm rounded-4 text-center py-5">
            <div class="card-body">
                <i class="bi bi-search text-muted display-4 mb-3 d-block"></i>
                <h5 class="fw-bold mb-2">{{ __('ui.no_matching_guides') ?? 'No matching help guides found' }}</h5>
                <p class="text-muted small mb-4">{{ __('ui.try_searching_keywords') ?? 'Try searching with different keywords like queue, ticket, room, or appointment.' }}</p>
                <a href="{{ route('help.index') }}" class="btn btn-primary text-white rounded-pill px-4">{{ __('ui.view_all_categories') ?? 'View All Categories' }}</a>
            </div>
        </div>
        @endif
    </div>
    @else

    {{-- Category Cards Grid --}}
    <div class="row g-4 mb-5">
        @foreach($categories as $categoryName => $catData)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift transition-all">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-center shadow-sm" style="background: var(--primary-gradient); width: 48px; height: 48px;">
                            @if(Str::contains(strtolower($categoryName), ['getting', 'دليل', 'démarrage'])) <i class="bi bi-rocket-takeoff fs-4"></i>
                            @elseif(Str::contains(strtolower($categoryName), ['queue', 'طابور', 'file'])) <i class="bi bi-ticket-perforated fs-4"></i>
                            @elseif(Str::contains(strtolower($categoryName), ['appointment', 'مواعيد', 'rendez-vous'])) <i class="bi bi-calendar-check fs-4"></i>
                            @elseif(Str::contains(strtolower($categoryName), ['customer', 'عملاء', 'client'])) <i class="bi bi-people fs-4"></i>
                            @elseif(Str::contains(strtolower($categoryName), ['service', 'خدمات'])) <i class="bi bi-briefcase fs-4"></i>
                            @elseif(Str::contains(strtolower($categoryName), ['room', 'قاعات', 'guichet'])) <i class="bi bi-door-open fs-4"></i>
                            @elseif(Str::contains(strtolower($categoryName), ['staff', 'موظف', 'personnel'])) <i class="bi bi-person-badge fs-4"></i>
                            @elseif(Str::contains(strtolower($categoryName), ['display', 'شاشات', 'écran'])) <i class="bi bi-display fs-4"></i>
                            @elseif(Str::contains(strtolower($categoryName), ['faq', 'أسئلة', 'foire'])) <i class="bi bi-question-circle fs-4"></i>
                            @else <i class="bi bi-folder2-open fs-4"></i>
                            @endif
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-body">{{ $categoryName }}</h5>
                            <span class="text-muted small">{{ count($catData['articles']) }} {{ count($catData['articles']) === 1 ? (__('ui.article') ?? 'article') : (__('ui.articles') ?? 'articles') }}</span>
                        </div>
                    </div>

                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2 mt-3 pt-3 border-top">
                        @foreach($catData['articles'] as $art)
                        <li>
                            <a href="{{ route('help.show', ['category' => $art['category_slug'], 'slug' => $art['slug']]) }}" class="text-decoration-none text-secondary d-flex align-items-center justify-content-between p-2 rounded-3 hover-bg-light small fw-medium">
                                <span><i class="bi bi-file-text {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }} text-primary"></i> {{ $art['title'] }}</span>
                                <i class="bi {{ app()->getLocale() == 'ar' ? 'bi-chevron-left' : 'bi-chevron-right' }} text-muted opacity-50 fs-7"></i>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Interactive Onboarding Banner Callout --}}
    <div class="card border-0 bg-primary-subtle rounded-4 p-4 mb-5 border-start border-primary border-4 shadow-sm">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center p-3 fs-3" style="width: 54px; height: 54px;">
                    <i class="bi bi-compass-fill"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-body mb-1">{{ __('ui.new_to_noubtigo') ?? 'New to Noubtigo? Take the Interactive Tour' }}</h5>
                    <p class="text-secondary small mb-0">{{ __('ui.interactive_tour_desc') ?? 'Launch an interactive walkthrough highlighting the Dashboard, Queue controls, Services, and Public Displays.' }}</p>
                </div>
            </div>
            <button class="btn btn-primary text-white rounded-pill px-4 py-2 fw-semibold text-nowrap" id="startOnboardingBtn">
                <i class="bi bi-play-circle me-1"></i> {{ __('ui.start_tour') ?? 'Start Tour' }}
            </button>
        </div>
    </div>
    @endif

</div>
@endsection
