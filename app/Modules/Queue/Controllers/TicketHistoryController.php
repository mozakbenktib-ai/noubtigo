<?php

namespace App\Modules\Queue\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Queue\Models\Ticket;
use App\Modules\Queue\Models\ActivityLog;
use App\Modules\Services\Models\Service;
use App\Modules\Rooms\Models\Room;
use Illuminate\Http\Request;

class TicketHistoryController extends Controller
{
    /**
     * Display the tickets history page with tabs.
     */
    public function index(Request $request)
    {
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();

        $tab = $request->get('tab', 'active');

        $query = Ticket::with(['customer', 'service', 'room', 'operator'])
            ->where('tickets.company_id', $tenantId);

        // Apply tab filter
        switch ($tab) {
            case 'completed':
                $query->where('tickets.status', 'done');
                break;
            case 'cancelled':
                $query->whereIn('tickets.status', ['cancelled', 'no_show', 'hold_cancelled']);
                break;
            case 'active':
            default:
                $tab = 'active';
                $query->whereIn('tickets.status', ['waiting', 'called', 'serving', 'on_hold']);
                break;
        }

        // Search filter
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('tickets.ticket_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q2) use ($search) {
                      $q2->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Date filter
        if ($date = $request->get('date')) {
            $query->whereDate('tickets.created_at', $date);
        }

        // Service filter
        if ($serviceId = $request->get('service_id')) {
            $query->where('tickets.service_id', $serviceId);
        }

        // Room filter
        if ($roomId = $request->get('room_id')) {
            $query->where('tickets.room_id', $roomId);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc') === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['ticket_number', 'status', 'created_at', 'customer', 'service'];
        if (in_array($sortBy, $allowedSorts)) {
            if ($sortBy === 'customer') {
                $query->leftJoin('customers', 'tickets.customer_id', '=', 'customers.id')
                    ->orderBy('customers.last_name', $sortOrder)
                    ->orderBy('customers.first_name', $sortOrder)
                    ->select('tickets.*');
            } elseif ($sortBy === 'service') {
                $query->leftJoin('services', 'tickets.service_id', '=', 'services.id')
                    ->orderBy('services.name', $sortOrder)
                    ->select('tickets.*');
            } else {
                $query->orderBy('tickets.' . $sortBy, $sortOrder);
            }
        } else {
            $query->orderBy('tickets.created_at', 'desc');
        }

        $tickets = $query->paginate(20);

        // Stats
        $statsQuery = Ticket::where('company_id', $tenantId);
        if ($date) {
            $statsQuery->whereDate('created_at', $date);
        }
        if ($serviceId) {
            $statsQuery->where('service_id', $serviceId);
        }
        if ($roomId) {
            $statsQuery->where('room_id', $roomId);
        }

        $stats = [
            'active'    => (clone $statsQuery)->whereIn('status', ['waiting', 'called', 'serving', 'on_hold'])->count(),
            'completed' => (clone $statsQuery)->where('status', 'done')->count(),
            'cancelled' => (clone $statsQuery)->whereIn('status', ['cancelled', 'no_show', 'hold_cancelled'])->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('queue.partials.history-table', compact('tickets'))->render(),
                'stats' => $stats
            ]);
        }

        $services = Service::where('company_id', $tenantId)->where('is_active', true)->get();
        $rooms = Room::where('company_id', $tenantId)->where('is_active', true)->get();

        return view('queue.history', compact('tickets', 'tab', 'stats', 'services', 'rooms'));
    }

    /**
     * Display the ticket details page (Source of Truth).
     */
    public function show(Ticket $ticket)
    {
        // Ensure ticket belongs to the right company
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        if ($tenantId && $ticket->company_id !== $tenantId) {
            abort(403);
        }

        // Load relationships
        $ticket->load(['customer', 'service', 'room', 'operator']);

        // Get activity timeline
        $timeline = ActivityLog::where('model_type', 'Ticket')
            ->where('model_id', $ticket->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('queue.show', compact('ticket', 'timeline'));
    }
}
