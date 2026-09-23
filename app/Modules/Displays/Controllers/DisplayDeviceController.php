<?php

namespace App\Modules\Displays\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Displays\Models\DisplayDevice;
use App\Modules\Queue\Models\Ticket;
use App\Modules\Rooms\Models\Room;
use App\Services\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DisplayDeviceController extends Controller
{
    /**
     * Admin: List all display devices.
     */
    public function index()
    {
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        if (!$tenantId && auth()->check() && auth()->user()->company_id) {
            $tenantId = auth()->user()->company_id;
            app(\App\Services\TenantManager::class)->setTenant(auth()->user()->company);
        }

        $company = $tenantId ? \App\Modules\Companies\Models\Company::find($tenantId) : null;

        $devices = $tenantId 
            ? DisplayDevice::with('room')->where('company_id', $tenantId)->orderBy('created_at', 'desc')->get()
            : collect();

        $rooms = $tenantId ? Room::where('company_id', $tenantId)->get() : collect();

        $contentService = app(\App\Modules\Displays\Services\DisplayContentService::class);
        $contentStats = $contentService->getDeviceContentCounts($tenantId);

        $subscriptionService = app(\App\Modules\Subscriptions\Services\SubscriptionService::class);
        $displayLimit = $company && $company->plan ? $company->plan->getLimit('display_limit') : null;
        $canCreateDisplay = $company ? $subscriptionService->canCreateDisplay($company) : false;
        $remainingDisplays = $company ? $subscriptionService->getRemainingDisplays($company) : null;

        return view('displays.index', compact(
            'devices',
            'rooms',
            'contentStats',
            'displayLimit',
            'canCreateDisplay',
            'remainingDisplays'
        ));
    }

    /**
     * Admin: Store a new display configuration.
     */
    public function store(Request $request)
    {
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        if (!$tenantId && auth()->check() && auth()->user()->company_id) {
            $tenantId = auth()->user()->company_id;
            app(\App\Services\TenantManager::class)->setTenant(auth()->user()->company);
        }

        if (!$tenantId) {
            return redirect()->back()->with('error', 'A company context is required to register displays.');
        }

        $company = \App\Modules\Companies\Models\Company::find($tenantId);

        $subscriptionService = app(\App\Modules\Subscriptions\Services\SubscriptionService::class);
        if ($company && !$subscriptionService->canCreateDisplay($company)) {
            return redirect()->back()->with('error', __('ui.display_limit_reached') ?: "Your plan's display limit has been reached. Please upgrade to add more displays.");
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'room_id' => 'nullable|exists:rooms,id',
            'show_type' => 'required|string|in:both,walk_in,appointment',
            'theme' => 'nullable|string|in:dark,light',
            'language' => 'nullable|string|in:en,ar,fr',
        ]);

        DisplayDevice::create([
            'company_id' => $tenantId,
            'room_id' => $validated['room_id'],
            'name' => $validated['name'],
            'show_type' => $validated['show_type'],
            'theme' => $validated['theme'] ?? 'dark',
            'language' => $validated['language'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Display registered. Enter the PIN on your TV to authorize.');
    }

    /**
     * Admin: Update an existing display configuration.
     */
    public function update(Request $request, DisplayDevice $device)
    {
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        if ($device->company_id !== $tenantId) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'room_id' => 'nullable|exists:rooms,id',
            'show_type' => 'required|string|in:both,walk_in,appointment',
            'theme' => 'nullable|string|in:dark,light',
            'language' => 'nullable|string|in:en,ar,fr',
        ]);

        $device->update($validated);

        return redirect()->back()->with('success', 'Display updated successfully.');
    }

    /**
     * TV Side: Initial setup / locked screen.
     * Accessible by unauthenticated users, but validated via PIN.
     */
    public function setup(Request $request, $uid = null)
    {
        // 1. If a specific UID is passed (from Preview link), check its pairing state
        if ($uid) {
            $device = DisplayDevice::withoutGlobalScopes()->where('uid', $uid)->first();
            if ($device && $device->device_token) {
                return redirect()->route('queue.display.show', ['token' => $device->device_token]);
            }
        }

        // 2. Otherwise check session cookie for persistence
        $token = $request->cookie('display_token');
        if ($token) {
            $device = DisplayDevice::withoutGlobalScopes()->where('device_token', $token)->first();
            if ($device && $device->is_active) {
                return redirect()->route('queue.display.show', ['token' => $token]);
            }
        }

        return view('displays.setup');
    }

    /**
     * TV Side: Submit PIN to authorize the device.
     */
    public function authorizeDevice(Request $request)
    {
        $request->validate([
            'pairing_code' => 'required|string|size:6',
        ]);

        /** @var DisplayDevice $device */
        $device = DisplayDevice::withoutGlobalScopes()
            ->where('pairing_code', $request->pairing_code)
            ->whereNull('device_token') // Only allow pairing if not already paired
            ->first();

        if (!$device) {
            return response()->json(['error' => 'Invalid or expired PIN.'], 422);
        }

        if (!$device->device_token) {
            $device->device_token = Str::random(64);
        }
        
        $device->paired_at = now();
        $device->save();

        return response()->json([
            'success' => true,
            'token' => $device->device_token,
            'redirect' => route('queue.display.show', ['token' => $device->device_token])
        ])->cookie('display_token', $device->device_token, 525600);
    }

    /**
     * TV Side: Authorized display view with single active session protection.
     */
    public function show(Request $request, $token)
    {
        $device = DisplayDevice::withoutGlobalScopes()
            ->where('device_token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        $company = $device->company;
        $room = $device->room;

        // Apply display-specific language preference if configured
        if (!empty($device->language)) {
            app()->setLocale($device->language);
        }

        // Session ID identification
        $clientSessionId = $request->header('X-Display-Session') 
            ?: $request->query('session_id') 
            ?: $request->cookie('display_client_session_id');

        // Check if another session is actively using this screen
        if ($device->hasActiveSession($clientSessionId)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'session_terminated' => true,
                    'error' => 'session_superseded',
                    'message' => __('ui.display_session_superseded') ?: 'This display is currently active on another screen.',
                    'device_name' => $device->name,
                ], 409);
            }

            return response()->view('displays.session_conflict', [
                'device' => $device,
                'company' => $company,
                'room' => $room,
                'sessionId' => $clientSessionId,
            ], 409);
        }

        // Active session tracking: claim or touch heartbeat
        if ($clientSessionId) {
            if ($device->current_session_id === $clientSessionId) {
                $device->touchSession($clientSessionId);
            } else {
                $device->claimSession($clientSessionId, $request->ip(), $request->userAgent());
            }
        }

        $activeQuery = Ticket::with(['customer', 'room', 'service'])->active()->where('company_id', $company->id);
        $waitingQuery = Ticket::with(['customer', 'service'])->waiting()->where('company_id', $company->id);

        if ($room) {
            $activeQuery->where('room_id', $room->id);
            $waitingQuery->where('room_id', $room->id);
        }

        // Apply Content Filter
        if ($device->show_type === DisplayDevice::SHOW_WALK_IN) {
            $activeQuery->whereNull('appointment_id');
            $waitingQuery->whereNull('appointment_id');
        } elseif ($device->show_type === DisplayDevice::SHOW_APPOINTMENT) {
            $activeQuery->whereNotNull('appointment_id');
            $waitingQuery->whereNotNull('appointment_id');
        }

        $activeTickets = $activeQuery->orderBy('called_at', 'desc')->get();
        $waitingTickets = $waitingQuery->orderByQueueOrder()->take(8)->get();

        $contentService = app(\App\Modules\Displays\Services\DisplayContentService::class);
        $contents = $contentService->getActiveContentForDisplay($device, $company->id);

        if ($request->expectsJson()) {
            return response()->json([
                'session_valid' => true,
                'current_session_id' => $device->current_session_id,
                'active' => $activeTickets,
                'waiting' => $waitingTickets,
                'contents_count' => $contents->count(),
            ]);
        }

        return view('queue.display', compact('activeTickets', 'waitingTickets', 'company', 'room', 'device', 'contents'));
    }

    /**
     * TV Side: Take over an active display session.
     */
    public function takeOverSession(Request $request, $token)
    {
        $device = DisplayDevice::withoutGlobalScopes()
            ->where('device_token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        $sessionId = $request->input('session_id') ?: (string) Str::uuid();

        $device->claimSession($sessionId, $request->ip(), $request->userAgent());

        return redirect()->route('queue.display.show', [
            'token' => $device->device_token,
            'session_id' => $sessionId,
        ])->with('success', __('ui.display_taken_over') ?: 'Display taken over successfully.');
    }

    /**
     * TV Side: Release active session (e.g. when browser tab is closed).
     */
    public function releaseSession(Request $request, $token)
    {
        $device = DisplayDevice::withoutGlobalScopes()
            ->where('device_token', $token)
            ->first();

        if ($device) {
            $sessionId = $request->input('session_id') ?: $request->header('X-Display-Session');
            $device->releaseSession($sessionId);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Admin: Reset / release an active session remotely.
     */
    public function resetSession(DisplayDevice $device)
    {
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        if ($device->company_id !== $tenantId) {
            abort(403);
        }

        $device->releaseSession();

        return redirect()->back()->with('success', __('ui.display_session_reset') ?: 'Active display session reset successfully.');
    }

    /**
     * Admin: Delete a display.
     */
    public function destroy(DisplayDevice $device)
    {
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        if ($device->company_id !== $tenantId) {
            abort(403);
        }

        $device->delete();
        return redirect()->back()->with('success', 'Display removed successfully.');
    }
}
