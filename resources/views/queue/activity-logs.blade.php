@extends('layouts.dashboard')

@section('title', 'Noubtigo | ' . __('ui.activity_logs'))
@section('header_title', __('ui.activity_logs'))
@section('header_subtitle', __('ui.activity_logs_subtitle'))

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/css/queue.css') }}">
<style>
.logs-table-wrapper {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    border: 1px solid rgba(0,0,0,0.05);
    overflow: hidden;
}

.logs-table { width: 100%; border-collapse: collapse; }

.logs-table thead th {
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

.logs-table tbody tr {
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.2s ease;
}

.logs-table tbody tr:hover { background: rgba(34,197,94,0.02); }
.logs-table tbody tr:last-child { border-bottom: none; }

.logs-table tbody td {
    padding: 0.75rem 1.25rem;
    font-size: 0.85rem;
    color: #334155;
    vertical-align: middle;
}

.action-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.2rem 0.55rem;
    border-radius: 2rem;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
}

.action-badge.created { background: #dcfce7; color: #16a34a; }
.action-badge.updated { background: #ede9fe; color: #7c3aed; }
.action-badge.deleted { background: #fef2f2; color: #dc2626; }
.action-badge.status_changed { background: #dbeafe; color: #2563eb; }
.action-badge.reordered { background: #fef3c7; color: #d97706; }

.log-changes-preview {
    font-size: 0.75rem;
    color: #64748b;
    max-width: 250px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.logs-filters {
    display: flex;
    gap: 0.75rem;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
}

.logs-filters .form-control,
.logs-filters .form-select {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    padding: 0.55rem 1rem;
    font-size: 0.85rem;
}

.logs-filters .form-control:focus,
.logs-filters .form-select:focus {
    border-color: #22c55e;
    box-shadow: 0 0 0 3px rgba(34,197,94,0.1);
}

[data-bs-theme="dark"] .logs-table-wrapper { background: #1e293b; border-color: rgba(255,255,255,0.05); }
[data-bs-theme="dark"] .logs-table thead th { background: #0f172a; color: #64748b; border-color: rgba(255,255,255,0.05); }
[data-bs-theme="dark"] .logs-table tbody tr { border-color: rgba(255,255,255,0.03); }
[data-bs-theme="dark"] .logs-table tbody td { color: #e2e8f0; }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    {{-- ── Filters ────────────────────────────────────────────────────────── --}}
    <form class="logs-filters fade-in" method="GET" action="{{ route('activity-logs.index') }}">
        <select class="form-select" name="model_type" style="width: 150px;">
            <option value="">{{ __('ui.all_types') }}</option>
            <option value="Ticket" {{ request('model_type') === 'Ticket' ? 'selected' : '' }}>{{ __('ui.ticket') }}</option>
            <option value="Customer" {{ request('model_type') === 'Customer' ? 'selected' : '' }}>{{ __('ui.customer') }}</option>
        </select>
        <select class="form-select" name="action" style="width: 170px;">
            <option value="">{{ __('ui.all_actions') }}</option>
            <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>{{ __('ui.action_created') }}</option>
            <option value="updated" {{ request('action') === 'updated' ? 'selected' : '' }}>{{ __('ui.action_updated') }}</option>
            <option value="status_changed" {{ request('action') === 'status_changed' ? 'selected' : '' }}>{{ __('ui.action_status_changed') }}</option>
            <option value="reordered" {{ request('action') === 'reordered' ? 'selected' : '' }}>{{ __('ui.action_reordered') }}</option>
            <option value="deleted" {{ request('action') === 'deleted' ? 'selected' : '' }}>{{ __('ui.action_deleted') }}</option>
        </select>
        <input type="date" class="form-control" name="date" value="{{ request('date') }}" style="width: 160px;">
        <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="{{ __('ui.search') }}..." style="width: 180px;">
        <button type="submit" class="btn btn-sm px-3 py-2 fw-bold text-white" style="background: var(--primary-gradient); border-radius: 0.75rem;">
            <i class="bi bi-funnel-fill me-1"></i> {{ __('ui.filter') }}
        </button>
        @if(request()->hasAny(['model_type', 'action', 'date', 'search']))
            <a href="{{ route('activity-logs.index') }}" class="btn btn-sm btn-outline-secondary px-3 py-2 rounded-3">
                <i class="bi bi-x-lg"></i>
            </a>
        @endif
    </form>

    {{-- ── Logs Table ────────────────────────────────────────────────────── --}}
    <div class="logs-table-wrapper fade-in">
        @if($logs->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-journal-x d-block mb-3" style="font-size: 2.5rem; opacity: 0.3;"></i>
                <p class="text-muted mb-0">{{ __('ui.no_logs_found') }}</p>
            </div>
        @else
            <table class="logs-table">
                <thead>
                    <tr>
                        <th>{{ __('ui.date') }}</th>
                        <th>{{ __('ui.user') }}</th>
                        <th>{{ __('ui.action') }}</th>
                        <th>{{ __('ui.model') }}</th>
                        <th>{{ __('ui.description') }}</th>
                        <th>{{ __('ui.changes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $log->created_at->format('M d, H:i') }}</div>
                                <div class="text-muted small">{{ $log->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                @if($log->user)
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($log->user->full_name) }}&background=22c55e&color=fff&size=28" 
                                             class="rounded-circle" width="28" alt="">
                                        <span class="fw-medium">{{ $log->user->full_name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted">{{ __('ui.system') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="action-badge {{ $log->action }}">
                                    {{ __('ui.action_' . $log->action) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $subject = $log->subject;
                                    $subjectUuid = $subject ? $subject->uuid : $log->model_id;
                                    
                                    $link = match($log->model_type) {
                                        'Ticket' => route('tickets.show', $subjectUuid),
                                        'Customer' => route('customers.show', $subjectUuid),
                                        'User' => route('rbac.users.show', $subjectUuid),
                                        default => null,
                                    };
                                @endphp

                                @if($log->model_label)
                                    @if($link)
                                        <a href="{{ $link }}" class="fw-medium text-primary text-decoration-none hover-underline">
                                            {{ $log->model_label }}
                                        </a>
                                    @else
                                        <span class="fw-medium text-dark">{{ $log->model_label }}</span>
                                    @endif
                                    <div class="text-muted small">{{ $log->model_type }}</div>
                                @else
                                    <span class="fw-medium">{{ $log->model_type }}</span>
                                    <span class="text-muted small">#{{ $subjectUuid }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-secondary">{{ Str::limit($log->description, 60) }}</span>
                            </td>
                            <td>
                                @if($log->changes)
                                    <div class="log-changes-preview">
                                        @if(isset($log->changes['before']['status']) && isset($log->changes['after']['status']))
                                            <span style="color:#ef4444">{{ $log->changes['before']['status'] }}</span>
                                            → <span style="color:#22c55e">{{ $log->changes['after']['status'] }}</span>
                                        @else
                                            {{ Str::limit(json_encode($log->changes), 40) }}
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- ── Pagination ────────────────────────────────────────────────────── --}}
    @if($logs->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $logs->withQueryString()->links() }}
        </div>
    @endif

</div>
@endsection
