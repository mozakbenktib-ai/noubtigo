@extends('layouts.dashboard')

@section('title', 'Noubtigo | ' . __('ui.ticket_details') . ' — ' . $ticket->ticket_number)
@section('header_title', __('ui.ticket_details'))
@section('header_subtitle', $ticket->ticket_number . ' — ' . __('ui.source_of_truth'))

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/css/queue.css') }}">
<style>
/* ── Ticket Details Page ─────────────────────────────────────────────── */
.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

@media (max-width: 992px) {
    .detail-grid { grid-template-columns: 1fr; }
}

.detail-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    border: 1px solid rgba(0,0,0,0.05);
    overflow: hidden;
    transition: all 0.3s ease;
}

.detail-card:hover {
    box-shadow: 0 4px 16px rgba(34,197,94,0.1);
}

.detail-card-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.detail-card-header i { font-size: 1.1rem; color: #22c55e; }
.detail-card-header h6 { margin: 0; font-weight: 700; font-size: 0.9rem; }

.detail-card-body { padding: 1.25rem; }

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.6rem 0;
    border-bottom: 1px solid #f8fafc;
}

.detail-row:last-child { border-bottom: none; }
.detail-label { font-size: 0.8rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
.detail-value { font-size: 0.9rem; font-weight: 600; color: #1e293b; }

/* ── Hero Ticket Banner ──────────────────────────────────────────────── */
.ticket-hero {
    background: var(--primary-gradient, linear-gradient(135deg, #22c55e, #06b6d4));
    border-radius: 1rem;
    padding: 2rem;
    color: white;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    position: relative;
    overflow: hidden;
}

.ticket-hero::before {
    content: '';
    position: absolute;
    right: -40px;
    top: -40px;
    width: 200px;
    height: 200px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
}

.ticket-hero::after {
    content: '';
    position: absolute;
    right: 60px;
    bottom: -60px;
    width: 150px;
    height: 150px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
}

.ticket-hero-number {
    font-size: 2.5rem;
    font-weight: 800;
    line-height: 1;
}

.ticket-hero-meta {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

/* ── Activity Timeline ───────────────────────────────────────────────── */
.activity-timeline {
    position: relative;
    padding: 0.5rem 0;
}

.activity-timeline::before {
    content: '';
    position: absolute;
    left: 19px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(180deg, #22c55e, #06b6d4);
    opacity: 0.2;
    border-radius: 1px;
}

[dir="rtl"] .activity-timeline::before {
    left: auto;
    right: 19px;
}

.timeline-entry {
    display: flex;
    gap: 1rem;
    padding: 0.75rem 0;
    position: relative;
    animation: fadeInUp 0.4s ease backwards;
}

.timeline-entry:nth-child(1) { animation-delay: 0s; }
.timeline-entry:nth-child(2) { animation-delay: 0.08s; }
.timeline-entry:nth-child(3) { animation-delay: 0.16s; }
.timeline-entry:nth-child(4) { animation-delay: 0.24s; }
.timeline-entry:nth-child(5) { animation-delay: 0.32s; }
.timeline-entry:nth-child(6) { animation-delay: 0.4s; }

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.timeline-entry-dot {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    z-index: 1;
    font-size: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.timeline-entry-dot.created { background: #22c55e; color: white; }
.timeline-entry-dot.status_changed_called { background: #3b82f6; color: white; }
.timeline-entry-dot.status_changed_serving { background: #f59e0b; color: white; }
.timeline-entry-dot.status_changed_done { background: #06b6d4; color: white; }
.timeline-entry-dot.status_changed_cancelled { background: #ef4444; color: white; }
.timeline-entry-dot.status_changed_no_show { background: #f59e0b; color: white; }
.timeline-entry-dot.status_changed_on_hold { background: #f97316; color: white; }
.timeline-entry-dot.updated { background: #8b5cf6; color: white; }
.timeline-entry-dot.deleted { background: #ef4444; color: white; }

.timeline-entry-content {
    flex: 1;
    background: #f8fafc;
    border-radius: 0.75rem;
    padding: 0.85rem 1rem;
    border: 1px solid rgba(0,0,0,0.04);
}

.timeline-entry-content:hover {
    background: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.timeline-entry-title {
    font-weight: 600;
    font-size: 0.85rem;
    color: #1e293b;
    margin-bottom: 0.25rem;
}

.timeline-entry-meta {
    font-size: 0.75rem;
    color: #94a3b8;
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.timeline-changes {
    margin-top: 0.5rem;
    font-size: 0.78rem;
    background: rgba(0,0,0,0.02);
    border-radius: 0.5rem;
    padding: 0.5rem 0.75rem;
}

.timeline-changes .change-before { color: #ef4444; text-decoration: line-through; }
.timeline-changes .change-after { color: #22c55e; font-weight: 600; }

/* ── Back Button ─────────────────────────────────────────────────────── */
.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #64748b;
    text-decoration: none;
    margin-bottom: 1.25rem;
    transition: all 0.2s;
}

.back-link:hover { color: #22c55e; transform: translateX(-3px); }

[data-bs-theme="dark"] .detail-card { background: #1e293b; border-color: rgba(255,255,255,0.05); }
[data-bs-theme="dark"] .detail-card-header { border-color: rgba(255,255,255,0.05); }
[data-bs-theme="dark"] .detail-value { color: #e2e8f0; }
[data-bs-theme="dark"] .timeline-entry-content { background: #0f172a; border-color: rgba(255,255,255,0.03); }
[data-bs-theme="dark"] .timeline-entry-content:hover { background: #1e293b; }
[data-bs-theme="dark"] .timeline-entry-title { color: #f1f5f9; }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    {{-- ── Back Link ─────────────────────────────────────────────────────── --}}
    <a href="{{ route('tickets.index') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> {{ __('ui.back_to_tickets') }}
    </a>

    {{-- ── Hero Banner ───────────────────────────────────────────────────── --}}
    <div class="ticket-hero fade-in">
        <div>
            <div class="ticket-hero-number">{{ $ticket->ticket_number }}</div>
            <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
                @php
                    $statusClass = match($ticket->status) {
                        'waiting' => 'waiting', 'called' => 'called', 'serving' => 'serving',
                        'done' => 'done', 'cancelled' => 'cancelled', 'no_show' => 'no_show',
                        'on_hold' => 'on_hold',
                        default => 'waiting',
                    };
                @endphp
                <span class="status-badge {{ $statusClass }}" style="font-size: 0.8rem; padding: 0.35rem 0.85rem;">
                    {{ __('ui.status_' . $ticket->status) }}
                </span>
                @if($ticket->is_vip)
                    <span class="vip-badge" style="font-size: 0.75rem; padding: 0.2rem 0.6rem;">{{ __('ui.vip') }}</span>
                @endif
                @if($ticket->source === 'appointment')
                    <span class="badge bg-white bg-opacity-25 rounded-pill">
                        <i class="bi bi-calendar-check me-1"></i>{{ __('ui.source_appointment') }}
                    </span>
                @else
                    <span class="badge bg-white bg-opacity-25 rounded-pill">
                        <i class="bi bi-person-walking me-1"></i>{{ __('ui.source_walk_in') }}
                    </span>
                @endif
            </div>
        </div>
        <div class="ticket-hero-meta">
            <div class="text-center">
                <div class="small opacity-75">{{ __('ui.created_at') }}</div>
                <div class="fw-bold">{{ $ticket->created_at->format('H:i') }}</div>
            </div>
            @if($ticket->called_at)
            <div class="text-center">
                <div class="small opacity-75">{{ __('ui.called_at') }}</div>
                <div class="fw-bold">{{ $ticket->called_at->format('H:i') }}</div>
            </div>
            @endif
            @if($ticket->started_at)
            <div class="text-center">
                <div class="small opacity-75">{{ __('ui.started_at') }}</div>
                <div class="fw-bold">{{ $ticket->started_at->format('H:i') }}</div>
            </div>
            @endif
            @if($ticket->finished_at)
            <div class="text-center">
                <div class="small opacity-75">{{ __('ui.finished_at') }}</div>
                <div class="fw-bold">{{ $ticket->finished_at->format('H:i') }}</div>
            </div>
            @endif
            @if($ticket->hold_at)
            <div class="text-center">
                <div class="small opacity-75">{{ __('ui.put_on_hold') }}</div>
                <div class="fw-bold">{{ $ticket->hold_at->format('H:i') }}</div>
            </div>
            @endif
            @if($ticket->resumed_at)
            <div class="text-center">
                <div class="small opacity-75">{{ __('ui.resumed_at') }}</div>
                <div class="fw-bold">{{ $ticket->resumed_at->format('H:i') }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Detail Cards Grid ─────────────────────────────────────────────── --}}
    <div class="detail-grid">

        {{-- Ticket Info --}}
        <div class="detail-card fade-in">
            <div class="detail-card-header">
                <i class="bi bi-ticket-perforated-fill"></i>
                <h6>{{ __('ui.ticket_info') }}</h6>
            </div>
            <div class="detail-card-body">
                <div class="detail-row">
                    <span class="detail-label">{{ __('ui.ticket_number') }}</span>
                    <span class="detail-value">{{ $ticket->ticket_number }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('ui.service') }}</span>
                    <span class="detail-value">{{ $ticket->service?->name ?? '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('ui.room') }}</span>
                    <span class="detail-value">{{ $ticket->room->name ?? '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('ui.source') }}</span>
                    <span class="detail-value">{{ __('ui.source_' . $ticket->source) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('ui.priority_score') }}</span>
                    <span class="detail-value">
                        @if($ticket->priority_score > 0)
                            <span class="badge score-badge"><i class="bi bi-lightning-charge-fill me-1"></i>{{ $ticket->priority_score }}</span>
                        @else
                            0
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Customer Info --}}
        <div class="detail-card fade-in">
            <div class="detail-card-header">
                <i class="bi bi-person-fill"></i>
                <h6>{{ __('ui.customer') }}</h6>
            </div>
            <div class="detail-card-body">
                @if($ticket->customer)
                    <div class="detail-row">
                        <span class="detail-label">{{ __('ui.full_name') }}</span>
                        <span class="detail-value">{{ $ticket->customer->full_name }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">{{ __('ui.phone_number') }}</span>
                        <span class="detail-value">{{ $ticket->customer->phone ?? __('ui.no_phone') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">{{ __('ui.email') }}</span>
                        <span class="detail-value">{{ $ticket->customer->email ?? __('ui.no_email') }}</span>
                    </div>
                @else
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-person-x d-block mb-2" style="font-size: 1.5rem; opacity: 0.4;"></i>
                        {{ __('ui.guest_customer') }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Staff Info --}}
        <div class="detail-card fade-in">
            <div class="detail-card-header">
                <i class="bi bi-person-badge-fill"></i>
                <h6>{{ __('ui.staff') }}</h6>
            </div>
            <div class="detail-card-body">
                @if($ticket->operator)
                    <div class="detail-row">
                        <span class="detail-label">{{ __('ui.operator') }}</span>
                        <span class="detail-value">{{ $ticket->operator->full_name }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">{{ __('ui.email') }}</span>
                        <span class="detail-value">{{ $ticket->operator->email }}</span>
                    </div>
                @else
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-person-x d-block mb-2" style="font-size: 1.5rem; opacity: 0.4;"></i>
                        {{ __('ui.unassigned') }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Timestamps --}}
        <div class="detail-card fade-in">
            <div class="detail-card-header">
                <i class="bi bi-clock-fill"></i>
                <h6>{{ __('ui.timestamps') }}</h6>
            </div>
            <div class="detail-card-body">
                <div class="detail-row">
                    <span class="detail-label">{{ __('ui.created_at') }}</span>
                    <span class="detail-value">{{ $ticket->created_at->format('M d, Y H:i:s') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('ui.called_at') }}</span>
                    <span class="detail-value">{{ $ticket->called_at ? $ticket->called_at->format('H:i:s') : '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('ui.started_at') }}</span>
                    <span class="detail-value">{{ $ticket->started_at ? $ticket->started_at->format('H:i:s') : '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('ui.finished_at') }}</span>
                    <span class="detail-value">{{ $ticket->finished_at ? $ticket->finished_at->format('H:i:s') : '—' }}</span>
                </div>
                @if($ticket->called_at && $ticket->finished_at)
                <div class="detail-row">
                    <span class="detail-label">{{ __('ui.total_duration') }}</span>
                    <span class="detail-value text-success fw-bold">
                        {{ $ticket->called_at->diffForHumans($ticket->finished_at, true) }}
                    </span>
                </div>
                @endif

                @if($ticket->waited_since && $ticket->called_at)
                <div class="detail-row">
                    <span class="detail-label">{{ __('ui.wait_time_pre_call') }}</span>
                    <span class="detail-value text-warning fw-bold">
                        {{ $ticket->waited_since->diffForHumans($ticket->called_at, true) }}
                    </span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Hold Information (if applicable) --}}
    @if($ticket->hold_reason)
    <div class="detail-card mt-4 fade-in">
        <div class="detail-card-header">
            <i class="bi bi-pause-circle-fill" style="color: #f97316;"></i>
            <h6>{{ __('ui.hold_reason') }}</h6>
        </div>
        <div class="detail-card-body">
            <div class="detail-row">
                <span class="detail-label">{{ __('ui.hold_reason') }}</span>
                <span class="detail-value">
                    <span class="status-badge on_hold">{{ __('ui.hold_reason_' . $ticket->hold_reason) }}</span>
                </span>
            </div>
            @if($ticket->hold_note)
            <div class="detail-row">
                <span class="detail-label">{{ __('ui.hold_note') }}</span>
                <span class="detail-value">{{ $ticket->hold_note }}</span>
            </div>
            @endif
            @if($ticket->hold_at)
            <div class="detail-row">
                <span class="detail-label">{{ __('ui.put_on_hold') }}</span>
                <span class="detail-value">{{ $ticket->hold_at->format('M d, Y H:i:s') }}</span>
            </div>
            @endif
            @if($ticket->holder)
            <div class="detail-row">
                <span class="detail-label">{{ __('ui.paused_by') }}</span>
                <span class="detail-value">{{ $ticket->holder->full_name }}</span>
            </div>
            @endif
            @if($ticket->resumed_at)
            <div class="detail-row">
                <span class="detail-label">{{ __('ui.resumed_at') }}</span>
                <span class="detail-value">{{ $ticket->resumed_at->format('M d, Y H:i:s') }}</span>
            </div>
            @endif
            @if($ticket->resumer)
            <div class="detail-row">
                <span class="detail-label">{{ __('ui.resumed_by') }}</span>
                <span class="detail-value">{{ $ticket->resumer->full_name }}</span>
            </div>
            @endif
            @if($ticket->hold_at && $ticket->resumed_at)
            <div class="detail-row">
                <span class="detail-label">{{ __('ui.hold_duration') }}</span>
                <span class="detail-value text-warning fw-bold">
                    {{ $ticket->hold_at->diffForHumans($ticket->resumed_at, true) }}
                </span>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Cancellation Information (if ticket was cancelled) --}}
    @if($ticket->status === 'cancelled' && $ticket->cancellation_reason)
    <div class="detail-card mt-4 fade-in" style="border-left: 4px solid #ef4444;">
        <div class="detail-card-header">
            <i class="bi bi-x-circle-fill" style="color: #ef4444;"></i>
            <h6>{{ __('ui.confirm_cancel_ticket') }}</h6>
        </div>
        <div class="detail-card-body">
            <div class="detail-row">
                <span class="detail-label">{{ __('ui.cancellation_reason') }}</span>
                <span class="detail-value">
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fs-7">
                        <i class="bi bi-x-circle me-1"></i>
                        @php
                            $reasonSnake = str_replace(' ', '_', strtolower($ticket->cancellation_reason));
                            $translatedReason = __('ui.cancel_reason_' . $reasonSnake);
                            if (str_starts_with($translatedReason, 'ui.cancel_reason_')) {
                                $translatedReason = $ticket->cancellation_reason;
                            }
                        @endphp
                        {{ $translatedReason }}
                    </span>
                </span>
            </div>
            @if($ticket->cancellation_note)
            <div class="detail-row">
                <span class="detail-label">{{ __('ui.cancellation_note') }}</span>
                <span class="detail-value fst-italic">{{ $ticket->cancellation_note }}</span>
            </div>
            @endif
            @if($ticket->cancelled_at)
            <div class="detail-row">
                <span class="detail-label">{{ __('ui.cancelled_at') }}</span>
                <span class="detail-value">{{ $ticket->cancelled_at->format('M d, Y H:i:s') }}</span>
            </div>
            @endif
            @if($ticket->canceller)
            <div class="detail-row">
                <span class="detail-label">{{ __('ui.cancelled_by') }}</span>
                <span class="detail-value d-flex align-items-center gap-2">
                    <i class="bi bi-person-circle text-secondary"></i>
                    {{ $ticket->canceller->full_name }}
                </span>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ── Activity Timeline ─────────────────────────────────────────────── --}}
    <div class="detail-card mt-4 fade-in" style="grid-column: 1 / -1;">
        <div class="detail-card-header">
            <i class="bi bi-clock-history"></i>
            <h6>{{ __('ui.activity_timeline') }}</h6>
        </div>
        <div class="detail-card-body">
            @if($timeline->isEmpty())
                <div class="text-center text-muted py-4">
                    <i class="bi bi-journal-x d-block mb-2" style="font-size: 1.5rem; opacity: 0.4;"></i>
                    {{ __('ui.no_activity_yet') }}
                </div>
            @else
                <x-activity-timeline :timeline="$timeline" />
            @endif
        </div>
    </div>

</div>
@endsection
