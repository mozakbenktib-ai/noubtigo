@extends('layouts.dashboard')

@section('title', 'Noubtigo | Experimental Packages & Add-ons Prototype')
@section('header_title', 'Experimental Packaging Sandbox')
@section('header_subtitle', 'Interactive UX simulation for Simple, Queue, Queue+ and modular Add-ons')

@push('styles')
<style>
    /* ── Prototype Color System & Glassmorphism ── */
    :root {
        --exp-simple: #10b981;
        --exp-queue: #0284c7;
        --exp-queue-plus: #8b5cf6;
        --exp-card-bg: rgba(255, 255, 255, 0.85);
        --exp-card-border: rgba(0, 0, 0, 0.08);
    }
    [data-bs-theme="dark"] {
        --exp-card-bg: rgba(30, 41, 59, 0.75);
        --exp-card-border: rgba(255, 255, 255, 0.08);
    }

    .sandbox-banner {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.12), rgba(234, 88, 12, 0.08));
        border: 1px solid rgba(245, 158, 11, 0.35);
        border-radius: 1rem;
        padding: 1rem 1.5rem;
        position: relative;
    }

    .glass-card {
        background: var(--exp-card-bg);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--exp-card-border);
        border-radius: 1.25rem;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
        transition: all 0.25s ease-in-out;
    }

    /* ── Model Switcher ── */
    .model-switcher-nav {
        background: rgba(0, 0, 0, 0.05);
        border-radius: 2rem;
        padding: 4px;
        display: inline-flex;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    [data-bs-theme="dark"] .model-switcher-nav {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.08);
    }
    .model-switcher-btn {
        border: none;
        background: transparent;
        padding: 0.5rem 1.25rem;
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 1.5rem;
        color: #64748b;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
    }
    .model-switcher-btn.active {
        background: #0f172a;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);
    }
    [data-bs-theme="dark"] .model-switcher-btn.active {
        background: #ffffff;
        color: #0f172a !important;
        box-shadow: 0 4px 12px rgba(255, 255, 255, 0.15);
    }

    /* ── Preset Chips ── */
    .preset-chip {
        border: 1px solid rgba(0, 0, 0, 0.1);
        background: var(--exp-card-bg);
        border-radius: 2rem;
        padding: 0.45rem 1rem;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        color: var(--bs-body-color);
    }
    [data-bs-theme="dark"] .preset-chip {
        border-color: rgba(255, 255, 255, 0.1);
    }
    .preset-chip:hover {
        border-color: var(--primary-color);
        transform: translateY(-2px);
    }
    .preset-chip.active {
        background: var(--primary-gradient);
        color: #ffffff !important;
        border-color: transparent;
        box-shadow: 0 4px 12px rgba(34, 197, 94, 0.25);
    }

    /* ── Core Package Cards ── */
    .package-card {
        border: 2px solid transparent;
        border-radius: 1.25rem;
        background: var(--exp-card-bg);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .package-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 16px 32px -8px rgba(0, 0, 0, 0.1);
    }
    .package-card.selected-simple {
        border-color: var(--exp-simple);
        box-shadow: 0 12px 28px -6px rgba(16, 185, 129, 0.25);
    }
    .package-card.selected-queue {
        border-color: var(--exp-queue);
        box-shadow: 0 12px 28px -6px rgba(2, 132, 199, 0.25);
    }
    .package-card.selected-queue_plus {
        border-color: var(--exp-queue-plus);
        box-shadow: 0 12px 28px -6px rgba(139, 92, 246, 0.25);
    }

    .package-card .select-indicator {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        border: 2px solid #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    .package-card.selected .select-indicator {
        background: #0f172a;
        border-color: #0f172a;
        color: white;
    }
    [data-bs-theme="dark"] .package-card.selected .select-indicator {
        background: #ffffff;
        border-color: #ffffff;
        color: #0f172a;
    }

    /* ── Add-on Cards ── */
    .addon-card {
        border: 2px solid transparent;
        background: var(--exp-card-bg);
        border-radius: 1.1rem;
        padding: 1.25rem;
        transition: all 0.25s ease;
        cursor: pointer;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .addon-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px -6px rgba(0, 0, 0, 0.08);
    }
    .addon-card.enabled {
        border-color: var(--primary-color);
        background: rgba(34, 197, 94, 0.03);
    }
    [data-bs-theme="dark"] .addon-card.enabled {
        background: rgba(34, 197, 94, 0.06);
    }

    .custom-toggle-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
        flex-shrink: 0;
    }
    .custom-toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #cbd5e1;
        transition: .3s;
        border-radius: 24px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    }
    input:checked + .slider {
        background-color: var(--primary-color);
    }
    input:checked + .slider:before {
        transform: translateX(20px);
    }

    /* ── Sticky Live Setup Bar ── */
    .live-setup-panel {
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.98));
        color: #ffffff;
        border-radius: 1.25rem;
        box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.12);
    }

    /* ── Matrix Table ── */
    .matrix-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    .matrix-table th {
        background: rgba(0, 0, 0, 0.03);
        padding: 0.9rem 1.25rem;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        border-bottom: 2px solid rgba(0, 0, 0, 0.06);
    }
    [data-bs-theme="dark"] .matrix-table th {
        background: rgba(255, 255, 255, 0.03);
        border-bottom-color: rgba(255, 255, 255, 0.08);
        color: #94a3b8;
    }
    .matrix-table td {
        padding: 0.85rem 1.25rem;
        font-size: 0.86rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.04);
        vertical-align: middle;
    }
    [data-bs-theme="dark"] .matrix-table td {
        border-bottom-color: rgba(255, 255, 255, 0.04);
    }
    .matrix-table tr.active-matrix-row {
        background: rgba(34, 197, 94, 0.05);
    }

    .badge-core {
        background: rgba(34, 197, 94, 0.12);
        color: #16a34a;
        font-weight: 700;
        font-size: 0.72rem;
        padding: 0.35rem 0.65rem;
        border-radius: 2rem;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    .badge-optional {
        background: rgba(59, 130, 246, 0.12);
        color: #2563eb;
        font-weight: 600;
        font-size: 0.72rem;
        padding: 0.35rem 0.65rem;
        border-radius: 2rem;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    .badge-excluded {
        color: #94a3b8;
        font-size: 0.9rem;
    }

    /* ── Interactive Workflow Simulator Drawer/Card ── */
    .sim-screen {
        background: #0b132b;
        border-radius: 1rem;
        padding: 1.25rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #e2e8f0;
        font-family: monospace;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    {{-- ── 1. Safety & Sandbox Mode Banner ── --}}
    <div class="sandbox-banner mb-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 p-2 bg-warning bg-opacity-25 text-warning fs-4 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-flask-fill"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-warning text-dark fw-bold px-2 py-1" style="font-size: 0.7rem;">SANDBOX EXPERIMENT</span>
                        <h6 class="mb-0 fw-bold">Noubtigo Product Packaging & Add-ons Prototype</h6>
                    </div>
                    <p class="mb-0 text-muted small">
                        Testing the <strong>Simple / Queue / Queue+</strong> packaging concept with modular add-ons. 
                        <strong>100% Non-destructive:</strong> No real subscriptions, limits, coupons, or billing accounts are affected.
                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('billing.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" target="_blank">
                    <i class="bi bi-box-arrow-up-right me-1"></i> View Live Production Billing
                </a>
                <button type="button" class="btn btn-sm btn-dark rounded-pill px-3" onclick="resetSandbox()">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Simulation
                </button>
            </div>
        </div>
    </div>

    {{-- ── 2. Architecture Comparison Switcher ── --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <span class="text-secondary small fw-bold text-uppercase tracking-wider d-block">Packaging Model Perspective</span>
            <h4 class="fw-bold mb-0">Evaluate Product Architecture</h4>
        </div>
        <div class="model-switcher-nav">
            <button type="button" class="model-switcher-btn active" id="btnModelExperimental" onclick="switchModel('experimental')">
                <i class="bi bi-layers-fill text-success"></i> Experimental: Simple / Queue / Queue+ & Add-ons
            </button>
            <button type="button" class="model-switcher-btn" id="btnModelCurrent" onclick="switchModel('current')">
                <i class="bi bi-shield-check text-secondary"></i> Current: Starter / Pro / Biz / Premium
            </button>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════════ --}}
    {{-- VIEW A: EXPERIMENTAL MODEL (SIMPLE / QUEUE / QUEUE+ & MODULAR ADD-ONS)   --}}
    {{-- ══════════════════════════════════════════════════════════════════════════ --}}
    <div id="viewExperimentalModel">

        {{-- Fast Presets Toolbar --}}
        <div class="glass-card p-3 mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-magic text-success"></i>
                    <span class="small fw-bold text-secondary text-uppercase" style="letter-spacing: 0.5px;">Test Fast Business Presets:</span>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2" id="presetChipsContainer">
                    @foreach($presets as $preset)
                    <button type="button" 
                            class="preset-chip" 
                            id="preset_{{ $preset['id'] }}"
                            onclick="applyPreset('{{ $preset['id'] }}', '{{ $preset['package'] }}', {{ json_encode($preset['addons']) }})"
                            title="{{ $preset['description'] }}">
                        <i class="bi bi-lightning-charge"></i>
                        <span>{{ $preset['title'] }}</span>
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── 3. Core Packages Section ── --}}
        <div class="mb-5">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-success bg-opacity-20 text-success fw-bold px-2 py-1" style="font-size: 0.68rem;">CORE PRODUCT</span>
                        <h5 class="fw-bold mb-0">1. Core Packages</h5>
                    </div>
                    <p class="text-muted small mb-0">
                        Packages represent the foundational operational tier. Every package is a standalone core product.
                    </p>
                </div>
                <span class="text-muted small d-none d-md-inline">
                    <i class="bi bi-hand-index-thumb me-1"></i> Click any package to test simulation
                </span>
            </div>

            <div class="row g-4">
                @foreach($packages as $pkgKey => $pkg)
                <div class="col-12 col-lg-4">
                    <div class="package-card p-4 glass-card {{ $pkgKey === 'queue' ? 'selected-queue selected' : '' }}" 
                         id="package_card_{{ $pkgKey }}"
                         onclick="selectPackage('{{ $pkgKey }}')">

                        {{-- Card Header --}}
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge rounded-pill mb-2 text-uppercase fw-bold" 
                                      style="background: {{ $pkgKey === 'simple' ? 'rgba(16, 185, 129, 0.15)' : ($pkgKey === 'queue' ? 'rgba(2, 132, 199, 0.15)' : 'rgba(139, 92, 246, 0.15)') }}; color: {{ $pkg['accent_color'] }}; font-size: 0.7rem; letter-spacing: 0.5px;">
                                    {{ $pkg['badge'] }}
                                </span>
                                <h4 class="fw-extrabold mb-1">{{ $pkg['name'] }}</h4>
                                <div class="fst-italic text-muted small fw-medium">“{{ $pkg['tagline'] }}”</div>
                            </div>
                            <div class="select-indicator" id="indicator_{{ $pkgKey }}">
                                <i class="bi bi-check fs-6 {{ $pkgKey === 'queue' ? '' : 'd-none' }}"></i>
                            </div>
                        </div>

                        <p class="text-muted small mb-3">
                            {{ $pkg['summary'] }}
                        </p>

                        {{-- Ideal For Tag --}}
                        <div class="p-2 rounded-3 bg-light-subtle border mb-4" style="font-size: 0.78rem;">
                            <span class="fw-bold text-secondary d-block mb-1"><i class="bi bi-building me-1"></i> Typical Fit:</span>
                            <span class="text-muted">{{ $pkg['ideal_for'] }}</span>
                        </div>

                        {{-- Capabilities List --}}
                        <div class="flex-grow-1">
                            <span class="text-uppercase fw-bold text-secondary small d-block mb-2" style="font-size: 0.68rem; letter-spacing: 1px;">
                                Included Capabilities:
                            </span>
                            <ul class="list-unstyled mb-4" style="font-size: 0.85rem;">
                                @foreach($pkg['capabilities'] as $cap)
                                <li class="d-flex align-items-start gap-2 mb-2">
                                    <i class="bi bi-check-circle-fill flex-shrink-0 mt-1" style="color: {{ $pkg['accent_color'] }};"></i>
                                    <span>{{ $cap }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Boundary Note Footer --}}
                        <div class="pt-3 border-top mt-auto">
                            <div class="text-muted small" style="font-size: 0.75rem;">
                                <i class="bi bi-info-circle me-1"></i> {{ $pkg['boundary_note'] }}
                            </div>
                            <button type="button" 
                                    class="btn btn-sm w-100 mt-3 rounded-pill fw-bold py-2 btn-pkg-select"
                                    id="btn_select_{{ $pkgKey }}"
                                    style="background: {{ $pkgKey === 'queue' ? $pkg['bg_gradient'] : 'transparent' }}; color: {{ $pkgKey === 'queue' ? '#fff' : 'var(--bs-body-color)' }}; border: 1px solid {{ $pkg['accent_color'] }};">
                                {{ $pkgKey === 'queue' ? '✓ Selected Package' : 'Select ' . $pkg['name'] }}
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ── 4. Modular Add-ons Section ── --}}
        <div class="mb-5">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-3 gap-2">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary bg-opacity-20 text-primary fw-bold px-2 py-1" style="font-size: 0.68rem;">OPTIONAL CAPABILITIES</span>
                        <h5 class="fw-bold mb-0">2. Modular Add-ons</h5>
                    </div>
                    <p class="text-muted small mb-0">
                        Add-ons are <strong>independent optional capabilities</strong> decoupled from core packages. Attach any add-on to any package.
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="toggleAllAddons(true)">
                        Enable All
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="toggleAllAddons(false)">
                        Clear All
                    </button>
                </div>
            </div>

            <div class="row g-3">
                @foreach($addons as $addKey => $addon)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="addon-card glass-card p-3" 
                         id="addon_card_{{ $addKey }}"
                         onclick="toggleAddon('{{ $addKey }}')">

                        <div>
                            {{-- Addon Header --}}
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" 
                                         style="background: {{ $addon['color'] }}20; color: {{ $addon['color'] }}; width: 40px; height: 40px;">
                                        <i class="bi {{ $addon['icon'] }} fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0">{{ $addon['name'] }}</h6>
                                        <span class="text-muted small" style="font-size: 0.72rem;">{{ $addon['category'] }}</span>
                                    </div>
                                </div>
                                
                                {{-- Switch --}}
                                <label class="custom-toggle-switch" onclick="event.stopPropagation();">
                                    <input type="checkbox" id="check_addon_{{ $addKey }}" onchange="onAddonCheckboxChange('{{ $addKey }}')">
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="fw-medium text-slate-700 small mb-1" style="font-size: 0.8rem;">
                                {{ $addon['headline'] }}
                            </div>
                            <p class="text-muted small mb-3" style="font-size: 0.78rem;">
                                {{ $addon['description'] }}
                            </p>

                            {{-- Capabilities --}}
                            <div class="border-top pt-2">
                                <span class="text-uppercase fw-bold text-secondary d-block mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">Features:</span>
                                <ul class="list-unstyled mb-0" style="font-size: 0.78rem;">
                                    @foreach($addon['capabilities'] as $cap)
                                    <li class="d-flex align-items-center gap-2 mb-1 text-muted">
                                        <i class="bi bi-plus-circle-fill text-primary" style="font-size: 0.7rem;"></i>
                                        <span>{{ $cap }}</span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-3" style="font-size: 0.72rem;">
                            <span class="text-muted">Type: Optional Module</span>
                            <span class="fw-bold" id="addon_status_badge_{{ $addKey }}" style="color: #94a3b8;">
                                DISABLED
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ── 5. Live Setup Simulation Panel ("Your Noubtigo Setup") ── --}}
        <div class="live-setup-panel p-4 mb-5" id="liveSetupPanel">
            <div class="row g-4 align-items-center">
                <div class="col-12 col-lg-7">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 px-2 py-1" style="font-size: 0.7rem;">
                            LIVE SIMULATION ENGINE
                        </span>
                        <span class="text-white-50 small">Dynamic Setup Preview</span>
                    </div>

                    <h3 class="fw-bold text-white mb-2">
                        Your Noubtigo Setup: <span id="simActivePackageName" class="text-success">Noubtigo Queue</span>
                    </h3>

                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3" id="simAddonsBadgeList">
                        <span class="badge bg-secondary bg-opacity-50 text-white-50 px-2 py-1">No add-ons selected</span>
                    </div>

                    <p class="text-white-50 small mb-0">
                        This sandbox models how customer experiences and staff consoles configure under this setup. 
                        <strong>Zero database writes</strong> or financial actions occur.
                    </p>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-white small fw-bold"><i class="bi bi-sliders me-1"></i> Capabilities Audit</span>
                            <span class="badge bg-success text-white" id="simCapabilitiesCount">11 Active</span>
                        </div>

                        <div class="small text-white-50 mb-3" style="font-size: 0.8rem;" id="simCapabilitiesBrief">
                            Core queueing, customer database & multi-service routing active.
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-success rounded-pill px-3 flex-grow-1" onclick="openStaffSimulationModal()">
                                <i class="bi bi-display me-1"></i> Simulate Staff Console
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" onclick="copySimulationShare()">
                                <i class="bi bi-share me-1"></i> Share Setup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 6. Full Capabilities Comparison Matrix ── --}}
        <div class="glass-card p-4 mb-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <div>
                    <h5 class="fw-bold mb-1">Capabilities Comparison Matrix</h5>
                    <p class="text-muted small mb-0">
                        Conceptual mapping of which capabilities are core vs optional across the 3 packages.
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-core">Core = Included in package</span>
                    <span class="badge badge-optional">Optional = Available via Add-on</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="matrix-table">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Capability / Feature</th>
                            <th class="text-center" style="width: 15%;" id="th_simple">Simple</th>
                            <th class="text-center" style="width: 15%;" id="th_queue">Queue</th>
                            <th class="text-center" style="width: 15%;" id="th_queue_plus">Queue+</th>
                            <th class="text-center" style="width: 15%;">Add-on Availability</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($comparisonMatrix as $cat)
                        <tr class="table-group-header">
                            <td colspan="5" class="fw-bold text-secondary py-2 bg-light-subtle" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                <i class="bi bi-folder2-open me-1"></i> {{ strtoupper($cat['category']) }}
                            </td>
                        </tr>
                        @foreach($cat['items'] as $item)
                        <tr id="matrix_row_{{ Str::slug($item['name']) }}">
                            <td class="fw-medium">
                                {{ $item['name'] }}
                            </td>

                            {{-- Simple Column --}}
                            <td class="text-center">
                                @if($item['simple'] === 'Core')
                                    <span class="badge-core"><i class="bi bi-check-circle-fill"></i> Core</span>
                                @elseif($item['simple'] === 'Optional')
                                    <span class="badge-optional"><i class="bi bi-plus-circle"></i> Optional</span>
                                @else
                                    <span class="badge-excluded">—</span>
                                @endif
                            </td>

                            {{-- Queue Column --}}
                            <td class="text-center">
                                @if($item['queue'] === 'Core')
                                    <span class="badge-core"><i class="bi bi-check-circle-fill"></i> Core</span>
                                @elseif($item['queue'] === 'Optional')
                                    <span class="badge-optional"><i class="bi bi-plus-circle"></i> Optional</span>
                                @else
                                    <span class="badge-excluded">—</span>
                                @endif
                            </td>

                            {{-- Queue+ Column --}}
                            <td class="text-center">
                                @if($item['queue_plus'] === 'Core')
                                    <span class="badge-core"><i class="bi bi-check-circle-fill"></i> Core</span>
                                @elseif($item['queue_plus'] === 'Optional')
                                    <span class="badge-optional"><i class="bi bi-plus-circle"></i> Optional</span>
                                @else
                                    <span class="badge-excluded">—</span>
                                @endif
                            </td>

                            {{-- Add-on status --}}
                            <td class="text-center">
                                @if($item['addon'])
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 px-2 py-1" style="font-size: 0.72rem;">
                                        {{ $item['addon'] }}
                                    </span>
                                @else
                                    <span class="text-muted small">Built into Core</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div> {{-- /viewExperimentalModel --}}


    {{-- ══════════════════════════════════════════════════════════════════════════ --}}
    {{-- VIEW B: CURRENT PRODUCTION MODEL (FOR STAKEHOLDER CONTRAST)              --}}
    {{-- ══════════════════════════════════════════════════════════════════════════ --}}
    <div id="viewCurrentModel" class="d-none">
        <div class="glass-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <span class="badge bg-secondary bg-opacity-20 text-secondary fw-bold px-2 py-1" style="font-size: 0.7rem;">PRODUCTION SYSTEM (ACTIVE)</span>
                    <h4 class="fw-bold mb-1">Current Production Plan Architecture</h4>
                    <p class="text-muted small mb-0">
                        The live application currently operates on numeric tier limits (staff limit, ticket limit, room limit, customer limit).
                    </p>
                </div>
                <a href="{{ route('billing.index') }}" class="btn btn-outline-primary btn-sm rounded-pill" target="_blank">
                    <i class="bi bi-arrow-right me-1"></i> Open Production Billing Page
                </a>
            </div>

            <div class="row g-4 mt-2">
                @foreach($currentProductionPlans as $prodPlan)
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="p-3 rounded-3 border bg-body h-100 d-flex flex-direction-column">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="fw-bold mb-0">{{ $prodPlan->name }}</h5>
                            <span class="badge bg-light text-dark border">{{ $prodPlan->slug }}</span>
                        </div>
                        <div class="fs-4 fw-extrabold text-success mb-2">
                            ${{ number_format($prodPlan->price, 2) }} <span class="text-muted fs-6 fw-normal">/mo</span>
                        </div>
                        <div class="text-muted small mb-3 border-top pt-2">
                            <strong>Configured Limits:</strong>
                            <ul class="list-unstyled mt-2 mb-0" style="font-size: 0.8rem;">
                                @if(is_array($prodPlan->limits))
                                    @foreach($prodPlan->limits as $k => $v)
                                    <li class="d-flex justify-content-between py-1 border-bottom border-light">
                                        <span class="text-muted">{{ str_replace('_', ' ', $k) }}:</span>
                                        <span class="fw-bold">{{ $v == -1 ? 'Unlimited' : $v }}</span>
                                    </li>
                                    @endforeach
                                @else
                                    <li class="text-muted">Standard defaults</li>
                                @endif
                            </ul>
                        </div>
                        <div class="mt-auto">
                            <span class="badge bg-success bg-opacity-10 text-success w-100 py-2">
                                <i class="bi bi-shield-check me-1"></i> Live Production Plan
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="alert alert-info mt-4 mb-0 rounded-3" style="font-size: 0.85rem;">
                <i class="bi bi-lightbulb-fill me-2"></i>
                <strong>Key Architectural Question for Decision-Makers:</strong>
                Do you prefer the <em>current quantitative tier model</em> (differentiated by staff/ticket/room quotas) or the <em>new qualitative capability model</em> (Simple = Line only; Queue = Customers + Queues; Queue+ = Queues + Appointments) with optional unbundled add-ons?
            </div>
        </div>
    </div>

</div>

{{-- ── Interactive Staff Console Simulation Modal ── --}}
<div class="modal fade" id="staffSimulationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1.25rem; overflow: hidden;">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success">SIMULATION</span>
                    <h6 class="modal-title fw-bold mb-0">Staff Console Simulation</h6>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light-subtle">
                <div class="mb-3">
                    <div class="small text-muted mb-1">Simulated Configuration:</div>
                    <h5 class="fw-bold" id="modalConfigTitle">Noubtigo Queue + WhatsApp Add-on</h5>
                </div>

                <div class="sim-screen mb-3" id="modalSimulatedScreen">
                    {{-- Dynamically populated by JS --}}
                </div>

                <div class="p-3 bg-white rounded-3 border small">
                    <div class="fw-bold text-slate-800 mb-1"><i class="bi bi-cpu me-1"></i> Under-the-hood Adaptation:</div>
                    <div class="text-muted" id="modalAdaptationExplanation">
                        Staff console UI dynamically strips away unused capabilities to keep staff speed maximized.
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light-subtle pt-0">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close Preview</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // ── Static Prototype State ──
    const packagesData = @json($packages);
    const addonsData = @json($addons);
    const presetsData = @json($presets);

    let state = {
        selectedPackage: 'queue',
        activeAddons: new Set(['whatsapp']),
        activePreset: 'preset_salon'
    };

    // ── Initialize on Load ──
    document.addEventListener('DOMContentLoaded', () => {
        // Load initial state
        updateUI();
    });

    // ── Model Switching ──
    function switchModel(mode) {
        const btnExp = document.getElementById('btnModelExperimental');
        const btnCur = document.getElementById('btnModelCurrent');
        const viewExp = document.getElementById('viewExperimentalModel');
        const viewCur = document.getElementById('viewCurrentModel');

        if (mode === 'experimental') {
            btnExp.classList.add('active');
            btnCur.classList.remove('active');
            viewExp.classList.remove('d-none');
            viewCur.classList.add('d-none');
        } else {
            btnCur.classList.add('active');
            btnExp.classList.remove('active');
            viewCur.classList.remove('d-none');
            viewExp.classList.add('d-none');
        }
    }

    // ── Package Selection ──
    function selectPackage(pkgKey) {
        state.selectedPackage = pkgKey;
        state.activePreset = null; // Custom combo
        updateUI();
    }

    // ── Addon Toggling ──
    function toggleAddon(addKey) {
        const check = document.getElementById(`check_addon_${addKey}`);
        if (check) {
            check.checked = !check.checked;
            onAddonCheckboxChange(addKey);
        }
    }

    function onAddonCheckboxChange(addKey) {
        const check = document.getElementById(`check_addon_${addKey}`);
        if (check.checked) {
            state.activeAddons.add(addKey);
        } else {
            state.activeAddons.delete(addKey);
        }
        state.activePreset = null;
        updateUI();
    }

    function toggleAllAddons(enable) {
        Object.keys(addonsData).forEach(key => {
            const check = document.getElementById(`check_addon_${key}`);
            if (check) check.checked = enable;
            if (enable) state.activeAddons.add(key);
            else state.activeAddons.delete(key);
        });
        state.activePreset = null;
        updateUI();
    }

    // ── Apply Business Preset ──
    function applyPreset(presetId, pkgKey, addons) {
        state.selectedPackage = pkgKey;
        state.activeAddons = new Set(addons);
        state.activePreset = presetId;

        // Sync addon checkboxes
        Object.keys(addonsData).forEach(key => {
            const check = document.getElementById(`check_addon_${key}`);
            if (check) check.checked = addons.includes(key);
        });

        updateUI();
    }

    // ── Reset Sandbox ──
    function resetSandbox() {
        applyPreset('preset_salon', 'queue', ['whatsapp']);
    }

    // ── Central UI Update ──
    function updateUI() {
        const currentPkg = packagesData[state.selectedPackage];

        // 1. Update Package Cards
        Object.keys(packagesData).forEach(key => {
            const card = document.getElementById(`package_card_${key}`);
            const ind = document.getElementById(`indicator_${key}`);
            const btn = document.getElementById(`btn_select_${key}`);
            const isSelected = (key === state.selectedPackage);

            if (card) {
                card.className = `package-card p-4 glass-card ${isSelected ? `selected-${key} selected` : ''}`;
            }
            if (ind) {
                ind.innerHTML = isSelected ? '<i class="bi bi-check fs-6"></i>' : '';
            }
            if (btn) {
                const pkg = packagesData[key];
                if (isSelected) {
                    btn.style.background = pkg.bg_gradient;
                    btn.style.color = '#ffffff';
                    btn.innerText = `✓ Selected: ${pkg.name}`;
                } else {
                    btn.style.background = 'transparent';
                    btn.style.color = 'var(--bs-body-color)';
                    btn.innerText = `Select ${pkg.name}`;
                }
            }
        });

        // 2. Update Add-on Cards
        Object.keys(addonsData).forEach(key => {
            const card = document.getElementById(`addon_card_${key}`);
            const check = document.getElementById(`check_addon_${key}`);
            const badge = document.getElementById(`addon_status_badge_${key}`);
            const isEnabled = state.activeAddons.has(key);

            if (check) check.checked = isEnabled;
            if (card) {
                if (isEnabled) card.classList.add('enabled');
                else card.classList.remove('enabled');
            }
            if (badge) {
                badge.innerText = isEnabled ? '✓ ENABLED' : 'DISABLED';
                badge.style.color = isEnabled ? '#16a34a' : '#94a3b8';
            }
        });

        // 3. Update Presets Chips
        presetsData.forEach(p => {
            const chip = document.getElementById(`preset_${p.id}`);
            if (chip) {
                if (state.activePreset === p.id) chip.classList.add('active');
                else chip.classList.remove('active');
            }
        });

        // 4. Update Live Setup Panel
        const pkgNameEl = document.getElementById('simActivePackageName');
        if (pkgNameEl) {
            pkgNameEl.innerText = currentPkg.name;
            pkgNameEl.style.color = currentPkg.accent_color;
        }

        const addonBadgeList = document.getElementById('simAddonsBadgeList');
        if (addonBadgeList) {
            if (state.activeAddons.size === 0) {
                addonBadgeList.innerHTML = '<span class="badge bg-secondary bg-opacity-50 text-white-50 px-2 py-1">No optional add-ons selected</span>';
            } else {
                let html = '';
                state.activeAddons.forEach(addKey => {
                    const addon = addonsData[addKey];
                    if (addon) {
                        html += `<span class="badge px-3 py-1 rounded-pill" style="background: ${addon.color}30; color: #ffffff; border: 1px solid ${addon.color};">
                                    <i class="bi ${addon.icon} me-1"></i> ${addon.name}
                                 </span>`;
                    }
                });
                addonBadgeList.innerHTML = html;
            }
        }

        // Count Capabilities
        const totalCaps = currentPkg.capabilities.length + (state.activeAddons.size * 4);
        const countEl = document.getElementById('simCapabilitiesCount');
        if (countEl) countEl.innerText = `${totalCaps} Active Capabilities`;

        const briefEl = document.getElementById('simCapabilitiesBrief');
        if (briefEl) {
            let desc = `Package: ${currentPkg.name}. `;
            if (state.activeAddons.size > 0) {
                desc += `Enhanced with ${state.activeAddons.size} optional add-on module(s).`;
            } else {
                desc += 'Pure core setup without optional add-ons.';
            }
            briefEl.innerText = desc;
        }

        // 5. Highlight Comparison Matrix Header
        ['simple', 'queue', 'queue_plus'].forEach(k => {
            const th = document.getElementById(`th_${k}`);
            if (th) {
                if (k === state.selectedPackage) {
                    th.style.background = 'rgba(34, 197, 94, 0.15)';
                    th.style.color = '#16a34a';
                    th.style.fontWeight = '800';
                } else {
                    th.style.background = '';
                    th.style.color = '';
                    th.style.fontWeight = '';
                }
            }
        });
    }

    // ── Open Staff Simulation Modal ──
    function openStaffSimulationModal() {
        const modalEl = document.getElementById('staffSimulationModal');
        const titleEl = document.getElementById('modalConfigTitle');
        const screenEl = document.getElementById('modalSimulatedScreen');
        const expEl = document.getElementById('modalAdaptationExplanation');

        const currentPkg = packagesData[state.selectedPackage];
        const activeAddonNames = Array.from(state.activeAddons).map(k => addonsData[k]?.name).filter(Boolean);

        titleEl.innerText = `${currentPkg.name} ${activeAddonNames.length ? ' + ' + activeAddonNames.join(', ') : '(No Add-ons)'}`;

        let terminalHtml = '';

        if (state.selectedPackage === 'simple') {
            terminalHtml = `
                <div class="text-success small mb-2">// NOUBTIGO SIMPLE MODE — STREAMLINED FOR INSTANT TICKETING</div>
                <div class="d-flex justify-content-between align-items-center p-2 rounded bg-black mb-3">
                    <div><span>CURRENT TICKET:</span> <strong class="text-warning fs-5">#A-042</strong></div>
                    <button class="btn btn-sm btn-success px-3">CALL NEXT</button>
                    <button class="btn btn-sm btn-secondary px-3">SKIP</button>
                </div>
                <div class="text-secondary small">
                    [INFO] Customer Name/Phone fields: HIDDEN (Zero collection)<br>
                    [INFO] Services: DEFAULT GENERAL SERVICE<br>
                    [INFO] Calendar/Appointments: DISABLED
                </div>
            `;
            expEl.innerText = "In Simple mode, the staff console strips away customer profiles, appointments, and service selectors. Staff only see Next / Call / Skip, keeping service times under 10 seconds.";
        } else if (state.selectedPackage === 'queue') {
            terminalHtml = `
                <div class="text-info small mb-2">// NOUBTIGO QUEUE MODE — CUSTOMER-CENTRIC MULTI-QUEUE CONSOLE</div>
                <div class="p-2 rounded bg-black mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>SERVING: <strong class="text-white">Zakaria T.</strong> (+212 600-000000)</span>
                        <span class="badge bg-info text-dark">VIP Priority</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>Service: <strong>Passport & ID Renewal</strong> | Desk: #3</div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-info px-2">Next Customer</button>
                            <button class="btn btn-sm btn-dark border px-2">Transfer Queue</button>
                        </div>
                    </div>
                </div>
                <div class="text-secondary small">
                    [INFO] Customer History: ACTIVE (3 visits recorded)<br>
                    [INFO] Multi-staff routing: ACTIVE<br>
                    [INFO] Appointments: NOT INCLUDED
                </div>
            `;
            expEl.innerText = "In Queue mode, staff have access to the customer CRM directory, visit timelines, priority tags, and multiple services.";
        } else {
            terminalHtml = `
                <div class="text-purple small mb-2" style="color: #c084fc;">// NOUBTIGO QUEUE+ MODE — APPOINTMENT MERGE & ADVANCED SCHEDULING</div>
                <div class="p-2 rounded bg-black mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>APPOINTMENT CHECK-IN: <strong class="text-white">Dr. Sarah Consultation</strong></span>
                        <span class="badge bg-success">10:30 AM Slot (Checked In)</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>Room: <strong>Clinic Room B</strong> | Patient: <strong>Karim B.</strong></div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-primary px-2">Call to Room</button>
                            <button class="btn btn-sm btn-outline-danger px-2">Mark No-Show</button>
                        </div>
                    </div>
                </div>
                <div class="text-secondary small">
                    [INFO] Calendar Engine: ACTIVE (Slots, capacities, rooms)<br>
                    [INFO] Walk-ins + Booked Merging: ENABLED<br>
                    [INFO] No-show lifecycle: ACTIVE
                </div>
            `;
            expEl.innerText = "In Queue+ mode, staff consoles synchronize the daily calendar time-slots, rooms, and check-in desk directly into the physical queue line.";
        }

        // Addon simulated impacts
        if (state.activeAddons.has('whatsapp')) {
            terminalHtml += `<div class="mt-2 text-success small"><i class="bi bi-whatsapp me-1"></i> [ADDON: WhatsApp] Automated turn alert ping scheduled for customer phone.</div>`;
        }
        if (state.activeAddons.has('display')) {
            terminalHtml += `<div class="mt-1 text-info small"><i class="bi bi-display me-1"></i> [ADDON: Displays] TV Screen counter updated with chime.</div>`;
        }
        if (state.activeAddons.has('notifications')) {
            terminalHtml += `<div class="mt-1 text-warning small"><i class="bi bi-bell me-1"></i> [ADDON: Notifications] SMS/Email confirmation dispatched.</div>`;
        }

        screenEl.innerHTML = terminalHtml;

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    // ── Share Setup / Copy ──
    function copySimulationShare() {
        const pkg = packagesData[state.selectedPackage].name;
        const addons = Array.from(state.activeAddons).map(k => addonsData[k]?.name).join(', ') || 'None';
        const text = `Noubtigo Experimental Packaging Test:\nSelected Package: ${pkg}\nActive Add-ons: ${addons}\nTest URL: ${window.location.href}`;

        navigator.clipboard.writeText(text).then(() => {
            alert('Simulation configuration copied to clipboard!');
        }).catch(() => {
            alert('Setup: ' + pkg + ' with Add-ons: ' + addons);
        });
    }
</script>
@endpush
