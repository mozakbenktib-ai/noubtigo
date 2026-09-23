<?php

namespace App\Modules\Queue\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Queue\Models\Ticket;
use App\Modules\Queue\Services\QueueService;
use App\Modules\Services\Models\Service;
use App\Modules\Rooms\Models\Room;
use App\Services\TenantManager;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SimpleQueueController extends Controller
{
    protected QueueService $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    /**
     * Display the Simple Queue dashboard.
     */
    public function index()
    {
        $tenantId = app(TenantManager::class)->getTenantId();
        $company = app(TenantManager::class)->getTenant();

        // Ensure company is in simple mode
        if (!$company || !$company->isSimpleQueue()) {
            return redirect()->route('queue.index');
        }

        // Get the single room (first active)
        $room = Room::where('company_id', $tenantId)
            ->where('is_active', true)
            ->first();

        // Active tickets (called/serving)
        $serving = Ticket::with(['service', 'customer'])
            ->where('company_id', $tenantId)
            ->active()
            ->orderBy('called_at', 'desc')
            ->first();

        // Waiting tickets (FIFO order)
        $waiting = Ticket::with(['service', 'customer'])
            ->where('company_id', $tenantId)
            ->waiting()
            ->orderBy('position', 'asc')
            ->get();

        // Services for ticket creation
        $services = Service::where('company_id', $tenantId)
            ->where('is_active', true)
            ->get();

        // Basic stats for today
        $todayStart = Carbon::today();
        $stats = [
            'tickets_today' => Ticket::withoutGlobalScopes()
                ->where('company_id', $tenantId)
                ->whereDate('created_at', $todayStart)
                ->count(),
            'served_today' => Ticket::withoutGlobalScopes()
                ->where('company_id', $tenantId)
                ->whereDate('created_at', $todayStart)
                ->whereIn('status', ['done'])
                ->count(),
            'avg_wait' => (int) Ticket::withoutGlobalScopes()
                ->where('company_id', $tenantId)
                ->whereDate('created_at', $todayStart)
                ->whereNotNull('called_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, waited_since, called_at)) as avg_wait')
                ->value('avg_wait'),
        ];

        // Company code for tracking
        $companyCode = $company->code ?? $company->generateUniqueCode();
        if (!$company->code) {
            $company->code = $companyCode;
            $company->save();
        }

        return view('queue.simple', compact(
            'company', 'room', 'serving', 'waiting', 'services', 'stats', 'companyCode'
        ));
    }

    /**
     * Create a quick ticket (Simple Mode).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'nullable|exists:services,id',
            'customer_identifier' => 'nullable|string|max:100',
        ]);

        $ticket = $this->queueService->createSimpleTicket(
            $validated,
            null,
            auth()->id()
        );

        return response()->json([
            'success' => true,
            'ticket' => $ticket->load('service'),
            'message' => __('ui.ticket_created_simple'),
        ]);
    }

    /**
     * Call the next ticket (FIFO).
     */
    public function callNext(Request $request)
    {
        $tenantId = app(TenantManager::class)->getTenantId();

        // Get the single room
        $room = Room::where('company_id', $tenantId)
            ->where('is_active', true)
            ->first();

        $ticket = $this->queueService->callTicket(
            null,
            auth()->id(),
            $room?->id
        );

        if ($ticket) {
            event(new \App\Modules\Queue\Events\TicketUpdated($ticket));
            return response()->json(['success' => true, 'ticket' => $ticket->load('service')]);
        }

        return response()->json(['success' => false, 'message' => __('ui.no_tickets_waiting')], 404);
    }

    /**
     * Pass (skip) the current ticket.
     */
    public function pass(Ticket $ticket)
    {
        $tenantId = app(TenantManager::class)->getTenantId();
        if ($tenantId && $ticket->company_id !== $tenantId) {
            abort(403);
        }

        $ticket = $this->queueService->updateStatus($ticket, 'no_show');
        event(new \App\Modules\Queue\Events\TicketUpdated($ticket));

        return response()->json(['success' => true, 'ticket' => $ticket]);
    }

    /**
     * Complete the current ticket.
     */
    public function done(Ticket $ticket)
    {
        $tenantId = app(TenantManager::class)->getTenantId();
        if ($tenantId && $ticket->company_id !== $tenantId) {
            abort(403);
        }

        $ticket = $this->queueService->updateStatus($ticket, 'done');
        event(new \App\Modules\Queue\Events\TicketUpdated($ticket));

        return response()->json(['success' => true, 'ticket' => $ticket]);
    }

    /**
     * Get basic stats (JSON for AJAX refresh).
     */
    public function stats()
    {
        $tenantId = app(TenantManager::class)->getTenantId();
        $todayStart = Carbon::today();

        return response()->json([
            'tickets_today' => Ticket::withoutGlobalScopes()
                ->where('company_id', $tenantId)
                ->whereDate('created_at', $todayStart)
                ->count(),
            'served_today' => Ticket::withoutGlobalScopes()
                ->where('company_id', $tenantId)
                ->whereDate('created_at', $todayStart)
                ->where('status', 'done')
                ->count(),
            'avg_wait' => (int) Ticket::withoutGlobalScopes()
                ->where('company_id', $tenantId)
                ->whereDate('created_at', $todayStart)
                ->whereNotNull('called_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, waited_since, called_at)) as avg_wait')
                ->value('avg_wait'),
            'waiting_count' => Ticket::withoutGlobalScopes()
                ->where('company_id', $tenantId)
                ->where('status', 'waiting')
                ->count(),
        ]);
    }
}
