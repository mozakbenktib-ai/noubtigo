@extends('layouts.dashboard')

@section('title', $article['title'] . ' | ' . (__('ui.help_center') ?? 'Help Center'))
@section('header_title', $article['title'])
@section('header_subtitle', $article['category'])

@section('content')
<div class="container-fluid py-4">

    {{-- Breadcrumb Navigation --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('help.index') }}" class="text-decoration-none text-muted"><i class="bi bi-journal-bookmark {{ app()->getLocale() == 'ar' ? 'ms-1' : 'me-1' }}"></i> {{ __('ui.help_center') ?? 'Help Center' }}</a></li>
            <li class="breadcrumb-item text-muted">{{ $article['category'] }}</li>
            <li class="breadcrumb-item active fw-semibold" aria-current="page">{{ $article['title'] }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        {{-- Main Article Reader --}}
        <div class="col-lg-8 col-xl-9">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-body p-4 p-md-5">

                    {{-- Title Header --}}
                    <div class="border-bottom pb-4 mb-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 font-monospace" style="font-size: 0.75rem;">
                                {{ $article['category'] }}
                            </span>
                            @if(!empty($article['roles']))
                            <span class="text-muted small">
                                <i class="bi bi-shield-check text-success {{ app()->getLocale() == 'ar' ? 'ms-1' : 'me-1' }}"></i> {{ __('ui.for_roles') ?? 'For' }}: {{ implode(', ', array_map('ucfirst', $article['roles'])) }}
                            </span>
                            @endif
                        </div>
                        <h1 class="fw-bold text-body mb-3">{{ $article['title'] }}</h1>
                        @if($article['description'])
                            <p class="lead text-secondary fs-6 mb-0" style="line-height: 1.6;">{{ $article['description'] }}</p>
                        @endif
                    </div>

                    {{-- Markdown Rendered Content --}}
                    <div class="markdown-body help-article-content text-body" style="line-height: 1.7; font-size: 0.95rem;">
                        {!! $article['content_html'] !!}
                    </div>

                </div>
            </div>
        </div>

        {{-- Right Sidebar --}}
        <div class="col-lg-4 col-xl-3">
            {{-- Quick Links Card --}}
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 sticky-top" style="top: 100px;">
                <h6 class="fw-bold text-body mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-life-preserver text-primary fs-5"></i> {{ __('ui.need_assistance') ?? 'Need Assistance?' }}
                </h6>
                <p class="text-muted small mb-3">{{ __('ui.cant_find_guide') ?? 'Can\'t find what you\'re looking for in this guide?' }}</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary rounded-pill btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#helpCenterModal">
                        <i class="bi bi-lightning-charge {{ app()->getLocale() == 'ar' ? 'ms-1' : 'me-1' }}"></i> {{ __('ui.open_page_help') ?? 'Open Page Help' }}
                    </button>
                    <a href="{{ route('help.index') }}" class="btn btn-light rounded-pill btn-sm fw-semibold text-secondary">
                        <i class="bi {{ app()->getLocale() == 'ar' ? 'bi-arrow-right ms-1' : 'bi-arrow-left me-1' }}"></i> {{ __('ui.all_documentation') ?? 'All Documentation' }}
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
