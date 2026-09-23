@extends('layouts.dashboard')
@section('title', 'Coupon Management')
@section('header_title', 'Coupons & Promotions')
@section('breadcrumb_parent', 'Billing & Payments')
@section('breadcrumb_parent_url', route('admin.payments.index'))

@section('content')
<div class="row g-3 mb-4 slide-up stagger-2">
    {{-- Stats Cards --}}
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:linear-gradient(135deg,#6366f1,#818cf8)">
                        <i class="bi bi-tags-fill text-white fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Coupons</div>
                        <h4 class="fw-bold mb-0">{{ $stats['total'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:linear-gradient(135deg,#22c55e,#16a34a)">
                        <i class="bi bi-check-circle-fill text-white fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Active Coupons</div>
                        <h4 class="fw-bold mb-0">{{ $stats['active'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:linear-gradient(135deg,#f59e0b,#d97706)">
                        <i class="bi bi-gift-fill text-white fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Redemptions</div>
                        <h4 class="fw-bold mb-0">{{ $stats['total_redemptions'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:linear-gradient(135deg,#10b981,#059669)">
                        <i class="bi bi-cash-stack text-white fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Discounts</div>
                        <h4 class="fw-bold mb-0 text-success">{{ number_format($stats['total_discounts'], 2) }} <small class="fs-6 fw-normal text-muted">DH</small></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Expiring Soon Warning --}}
@if($stats['expiring_soon']->count() > 0)
<div class="alert alert-warning border-0 rounded-4 d-flex align-items-center gap-3 mb-4 slide-up stagger-3" style="background:rgba(245,158,11,0.08)">
    <i class="bi bi-exclamation-triangle-fill text-warning fs-4"></i>
    <div>
        <strong>{{ $stats['expiring_soon']->count() }} coupon(s) expiring within 7 days:</strong>
        @foreach($stats['expiring_soon'] as $ec)
            <span class="badge bg-warning text-dark ms-1">{{ $ec->code }}</span>
        @endforeach
    </div>
</div>
@endif

{{-- Actions Bar --}}
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 slide-up stagger-4">
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.coupons.create') }}" class="btn btn-sm text-white fw-semibold rounded-3 px-3" style="background:var(--primary-gradient)">
            <i class="bi bi-plus-lg me-1"></i> Create Coupon
        </a>
        <a href="{{ route('admin.coupons.export-csv') }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3">
            <i class="bi bi-filetype-csv me-1"></i> Export CSV
        </a>
        <a href="{{ route('admin.coupons.export-pdf') }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3" target="_blank">
            <i class="bi bi-file-earmark-pdf me-1"></i> Report
        </a>
    </div>
    {{-- Search and Filters --}}
    <form method="GET" action="{{ route('admin.coupons.index') }}" class="d-flex gap-2 flex-wrap">
        <input type="text" name="search" class="form-control form-control-sm rounded-3" style="max-width:200px" placeholder="Search code or name..." value="{{ request('search') }}">
        <select name="status" class="form-select form-select-sm rounded-3" style="max-width:140px">
            <option value="">All Status</option>
            @foreach($statuses as $s)
                <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
            @endforeach
        </select>
        <select name="type" class="form-select form-select-sm rounded-3" style="max-width:180px">
            <option value="">All Types</option>
            @foreach($types as $t)
                <option value="{{ $t->value }}" {{ request('type') === $t->value ? 'selected' : '' }}>{{ $t->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3"><i class="bi bi-search"></i></button>
    </form>
</div>

{{-- Bulk Action Form --}}
<form method="POST" action="{{ route('admin.coupons.bulk-actions') }}" id="bulkForm">
    @csrf
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden slide-up stagger-5">
        {{-- Bulk Action Bar --}}
        <div class="card-header bg-white border-bottom d-flex align-items-center gap-2 py-2 px-3" id="bulkBar" style="display:none!important">
            <span class="text-muted small" id="bulkCount">0 selected</span>
            <select name="bulk_action" class="form-select form-select-sm rounded-3" style="max-width:140px">
                <option value="enable">Enable</option>
                <option value="disable">Disable</option>
                <option value="archive">Archive</option>
                <option value="delete">Delete</option>
            </select>
            <button type="submit" class="btn btn-sm btn-warning rounded-3 px-3 fw-semibold" onclick="return confirm('Apply bulk action to selected coupons?')">Apply</button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:0.875rem">
                <thead class="bg-light text-muted">
                    <tr>
                        <th style="width:30px"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                        <th>Coupon</th>
                        <th>Promotion</th>
                        <th>Duration</th>
                        <th>Plans</th>
                        <th>Usage</th>
                        <th>Validity</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                    @php
                        $promoType = $coupon->getPromotionTypeString();
                        $badgeColor = match($promoType) {
                            'percentage_discount' => 'bg-primary bg-opacity-10 text-primary',
                            'fixed_discount' => 'bg-info bg-opacity-10 text-info',
                            'free_period' => 'bg-success bg-opacity-10 text-success',
                            'extended_subscription' => 'bg-warning bg-opacity-10 text-warning',
                            'free_until_date' => 'bg-purple bg-opacity-10 text-purple',
                            'lifetime' => 'bg-danger bg-opacity-10 text-danger',
                            default => 'bg-secondary bg-opacity-10 text-secondary',
                        };
                    @endphp
                    <tr>
                        <td><input type="checkbox" name="coupon_ids[]" value="{{ $coupon->id }}" class="form-check-input bulk-check"></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-dark bg-opacity-10 text-dark fw-bold px-2 py-1.5 fs-7" style="font-family:monospace;letter-spacing:1px">{{ $coupon->code }}</span>
                                @if($coupon->is_hidden)
                                    <i class="bi bi-eye-slash-fill text-muted" title="Hidden coupon"></i>
                                @endif
                            </div>
                            <small class="text-muted d-block mt-0.5">{{ $coupon->name }}</small>
                        </td>
                        <td>
                            <span class="badge {{ $badgeColor }} fw-bold px-2.5 py-1.5 rounded-pill">
                                {{ $coupon->getPromotionLabel() }}
                            </span>
                        </td>
                        <td>
                            <span class="text-dark fw-semibold">{{ $coupon->getDurationDescription() }}</span>
                        </td>
                        <td>
                            <span class="text-muted small">{{ $coupon->getPlansSummary() }}</span>
                        </td>
                        <td>
                            <span class="fw-bold text-dark">{{ $coupon->current_uses }}</span>
                            <span class="text-muted small">/ {{ $coupon->is_unlimited ? 'Unlimited' : ($coupon->max_total_uses ?? 'Unlimited') }}</span>
                        </td>
                        <td>
                            @if($coupon->expires_at)
                                @php $expiry = \Carbon\Carbon::parse($coupon->expires_at); @endphp
                                @if($expiry->isPast())
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1"><i class="bi bi-x-circle me-1"></i>{{ $coupon->getValiditySummary() }}</span>
                                @elseif($expiry->diffInDays(now()) <= 7)
                                    <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1"><i class="bi bi-clock me-1"></i>{{ $coupon->getValiditySummary() }}</span>
                                @else
                                    <span class="text-muted small">{{ $coupon->getValiditySummary() }}</span>
                                @endif
                            @else
                                <span class="text-success small fw-semibold"><i class="bi bi-infinity me-1"></i>Permanent</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusBadge = match($coupon->status->value) {
                                    'active' => 'bg-success',
                                    'disabled' => 'bg-secondary',
                                    'archived' => 'bg-dark bg-opacity-50',
                                    default => 'bg-light text-dark',
                                };
                            @endphp
                            <span class="badge {{ $statusBadge }} rounded-pill">{{ $coupon->status->label() }}</span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('admin.coupons.show', $coupon->uuid) }}" class="btn btn-sm btn-outline-primary rounded-2 px-2 py-1" title="View Details & Analytics"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('admin.coupons.edit', $coupon->uuid) }}" class="btn btn-sm btn-outline-secondary rounded-2 px-2 py-1" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.coupons.duplicate', $coupon->uuid) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-info rounded-2 px-2 py-1" title="Duplicate"><i class="bi bi-copy"></i></button>
                                </form>
                                <form method="POST" action="{{ route('admin.coupons.destroy', $coupon->uuid) }}" class="d-inline" onsubmit="return confirm('Archive/delete coupon {{ $coupon->code }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-2 px-2 py-1" title="Delete"><i class="bi bi-trash3"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="bi bi-tags d-block mb-2 text-muted" style="font-size:2rem;opacity:.3"></i>
                            <p class="text-muted mb-0 small">No coupons or promotional campaigns found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $coupons->withQueryString()->links() }}</div>
</form>

{{-- Top Coupons --}}
@if($stats['top_coupons']->count() > 0)
<div class="card border-0 shadow-sm rounded-4 mt-4 slide-up stagger-6">
    <div class="card-header bg-white border-bottom py-3 px-4">
        <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Top Performing Coupons</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" style="font-size:0.85rem">
                <thead class="bg-light">
                    <tr><th>Code</th><th>Name</th><th>Promotion</th><th>Duration</th><th>Total Uses</th></tr>
                </thead>
                <tbody>
                    @foreach($stats['top_coupons'] as $tc)
                    <tr>
                        <td><span class="badge bg-dark bg-opacity-10 text-dark fw-bold px-2 py-1" style="font-family:monospace">{{ $tc->code }}</span></td>
                        <td>{{ $tc->name }}</td>
                        <td><span class="badge bg-primary bg-opacity-10 text-primary">{{ $tc->getPromotionLabel() }}</span></td>
                        <td>{{ $tc->getDurationDescription() }}</td>
                        <td><span class="fw-bold">{{ $tc->usages_count }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
(function() {
    const selectAll = document.getElementById('selectAll');
    const checks = document.querySelectorAll('.bulk-check');
    const bulkBar = document.getElementById('bulkBar');
    const bulkCount = document.getElementById('bulkCount');

    function updateBulkBar() {
        const selected = document.querySelectorAll('.bulk-check:checked').length;
        if (selected > 0) {
            bulkBar.style.display = 'flex!important';
            bulkBar.removeAttribute('style');
            bulkBar.style.display = 'flex';
        } else {
            bulkBar.style.display = 'none';
        }
        bulkCount.textContent = selected + ' selected';
    }

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            checks.forEach(c => c.checked = selectAll.checked);
            updateBulkBar();
        });
    }
    checks.forEach(c => c.addEventListener('change', updateBulkBar));
})();
</script>
@endpush
