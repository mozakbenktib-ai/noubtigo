<?php

namespace App\Modules\Appointments\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Appointments\Models\Appointment;
use App\Modules\Appointments\Services\AppointmentService;
use App\Modules\Services\Models\Service;
use App\Modules\Rooms\Models\Room;
use App\Modules\Customers\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(protected AppointmentService $svc) {}

    // ─── Main calendar view ──────────────────────────────────────────────────────

    public function index()
    {
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();

        $services  = Service::where('company_id', $tenantId)->where('is_active', true)->get();
        $rooms     = Room::where('company_id', $tenantId)->where('is_active', true)->get();
        $staff     = \App\Models\User::where('company_id', $tenantId)->get();

        // Stats for today
        $todayBase = Appointment::where('company_id', $tenantId)->today();
        $stats = [
            'total'      => (clone $todayBase)->count(),
            'confirmed'  => (clone $todayBase)->where('status', 'confirmed')->count(),
            'pending'    => (clone $todayBase)->where('status', 'pending')->count(),
            'checked_in' => (clone $todayBase)->where('status', 'checked_in')->count(),
            'completed'  => (clone $todayBase)->where('status', 'completed')->count(),
            'cancelled'  => (clone $todayBase)->where('status', 'cancelled')->count(),
            'no_show'    => (clone $todayBase)->where('status', 'no_show')->count(),
        ];

        // List view – upcoming 30 days
        $appointments = Appointment::with(['service', 'room', 'staff', 'customer'])
            ->where('company_id', $tenantId)
            ->upcoming()
            ->paginate(15);

        return view('pages.appointments', compact(
            'services', 'rooms', 'staff', 'stats', 'appointments'
        ));
    }

    // ─── FullCalendar JSON feed ──────────────────────────────────────────────────

    public function events(Request $request)
    {
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();

        // Parse range from FullCalendar (these are UTC boundaries)
        $start = Carbon::parse($request->get('start', Carbon::now('UTC')->startOfMonth()));
        $end   = Carbon::parse($request->get('end',   Carbon::now('UTC')->endOfMonth()));

        $events = $this->svc->getCalendarEvents($tenantId, $start, $end);
        return response()->json($events);
    }

    // ─── Store (book) ────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id'       => 'required|integer',
            'customer_name'    => 'required|string|max:120',
            'customer_email'   => 'nullable|email|max:120',
            'customer_phone'   => 'nullable|string|max:30',
            'customer_id'      => 'nullable|exists:customers,id',
            'slot_id'          => 'nullable|exists:appointment_slots,id',
            'room_id'          => 'required|integer',
            'user_id'          => 'nullable|exists:users,id',
            'appointment_date' => 'required|date',
            'duration_minutes' => 'nullable|integer|min:5|max:480',
            'notes'            => 'nullable|string|max:2000',
        ]);

        $tenantId    = app(\App\Services\TenantManager::class)->getTenantId();
        abort_unless(Room::where('company_id', $tenantId)->whereKey($validated['room_id'])->exists(), 422, 'Invalid room.');
        abort_unless(Service::where('company_id', $tenantId)->whereKey($validated['service_id'])->exists(), 422, 'Invalid service.');
        
        $company = \App\Modules\Companies\Models\Company::find($tenantId);
        if (!app(\App\Modules\Subscriptions\Services\SubscriptionService::class)->canCreateTicket($company)) {
            return response()->json([
                'success' => false,
                'message' => 'Your monthly ticket limit has been reached. Please upgrade your plan.',
            ], 400);
        }

        $appointment = $this->svc->book($validated, $tenantId);

        return response()->json([
            'success'     => true,
            'appointment' => $appointment,
            'message'     => 'Appointment booked successfully.',
        ]);
    }

    // ─── Update (edit / reschedule) ──────────────────────────────────────────────

    public function update(Request $request, Appointment $appointment)
    {
        $this->authoriseTenant($appointment);

        $validated = $request->validate([
            'service_id'       => 'sometimes|integer',
            'customer_name'    => 'sometimes|string|max:120',
            'customer_email'   => 'nullable|email|max:120',
            'customer_phone'   => 'nullable|string|max:30',
            'customer_id'      => 'nullable|exists:customers,id',
            'slot_id'          => 'nullable|exists:appointment_slots,id',
            'room_id'          => 'sometimes|required|integer',
            'user_id'          => 'nullable|exists:users,id',
            'appointment_date' => 'sometimes|date',
            'duration_minutes' => 'nullable|integer|min:5|max:480',
            'notes'            => 'nullable|string|max:2000',
            'status'           => 'nullable|in:pending,confirmed,cancelled,completed,no_show,checked_in',
        ]);

        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        if (isset($validated['room_id']) && !Room::where('company_id', $tenantId)->whereKey($validated['room_id'])->exists()) abort(422, 'Invalid room.');
        if (isset($validated['service_id']) && !Service::where('company_id', $tenantId)->whereKey($validated['service_id'])->exists()) abort(422, 'Invalid service.');

        $rescheduleFields = ['service_id', 'slot_id', 'appointment_date', 'room_id'];
        $isReschedule = !empty(array_intersect(array_keys($validated), $rescheduleFields));

        if (!$isReschedule) {
            // Optimization: If only status/metadata changed, update directly
            $appointment->update($validated);
        } else {
            // Full reschedule logic (capacity check, overbooking, etc.)
            $appointment = $this->svc->reschedule($appointment, $validated);
        }

        return response()->json([
            'success'     => true,
            'appointment' => $appointment,
        ]);
    }

    // ─── Check-in ────────────────────────────────────────────────────────────────

    public function checkIn(Appointment $appointment)
    {
        $this->authoriseTenant($appointment);
        
        try {
            $appointment = $this->svc->checkIn($appointment);

            return response()->json([
                'success'     => true,
                'appointment' => $appointment,
                'grace_until' => $appointment->grace_until?->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // ─── Cancel ──────────────────────────────────────────────────────────────────

    public function cancel(Request $request, Appointment $appointment)
    {
        $this->authoriseTenant($appointment);
        $request->validate(['reason' => 'nullable|string|max:500']);

        $appointment = $this->svc->cancel($appointment, $request->get('reason', ''));

        return response()->json(['success' => true, 'appointment' => $appointment]);
    }

    // ─── Destroy ─────────────────────────────────────────────────────────────────

    public function destroy(Appointment $appointment)
    {
        $this->authoriseTenant($appointment);
        $appointment->delete();

        return response()->json(['success' => true]);
    }

    // ─── Available slots for a service + date ────────────────────────────────────

    public function slots(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'date'       => 'required|date',
            'room_id'    => 'required|exists:rooms,id',
        ]);

        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        $slots    = $this->svc->getSlotsForDate(
            (int) $request->service_id,
            Carbon::parse($request->date),
            $tenantId,
            (int) $request->room_id
        );

        return response()->json($slots);
    }

    // ─── Helper ──────────────────────────────────────────────────────────────────

    protected function authoriseTenant(Appointment $appointment): void
    {
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        if ($tenantId && $appointment->company_id !== $tenantId) {
            abort(403);
        }
    }
}
