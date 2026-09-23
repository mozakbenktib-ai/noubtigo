@extends('layouts.dashboard')

@section('title', 'Noubtigo | Subscriptions')
@section('header_title', 'Subscription Management')
@section('header_subtitle', 'View and manage all tenant subscriptions')

@push('styles')
<style>
.sub-table-wrapper {
    background: white; border-radius: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    border: 1px solid rgba(0,0,0,0.05); overflow: hidden;
}
.sub-table { width: 100%; border-collapse: collapse; }
.sub-table thead th {
    background: #f8fafc; padding: 0.85rem 1.25rem;
    font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;
    font-weight: 700; color: #94a3b8; border-bottom: 1px solid #e2e8f0; white-space: nowrap;
}
.sub-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background .15s; }
.sub-table tbody tr:hover { background: rgba(34,197,94,0.02); }
.sub-table tbody tr:last-child { border-bottom: none; }
.sub-table tbody td { padding: 0.75rem 1.25rem; font-size: 0.85rem; color: #334155; vertical-align: middle; }
.status-badge { display: inline-flex; align-items: center; gap: .3rem; padding: .25rem .65rem; border-radius: 2rem; font-size: .7rem; font-weight: 600; text-transform: uppercase; }
.status-active { background: #dcfce7; color: #16a34a; }
.status-pending { background: #fef9c3; color: #854d0e; }
.status-suspended { background: #fef2f2; color: #dc2626; }
.status-cancelled { background: #f1f5f9; color: #64748b; }
.status-expired { background: #e0e7ff; color: #4338ca; }
[data-bs-theme="dark"] .sub-table-wrapper { background: #1e293b; border-color: rgba(255,255,255,0.05); }
[data-bs-theme="dark"] .sub-table thead th { background: #0f172a; color: #64748b; }
[data-bs-theme="dark"] .sub-table tbody td { color: #e2e8f0; }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    {{-- Filters --}}
    <form class="d-flex flex-wrap gap-2 mb-4" method="GET" action="{{ route('admin.subscriptions.index') }}">
        <select class="form-select form-select-sm rounded-3" name="status" style="width:160px">
            <option value="">All Statuses</option>
            @foreach(['pending','active','suspended','cancelled','expired'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-sm px-3 text-white fw-semibold rounded-3" style="background:var(--primary-gradient)">
            <i class="bi bi-funnel-fill me-1"></i> Filter
        </button>
        @if(request('status'))
            <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-sm btn-outline-secondary rounded-3"><i class="bi bi-x-lg"></i></a>
        @endif
    </form>

    {{-- Table --}}
    <div class="sub-table-wrapper">
        @if($subscriptions->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-calendar-x d-block mb-3" style="font-size:2.5rem;opacity:.3"></i>
                <p class="text-muted mb-0">No subscriptions found</p>
            </div>
        @else
            <table class="sub-table">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Plan</th>
                        <th>Cycle</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subscriptions as $sub)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $sub->company->name ?? '—' }}</div>
                        </td>
                        <td>{{ $sub->plan->name ?? '—' }}</td>
                        <td>
                            <span class="badge rounded-pill" style="background:{{ $sub->billing_cycle === 'annual' ? '#e0e7ff' : '#f0fdf4' }};color:{{ $sub->billing_cycle === 'annual' ? '#4338ca' : '#16a34a' }}">
                                <i class="bi bi-arrow-repeat me-1"></i>{{ ucfirst($sub->billing_cycle) }}
                            </span>
                        </td>
                        <td><span class="status-badge status-{{ $sub->status }}">{{ ucfirst($sub->status) }}</span></td>
                        <td>{{ $sub->starts_at ? $sub->starts_at->format('M d, Y') : '—' }}</td>
                        <td>{{ $sub->ends_at ? $sub->ends_at->format('M d, Y') : '—' }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                @if($sub->status !== 'active')
                                <form method="POST" action="{{ route('admin.subscriptions.activate', $sub) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-success rounded-3 px-2 py-1" onclick="return confirm('Activate this subscription?')">
                                        <i class="bi bi-play-fill"></i> Activate
                                    </button>
                                </form>
                                @endif
                                @if($sub->status === 'active')
                                <form method="POST" action="{{ route('admin.subscriptions.suspend', $sub) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-warning rounded-3 px-2 py-1" onclick="return confirm('Suspend this subscription?')">
                                        <i class="bi bi-pause-fill"></i> Suspend
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if($subscriptions->hasPages())
        <div class="d-flex justify-content-center mt-4">{{ $subscriptions->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
