@extends('layouts.dashboard')
@section('title', 'Coupon: ' . $coupon->code)
@section('header_title', 'Campaign Details: ' . $coupon->code)
@section('breadcrumb_parent', 'Coupons')
@section('breadcrumb_parent_url', route('admin.coupons.index'))

@section('content')
{{-- 1. Analytics Cards (Section 18) --}}
<div class="row g-3 mb-4 slide-up stagger-2">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-3">
                <div class="text-muted small mb-1"><i class="bi bi-gift me-1"></i>Total Redemptions</div>
                <h4 class="fw-bold mb-0 text-slate-800">{{ $analytics['total_redemptions'] }}</h4>
                <div class="text-muted small mt-1">
                    <span class="text-success fw-semibold">{{ $analytics['active_promotions'] }} active</span> • {{ $analytics['expired_promotions'] }} finished
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-3">
                <div class="text-muted small mb-1"><i class="bi bi-graph-up me-1"></i>Gross Revenue</div>
                <h4 class="fw-bold mb-0 text-slate-800">{{ number_format($analytics['revenue_before_discounts'], 2) }} <small class="fs-6 fw-normal text-muted">DH</small></h4>
                <div class="text-muted small mt-1">Before promotional discounts</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-3">
                <div class="text-muted small mb-1"><i class="bi bi-percent me-1"></i>Discount Given</div>
                <h4 class="fw-bold mb-0 text-danger">{{ number_format($analytics['discount_amount'], 2) }} <small class="fs-6 fw-normal text-muted">DH</small></h4>
                <div class="text-muted small mt-1">Total savings passed to customers</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-3">
                <div class="text-muted small mb-1"><i class="bi bi-cash-stack me-1"></i>Net Revenue</div>
                <h4 class="fw-bold mb-0 text-success">{{ number_format($analytics['revenue_after_discounts'], 2) }} <small class="fs-6 fw-normal text-muted">DH</small></h4>
                <div class="text-muted small mt-1">Realized revenue after promos</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Left: Overview & Active Redemptions & Usages --}}
    <div class="col-lg-8">
        {{-- Promotion Overview Card --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 slide-up stagger-3">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-info-circle text-primary me-2"></i>Promotion Specifications</h6>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.coupons.edit', $coupon->uuid) }}" class="btn btn-sm btn-primary text-white rounded-3 px-3 fw-semibold">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    <form method="POST" action="{{ route('admin.coupons.duplicate', $coupon->uuid) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-info rounded-3 px-3 fw-semibold"><i class="bi bi-copy me-1"></i> Duplicate</button>
                    </form>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-sm-6 col-md-4">
                        <span class="text-muted small d-block">Coupon Code</span>
                        <span class="badge bg-dark bg-opacity-10 text-dark fw-bold fs-6 px-2.5 py-1" style="font-family:monospace">{{ $coupon->code }}</span>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <span class="text-muted small d-block">Promotion Offer</span>
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold fs-6 px-2.5 py-1">{{ $coupon->getPromotionLabel() }}</span>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <span class="text-muted small d-block">Promotion Duration</span>
                        <strong class="text-slate-800">{{ $coupon->getDurationDescription() }}</strong>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <span class="text-muted small d-block">Applicable Plans</span>
                        <span class="text-slate-700">{{ $coupon->getPlansSummary() }}</span>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <span class="text-muted small d-block">Customer Eligibility</span>
                        <span class="text-capitalize text-slate-700">{{ str_replace('_', ' ', $coupon->customer_type) }}</span>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <span class="text-muted small d-block">Redemption Validity</span>
                        <span class="text-slate-700">{{ $coupon->getValiditySummary() }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Redemptions Table --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 slide-up stagger-4">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-people text-primary me-2"></i>Promotional Redemptions & Subscriptions</h6>
                <span class="badge bg-light text-muted">{{ $redemptions->total() }} total</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.875rem">
                    <thead class="bg-light">
                        <tr>
                            <th>Company / Tenant</th>
                            <th>Plan</th>
                            <th>Snapshot Offer</th>
                            <th>Cycles / Duration</th>
                            <th>Expires / Renews</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($redemptions as $redemption)
                        <tr>
                            <td>
                                <strong>{{ $redemption->company->name ?? 'Unknown Company' }}</strong>
                                <small class="text-muted d-block">{{ $redemption->redeemed_at->format('M d, Y') }}</small>
                            </td>
                            <td>{{ $redemption->subscription?->plan?->name ?? '—' }}</td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    {{ $redemption->promotion_type ? ucwords(str_replace('_', ' ', $redemption->promotion_type)) : 'Discount' }}
                                </span>
                            </td>
                            <td>
                                @if($redemption->max_discounted_cycles)
                                    {{ $redemption->billing_cycles_discounted }} / {{ $redemption->max_discounted_cycles }} cycles
                                @elseif($redemption->duration_value)
                                    {{ $redemption->duration_value }} {{ $redemption->duration_unit ?? 'months' }}
                                @else
                                    Standard
                                @endif
                            </td>
                            <td>
                                @if($redemption->free_until_date)
                                    {{ $redemption->free_until_date->format('M d, Y') }}
                                @elseif($redemption->ends_at)
                                    {{ $redemption->ends_at->format('M d, Y') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $redemption->status === 'active' ? 'success' : 'secondary' }} rounded-pill">
                                    {{ ucfirst($redemption->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted small">No active redemptions yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($redemptions->hasPages())
                <div class="p-3 border-top bg-light">{{ $redemptions->links() }}</div>
            @endif
        </div>

        {{-- Invoice Usages History --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 slide-up stagger-5">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-receipt text-primary me-2"></i>Invoice Charges & Usages Log</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.875rem">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>Tenant</th>
                            <th>Invoice / Payment</th>
                            <th>Discount Applied</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usages as $usage)
                        <tr>
                            <td>{{ $usage->created_at->format('M d, Y H:i') }}</td>
                            <td>{{ $usage->company->name ?? '—' }}</td>
                            <td>
                                @if($usage->invoice)
                                    <span class="badge bg-light text-dark border">{{ $usage->invoice->invoice_number }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="fw-bold text-success">-{{ number_format($usage->discount_amount, 2) }} DH</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted small">No usage transactions logged yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($usages->hasPages())
                <div class="p-3 border-top bg-light">{{ $usages->links() }}</div>
            @endif
        </div>
    </div>

    {{-- Right: Validation History & Audit Logs --}}
    <div class="col-lg-4">
        {{-- Validation Attempts --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 slide-up stagger-3">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-shield-check text-primary me-2"></i>Validation Activity</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush small">
                    @forelse($validationAttempts as $attempt)
                    <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">{{ $attempt->company->name ?? 'Guest / Checkout' }}</div>
                            <small class="text-muted">{{ $attempt->created_at->diffForHumans() }}</small>
                            @if($attempt->failure_reason)
                                <div class="text-danger small mt-0.5">{{ $attempt->failure_reason }}</div>
                            @endif
                        </div>
                        <span class="badge bg-{{ $attempt->status === 'success' ? 'success' : 'danger' }} rounded-pill">
                            {{ ucfirst($attempt->status) }}
                        </span>
                    </li>
                    @empty
                    <li class="list-group-item text-center py-4 text-muted">No validation attempts recorded.</li>
                    @endforelse
                </ul>
            </div>
            @if($validationAttempts->hasPages())
                <div class="p-2 border-top">{{ $validationAttempts->links() }}</div>
            @endif
        </div>

        {{-- Admin Modification Audit Logs --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 slide-up stagger-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-clock-history text-primary me-2"></i>Admin Audit Trail</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush small">
                    @forelse($auditLogs as $log)
                    <li class="list-group-item px-4 py-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="text-capitalize">{{ $log->action }}</strong>
                            <span class="text-muted">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        <span class="text-muted">By {{ $log->user->name ?? 'System' }}</span>
                    </li>
                    @empty
                    <li class="list-group-item text-center py-4 text-muted">No audit logs recorded.</li>
                    @endforelse
                </ul>
            </div>
            @if($auditLogs->hasPages())
                <div class="p-2 border-top">{{ $auditLogs->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
