@extends('layouts.dashboard')

@section('title', 'Noubtigo | Billing & Subscriptions')
@section('header_title', __('ui.billing') ?? 'Billing & Subscription')
@section('header_subtitle', __('ui.billing_subtitle') ?? 'Manage your plan, payments, and invoices')

@push('styles')
<style>
    /* Premium Modern Glassmorphism Theme */
    .premium-billing-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 1.25rem;
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.02);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    [data-bs-theme="dark"] .premium-billing-card {
        background: rgba(30, 41, 59, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.06);
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.2);
    }

    /* Plan Selection Grid Card styling */
    .premium-plan-label {
        display: block;
        cursor: pointer;
        height: 100%;
    }
    
    .premium-plan-card {
        background: var(--bs-card-bg, #fff);
        border: 2px solid rgba(0, 0, 0, 0.05);
        border-radius: 1.25rem;
        padding: 2rem 1.75rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    [data-bs-theme="dark"] .premium-plan-card {
        border-color: rgba(255, 255, 255, 0.05);
    }
    
    .premium-plan-card:hover {
        transform: translateY(-4px);
        border-color: var(--primary-color);
        box-shadow: 0 20px 25px -5px rgba(34, 197, 94, 0.1), 0 8px 10px -6px rgba(34, 197, 94, 0.05);
    }
    
    .premium-plan-label input:checked + .premium-plan-card {
        border-color: var(--primary-color);
        background: rgba(34, 197, 94, 0.02);
        box-shadow: 0 20px 25px -5px rgba(34, 197, 94, 0.12);
    }
    
    [data-bs-theme="dark"] .premium-plan-label input:checked + .premium-plan-card {
        background: rgba(34, 197, 94, 0.04);
    }

    /* Custom Switch & Control designs */
    .billing-cycle-switch {
        background: rgba(0, 0, 0, 0.03);
        padding: 0.4rem;
        border-radius: 2rem;
        display: inline-flex;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    [data-bs-theme="dark"] .billing-cycle-switch {
        background: rgba(255, 255, 255, 0.03);
        border-color: rgba(255, 255, 255, 0.05);
    }
    
    .billing-cycle-btn {
        border: none;
        background: transparent;
        padding: 0.5rem 1.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 2rem;
        color: var(--text-muted, #64748b);
        transition: all 0.2s ease;
    }
    
    .billing-cycle-btn.active {
        background: var(--primary-color);
        color: #fff !important;
        box-shadow: 0 4px 12px rgba(34, 197, 94, 0.25);
    }

    /* Method Selection card styles */
    .premium-method-card {
        border: 2px solid rgba(0, 0, 0, 0.05);
        border-radius: 1rem;
        padding: 1.25rem;
        transition: all 0.25s ease;
        height: 100%;
        background: var(--bs-card-bg, #fff);
    }
    
    [data-bs-theme="dark"] .premium-method-card {
        border-color: rgba(255, 255, 255, 0.05);
    }
    
    .premium-method-card:hover {
        border-color: var(--primary-color);
    }
    
    .premium-method-label input:checked + .premium-method-card {
        border-color: var(--primary-color);
        background: rgba(34, 197, 94, 0.02);
    }
    
    [data-bs-theme="dark"] .premium-method-label input:checked + .premium-method-card {
        background: rgba(34, 197, 94, 0.04);
    }
    
    /* Modern Status Badges */
    .status-badge {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.35rem 0.75rem;
        border-radius: 2rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    .status-active { background: rgba(34, 197, 94, 0.12); color: #16a34a; }
    .status-pending { background: rgba(234, 179, 8, 0.12); color: #a16207; }
    .status-suspended { background: rgba(239, 68, 68, 0.12); color: #dc2626; }
    .status-cancelled { background: rgba(100, 116, 139, 0.12); color: #475569; }

    /* Table customization */
    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .modern-table th {
        background: rgba(0, 0, 0, 0.01);
        padding: 1rem 1.25rem;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.5px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    [data-bs-theme="dark"] .modern-table th {
        background: rgba(255, 255, 255, 0.01);
        border-bottom-color: rgba(255, 255, 255, 0.05);
        color: #94a3b8;
    }
    .modern-table td {
        padding: 1.1rem 1.25rem;
        font-size: 0.875rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.03);
    }
    [data-bs-theme="dark"] .modern-table td {
        border-bottom-color: rgba(255, 255, 255, 0.02);
    }
    .btn-gradient {
        background: var(--primary-gradient);
        border: none;
        color: white !important;
        transition: all 0.2s ease-in-out;
    }
    .btn-gradient:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(34, 197, 94, 0.2);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="row g-4">

        {{-- ── 1. Current Subscription Status ── --}}
        <div class="col-12">
            <div class="premium-billing-card p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4">
                    <div>
                        <span class="text-secondary small fw-bold text-uppercase tracking-wider mb-1 d-block">{{ __('ui.current_plan') }}</span>
                        @if($activeSubscription)
                            <h3 class="fw-extrabold text-slate-800 mb-2">{{ $activeSubscription->plan->name }}</h3>
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <span class="status-badge status-active">
                                    <i class="bi bi-check-circle-fill"></i> {{ __('ui.active') }}
                                </span>
                                <span class="text-muted small">
                                    <i class="bi bi-arrow-repeat me-1"></i>
                                    {{ __('ui.billing_cycle_label', ['cycle' => __('ui.' . $activeSubscription->billing_cycle)]) }}
                                </span>
                                @if($activeSubscription->ends_at)
                                <span class="text-muted small">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    {{ __('ui.renews') }} {{ $activeSubscription->ends_at->format('M d, Y') }}
                                    <span class="text-{{ $activeSubscription->ends_at->isPast() ? 'danger' : 'muted' }}">
                                        ({{ $activeSubscription->ends_at->diffForHumans() }})
                                    </span>
                                </span>
                                @endif
                            </div>
                        @elseif($pendingSubscription)
                            <h3 class="fw-extrabold mb-2">{{ $pendingSubscription->plan->name }}</h3>
                            <div class="d-flex align-items-center gap-3">
                                <span class="status-badge status-pending">
                                    <i class="bi bi-hourglass-split"></i> {{ __('ui.pending_payment') }}
                                </span>
                                <span class="text-muted small">{{ __('ui.subscription_pending_desc') }}</span>
                            </div>
                            
                            {{-- Pending Payment Handling Area --}}
                            @php $pendingPayment = $payments->where('status', 'pending')->first(); @endphp
                            @if($pendingPayment)
                                <div class="mt-4 p-4 border border-warning border-opacity-25 rounded-4 bg-warning bg-opacity-5">
                                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-credit-card-2-front me-2 text-warning"></i> {{ __('ui.choose_method_pending') }}</h6>
                                    
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <div class="p-3 rounded-3 border text-dark h-100 bg-white" id="pending-toggle-virement" style="cursor:pointer; transition: all 0.2s; border-color: var(--primary-color);">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;flex-shrink:0">
                                                        <i class="bi bi-bank fs-5"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="fw-bold mb-0">{{ __('ui.bank_transfer') }}</h6>
                                                        <small class="text-muted">{{ __('ui.bank_transfer') }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="p-3 rounded-3 border text-dark h-100 bg-white" id="pending-toggle-chari" style="cursor:pointer; transition: all 0.2s; border-color: #e2e8f0;">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;flex-shrink:0">
                                                        <i class="bi bi-phone fs-5"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="fw-bold mb-0">{{ __('ui.online_payment') }}</h6>
                                                        <small class="text-muted">{{ __('ui.chari_pay') }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Bank Transfer Instructions --}}
                                    <div id="pending-panel-virement" class="p-4 border border-opacity-10 rounded-4 bg-white">
                                        <h6 class="fw-bold mb-3"><i class="bi bi-bank text-warning me-1"></i> {{ __('ui.bank_transfer_details') }}</h6>
                                        <div class="row small mb-3 g-2 text-muted">
                                            <div class="col-sm-6"><strong>{{ __('ui.bank_name') }}:</strong> Noubtigo Global Bank</div>
                                            <div class="col-sm-6"><strong>{{ __('ui.account_name') }}:</strong> Noubtigo Solutions</div>
                                            <div class="col-sm-6"><strong>IBAN:</strong> XX89 1234 5678 9012</div>
                                            <div class="col-sm-6"><strong>SWIFT/BIC:</strong> NBTGXX22</div>
                                            <div class="col-12 mt-2"><strong class="fs-6 text-dark">{{ __('ui.amount_due') }}: {{ number_format($pendingPayment->amount, 2) }} {{ $pendingPayment->currency }}</strong></div>
                                        </div>
                                        
                                        @if(!$pendingPayment->receipt_path)
                                            <form action="{{ route('billing.upload_receipt') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-wrap align-items-end gap-3 mt-3 pt-3 border-top border-light">
                                                @csrf
                                                <div class="flex-grow-1" style="max-width: 400px;">
                                                    <label class="form-label fw-bold small mb-1">{{ __('ui.upload_bank_receipt') }} <span class="text-danger">*</span></label>
                                                    <input type="file" name="receipt" class="form-control" accept="image/*,.pdf" required>
                                                </div>
                                                <button type="submit" class="btn btn-warning text-dark fw-bold px-4 rounded-3"><i class="bi bi-upload me-2"></i> {{ __('ui.submit_receipt') }}</button>
                                            </form>
                                        @else
                                            <div class="alert alert-success border-0 mb-0 mt-3 d-flex align-items-center p-3 rounded-3 shadow-sm">
                                                <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
                                                <div>
                                                    <strong class="d-block text-success mb-1">{{ __('ui.receipt_submitted_title') }}</strong>
                                                    <span class="small text-muted">{{ __('ui.receipt_submitted_desc') }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Chari Payment Instructions --}}
                                    <div id="pending-panel-chari" class="p-4 border border-opacity-10 rounded-4 bg-white d-none">
                                        <h6 class="fw-bold mb-3"><i class="bi bi-phone text-info me-1"></i> {{ __('ui.chari_portal_details') }}</h6>
                                        <p class="small text-muted mb-3">{{ __('ui.chari_portal_desc') }}</p>
                                        
                                        <div class="mb-3">
                                            <button type="button" class="btn btn-info text-white fw-bold rounded-3 px-4 py-2" data-bs-toggle="modal" data-bs-target="#chariPaymentModal" onclick="document.getElementById('chariPaymentIframe').src = 'https://chari.pay.ma/0644031300/{{ $pendingPayment->amount }}';">
                                                <i class="bi bi-box-arrow-up-right me-2"></i> {{ __('ui.open_chari_portal') }} ({{ number_format($pendingPayment->amount, 2) }} MAD)
                                            </button>
                                        </div>

                                        @if(!$pendingPayment->receipt_path)
                                            <form action="{{ route('billing.upload_receipt') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-wrap align-items-end gap-3 mt-3 pt-3 border-top border-light">
                                                @csrf
                                                <div class="flex-grow-1" style="max-width: 400px;">
                                                    <label class="form-label fw-bold small mb-1">{{ __('ui.upload_screenshot') }} <span class="text-danger">*</span></label>
                                                    <input type="file" name="receipt" class="form-control" accept="image/*,.pdf" required>
                                                </div>
                                                <button type="submit" class="btn btn-info text-white fw-bold px-4 rounded-3"><i class="bi bi-upload me-2"></i> {{ __('ui.submit_screenshot') }}</button>
                                            </form>
                                        @else
                                            <div class="alert alert-success border-0 mb-0 mt-3 d-flex align-items-center p-3 rounded-3 shadow-sm">
                                                <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
                                                <div>
                                                    <strong class="d-block text-success mb-1">{{ __('ui.screenshot_submitted_title') }}</strong>
                                                    <span class="small text-muted">{{ __('ui.screenshot_submitted_desc') }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @elseif($company->plan)
                            <h3 class="fw-extrabold mb-1">{{ $company->plan->name }}</h3>
                            <div class="d-flex align-items-center gap-2 mt-2">
                                <span class="status-badge" style="background:#e0e7ff;color:#4338ca;">{{ __('ui.legacy_active') }}</span>
                            </div>
                        @else
                            <h3 class="fw-bold mb-1 text-muted">{{ __('ui.no_active_plan') }}</h3>
                            <p class="text-muted small mb-0">{{ __('ui.select_plan_desc') }}</p>
                        @endif
                    </div>
                    <div class="text-md-end">
                        <a href="#choose-plan" class="btn btn-gradient px-4 py-2.5 text-white fw-semibold rounded-3 shadow-sm">
                            <i class="bi bi-arrow-up-circle me-1"></i>
                            {{ $activeSubscription ? __('ui.change_plan') : __('ui.choose_plan') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 2. Pricing & Plan Subscription Forms ── --}}
        <div class="col-12" id="choose-plan">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <div>
                    <h4 class="fw-extrabold mb-1">{{ __('ui.available_plans') }}</h4>
                    <p class="text-muted small mb-0">{{ __('ui.choose_plan_for_organization') }}</p>
                </div>

                {{-- Interactive Switcher for Billing Cycles --}}
                <div class="billing-cycle-switch">
                    <button type="button" class="billing-cycle-btn active" id="btn-cycle-monthly" onclick="setBillingCycle('monthly')">{{ __('ui.monthly') }}</button>
                    <button type="button" class="billing-cycle-btn" id="btn-cycle-annual" onclick="setBillingCycle('annual')">
                        {{ __('ui.annual') }} 
                        <span class="badge bg-success-subtle text-success ms-1" style="font-size: 0.65rem;">{{ __('ui.save_more') }}</span>
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('billing.subscribe') }}" id="subscribeForm" enctype="multipart/form-data">
                @csrf
                
                {{-- Hidden input matching cycle --}}
                <input type="hidden" name="billing_cycle" id="billingCycleInput" value="monthly">

                {{-- Premium Plan Grid --}}
                <div class="row g-4 mb-5">
                    @foreach($plans as $plan)
                    <div class="col-md-4">
                        <label class="premium-plan-label">
                            <input type="radio" name="plan_id" value="{{ $plan->id }}" class="d-none plan-radio"
                                   data-name="{{ $plan->name }}"
                                   data-monthly="{{ $plan->price }}" data-annual="{{ $plan->annual_price }}"
                                   {{ ($activeSubscription && $activeSubscription->plan_id === $plan->id) ? 'checked' : '' }}>
                            
                            <div class="premium-plan-card">
                                @if($activeSubscription && $activeSubscription->plan_id === $plan->id)
                                    <span class="badge bg-success position-absolute top-0 end-0 m-3 px-3 py-1.5 rounded-pill">{{ __('ui.current') }}</span>
                                @endif
                                
                                <h5 class="fw-bold mb-1 text-slate-800">{{ $plan->name }}</h5>
                                <p class="text-muted small mb-4">{{ $plan->description }}</p>
                                
                                <div class="mb-4">
                                    <span class="plan-price display-5 fw-extrabold text-slate-900" id="price-val-{{ $plan->id }}">{{ number_format($plan->price, 2) }}</span>
                                    <span class="plan-period text-muted fs-6">/ {{ __('ui.mo') }}</span>
                                    
                                    <div class="text-muted small mt-2" id="annual-hint-{{ $plan->id }}">
                                        {{ __('ui.or') }} {{ number_format($plan->annual_price, 2) }} / {{ __('ui.yr') }}
                                    </div>
                                </div>
                                
                                <hr class="opacity-10 my-2">
                                
                                <ul class="list-unstyled mb-0 mt-3 small text-muted d-flex flex-column gap-3 flex-grow-1">
                                    <li class="d-flex align-items-center gap-2">
                                        <i class="bi bi-people text-success"></i>
                                        <span><strong>{{ $plan->limits['staff_limit'] ?? 0 }}</strong> {{ __('ui.staff_limit') }}</span>
                                    </li>
                                    <li class="d-flex align-items-center gap-2">
                                        <i class="bi bi-door-open text-success"></i>
                                        <span><strong>{{ $plan->limits['room_limit'] ?? 0 }}</strong> {{ __('ui.room_limit') }}</span>
                                    </li>
                                    <li class="d-flex align-items-center gap-2">
                                        <i class="bi bi-tv text-success"></i>
                                        <span><strong>{{ ($plan->limits['display_limit'] ?? 0) == -1 ? __('ui.unlimited') : ($plan->limits['display_limit'] ?? 0) }}</strong> {{ __('ui.display_limit') }}</span>
                                    </li>
                                    <li class="d-flex align-items-center gap-2">
                                        <i class="bi bi-ticket-detailed text-success"></i>
                                        <span><strong>{{ $plan->limits['ticket_limit_monthly'] ?? 0 }}</strong> {{ __('ui.ticket_limit_monthly') }}</span>
                                    </li>
                                    <li class="d-flex align-items-center gap-2">
                                        <i class="bi bi-person-vcard text-success"></i>
                                        <span><strong>{{ $plan->limits['customer_limit'] ?? 0 }}</strong> {{ __('ui.customer_limit') ?? 'Customer Limit' }}</span>
                                    </li>
                                </ul>
                            </div>
                        </label>
                    </div>
                    @endforeach
                </div>

                {{-- Payment Methods & Confirm Summary Section --}}
                <div class="row g-4 mb-5">
                    
                    {{-- Left side: billing details & methods --}}
                    <div class="col-lg-7">
                        <div class="premium-billing-card p-4 h-100">
                            <h5 class="fw-bold mb-4 d-flex align-items-center gap-2 text-slate-800">
                                <i class="bi bi-shield-lock text-primary"></i> 1. {{ __('ui.choose_payment_method') }}
                            </h5>
                            
                            <div class="row g-3 mb-4">
                                {{-- Virement --}}
                                <div class="col-md-6">
                                    <label class="premium-method-label w-100 h-100">
                                        <input type="radio" name="payment_method" value="virement" class="d-none payment-method-radio" checked>
                                        <div class="premium-method-card" id="card-virement" style="cursor:pointer;">
                                            <div class="d-flex align-items-center gap-3 mb-2">
                                                <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;flex-shrink:0">
                                                    <i class="bi bi-bank fs-5"></i>
                                                </div>
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-slate-800">{{ __('ui.bank_transfer') }}</h6>
                                                    <small class="text-muted">{{ __('ui.bank_transfer') }}</small>
                                                </div>
                                            </div>
                                            <p class="text-muted small mb-0">{{ __('ui.bank_transfer_desc') }}</p>
                                        </div>
                                    </label>
                                </div>
                                {{-- Chari --}}
                                <div class="col-md-6">
                                    <label class="premium-method-label w-100 h-100">
                                        <input type="radio" name="payment_method" value="chari_online" class="d-none payment-method-radio">
                                        <div class="premium-method-card" id="card-chari" style="cursor:pointer;">
                                            <div class="d-flex align-items-center gap-3 mb-2">
                                                <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;flex-shrink:0">
                                                    <i class="bi bi-phone fs-5"></i>
                                                </div>
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-slate-800">{{ __('ui.online_payment') }}</h6>
                                                    <small class="text-muted">{{ __('ui.chari_pay') }}</small>
                                                </div>
                                            </div>
                                            <p class="text-muted small mb-0">{{ __('ui.online_payment_desc') }}</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            {{-- Bank Transfer Instructions --}}
                            <div class="p-4 rounded-4 bg-light border border-opacity-10" id="bank-transfer-details">
                                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle-fill text-warning me-2"></i>{{ __('ui.bank_transfer_details') }}</h6>
                                <div class="row text-muted small g-2 mb-3">
                                    <div class="col-sm-6"><strong>{{ __('ui.bank_name') }}:</strong> Noubtigo Global Bank</div>
                                    <div class="col-sm-6"><strong>{{ __('ui.account_name') }}:</strong> Noubtigo Solutions</div>
                                    <div class="col-sm-6"><strong>IBAN:</strong> XX89 1234 5678 9012</div>
                                    <div class="col-sm-6"><strong>SWIFT/BIC:</strong> NBTGXX22</div>
                                </div>
                                <div class="small text-muted bg-white p-3 rounded-3 border-light">
                                    {{ __('ui.bank_transfer_instructions') }}
                                </div>
                            </div>

                            {{-- Chari Details Panel --}}
                            <div class="p-4 rounded-4 bg-light border border-opacity-10 d-none" id="online-payment-details">
                                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-phone-fill text-info me-2"></i>{{ __('ui.online_portal_title') }}</h6>
                                <p class="text-muted small mb-3">{{ __('ui.online_portal_desc') }}</p>
                                <button type="button" class="btn btn-info text-white fw-bold rounded-3 px-4 py-2.5" id="openChariPaymentBtn" data-bs-toggle="modal" data-bs-target="#chariPaymentModal">
                                    <i class="bi bi-box-arrow-up-right me-2"></i> {{ __('ui.open_chari_portal') }}
                                </button>
                                <p class="text-muted small mt-3 mb-0"><i class="bi bi-shield-check me-1"></i>{{ __('ui.secure_payment_powered') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Right side: coupon code & receipt uploads & summary --}}
                    <div class="col-lg-5">
                        <div class="premium-billing-card p-4 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <h5 class="fw-bold mb-4 d-flex align-items-center gap-2 text-slate-800">
                                    <i class="bi bi-gift text-primary"></i> 2. {{ __('ui.complete_order') }}
                                </h5>

                                {{-- Coupon Section --}}
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-slate-700 small mb-2">{{ __('ui.promo_code') }}</label>
                                    <div class="input-group">
                                        <input type="text" name="coupon_code" id="couponCodeInput" class="form-control rounded-start-3" placeholder="e.g. WELCOME10" value="{{ old('coupon_code', request('promo', request('promo_code', request('coupon', request('coupon_code', request('code')))))) }}">
                                        <button type="button" class="btn btn-outline-primary px-3 fw-semibold rounded-end-3" id="applyCouponBtn">{{ __('ui.apply_code') }}</button>
                                    </div>
                                    <div id="couponMessage" class="small fw-semibold mt-2"></div>
                                </div>

                                {{-- Receipt upload --}}
                                <div class="mb-4" id="receipt-upload-card">
                                    <label class="form-label fw-bold text-slate-700 small mb-2">{{ __('ui.upload_payment_receipt') }} <span class="text-danger">*</span></label>
                                    <input type="file" name="receipt" class="form-control rounded-3 @error('receipt') is-invalid @enderror" accept="image/*,.pdf" required>
                                    <small class="text-muted d-block mt-2" id="receipt-instructions">{{ __('ui.receipt_size_limit') }}</small>
                                    @error('receipt')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Final Summary & Action Button --}}
                            <div class="pt-4 border-top border-light mt-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted fw-semibold">{{ __('ui.total_amount_due') }}</span>
                                    <h3 class="fw-extrabold text-primary mb-0" id="totalAmountDisplay">0.00 <small class="text-muted fw-normal" style="font-size:0.75rem">MAD</small></h3>
                                </div>
                                <button type="button" class="btn btn-gradient w-100 py-3 text-white fw-bold rounded-4 shadow-sm" id="triggerConfirmBtn">
                                    <i class="bi bi-check-circle me-1"></i> {{ __('ui.request_subscription') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- ── 3. Payment History ── --}}
        <div class="col-12">
            <h5 class="fw-extrabold mb-3">{{ __('ui.payment_history') }}</h5>
            <div class="premium-billing-card overflow-hidden">
                @if($payments->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-receipt d-block mb-2 text-muted" style="font-size:2.5rem; opacity: 0.4;"></i>
                        <p class="text-muted mb-0 small">{{ __('ui.no_payments_yet') }}</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>{{ __('ui.date') }}</th>
                                    <th>{{ __('ui.plan') }}</th>
                                    <th>{{ __('ui.cycle') }}</th>
                                    <th>{{ __('ui.amount') }}</th>
                                    <th>{{ __('ui.method') }}</th>
                                    <th>{{ __('ui.status') }}</th>
                                    <th>{{ __('ui.invoice') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payments as $payment)
                                <tr>
                                    <td>{{ $payment->created_at->format('M d, Y') }}</td>
                                    <td><strong>{{ $payment->subscription->plan->name ?? '—' }}</strong></td>
                                    <td class="text-capitalize">{{ $payment->subscription ? $payment->subscription->billing_cycle : '—' }}</td>
                                    <td class="fw-semibold text-slate-800">{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</td>
                                    <td>
                                        @if($payment->payment_method === 'virement')
                                            <span class="badge bg-warning bg-opacity-10 text-warning px-2.5 py-1.5 rounded-pill"><i class="bi bi-bank me-1"></i>Virement</span>
                                        @elseif($payment->payment_method === 'chari_online')
                                            <span class="badge bg-info bg-opacity-10 text-info px-2.5 py-1.5 rounded-pill"><i class="bi bi-phone me-1"></i>Chari</span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-2.5 py-1.5 rounded-pill">{{ ucfirst($payment->payment_method) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $payment->status }}">
                                            {{ __('ui.payment_status_' . $payment->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($payment->invoice && $payment->status === 'paid')
                                        <a href="{{ route('billing.invoice.show', $payment->invoice) }}" class="btn btn-sm btn-outline-primary rounded-3 px-3 py-1 text-nowrap">
                                            <i class="bi bi-download me-1"></i>{{ $payment->invoice->invoice_number }}
                                        </a>
                                        @else
                                        <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($payments->hasPages())
                        <div class="p-3 border-top border-light d-flex justify-content-center">{{ $payments->links() }}</div>
                    @endif
                @endif
            </div>
        </div>

    </div>
</div>

{{-- Chari Payment Modal --}}
<div class="modal fade" id="chariPaymentModal" tabindex="-1" aria-labelledby="chariPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-info bg-opacity-10 px-4 py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px">
                        <i class="bi bi-phone"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="chariPaymentModalLabel">Chari Payment Portal</h5>
                        <small class="text-muted">Secure online payment</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="height: 600px;">
                <iframe id="chariPaymentIframe" src="about:blank" width="100%" height="100%" style="border:none;" loading="lazy"></iframe>
            </div>
            <div class="modal-footer border-0 bg-light px-4 py-3">
                <div class="d-flex align-items-center gap-2 me-auto">
                    <i class="bi bi-shield-check text-success"></i>
                    <small class="text-muted">After payment, take a screenshot and upload it as your receipt.</small>
                </div>
                <button type="button" class="btn btn-dark rounded-3 px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Hidden button to trigger modal --}}
<button type="button" id="hiddenModalTrigger" class="d-none" data-bs-toggle="modal" data-bs-target="#confirmSubscriptionModal"></button>

{{-- Subscription Confirmation Modal --}}
<div class="modal fade" id="confirmSubscriptionModal" tabindex="-1" aria-labelledby="confirmSubscriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-primary bg-opacity-10 px-4 py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px">
                        <i class="bi bi-cart-check"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="confirmSubscriptionModalLabel">{{ __('ui.confirm_sub_request') }}</h5>
                        <small class="text-muted">{{ __('ui.review_selection') }}</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <div class="mb-3 border-bottom pb-2">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">{{ __('ui.selected_plan') }}:</span>
                        <span class="fw-bold" id="confirmPlanName">—</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">{{ __('ui.billing_cycle') }}:</span>
                        <span class="fw-bold text-capitalize" id="confirmBillingCycle">—</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2" id="confirmPaymentMethodRow">
                        <span class="text-muted">{{ __('ui.payment_method') }}:</span>
                        <span class="fw-bold" id="confirmPaymentMethod">—</span>
                    </div>
                </div>

                {{-- Promotion Breakdown in Modal --}}
                <div class="mb-3 p-3 rounded-3 bg-success bg-opacity-10 border border-success border-opacity-25 d-none" id="confirmPromoBox">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-gift-fill text-success"></i>
                        <strong class="text-success small" id="confirmPromoTitle">Promotional Campaign Applied</strong>
                    </div>
                    <ul class="list-unstyled mb-0 small text-dark ps-1" id="confirmPromoList"></ul>
                </div>

                <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded-3">
                    <span class="text-dark fw-semibold">{{ __('ui.total') }}:</span>
                    <span class="fw-bold text-primary fs-5" id="confirmTotalPrice">0.00 MAD</span>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 py-3">
                <button type="button" class="btn btn-outline-secondary rounded-3 px-4" data-bs-dismiss="modal">{{ __('ui.cancel') }}</button>
                <button type="button" class="btn btn-primary rounded-3 px-4 text-white fw-bold" id="submitSubscriptionBtn">{{ __('ui.confirm_submit') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const planRadios = document.querySelectorAll('.plan-radio');
    const methodRadios = document.querySelectorAll('.payment-method-radio');
    const totalDisplay = document.getElementById('totalAmountDisplay');
    const bankDetails = document.getElementById('bank-transfer-details');
    const onlineDetails = document.getElementById('online-payment-details');
    const chariBtn = document.getElementById('openChariPaymentBtn');
    const chariIframe = document.getElementById('chariPaymentIframe');

    let appliedCoupon = null;
    let currentPrice = 0;

    // Billing cycle interactive buttons
    window.setBillingCycle = function(cycle) {
        document.getElementById('billingCycleInput').value = cycle;
        
        document.getElementById('btn-cycle-monthly').classList.toggle('active', cycle === 'monthly');
        document.getElementById('btn-cycle-annual').classList.toggle('active', cycle === 'annual');

        planRadios.forEach(radio => {
            const planId = radio.value;
            const priceVal = cycle === 'annual' ? radio.dataset.annual : radio.dataset.monthly;
            const periodVal = cycle === 'annual' ? "{{ __('ui.yr') }}" : "{{ __('ui.mo') }}";
            
            const priceEl = document.getElementById('price-val-' + planId);
            if (priceEl) priceEl.textContent = parseFloat(priceVal).toFixed(2);
            
            const parent = priceEl?.closest('.premium-plan-card');
            const periodEl = parent?.querySelector('.plan-period');
            if (periodEl) periodEl.textContent = '/ ' + periodVal;
        });

        if (document.getElementById('couponCodeInput')?.value.trim()) {
            checkCoupon();
        } else {
            updatePrice();
        }
    };

    function updatePrice() {
        const selectedPlan = document.querySelector('.plan-radio:checked');
        if (!selectedPlan) { totalDisplay.innerHTML = '0.00 <small class="text-muted fw-normal" style="font-size:0.7rem">MAD</small>'; return; }
        
        const cycle = document.getElementById('billingCycleInput').value;
        const originalPrice = cycle === 'annual' 
            ? parseFloat(selectedPlan.dataset.annual) 
            : parseFloat(selectedPlan.dataset.monthly);
        
        let price = originalPrice;
        if (appliedCoupon && appliedCoupon.valid) {
            price = parseFloat(appliedCoupon.final_amount);
        }
        currentPrice = price;
        
        if (appliedCoupon && appliedCoupon.valid && parseFloat(appliedCoupon.discount_amount) > 0) {
            totalDisplay.innerHTML = '<span class="text-decoration-line-through text-muted me-2" style="font-size:1.1rem">' + originalPrice.toFixed(2) + '</span> ' + price.toFixed(2) + ' <small class="text-muted fw-normal" style="font-size:0.75rem">MAD</small>';
        } else {
            totalDisplay.innerHTML = price.toFixed(2) + ' <small class="text-muted fw-normal" style="font-size:0.75rem">MAD</small>';
        }

        if (chariBtn) {
            chariBtn.setAttribute('data-chari-price', price.toFixed(2));
        }

        const receiptInput = document.querySelector('input[name="receipt"]');
        const receiptCard = document.getElementById('receipt-upload-card');
        const triggerConfirmBtn = document.getElementById('triggerConfirmBtn');
        const isExtension = (appliedCoupon && appliedCoupon.preview && appliedCoupon.preview.is_extension);
        const isFree = (price <= 0.00 || (appliedCoupon && appliedCoupon.preview && appliedCoupon.preview.is_free_period));

        if (isFree || isExtension) {
            if (receiptInput) {
                receiptInput.removeAttribute('required');
            }
            if (receiptCard) {
                receiptCard.style.opacity = '0.4';
                const fileInp = receiptCard.querySelector('input');
                if (fileInp) fileInp.disabled = true;
                const star = receiptCard.querySelector('.text-danger');
                if (star) star.style.display = 'none';
            }
            if (triggerConfirmBtn) {
                triggerConfirmBtn.innerHTML = isExtension 
                    ? '<i class="bi bi-clock-history me-1"></i> Claim Subscription Extension' 
                    : '<i class="bi bi-gift me-1"></i> Claim Free Subscription';
            }
        } else {
            if (receiptInput) {
                receiptInput.setAttribute('required', 'required');
            }
            if (receiptCard) {
                receiptCard.style.opacity = '1';
                const fileInp = receiptCard.querySelector('input');
                if (fileInp) fileInp.disabled = false;
                const star = receiptCard.querySelector('.text-danger');
                if (star) star.style.display = '';
            }
            if (triggerConfirmBtn) {
                triggerConfirmBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> {{ __("ui.request_subscription") }}';
            }
        }
    }

    // Toggle Payment Method layouts
    methodRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.premium-method-card').forEach(c => c.style.borderColor = '');
            
            if (radio.value === 'virement') {
                bankDetails.classList.remove('d-none');
                onlineDetails.classList.add('d-none');
            } else {
                bankDetails.classList.add('d-none');
                onlineDetails.classList.remove('d-none');
            }
            updatePrice();
        });
    });

    // Check Coupon AJAX call
    const applyBtn = document.getElementById('applyCouponBtn');
    const couponInput = document.getElementById('couponCodeInput');
    const couponMsg = document.getElementById('couponMessage');

    function checkCoupon() {
        const code = couponInput.value.trim();
        const selectedPlan = document.querySelector('.plan-radio:checked');
        
        if (!code) {
            appliedCoupon = null;
            couponMsg.innerHTML = '';
            updatePrice();
            return;
        }

        if (!selectedPlan) {
            couponMsg.className = 'small text-danger fw-semibold mt-2';
            couponMsg.innerHTML = 'Please select a subscription plan first.';
            return;
        }

        const cycle = document.getElementById('billingCycleInput').value;

        couponMsg.className = 'small text-muted mt-2';
        couponMsg.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Validating campaign...';

        fetch('{{ route("billing.coupon.validate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                coupon_code: code,
                plan_id: selectedPlan.value,
                billing_cycle: cycle
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.valid) {
                appliedCoupon = {
                    valid: true,
                    code: code,
                    final_amount: data.final_amount,
                    discount_amount: data.discount_amount,
                    coupon_type: data.coupon_type,
                    promotion_type: data.promotion_type,
                    preview: data.preview
                };

                let previewHtml = '<div class="alert alert-success border-0 p-3 rounded-3 mt-2 shadow-sm">';
                previewHtml += '<div class="d-flex align-items-center gap-2 fw-bold text-success mb-1">';
                previewHtml += '<i class="bi bi-check-circle-fill"></i> Promotion Applied: ' + (data.preview?.promotion_label || data.name || code);
                previewHtml += '</div>';

                if (data.preview && data.preview.summary_lines && data.preview.summary_lines.length) {
                    previewHtml += '<ul class="mb-0 ps-3 small text-dark mt-1">';
                    data.preview.summary_lines.forEach(line => {
                        previewHtml += '<li>' + line + '</li>';
                    });
                    previewHtml += '</ul>';
                }
                previewHtml += '</div>';

                couponMsg.className = 'mt-2';
                couponMsg.innerHTML = previewHtml;
            } else {
                appliedCoupon = null;
                couponMsg.className = 'small text-danger fw-semibold mt-2';
                couponMsg.innerHTML = '<i class="bi bi-exclamation-circle-fill me-1"></i> ' + data.message;
            }
            updatePrice();
        })
        .catch(err => {
            appliedCoupon = null;
            couponMsg.className = 'small text-danger fw-semibold mt-2';
            couponMsg.innerHTML = 'Error validating coupon code.';
            updatePrice();
        });
    }

    if (applyBtn) {
        applyBtn.addEventListener('click', checkCoupon);
    }

    if (couponInput && couponInput.value.trim()) {
        const selectedPlan = document.querySelector('.plan-radio:checked');
        if (selectedPlan) {
            checkCoupon();
        }
    }

    planRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            if (couponInput && couponInput.value.trim()) {
                checkCoupon();
            } else {
                updatePrice();
            }
        });
    });

    // Handle form confirmation summary
    const triggerConfirm = document.getElementById('triggerConfirmBtn');
    const modalTrigger = document.getElementById('hiddenModalTrigger');
    const submitBtn = document.getElementById('submitSubscriptionBtn');
    
    if (triggerConfirm) {
        triggerConfirm.addEventListener('click', function() {
            const selectedPlan = document.querySelector('.plan-radio:checked');
            if (!selectedPlan) {
                alert('Please select a subscription plan first.');
                return;
            }

            const methodRadio = document.querySelector('.payment-method-radio:checked');
            const receiptInput = document.querySelector('input[name="receipt"]');
            const isExtension = (appliedCoupon && appliedCoupon.preview && appliedCoupon.preview.is_extension);
            const isFree = (currentPrice <= 0.00 || (appliedCoupon && appliedCoupon.preview && appliedCoupon.preview.is_free_period));
            
            if (!isFree && !isExtension && receiptInput && receiptInput.required && !receiptInput.files.length) {
                alert('Please upload your bank payment receipt first.');
                receiptInput.classList.add('is-invalid');
                return;
            }

            // Populate Modal Summary
            document.getElementById('confirmPlanName').textContent = selectedPlan.dataset.name;
            document.getElementById('confirmBillingCycle').textContent = document.getElementById('billingCycleInput').value;
            
            const methodRow = document.getElementById('confirmPaymentMethodRow');
            if (isFree || isExtension) {
                if (methodRow) methodRow.style.display = 'none';
            } else {
                if (methodRow) methodRow.style.display = 'flex';
                document.getElementById('confirmPaymentMethod').textContent = methodRadio?.value === 'virement' ? 'Bank Transfer (Virement)' : 'Chari Online Payment';
            }

            // Promo Box in Modal
            const promoBox = document.getElementById('confirmPromoBox');
            const promoList = document.getElementById('confirmPromoList');
            const promoTitle = document.getElementById('confirmPromoTitle');

            if (appliedCoupon && appliedCoupon.valid && appliedCoupon.preview) {
                promoBox.classList.remove('d-none');
                promoTitle.textContent = appliedCoupon.preview.promotion_label || 'Promotion Active';
                promoList.innerHTML = '';
                if (appliedCoupon.preview.summary_lines) {
                    appliedCoupon.preview.summary_lines.forEach(line => {
                        promoList.innerHTML += '<li>• ' + line + '</li>';
                    });
                }
            } else {
                promoBox.classList.add('d-none');
            }

            document.getElementById('confirmTotalPrice').textContent = currentPrice.toFixed(2) + ' MAD';

            modalTrigger.click();
        });
    }

    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            document.getElementById('subscribeForm').submit();
        });
    }

    // Handle Chari live iframe loading
    if (chariBtn) {
        chariBtn.addEventListener('click', function() {
            const price = chariBtn.getAttribute('data-chari-price') || '0.00';
            chariIframe.src = 'https://chari.pay.ma/0644031300/' + price;
        });
    }

    // Toggle logic for pending payments instructions
    const pendVirement = document.getElementById('pending-toggle-virement');
    const pendChari = document.getElementById('pending-toggle-chari');
    const panelVirement = document.getElementById('pending-panel-virement');
    const panelChari = document.getElementById('pending-panel-chari');

    if (pendVirement && pendChari) {
        pendVirement.addEventListener('click', function() {
            pendVirement.style.borderColor = 'var(--primary-color)';
            pendChari.style.borderColor = '#e2e8f0';
            panelVirement.classList.remove('d-none');
            panelChari.classList.add('d-none');
        });
        pendChari.addEventListener('click', function() {
            pendChari.style.borderColor = 'var(--primary-color)';
            pendVirement.style.borderColor = '#e2e8f0';
            panelChari.classList.remove('d-none');
            panelVirement.classList.add('d-none');
        });
    }

    // Initial price computation
    updatePrice();
})();
</script>
@endpush
