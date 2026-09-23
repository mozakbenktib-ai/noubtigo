@extends('layouts.dashboard')

@section('title', __('ui.analytics') . ' | Noubtigo')
@section('header_title', __('ui.analytics'))
@section('header_subtitle', __('ui.analytics_subtitle'))

@section('content')
<div class="container-fluid pb-5" id="analyticsPage">

    {{-- ═══ FILTER BAR ═══ --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-3 bg-white d-flex flex-wrap align-items-center gap-3">
                    <span class="fw-medium text-secondary"><i class="bi bi-filter me-1"></i> {{ __('ui.date_range') }}:</span>
                    <div class="btn-group shadow-none" role="group">
                        <a href="{{ route('analytics.index', array_merge(request()->except('range'), ['range' => 'today'])) }}" class="btn btn-sm {{ $range == 'today' ? 'btn-success' : 'btn-outline-secondary' }}">{{ __('ui.today') }}</a>
                        <a href="{{ route('analytics.index', array_merge(request()->except('range'), ['range' => '7days'])) }}" class="btn btn-sm {{ $range == '7days' ? 'btn-success' : 'btn-outline-secondary' }}">{{ __('ui.last_7_days') }}</a>
                        <a href="{{ route('analytics.index', array_merge(request()->except('range'), ['range' => '30days'])) }}" class="btn btn-sm {{ $range == '30days' ? 'btn-success' : 'btn-outline-secondary' }}">{{ __('ui.last_30_days') }}</a>
                    </div>

                    <div class="vr d-none d-md-block"></div>

                    {{-- Service Filter --}}
                    <select id="filterService" class="form-select form-select-sm" style="width:auto;min-width:160px" onchange="applyFilters()">
                        <option value="">{{ __('ui.all_services_filter') }}</option>
                        @foreach($services as $svc)
                            <option value="{{ $svc->id }}" {{ $serviceId == $svc->id ? 'selected' : '' }}>{{ $svc->name }}</option>
                        @endforeach
                    </select>

                    {{-- Staff Filter --}}
                    <select id="filterStaff" class="form-select form-select-sm" style="width:auto;min-width:160px" onchange="applyFilters()">
                        <option value="">{{ __('ui.all_staff') }}</option>
                        @foreach($staffList as $u)
                            <option value="{{ $u->id }}" {{ $staffId == $u->id ? 'selected' : '' }}>{{ $u->first_name }} {{ $u->last_name }}</option>
                        @endforeach
                    </select>

                    {{-- Room Filter --}}
                    <select id="filterRoom" class="form-select form-select-sm" style="width:auto;min-width:160px" onchange="applyFilters()">
                        <option value="">{{ __('ui.all_rooms_filter') }}</option>
                        @foreach($rooms as $r)
                            <option value="{{ $r->id }}" {{ $roomId == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                        @endforeach
                    </select>

                    <div class="ms-auto d-flex gap-2">
                        <a href="{{ route('analytics.export', request()->all()) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-download me-1"></i>{{ __('ui.export_csv') }}</a>
                        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer me-1"></i>{{ __('ui.print_report') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ KPI CARDS ═══ --}}
    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['label' => __('ui.total_tickets'), 'value' => number_format($kpis['total_tickets']), 'icon' => 'bi-ticket-perforated', 'trend' => $kpis['ticket_trend'], 'color' => 'primary'],
                ['label' => __('ui.avg_waiting_time'), 'value' => $kpis['avg_wait_minutes'].' <small class="fs-6 fw-normal">min</small>', 'icon' => 'bi-clock-history', 'trend' => $kpis['wait_trend'] * -1, 'color' => $kpis['avg_wait_minutes'] > 15 ? 'danger' : ($kpis['avg_wait_minutes'] > 5 ? 'warning' : 'success')],
                ['label' => __('ui.avg_service_time'), 'value' => $kpis['avg_service_minutes'].' <small class="fs-6 fw-normal">min</small>', 'icon' => 'bi-lightning-charge', 'trend' => null, 'color' => 'info'],
                ['label' => __('ui.completion_rate'), 'value' => $kpis['completion_rate'].'%', 'icon' => 'bi-check2-circle', 'trend' => null, 'color' => $kpis['completion_rate'] >= 80 ? 'success' : 'warning'],
                ['label' => __('ui.no_show_rate'), 'value' => $kpis['no_show_rate'].'%', 'icon' => 'bi-person-slash', 'trend' => null, 'color' => $kpis['no_show_rate'] > 10 ? 'danger' : 'success'],
                ['label' => __('ui.cancellation_rate'), 'value' => $kpis['cancellation_rate'].'%', 'icon' => 'bi-x-circle', 'trend' => null, 'color' => $kpis['cancellation_rate'] > 15 ? 'danger' : 'success'],
                ['label' => __('ui.customer_throughput'), 'value' => $kpis['throughput_per_hour'].' <small class="fs-6 fw-normal">'.__('ui.per_hour').'</small>', 'icon' => 'bi-speedometer2', 'trend' => null, 'color' => 'primary'],
                ['label' => __('ui.new_customers'), 'value' => number_format($kpis['new_customers']), 'icon' => 'bi-person-plus', 'trend' => null, 'color' => 'info'],
            ];
        @endphp

        @foreach($cards as $card)
        <div class="col-6 col-md-4 col-xl-3">
            <div class="analytics-kpi-card card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-body p-3 position-relative">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="kpi-label text-secondary text-uppercase fw-bold" style="font-size:.7rem;letter-spacing:.5px">{{ $card['label'] }}</span>
                        <span class="kpi-icon-badge badge bg-{{ $card['color'] }} bg-opacity-10 text-{{ $card['color'] }} rounded-3 p-2">
                            <i class="bi {{ $card['icon'] }}"></i>
                        </span>
                    </div>
                    <h3 class="fw-bold mb-1">{!! $card['value'] !!}</h3>
                    @if($card['trend'] !== null && $card['trend'] != 0)
                        <small class="{{ $card['trend'] > 0 ? 'text-success' : 'text-danger' }}">
                            <i class="bi {{ $card['trend'] > 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short' }}"></i>
                            {{ abs($card['trend']) }}% <span class="text-muted">{{ __('ui.compared_to_previous') }}</span>
                        </small>
                    @endif
                </div>
                <div class="kpi-accent-bar bg-{{ $card['color'] }}" style="height:3px"></div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ═══ CHARTS ROW 1: Peak Hours + Service Distribution ═══ --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart-fill text-success me-2"></i>{{ __('ui.peak_hours') }}</h6>
                        <div class="btn-group btn-group-sm" id="peakToggle">
                            <button class="btn btn-outline-secondary btn-sm active" onclick="showPeakBar(this)"><i class="bi bi-bar-chart"></i></button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="showPeakHeatmap(this)"><i class="bi bi-grid-3x3"></i> {{ __('ui.heatmap') }}</button>
                        </div>
                    </div>
                    <div id="peakBarWrap" style="height:280px"><canvas id="peakHoursChart"></canvas></div>
                    <div id="peakHeatmapWrap" style="display:none">
                        @include('analytics._heatmap', ['heatmap' => $heatmap])
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart-fill text-info me-2"></i>{{ __('ui.service_distribution') }}</h6>
                    <div style="height:280px"><canvas id="serviceDistChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ CHARTS ROW 2: Volume Trend + Wait Time Trend ═══ --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-graph-up text-primary me-2"></i>{{ __('ui.volume_trend') }}</h6>
                    <div style="height:250px"><canvas id="volumeChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-hourglass-split text-warning me-2"></i>{{ __('ui.wait_time_trend') }}</h6>
                    <div style="height:250px"><canvas id="waitTrendChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ SMART INSIGHTS ═══ --}}
    @if(!empty($insights))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-lightbulb-fill text-warning me-2"></i>{{ __('ui.smart_insights') }}</h6>
                    <div class="row g-3">
                        @foreach($insights as $insight)
                        <div class="col-md-6 col-xl-4">
                            <div class="insight-card d-flex gap-3 p-3 rounded-3 border-start border-4 border-{{ $insight['type'] === 'success' ? 'success' : ($insight['type'] === 'danger' ? 'danger' : ($insight['type'] === 'warning' ? 'warning' : 'info')) }} bg-{{ $insight['type'] }}-subtle">
                                <div class="flex-shrink-0">
                                    <i class="bi {{ $insight['icon'] }} fs-4 text-{{ $insight['type'] === 'success' ? 'success' : ($insight['type'] === 'danger' ? 'danger' : ($insight['type'] === 'warning' ? 'warning' : 'info')) }}"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold small">{{ $insight['title'] }}</div>
                                    <div class="text-secondary" style="font-size:.82rem">{{ $insight['message'] }}</div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══ STAFF PERFORMANCE TABLE ═══ --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-people-fill text-warning me-2"></i>{{ __('ui.staff_performance') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size:.88rem">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 rounded-start-2 px-3">{{ __('ui.operator') }}</th>
                                    <th class="border-0 text-center">{{ __('ui.tickets_handled') }}</th>
                                    <th class="border-0 text-center">{{ __('ui.avg_service_time') }}</th>
                                    <th class="border-0 text-center">{{ __('ui.idle_time') }}</th>
                                    <th class="border-0 text-center rounded-end-2">{{ __('ui.efficiency_score') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($staffPerformance as $staff)
                                <tr>
                                    <td class="px-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($staff['full_name']) }}&background=06b6d4&color=fff&size=28" class="rounded-circle" width="28">
                                            <div>
                                                <span class="fw-medium">{{ $staff['full_name'] }}</span>
                                                @if($staff['speed_vs_team'] > 15)
                                                    <span class="badge bg-success-subtle text-success ms-1" style="font-size:.65rem">{{ $staff['speed_vs_team'] }}% {{ __('ui.faster') }}</span>
                                                @elseif($staff['speed_vs_team'] < -15)
                                                    <span class="badge bg-danger-subtle text-danger ms-1" style="font-size:.65rem">{{ abs($staff['speed_vs_team']) }}% {{ __('ui.slower') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center"><span class="badge bg-light text-dark fw-bold border">{{ $staff['tickets_handled'] }}</span></td>
                                    <td class="text-center text-secondary fw-semibold">{{ $staff['avg_service_time'] }} {{ __('ui.mins') }}</td>
                                    <td class="text-center text-secondary">{{ $staff['idle_minutes'] }} {{ __('ui.mins') }}</td>
                                    <td class="text-center" style="min-width:130px">
                                        <div class="d-flex align-items-center gap-2 justify-content-center">
                                            <div class="progress flex-grow-1" style="height:6px;max-width:80px">
                                                <div class="progress-bar bg-{{ $staff['efficiency_score'] >= 70 ? 'success' : ($staff['efficiency_score'] >= 40 ? 'warning' : 'danger') }}" style="width:{{ $staff['efficiency_score'] }}%"></div>
                                            </div>
                                            <span class="fw-bold small">{{ $staff['efficiency_score'] }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-secondary opacity-50">{{ __('ui.queue_is_empty') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ WAIT TIME INTELLIGENCE ═══ --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-bullseye text-danger me-2"></i>{{ __('ui.wait_time_intelligence') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size:.85rem">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 rounded-start-2 px-3">{{ __('ui.service') }}</th>
                                    <th class="border-0 text-center">{{ __('ui.expected_duration') }}</th>
                                    <th class="border-0 text-center">{{ __('ui.actual_duration') }}</th>
                                    <th class="border-0 text-center rounded-end-2">{{ __('ui.accuracy') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($waitAccuracy as $wa)
                                <tr>
                                    <td class="px-3 fw-medium">{{ $wa['name'] }}</td>
                                    <td class="text-center text-secondary">{{ $wa['expected_minutes'] }} {{ __('ui.mins') }}</td>
                                    <td class="text-center text-secondary">{{ $wa['actual_minutes'] }} {{ __('ui.mins') }}</td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill bg-{{ $wa['accuracy_pct'] >= 90 ? 'success' : ($wa['accuracy_pct'] >= 70 ? 'warning' : 'danger') }}-subtle text-{{ $wa['accuracy_pct'] >= 90 ? 'success' : ($wa['accuracy_pct'] >= 70 ? 'warning' : 'danger') }} fw-bold">
                                            {{ $wa['accuracy_pct'] }}%
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-secondary opacity-50">{{ __('ui.queue_is_empty') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ ROOM PERFORMANCE & CANCELLATIONS ═══ --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-door-open-fill text-primary me-2"></i>{{ __('ui.room_performance') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size:.88rem">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 rounded-start-2 px-3">{{ __('ui.room') }}</th>
                                    <th class="border-0 text-center">{{ __('ui.tickets_handled') }}</th>
                                    <th class="border-0 text-center">{{ __('ui.avg_service_time') }}</th>
                                    <th class="border-0 text-center rounded-end-2">{{ __('ui.staff_count') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roomPerformance as $room)
                                <tr>
                                    <td class="px-3 fw-medium">{{ $room['name'] }}</td>
                                    <td class="text-center"><span class="badge bg-light text-dark fw-bold border">{{ $room['tickets_handled'] }}</span></td>
                                    <td class="text-center text-secondary fw-semibold">{{ round($room['avg_service_time'], 1) }} {{ __('ui.mins') }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-info-subtle text-info px-3">{{ $room['staff_count'] }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-secondary opacity-50">{{ __('ui.queue_is_empty') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-x-circle-fill text-danger me-2"></i>{{ __('ui.cancellation_reasons_distribution') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size:.88rem">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 rounded-start-2 px-3">{{ __('ui.cancellation_note') }}</th>
                                    <th class="border-0 text-center">{{ __('ui.tickets') }}</th>
                                    <th class="border-0 text-center rounded-end-2">{{ __('ui.percentage') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalCancelled = array_sum(array_column($cancellationReasonsDistribution, 'count'));
                                @endphp
                                @forelse($cancellationReasonsDistribution as $crd)
                                    @php
                                        $reasonPct = $totalCancelled > 0 ? round(($crd['count'] / $totalCancelled) * 100, 1) : 0;
                                        $reasonSnake = str_replace(' ', '_', strtolower($crd['reason']));
                                        $translatedReason = __('ui.cancel_reason_' . $reasonSnake);
                                        if (str_starts_with($translatedReason, 'ui.cancel_reason_')) {
                                            $translatedReason = $crd['reason'];
                                        }
                                    @endphp
                                    <tr>
                                        <td class="px-3 fw-medium">{{ $translatedReason }}</td>
                                        <td class="text-center"><span class="badge bg-light text-dark fw-bold border">{{ $crd['count'] }}</span></td>
                                        <td class="text-center" style="min-width:130px">
                                            <div class="d-flex align-items-center gap-2 justify-content-center">
                                                <div class="progress flex-grow-1" style="height:6px;max-width:80px">
                                                    <div class="progress-bar bg-danger" style="width:{{ $reasonPct }}%"></div>
                                                </div>
                                                <span class="fw-bold small">{{ $reasonPct }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-secondary opacity-50">{{ __('ui.no_active_ticket') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

<style>
.analytics-kpi-card { transition: transform .2s, box-shadow .2s; }
.analytics-kpi-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,.1) !important; }
.insight-card { transition: transform .15s; }
.insight-card:hover { transform: translateX(4px); }
.bg-success-subtle { background-color: rgba(34,197,94,.08) !important; }
.bg-danger-subtle { background-color: rgba(239,68,68,.08) !important; }
.bg-warning-subtle { background-color: rgba(245,158,11,.08) !important; }
.bg-info-subtle { background-color: rgba(6,182,212,.08) !important; }

@media print {
    .sidebar, header, .card-body form, .btn-group, .ms-auto { display: none !important; }
    .main-content { margin: 0 !important; padding: 1rem !important; }
    .analytics-kpi-card:hover { transform: none; box-shadow: none !important; }
}

/* Heatmap styles */
.heatmap-grid { display: grid; grid-template-columns: 60px repeat(24, 1fr); gap: 2px; font-size: .7rem; }
.heatmap-cell { aspect-ratio: 1; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-weight: 600; transition: transform .1s; cursor: default; }
.heatmap-cell:hover { transform: scale(1.3); z-index: 2; }
.heatmap-label { display: flex; align-items: center; font-weight: 600; color: #64748b; font-size: .72rem; }
.heatmap-header { display: flex; align-items: flex-end; justify-content: center; font-weight: 500; color: #94a3b8; font-size: .65rem; }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const getBrand = v => getComputedStyle(document.documentElement).getPropertyValue(v).trim();
const P = getBrand('--primary-color'), S = getBrand('--secondary-color');
Chart.defaults.font.family = '{{ app()->getLocale() == "ar" ? "Cairo" : "Inter" }}, sans-serif';
Chart.defaults.color = '#64748b';

// ── Peak Hours Bar ──
new Chart(document.getElementById('peakHoursChart'), {
    type: 'bar',
    data: {
        labels: Array.from({length:24}, (_,i) => `${i}:00`),
        datasets: [{ label: '{{ __("ui.tickets_by_hour") }}', data: @json($peakHoursFlat), backgroundColor: P, borderRadius: 5, hoverBackgroundColor: S }]
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true,grid:{display:false}},x:{grid:{display:false}}} }
});

// ── Service Distribution ──
const svcNames = @json(collect($serviceDistribution)->pluck('name'));
const svcCounts = @json(collect($serviceDistribution)->pluck('ticket_count'));
new Chart(document.getElementById('serviceDistChart'), {
    type: 'doughnut',
    data: { labels: svcNames, datasets: [{ data: svcCounts, backgroundColor: [P, S, '#3b82f6','#f59e0b','#ef4444','#8b5cf6','#ec4899'], borderWidth:0, cutout:'70%' }] },
    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'bottom',labels:{usePointStyle:true,padding:15,font:{size:11}}}} }
});

// ── Volume Trend ──
const volDates = @json(collect($volumeTrend)->pluck('local_date'));
const volCounts = @json(collect($volumeTrend)->pluck('count'));
new Chart(document.getElementById('volumeChart'), {
    type: 'line',
    data: { labels: volDates, datasets: [{ label: '{{ __("ui.tickets_per_day") }}', data: volCounts, borderColor: P, backgroundColor: P+'18', borderWidth:2.5, fill:true, tension:.4, pointRadius:3, pointBackgroundColor:'#fff', pointBorderWidth:2 }] },
    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true,grid:{color:'#f1f5f9'}},x:{grid:{display:false}}} }
});

// ── Wait Trend ──
const waitDates = @json(collect($waitTrend)->pluck('local_date'));
const waitVals = @json(collect($waitTrend)->pluck('avg_wait'));
new Chart(document.getElementById('waitTrendChart'), {
    type: 'line',
    data: { labels: waitDates, datasets: [{ label: '{{ __("ui.avg_wait_per_day") }}', data: waitVals, borderColor: S, backgroundColor: S+'18', borderWidth:2.5, fill:true, tension:.4, pointRadius:3, pointBackgroundColor:'#fff', pointBorderWidth:2 }] },
    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true,grid:{color:'#f1f5f9'}},x:{grid:{display:false}}} }
});

// ── Heatmap / Bar toggle ──
function showPeakBar(btn) {
    document.getElementById('peakBarWrap').style.display = '';
    document.getElementById('peakHeatmapWrap').style.display = 'none';
    document.querySelectorAll('#peakToggle .btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}
function showPeakHeatmap(btn) {
    document.getElementById('peakBarWrap').style.display = 'none';
    document.getElementById('peakHeatmapWrap').style.display = '';
    document.querySelectorAll('#peakToggle .btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

// ── Filter navigation ──
function applyFilters() {
    const svc = document.getElementById('filterService').value;
    const staff = document.getElementById('filterStaff').value;
    const room = document.getElementById('filterRoom').value;
    const params = new URLSearchParams(window.location.search);
    svc ? params.set('service_id', svc) : params.delete('service_id');
    staff ? params.set('staff_id', staff) : params.delete('staff_id');
    room ? params.set('room_id', room) : params.delete('room_id');
    window.location.href = '{{ route("analytics.index") }}?' + params.toString();
}
</script>
@endpush
