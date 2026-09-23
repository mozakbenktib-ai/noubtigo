@extends('layouts.dashboard')

@section('title', 'Noubtigo | Payments')
@section('header_title', __('ui.payment_management'))
@section('header_subtitle', __('ui.payment_management_subtitle'))

@push('styles')
<style>
.payment-table-wrapper {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    border: 1px solid rgba(0,0,0,0.05);
    overflow: hidden;
}
.payment-table { width: 100%; border-collapse: collapse; }
.payment-table thead th {
    background: #f8fafc;
    padding: 0.85rem 1.25rem;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 700;
    color: #94a3b8;
    border-bottom: 1px solid #e2e8f0;
    white-space: nowrap;
}
.payment-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background 0.15s; }
.payment-table tbody tr:hover { background: rgba(34,197,94,0.02); }
.payment-table tbody tr:last-child { border-bottom: none; }
.payment-table tbody td { padding: 0.75rem 1.25rem; font-size: 0.85rem; color: #334155; vertical-align: middle; }
.status-badge { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.25rem 0.65rem; border-radius: 2rem; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; }
.status-paid { background: #dcfce7; color: #16a34a; }
.status-pending { background: #fef9c3; color: #854d0e; }
.status-failed { background: #fef2f2; color: #dc2626; }
.status-processing { background: #dbeafe; color: #1d4ed8; }
.status-cancelled { background: #f1f5f9; color: #64748b; }
.status-refunded { background: #fdf4ff; color: #7e22ce; }
[data-bs-theme="dark"] .payment-table-wrapper { background: #1e293b; border-color: rgba(255,255,255,0.05); }
[data-bs-theme="dark"] .payment-table thead th { background: #0f172a; color: #64748b; }
[data-bs-theme="dark"] .payment-table tbody td { color: #e2e8f0; }
[data-bs-theme="dark"] .payment-table tbody tr { border-color: rgba(255,255,255,0.03); }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        @foreach([
            ['label' => __('ui.total'), 'value' => $stats['total'], 'icon' => 'bi-credit-card-2-front', 'color' => '#6366f1'],
            ['label' => __('ui.pending'), 'value' => $stats['pending'], 'icon' => 'bi-hourglass-split', 'color' => '#f59e0b'],
            ['label' => __('ui.paid'), 'value' => $stats['paid'], 'icon' => 'bi-check-circle-fill', 'color' => '#22c55e'],
            ['label' => __('ui.failed'), 'value' => $stats['failed'], 'icon' => 'bi-x-circle-fill', 'color' => '#ef4444'],
        ] as $stat)
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:42px;height:42px;background:{{ $stat['color'] }}20">
                        <i class="bi {{ $stat['icon'] }}" style="color:{{ $stat['color'] }};font-size:1.2rem"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.5px">{{ $stat['label'] }}</p>
                        <h4 class="fw-bold mb-0">{{ $stat['value'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <form class="d-flex flex-wrap gap-2 mb-4" method="GET" action="{{ route('admin.payments.index') }}">
        <input type="text" class="form-control form-control-sm rounded-3" name="search" value="{{ request('search') }}" placeholder="Search company..." style="width:180px">
        <select class="form-select form-select-sm rounded-3" name="status" style="width:150px">
            <option value="">{{ __('ui.all_statuses') }}</option>
            @foreach(['pending','processing','paid','failed','cancelled','refunded'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <select class="form-select form-select-sm rounded-3" name="method" style="width:150px">
            <option value="">{{ __('ui.all_methods') }}</option>
            <option value="manual" {{ request('method') === 'manual' ? 'selected' : '' }}>{{ __('ui.manual') }}</option>
            <option value="virement" {{ request('method') === 'virement' ? 'selected' : '' }}>Virement Bancaire (Bank Transfer)</option>
            <option value="chari_online" {{ request('method') === 'chari_online' ? 'selected' : '' }}>Paiement en ligne (Chari Pay)</option>
        </select>
        <button type="submit" class="btn btn-sm px-3 text-white fw-semibold rounded-3" style="background:var(--primary-gradient)">
            <i class="bi bi-funnel-fill me-1"></i> {{ __('ui.filter') }}
        </button>
        @if(request()->hasAny(['search','status','method']))
            <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-outline-secondary rounded-3">
                <i class="bi bi-x-lg"></i>
            </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="payment-table-wrapper">
        @if($payments->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-credit-card d-block mb-3" style="font-size:2.5rem;opacity:.3"></i>
                <p class="text-muted mb-0">{{ __('ui.no_payments_found') }}</p>
            </div>
        @else
            <table class="payment-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('ui.company') }}</th>
                        <th>{{ __('ui.plan') }}</th>
                        <th>{{ __('ui.amount') }}</th>
                        <th>{{ __('ui.method') }}</th>
                        <th>{{ __('ui.status') }}</th>
                        <th>{{ __('ui.date') }}</th>
                        <th>{{ __('ui.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                    <tr>
                        <td class="text-muted small">{{ $payment->id }}</td>
                        <td>
                            <div class="fw-semibold">{{ $payment->company->name ?? '—' }}</div>
                            <div class="text-muted small">{{ $payment->company->email ?? '' }}</div>
                        </td>
                        <td>{{ $payment->subscription->plan->name ?? '—' }}</td>
                        <td>
                            <span class="fw-bold">{{ number_format($payment->amount, 2) }}</span>
                            <span class="text-muted small">{{ $payment->currency }}</span>
                        </td>
                        <td><span class="badge bg-secondary-subtle text-secondary rounded-pill px-2">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span></td>
                        <td><span class="status-badge status-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                        <td>
                            <div>{{ $payment->created_at->format('M d, Y') }}</div>
                            <div class="text-muted small">{{ $payment->created_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                @if($payment->receipt_path)
                                <a href="{{ route('admin.payments.receipt', $payment) }}" class="btn btn-xs btn-outline-primary btn-sm rounded-3 px-2 py-1" title="Download Receipt" target="_blank">
                                    <i class="bi bi-file-earmark-text"></i> {{ __('ui.receipt') ?? 'Receipt' }}
                                </a>
                                @endif

                                @if($payment->status === 'pending' && in_array($payment->payment_method, ['manual', 'virement', 'chari_online']))
                                <form method="POST" action="{{ route('admin.payments.approve', $payment) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-success btn-sm rounded-3 px-2 py-1" title="Approve"
                                            onclick="return confirm('Approve this payment and activate subscription?')">
                                        <i class="bi bi-check-lg"></i> {{ __('ui.approve') }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger rounded-3 px-2 py-1" title="Reject"
                                            onclick="return confirm('Reject this payment?')">
                                        <i class="bi bi-x-lg"></i> {{ __('ui.reject') }}
                                    </button>
                                </form>
                                @elseif($payment->status === 'paid')
                                <form method="POST" action="{{ route('admin.payments.refund', $payment) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary rounded-3 px-2 py-1"
                                            onclick="return confirm('Refund this payment?')">
                                        <i class="bi bi-arrow-counterclockwise"></i> {{ __('ui.refund') }}
                                    </button>
                                </form>
                                @else
                                <span class="text-muted small">—</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if($payments->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $payments->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
