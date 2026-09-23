@extends('layouts.dashboard')

@section('title', 'Simple Queue | Noubtigo')
@section('header_title', __('ui.simple_queue'))
@section('header_subtitle', __('ui.simple_queue_subtitle'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/simple-queue.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="container-fluid px-0">

    {{-- ── Daily Code + Stats Row ──────────────────────────────────────── --}}
    <div class="row g-4 mb-5 slide-up stagger-1">
        <div class="col-xl-4 col-lg-5">
            <div class="sq-daily-code h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-box bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <div>
                        <div class="fw-bold small text-uppercase letter-spacing-1">{{ __('ui.company_code') }}</div>
                        <div class="text-muted small">{{ __('ui.share_with_customers') }}</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="sq-daily-code-value">{{ $companyCode }}</span>
                    <button class="btn btn-light rounded-circle ripple p-2" onclick="navigator.clipboard.writeText('{{ $companyCode }}'); showToast('{{ __('ui.copied_to_clipboard') }}')" title="{{ __('ui.copy') }}">
                        <i class="bi bi-copy"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="col-xl-8 col-lg-7">
            <div class="row g-3 h-100">
                <div class="col-md-4">
                    <div class="sq-stat-card h-100">
                        <div class="sq-stat-value" id="stat-tickets-today">{{ $stats['tickets_today'] }}</div>
                        <div class="sq-stat-label">{{ __('ui.tickets_today') }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="sq-stat-card h-100">
                        <div class="sq-stat-value" id="stat-avg-wait">{{ $stats['avg_wait'] }}<span style="font-size:.9rem" class="ms-1">{{ __('ui.mins') }}</span></div>
                        <div class="sq-stat-label">{{ __('ui.avg_waiting_time') }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="sq-stat-card h-100">
                        <div class="sq-stat-value" id="stat-served-today">{{ $stats['served_today'] }}</div>
                        <div class="sq-stat-label">{{ __('ui.served_count') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Quick Ticket Form ───────────────────────────────────────────── --}}
    <div class="sq-ticket-form mb-5 slide-up stagger-2">
        <form id="simple-ticket-form" onsubmit="createSimpleTicket(event)">
            <div class="row g-4 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted small">
                        <i class="bi bi-briefcase-fill me-2"></i>{{ __('ui.service_type') }}
                    </label>
                    <select class="form-select shadow-none" id="sq-service">
                        <option value="">{{ __('ui.select_service') }}</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted small">
                        <i class="bi bi-telephone me-2"></i>{{ __('ui.phone_number') }} 
                    </label>
                    <input type="tel" class="form-control shadow-none" id="sq-phone" placeholder="{{ __('ui.phone_example') }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="sq-issue-btn ripple py-3" id="sq-submit-btn">
                        <i class="bi bi-ticket-detailed me-2"></i>{{ __('ui.issue_ticket') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="row g-5 slide-up stagger-3">
        {{-- ── Now Serving + Actions ───────────────────────────────────── --}}
        <div class="col-lg-5">
            <div class="sq-serving-card {{ $serving ? 'has-ticket' : '' }}" id="sq-serving-card">
                <div class="sq-serving-label">
                    <i class="bi bi-broadcast me-2"></i> {{ __('ui.now_serving') }}
                </div>

                @if($serving)
                    <div class="sq-serving-number" id="sq-serving-number">{{ $serving->ticket_number }}</div>
                    <div class="sq-serving-service shadow-sm">
                        {{ $serving->service?->name ?? __('ui.general_queue') }}
                        @if($serving->customer_identifier)
                            <span class="ms-2 opacity-75">• {{ $serving->customer_identifier }}</span>
                        @endif
                    </div>
                @else
                    <div class="sq-serving-number text-white text-opacity-25">---</div>
                    <div class="sq-serving-service bg-white bg-opacity-10 border-0 shadow-none">
                        {{ __('ui.no_active_ticket') }}
                    </div>
                @endif
            </div>

            {{-- Action Buttons --}}
            <div class="sq-actions" id="sq-actions">
                <button class="sq-action-btn btn-next ripple" onclick="callNext()" id="btn-next">
                    <i class="bi bi-skip-forward-fill"></i>
                    <span>{{ __('ui.next') }}</span>
                </button>
                <button class="sq-action-btn btn-pass ripple" onclick="passTicket()" id="btn-pass" {{ !$serving ? 'disabled' : '' }} data-ticket-id="{{ $serving->id ?? '' }}">
                    <i class="bi bi-arrow-right-circle-fill"></i>
                    <span>{{ __('ui.pass') }}</span>
                </button>
                <button class="sq-action-btn btn-done ripple" onclick="doneTicket()" id="btn-done" {{ !$serving ? 'disabled' : '' }} data-ticket-id="{{ $serving->id ?? '' }}">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>{{ __('ui.done_btn') }}</span>
                </button>
            </div>
        </div>

        {{-- ── Waiting List ────────────────────────────────────────────── --}}
        <div class="col-lg-7">
            <div class="sq-waiting-list h-100" id="sq-waiting-list">
                <div class="sq-waiting-header">
                    <h6 class="mb-0">
                        <i class="bi bi-people-fill text-primary"></i>
                        {{ __('ui.waiting_queue') }}
                    </h6>
                    <span class="sq-waiting-count shadow-sm" id="sq-waiting-count">{{ $waiting->count() }}</span>
                </div>

                <div class="waiting-items-container" style="max-height: 500px; overflow-y: auto;">
                    @forelse($waiting as $index => $ticket)
                        <div class="sq-waiting-item animate__animated animate__fadeInUp animate__faster" style="animation-delay: {{ $index * 0.05 }}s">
                            <div class="sq-waiting-position shadow-sm">{{ $index + 1 }}</div>
                            <div class="flex-grow-1">
                                <div class="sq-waiting-ticket">{{ $ticket->ticket_number }}</div>
                                <div class="sq-waiting-service d-flex align-items-center gap-2">
                                    {{ $ticket->service?->name ?? __('ui.general_queue') }}
                                    @if($ticket->customer_identifier)
                                        <span class="badge bg-light text-dark fw-normal" style="font-size: 0.7rem;">{{ $ticket->customer_identifier }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="sq-waiting-time d-flex align-items-center gap-2">
                                    <i class="bi bi-clock-history text-primary"></i>
                                    {{ $ticket->waited_since->diffForHumans(null, true, true) }}
                                </div>
                                @permission('queue.delete')
                                <button class="btn btn-sm btn-link text-danger p-0 border-0" onclick="showCancelModal({{ $ticket->id }}, '{{ $ticket->ticket_number }}')" title="{{ __('ui.cancel_ticket') }}">
                                    <i class="bi bi-x-circle" style="font-size: 1.1rem;"></i>
                                </button>
                                @endpermission
                            </div>
                        </div>
                    @empty
                        <div class="sq-empty-state py-5" style="justify-items: center;">
                            <div class="mb-3">
                                <i class="bi bi-cup-hot text-muted opacity-25" style="font-size: 4rem;"></i>
                            </div>
                            <h5 class="fw-bold text-muted">{{ __('ui.queue_is_empty') }}</h5>
                            <p class="text-muted small">{{ __('ui.no_customers_waiting') }}</p>
                        </div>
                    @endforelse
                </div>
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
                    <select class="form-select" id="cancel-reason" required>
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
                    <textarea class="form-control" id="cancel-note" rows="2" placeholder="{{ __('ui.cancel_note_placeholder') }}"></textarea>
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

{{-- Modern Toast --}}
<div id="toast-container" class="position-fixed top-0 end-0 p-4" style="z-index: 9999;"></div>

{{-- Sound Alert --}}
<audio id="notification-sound" preload="auto">
    <source src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" type="audio/mpeg">
</audio>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const companyId = "{{ auth()->user()->company_id }}";

    // ── Sound Helper ──────────────────────────────────────────────
    function playNotification() {
        const audio = document.getElementById('notification-sound');
        if (audio) {
            audio.play().catch(e => console.log('Autoplay blocked. User interaction required.'));
        }
    }

    // ── Toast Helper ──────────────────────────────────────────────
    function showToast(msg, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `glass-card border-0 shadow-xl p-3 mb-2 animate__animated animate__fadeInRight animate__faster d-flex align-items-center gap-3`;
        toast.style.minWidth = '300px';
        
        let icon = 'check-circle-fill text-success';
        if (type === 'danger') icon = 'exclamation-circle-fill text-danger';
        if (type === 'warning') icon = 'exclamation-triangle-fill text-warning';
        
        toast.innerHTML = `
            <i class="bi bi-${icon} fs-4"></i>
            <div class="fw-bold small">${msg}</div>
        `;
        
        container.appendChild(toast);
        setTimeout(() => {
            toast.classList.replace('animate__fadeInRight', 'animate__fadeOutRight');
            setTimeout(() => toast.remove(), 500);
        }, 5000);
    }

    // ── Dynamic Swapping function for Simple Queue ────────────────
    let isRefreshing = false;
    function refreshSimpleQueue() {
        if (isRefreshing) return;
        isRefreshing = true;
        
        fetch(window.location.href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(htmlText => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(htmlText, 'text/html');
            
            // Swap serving card
            const newServing = doc.getElementById('sq-serving-card');
            const oldServing = document.getElementById('sq-serving-card');
            if (newServing && oldServing) {
                oldServing.innerHTML = newServing.innerHTML;
                if (newServing.classList.contains('has-ticket')) {
                    oldServing.classList.add('has-ticket');
                } else {
                    oldServing.classList.remove('has-ticket');
                }
            }
            
            // Swap actions
            const newActions = doc.getElementById('sq-actions');
            const oldActions = document.getElementById('sq-actions');
            if (newActions && oldActions) {
                oldActions.innerHTML = newActions.innerHTML;
            }
            
            // Swap waiting list items container
            const newItems = doc.querySelector('.waiting-items-container');
            const oldItems = document.querySelector('.waiting-items-container');
            if (newItems && oldItems) {
                oldItems.innerHTML = newItems.innerHTML;
            }
            
            // Swap stats
            ['stat-tickets-today', 'stat-avg-wait', 'stat-served-today', 'sq-waiting-count'].forEach(id => {
                const newVal = doc.getElementById(id);
                const oldVal = document.getElementById(id);
                if (newVal && oldVal) {
                    oldVal.innerHTML = newVal.innerHTML;
                }
            });
            
            isRefreshing = false;
        })
        .catch(err => {
            console.error('Error refreshing simple queue:', err);
            isRefreshing = false;
        });
    }

    // ── Create Simple Ticket ──────────────────────────────────────
    function createSimpleTicket(e) {
        e.preventDefault();
        const btn = document.getElementById('sq-submit-btn');
        const origHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        fetch('{{ route("queue.simple.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                service_id: document.getElementById('sq-service').value || null,
                customer_identifier: document.getElementById('sq-phone').value || null
            })
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = origHTML;
            if (data.success) {
                const phoneVal = document.getElementById('sq-phone').value || '';
                const hasPhone = /\d{6,}/.test(phoneVal);

                if (!hasPhone) {
                    showToast('{{ __("ui.no_phone_warning") }}', 'warning');
                } else {
                    showToast('{{ __("ui.ticket_created_simple") }}');
                }

                document.getElementById('simple-ticket-form').reset();
                refreshSimpleQueue();
            } else {
                showToast(data.message || '{{ __('ui.error_generic') }}', 'danger');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = origHTML;
        });
    }

    // ── Call Next ─────────────────────────────────────────────────
    function callNext() {
        const btn = document.getElementById('btn-next');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        fetch('{{ route("queue.simple.next") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-skip-forward-fill"></i> <span>{{ __("ui.next") }}</span>';
            if (data.success) {
                showToast('{{ __('ui.now_calling_ticket') }}: ' + data.ticket.ticket_number);
                refreshSimpleQueue();
            } else {
                showToast('{{ __("ui.no_tickets_waiting") }}', 'danger');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-skip-forward-fill"></i> <span>{{ __("ui.next") }}</span>';
        });
    }

    // ── Pass & Done ────────────────────────────────────────────────
    function passTicket() {
        const btn = document.getElementById('btn-pass');
        const ticketId = btn.getAttribute('data-ticket-id');
        if (!ticketId) return;
        btn.disabled = true;
        fetch(`/simple/${ticketId}/pass`, { method: 'PATCH', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } })
            .then(r => r.json()).then(data => {
                if (data.success) {
                    showToast('{{ __('ui.ticket_passed_success') }}');
                    refreshSimpleQueue();
                } else {
                    btn.disabled = false;
                }
            });
    }

    function doneTicket() {
        const btn = document.getElementById('btn-done');
        const ticketId = btn.getAttribute('data-ticket-id');
        if (!ticketId) return;
        btn.disabled = true;
        fetch(`/simple/${ticketId}/done`, { method: 'PATCH', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } })
            .then(r => r.json()).then(data => {
                if (data.success) {
                    showToast('{{ __('ui.ticket_completed_success') }}');
                    refreshSimpleQueue();
                } else {
                    btn.disabled = false;
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
            showToast('{{ __("ui.select_reason") }}', 'danger');
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
                showToast('{{ __("ui.ticket_cancelled_success") }}', 'success');
                refreshSimpleQueue();
            } else {
                showToast(data.message || '{{ __("ui.ticket_cancelled_error") }}', 'danger');
            }
        })
        .catch(() => {
            showToast('{{ __("ui.ticket_cancelled_error") }}', 'danger');
        });
    }

    // ── Realtime Listener ─────────────────────────────────────────
    if (typeof Echo !== 'undefined' && companyId) {
        Echo.channel(`queue.company.${companyId}`)
            .listen('.ticket.called', (e) => {
                playNotification();
                showToast('{{ __('ui.ticket_called') }}: ' + (e.ticket?.ticket_number || ''), 'info');
                refreshSimpleQueue();
            })
            .listen('.ticket.created', (e) => {
                showToast('{{ __('ui.new_customer_joined') }}', 'success');
                refreshSimpleQueue();
            })
            .listen('.ticket.updated', (e) => {
                refreshSimpleQueue();
            });
    }

    // ── Stats Refresh ─────────────────────────────────────────────
    setInterval(() => {
        fetch('{{ route("queue.simple.stats") }}', { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                const statTickets = document.getElementById('stat-tickets-today');
                const statServed = document.getElementById('stat-served-today');
                const statWait = document.getElementById('stat-avg-wait');
                const waitingCount = document.getElementById('sq-waiting-count');
                
                if (statTickets) statTickets.textContent = data.tickets_today;
                if (statServed) statServed.textContent = data.served_today;
                if (statWait) statWait.innerHTML = data.avg_wait + '<span style="font-size:.9rem" class="ms-1">{{ __("ui.mins") }}</span>';
                if (waitingCount) waitingCount.textContent = data.waiting_count;
            });
    }, 30000);
</script>
@endpush
