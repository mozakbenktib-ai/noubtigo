<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modules\Queue\Models\Ticket;
use App\Modules\Customers\Models\Customer;
use App\Modules\Services\Models\Service;
use App\Modules\Rooms\Models\Room;
use App\Models\User;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = trim($request->input('q', ''));
        
        if (empty($query) || strlen($query) < 2) {
            return response()->json([]);
        }

        $results = [];
        $user = auth()->user();

        // 1. Tickets (search by ticket_number, customer's first_name, last_name, phone)
        if ($user->hasPermission('queue.view')) {
            $tickets = Ticket::with('customer')
                ->where(function ($q) use ($query) {
                    $q->where('ticket_number', 'like', "%{$query}%")
                      ->orWhereHas('customer', function ($qc) use ($query) {
                          $qc->where('first_name', 'like', "%{$query}%")
                            ->orWhere('last_name', 'like', "%{$query}%")
                            ->orWhere('phone', 'like', "%{$query}%");
                      });
                })
                ->limit(5)
                ->get();

            foreach ($tickets as $ticket) {
                $customerName = $ticket->customer ? $ticket->customer->full_name : 'Walk-in';
                $results[] = [
                    'title' => "Ticket {$ticket->ticket_number}",
                    'subtitle' => "Customer: {$customerName} • Status: " . ucfirst($ticket->status),
                    'href' => route('tickets.show', $ticket->id),
                    'icon' => 'bi bi-ticket-perforated-fill',
                    'colors' => ['bg' => 'rgba(6,182,212,0.1)', 'color' => '#06b6d4'],
                    'group' => 'Tickets'
                ];
            }
        }

        // 2. Customers (search by first_name, last_name, email, phone, identifier)
        if ($user->hasPermission('users.view')) {
            $customers = Customer::where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%")
                  ->orWhere('identifier', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get();

            foreach ($customers as $customer) {
                $vipTag = $customer->is_vip ? ' (VIP)' : '';
                $results[] = [
                    'title' => $customer->full_name . $vipTag,
                    'subtitle' => "Phone: " . ($customer->phone ?? 'N/A') . " • Email: " . ($customer->email ?? 'N/A'),
                    'href' => route('customers.show', $customer->uuid),
                    'icon' => 'bi bi-people-fill',
                    'colors' => ['bg' => 'rgba(236,72,153,0.1)', 'color' => '#ec4899'],
                    'group' => 'Customers'
                ];
            }
        }

        // 3. Services (search by name, prefix)
        if ($user->hasPermission('services.view')) {
            $services = Service::where('name', 'like', "%{$query}%")
                ->orWhere('prefix', 'like', "%{$query}%")
                ->limit(5)
                ->get();

            foreach ($services as $service) {
                $prefix = $service->prefix ? "[{$service->prefix}] " : "";
                $results[] = [
                    'title' => $prefix . $service->name,
                    'subtitle' => "Duration: {$service->duration_minutes} mins • Price: {$service->price}",
                    'href' => route('services.index'), // Services doesn't have a show page, link to list
                    'icon' => 'bi bi-briefcase-fill',
                    'colors' => ['bg' => 'rgba(59,130,246,0.1)', 'color' => '#3b82f6'],
                    'group' => 'Services'
                ];
            }
        }

        // 4. Rooms (search by name)
        if ($user->hasPermission('rooms.view')) {
            $rooms = Room::where('name', 'like', "%{$query}%")
                ->limit(5)
                ->get();

            foreach ($rooms as $room) {
                $results[] = [
                    'title' => $room->name,
                    'subtitle' => "Capacity: " . ($room->capacity ?? 'Unlimited') . " • " . ($room->is_active ? 'Active' : 'Inactive'),
                    'href' => route('rooms.index'),
                    'icon' => 'bi bi-door-open-fill',
                    'colors' => ['bg' => 'rgba(168,85,247,0.1)', 'color' => '#a855f7'],
                    'group' => 'Rooms'
                ];
            }
        }

        // 5. Staff/Users (search by name, email)
        if ($user->hasPermission('users.view')) {
            $staff = User::with('roles')->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get();

            foreach ($staff as $member) {
                $firstRole = $member->roles->first();
                $roleName = $firstRole ? $firstRole->name : 'Staff';
                $results[] = [
                    'title' => $member->full_name,
                    'subtitle' => "Email: {$member->email} • Role: {$roleName}",
                    'href' => route('rbac.users.index'),
                    'icon' => 'bi bi-person-badge-fill',
                    'colors' => ['bg' => 'rgba(99,102,241,0.1)', 'color' => '#6366f1'],
                    'group' => 'Staff'
                ];
            }
        }

        return response()->json($results);
    }
}
