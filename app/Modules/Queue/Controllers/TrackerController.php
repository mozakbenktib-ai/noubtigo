<?php

namespace App\Modules\Queue\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Queue\Models\Ticket;
use App\Modules\Companies\Models\Company;
use App\Services\TenantManager;
use App\Services\TimezoneService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class TrackerController extends Controller
{
    protected TimezoneService $tz;

    public function __construct(TimezoneService $tz)
    {
        $this->tz = $tz;
    }

    /**
     * Show the tracking hub for a specific company (via QR).
     */
    public function hub($token)
    {
        $company = Company::where('secure_public_token', $token)->firstOrFail();
        
        return view('queue.track-hub', compact('company'));
    }

    /**
     * Show the tracker landing page (Option 1: Track by Ticket).
     */
    public function index(string $token)
    {
        $company = Company::where('secure_public_token', $token)->firstOrFail();

        return view('queue.track-landing', compact('company'));
    }

    /**
     * Verify ticket (supports search by Ticket Number OR Phone Number).
     */
    public function verify(Request $request)
    {
        $request->validate([
            'ticket_number' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        if (!$request->filled('ticket_number') && !$request->filled('phone')) {
            return back()->withErrors(['ticket_number' => __('ui.enter_ticket_or_phone') ?? 'Please enter a ticket number or phone number.'])->withInput();
        }

        $company = null;
        $token = $request->input('c', $request->input('company_token'));
        if ($token) {
            $company = Company::where('secure_public_token', $token)->first();
        }

        // Base Ticket Query (last 48 hours for active/today tickets)
        $ticketQuery = Ticket::withoutGlobalScopes()
            ->where('created_at', '>=', now()->subHours(48));

        // Filter by company if user scanned company QR token
        if ($company) {
            $ticketQuery->where('company_id', $company->id);
        }

        // Filter by Ticket Number OR Phone Number
        if ($request->filled('ticket_number')) {
            $ticketQuery->where('ticket_number', strtoupper(trim($request->ticket_number)));
        } elseif ($request->filled('phone')) {
            $normalizedPhone = \App\Modules\Customers\Models\Customer::normalizePhone($request->phone);
            $rawPhone = preg_replace('/[^0-9]/', '', $request->phone);

            $ticketQuery->where(function ($q) use ($normalizedPhone, $rawPhone) {
                // Match via Customer relationship
                $q->whereHas('customer', function ($cq) use ($normalizedPhone, $rawPhone) {
                    $cq->where('phone', $normalizedPhone)
                       ->orWhere('phone', 'LIKE', '%' . $rawPhone . '%');
                })
                // Match customer_identifier field directly if phone was stored there
                ->orWhere('customer_identifier', 'LIKE', '%' . $rawPhone . '%')
                ->orWhere('customer_identifier', 'LIKE', '%' . $normalizedPhone . '%');
            });
        }

        $ticket = $ticketQuery->latest()->first();

        if (!$ticket) {
            return back()->withErrors(['ticket_number' => __('ui.invalid_details')])->withInput();
        }

        // Target Company from scanned QR token or ticket's company
        $targetCompany = $company ?? $ticket->company;

        // Verify if the ticket is active OR created on the SAME DATE
        if (!in_array($ticket->status, ['waiting', 'called', 'serving', 'on_hold'])) {
            $companyTz = $targetCompany->timezone ?? 'UTC';
            $ticketLocalDate = $this->tz->toLocal($ticket->created_at, $companyTz)->format('Y-m-d');
            $todayLocal = $this->tz->localNow($companyTz)->format('Y-m-d');

            if ($ticketLocalDate !== $todayLocal) {
                return back()->withErrors(['ticket_number' => __('ui.ticket_expired')])->withInput();
            }
        }

        // Store ticket ID in session
        Session::put('tracking_ticket_id', $ticket->id);

        return redirect()->route('queue.track.status');
    }

    /**
     * Show the tracking status.
     */
    public function show()
    {
        $ticketId = Session::get('tracking_ticket_id');
        
        if (!$ticketId) {
            return redirect()->route('landing');
        }

        $ticket = Ticket::withoutGlobalScopes()
            ->with(['service', 'room', 'company'])
            ->find($ticketId);

        if (!$ticket) {
            Session::forget('tracking_ticket_id');
            return redirect()->route('landing');
        }

        // Date check: only enforce for completed/inactive tickets
        if (!in_array($ticket->status, ['waiting', 'called', 'serving', 'on_hold'])) {
            $companyTz = $ticket->company->timezone ?? 'UTC';
            $ticketLocalDate = $this->tz->toLocal($ticket->created_at, $companyTz)->format('Y-m-d');
            $todayLocal = $this->tz->localNow($companyTz)->format('Y-m-d');

            if ($ticketLocalDate !== $todayLocal) {
                Session::forget('tracking_ticket_id');
                return redirect()->route('queue.track.landing', $ticket->company->secure_public_token);
            }
        }

        // Calculate position
        $position = 0;
        if ($ticket->status === 'waiting') {
            $position = Ticket::withoutGlobalScopes()
                ->where('company_id', $ticket->company_id)
                ->where('status', 'waiting')
                ->where(function ($query) use ($ticket) {
                    $query->where('priority_score', '>', $ticket->priority_score)
                          ->orWhere(function ($q) use ($ticket) {
                              $q->where('priority_score', $ticket->priority_score)
                                ->where('position', '<', $ticket->position);
                          });
                })
                ->count();
            
            // Current ticket's position is count + 1
            $position += 1;
        }

        // Estimate wait time (10 mins per person ahead)
        $estimatedWait = max(0, ($position - 1) * 10);

        return view('queue.track-status', compact('ticket', 'position', 'estimatedWait'));
    }
}
