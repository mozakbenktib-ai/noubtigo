<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modules\Queue\Models\Ticket;
use App\Modules\Customers\Models\Customer;
use App\Modules\Appointments\Models\Appointment;
use App\Modules\Rooms\Models\Room;
use App\Modules\Services\Models\Service;
use App\Modules\Todos\Models\Todo;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->hasPermission('dashboard.view')) {
            if ($user->hasPermission('queue.view')) {
                return redirect()->route('queue.index');
            }
            return redirect()->route('queue.simple.index');
        }

        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();

        // 1. KPI Counts
        $totalCustomers = Customer::where('company_id', $tenantId)->count();
        $appointmentsTodayCount = Appointment::where('company_id', $tenantId)->today()->count();
        $queueSize = Ticket::where('company_id', $tenantId)->whereIn('status', ['waiting', 'called'])->count();
        $servedToday = Ticket::where('company_id', $tenantId)
            ->where('status', 'done')
            ->whereDate('updated_at', Carbon::today())
            ->count();

        // 2. Active Consultation Rooms Overview
        $rooms = Room::where('company_id', $tenantId)->where('is_active', true)->get()->map(function ($room) use ($tenantId) {
            // Find active called or serving ticket in this room
            $activeTicket = Ticket::where('company_id', $tenantId)
                ->where('room_id', $room->id)
                ->whereIn('status', ['called', 'serving'])
                ->with(['customer', 'service'])
                ->first();

            // Count waiting tickets in this room
            $waitingCount = Ticket::where('company_id', $tenantId)
                ->where('room_id', $room->id)
                ->where('status', 'waiting')
                ->count();

            $room->activeTicket = $activeTicket;
            $room->waitingCount = $waitingCount;
            return $room;
        });

        // 3. Today's Appointments Timeline Checklist
        $todayAppointments = Appointment::where('company_id', $tenantId)
            ->with(['customer', 'service', 'room'])
            ->today()
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->orderBy('appointment_date', 'asc')
            ->get();

        $todos = Todo::where(function ($query) use ($user, $tenantId) {
                $query->where('user_id', $user->id)
                    ->orWhere(function ($publicTasks) use ($tenantId) {
                        $publicTasks->where('company_id', $tenantId)->where('visibility', 'public');
                    });
            })
            ->orderBy('is_completed')
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'normal' THEN 2 ELSE 3 END")
            ->orderBy('due_date')
            ->latest('created_at')
            ->get();

        // 4. Data lists for Quick Ticket Dispenser widget
        $services = Service::where('company_id', $tenantId)->where('is_active', true)->get();
        $dispenserRooms = Room::where('company_id', $tenantId)->where('is_active', true)->get();

        return view('pages.dashboard', compact(
            'totalCustomers',
            'appointmentsTodayCount',
            'queueSize',
            'servedToday',
            'rooms',
            'todayAppointments',
            'todos',
            'services',
            'dispenserRooms'
        ));
    }
}
