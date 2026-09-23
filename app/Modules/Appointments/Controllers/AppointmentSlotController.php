<?php

namespace App\Modules\Appointments\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Appointments\Models\AppointmentSlot;
use App\Modules\Services\Models\Service;
use App\Modules\Rooms\Models\Room;
use Illuminate\Http\Request;
use App\Services\TimezoneService;

class AppointmentSlotController extends Controller
{
    protected TimezoneService $tz;

    public function __construct(TimezoneService $tz)
    {
        $this->tz = $tz;
    }
    // ─── Index ───────────────────────────────────────────────────────────────────

    public function index()
    {
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();

        $slots    = AppointmentSlot::with(['service', 'room'])
            ->where('company_id', $tenantId)
            ->orderBy('service_id')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $services = Service::where('company_id', $tenantId)->where('is_active', true)->get();
        $rooms = Room::where('company_id', $tenantId)->where('is_active', true)->get();

        return view('pages.appointment_slots', compact('slots', 'services', 'rooms'));
    }

    // ─── Store ───────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id'        => 'required|exists:services,id',
            'room_id'           => 'required|integer',
            'days_of_week'      => 'required|array',
            'days_of_week.*'    => 'integer|min:0|max:6',
            'start_time'        => 'required|date_format:H:i',
            'end_time'          => 'required|date_format:H:i|after:start_time',
            'interval_minutes'  => 'required|integer|min:5|max:480',
            'grace_minutes'     => 'nullable|integer|min:0|max:120',
            'is_active'         => 'boolean',
        ]);

        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        $this->validateTenantRelations($validated, $tenantId);

        $created = [];
        $companyTz = $this->tz->resolve();

        foreach ($validated['days_of_week'] as $day) {
            $created[] = AppointmentSlot::create([
                'company_id'        => $tenantId,
                'service_id'        => $validated['service_id'],
                'room_id'           => $validated['room_id'],
                'day_of_week'       => $day,
                'start_time'        => $this->tz->toUTC($validated['start_time'], $companyTz)->format('H:i:s'),
                'end_time'          => $this->tz->toUTC($validated['end_time'], $companyTz)->format('H:i:s'),
                'interval_minutes'  => $validated['interval_minutes'],
                'capacity'          => 1,
                'overbooking_limit' => 0,
                'grace_minutes'     => $validated['grace_minutes'] ?? 0,
                'is_active'         => request()->has('is_active') ? $validated['is_active'] : true,
            ]);
        }

        return response()->json(['success' => true]);
    }

    // ─── Update ──────────────────────────────────────────────────────────────────

    public function update(Request $request, AppointmentSlot $slot)
    {
        $this->authoriseTenant($slot);

        $validated = $request->validate([
            'day_of_week'       => 'sometimes|integer|min:0|max:6',
            'room_id'           => 'sometimes|required|integer',
            'start_time'        => 'sometimes|date_format:H:i',
            'end_time'          => 'sometimes|date_format:H:i|after:start_time',
            'interval_minutes'  => 'sometimes|integer|min:5|max:480',
            'grace_minutes'     => 'sometimes|nullable|integer|min:0|max:120',
            'is_active'         => 'boolean',
        ]);

        $this->validateTenantRelations($validated, $slot->company_id);

        if (isset($validated['start_time'])) {
            $validated['start_time'] = $this->tz->toUTC($validated['start_time'])->format('H:i:s');
        }
        if (isset($validated['end_time'])) {
            $validated['end_time'] = $this->tz->toUTC($validated['end_time'])->format('H:i:s');
        }

        $slot->update($validated);

        return response()->json(['success' => true, 'slot' => $slot->load(['service', 'room'])]);
    }

    // ─── Destroy ─────────────────────────────────────────────────────────────────

    public function destroy(AppointmentSlot $slot)
    {
        $this->authoriseTenant($slot);
        $slot->delete();

        return response()->json(['success' => true]);
    }

    // ─── Helper ──────────────────────────────────────────────────────────────────

    protected function authoriseTenant(AppointmentSlot $slot): void
    {
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        if ($tenantId && $slot->company_id !== $tenantId) {
            abort(403);
        }
    }

    protected function validateTenantRelations(array $data, int $tenantId): void
    {
        if (isset($data['service_id']) && !Service::where('company_id', $tenantId)->whereKey($data['service_id'])->exists()) {
            abort(422, 'Invalid service.');
        }
        if (isset($data['room_id']) && !Room::where('company_id', $tenantId)->whereKey($data['room_id'])->exists()) {
            abort(422, 'Invalid room.');
        }
    }
}
