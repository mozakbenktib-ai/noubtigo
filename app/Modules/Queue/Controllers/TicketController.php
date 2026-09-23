<?php

namespace App\Modules\Queue\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Queue\Models\Ticket;
use App\Modules\Queue\Services\QueueService;
use App\Modules\Services\Models\Service;
use App\Modules\Rooms\Models\Room;
use App\Modules\Customers\Models\Customer;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    protected $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    /**
     * Display the queue dashboard.
     */
    public function index()
    {
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        $company = app(\App\Services\TenantManager::class)->getTenant();

        // Redirect Simple Queue companies to the simple interface
        if ($company && $company->isSimpleQueue()) {
            return redirect()->route('queue.simple.index');
        }

        $tickets = Ticket::with(['customer', 'room', 'service', 'appointment'])
            ->where('company_id', $tenantId)
            ->queue()
            ->orderByQueueOrder()
            ->get();

        // Today's history for timeline
        $history = Ticket::with(['customer', 'room', 'service', 'appointment'])
            ->where('company_id', $tenantId)
            ->history()
            ->whereDate('created_at', today())
            ->orderBy('finished_at', 'desc')
            ->get();

        $onHoldTickets = Ticket::with(['customer', 'room', 'service', 'holder', 'appointment'])
            ->where('company_id', $tenantId)
            ->onHold()
            ->orderBy('hold_at', 'desc')
            ->get();
            
        $services = Service::where('company_id', $tenantId)->where('is_active', true)->get();
        $rooms = Room::where('company_id', $tenantId)->where('is_active', true)->get();

        return view('queue.index', compact('tickets', 'history', 'services', 'rooms', 'onHoldTickets'));
    }

    /**
     * Store a newly created ticket.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'room_id' => 'required|exists:rooms,id',
            'customer_id' => 'required|exists:customers,id',
            'is_vip' => 'nullable|boolean',
        ]);

        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        
        // Prevent adding a customer who is already queued
        $alreadyQueued = Ticket::where('company_id', $tenantId)
            ->where('customer_id', $validated['customer_id'])
            ->whereIn('status', ['waiting', 'called', 'serving', 'on_hold'])
            ->exists();
            
        if ($alreadyQueued) {
            return response()->json([
                'success' => false,
                'message' => 'This customer is already in the queue.'
            ], 422);
        }

        $ticket = $this->queueService->createTicket($validated, null, auth()->id());

        return response()->json([
            'success' => true,
            'ticket' => $ticket,
            'message' => 'Ticket created successfully.'
        ]);
    }

    /**
     * Update the queue order (drag & drop).
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|exists:tickets,id',
        ]);

        $this->queueService->reorderTickets($validated['order']);

        return response()->json(['success' => true]);
    }

    /**
     * Call the next ticket or a specific ticket.
     */
    public function callNext(Request $request)
    {
        $ticketId = $request->input('ticket_id');
        $roomId = $request->input('room_id');
        $operatorId = auth()->id();

        $ticket = $this->queueService->callTicket($ticketId, $operatorId, $roomId ? (int) $roomId : null);

        if ($ticket) {
            event(new \App\Modules\Queue\Events\TicketUpdated($ticket));
            return response()->json(['success' => true, 'ticket' => $ticket]);
        }

        return response()->json(['success' => false, 'message' => __('ui.no_tickets_waiting')], 404);
    }

    /**
     * Update the status of a specific ticket.
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:waiting,called,serving,done,cancelled,no_show',
        ]);

        // Ensure ticket belongs to the right company
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        if ($tenantId && $ticket->company_id !== $tenantId) {
            abort(403);
        }

        $ticket = $this->queueService->updateStatus($ticket, $validated['status']);
        
        event(new \App\Modules\Queue\Events\TicketUpdated($ticket));

        return response()->json(['success' => true, 'ticket' => $ticket]);
    }

    /**
     * Change the room of a specific ticket.
     */
    public function changeRoom(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
        ]);

        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        if ($tenantId && $ticket->company_id !== $tenantId) {
            abort(403);
        }

        $ticket = $this->queueService->changeRoom($ticket, $validated['room_id']);

        return response()->json(['success' => true, 'ticket' => $ticket]);
    }

    /**
     * Put a ticket on hold.
     */
    public function hold(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'note' => 'nullable|string|max:1000',
        ]);

        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        if ($tenantId && $ticket->company_id !== $tenantId) {
            abort(403);
        }

        try {
            $ticket = $this->queueService->putOnHold(
                $ticket, 
                $validated['reason'], 
                $validated['note'] ?? null, 
                auth()->id()
            );
            event(new \App\Modules\Queue\Events\TicketUpdated($ticket));
            return response()->json(['success' => true, 'ticket' => $ticket]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Resume a ticket on hold.
     */
    public function resume(Request $request, Ticket $ticket)
    {
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        if ($tenantId && $ticket->company_id !== $tenantId) {
            abort(403);
        }

        try {
            $ticket = $this->queueService->resumeService($ticket, auth()->id());
            event(new \App\Modules\Queue\Events\TicketUpdated($ticket));
            return response()->json(['success' => true, 'ticket' => $ticket]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Cancel a ticket hold (customer did not return).
     */
    public function cancelHold(Request $request, Ticket $ticket)
    {
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        if ($tenantId && $ticket->company_id !== $tenantId) {
            abort(403);
        }

        try {
            $ticket = $this->queueService->cancelHold($ticket, auth()->id());
            event(new \App\Modules\Queue\Events\TicketUpdated($ticket));
            return response()->json(['success' => true, 'ticket' => $ticket]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Cancel a ticket.
     */
    public function cancel(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'reason' => 'required|string|in:Customer Requested Cancellation,Customer Left,Duplicate Ticket,Wrong Service,No Longer Needed,Other',
            'note' => 'nullable|string|max:1000',
        ]);

        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        if ($tenantId && $ticket->company_id !== $tenantId) {
            abort(403);
        }

        try {
            $ticket = $this->queueService->cancelTicket($ticket, $validated['reason'], $validated['note'] ?? null, auth()->id());
            return response()->json(['success' => true, 'ticket' => $ticket]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
