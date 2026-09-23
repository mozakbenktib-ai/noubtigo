@extends('layouts.dashboard')

@section('title', __('ui.appointments_management') . ' | Noubtigo')
@section('header_title', __('ui.appointments_management') ?? 'Appointments')
@section('header_subtitle', __('ui.schedule_visits') ?? 'Manage & schedule your appointments')

@push('styles')
    <!-- FullCalendar 6 -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
    <style>
        /* ── Premium Layout ── */
        .glass-panel {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: var(--radius-lg);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.04);
            padding: 1.5rem;
        }

        /* ── Stats row ── */
        .appt-stat-card {
            background: #fff;
            border-radius: 20px;
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .05);
            transition: all .2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.02);
        }

        .appt-stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 4px; height: 100%;
            background: var(--primary-gradient);
            opacity: 0;
            transition: opacity 0.2s;
        }

        .appt-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, .08);
        }

        .appt-stat-card:hover::before {
            opacity: 1;
        }

        .appt-stat-icon {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            flex-shrink: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.5), rgba(255,255,255,0.1));
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .appt-stat-card .stat-value {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -1px;
            color: #1e293b;
        }

        .appt-stat-card .stat-label {
            font-size: .85rem;
            color: #64748b;
            font-weight: 600;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── View toggle tabs ── */
        .view-tabs {
            border: none;
            gap: 10px;
            background: #f1f5f9;
            padding: 6px;
            border-radius: 14px;
        }

        .view-tabs .nav-link {
            border: none !important;
            border-radius: 10px !important;
            color: #64748b;
            font-weight: 600;
            padding: 8px 20px;
            transition: all .2s;
            font-size: 0.9rem;
        }

        .view-tabs .nav-link.active {
            background: #fff !important;
            color: #0f172a !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        /* ── FullCalendar overrides ── */
        .fc {
            font-family: 'Inter', sans-serif !important;
        }
        .fc .fc-toolbar-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #1e293b;
        }
        .fc .fc-button-primary {
            background: #fff !important;
            color: #1e293b !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 10px !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02) !important;
            transition: all .18s;
            font-weight: 600;
            text-transform: capitalize;
        }
        .fc .fc-button-primary:hover {
            background: #f8fafc !important;
            border-color: #cbd5e1 !important;
        }
        .fc .fc-button-primary:not(:disabled).fc-button-active,
        .fc .fc-button-primary:not(:disabled):active {
            background: var(--primary-gradient) !important;
            color: #fff !important;
            border-color: transparent !important;
        }
        .fc-calendar-wrap {
            background: #fff;
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, .04);
            border: 1px solid rgba(0,0,0,0.02);
        }
        .fc-day-today {
            background: rgba(59, 130, 246, 0.02) !important;
        }
        .fc-day-today .fc-daygrid-day-number {
            background: var(--primary-gradient);
            color: #fff;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }
        .fc .fc-daygrid-event, .fc .fc-timegrid-event {
            border-radius: 8px !important;
            font-size: .8rem;
            font-weight: 600;
            padding: 4px 8px;
            cursor: pointer;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.15s;
        }
        .fc .fc-daygrid-event:hover {
            transform: scale(1.02);
        }

        /* ── List table ── */
        .appt-table {
            border-collapse: separate;
            border-spacing: 0 8px;
        }
        .appt-table th {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #94a3b8;
            font-weight: 700;
            border-bottom: none;
            padding-bottom: 0;
        }
        .appt-table tbody tr {
            background: #fff;
            border-radius: 16px;
            transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }
        .appt-table tbody tr:hover {
            transform: translateY(-2px) scale(1.005);
            box-shadow: 0 8px 16px rgba(0,0,0,0.06);
        }
        .appt-table td {
            border: none;
            padding: 16px 20px;
            vertical-align: middle;
        }
        .appt-table td:first-child { border-top-left-radius: 16px; border-bottom-left-radius: 16px; }
        .appt-table td:last-child { border-top-right-radius: 16px; border-bottom-right-radius: 16px; }

        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
            letter-spacing: 0.3px;
        }

        /* ── Offcanvas (Side Drawer) ── */
        .offcanvas {
            border-left: none;
            box-shadow: -10px 0 40px rgba(0,0,0,0.1);
        }
        .offcanvas-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.5rem;
        }
        .offcanvas-body {
            padding: 1.5rem;
        }

        /* Slot picker pills */
        .slot-pill {
            display: inline-block;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 18px;
            cursor: pointer;
            transition: all .2s;
            text-align: center;
            background: #fff;
            font-weight: 600;
            color: #475569;
            width: 100%;
        }
        .slot-pill:hover {
            border-color: #3b82f6;
            background: #eff6ff;
            color: #1d4ed8;
            transform: translateY(-2px);
        }
        .slot-pill.selected {
            border-color: transparent;
            background: var(--primary-gradient);
            color: #fff;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        .slot-pill.full {
            border-color: #fecaca;
            background: #fef2f2;
            color: #ef4444;
            opacity: .6;
            cursor: not-allowed;
        }
        .slot-pill.overbook {
            border-color: #fde68a;
            background: #fffbeb;
            color: #d97706;
        }
        .slot-time { font-size: 1rem; }
        .slot-cap { font-size: 0.75rem; font-weight: 500; opacity: 0.8; margin-top: 2px; }

        /* Action Buttons */
        .action-btn {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center; justify-content: center;
            border: none;
            transition: all 0.2s;
            color: #64748b;
            background: #f1f5f9;
        }
        .action-btn:hover {
            background: #e2e8f0;
            color: #0f172a;
            transform: translateY(-2px);
        }
        .checkin-action {
            background: linear-gradient(135deg, #22c55e, #10b981);
            color: white;
            box-shadow: 0 4px 10px rgba(34, 197, 94, 0.3);
        }
        .checkin-action:hover {
            background: linear-gradient(135deg, #16a34a, #059669);
            color: white;
        }

        [data-bs-theme="dark"] .appt-stat-card,
        [data-bs-theme="dark"] .fc-calendar-wrap,
        [data-bs-theme="dark"] .appt-table tbody tr,
        [data-bs-theme="dark"] .offcanvas {
            background: #1e293b !important;
            border-color: #334155;
        }
        [data-bs-theme="dark"] .slot-pill {
            background: #0f172a;
            border-color: #334155;
            color: #cbd5e1;
        }
        .detail-modal { border-radius: 1.35rem !important; overflow: hidden; }
        .detail-modal-header { background: linear-gradient(135deg, #eef4ff 0%, #f8fbff 55%, #fff 100%); }
        .detail-icon { width: 52px; height: 52px; border-radius: 16px; background: linear-gradient(135deg, #2563eb, #7c3aed); color: #fff; box-shadow: 0 8px 20px rgba(37,99,235,.2); }
        .detail-info-card { height: 100%; padding: 1rem; border: 1px solid #edf0f5; border-radius: 1rem; background: #fff; box-shadow: 0 4px 14px rgba(15,23,42,.04); }
        .detail-info-card.room-card { background: linear-gradient(135deg, #eff6ff, #f5f3ff); border-color: #dbeafe; }
        .detail-info-label { color: #94a3b8; font-size: .68rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        .detail-info-value { color: #172033; font-weight: 700; margin-top: .28rem; }

        /* Noubtigo calendar workspace */
        .calendar-page { --calendar-ink:#102a43; --calendar-muted:#718096; --calendar-line:#e8eef4; --calendar-soft:#f7fafc; }
        .calendar-hero { background:linear-gradient(135deg,#0f766e 0%,#0891b2 100%); border-radius:24px; padding:28px 32px; color:#fff; box-shadow:0 14px 34px rgba(8,145,178,.18); }
        .calendar-hero h1 { font-size:clamp(1.35rem,2vw,2rem); letter-spacing:-.04em; }
        .calendar-shell { display:grid; grid-template-columns:250px minmax(0,1fr); gap:20px; align-items:start; }
        .calendar-sidebar,.calendar-main { background:#fff; border:1px solid var(--calendar-line); border-radius:22px; box-shadow:0 8px 26px rgba(15,42,67,.045); }
        .calendar-sidebar { padding:20px; position:sticky; top:18px; }
        .calendar-main { padding:20px; min-width:0; }
        .calendar-sidebar-title { font-size:.7rem; text-transform:uppercase; letter-spacing:.12em; color:#9aa9b8; font-weight:800; }
        .mini-calendar { font-size:.78rem; }
        .mini-calendar .day { width:29px;height:29px;display:inline-flex;align-items:center;justify-content:center;border-radius:50%;color:#52606d;cursor:pointer; }
        .mini-calendar .day:hover,.mini-calendar .day.is-today { background:#dff7f4;color:#087f78;font-weight:800; }
        .filter-check { display:flex;align-items:center;gap:9px;padding:7px 0;color:#52606d;font-size:.84rem; }
        .filter-check input { accent-color:#0f9f93;width:15px;height:15px; }
        .legend-dot { width:9px;height:9px;border-radius:50%;display:inline-block; }
        .status-no-show { background-color:#f97316 !important; color:#fff !important; }
        .calendar-toolbar { display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:18px; }
        .calendar-toolbar .fc-toolbar-title { font-size:1.3rem; color:var(--calendar-ink); }
        .calendar-nav button,.calendar-view button { border:1px solid var(--calendar-line);background:#fff;color:#52606d;border-radius:10px;padding:.5rem .75rem;font-weight:700; }
        .calendar-nav button:hover,.calendar-view button:hover { background:#f0fdfa;color:#087f78; }
        .calendar-view button.active { background:#102a43;color:#fff;border-color:#102a43; }
        .fc { --fc-border-color:var(--calendar-line); --fc-today-bg-color:#f0fdfa; }
        .fc .fc-scrollgrid { border-radius:16px; overflow:hidden; }
        .fc .fc-col-header-cell-cushion { color:#8292a3;text-transform:uppercase;font-size:.68rem;letter-spacing:.08em;padding:12px 4px; }
        .fc .fc-daygrid-day-number { color:#52606d;font-weight:700;padding:10px; }
        .fc .fc-day-today .fc-daygrid-day-number { background:#0f9f93;color:#fff;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;padding:0;margin:6px; }
        .fc .fc-event { border:0;border-left:3px solid rgba(255,255,255,.75);border-radius:8px;padding:3px 5px;box-shadow:0 3px 8px rgba(15,42,67,.12); }
        .fc .fc-event-title,.fc .fc-event-time { font-weight:700;font-size:.76rem; }
        .fc .fc-timegrid-slot { height:3.1rem; }
        .calendar-empty { display:none;text-align:center;padding:52px 20px;color:#718096; }
        .calendar-empty i { font-size:2.4rem;color:#72c9c2; }
        .calendar-search { max-width:240px; border-radius:11px;border:1px solid var(--calendar-line);padding:.56rem .85rem; }
        [data-bs-theme="dark"] .calendar-sidebar,[data-bs-theme="dark"] .calendar-main { background:#1e293b; border-color:#334155; }
        [data-bs-theme="dark"] .calendar-toolbar .fc-toolbar-title,[data-bs-theme="dark"] .calendar-sidebar,[data-bs-theme="dark"] .calendar-main { color:#e2e8f0; }
        @media (max-width: 991px) { .calendar-shell { grid-template-columns:1fr; } .calendar-sidebar { position:static; } }
        @media (max-width: 575px) { .calendar-hero { padding:22px 20px; } .calendar-main { padding:12px; } .calendar-toolbar { align-items:flex-start; } .calendar-search { max-width:none;width:100%; } .fc .fc-toolbar-title { font-size:1.05rem; } }
    </style>
@endpush

@section('content')
    <div class="calendar-page">
        <div class="calendar-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3 fade-in">
            <div><div class="small text-white-50 fw-semibold mb-1">{{ __('ui.calendar_view') }}</div><h1 class="mb-1 fw-bold">{{ __('ui.appointments_management') }}</h1><p class="mb-0 text-white-50">{{ __('ui.schedule_visits') ?? 'Manage & schedule your appointments' }}</p></div>
            <div class="d-flex gap-2">
                @permission('appointment_slots.view')<a href="{{ route('appointment-slots.index') }}" class="btn btn-light rounded-pill fw-semibold"><i class="bi bi-sliders me-2"></i>{{ __('ui.slot_settings') }}</a>@endpermission
                <button class="btn btn-light rounded-pill fw-bold text-success" onclick="openNewBookDrawer()"><i class="bi bi-plus-lg me-2"></i>{{ __('ui.new_appointment') }}</button>
            </div>
        </div>

        <div class="calendar-shell fade-in">
            <aside class="calendar-sidebar">
                <div class="d-flex align-items-center justify-content-between mb-3"><span class="calendar-sidebar-title">{{ __('ui.calendar_view') }}</span><i class="bi bi-calendar3 text-success"></i></div>
                <div class="d-flex align-items-center justify-content-between mb-2"><button class="btn btn-sm btn-light rounded-pill" onclick="moveCalendar('today')">{{ __('ui.calendar_today') }}</button><div class="small text-secondary" id="miniMonthLabel"></div></div>
                <div class="mini-calendar mb-4" id="miniCalendar"></div>
                <div class="calendar-sidebar-title mb-2">{{ __('ui.status') }}</div>
                @foreach(['pending','confirmed','checked_in','completed','cancelled','no_show'] as $st)
                    @php $statusDot = ['pending' => 'bg-warning', 'confirmed' => 'bg-success', 'checked_in' => 'bg-info', 'completed' => 'bg-secondary', 'cancelled' => 'bg-danger', 'no_show' => 'status-no-show'][$st]; @endphp
                    <label class="filter-check"><input type="checkbox" class="status-filter" value="{{ $st }}" checked> <i class="legend-dot {{ $statusDot }}"></i><span>{{ __('ui.'.$st) }}</span></label>
                @endforeach
                <div class="calendar-sidebar-title mt-4 mb-2">{{ __('ui.assigned_room') }}</div>
                <label class="filter-check"><input type="checkbox" class="room-filter" value="all" checked> <span>{{ __('ui.all_rooms') ?? 'All rooms' }}</span></label>
                @foreach($rooms as $room)<label class="filter-check"><input type="checkbox" class="room-filter" value="{{ $room->id }}" checked> <span>{{ $room->name }}</span></label>@endforeach
            </aside>

            <main class="calendar-main">
                <div class="calendar-toolbar">
                    <div class="d-flex align-items-center gap-2 calendar-nav"><button onclick="moveCalendar('prev')" aria-label="Previous"><i class="bi bi-chevron-left"></i></button><button onclick="moveCalendar('today')">{{ __('ui.calendar_today') }}</button><button onclick="moveCalendar('next')" aria-label="Next"><i class="bi bi-chevron-right"></i></button></div>
                    <div class="d-flex align-items-center gap-2 flex-wrap"><input id="calendarSearch" class="calendar-search" type="search" placeholder="{{ __('ui.search') ?? 'Search customer or service...' }}"><div class="calendar-view btn-group"><button data-view="dayGridMonth" class="active">{{ __('ui.calendar_month') }}</button><button data-view="timeGridWeek">{{ __('ui.calendar_week') }}</button><button data-view="timeGridDay">{{ __('ui.calendar_day') }}</button><button data-view="listWeek">{{ __('ui.list_view') }}</button></div></div>
                </div>
                <div id="appointmentCalendar"></div><div class="calendar-empty" id="calendarEmpty"><i class="bi bi-calendar2-check d-block mb-3"></i><h5 class="fw-bold">{{ __('ui.no_upcoming_appointments') }}</h5><p class="mb-3">{{ __('ui.no_upcoming_appointments') }}</p><button class="btn btn-gradient rounded-pill" onclick="openNewBookDrawer()"><i class="bi bi-plus-lg me-2"></i>{{ __('ui.new_appointment') }}</button></div>
            </main>
        </div>

        <div class="d-none"><button id="cal-tab"></button><button id="list-tab"></button></div>

        {{-- Legacy list view remains available through the shared FullCalendar list view. --}}
        <div class="d-none" id="legacyListView">

        {{-- List view --}}
        <div class="tab-pane fade" id="legacyListTable" role="tabpanel">
            <div class="glass-panel fade-in">
                
                {{-- Filter row --}}
                <div class="d-flex flex-wrap gap-3 mb-4 p-3 bg-light rounded-4 border">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-funnel text-secondary"></i>
                        <span class="fw-semibold text-secondary small text-uppercase">Filters:</span>
                    </div>
                    <select class="form-select form-select-sm w-auto rounded-pill px-3 shadow-sm border-0" id="filterService" onchange="filterList()">
                        <option value="">{{ __('ui.all_services') }}</option>
                        @foreach($services as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                    <select class="form-select form-select-sm w-auto rounded-pill px-3 shadow-sm border-0" id="filterStatus" onchange="filterList()">
                        <option value="">{{ __('ui.all_statuses') }}</option>
                        @foreach(['pending', 'confirmed', 'checked_in', 'completed', 'cancelled', 'no_show'] as $st)
                            <option value="{{ $st }}">{{ __('ui.' . $st) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle appt-table" id="appointmentsTable">
                        <thead>
                            <tr>
                                <th style="width: 50px;">ID</th>
                                <th>{{ __('ui.customer') }}</th>
                                <th>{{ __('ui.service') }}</th>
                                <th>{{ __('ui.date_time') }}</th>
                                <th>{{ __('ui.status') }}</th>
                                <th class="text-end">{{ __('ui.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $appt)
                                @php 
                                    $badge = $appt->statusBadge(); 
                                    $apptJson = json_encode([
                                        'id' => $appt->id,
                                        'title' => $appt->customer_name,
                                        'service_name' => $appt->service?->name,
                                        'room_name' => $appt->room?->name,
                                        'customer_phone' => $appt->customer_phone,
                                        'customer_email' => $appt->customer_email,
                                        'customer_id' => $appt->customer_id,
                                        'service_id' => $appt->service_id,
                                        'status' => $appt->status,
                                        'start' => $appt->appointment_date,
                                        'is_overbooked' => $appt->is_overbooked,
                                        'checked_in_at' => $appt->checked_in_at,
                                        'room_id' => $appt->room_id,
                                        'user_id' => $appt->user_id,
                                        'notes' => $appt->notes,
                                        'slot_id' => $appt->slot_id,
                                    ]);
                                @endphp
                                <tr data-service="{{ $appt->service_id }}" data-status="{{ $appt->status }}">
                                    <td class="text-secondary fw-bold">#{{ $appt->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:40px; height:40px;">
                                                {{ substr($appt->customer_name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $appt->customer_name }}</div>
                                                @if($appt->customer_phone)
                                                    <small class="text-secondary"><i class="bi bi-telephone me-1"></i>{{ $appt->customer_phone }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-medium">
                                            {{ $appt->service?->name ?? 'General' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">@localtime($appt->appointment_date, 'M d, Y')</div>
                                        <small class="text-primary fw-bold"><i class="bi bi-clock me-1"></i>@localtime($appt->appointment_date, 'H:i')</small>
                                    </td>
                                    <td>
                                        <span class="status-badge {{ $badge['class'] }} shadow-sm">
                                            @if($appt->isCheckedIn()) <i class="bi bi-person-check-fill me-1"></i> @endif
                                            {{ __('ui.' . $appt->status) }}
                                        </span>
                                        @if($appt->is_overbooked)
                                            <span class="badge bg-warning text-dark ms-1" title="Overbooked"><i class="bi bi-lightning-fill"></i></span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex gap-2 justify-content-end">
                                            @if(!$appt->isCheckedIn() && in_array($appt->status, ['pending', 'confirmed']))
                                                <button class="action-btn checkin-action" title="{{ __('ui.check_in') }}" onclick="checkIn({{ $appt->id }}, this)">
                                                    <i class="bi bi-person-check-fill"></i>
                                                </button>
                                            @endif
                                            <button class="action-btn" data-appt="{{ $apptJson }}" onclick="openDetailFromList(this)" title="Details & Edit">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary py-5">
                                        <div class="empty-state">
                                            <div class="empty-state-icon bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                                <i class="bi bi-calendar-x fs-1 text-muted"></i>
                                            </div>
                                            <h5 class="fw-bold mb-2">{{ __('ui.no_upcoming_appointments') }}</h5>
                                            <p class="text-secondary mb-4">You have no appointments matching the current filters.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($appointments->hasPages())
                    <div class="d-flex justify-content-center mt-4">{{ $appointments->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════════
    OFFCANVAS: Book Appointment
    ═══════════════════════════════════════════════════════════════════════════ --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="bookOffcanvas" style="width: 500px; max-width: 100vw;z-index: 10001">
        <div class="offcanvas-header bg-light border-bottom">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary text-white rounded p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px;">
                    <i class="bi bi-calendar2-plus fs-5"></i>
                </div>
                <div>
                    <h5 class="offcanvas-title fw-bold" id="drawerTitle">{{ __('ui.new_appointment') }}</h5>
                    <p class="mb-0 text-muted small">{{ __('ui.schedule_a_visit') }}</p>
                </div>
            </div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-4">
            <form id="bookForm">
                @csrf
                <input type="hidden" id="book_appt_id" value="">
                
                <h6 class="fw-bold text-uppercase text-secondary small mb-3 letter-spacing-1">{{ __('ui.customer_details') }}</h6>
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="form-label small fw-semibold">{{ __('ui.existing_customer') }}</label>
                        <x-customer-search id="book_customer_id" name="customer_id" onchange="prefillCustomer" />
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">{{ __('ui.customer_name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="customer_name" id="book_customer_name" placeholder="{{ __('ui.full_name') }}" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">{{ __('ui.phone_number') }}</label>
                        <input type="text" class="form-control" name="customer_phone" id="book_phone" placeholder="+212 6...">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">{{ __('ui.email') }}</label>
                        <input type="email" class="form-control" name="customer_email" id="book_email" placeholder="mail@example.com">
                    </div>
                </div>

                <h6 class="fw-bold text-uppercase text-secondary small mb-3 letter-spacing-1">{{ __('ui.appointment_details') }}</h6>
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="form-label small fw-semibold">{{ __('ui.service') }} <span class="text-danger">*</span></label>
                        <select class="form-select" name="service_id" id="book_service" required onchange="loadSlots()">
                            <option value="">{{ __('ui.select_service') }}</option>
                            @foreach($services as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">{{ __('ui.date') }} <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="appointment_date" id="book_date" min="{{ date('Y-m-d') }}" required onchange="loadSlots()">
                    </div>
                    
                    {{-- Slot Picker --}}
                    <div class="col-12" id="slotPickerWrap" style="display:none">
                        <label class="form-label small fw-semibold d-block mb-2">{{ __('ui.available_time_slots') }} <span class="text-danger">*</span></label>
                        <input type="hidden" name="slot_id" id="book_slot_id">
                        <div class="row g-2" id="slotGrid"></div>
                        <div id="slotEmpty" class="alert alert-light border border-secondary-subtle text-secondary small text-center mt-2 py-3" style="display:none">
                            <i class="bi bi-clock-history fs-4 d-block mb-1"></i>
                            No configured slots for this day.
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold">{{ __('ui.assigned_room') }} <span class="text-danger">*</span></label>
                        <select class="form-select" name="room_id" id="book_room_id" required onchange="loadSlots()">
                            <option value="">{{ __('ui.select_room') }}</option>
                            @foreach($rooms as $r)
                                <option value="{{ $r->id }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">{{ __('ui.notes') }}</label>
                        <textarea class="form-control bg-light" name="notes" rows="3" placeholder="{{ __('ui.internal_notes') }}"></textarea>
                    </div>
                </div>

                {{-- Overbooking warning --}}
                <div id="overbookingAlert" class="alert alert-warning border-0 rounded-3 mt-3 small shadow-sm" style="display:none">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Overbooking:</strong> {{ __('ui.overbooking_warning') }}
                </div>

                <div class="mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-gradient w-100 py-3 fw-bold shadow" id="bookSubmitBtn">
                        <i class="bi bi-calendar-check me-2"></i>{{ __('ui.book_appointment') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════════
    MODAL: Detail / Edit / Check-in
    ═══════════════════════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content detail-modal border-0 shadow-lg">
                <div class="modal-header detail-modal-header border-bottom-0 p-4">
                    <div class="d-flex align-items-center gap-3 w-100">
                        <div class="detail-icon d-flex align-items-center justify-content-center" id="detailIcon">
                            <i class="bi bi-calendar2-event-fill fs-5"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-0 text-dark" id="detailName">{{ __('ui.appointments') }}</h5>
                            <small class="text-secondary fw-semibold" id="detailSub">—</small>
                        </div>
                        <span class="badge bg-primary text-white px-3 py-2 rounded-pill shadow-sm" id="detailStatusBadge">—</span>
                    </div>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-4" id="detailBody">
                        {{-- JS fills this in --}}
                    </div>

                    {{-- Grace period countdown --}}
                    <div id="graceSection" style="display:none" class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <small class="fw-bold text-warning text-uppercase letter-spacing-1"><i class="bi bi-clock-history me-1"></i>{{ __('ui.grace_period') }}</small>
                            <small class="fw-bold font-monospace" id="graceTimer">—</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" id="graceFill" style="width:100%"></div>
                        </div>
                    </div>

                    {{-- Action buttons --}}
                    <div class="d-flex flex-wrap gap-2 justify-content-center mt-2" id="detailActions">
                        {{-- injected by JS --}}
                    </div>

                    {{-- Cancel reason sub-form --}}
                    <div id="cancelForm" style="display:none" class="mt-4 bg-danger-subtle p-3 rounded-4 border border-danger-subtle">
                        <label class="form-label small fw-bold text-danger"><i class="bi bi-x-circle-fill me-1"></i>{{ __('ui.cancellation_reason') }}</label>
                        <textarea class="form-control border-danger-subtle mb-3" id="cancelReason" rows="2" placeholder="Optional reason..."></textarea>
                        <div class="d-flex gap-2">
                            <button class="btn btn-danger w-100 fw-bold shadow-sm" onclick="submitCancel()">Confirm Cancellation</button>
                            <button class="btn btn-outline-danger w-100 fw-bold" onclick="document.getElementById('cancelForm').style.display='none'">Nevermind</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    @php($calendarLocale = in_array(app()->getLocale(), ['en', 'fr', 'ar'], true) ? app()->getLocale() : 'en')
    <!-- FullCalendar 6 -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales-all.global.min.js"></script>
    <script>
        // ─────────────────────────────────────────────────────────────────────────────
        // State
        // ─────────────────────────────────────────────────────────────────────────────
        let calendar;
        let currentAppt = null;
        let graceInterval = null;
        let bookDrawer = null;
        const currentLocale = @json($calendarLocale);
        const calendarDirection = currentLocale === 'ar' ? 'rtl' : 'ltr';

        document.addEventListener('DOMContentLoaded', function () {
            bookDrawer = new bootstrap.Offcanvas(document.getElementById('bookOffcanvas'));
            
            const calEl = document.getElementById('appointmentCalendar');
            calendar = new FullCalendar.Calendar(calEl, {
                initialView: 'dayGridMonth',
                headerToolbar: { left: '', center: 'title', right: '' },
                locale: currentLocale,
                allDayText: @json(__('ui.all_day')),
                noEventsText: @json(__('ui.no_events_to_display')),
                direction: calendarDirection,
                firstDay: 1,
                height: 'auto',
                // Rescheduling remains available through the existing edit flow.
                // Drag/drop is intentionally disabled because it can omit room/slot context.
                editable: false,
                droppable: false,
                selectable: true,
                selectMirror: true,
                nowIndicator: true,
                timeZone: '{{ app(\App\Services\TimezoneService::class)->resolve() }}',

                events: {
                    url: '{{ route("appointments.events") }}',
                    method: 'GET',
                    extraParams: {},
                    failure: () => { document.getElementById('calendarEmpty').style.display = 'block'; },
                },

                eventDataTransform: function (event) {
                    const statuses = [...document.querySelectorAll('.status-filter:checked')].map(el => el.value);
                    const rooms = [...document.querySelectorAll('.room-filter:checked')].map(el => el.value);
                    const search = (document.getElementById('calendarSearch')?.value || '').trim().toLowerCase();
                    const props = event.extendedProps || {};
                    const haystack = `${event.title || ''} ${props.customer_name || ''} ${props.service_name || ''} ${props.room_name || ''}`.toLowerCase();
                    return statuses.includes(props.status) && (rooms.includes('all') || rooms.includes(String(props.room_id))) && (!search || haystack.includes(search)) ? event : false;
                },

                dateClick: function (info) {
                    const chosenDate = info.dateStr.substring(0, 10);
                    openNewBookDrawer(chosenDate);
                },

                select: function (info) {
                    const chosenDate = info.startStr.substring(0, 10);
                    openNewBookDrawer(chosenDate);
                },

                eventClick: function (info) {
                    openDetailFromCalendar(info.event);
                },

                eventDidMount: function (info) {
                    info.el.setAttribute('title', info.event.title);
                }
            });

            calendar.render();
            renderMiniCalendar();
            document.querySelectorAll('.calendar-view button').forEach(button => button.addEventListener('click', () => {
                document.querySelectorAll('.calendar-view button').forEach(item => item.classList.remove('active'));
                button.classList.add('active');
                calendar.changeView(button.dataset.view);
            }));
            document.querySelectorAll('.status-filter,.room-filter').forEach(input => input.addEventListener('change', () => calendar.refetchEvents()));
            document.getElementById('calendarSearch').addEventListener('input', () => calendar.refetchEvents());
            document.querySelectorAll('.room-filter').forEach(input => input.addEventListener('change', function () {
                if (this.value === 'all' && this.checked) document.querySelectorAll('.room-filter:not([value="all"])').forEach(item => item.checked = true);
                if (this.value !== 'all' && !this.checked) document.querySelector('.room-filter[value="all"]').checked = false;
            }));
        });

        function renderMiniCalendar() {
            const date = calendar?.getDate() || new Date();
            const year = date.getFullYear(), month = date.getMonth();
            const calendarFirstDay = calendar?.getOption('firstDay') ?? 0;
            const monthFirstDay = new Date(year, month, 1).getDay();
            const firstDay = (monthFirstDay - calendarFirstDay + 7) % 7;
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const today = new Date();
            document.getElementById('miniMonthLabel').textContent = date.toLocaleDateString('{{ app()->getLocale() }}', { month:'short', year:'numeric' });
            let html = '<div class="d-grid" style="grid-template-columns:repeat(7,1fr);gap:3px;text-align:center">';
            const weekStart = new Date(2021, 7, 1 + calendarFirstDay);
            for (let index = 0; index < 7; index++) {
                const weekday = new Date(weekStart);
                weekday.setDate(weekStart.getDate() + index);
                const label = new Intl.DateTimeFormat(currentLocale, { weekday: 'short' }).format(weekday);
                html += `<span class="text-muted small fw-bold">${label}</span>`;
            }
            for (let i = 0; i < firstDay; i++) html += '<span></span>';
            for (let day = 1; day <= daysInMonth; day++) {
                const active = day === today.getDate() && month === today.getMonth() && year === today.getFullYear() ? ' is-today' : '';
                html += `<button type="button" class="day border-0 bg-transparent${active}" data-date="${year}-${String(month+1).padStart(2,'0')}-${String(day).padStart(2,'0')}">${day}</button>`;
            }
            document.getElementById('miniCalendar').innerHTML = html + '</div>';
            document.querySelectorAll('#miniCalendar .day').forEach(button => button.addEventListener('click', () => { calendar.gotoDate(button.dataset.date); renderMiniCalendar(); }));
        }

        function moveCalendar(direction) {
            if (!calendar) return;
            if (direction === 'prev') calendar.prev();
            if (direction === 'next') calendar.next();
            if (direction === 'today') calendar.today();
            renderMiniCalendar();
        }

        // ─────────────────────────────────────────────────────────────────────────────
        // Slot Picker
        // ─────────────────────────────────────────────────────────────────────────────
        function loadSlots() {
            const serviceId = document.getElementById('book_service').value;
            const roomId = document.getElementById('book_room_id').value;
            const date = document.getElementById('book_date').value;

            if (!serviceId || !roomId || !date) return;

            fetch(`{{ route('appointments.slots') }}?service_id=${serviceId}&room_id=${roomId}&date=${date}`)
                .then(r => r.json())
                .then(slots => {
                    const grid = document.getElementById('slotGrid');
                    const empty = document.getElementById('slotEmpty');
                    const wrap = document.getElementById('slotPickerWrap');

                    grid.innerHTML = '';
                    wrap.style.display = 'block';

                    if (!slots.length) {
                        empty.style.display = 'block';
                        return;
                    }

                    empty.style.display = 'none';

                    const activeSlotId = document.getElementById('book_slot_id').value;

                    slots.forEach(slot => {
                        const col = document.createElement('div');
                        col.className = 'col-6 col-sm-4 col-md-3';

                        let cls = 'slot-pill shadow-sm';
                        if (activeSlotId == slot.id) cls += ' selected';

                        let cap = 'Available';

                        col.innerHTML = `
                            <div class="${cls}" data-slot="" data-time="${slot.start_time}" data-ob="false" onclick="selectSlot(this)">
                                <div class="slot-time">${slot.time_range}</div>
                                <div class="slot-cap">${cap}</div>
                            </div>`;
                        grid.appendChild(col);
                    });
                });
        }

        function selectSlot(el) {
            if (el.classList.contains('full')) return;

            document.querySelectorAll('.slot-pill').forEach(c => c.classList.remove('selected'));
            el.classList.add('selected');

            document.getElementById('book_slot_id').value = el.dataset.slot;

            const dateInput = document.getElementById('book_date');
            const [h, m] = el.dataset.time.split(':');
            const dt = new Date(dateInput.value);
            dt.setHours(+h, +m);
            document.getElementById('book_date').dataset.time = el.dataset.time;

            document.getElementById('overbookingAlert').style.display = (el.dataset.ob === '1' || el.dataset.ob === 'true') ? 'block' : 'none';
        }

        function openNewBookDrawer(prefillDate = null) {
            document.getElementById('bookForm').reset();
            document.getElementById('book_appt_id').value = '';
            document.getElementById('book_slot_id').value = '';
            document.getElementById('slotPickerWrap').style.display = 'none';
            document.getElementById('overbookingAlert').style.display = 'none';
            document.getElementById('book_date').dataset.time = '';
            
            // Auto-fill selected date or default to today's date
            const targetDate = prefillDate || '{{ date('Y-m-d') }}';
            document.getElementById('book_date').value = targetDate;

            document.getElementById('drawerTitle').textContent = '{{ __("ui.new_appointment") }}';
            document.getElementById('bookSubmitBtn').innerHTML = '<i class="bi bi-calendar-check me-2"></i>{{ __("ui.book_appointment") }}';
            
            loadSlots();
            bookDrawer.show();
        }

        // ─────────────────────────────────────────────────────────────────────────────
        // Book Form submit
        // ─────────────────────────────────────────────────────────────────────────────
        document.getElementById('bookForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const fd = new FormData(this);
            const btn = document.getElementById('bookSubmitBtn');

            let apptDate = fd.get('appointment_date');
            const slotTime = document.getElementById('book_date').dataset.time;

            if (!slotTime) {
                showToast('Please select an available time slot.', 'warning');
                return;
            }

            if (slotTime) apptDate += ' ' + slotTime.substring(0, 5) + ':00';
            fd.set('appointment_date', apptDate);

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

            const apptId = document.getElementById('book_appt_id').value;
            const url = apptId ? `/appointments/${apptId}` : '{{ route("appointments.store") }}';

            if (apptId) fd.append('_method', 'PUT');

            fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: fd
            }).then(r => r.json()).then(data => {
                if (data.success || data.id) {
                    bookDrawer.hide();
                    calendar.refetchEvents();
                    showToast(apptId ? 'Appointment updated!' : 'Appointment booked!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    let errMsg = data.message || 'Saving failed.';
                    if (data.errors) {
                        const firstError = Object.values(data.errors)[0];
                        if (Array.isArray(firstError) && firstError.length > 0) {
                            errMsg = firstError[0];
                        } else if (typeof firstError === 'string') {
                            errMsg = firstError;
                        }
                    }
                    showToast(errMsg, 'danger');
                }
            }).catch(() => showToast('Network error.', 'danger'))
              .finally(() => {
                  btn.disabled = false;
                  btn.innerHTML = `<i class="bi bi-${apptId ? 'save' : 'calendar-check'} me-2"></i>${apptId ? 'Save Changes' : '{{ __("ui.book_appointment") }}'}`;
              });
        });

        // ─────────────────────────────────────────────────────────────────────────────
        // Modal Logic
        // ─────────────────────────────────────────────────────────────────────────────
        function prefillCustomer(sel) {
            const opt = sel.options[sel.selectedIndex];
            if (opt && opt.dataset.name) document.getElementById('book_customer_name').value = opt.dataset.name;
            if (opt && opt.dataset.phone) document.getElementById('book_phone').value = opt.dataset.phone;
            if (opt && opt.dataset.email) document.getElementById('book_email').value = opt.dataset.email;
        }

        function openDetailFromCalendar(event) {
            const p = event.extendedProps;
            renderDetail({
                id: event.id,
                title: p.customer_name,
                service_name: p.service_name,
                room_name: p.room_name,
                customer_phone: p.customer_phone,
                customer_email: p.customer_email,
                customer_id: p.customer_id,
                service_id: p.service_id,
                status: p.status,
                start: event.startStr,
                is_overbooked: p.is_overbooked,
                checked_in_at: p.checked_in_at,
                room_id: p.room_id,
                user_id: p.user_id,
                notes: p.notes,
                slot_id: p.slot_id
            });
            new bootstrap.Modal(document.getElementById('detailModal')).show();
        }

        function openDetailFromList(btn) {
            const data = JSON.parse(btn.dataset.appt);
            renderDetail(data);
            new bootstrap.Modal(document.getElementById('detailModal')).show();
        }

        function renderDetail(data) {
            currentAppt = data;
            document.getElementById('detailName').textContent = data.title || '—';
            document.getElementById('detailSub').textContent = (data.service_name || '') + (data.customer_phone ? '  ·  ' + data.customer_phone : '');
            
            const start = data.start ? new Date(data.start) : null;
            document.getElementById('detailStatusBadge').textContent = ucFirst(data.status || '');

            const body = document.getElementById('detailBody');
            body.innerHTML = `
                <div class="col-md-6"><div class="detail-info-card"><div class="detail-info-label"><i class="bi bi-calendar3 me-1"></i>Date & Time</div><div class="detail-info-value">${start ? start.toLocaleString('{{ app()->getLocale() }}', { dateStyle: 'medium', timeStyle: 'short', timeZone: '{{ app(\App\Services\TimezoneService::class)->resolve() }}' }) : '—'}</div></div>
                </div>
                <div class="col-md-6"><div class="detail-info-card"><div class="detail-info-label"><i class="bi bi-briefcase me-1"></i>Service</div><div class="detail-info-value">${data.service_name || '—'}</div></div>
                </div>
                <div class="col-md-6"><div class="detail-info-card room-card"><div class="detail-info-label text-primary"><i class="bi bi-door-open me-1"></i>Room</div><div class="detail-info-value">${data.room_name || '—'}</div></div></div>
                <div class="col-md-6"><div class="detail-info-card"><div class="detail-info-label"><i class="bi bi-telephone me-1"></i>Customer Contact</div><div class="detail-info-value">${data.customer_phone || data.customer_email || 'No contact details'}</div></div></div>
                ${data.is_overbooked ? `<div class="col-12 mt-2"><span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="bi bi-lightning-fill me-1"></i>Overbooked slot</span></div>` : ''}
                ${data.checked_in_at ? `<div class="col-12 mt-2"><span class="badge bg-info text-dark px-3 py-2 rounded-pill"><i class="bi bi-person-check-fill me-1"></i>Checked in: ${new Date(data.checked_in_at).toLocaleTimeString('{{ app()->getLocale() }}', { timeStyle: 'short', timeZone: '{{ app(\App\Services\TimezoneService::class)->resolve() }}' })}</span></div>` : ''}
                ${data.notes ? `<div class="col-12"><div class="detail-info-card"><div class="detail-info-label"><i class="bi bi-sticky me-1"></i>Notes</div><div class="detail-info-value fw-normal">${data.notes}</div></div></div>` : ''}
            `;

            buildDetailActions(data.status, data.is_overbooked, data.checked_in_at);
            document.getElementById('graceSection').style.display = 'none';
            document.getElementById('cancelForm').style.display = 'none';
        }

        function buildDetailActions(status, isOB, checkedInAt) {
            const area = document.getElementById('detailActions');
            area.innerHTML = '';

            const add = (icon, label, cls, fn) => {
                const btn = document.createElement('button');
                btn.className = `btn btn-sm px-3 py-2 fw-bold shadow-sm rounded-pill ${cls}`;
                btn.innerHTML = `<i class="bi ${icon} me-1"></i>${label}`;
                btn.onclick = fn;
                area.appendChild(btn);
            };

            if (['pending', 'confirmed'].includes(status) && !checkedInAt) {
                add('bi-person-check-fill', '{{ __("ui.check_in_btn") }}', 'btn-success', () => doCheckIn());
            }
            if (['pending', 'confirmed'].includes(status)) {
                add('bi-pencil-square', '{{ __("ui.edit") }}', 'btn-primary', () => openEditAppt());
            }
            if (!['cancelled', 'completed', 'no_show'].includes(status)) {
                add('bi-x-circle-fill', '{{ __("ui.cancel_appointment") }}', 'btn-danger', () => {
                    document.getElementById('cancelForm').style.display = 'block';
                });
                add('bi-check2-all', '{{ __("ui.completed") }}', 'btn-secondary', () => updateStatus('completed'));
            }
            if (!['no_show', 'cancelled', 'completed'].includes(status)) {
                add('bi-person-x-fill', '{{ __("ui.no_show") }}', 'btn-dark', () => updateStatus('no_show'));
            }
        }

        function openEditAppt() {
            if (!currentAppt) return;
            const p = currentAppt;

            bootstrap.Modal.getInstance(document.getElementById('detailModal')).hide();

            document.getElementById('bookForm').reset();
            document.getElementById('book_appt_id').value = p.id;
            document.getElementById('book_customer_name').value = p.title || '';
            document.getElementById('book_phone').value = p.customer_phone || '';
            document.getElementById('book_email').value = p.customer_email || '';

            // Programmatically select customer in the AJAX search component
            if (p.customer_id) {
                const wrapper = document.getElementById('book_customer_id-wrapper');
                if (wrapper && wrapper.selectCustomer) {
                    wrapper.selectCustomer({
                        id: p.customer_id,
                        full_name: p.title || '',
                        phone: p.customer_phone || '',
                        email: p.customer_email || ''
                    });
                } else {
                    document.getElementById('book_customer_id').value = p.customer_id;
                }
            }

            document.getElementById('book_service').value = p.service_id || '';

            const d = p.start ? new Date(p.start) : null;
            if (d) {
                const dateStr = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                document.getElementById('book_date').value = dateStr;
                document.getElementById('book_date').dataset.time = String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0') + ':00';
            }

            document.querySelector('[name="room_id"]').value = p.room_id || '';
            document.querySelector('[name="notes"]').value = p.notes || '';
            document.getElementById('book_slot_id').value = p.slot_id || '';

            if (p.service_id && d) loadSlots();

            document.getElementById('drawerTitle').textContent = 'Edit Appointment';
            document.getElementById('bookSubmitBtn').innerHTML = '<i class="bi bi-save me-2"></i>Save Changes';
            bookDrawer.show();
        }

        // ─────────────────────────────────────────────────────────────────────────────
        // Actions APIs
        // ─────────────────────────────────────────────────────────────────────────────
        function doCheckIn() {
            if (!currentAppt) return;
            fetch(`/appointments/${currentAppt.id}/checkin`, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    showToast('Checked in!', 'success');
                    calendar.refetchEvents();
                    bootstrap.Modal.getInstance(document.getElementById('detailModal')).hide();
                    setTimeout(() => location.reload(), 900);
                } else showToast(data.message || 'Failed to check in.', 'danger');
            });
        }

        function updateStatus(status) {
            if (!currentAppt) return;
            fetch(`/appointments/${currentAppt.id}`, {
                method: 'PUT',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ status })
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    showToast('Status updated!', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('detailModal')).hide();
                    calendar.refetchEvents();
                    setTimeout(() => location.reload(), 900);
                }
            });
        }

        function submitCancel() {
            if (!currentAppt) return;
            const reason = document.getElementById('cancelReason').value;
            fetch(`/appointments/${currentAppt.id}/cancel`, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ reason })
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    showToast('Appointment cancelled.', 'warning');
                    bootstrap.Modal.getInstance(document.getElementById('detailModal')).hide();
                    calendar.refetchEvents();
                    setTimeout(() => location.reload(), 900);
                }
            });
        }

        function rescheduleAppt(id, newDateStr, revert) {
            if (!confirm('Reschedule this appointment to ' + new Date(newDateStr).toLocaleString() + '?')) return revert();
            fetch(`/appointments/${id}`, {
                method: 'PUT',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ appointment_date: newDateStr })
            }).then(r => r.json()).then(data => {
                if (!data.success) { revert(); showToast(data.message || 'Reschedule failed.', 'danger'); }
                else showToast('Rescheduled!', 'success');
            }).catch(() => { revert(); showToast('Network error.', 'danger'); });
        }

        function checkIn(apptId, btn) {
            btn.disabled = true;
            fetch(`/appointments/${apptId}/checkin`, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            }).then(r => r.json()).then(data => {
                if (data.success) { showToast('Checked in!', 'success'); setTimeout(() => location.reload(), 800); }
                else { btn.disabled = false; showToast(data.message || 'Failed.', 'danger'); }
            }).catch(() => { btn.disabled = false; showToast('Network error.', 'danger'); });
        }

        function filterList() {
            const svc = document.getElementById('filterService').value;
            const status = document.getElementById('filterStatus').value;
            document.querySelectorAll('#appointmentsTable tbody tr').forEach(row => {
                const matchSvc = !svc || row.dataset.service === svc;
                const matchStatus = !status || row.dataset.status === status;
                row.style.display = (matchSvc && matchStatus) ? '' : 'none';
            });
        }

        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer') || (() => {
                const el = document.createElement('div');
                el.id = 'toastContainer';
                el.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:106000;display:flex;flex-direction:column;gap:8px';
                document.body.appendChild(el);
                return el;
            })();

            const toast = document.createElement('div');
            toast.className = `alert alert-${type} border-0 shadow-lg rounded-pill fade-in mb-0 px-4 py-3 fw-bold text-white`;
            toast.style.cssText = 'min-width:260px;animation:slideUp .3s ease;';
            if(type === 'success') toast.style.background = 'linear-gradient(135deg, #22c55e, #16a34a)';
            else if(type === 'danger') toast.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
            else if(type === 'warning') toast.style.background = 'linear-gradient(135deg, #f59e0b, #d97706)';

            toast.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}-fill me-2 fs-5 align-middle"></i><span class="align-middle">${message}</span>`;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 3500);
        }

        function ucFirst(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1).replace('_', ' ') : ''; }
    </script>
@endpush
