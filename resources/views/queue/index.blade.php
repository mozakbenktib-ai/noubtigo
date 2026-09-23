@extends('layouts.dashboard')

@section('title', 'Noubtigo | ' . __('ui.queue_management'))
@section('header_title', __('ui.queue_management'))
@section('header_subtitle', __('ui.queue_subtitle'))
@php
    $tenant = app(\App\Services\TenantManager::class)->getTenant();
    $dailyCode = $tenant ? $tenant->getDailyTrackingCode() : '----';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/queue.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="container-fluid px-0">
    {{-- ── Daily Tracking Code Banner ────────────────────────────────────────── --}}
    <div id="toast-container" class="position-fixed top-0 end-0 p-4" style="z-index: 106000;"></div>

    <div class="alert bg-white border shadow-sm rounded-4 d-flex align-items-center justify-content-between p-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary-subtle text-primary p-2 rounded-3">
                <i class="bi bi-shield-lock-fill fs-5"></i>
            </div>
            <div>
                <h6 class="mb-0 fw-bold">{{ __('ui.daily_code') }}</h6>
                <p class="text-secondary small mb-0">{{ __('ui.enter_ticket_details') }}</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="fs-3 fw-bold text-primary border-start ps-3 me-2" style="letter-spacing: 4px;">{{ $dailyCode }}</span>
            <button class="btn btn-sm btn-light rounded-circle me-3" onclick="navigator.clipboard.writeText('{{ $dailyCode }}'); showToastAlert('{{ __('ui.daily_code_copied') }}', 'success');" title="{{ __('ui.copy_code') }}">
                <i class="bi bi-copy"></i>
            </button>
            <button class="btn btn-primary d-flex align-items-center gap-2 px-3 py-2 rounded-3 shadow-sm fw-bold border-0 text-white" data-bs-toggle="offcanvas" data-bs-target="#newTicketDrawer" style="background: var(--primary-gradient);">
                <i class="bi bi-plus-lg"></i>
                <span>{{ __('ui.new_ticket') }}</span>
            </button>
        </div>
    </div>

    {{-- ── Room Lanes ─────────────────────────────────────────────────────── --}}

    {{-- ── Room Lanes ─────────────────────────────────────────────────────── --}}
    <div class="room-lanes-grid">
        @foreach($rooms as $room)
            @php
                $roomTickets = $tickets->where('room_id', $room->id);
                $roomServing = $roomTickets->where('status', 'serving')->first() ?? $roomTickets->where('status', 'called')->first();
                $roomWaiting = $roomTickets->where('status', 'waiting');
                $roomAppointments = $roomWaiting->where('source', 'appointment');
                $roomWalkins = $roomWaiting->where('source', 'walk-in');
                $roomHistory = $history->where('room_id', $room->id);
            @endphp

            <div class="room-lane" id="room-lane-{{ $room->id }}">
                {{-- Room Header --}}
                <div class="room-lane-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-door-open-fill"></i>
                        <h5 class="mb-0 fw-bold">{{ $room->name }}</h5>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-white bg-opacity-25 rounded-pill">
                            {{ $roomWaiting->count() }} {{ __('ui.waiting') }}
                        </span>
                        <button class="btn btn-sm btn-link text-white p-0" onclick="openTimeline({{ $room->id }}, '{{ $room->name }}')" title="{{ __('ui.queue_history') }}">
                            <i class="bi bi-clock-history fs-5"></i>
                        </button>
                    </div>
                </div>

                {{-- Now Serving --}}
                <div class="room-now-serving">
                    <div class="serving-label">
                        <i class="bi bi-broadcast me-1"></i> {{ __('ui.now_serving') }}
                    </div>

                    @if($roomServing)
                        <div class="serving-ticket-number">{{ $roomServing->ticket_number }}</div>
                        <div class="serving-customer-name">
                            @if($roomServing->customer_id)
                                    <a href="{{ route('customers.show', $roomServing->customer->uuid ?? $roomServing->customer_id) }}" class="text-decoration-none text-dark hover-primary" target="_blank" title="{{ __('ui.view_profile') }}">
                                    {{ $roomServing->customer->full_name }} <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 0.75rem;"></i>
                                </a>
                            @else
                                {{ __('ui.customer') }}
                            @endif
                            @if($roomServing->is_vip) <span class="vip-badge ms-1">{{ __('ui.vip') }}</span> @endif
                        </div>
                        <div class="serving-meta mt-1">
                            @if($roomServing->source === 'appointment')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-calendar-check me-1"></i>{{ __('ui.source_appointment') }}
                                    @if($roomServing->appointment)
                                        ({{ app(\App\Services\TimezoneService::class)->toLocal($roomServing->appointment->appointment_date)->format('H:i') }})
                                    @endif
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle"><i class="bi bi-person-walking me-1"></i>{{ __('ui.source_walk_in') }}</span>
                            @endif
                            @if($roomServing->priority_score > 0)
                                <span class="badge score-badge"><i class="bi bi-lightning-charge-fill me-1"></i>{{ $roomServing->priority_score }} pts</span>
                            @endif
                        </div>
                    @else
                        <div class="serving-ticket-number serving-empty">---</div>
                        <div class="serving-customer-name text-muted">{{ __('ui.no_active_ticket') }}</div>
                    @endif

                    {{-- Action Buttons --}}
                    <div class="sq-actions-mini mt-3">
                        <button class="sq-mini-btn btn-next ripple" onclick="callNextTicket({{ $room->id }})" title="{{ __('ui.call_next') }}">
                            <i class="bi bi-skip-forward-fill"></i>
                        </button>
                        @if($roomServing)
                            <button class="sq-mini-btn btn-hold ripple" onclick="showHoldModal({{ $roomServing->id }}, '{{ $roomServing->ticket_number }}')" title="{{ __('ui.put_on_hold') }}">
                                <i class="bi bi-pause-circle-fill"></i>
                            </button>
                            <button class="sq-mini-btn btn-pass ripple" onclick="passTicket({{ $roomServing->id }})" title="{{ __('ui.pass') }}">
                                <i class="bi bi-arrow-right-circle-fill"></i>
                            </button>
                            <button class="sq-mini-btn btn-done ripple" onclick="completeTicket({{ $roomServing->id }})" title="{{ __('ui.complete') }}">
                                <i class="bi bi-check-circle-fill"></i>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Waiting List --}}
                <div class="room-waiting-list" style="padding-top: 10px;">
                    @if($roomWaiting->count() > 0)
                        <div id="queue-list-waiting-{{ $room->id }}" class="queue-list mb-3">
                            @foreach($roomWaiting as $ticket)
                                <div class="queue-list-item" data-id="{{ $ticket->id }}">
                                    <div class="ticket-avatar">
                                        {{ current(explode('-', $ticket->ticket_number)) }}
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2">
                                            <h6 class="mb-0 fw-bold">{{ $ticket->ticket_number }}</h6>
                                            @if($ticket->source === 'appointment')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle py-1" style="font-size: 0.65rem">
                                                    <i class="bi bi-calendar-check me-1"></i>{{ __('ui.source_appointment') }}
                                                    @if($ticket->appointment)
                                                        ({{ app(\App\Services\TimezoneService::class)->toLocal($ticket->appointment->appointment_date)->format('H:i') }})
                                                    @endif
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle py-1" style="font-size: 0.65rem"><i class="bi bi-person-walking me-1"></i>{{ __('ui.source_walk_in') }}</span>
                                            @endif
                                            @if($ticket->is_vip) <span class="vip-badge">{{ __('ui.vip') }}</span> @endif
                                        </div>
                                        <div class="text-secondary small mt-1 d-flex flex-wrap align-items-center gap-1">
                                            <span>
                                                <i class="bi bi-person me-1"></i>
                                                @if($ticket->customer_id)
                                                    <a href="{{ route('customers.show', $ticket->customer->uuid ?? $ticket->customer_id) }}" class="text-decoration-none" target="_blank" title="{{ __('ui.view_profile') }}">
                                                        {{ $ticket->customer->full_name }}
                                                    </a>
                                                @else
                                                    {{ __('ui.guest_customer') }}
                                                @endif
                                            </span>
                                            <span><i class="bi bi-clock me-1"></i>{{ $ticket->waited_since->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        @if($ticket->priority_score > 0)
                                            <span class="badge score-badge" style="font-size:.6rem"><i class="bi bi-lightning-charge-fill"></i> {{ $ticket->priority_score }}</span>
                                        @endif
                                        <div class="mt-1 text-muted small d-flex align-items-center justify-content-end gap-1">
                                            <button class="btn btn-sm btn-link text-muted p-0 border-0" onclick="showChangeRoomModal({{ $ticket->id }}, {{ $ticket->room_id }})" title="{{ __('ui.change_room') }}">
                                                <i class="bi bi-door-open"></i>
                                            </button>
                                            @permission('queue.delete')
                                            <button class="btn btn-sm btn-link text-danger p-0 border-0" onclick="showCancelModal({{ $ticket->id }}, '{{ $ticket->ticket_number }}')" title="{{ __('ui.cancel_ticket') }}">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                            @endpermission
                                            <i class="bi bi-grip-vertical"></i>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($roomWaiting->count() === 0)
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-cup-hot d-block mb-2 opacity-50" style="font-size:1.5rem"></i>
                            <small>{{ __('ui.queue_is_empty') }}</small>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── On Hold Tickets Section ─────────────────────────────────────────── --}}
    @if($onHoldTickets->count() > 0)
    <div class="on-hold-section" id="on-hold-section">
        <div class="on-hold-section-header">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-pause-circle-fill text-warning fs-5"></i>
                <h5>{{ __('ui.on_hold_tickets') }}</h5>
            </div>
            <span class="on-hold-count">{{ $onHoldTickets->count() }}</span>
        </div>
        <div class="on-hold-list">
            @foreach($onHoldTickets as $holdTicket)
                <div class="on-hold-card" id="on-hold-card-{{ $holdTicket->id }}">
                    <div class="on-hold-avatar">
                        {{ current(explode('-', $holdTicket->ticket_number)) }}
                    </div>
                    <div class="on-hold-info">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h6 class="mb-0 fw-bold">{{ $holdTicket->ticket_number }}</h6>
                            @if($holdTicket->is_vip) <span class="vip-badge">{{ __('ui.vip') }}</span> @endif
                            <span class="on-hold-reason">
                                <i class="bi bi-pause-fill"></i>
                                {{ __('ui.hold_reason_' . $holdTicket->hold_reason) }}
                            </span>
                        </div>
                        <div class="text-secondary small d-flex flex-wrap align-items-center gap-2">
                            <span>
                                <i class="bi bi-person me-1"></i>
                                @if($holdTicket->customer_id)
                                    <a href="{{ route('customers.show', $holdTicket->customer->uuid ?? $holdTicket->customer_id) }}" class="text-decoration-none" target="_blank" title="{{ __('ui.view_profile') }}">
                                        {{ $holdTicket->customer->full_name }}
                                    </a>
                                @else
                                    {{ __('ui.guest_customer') }}
                                @endif
                            </span>
                            <span><i class="bi bi-door-open me-1"></i>{{ $holdTicket->room->name ?? '' }}</span>
                            <span class="on-hold-timer" data-hold-since="{{ $holdTicket->hold_at->toIso8601String() }}">
                                <i class="bi bi-clock me-1"></i>
                                <span class="timer-value">{{ $holdTicket->hold_at->diffForHumans() }}</span>
                            </span>
                        </div>
                        @if($holdTicket->hold_note)
                            <div class="text-muted small mt-1 fst-italic">
                                <i class="bi bi-chat-text me-1"></i>{{ $holdTicket->hold_note }}
                            </div>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <button class="sq-mini-btn btn-resume ripple" onclick="resumeTicket({{ $holdTicket->id }})" title="{{ __('ui.resume_service') }}">
                            <i class="bi bi-play-circle-fill"></i>
                        </button>
                        <button class="sq-mini-btn btn-cancel-hold ripple" onclick="cancelHoldTicket({{ $holdTicket->id }})" title="{{ __('ui.cancel_hold') }}">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── New Ticket Drawer (Offcanvas) ────────────────────────────────── --}}
    <div class="offcanvas offcanvas-end ticket-drawer" tabindex="-1" id="newTicketDrawer" aria-labelledby="newTicketDrawerLabel" style="z-index: 10001">
        <div class="offcanvas-header glass-drawer-header border-bottom text-white">
            <h5 class="offcanvas-title fw-bold" id="newTicketDrawerLabel">
                <i class="bi bi-ticket-detailed me-2"></i>{{ __('ui.new_ticket') }}
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form id="new-ticket-form" onsubmit="createTicket(event)">
                <div class="mb-4">
                    <label class="form-label fw-semibold text-secondary small">{{ __('ui.service_type') }}</label>
                    <select class="form-select form-control-custom" id="service_id" required>
                        <option value="">{{ __('ui.select_service') }}</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}">{{ $service->name }} ({{ __('ui.prefix_label', ['prefix' => $service->prefix ?? substr($service->name, 0, 1)]) }})</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-semibold text-secondary small">{{ __('ui.customer') }}</label>
                    <x-customer-search id="customer_id" name="customer_id" required="true" />
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold text-secondary small">{{ __('ui.assigned_room') }}</label>
                    <select class="form-select form-control-custom" id="room_id" required>
                        <option value="">{{ __('ui.select_room') }}</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-4">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" id="is_vip" role="switch">
                        <label class="form-check-label fw-semibold text-danger" for="is_vip">
                            <i class="bi bi-star-fill me-1"></i> {{ __('ui.vip_status') }}
                        </label>
                    </div>
                </div>
                
                <div class="mt-5">
                    <button type="submit" class="btn btn-success w-100 py-3 fw-bold text-white shadow-sm" id="btn-create" style="background: var(--primary-gradient); border: none;">
                        <i class="bi bi-ticket-detailed me-2"></i> {{ __('ui.issue_ticket') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Sound Alert --}}
    <audio id="notification-sound" preload="auto">
        <source src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" type="audio/mpeg">
    </audio>
</div>

{{-- ── Timeline Offcanvas ─────────────────────────────────────────────── --}}
<div class="offcanvas offcanvas-end timeline-offcanvas" tabindex="-1" id="timelineOffcanvas">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold">
            <i class="bi bi-clock-history me-2 text-primary"></i>
            <span id="timeline-room-name"></span> — {{ __('ui.queue_history') }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        {{-- Stats Summary --}}
        <div class="timeline-stats">
            <div class="stat-card completed">
                <i class="bi bi-check-circle-fill"></i>
                <span class="stat-number" id="stat-completed">0</span>
                <span class="stat-label">{{ __('ui.completed') }}</span>
            </div>
            <div class="stat-card cancelled">
                <i class="bi bi-x-circle-fill"></i>
                <span class="stat-number" id="stat-cancelled">0</span>
                <span class="stat-label">{{ __('ui.cancelled') }}</span>
            </div>
            <div class="stat-card skipped">
                <i class="bi bi-skip-forward-circle-fill"></i>
                <span class="stat-number" id="stat-skipped">0</span>
                <span class="stat-label">{{ __('ui.no_show') }}</span>
            </div>
        </div>

        <div class="px-3 py-2 border-bottom bg-light d-flex justify-content-between align-items-center">
            <span class="small fw-bold text-secondary text-uppercase">{{ __('ui.recent_activity') }}</span>
            <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-link text-primary p-0 text-decoration-none fw-bold small">
                {{ __('ui.view_full_history') }} <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        {{-- Timeline --}}
        <div class="timeline-list" id="timeline-list">
            {{-- Populated by JS --}}
        </div>
    </div>
</div>

{{-- ── Change Room Modal ─────────────────────────────────────────────── --}}
<div class="modal fade" id="changeRoomModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">{{ __('ui.change_room') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="change-room-ticket-id">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">{{ __('ui.select_new_room') }}</label>
                    <select class="form-select form-control-custom" id="new-room-id">
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-primary w-100 rounded-3 fw-bold" onclick="executeRoomChange()">
                    {{ __('ui.transfer') }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Put On Hold Modal ───────────────────────────────────────────────── --}}
<div class="modal fade" id="holdModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-warning bg-opacity-10 text-warning p-2 rounded-3">
                        <i class="bi bi-pause-circle-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold mb-0">{{ __('ui.put_on_hold') }}</h6>
                        <small class="text-muted" id="hold-ticket-label"></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="hold-ticket-id">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">{{ __('ui.hold_reason') }} *</label>
                    <select class="form-select form-control-custom" id="hold-reason" required>
                        <option value="">{{ __('ui.select_reason') }}</option>
                        <option value="payment">{{ __('ui.hold_reason_payment') }}</option>
                        <option value="documents">{{ __('ui.hold_reason_documents') }}</option>
                        <option value="medical">{{ __('ui.hold_reason_medical') }}</option>
                        <option value="temporary_leave">{{ __('ui.hold_reason_temporary_leave') }}</option>
                        <option value="manager_approval">{{ __('ui.hold_reason_manager_approval') }}</option>
                        <option value="other">{{ __('ui.hold_reason_other') }}</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">{{ __('ui.hold_note') }}</label>
                    <textarea class="form-control form-control-custom" id="hold-note" rows="2" placeholder="{{ __('ui.hold_note_placeholder') }}"></textarea>
                </div>
                <button class="btn w-100 rounded-3 fw-bold text-white py-2" onclick="executeHold()" style="background: linear-gradient(135deg, #f97316, #ea580c);">
                    <i class="bi bi-pause-circle-fill me-2"></i>{{ __('ui.confirm_hold') }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Cancel Ticket Modal ────────────────────────────────────────────── --}}
<div class="modal fade" id="cancelTicketModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-danger bg-opacity-10 text-danger p-2 rounded-3">
                        <i class="bi bi-x-circle-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold mb-0">{{ __('ui.confirm_cancel_ticket') }}</h6>
                        <small class="text-muted" id="cancel-ticket-label"></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cancel-ticket-id">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">{{ __('ui.cancellation_reason') }} *</label>
                    <select class="form-select form-control-custom" id="cancel-reason" required>
                        <option value="">{{ __('ui.select_reason') }}</option>
                        <option value="Customer Requested Cancellation">{{ __('ui.cancel_reason_customer_requested_cancellation') }}</option>
                        <option value="Customer Left">{{ __('ui.cancel_reason_customer_left') }}</option>
                        <option value="Duplicate Ticket">{{ __('ui.cancel_reason_duplicate_ticket') }}</option>
                        <option value="Wrong Service">{{ __('ui.cancel_reason_wrong_service') }}</option>
                        <option value="No Longer Needed">{{ __('ui.cancel_reason_no_longer_needed') }}</option>
                        <option value="Other">{{ __('ui.cancel_reason_other') }}</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">{{ __('ui.notes') }}</label>
                    <textarea class="form-control form-control-custom" id="cancel-note" rows="2" placeholder="{{ __('ui.cancel_note_placeholder') }}"></textarea>
                </div>
                <div class="alert alert-warning py-2 small">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    {{ __('ui.cancel_confirm_text') }}
                </div>
                <button class="btn w-100 rounded-3 fw-bold text-white py-2" onclick="executeCancel()" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                    <i class="bi bi-x-circle-fill me-2"></i>{{ __('ui.cancel_confirm_btn') }}
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const companyId = "{{ auth()->user()->company_id }}";

    // Sound alert
    function playNotification() {
        const audio = document.getElementById('notification-sound');
        if (audio) {
            audio.play().catch(e => console.log('Autoplay blocked. User interaction required.'));
        }
    }

    // Modern Toast Helper for Advanced View
    function showToastAlert(msg, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = `glass-card border-0 shadow-xl p-3 mb-2 animate__animated animate__fadeInRight animate__faster d-flex align-items-center gap-3`;
        toast.style.minWidth = '300px';
        toast.style.background = 'var(--bs-body-bg)';
        toast.style.color = 'var(--bs-body-color)';
        toast.style.borderRadius = '12px';
        toast.style.boxShadow = '0 10px 30px rgba(0,0,0,0.1)';
        toast.style.border = '1px solid rgba(0,0,0,0.05)';
        
        const icon = type === 'success' 
            ? 'check-circle-fill text-success' 
            : (type === 'danger' ? 'exclamation-circle-fill text-danger' : 'info-circle-fill text-info');
        
        toast.innerHTML = `
            <i class="bi bi-${icon} fs-4"></i>
            <div class="fw-bold small">${msg}</div>
        `;
        
        container.appendChild(toast);
        setTimeout(() => {
            toast.classList.replace('animate__fadeInRight', 'animate__fadeOutRight');
            setTimeout(() => toast.remove(), 500);
        }, 4000);
    }

    // AJAX Refresh Dashboard Function (HTML Swapping)
    let isRefreshing = false;
    function refreshAdvancedQueue(highlightRoomId = null) {
        if (isRefreshing) return;
        isRefreshing = true;
        
        fetch(window.location.href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(htmlText => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(htmlText, 'text/html');
            
            // Swap Room Lanes Grid
            const newGrid = doc.querySelector('.room-lanes-grid');
            const oldGrid = document.querySelector('.room-lanes-grid');
            if (newGrid && oldGrid) {
                oldGrid.innerHTML = newGrid.innerHTML;
                
                // Re-initialize Drag & Drop
                initSortableLanes();
            }

            // Swap On Hold Section
            const newHoldSection = doc.querySelector('#on-hold-section');
            const oldHoldSection = document.querySelector('#on-hold-section');
            if (newHoldSection && oldHoldSection) {
                oldHoldSection.outerHTML = newHoldSection.outerHTML;
            } else if (newHoldSection && !oldHoldSection) {
                // Insert before the ticket drawer
                const drawer = document.querySelector('#newTicketDrawer');
                if (drawer) {
                    drawer.insertAdjacentHTML('beforebegin', newHoldSection.outerHTML);
                }
            } else if (!newHoldSection && oldHoldSection) {
                oldHoldSection.remove();
            }

            // Extract & Update Global History Data
            const scripts = doc.querySelectorAll('script');
            scripts.forEach(script => {
                if (script.textContent.includes('historyData')) {
                    const match = script.textContent.match(/const historyData\s*=\s*({[\s\S]+?});/);
                    if (match && match[1]) {
                        try {
                            window.historyData = JSON.parse(match[1]);
                        } catch (e) {
                            console.error('Failed to parse history data:', e);
                        }
                    }
                }
            });
            
            // Highlight updated room lane if specified
            if (highlightRoomId) {
                const lane = document.getElementById(`room-lane-${highlightRoomId}`);
                if (lane) {
                    lane.classList.add('lane-highlight');
                    setTimeout(() => lane.classList.remove('lane-highlight'), 1000);
                }
            }
            
            isRefreshing = false;
        })
        .catch(err => {
            console.error('Error refreshing queue:', err);
            isRefreshing = false;
        });
    }

        document.addEventListener('DOMContentLoaded', () => {
    // Realtime Listener using Reverb WebSockets
    if (typeof Echo !== 'undefined' && companyId) {
        console.log('Reverb available');
        Echo.channel(`queue.company.${companyId}`)
            .listen('.ticket.called', (e) => {
                console.log('Ticket Called:', e);
                //playNotification();
                //showToastAlert('Ticket Called: ' + (e.ticket?.ticket_number || ''), 'info');
                refreshAdvancedQueue(e.ticket?.room_id);
            })
            .listen('.ticket.created', (e) => {
                //showToastAlert('New Ticket Issued: ' + (e.ticket?.ticket_number || ''), 'success');
                refreshAdvancedQueue(e.ticket?.room_id);
            })
            .listen('.ticket.updated', (e) => {
                refreshAdvancedQueue(e.ticket?.room_id);
            });
    }
    else{
        console.log('Reverb not available ');
    }
 });
    // History data from PHP (initially)
    window.historyData = @json($history->groupBy('room_id'));
    
    // Sortable JS Initialization Function
    function initSortableLanes() {
        document.querySelectorAll('[id^="queue-list-"]').forEach(el => {
            if (Sortable.get(el)) {
                Sortable.get(el).destroy();
            }
            new Sortable(el, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: function (evt) {
                    const targetEl = evt.to;
                    Swal.fire({
                        title: '{{ __("ui.confirm_reorder") }}',
                        text: '{{ __("ui.confirm_reorder_text") }}',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: 'var(--primary-color)',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: '{{ __("ui.yes_apply") }}',
                        cancelButtonText: '{{ __("ui.cancel") }}'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let order = [];
                            targetEl.querySelectorAll('.queue-list-item').forEach(item => {
                                order.push(item.getAttribute('data-id'));
                            });
                            fetch('{{ route("queue.reorder") }}', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                                body: JSON.stringify({ order: order })
                            }).then(response => {
                                if (response.ok) {
                        showToastAlert('{{ __('ui.queue_order_updated') }}', 'success');
                                    refreshAdvancedQueue();
                                }
                            });
                        } else {
                            refreshAdvancedQueue();
                        }
                    });
                }
            });
        });
    }

    // Initial load call
    document.addEventListener('DOMContentLoaded', () => {
        initSortableLanes();
    });

    // Open Timeline Offcanvas for a room
    function openTimeline(roomId, roomName) {
        document.getElementById('timeline-room-name').textContent = roomName;
        const items = window.historyData[roomId] || [];
        
        // Stats
        let completed = 0, cancelled = 0, skipped = 0;
        items.forEach(t => {
            if (t.status === 'done' || t.status === 'completed') completed++;
            else if (t.status === 'cancelled') cancelled++;
            else if (t.status === 'no_show' || t.status === 'skipped') skipped++;
        });
        document.getElementById('stat-completed').textContent = completed;
        document.getElementById('stat-cancelled').textContent = cancelled;
        document.getElementById('stat-skipped').textContent = skipped;

        // Build timeline
        const list = document.getElementById('timeline-list');
        if (items.length === 0) {
            list.innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class="bi bi-inbox d-block mb-2" style="font-size:2rem;opacity:.4"></i>
                    <small>{{ __('ui.no_history_today') }}</small>
                </div>`;
        } else {
            list.innerHTML = items.map(t => {
                const statusConfig = {
                    done:      { icon: 'check-circle-fill', color: '#22c55e', label: '{{ __("ui.completed") }}' },
                    completed: { icon: 'check-circle-fill', color: '#22c55e', label: '{{ __("ui.completed") }}' },
                    cancelled: { icon: 'x-circle-fill', color: '#ef4444', label: '{{ __("ui.cancelled") }}' },
                    no_show:   { icon: 'skip-forward-circle-fill', color: '#f59e0b', label: '{{ __("ui.no_show") }}' },
                    skipped:   { icon: 'skip-forward-circle-fill', color: '#f59e0b', label: '{{ __("ui.no_show") }}' },
                    on_hold:   { icon: 'pause-circle-fill', color: '#f97316', label: '{{ __("ui.on_hold") }}' },
                };
                const cfg = statusConfig[t.status] || statusConfig.done;
                const source = t.source === 'appointment'
                    ? '<span class="badge bg-success-subtle text-success" style="font-size:.6rem"><i class="bi bi-calendar-check me-1"></i>{{ __("ui.source_appointment") }}</span>'
                    : '<span class="badge bg-secondary-subtle text-secondary" style="font-size:.6rem"><i class="bi bi-person-walking me-1"></i>{{ __("ui.source_walk_in") }}</span>';
                const vip = t.is_vip ? '<span class="vip-badge ms-1">' + "{{ __('ui.vip') }}" + '</span>' : '';
                const time = t.finished_at ? new Date(t.finished_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit', timeZone: '{{ app(\App\Services\TimezoneService::class)->resolve() }}'}) : '';
                const created = new Date(t.created_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit', timeZone: '{{ app(\App\Services\TimezoneService::class)->resolve() }}'});
                const customerName = t.customer ? t.customer.full_name : '{{ __("ui.guest_customer") }}';
                const serviceName = t.service ? t.service.name : '';

                return `
                <div class="timeline-item">
                    <div class="timeline-dot" style="background:${cfg.color}">
                        <i class="bi bi-${cfg.icon}"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <strong>${t.ticket_number}</strong> ${vip}
                            <span class="badge rounded-pill" style="background:${cfg.color}15;color:${cfg.color};font-size:.65rem">${cfg.label}</span>
                            ${source}
                        </div>
                        <div class="text-secondary small">
                            <i class="bi bi-person me-1"></i>${customerName}
                            ${serviceName ? `<span class="ms-2"><i class="bi bi-tag me-1"></i>${serviceName}</span>` : ''}
                        </div>
                        <div class="text-muted small mt-1">
                            <i class="bi bi-clock me-1"></i>${created} → ${time}
                            ${t.priority_score > 0 ? `<span class="ms-2 badge score-badge" style="font-size:.55rem"><i class="bi bi-lightning-charge-fill"></i> ${t.priority_score}</span>` : ''}
                        </div>
                    </div>
                </div>`;
            }).join('');
        }

        new bootstrap.Offcanvas(document.getElementById('timelineOffcanvas')).show();
    }

    // Create a new Ticket via AJAX (no reload)
    function createTicket(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-create');
        btn.disabled = true;
        const origHTML = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> {{ __("ui.loading") }}';
        
        const payload = {
            service_id: document.getElementById('service_id').value,
            customer_id: document.getElementById('customer_id').value,
            room_id: document.getElementById('room_id').value,
            is_vip: document.getElementById('is_vip').checked ? 1 : 0
        };

        fetch('{{ route("queue.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = origHTML;
            if(data.success) {
                // Reset form
                document.getElementById('new-ticket-form').reset();
                
                // Hide offcanvas drawer
                const drawerEl = document.getElementById('newTicketDrawer');
                const drawerInstance = bootstrap.Offcanvas.getInstance(drawerEl) || new bootstrap.Offcanvas(drawerEl);
                drawerInstance.hide();
                
                showToastAlert('{{ __('ui.ticket_issued_successfully') }}', 'success');
                refreshAdvancedQueue(payload.room_id);
            }
            else {
                showToastAlert(data.message || '{{ __('ui.error_creating_ticket') }}', 'danger');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = origHTML;
        });
    }

    // Call Next Ticket for a specific room (no reload)
    function callNextTicket(roomId) {
        const btn = document.querySelector(`#room-lane-${roomId} .btn-next`);
        if (btn) {
            btn.disabled = true;
        }

        fetch('{{ route("queue.call") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ room_id: roomId })
        })
        .then(r => r.json())
        .then(data => {
            if (btn) btn.disabled = false;
            if(data.success) {
                showToastAlert('{{ __('ui.calling_ticket') }}: ' + data.ticket.ticket_number, 'success');
                refreshAdvancedQueue(roomId);
            }
            else {
                showToastAlert(data.message || '{{ __('ui.no_tickets_waiting') }}', 'danger');
            }
        })
        .catch(() => {
            if (btn) btn.disabled = false;
        });
    }

    // Complete Current Ticket (no reload)
    function completeTicket(id) {
        fetch(`/queue/${id}/status`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ status: 'done' })
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                showToastAlert('{{ __('ui.ticket_completed_success') }}', 'success');
                refreshAdvancedQueue(data.ticket.room_id);
            }
        });
    }

    // Pass (Skip) Current Ticket (no reload)
    function passTicket(id) {
        fetch(`/queue/${id}/status`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ status: 'no_show' })
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                showToastAlert('{{ __('ui.ticket_skipped_success') }}', 'info');
                refreshAdvancedQueue(data.ticket.room_id);
            }
        });
    }

    // Change Room Modal logic
    const changeRoomModal = new bootstrap.Modal(document.getElementById('changeRoomModal'));
    
    function showChangeRoomModal(ticketId, currentRoomId) {
        document.getElementById('change-room-ticket-id').value = ticketId;
        document.getElementById('new-room-id').value = currentRoomId;
        changeRoomModal.show();
    }

    function executeRoomChange() {
        const ticketId = document.getElementById('change-room-ticket-id').value;
        const roomId = document.getElementById('new-room-id').value;
        
        fetch(`/queue/${ticketId}/room`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ room_id: roomId })
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                changeRoomModal.hide();
                showToastAlert('{{ __('ui.ticket_transferred_successfully') }}', 'success');
                refreshAdvancedQueue();
            } else {
                showToastAlert('{{ __('ui.error_transferring_ticket') }}', 'danger');
            }
        });
    }

    // ── Hold / Resume Functions ──────────────────────────────────────────
    const holdModal = new bootstrap.Modal(document.getElementById('holdModal'));

    function showHoldModal(ticketId, ticketNumber) {
        document.getElementById('hold-ticket-id').value = ticketId;
        document.getElementById('hold-ticket-label').textContent = '{{ __("ui.ticket") }}: ' + ticketNumber;
        document.getElementById('hold-reason').value = '';
        document.getElementById('hold-note').value = '';
        holdModal.show();
    }

    function executeHold() {
        const ticketId = document.getElementById('hold-ticket-id').value;
        const reason = document.getElementById('hold-reason').value;
        const note = document.getElementById('hold-note').value;

        if (!reason) {
            showToastAlert('{{ __("ui.select_reason") }}', 'danger');
            return;
        }

        fetch(`/queue/${ticketId}/hold`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ reason: reason, note: note })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                holdModal.hide();
                showToastAlert('{{ __("ui.ticket_on_hold_success") }}', 'success');
                refreshAdvancedQueue(data.ticket?.room_id);
            } else {
                showToastAlert(data.message || '{{ __("ui.ticket_hold_error") }}', 'danger');
            }
        })
        .catch(() => {
            showToastAlert('{{ __("ui.ticket_hold_error") }}', 'danger');
        });
    }

    function resumeTicket(ticketId) {
        Swal.fire({
            title: '{{ __("ui.resume_service") }}',
            text: '{{ __("ui.resume_confirm_text") }}',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#8b5cf6',
            cancelButtonColor: '#64748b',
            confirmButtonText: '{{ __("ui.resume_confirm_btn") }}',
            cancelButtonText: '{{ __("ui.cancel") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/queue/${ticketId}/resume`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({})
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToastAlert('{{ __("ui.ticket_resumed_success") }}', 'success');
                        refreshAdvancedQueue(data.ticket?.room_id);
                    } else {
                        showToastAlert(data.message || '{{ __("ui.ticket_resume_error") }}', 'danger');
                    }
                })
                .catch(() => {
                    showToastAlert('{{ __("ui.ticket_resume_error") }}', 'danger');
                });
            }
        });
    }

    function cancelHoldTicket(ticketId) {
        Swal.fire({
            title: '{{ __("ui.cancel_hold") }}',
            text: '{{ __("ui.cancel_hold_confirm_text") }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: '{{ __("ui.cancel_hold_confirm_btn") }}',
            cancelButtonText: '{{ __("ui.cancel") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/queue/${ticketId}/cancel-hold`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({})
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToastAlert('{{ __("ui.ticket_hold_cancelled_success") }}', 'success');
                        refreshAdvancedQueue(data.ticket?.room_id);
                    } else {
                        showToastAlert(data.message || '{{ __("ui.ticket_hold_cancelled_error") }}', 'danger');
                    }
                })
                .catch(() => {
                    showToastAlert('{{ __("ui.ticket_hold_cancelled_error") }}', 'danger');
                });
            }
        });
    }

    // ── Cancel Ticket Functions ──────────────────────────────────────────
    const cancelTicketModal = new bootstrap.Modal(document.getElementById('cancelTicketModal'));

    function showCancelModal(ticketId, ticketNumber) {
        document.getElementById('cancel-ticket-id').value = ticketId;
        document.getElementById('cancel-ticket-label').textContent = '{{ __("ui.ticket") }}: ' + ticketNumber;
        document.getElementById('cancel-reason').value = '';
        document.getElementById('cancel-note').value = '';
        cancelTicketModal.show();
    }

    function executeCancel() {
        const ticketId = document.getElementById('cancel-ticket-id').value;
        const reason = document.getElementById('cancel-reason').value;
        const note = document.getElementById('cancel-note').value;

        if (!reason) {
            showToastAlert('{{ __("ui.select_reason") }}', 'danger');
            return;
        }

        fetch(`/queue/${ticketId}/cancel`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ reason: reason, note: note })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                cancelTicketModal.hide();
                showToastAlert('{{ __("ui.ticket_cancelled_success") }}', 'success');
                refreshAdvancedQueue(data.ticket?.room_id);
            } else {
                showToastAlert(data.message || '{{ __("ui.ticket_cancelled_error") }}', 'danger');
            }
        })
        .catch(() => {
            showToastAlert('{{ __("ui.ticket_cancelled_error") }}', 'danger');
        });
    }

    // Live timer for on-hold cards
    function updateHoldTimers() {
        document.querySelectorAll('.on-hold-timer[data-hold-since]').forEach(el => {
            const holdSince = new Date(el.dataset.holdSince);
            const now = new Date();
            const diffMs = now - holdSince;
            const mins = Math.floor(diffMs / 60000);
            const secs = Math.floor((diffMs % 60000) / 1000);
            const timerVal = el.querySelector('.timer-value');
            if (timerVal) {
                timerVal.textContent = `${mins}m ${secs.toString().padStart(2, '0')}s`;
            }
        });
    }
    setInterval(updateHoldTimers, 1000);
</script>
@endpush
