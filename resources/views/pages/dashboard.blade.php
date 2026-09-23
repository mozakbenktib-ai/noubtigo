@extends('layouts.dashboard')

@section('title', __('ui.dashboard') . ' | Noubtigo')
@section('header_title', __('ui.dashboard_subtitle') ?? 'Live Control Center')
@section('header_subtitle', __('ui.dashboard_desc') ?? 'Monitor waiting rooms, dispatch tickets, and check-in appointments in real time.')

@section('content')
<!-- Custom Styles for Premium Live Control Center -->
<style>
    /* Ensure padding inside main layout */
    #dashboard-container {
        padding-top: 1.5rem;
        padding-bottom: 3rem;
    }

    /* Floating, Premium KPI Cards */
    .premium-kpi-card {
        border-radius: var(--radius-lg);
        border: 1px solid rgba(255,255,255,0.2);
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
        transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.4s ease;
        color: white;
    }
    .premium-kpi-card:hover {
        transform: translateY(-6px);
    }
    .premium-kpi-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 60%);
        transform: rotate(30deg);
        pointer-events: none;
    }
    
    .kpi-1 { background: linear-gradient(135deg, #6366f1, #8b5cf6); box-shadow: 0 10px 20px -5px rgba(139, 92, 246, 0.4); }
    .kpi-2 { background: linear-gradient(135deg, #3b82f6, #06b6d4); box-shadow: 0 10px 20px -5px rgba(6, 182, 212, 0.4); }
    .kpi-3 { background: linear-gradient(135deg, #f59e0b, #ef4444); box-shadow: 0 10px 20px -5px rgba(239, 68, 68, 0.4); }
    .kpi-4 { background: linear-gradient(135deg, #10b981, #22c55e); box-shadow: 0 10px 20px -5px rgba(34, 197, 94, 0.4); }

    .kpi-icon {
        font-size: 2rem;
        opacity: 0.8;
        margin-bottom: 0.5rem;
    }

    /* Stunning Glass Panel */
    .premium-panel {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-lg);
        padding: 1.75rem;
        box-shadow: 0 15px 35px rgba(0,0,0,0.03);
        height: 100%;
        position: relative;
    }
    [data-bs-theme="dark"] .premium-panel {
        background: rgba(30, 41, 59, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    }

    .panel-title {
        font-weight: 800;
        font-size: 1.15rem;
        letter-spacing: -0.3px;
        margin-bottom: 0.2rem;
    }

    /* Active Room Card */
    .room-widget {
        background: #ffffff;
        border-radius: var(--radius-md);
        border: 1px solid rgba(0,0,0,0.05);
        padding: 1.25rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }
    [data-bs-theme="dark"] .room-widget {
        background: rgba(15, 23, 42, 0.8);
        border: 1px solid rgba(255,255,255,0.05);
    }
    .room-widget:hover {
        border-color: rgba(6, 182, 212, 0.3);
        box-shadow: 0 10px 25px rgba(6, 182, 212, 0.1);
    }

    .room-status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }
    .status-active { background: #22c55e; box-shadow: 0 0 10px #22c55e; animation: pulseGlow 2s infinite; }
    .status-idle { background: #94a3b8; }

    @keyframes pulseGlow {
        0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); }
        70% { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
        100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }

    /* Ticket Dispenser Polish */
    .dispenser-input {
        background: rgba(241, 245, 249, 0.6);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: var(--radius-sm);
        padding: 0.6rem 1rem;
        font-weight: 500;
        transition: all 0.2s;
    }
    .dispenser-input:focus {
        background: #fff;
        border-color: var(--secondary-color);
        box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.1);
    }
    [data-bs-theme="dark"] .dispenser-input {
        background: rgba(15, 23, 42, 0.5);
        border-color: rgba(255,255,255,0.1);
        color: #fff;
    }
    [data-bs-theme="dark"] .dispenser-input:focus {
        background: rgba(30, 41, 59, 1);
    }

    .btn-gradient {
        background: linear-gradient(135deg, #06b6d4, #3b82f6);
        color: white;
        font-weight: 700;
        border: none;
        border-radius: var(--radius-md);
        box-shadow: 0 8px 15px -5px rgba(6, 182, 212, 0.4);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 20px -5px rgba(6, 182, 212, 0.5);
        color: white;
    }

    /* Smart to-do list */
    .smart-todo-list { display: grid; gap: 0.75rem; }
    .smart-todo-item {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        padding: 0.9rem 1rem;
        background: rgba(248, 250, 252, 0.82);
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: var(--radius-md);
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .smart-todo-item:hover {
        transform: translateX(4px);
        border-color: rgba(6, 182, 212, 0.35);
        box-shadow: 0 8px 18px rgba(14, 116, 144, 0.08);
    }
    .smart-todo-check {
        width: 2rem;
        height: 2rem;
        flex: 0 0 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #bfdbfe;
        border-radius: 0.7rem;
        background: #eff6ff;
        color: #2563eb;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .smart-todo-check:hover { background: #dbeafe; }
    .smart-todo-item.is-complete .smart-todo-check {
        background: #dcfce7;
        border-color: #86efac;
        color: #16a34a;
    }
    .smart-todo-item.is-complete .smart-todo-title,
    .smart-todo-item.is-complete .smart-todo-detail { opacity: 0.55; }
    .smart-todo-item.is-complete .smart-todo-title { text-decoration: line-through; }
    .smart-todo-title { font-size: 0.9rem; font-weight: 750; color: var(--text-color); }
    .smart-todo-detail { font-size: 0.78rem; color: var(--text-muted); margin-top: 0.1rem; }
    .smart-todo-badge { font-size: 0.7rem; font-weight: 700; padding: 0.35rem 0.55rem; border-radius: 999px; }
    .smart-todo-actions { display: flex; gap: 0.35rem; }
    .smart-todo-action { width: 2rem; height: 2rem; padding: 0; border-radius: 0.6rem; display: inline-flex; align-items: center; justify-content: center; }
    .todo-filter { border: 0; background: transparent; color: var(--text-muted); font-size: 0.78rem; font-weight: 700; padding: 0.4rem 0.65rem; border-radius: 999px; }
    .todo-filter.active, .todo-filter:hover { background: #e0f2fe; color: #0284c7; }
    [data-bs-theme="dark"] .smart-todo-item { background: rgba(15, 23, 42, 0.45); border-color: rgba(255,255,255,0.08); }

    .timeline-row {
        position: relative;
        padding: 12px 0 12px 20px;
        border-left: 2px solid rgba(0,0,0,0.05);
    }
    [data-bs-theme="dark"] .timeline-row { border-left-color: rgba(255,255,255,0.1); }
    
    .timeline-row::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 20px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--primary-color);
        border: 2px solid var(--bg-light);
    }
</style>

<div class="container-fluid" id="dashboard-container">
    
    <!-- 1. Glowing KPIs Row -->
    <div class="row g-4 mb-4" id="dashboard-kpis">
        <div class="col-sm-6 col-xl-3">
            <div class="premium-kpi-card kpi-1">
                <i class="bi bi-people-fill kpi-icon"></i>
                <h2 class="fw-bold mb-0 display-6">{{ number_format($totalCustomers) }}</h2>
                <span class="text-white-50 fw-semibold text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">{{ __('ui.total_customers') ?? 'Total Customers' }}</span>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="premium-kpi-card kpi-2">
                <i class="bi bi-calendar-check-fill kpi-icon"></i>
                <h2 class="fw-bold mb-0 display-6">{{ number_format($appointmentsTodayCount) }}</h2>
                <span class="text-white-50 fw-semibold text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">{{ __('ui.todays_appointments') ?? 'Today\'s Appointments' }}</span>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="premium-kpi-card kpi-3">
                <i class="bi bi-hourglass-split kpi-icon"></i>
                <h2 class="fw-bold mb-0 display-6">{{ number_format($queueSize) }}</h2>
                <span class="text-white-50 fw-semibold text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">{{ __('ui.active_queue_size') ?? 'Active Queue Size' }}</span>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="premium-kpi-card kpi-4">
                <i class="bi bi-check-circle-fill kpi-icon"></i>
                <h2 class="fw-bold mb-0 display-6">{{ number_format($servedToday) }}</h2>
                <span class="text-white-50 fw-semibold text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">{{ __('ui.tickets_served_today') ?? 'Tickets Served Today' }}</span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- LEFT COLUMN: Rooms & Feed -->
        <div class="col-xl-8">
            <div class="d-flex flex-column gap-4">
                
                <!-- Rooms Monitor Panel -->
                <div class="premium-panel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="panel-title text-dark">{{ __('ui.rooms_status') ?? 'Consultation Rooms Status' }}</h4>
                            <span class="text-muted small">{{ __('ui.rooms_status_desc') ?? 'Live desk operations and serving duration.' }}</span>
                        </div>
                        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill"><i class="bi bi-broadcast me-1"></i> {{ __('ui.live_monitor') ?? 'Live Monitor' }}</span>
                    </div>

                    <div class="row g-3" id="dashboard-rooms-grid">
                        @foreach($rooms as $room)
                            <div class="col-md-6">
                                <div class="room-widget d-flex flex-column h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-light rounded p-2 text-secondary">
                                                <i class="bi bi-door-open fs-5"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-bold mb-0 text-dark">{{ $room->name }}</h6>
                                            </div>
                                        </div>
                                        <div>
                                            @if($room->activeTicket)
                                                <span class="status-active room-status-dot"></span><span class="small fw-bold text-success">{{ __('ui.serving') ?? 'Serving' }}</span>
                                            @else
                                                <span class="status-idle room-status-dot"></span><span class="small fw-semibold text-secondary">{{ __('ui.idle') ?? 'Idle' }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex-grow-1">
                                        @if($room->activeTicket)
                                            <div class="p-3 bg-light rounded-3 mb-3 border border-success-subtle position-relative overflow-hidden">
                                                <div class="position-absolute top-0 end-0 bg-success text-white fw-bold px-2 py-1" style="border-bottom-left-radius: 8px; font-size: 0.7rem;">
                                                    <i class="bi bi-clock me-1"></i>
                                                    <span data-time-elapsed="{{ ($room->activeTicket->started_at ?? $room->activeTicket->called_at)->valueOf() }}">00:00</span>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="display-6 fw-black text-dark" style="letter-spacing: -1px;">{{ $room->activeTicket->ticket_number }}</div>
                                                    <div>
                                                        <h6 class="fw-bold mb-0 text-dark">{{ $room->activeTicket->customer ? $room->activeTicket->customer->full_name : __('ui.walk_in_guest') }}</h6>
                                                        <small class="text-secondary">{{ $room->activeTicket->service ? $room->activeTicket->service->name : __('ui.general') }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="p-3 bg-light rounded-3 mb-3 text-center border border-dashed border-secondary-subtle">
                                                <span class="text-muted small">{{ __('ui.ready_for_next') ?? 'Ready for next customer' }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                        <span class="small text-secondary fw-semibold">
                                            <i class="bi bi-people me-1"></i> {{ $room->waitingCount }} {{ __('ui.waiting') ?? 'waiting' }}
                                        </span>
                                        @if(auth()->user()->hasPermission('queue.call'))
                                            <button class="btn btn-sm btn-dark rounded-pill px-3 fw-bold" onclick="callNextInRoom({{ $room->id }})">
                                                <i class="bi bi-megaphone me-1"></i> {{ __('ui.call_next') ?? 'Call Next' }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @if($rooms->isEmpty())
                            <div class="col-12 text-center py-5 text-muted">
                                <i class="bi bi-building fs-1 opacity-25 mb-3 d-block"></i>
                                {{ __('ui.no_rooms') ?? 'No rooms configured for this terminal.' }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Smart To-Do List -->
                <div class="premium-panel mt-4">
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                        <div>
                            <h4 class="panel-title text-dark">{{ __('ui.todo_today') }}</h4>
                            <p class="text-muted small mb-0">{{ __('ui.smart_todo_desc') }}</p>
                        </div>
                        <button class="btn btn-gradient btn-sm rounded-pill px-3" type="button" onclick="openTodoModal()"><i class="bi bi-plus-lg me-1"></i>{{ __('ui.todo_add') }}</button>
                    </div>
                    <div class="d-flex flex-wrap gap-1 mb-3" aria-label="{{ __('ui.todo_date_filters') }}">
                        <button class="todo-filter active" type="button" data-todo-filter="all">{{ __('ui.todo_filter_all') }}</button>
                        <button class="todo-filter" type="button" data-todo-filter="past">{{ __('ui.todo_filter_past') }}</button>
                        <button class="todo-filter" type="button" data-todo-filter="today">{{ __('ui.todo_filter_today') }}</button>
                        <button class="todo-filter" type="button" data-todo-filter="upcoming">{{ __('ui.todo_filter_upcoming') }}</button>
                    </div>

                    <div class="smart-todo-list" id="dashboard-todos">
                        @forelse($todos as $todo)
                            @php
                                $priorityClass = ['high' => 'bg-danger-subtle text-danger-emphasis', 'normal' => 'bg-primary-subtle text-primary-emphasis', 'low' => 'bg-secondary-subtle text-secondary-emphasis'][$todo->priority];
                                $dateGroup = $todo->due_date->lt(today()) ? 'past' : ($todo->due_date->isToday() ? 'today' : 'upcoming');
                                $isOwner = $todo->user_id === auth()->id();
                            @endphp
                            <div class="smart-todo-item {{ $todo->is_completed ? 'is-complete' : '' }}" data-date-group="{{ $dateGroup }}">
                                <button class="smart-todo-check" type="button" aria-label="{{ __('ui.todo_mark_complete') }}" aria-pressed="{{ $todo->is_completed ? 'true' : 'false' }}" @if($isOwner) onclick="toggleTodo({{ $todo->id }}, {{ $todo->is_completed ? 'false' : 'true' }})" @else disabled @endif><i class="bi {{ $todo->is_completed ? 'bi-check-lg' : 'bi-circle' }}"></i></button>
                                <div class="flex-grow-1 min-w-0"><div class="smart-todo-title text-truncate">{{ $todo->title }} @if($todo->visibility === 'public')<i class="bi bi-people-fill text-primary ms-1" title="{{ __('ui.todo_public') }}"></i>@endif</div><div class="smart-todo-detail text-truncate">{{ $todo->notes ?: __('ui.todo_due', ['date' => $todo->due_date->translatedFormat('M j')]) }}</div></div>
                                <span class="smart-todo-badge {{ $priorityClass }}">{{ __('ui.todo_priority_' . $todo->priority) }}</span>
                                @if($isOwner)<div class="smart-todo-actions"><button class="btn btn-light border smart-todo-action" type="button" aria-label="{{ __('ui.todo_edit') }}" data-id="{{ $todo->id }}" data-title="{{ $todo->title }}" data-notes="{{ $todo->notes }}" data-priority="{{ $todo->priority }}" data-visibility="{{ $todo->visibility }}" data-due-date="{{ $todo->due_date->format('Y-m-d') }}" onclick="openTodoModalFromButton(this)"><i class="bi bi-pencil"></i></button><button class="btn btn-light border text-danger smart-todo-action" type="button" aria-label="{{ __('ui.todo_delete') }}" onclick="deleteTodo({{ $todo->id }})"><i class="bi bi-trash3"></i></button></div>@endif
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted"><i class="bi bi-check2-circle d-block fs-3 mb-2 text-success"></i>{{ __('ui.todo_empty') }}</div>
                        @endforelse
                    </div>
                    @if($todos->isNotEmpty())<div id="todo-filter-empty" class="text-center py-4 text-muted d-none">{{ __('ui.todo_filter_empty') }}</div>@endif
                </div>

            </div>
        </div>

        <!-- RIGHT COLUMN: Dispenser & Appointments -->
        <div class="col-xl-4">
            <div class="d-flex flex-column gap-4">
                
                <!-- Quick Dispenser -->
                <div class="premium-panel text-center">
                    <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-ticket-perforated-fill fs-2"></i>
                    </div>
                    <h4 class="panel-title text-dark">{{ __('ui.quick_dispatch') ?? 'Quick Dispatch' }}</h4>
                    <p class="text-muted small mb-4">{{ __('ui.issue_ticket_desc') ?? 'Issue a ticket directly into the queue.' }}</p>

                    <form id="quick-dispense-form" class="text-start d-flex flex-column gap-3">
                        @csrf
                        <div>
                            <x-customer-search id="dispenser_customer_id" name="customer_id" :required="true" />
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <select class="form-select dispenser-input" name="service_id" id="dispenser_service_id" required>
                                    <option value="">{{ __('ui.select_service') ?? '-- Service --' }}</option>
                                    @foreach($services as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <select class="form-select dispenser-input" name="room_id" id="dispenser_room_id" required>
                                    <option value="">{{ __('ui.select_room') ?? '-- Room --' }}</option>
                                    @foreach($dispenserRooms as $r)
                                        <option value="{{ $r->id }}">{{ $r->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-check form-switch py-2">
                            <input class="form-check-input" type="checkbox" name="is_vip" id="dispenser_is_vip" value="1">
                            <label class="form-check-label text-secondary small fw-bold" for="dispenser_is_vip">{{ __('ui.prioritize_vip') ?? 'Prioritize (Urgent)' }}</label>
                        </div>
                        <button type="submit" class="btn btn-gradient w-100 py-3 mt-1 d-flex justify-content-center align-items-center gap-2">
                            <i class="bi bi-lightning-charge-fill"></i> {{ __('ui.issue_ticket') ?? 'Issue Ticket Now' }}
                        </button>
                    </form>
                </div>

                <!-- Today's Appointments -->
                <div class="premium-panel">
                    <h4 class="panel-title text-dark mb-1">{{ __('ui.todays_schedule') ?? 'Today\'s Schedule' }}</h4>
                    <p class="text-muted small mb-4">{{ __('ui.checkin_expected_clients') ?? 'Check-in expected clients easily.' }}</p>

                    <div id="dashboard-appointments-list">
                        @forelse($todayAppointments as $app)
                            <div class="timeline-row">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge {{ $app->statusBadge()['class'] }} rounded-pill mb-1" style="font-size: 0.6rem;">
                                            {{ __('ui.' . $app->status) }}
                                        </span>
                                        <h6 class="fw-bold text-dark mb-0 fs-sm">{{ $app->customer ? $app->customer->full_name : $app->customer_name }}</h6>
                                        <div class="small text-muted">
                                            @if($app->appointment_date) @localtime($app->appointment_date, 'h:i A') @endif • {{ $app->service ? $app->service->name : __('ui.service') }}
                                        </div>
                                    </div>
                                    <div>
                                        @if($app->status === 'confirmed' || $app->status === 'pending')
                                            <button class="btn btn-sm btn-outline-info rounded-pill px-3 py-1 fw-bold" onclick="checkinAppointment({{ $app->id }}, this)" style="font-size: 0.7rem;">
                                                {{ __('ui.check_in_btn') ?? 'Check-in' }}
                                            </button>
                                        @elseif($app->status === 'checked_in')
                                            <button class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold shadow-sm" onclick="prefillDispenser({{ json_encode($app) }})" style="font-size: 0.7rem;">
                                                {{ __('ui.dispatch_btn') ?? 'Dispatch' }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 opacity-50">
                                <i class="bi bi-calendar2-check display-4 mb-2 d-block"></i>
                                <span class="small">{{ __('ui.no_pending_appointments') ?? 'No pending appointments for today.' }}</span>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="todoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content border-0 shadow" id="todo-form">
            <div class="modal-header border-0 pb-0"><h5 class="modal-title fw-bold" id="todo-modal-title">{{ __('ui.todo_add') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body pt-3">
                <input type="hidden" id="todo-id">
                <div class="mb-3"><label class="form-label small fw-bold">{{ __('ui.todo_title') }}</label><input class="form-control" id="todo-title" maxlength="160" required autofocus></div>
                <div class="mb-3"><label class="form-label small fw-bold">{{ __('ui.todo_notes') }}</label><textarea class="form-control" id="todo-notes" rows="3" maxlength="1000"></textarea></div>
                <div class="row g-3"><div class="col-6"><label class="form-label small fw-bold">{{ __('ui.todo_priority') }}</label><select class="form-select" id="todo-priority"><option value="high">{{ __('ui.todo_priority_high') }}</option><option value="normal" selected>{{ __('ui.todo_priority_normal') }}</option><option value="low">{{ __('ui.todo_priority_low') }}</option></select></div><div class="col-6"><label class="form-label small fw-bold">{{ __('ui.todo_due_date') }}</label><input type="date" class="form-control" id="todo-due-date" value="{{ now()->format('Y-m-d') }}" required></div></div>
                <div class="mt-3"><label class="form-label small fw-bold">{{ __('ui.todo_visibility') }}</label><select class="form-select" id="todo-visibility"><option value="private">{{ __('ui.todo_private') }}</option><option value="public">{{ __('ui.todo_public') }}</option></select><div class="form-text">{{ __('ui.todo_visibility_help') }}</div></div>
            </div>
            <div class="modal-footer border-0"><button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('ui.cancel') }}</button><button type="submit" class="btn btn-gradient px-4">{{ __('ui.todo_save') }}</button></div>
        </form>
    </div>
</div>

<!-- Modal for printing ticket confirmation -->
<div class="modal fade" id="ticketPrintedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-4 text-center">
                <div class="rounded-circle bg-success-subtle text-success mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="bi bi-check-circle-fill fs-2"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1">Success!</h4>
                <p class="text-secondary small mb-4">Ticket printed successfully.</p>
                
                <div class="p-3 bg-light rounded-3 text-start mb-4">
                    <h2 class="fw-bold text-primary text-center mb-3 display-6" id="modal-ticket-number">A-001</h2>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Client:</span>
                        <span class="fw-bold text-dark small" id="modal-ticket-customer">-</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Service:</span>
                        <span class="fw-bold text-dark small" id="modal-ticket-service">-</span>
                    </div>
                </div>
                <button class="btn btn-primary w-100 rounded-pill py-2 fw-bold" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function startDurationTimers() {
        document.querySelectorAll('[data-time-elapsed]').forEach(el => {
            const startVal = el.getAttribute('data-time-elapsed');
            if (!startVal) return;
            const startTime = parseInt(startVal, 10);
            function updateTimer() {
                const now = new Date().getTime();
                const diffMs = now - startTime;
                if (diffMs < 0) { el.textContent = "00:00"; return; }
                const diffSecs = Math.floor(diffMs / 1000);
                el.textContent = String(Math.floor(diffSecs / 60)).padStart(2, '0') + ':' + String(diffSecs % 60).padStart(2, '0');
            }
            updateTimer();
            setInterval(updateTimer, 1000);
        });
    }

    const todoModalElement = document.getElementById('todoModal');
    const todoModal = () => bootstrap.Modal.getOrCreateInstance(todoModalElement);

    function openTodoModal(todo = null) {
        document.getElementById('todo-form').reset();
        document.getElementById('todo-id').value = todo?.id || '';
        document.getElementById('todo-modal-title').textContent = todo ? @js(__('ui.todo_edit')) : @js(__('ui.todo_add'));
        document.getElementById('todo-title').value = todo?.title || '';
        document.getElementById('todo-notes').value = todo?.notes || '';
        document.getElementById('todo-priority').value = todo?.priority || 'normal';
        document.getElementById('todo-visibility').value = todo?.visibility || 'private';
        document.getElementById('todo-due-date').value = todo?.due_date || @js(now()->format('Y-m-d'));
        todoModal().show();
    }

    function openTodoModalFromButton(button) {
        openTodoModal({
            id: button.dataset.id,
            title: button.dataset.title,
            notes: button.dataset.notes,
            priority: button.dataset.priority,
            visibility: button.dataset.visibility,
            due_date: button.dataset.dueDate,
        });
    }

    todoModalElement.querySelectorAll('[data-bs-dismiss="modal"]').forEach(button => {
        button.addEventListener('click', () => todoModal().hide());
    });

    async function todoRequest(url, method, body = null) {
        const response = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: body ? JSON.stringify(body) : null,
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || @js(__('ui.todo_error')));
        return data;
    }

    async function toggleTodo(id, isCompleted) {
        try {
            await todoRequest(`/todos/${id}/toggle`, 'PATCH', { is_completed: isCompleted });
            refreshDashboard();
        } catch (error) { showToastAlert(error.message, 'danger'); }
    }

    async function deleteTodo(id) {
        if (!confirm(@js(__('ui.todo_delete_confirm')))) return;
        try {
            await todoRequest(`/todos/${id}`, 'DELETE');
            refreshDashboard();
        } catch (error) { showToastAlert(error.message, 'danger'); }
    }

    document.getElementById('todo-form').addEventListener('submit', async function (event) {
        event.preventDefault();
        const id = document.getElementById('todo-id').value;
        const button = this.querySelector('[type="submit"]');
        button.disabled = true;
        try {
            await todoRequest(id ? `/todos/${id}` : '/todos', id ? 'PATCH' : 'POST', {
                title: document.getElementById('todo-title').value,
                notes: document.getElementById('todo-notes').value || null,
                priority: document.getElementById('todo-priority').value,
                visibility: document.getElementById('todo-visibility').value,
                due_date: document.getElementById('todo-due-date').value || null,
            });
            todoModal().hide();
            refreshDashboard();
        } catch (error) { showToastAlert(error.message, 'danger'); }
        finally { button.disabled = false; }
    });

    document.querySelectorAll('[data-todo-filter]').forEach(button => {
        button.addEventListener('click', () => {
            const filter = button.dataset.todoFilter;
            document.querySelectorAll('[data-todo-filter]').forEach(item => item.classList.toggle('active', item === button));
            let visible = 0;
            document.querySelectorAll('#dashboard-todos [data-date-group]').forEach(task => {
                const show = filter === 'all' || task.dataset.dateGroup === filter;
                task.classList.toggle('d-none', !show);
                if (show) visible++;
            });
            document.getElementById('todo-filter-empty')?.classList.toggle('d-none', visible > 0);
        });
    });

    function prefillDispenser(app) {
        // Use the AJAX customer-search component's API to programmatically select a customer
        if (app.customer_id) {
            const wrapper = document.getElementById('dispenser_customer_id-wrapper');
            if (wrapper && wrapper.selectCustomer) {
                wrapper.selectCustomer({
                    id: app.customer_id,
                    full_name: app.customer ? (app.customer.full_name || app.customer.first_name) : 'Customer #' + app.customer_id,
                    phone: app.customer ? app.customer.phone : '',
                    email: app.customer ? app.customer.email : ''
                });
            } else {
                document.getElementById('dispenser_customer_id').value = app.customer_id;
            }
        }
        if (app.service_id) document.getElementById('dispenser_service_id').value = app.service_id;
        if (app.room_id) document.getElementById('dispenser_room_id').value = app.room_id;
        document.getElementById('quick-dispense-form').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function refreshDashboard() {
        fetch('/dashboard')
            .then(res => res.text())
            .then(html => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                ['dashboard-kpis', 'dashboard-rooms-grid', 'dashboard-todos', 'dashboard-appointments-list'].forEach(id => {
                    const el = doc.getElementById(id);
                    if (el && document.getElementById(id)) document.getElementById(id).innerHTML = el.innerHTML;
                });
                startDurationTimers();
            });
    }

    function checkinAppointment(appId, btn) {
        if (!confirm("Check-in this customer?")) return;
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm"></span>`;
        fetch(`/appointments/${appId}/checkin`, {
            method: 'PATCH', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).then(res => res.json()).then(data => {
            if (data.success) { showToastAlert('Checked in!', 'success'); refreshDashboard(); }
            else { showToastAlert(data.message, 'danger'); btn.disabled = false; btn.innerText = 'Check-in'; }
        });
    }

    function callNextInRoom(roomId) {
        fetch(`/queue/call`, {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ room_id: roomId })
        }).then(res => res.json()).then(data => {
            if (data.success) { showToastAlert('Called next customer!', 'success'); refreshDashboard(); }
            else showToastAlert(data.message || 'Queue empty.', 'warning');
        });
    }

    document.getElementById('quick-dispense-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = this.querySelector('button');
        const orig = btn.innerHTML;
        btn.disabled = true; btn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Processing...`;
        
        const dataObj = Object.fromEntries(new FormData(this));
        if (!dataObj.is_vip) dataObj.is_vip = 0;
        
        fetch(`/queue/store`, {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(dataObj)
        }).then(res => res.json()).then(data => {
            btn.disabled = false; btn.innerHTML = orig;
            if (data.success) {
                showToastAlert(`Ticket ${data.ticket.ticket_number} created successfully!`, 'success');
                this.reset();
                refreshDashboard();
            } else {
                showToastAlert(data.message || 'Failed to create ticket.', 'danger');
            }
        }).catch(() => { btn.disabled = false; btn.innerHTML = orig; showToastAlert('Error creating ticket.', 'danger'); });
    });

    function showToastAlert(message, type) {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'position-fixed top-0 end-0 p-4';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
        
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'danger' ? 'danger' : (type === 'warning' ? 'warning' : 'success')} border-0 show shadow-lg mb-2`;
        toast.role = 'alert';
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body fw-medium">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>`;
        container.appendChild(toast);
        setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 4000);
    }

    document.addEventListener('DOMContentLoaded', startDurationTimers);
</script>
@endpush
