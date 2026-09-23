<?php

namespace App\Modules\Rooms\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Rooms\Models\Room;
use App\Modules\Subscriptions\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoomController extends Controller
{
    public function index()
    {
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        $rooms = Room::where('company_id', $tenantId)->get();

        return view('pages.rooms', compact('rooms'));
    }

    public function store(Request $request)
    {
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        $company = \App\Modules\Companies\Models\Company::find($tenantId);

        // Limit Enforcement
        $subscriptionService = app(SubscriptionService::class);
        if (!$subscriptionService->canCreateRoom($company)) {
            return response()->json([
                'success' => false,
                'message' => "Your plan's room limit has been reached. Please upgrade to add more rooms."
            ], 403);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'appointments_enabled' => 'boolean',
            'appointment_duration_minutes' => 'nullable|integer|min:5|max:480',
            'appointment_slot_interval_minutes' => 'nullable|integer|min:5|max:480',
            'appointment_schedule' => 'nullable|array',
        ]);

        $validated['appointment_schedule'] = $this->normaliseSchedule($validated['appointment_schedule'] ?? []);

        $validated['company_id'] = $tenantId;
        $validated['slug'] = Str::slug($validated['name']);

        $count = Room::where('company_id', $tenantId)->where('slug', 'like', $validated['slug'] . '%')->count();
        if ($count > 0) {
            $validated['slug'] .= '-' . time();
        }

        $room = Room::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Room added successfully.',
            'room' => $room
        ]);
    }

    public function update(Request $request, Room $room)
    {
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        if ($room->company_id !== $tenantId) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'appointments_enabled' => 'boolean',
            'appointment_duration_minutes' => 'nullable|integer|min:5|max:480',
            'appointment_slot_interval_minutes' => 'nullable|integer|min:5|max:480',
            'appointment_schedule' => 'nullable|array',
        ]);

        $validated['appointment_schedule'] = $this->normaliseSchedule($validated['appointment_schedule'] ?? []);

        if ($room->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']);
            $count = Room::where('company_id', $tenantId)
                ->where('id', '!=', $room->id)
                ->where('slug', 'like', $validated['slug'] . '%')
                ->count();
            if ($count > 0) {
                $validated['slug'] .= '-' . time();
            }
        }

        $room->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Room updated successfully.',
            'room' => $room
        ]);
    }

    public function destroy(Room $room)
    {
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        if ($room->company_id !== $tenantId) {
            abort(403);
        }

        $room->delete();

        return response()->json([
            'success' => true,
            'message' => 'Room deleted successfully.'
        ]);
    }

    private function normaliseSchedule(array $schedule): array
    {
        $result = [];
        for ($day = 0; $day <= 6; $day++) {
            $result[(string) $day] = collect($schedule[(string) $day] ?? $schedule[$day] ?? [])
                ->filter(fn ($range) => is_array($range) && !empty($range['start']) && !empty($range['end']))
                ->map(fn ($range) => ['start' => substr($range['start'], 0, 5), 'end' => substr($range['end'], 0, 5), 'breaks' => collect($range['breaks'] ?? [])->map(fn ($break) => ['start' => substr($break['start'] ?? '', 0, 5), 'end' => substr($break['end'] ?? '', 0, 5)])->filter(fn ($break) => $break['start'] && $break['end'])->values()->all()])
                ->values()->all();
        }
        return $result;
    }
}
