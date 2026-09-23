<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reports\Services\AnalyticsService;
use App\Modules\Services\Models\Service;
use App\Modules\Rooms\Models\Room;
use App\Models\User;
use App\Services\TimezoneService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function __construct(
        protected TimezoneService $tz,
        protected AnalyticsService $analytics
    ) {}

    /**
     * Display the analytics dashboard.
     */
    public function index(Request $request)
    {
        // ── Parse Filters ──
        $range     = $request->get('range', '7days');
        $serviceId = $request->get('service_id') ? (int) $request->get('service_id') : null;
        $staffId   = $request->get('staff_id') ? (int) $request->get('staff_id') : null;
        $roomId    = $request->get('room_id') ? (int) $request->get('room_id') : null;

        // ── Compute Date Boundaries ──
        $companyTz = $this->tz->resolve();
        $localNow  = $this->tz->localNow($companyTz);

        $localStartDate = match($range) {
            'today'  => $localNow->copy()->startOfDay(),
            '30days' => $localNow->copy()->subDays(30)->startOfDay(),
            default  => $localNow->copy()->subDays(7)->startOfDay(),
        };
        $localEndDate = $localNow->copy()->endOfDay();

        // Convert to UTC for DB queries
        $utcFrom = $localStartDate->copy()->setTimezone('UTC');
        $utcTo   = $localEndDate->copy()->setTimezone('UTC');

        // ── Fetch All Data ──
        $kpis               = $this->analytics->getOverviewKPIs($utcFrom, $utcTo, $serviceId, $staffId, $roomId);
        $heatmap            = $this->analytics->getPeakHoursHeatmap($utcFrom, $utcTo, $serviceId, $staffId, $roomId);
        $volumeTrend        = $this->analytics->getQueueVolumeTrend($utcFrom, $utcTo, $serviceId, $staffId, $roomId);
        $waitTrend          = $this->analytics->getWaitTimeTrend($utcFrom, $utcTo, $serviceId, $staffId, $roomId);
        $serviceDistribution = $this->analytics->getServiceDistribution($utcFrom, $utcTo, $serviceId, $staffId, $roomId);
        $staffPerformance   = $this->analytics->getStaffPerformance($utcFrom, $utcTo, $serviceId, $staffId, $roomId);
        $roomPerformance    = $this->analytics->getRoomPerformance($utcFrom, $utcTo, $serviceId, $staffId, $roomId);
        $waitAccuracy       = $this->analytics->getWaitTimeAccuracy($utcFrom, $utcTo, $serviceId, $staffId, $roomId);
        $insights           = $this->analytics->generateInsights($utcFrom, $utcTo, $serviceId, $staffId, $roomId);
        $cancellationReasonsDistribution = $this->analytics->getCancellationReasonsDistribution($utcFrom, $utcTo, $serviceId, $staffId, $roomId);

        // ── Populate Filter Options ──
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        $services = Service::where('company_id', $tenantId)->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $staffList = User::where('company_id', $tenantId)->orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $rooms = Room::where('company_id', $tenantId)->orderBy('name')->get(['id', 'name']);

        // ── Peak Hours (flat 24h for bar chart — aggregated across all days) ──
        $peakHoursFlat = array_fill(0, 24, 0);
        foreach ($heatmap as $dayData) {
            foreach ($dayData as $hour => $count) {
                $peakHoursFlat[$hour] += $count;
            }
        }

        return view('analytics.index', compact(
            'kpis',
            'heatmap',
            'peakHoursFlat',
            'volumeTrend',
            'waitTrend',
            'serviceDistribution',
            'staffPerformance',
            'roomPerformance',
            'waitAccuracy',
            'insights',
            'services',
            'staffList',
            'rooms',
            'range',
            'serviceId',
            'staffId',
            'roomId',
            'cancellationReasonsDistribution'
        ));
    }

    /**
     * Staff performance details (JSON for AJAX).
     */
    public function staffDetail(Request $request)
    {
        [$utcFrom, $utcTo] = $this->parseDateRange($request);
        $serviceId = $request->get('service_id') ? (int) $request->get('service_id') : null;

        return response()->json([
            'staff' => $this->analytics->getStaffPerformance($utcFrom, $utcTo, $serviceId, null, $request->get('room_id')),
        ]);
    }

    /**
     * Services analytics details (JSON for AJAX).
     */
    public function servicesDetail(Request $request)
    {
        [$utcFrom, $utcTo] = $this->parseDateRange($request);
        $staffId = $request->get('staff_id') ? (int) $request->get('staff_id') : null;

        return response()->json([
            'services'    => $this->analytics->getServiceDistribution($utcFrom, $utcTo, null, $staffId, $request->get('room_id')),
            'bottlenecks' => $this->analytics->getServiceBottlenecks($utcFrom, $utcTo, null, $staffId, $request->get('room_id')),
            'accuracy'    => $this->analytics->getWaitTimeAccuracy($utcFrom, $utcTo, null, $staffId, $request->get('room_id')),
        ]);
    }

    /**
     * Wait time intelligence (JSON for AJAX).
     */
    public function waitTimesDetail(Request $request)
    {
        [$utcFrom, $utcTo] = $this->parseDateRange($request);
        $serviceId = $request->get('service_id') ? (int) $request->get('service_id') : null;

        return response()->json([
            'trend'    => $this->analytics->getWaitTimeTrend($utcFrom, $utcTo, $serviceId, null, $request->get('room_id')),
            'accuracy' => $this->analytics->getWaitTimeAccuracy($utcFrom, $utcTo, $serviceId, null, $request->get('room_id')),
        ]);
    }

    /**
     * Export analytics as CSV.
     */
    public function exportCsv(Request $request)
    {
        [$utcFrom, $utcTo] = $this->parseDateRange($request);
        $serviceId = $request->get('service_id') ? (int) $request->get('service_id') : null;
        $staffId   = $request->get('staff_id') ? (int) $request->get('staff_id') : null;
        $roomId    = $request->get('room_id') ? (int) $request->get('room_id') : null;

        $kpis     = $this->analytics->getOverviewKPIs($utcFrom, $utcTo, $serviceId, $staffId, $roomId);
        $staff    = $this->analytics->getStaffPerformance($utcFrom, $utcTo, $serviceId, $staffId, $roomId);
        $services = $this->analytics->getServiceDistribution($utcFrom, $utcTo, $serviceId, $staffId, $roomId);
        $accuracy = $this->analytics->getWaitTimeAccuracy($utcFrom, $utcTo, $serviceId, $staffId, $roomId);
        $roomsPerf = $this->analytics->getRoomPerformance($utcFrom, $utcTo, $serviceId, $staffId, $roomId);

        $filename = 'noubtigo_analytics_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($kpis, $staff, $services, $accuracy, $roomsPerf) {
            $out = fopen('php://output', 'w');

            // KPIs Section
            fputcsv($out, ['=== OVERVIEW KPIs ===']);
            fputcsv($out, ['Metric', 'Value']);
            fputcsv($out, ['Total Tickets', $kpis['total_tickets']]);
            fputcsv($out, ['Avg Wait Time (min)', $kpis['avg_wait_minutes']]);
            fputcsv($out, ['Avg Service Time (min)', $kpis['avg_service_minutes']]);
            fputcsv($out, ['Completion Rate (%)', $kpis['completion_rate']]);
            fputcsv($out, ['No-Show Rate (%)', $kpis['no_show_rate']]);
            fputcsv($out, ['Cancellation Rate (%)', $kpis['cancellation_rate']]);
            fputcsv($out, ['Customer Throughput/Hour', $kpis['throughput_per_hour']]);
            fputcsv($out, ['New Customers', $kpis['new_customers']]);
            fputcsv($out, []);

            // Staff Section
            fputcsv($out, ['=== STAFF PERFORMANCE ===']);
            fputcsv($out, ['Name', 'Tickets Handled', 'Avg Service Time (min)', 'Idle Time (min)', 'Efficiency (%)']);
            foreach ($staff as $s) {
                fputcsv($out, [$s['full_name'], $s['tickets_handled'], $s['avg_service_time'], $s['idle_minutes'], $s['efficiency_score']]);
            }
            fputcsv($out, []);

            // Services Section
            fputcsv($out, ['=== SERVICES ===']);
            fputcsv($out, ['Service', 'Tickets', 'Avg Wait (min)', 'Avg Service (min)']);
            foreach ($services as $svc) {
                fputcsv($out, [$svc['name'], $svc['ticket_count'], round($svc['avg_wait'] ?? 0, 1), round($svc['avg_service'] ?? 0, 1)]);
            }
            fputcsv($out, []);

            // Accuracy Section
            fputcsv($out, ['=== WAIT TIME ACCURACY ===']);
            fputcsv($out, ['Service', 'Expected (min)', 'Actual (min)', 'Accuracy (%)']);
            foreach ($accuracy as $a) {
                fputcsv($out, [$a['name'], $a['expected_minutes'], $a['actual_minutes'], $a['accuracy_pct']]);
            }
            fputcsv($out, []);

            // Rooms Section
            fputcsv($out, ['=== ROOM PERFORMANCE ===']);
            fputcsv($out, ['Room', 'Tickets Handled', 'Avg Service Time (min)', 'Staff Count']);
            foreach ($roomsPerf as $r) {
                fputcsv($out, [$r['name'], $r['tickets_handled'], round($r['avg_service_time'], 1), $r['staff_count']]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Parse date range from request.
     */
    protected function parseDateRange(Request $request): array
    {
        $range    = $request->get('range', '7days');
        $companyTz = $this->tz->resolve();
        $localNow  = $this->tz->localNow($companyTz);

        $localStart = match($range) {
            'today'  => $localNow->copy()->startOfDay(),
            '30days' => $localNow->copy()->subDays(30)->startOfDay(),
            default  => $localNow->copy()->subDays(7)->startOfDay(),
        };

        return [
            $localStart->copy()->setTimezone('UTC'),
            $localNow->copy()->endOfDay()->setTimezone('UTC'),
        ];
    }
}
