@extends('layouts.dashboard')

@section('title', __('ui.customer_details') . ' | ' . $customer->full_name)
@section('header_title', $customer->full_name)
@section('header_subtitle', __('ui.customer_profile_activity_history'))

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/css/queue.css') }}">
<style>
    .customer-card {
        background: white;
        border-radius: 1.5rem;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 10px 30px rgba(0,0,0,0.02);
        overflow: hidden;
        margin-bottom: 2rem;
    }
    .customer-header {
        background: var(--primary-gradient);
        padding: 3rem 2rem;
        color: white;
        text-align: center;
        position: relative;
    }
    .customer-avatar-large {
        width: 100px;
        height: 100px;
        background: white;
        color: var(--primary-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: 800;
        margin: 0 auto 1.5rem;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .customer-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        padding: 2rem;
        background: #f8fafc;
    }
    .info-item {
        display: flex;
        flex-direction: column;
    }
    .info-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #94a3b8;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .info-value {
        font-weight: 600;
        color: #1e293b;
    }
    
    .stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-top: -1.5rem;
        padding: 0 2rem;
        position: relative;
        z-index: 10;
    }
    .stat-mini-card {
        background: white;
        border-radius: 1rem;
        padding: 1.25rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        text-align: center;
        border: 1px solid rgba(0,0,0,0.03);
    }
    .stat-mini-value {
        display: block;
        font-size: 1.5rem;
        font-weight: 800;
        color: #1e293b;
    }
    .stat-mini-label {
        font-size: 0.7rem;
        color: #94a3b8;
        text-transform: uppercase;
        font-weight: 700;
    }

    /* Override timeline styles for details page */
    .detail-card {
        background: white;
        border-radius: 1.5rem;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        margin-bottom: 2rem;
    }
    .detail-card-header {
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .detail-card-header i {
        color: var(--primary-color);
        font-size: 1.25rem;
    }
    .detail-card-header h6 {
        margin: 0;
        font-weight: 700;
        color: #1e293b;
    }
    .detail-card-body {
        padding: 2rem;
    }

    [data-bs-theme="dark"] .customer-card,
    [data-bs-theme="dark"] .detail-card,
    [data-bs-theme="dark"] .stat-mini-card { background: #1e293b; border-color: rgba(255,255,255,0.05); }
    [data-bs-theme="dark"] .customer-info-grid { background: #0f172a; }
    [data-bs-theme="dark"] .info-value,
    [data-bs-theme="dark"] .stat-mini-value,
    [data-bs-theme="dark"] .detail-card-header h6 { color: #f1f5f9; }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="row">
        {{-- Left Column: Profile & Stats --}}
        <div class="col-lg-4">
            <div class="customer-card fade-in">
                <div class="customer-header">
                    <div class="customer-avatar-large">
                        {{ strtoupper(substr($customer->first_name, 0, 1)) }}{{ strtoupper(substr($customer->last_name, 0, 1)) }}
                    </div>
                    <h4 class="fw-bold mb-1">{{ $customer->full_name }}</h4>
                    <p class="mb-0 text-white-50">{{ __('ui.customer_since') }} {{ $customer->created_at ? $customer->created_at->format('M Y') : __('ui.unknown') }}</p>
                </div>
                
                <div class="stats-row">
                    <div class="stat-mini-card">
                        <span class="stat-mini-value">{{ $customer->tickets->count() }}</span>
                        <span class="stat-mini-label">{{ __('ui.total_tickets') }}</span>
                    </div>
                    <div class="stat-mini-card">
                        <span class="stat-mini-value">{{ $customer->tickets->where('status', 'done')->count() }}</span>
                        <span class="stat-mini-label">{{ __('ui.completed') }}</span>
                    </div>
                    <div class="stat-mini-card">
                        <span class="stat-mini-value text-danger">{{ $customer->tickets->whereIn('status', ['cancelled', 'no_show'])->count() }}</span>
                        <span class="stat-mini-label">{{ __('ui.missed') }}</span>
                    </div>
                </div>

                <div class="customer-info-grid mt-4">
                    <div class="info-item">
                        <span class="info-label">{{ __('ui.phone_number') }}</span>
                        <span class="info-value">{{ $customer->phone ?? '—' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">{{ __('ui.identifier_cin') }}</span>
                        <span class="info-value">{{ $customer->identifier ?? $customer->cin ?? $customer->file_number ?? '—' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">{{ __('ui.latest_activity') }}</span>
                        <span class="info-value">{{ $customer->updated_at ? $customer->updated_at->diffForHumans() : __('ui.never') }}</span>
                    </div>
                </div>
                
                @if(!empty($customer->phone))
                    <div class="p-4 bg-white border-top">
                        <button class="btn btn-outline-primary w-100 rounded-3 fw-bold" onclick="window.location='{{ route('whatsapp.chat.show', $customer->phone) }}'">
                            <i class="bi bi-whatsapp me-2"></i> {{ __('ui.open_whatsapp_chat') }}
                        </button>
                    </div>
                @endif
            </div>
        </div>

        {{-- Right Column: Timeline & History --}}
        <div class="col-lg-8">
            {{-- Activity Timeline --}}
            <div class="detail-card fade-in">
                <div class="detail-card-header">
                    <i class="bi bi-clock-history"></i>
                    <h6>{{ __('ui.activity_history') }}</h6>
                </div>
                <div class="detail-card-body">
                    @if($timeline->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-journal-x d-block mb-3 display-4 text-muted opacity-25"></i>
                            <p class="text-muted">{{ __('ui.no_activity_logs_customer') }}</p>
                        </div>
                    @else
                        <x-activity-timeline :timeline="$timeline" />
                    @endif
                </div>
            </div>

            {{-- Recent Tickets --}}
            <div class="detail-card fade-in" style="animation-delay: 0.1s;">
                <div class="detail-card-header">
                    <i class="bi bi-ticket-perforated"></i>
                    <h6>{{ __('ui.recent_queue_history') }}</h6>
                </div>
                <div class="detail-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">{{ __('ui.ticket') }}</th>
                                    <th>{{ __('ui.service') }}</th>
                                    <th>{{ __('ui.status') }}</th>
                                    <th class="text-end pe-4">{{ __('ui.date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customer->tickets()->latest()->take(5)->get() as $ticket)
                                <tr onclick="window.location='{{ route('tickets.show', $ticket) }}'" class="cursor-pointer">
                                    <td class="ps-4"><strong>{{ $ticket->ticket_number }}</strong></td>
                                    <td>{{ $ticket->service?->name ?? '—' }}</td>
                                    <td>
                                        <span class="badge rounded-pill bg-{{ $ticket->status == 'done' ? 'success' : ($ticket->status == 'cancelled' ? 'danger' : 'primary') }}">
                                            {{ __('ui.status_' . $ticket->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4 small text-secondary">{{ $ticket->created_at ? $ticket->created_at->format('M d, Y') : __('ui.unknown') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($customer->tickets->isEmpty())
                        <div class="text-center py-4 text-muted">{{ __('ui.no_ticket_history') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
