<?php

namespace App\Modules\Reports\Services;

use App\Modules\Queue\Models\Ticket;
use App\Modules\Customers\Models\Customer;
use App\Modules\Services\Models\Service;
use App\Models\User;
use App\Services\TimezoneService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    protected int $offsetMinutes;
    protected string $companyTz;

    public function __construct(protected TimezoneService $tz)
    {
        $this->companyTz = $this->tz->resolve();
        $this->offsetMinutes = Carbon::now($this->companyTz)->offsetMinutes;
    }

    // ── Overview KPIs ──────────────────────────────────────────────────

    public function getOverviewKPIs(Carbon $from, Carbon $to, ?int $serviceId = null, ?int $staffId = null, ?int $roomId = null): array
    {
        $query = $this->baseQuery($from, $to, $serviceId, $staffId, $roomId);

        $totalTickets = (clone $query)->count();

        $avgWaitTime = (clone $query)
            ->whereNotNull('tickets.called_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, tickets.waited_since, tickets.called_at)) as avg_wait')
            ->value('avg_wait') ?? 0;

        $avgServiceTime = (clone $query)
            ->whereNotNull('tickets.started_at')
            ->whereNotNull('tickets.finished_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, tickets.started_at, tickets.finished_at)) as avg_svc')
            ->value('avg_svc') ?? 0;

        $doneCount      = (clone $query)->where('tickets.status', 'done')->count();
        $noShowCount     = (clone $query)->where('tickets.status', 'no_show')->count();
        $cancelledCount  = (clone $query)->where('tickets.status', 'cancelled')->count();

        $completionRate   = $totalTickets > 0 ? ($doneCount / $totalTickets) * 100 : 0;
        $noShowRate       = $totalTickets > 0 ? ($noShowCount / $totalTickets) * 100 : 0;
        $cancellationRate = $totalTickets > 0 ? ($cancelledCount / $totalTickets) * 100 : 0;

        $newCustomers = Customer::where('created_at', '>=', $from)
            ->where('created_at', '<=', $to)
            ->count();

        // Customer throughput: served customers per hour across the date range
        $hoursInRange = max(1, $from->diffInHours($to));
        $throughputPerHour = $totalTickets > 0 ? round($doneCount / $hoursInRange, 1) : 0;

        // Previous period comparison for trend
        $periodLength = $from->diffInDays($to);
        $prevFrom = $from->copy()->subDays($periodLength);
        $prevTo = $from->copy();
        $prevQuery = $this->baseQuery($prevFrom, $prevTo, $serviceId, $staffId, $roomId);
        $prevTotalTickets = (clone $prevQuery)->count();

        $ticketTrend = $prevTotalTickets > 0
            ? round((($totalTickets - $prevTotalTickets) / $prevTotalTickets) * 100, 1)
            : 0;

        $prevAvgWait = (clone $prevQuery)
            ->whereNotNull('tickets.called_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, tickets.waited_since, tickets.called_at)) as avg_wait')
            ->value('avg_wait') ?? 0;

        $waitTrend = $prevAvgWait > 0
            ? round((($avgWaitTime - $prevAvgWait) / $prevAvgWait) * 100, 1)
            : 0;

        return [
            'total_tickets'     => $totalTickets,
            'avg_wait_seconds'  => round($avgWaitTime),
            'avg_wait_minutes'  => round($avgWaitTime / 60, 1),
            'avg_service_seconds' => round($avgServiceTime),
            'avg_service_minutes' => round($avgServiceTime / 60, 1),
            'completion_rate'   => round($completionRate, 1),
            'no_show_rate'      => round($noShowRate, 1),
            'cancellation_rate' => round($cancellationRate, 1),
            'new_customers'     => $newCustomers,
            'throughput_per_hour' => $throughputPerHour,
            'done_count'        => $doneCount,
            'no_show_count'     => $noShowCount,
            'cancelled_count'   => $cancelledCount,
            'ticket_trend'      => $ticketTrend,
            'wait_trend'        => $waitTrend,
        ];
    }

    // ── Peak Hours Heatmap (7×24 matrix) ───────────────────────────────

    public function getPeakHoursHeatmap(Carbon $from, Carbon $to, ?int $serviceId = null, ?int $staffId = null, ?int $roomId = null): array
    {
        $offset = $this->offsetMinutes;

        $rows = $this->baseQuery($from, $to, $serviceId, $staffId, $roomId)
            ->selectRaw("DAYOFWEEK(DATE_ADD(tickets.waited_since, INTERVAL {$offset} MINUTE)) as dow,
                          HOUR(DATE_ADD(tickets.waited_since, INTERVAL {$offset} MINUTE)) as hr,
                          COUNT(*) as cnt")
            ->groupBy('dow', 'hr')
            ->get();

        // Build 7×24 matrix (1=Sunday ... 7=Saturday in MySQL DAYOFWEEK)
        $matrix = [];
        for ($d = 1; $d <= 7; $d++) {
            $matrix[$d] = array_fill(0, 24, 0);
        }
        foreach ($rows as $row) {
            $matrix[$row->dow][$row->hr] = $row->cnt;
        }

        return $matrix;
    }

    // ── Queue Volume Trend ─────────────────────────────────────────────

    public function getQueueVolumeTrend(Carbon $from, Carbon $to, ?int $serviceId = null, ?int $staffId = null, ?int $roomId = null): array
    {
        $offset = $this->offsetMinutes;

        return $this->baseQuery($from, $to, $serviceId, $staffId, $roomId)
            ->selectRaw("DATE(DATE_ADD(tickets.created_at, INTERVAL {$offset} MINUTE)) as local_date,
                          COUNT(*) as count")
            ->groupBy('local_date')
            ->orderBy('local_date')
            ->get()
            ->toArray();
    }

    // ── Wait Time Trend ────────────────────────────────────────────────

    public function getWaitTimeTrend(Carbon $from, Carbon $to, ?int $serviceId = null, ?int $staffId = null, ?int $roomId = null): array
    {
        $offset = $this->offsetMinutes;

        return $this->baseQuery($from, $to, $serviceId, $staffId, $roomId)
            ->whereNotNull('tickets.called_at')
            ->selectRaw("DATE(DATE_ADD(tickets.created_at, INTERVAL {$offset} MINUTE)) as local_date,
                          AVG(TIMESTAMPDIFF(MINUTE, tickets.waited_since, tickets.called_at)) as avg_wait")
            ->groupBy('local_date')
            ->orderBy('local_date')
            ->get()
            ->toArray();
    }

    // ── Service Distribution ───────────────────────────────────────────

    public function getServiceDistribution(Carbon $from, Carbon $to, ?int $serviceId = null, ?int $staffId = null, ?int $roomId = null): array
    {
        $query = $this->baseQuery($from, $to, $serviceId, $staffId, $roomId)
            ->join('services', 'tickets.service_id', '=', 'services.id')
            ->selectRaw('services.id as service_id, services.name,
                          COUNT(*) as ticket_count,
                          AVG(TIMESTAMPDIFF(MINUTE, tickets.waited_since, tickets.called_at)) as avg_wait,
                          AVG(TIMESTAMPDIFF(MINUTE, tickets.started_at, tickets.finished_at)) as avg_service,
                          services.duration_minutes as expected_duration')
            ->groupBy('services.id', 'services.name', 'services.duration_minutes')
            ->orderBy('ticket_count', 'desc')
            ->get();

        return $query->toArray();
    }

    // ── Service Bottlenecks ────────────────────────────────────────────

    public function getServiceBottlenecks(Carbon $from, Carbon $to, ?int $serviceId = null, ?int $staffId = null, ?int $roomId = null): array
    {
        $services = $this->getServiceDistribution($from, $to, $serviceId, $staffId, $roomId);

        if (empty($services)) return [];

        $globalAvgWait = collect($services)->avg('avg_wait') ?? 0;

        $bottlenecks = [];
        foreach ($services as $svc) {
            $wait = $svc['avg_wait'] ?? 0;
            $deviation = $globalAvgWait > 0
                ? round((($wait - $globalAvgWait) / $globalAvgWait) * 100, 1)
                : 0;

            $bottlenecks[] = array_merge($svc, [
                'deviation_pct' => $deviation,
                'is_bottleneck' => $deviation > 20,
            ]);
        }

        usort($bottlenecks, fn($a, $b) => $b['deviation_pct'] <=> $a['deviation_pct']);

        return $bottlenecks;
    }

    // ── Staff Performance ──────────────────────────────────────────────

    public function getStaffPerformance(Carbon $from, Carbon $to, ?int $serviceId = null, ?int $staffId = null, ?int $roomId = null): array
    {
        $offset = $this->offsetMinutes;

        $query = $this->baseQuery($from, $to, $serviceId, $staffId, $roomId)
            ->whereNotNull('tickets.user_id')
            ->join('users', 'tickets.user_id', '=', 'users.id')
            ->selectRaw("
                users.id,
                CONCAT(users.first_name, ' ', users.last_name) as full_name,
                COUNT(*) as tickets_handled,
                SUM(CASE WHEN tickets.status = 'done' THEN 1 ELSE 0 END) as tickets_completed,
                AVG(TIMESTAMPDIFF(MINUTE, tickets.started_at, tickets.finished_at)) as avg_service_time,
                MIN(DATE_ADD(tickets.waited_since, INTERVAL {$offset} MINUTE)) as first_ticket_time,
                MAX(DATE_ADD(COALESCE(tickets.finished_at, tickets.created_at), INTERVAL {$offset} MINUTE)) as last_ticket_time,
                SUM(TIMESTAMPDIFF(MINUTE, tickets.started_at, tickets.finished_at)) as total_service_minutes
            ")
            ->groupBy('users.id', 'full_name')
            ->orderBy('tickets_handled', 'desc')
            ->get();

        $teamAvgServiceTime = $query->avg('avg_service_time') ?? 1;

        return $query->map(function ($staff) use ($teamAvgServiceTime) {
            $firstTicket = $staff->first_ticket_time ? Carbon::parse($staff->first_ticket_time) : null;
            $lastTicket  = $staff->last_ticket_time  ? Carbon::parse($staff->last_ticket_time) : null;

            $totalWorkMinutes = ($firstTicket && $lastTicket)
                ? max(1, $firstTicket->diffInMinutes($lastTicket))
                : 1;

            $totalServiceMinutes = $staff->total_service_minutes ?? 0;
            $idleMinutes = max(0, $totalWorkMinutes - $totalServiceMinutes);
            $efficiencyScore = min(100, round(($totalServiceMinutes / $totalWorkMinutes) * 100, 1));

            // Speed comparison against team average
            $speedVsTeam = $teamAvgServiceTime > 0
                ? round((($teamAvgServiceTime - ($staff->avg_service_time ?? 0)) / $teamAvgServiceTime) * 100, 1)
                : 0;

            return [
                'id'                 => $staff->id,
                'full_name'          => $staff->full_name,
                'tickets_handled'    => $staff->tickets_handled,
                'tickets_completed'  => $staff->tickets_completed,
                'avg_service_time'   => round($staff->avg_service_time ?? 0, 1),
                'idle_minutes'       => round($idleMinutes),
                'efficiency_score'   => $efficiencyScore,
                'speed_vs_team'      => $speedVsTeam,
            ];
        })->toArray();
    }

    // ── Room Performance ──────────────────────────────────────────────

    public function getRoomPerformance(Carbon $from, Carbon $to, ?int $serviceId = null, ?int $staffId = null, ?int $roomId = null): array
    {
        return $this->baseQuery($from, $to, $serviceId, $staffId, $roomId)
            ->whereNotNull('tickets.room_id')
            ->join('rooms', 'tickets.room_id', '=', 'rooms.id')
            ->selectRaw("
                rooms.id,
                rooms.name,
                COUNT(*) as tickets_handled,
                AVG(TIMESTAMPDIFF(MINUTE, tickets.started_at, tickets.finished_at)) as avg_service_time,
                COUNT(DISTINCT tickets.user_id) as staff_count
            ")
            ->groupBy('rooms.id', 'rooms.name')
            ->orderBy('tickets_handled', 'desc')
            ->get()
            ->toArray();
    }

    // ── Wait Time Accuracy ─────────────────────────────────────────────

    public function getWaitTimeAccuracy(Carbon $from, Carbon $to, ?int $serviceId = null, ?int $staffId = null, ?int $roomId = null): array
    {
        return $this->baseQuery($from, $to, $serviceId, $staffId, $roomId)
            ->join('services', 'tickets.service_id', '=', 'services.id')
            ->whereNotNull('tickets.started_at')
            ->whereNotNull('tickets.finished_at')
            ->selectRaw("
                services.id as service_id,
                services.name,
                services.duration_minutes as expected_minutes,
                AVG(TIMESTAMPDIFF(MINUTE, tickets.started_at, tickets.finished_at)) as actual_minutes,
                COUNT(*) as sample_size
            ")
            ->groupBy('services.id', 'services.name', 'services.duration_minutes')
            ->orderBy('services.name')
            ->get()
            ->map(function ($row) {
                $expected = $row->expected_minutes ?: 1;
                $actual   = $row->actual_minutes ?: 0;
                $accuracy = max(0, 100 - abs(($actual - $expected) / $expected) * 100);

                return [
                    'service_id'       => $row->service_id,
                    'name'             => $row->name,
                    'expected_minutes' => $row->expected_minutes,
                    'actual_minutes'   => round($actual, 1),
                    'accuracy_pct'     => round($accuracy, 1),
                    'sample_size'      => $row->sample_size,
                ];
            })
            ->toArray();
    }

    // ── Smart Insights Engine ──────────────────────────────────────────

    public function generateInsights(Carbon $from, Carbon $to, ?int $serviceId = null, ?int $staffId = null, ?int $roomId = null): array
    {
        $insights = [];
        $kpis = $this->getOverviewKPIs($from, $to, $serviceId, $staffId, $roomId);

        // 1. Peak hours insight
        $heatmap = $this->getPeakHoursHeatmap($from, $to, $serviceId, $staffId, $roomId);
        $peakHour = $this->findPeakHour($heatmap);
        if ($peakHour) {
            $insights[] = [
                'type'    => 'info',
                'icon'    => 'bi-clock-fill',
                'title'   => __('ui.analytics_insight_peak_hours'),
                'message' => __('ui.analytics_insight_peak_hours_msg', [
                    'start' => $peakHour['start'],
                    'end'   => $peakHour['end'],
                    'days'  => $peakHour['days'],
                ]),
            ];
        }

        // 2. High no-show rate
        if ($kpis['no_show_rate'] > 10) {
            $highNoShowDay = $this->findHighNoShowDay($from, $to);
            $insights[] = [
                'type'    => 'danger',
                'icon'    => 'bi-exclamation-triangle-fill',
                'title'   => __('ui.analytics_insight_high_no_show'),
                'message' => __('ui.analytics_insight_high_no_show_msg', [
                    'rate' => $kpis['no_show_rate'],
                    'day'  => $highNoShowDay,
                ]),
            ];
        }

        // 3. High cancellation rate
        if ($kpis['cancellation_rate'] > 15) {
            $insights[] = [
                'type'    => 'warning',
                'icon'    => 'bi-x-circle-fill',
                'title'   => __('ui.analytics_insight_high_cancellation'),
                'message' => __('ui.analytics_insight_high_cancellation_msg', [
                    'rate' => $kpis['cancellation_rate'],
                ]),
            ];
        }

        // 4. Service bottleneck detection
        $bottlenecks = $this->getServiceBottlenecks($from, $to, $serviceId, $staffId, $roomId);
        foreach ($bottlenecks as $bn) {
            if ($bn['is_bottleneck'] && $bn['deviation_pct'] > 30) {
                $insights[] = [
                    'type'    => 'warning',
                    'icon'    => 'bi-cone-striped',
                    'title'   => __('ui.analytics_insight_bottleneck'),
                    'message' => __('ui.analytics_insight_bottleneck_msg', [
                        'service'   => $bn['name'],
                        'deviation' => abs($bn['deviation_pct']),
                    ]),
                ];
                break; // Show only the worst bottleneck
            }
        }

        // 5. Staff speed insight
        $staffPerf = $this->getStaffPerformance($from, $to, $serviceId, $staffId, $roomId);
        if (!empty($staffPerf)) {
            $fastest = collect($staffPerf)->sortBy('avg_service_time')->first();
            if ($fastest && $fastest['speed_vs_team'] > 20) {
                $insights[] = [
                    'type'    => 'success',
                    'icon'    => 'bi-lightning-charge-fill',
                    'title'   => __('ui.analytics_insight_fast_staff'),
                    'message' => __('ui.analytics_insight_fast_staff_msg', [
                        'name'    => $fastest['full_name'],
                        'percent' => $fastest['speed_vs_team'],
                    ]),
                ];
            }
        }

        // 6. Good completion rate
        if ($kpis['completion_rate'] > 85 && $kpis['total_tickets'] > 10) {
            $insights[] = [
                'type'    => 'success',
                'icon'    => 'bi-trophy-fill',
                'title'   => __('ui.analytics_insight_great_completion'),
                'message' => __('ui.analytics_insight_great_completion_msg', [
                    'rate' => $kpis['completion_rate'],
                ]),
            ];
        }

        // 7. Ticket volume trend
        if ($kpis['ticket_trend'] > 20) {
            $insights[] = [
                'type'    => 'info',
                'icon'    => 'bi-graph-up-arrow',
                'title'   => __('ui.analytics_insight_volume_surge'),
                'message' => __('ui.analytics_insight_volume_surge_msg', [
                    'percent' => $kpis['ticket_trend'],
                ]),
            ];
        } elseif ($kpis['ticket_trend'] < -20) {
            $insights[] = [
                'type'    => 'warning',
                'icon'    => 'bi-graph-down-arrow',
                'title'   => __('ui.analytics_insight_volume_drop'),
                'message' => __('ui.analytics_insight_volume_drop_msg', [
                    'percent' => abs($kpis['ticket_trend']),
                ]),
            ];
        }

        // 8. Wait time improvement/degradation
        if ($kpis['wait_trend'] > 25) {
            $insights[] = [
                'type'    => 'danger',
                'icon'    => 'bi-hourglass-split',
                'title'   => __('ui.analytics_insight_wait_increase'),
                'message' => __('ui.analytics_insight_wait_increase_msg', [
                    'percent' => $kpis['wait_trend'],
                ]),
            ];
        } elseif ($kpis['wait_trend'] < -15) {
            $insights[] = [
                'type'    => 'success',
                'icon'    => 'bi-speedometer2',
                'title'   => __('ui.analytics_insight_wait_decrease'),
                'message' => __('ui.analytics_insight_wait_decrease_msg', [
                    'percent' => abs($kpis['wait_trend']),
                ]),
            ];
        }

        // If no insights generated, add a neutral one
        if (empty($insights)) {
            $insights[] = [
                'type'    => 'info',
                'icon'    => 'bi-check-circle-fill',
                'title'   => __('ui.analytics_insight_all_good'),
                'message' => __('ui.analytics_insight_all_good_msg'),
            ];
        }

        return $insights;
    }

    // ── Helper: Base Query Builder ─────────────────────────────────────

    protected function baseQuery(Carbon $from, Carbon $to, ?int $serviceId = null, ?int $staffId = null, ?int $roomId = null)
    {
        $query = Ticket::where('tickets.created_at', '>=', $from)
            ->where('tickets.created_at', '<=', $to);

        if ($serviceId) {
            $query->where('tickets.service_id', $serviceId);
        }

        if ($staffId) {
            $query->where('tickets.user_id', $staffId);
        }

        if ($roomId) {
            $query->where('tickets.room_id', $roomId);
        }

        return $query;
    }

    // ── Helper: Find Peak Hour from Heatmap ────────────────────────────

    protected function findPeakHour(array $heatmap): ?array
    {
        $hourTotals = array_fill(0, 24, 0);
        foreach ($heatmap as $dayData) {
            foreach ($dayData as $hour => $count) {
                $hourTotals[$hour] += $count;
            }
        }

        $maxCount = max($hourTotals);
        if ($maxCount === 0) return null;

        $peakHour = array_search($maxCount, $hourTotals);

        // Find contiguous peak window (hours with >60% of max)
        $threshold = $maxCount * 0.6;
        $start = $peakHour;
        $end = $peakHour;

        while ($start > 0 && $hourTotals[$start - 1] >= $threshold) $start--;
        while ($end < 23 && $hourTotals[$end + 1] >= $threshold) $end++;

        // Find peak days
        $dayNames = [1 => 'Sun', 2 => 'Mon', 3 => 'Tue', 4 => 'Wed', 5 => 'Thu', 6 => 'Fri', 7 => 'Sat'];
        $dayTotals = [];
        foreach ($heatmap as $dow => $hours) {
            $dayTotals[$dow] = array_sum($hours);
        }
        arsort($dayTotals);
        $topDays = array_slice(array_keys($dayTotals), 0, 3);
        $dayLabels = array_map(fn($d) => $dayNames[$d] ?? '', $topDays);

        return [
            'start' => sprintf('%02d:00', $start),
            'end'   => sprintf('%02d:00', $end + 1),
            'days'  => implode(', ', $dayLabels),
        ];
    }

    // ── Helper: Find High No-Show Day ──────────────────────────────────

    protected function findHighNoShowDay(Carbon $from, Carbon $to): string
    {
        $offset = $this->offsetMinutes;
        $dayNames = [1 => 'Sunday', 2 => 'Monday', 3 => 'Tuesday', 4 => 'Wednesday', 5 => 'Thursday', 6 => 'Friday', 7 => 'Saturday'];

        $result = Ticket::where('tickets.created_at', '>=', $from)
            ->where('tickets.created_at', '<=', $to)
            ->where('tickets.status', 'no_show')
            ->selectRaw("DAYOFWEEK(DATE_ADD(tickets.created_at, INTERVAL {$offset} MINUTE)) as dow, COUNT(*) as cnt")
            ->groupBy('dow')
            ->orderBy('cnt', 'desc')
            ->first();

        if (!$result) return 'N/A';
        return $dayNames[$result->dow] ?? 'N/A';
    }

    // ── Cancellation Reasons Distribution ──────────────────────────────

    public function getCancellationReasonsDistribution(Carbon $from, Carbon $to, ?int $serviceId = null, ?int $staffId = null, ?int $roomId = null): array
    {
        return $this->baseQuery($from, $to, $serviceId, $staffId, $roomId)
            ->where('tickets.status', 'cancelled')
            ->whereNotNull('tickets.cancellation_reason')
            ->selectRaw('tickets.cancellation_reason, COUNT(*) as count')
            ->groupBy('tickets.cancellation_reason')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($row) {
                return [
                    'reason' => $row->cancellation_reason,
                    'count' => $row->count,
                ];
            })
            ->toArray();
    }
}

