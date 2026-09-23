<?php

namespace App\Modules\Queue\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Queue\Models\Ticket;
use App\Modules\Rooms\Models\Room;
use App\Services\TenantManager;
use Illuminate\Http\Request;

class DisplayController extends Controller
{
    /**
     * Display the public queue screen.
     */
    public function show(Request $request, Room $room = null)
    {
        $tenantManager = app(TenantManager::class);
        $company = $tenantManager->getTenant();

        if (!$company) {
            abort(404, 'Company not found');
        }

        // Base query for active tickets
        $activeQuery = Ticket::with(['customer', 'room', 'service'])->active();
        
        // Base query for waiting tickets
        $waitingQuery = Ticket::with(['customer', 'service'])->waiting();

        if ($room) {
            $activeQuery->where('room_id', $room->id);
            $waitingQuery->where('room_id', $room->id);
        }

        // Active tickets (Called or Serving)
        $activeTickets = $activeQuery->orderBy('called_at', 'desc')->get();

        // Waiting tickets (top 8)
        $waitingTickets = $waitingQuery->orderBy('position', 'asc')->take(8)->get();

        // Check if a specific display device is bound
        $deviceToken = $request->get('device') ?: $request->cookie('display_token');
        if ($deviceToken) {
            $device = \App\Modules\Displays\Models\DisplayDevice::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->where(function ($q) use ($deviceToken) {
                    $q->where('device_token', $deviceToken)
                      ->orWhere('uid', $deviceToken);
                })
                ->where('is_active', true)
                ->first();

            if ($device && $device->device_token) {
                return redirect()->route('queue.display.show', ['token' => $device->device_token]);
            }
        }

        // If no device token is bound, redirect to TV authorization / pairing screen
        return redirect()->route('queue.display.tv');

        $contentService = app(\App\Modules\Displays\Services\DisplayContentService::class);
        $contents = $contentService->getActiveContentForDisplay($device, $company->id);

        if ($request->expectsJson()) {
            return response()->json([
                'active'  => $activeTickets,
                'waiting' => $waitingTickets,
                'contents_count' => $contents->count(),
            ]);
        }

        return view('queue.display', compact('activeTickets', 'waitingTickets', 'company', 'room', 'device', 'contents'));
    }
}
