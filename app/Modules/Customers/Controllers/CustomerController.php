<?php

namespace App\Modules\Customers\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Customers\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected $activityLogService;
    
    public function __construct(\App\Modules\Queue\Services\ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('customers.view')) {
            abort(403);
        }
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        
        $filters = $request->only(['search', 'vip', 'service', 'status', 'visit_date']);
        
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc') === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['first_name', 'last_name', 'identifier', 'phone', 'is_vip', 'tickets_count', 'last_visit', 'created_at'];
        
        if ($sortBy === 'customer' || $sortBy === 'name') {
            $sortBy = 'last_name';
        }

        $customersQuery = \App\Modules\Customers\Models\Customer::where('company_id', $tenantId)
            ->filter($filters)
            ->withCount('tickets');

        if ($sortBy === 'last_visit') {
            $customersQuery->leftJoin('tickets', function ($join) {
                $join->on('customers.id', '=', 'tickets.customer_id')
                     ->whereRaw('tickets.id = (select id from tickets where tickets.customer_id = customers.id order by created_at desc limit 1)');
            })
            ->orderBy('tickets.created_at', $sortOrder)
            ->select('customers.*');
        } elseif (in_array($sortBy, $allowedSorts)) {
            if ($sortBy === 'last_name') {
                $customersQuery->orderBy('customers.last_name', $sortOrder)
                               ->orderBy('customers.first_name', $sortOrder);
            } else {
                $customersQuery->orderBy('customers.' . $sortBy, $sortOrder);
            }
        } else {
            $customersQuery->orderBy('customers.created_at', 'desc');
        }

        $customers = $customersQuery->paginate(10)->withQueryString();

        $services = \App\Modules\Services\Models\Service::where('company_id', $tenantId)->get();

        // Quick Stats
        $today = now()->startOfDay();
        $stats = [
            'total' => \App\Modules\Customers\Models\Customer::where('company_id', $tenantId)->count(),
            'today_visits' => \App\Modules\Queue\Models\Ticket::where('company_id', $tenantId)
                ->where('created_at', '>=', $today)
                ->distinct('customer_id')
                ->count(),
            'vips' => \App\Modules\Customers\Models\Customer::where('company_id', $tenantId)->where('is_vip', true)->count(),
            'appointments_today' => \App\Modules\Appointments\Models\Appointment::where('company_id', $tenantId)
                ->where('appointment_date', $today->toDateString())
                ->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.customers.partials.customers-table', compact('customers'))->render(),
                'stats' => $stats
            ]);
        }

        return view('pages.customers', compact('customers', 'services', 'stats'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('customers.create')) {
            abort(403);
        }
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        $company = \App\Modules\Companies\Models\Company::find($tenantId);
        $subscriptionService = app(\App\Modules\Subscriptions\Services\SubscriptionService::class);
        if (!$subscriptionService->canCreateCustomer($company)) {
            return response()->json([
                'success' => false,
                'message' => "Your plan's customer limit has been reached. Please upgrade to add more customers."
            ], 403);
        }
        
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'identifier' => 'nullable|string|max:50',
            'cin' => 'nullable|string|max:50',
            'file_number' => 'nullable|string|max:50',
            'plate_number' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_vip' => 'nullable|boolean',
        ]);

        $validated['company_id'] = $tenantId;

        $customer = \App\Modules\Customers\Models\Customer::create($validated);

        // Audit Log
        $this->activityLogService->logCustomerEvent($customer, 'created', "Customer {$customer->full_name} created.");

        return response()->json([
            'success' => true,
            'message' => 'Customer added successfully.',
            'customer' => $customer
        ]);
    }

    public function show(\App\Modules\Customers\Models\Customer $customer)
    {
        if (!auth()->user()->hasPermission('customers.view')) {
            abort(403);
        }
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        if ($customer->company_id !== $tenantId) {
            abort(403);
        }

        $timeline = \App\Modules\Queue\Models\ActivityLog::where('model_type', 'Customer')
            ->where('model_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('customers.show', compact('customer', 'timeline'));
    }

    public function update(Request $request, \App\Modules\Customers\Models\Customer $customer)
    {
        if (!auth()->user()->hasPermission('customers.edit')) {
            abort(403);
        }
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        if ($customer->company_id !== $tenantId) {
            abort(403);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'identifier' => 'nullable|string|max:50',
            'cin' => 'nullable|string|max:50',
            'file_number' => 'nullable|string|max:50',
            'plate_number' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_vip' => 'nullable|boolean',
        ]);

        $before = $customer->only(array_keys($validated));
        $customer->update($validated);
        $after = $customer->only(array_keys($validated));

        // Audit Log
        $this->activityLogService->logCustomerEvent($customer, 'updated', "Customer profile updated.", [
            'before' => $before,
            'after' => $after
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully.',
            'customer' => $customer
        ]);
    }

    public function destroy(\App\Modules\Customers\Models\Customer $customer)
    {
        if (!auth()->user()->hasPermission('customers.delete')) {
            abort(403);
        }
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        if ($customer->company_id !== $tenantId) {
            abort(403);
        }

        $customerName = $customer->full_name;
        $customerId = $customer->id;
        $customer->delete();

        // Audit Log (Deleted models can't be passed easily if they use soft deletes, but here it's fine)
        $this->activityLogService->log('Customer', $customerId, 'deleted', "Customer {$customerName} deleted.");

        return response()->json([
            'success' => true,
            'message' => 'Customer deleted successfully.'
        ]);
    }

    public function toggleVip(Request $request, \App\Modules\Customers\Models\Customer $customer)
    {
        if (!auth()->user()->hasPermission('customers.edit')) {
            abort(403);
        }
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        if ($customer->company_id !== $tenantId) {
            abort(403);
        }

        $customer->update(['is_vip' => $request->is_vip]);

        // Audit Log
        $status = $request->is_vip ? 'marked as VIP' : 'removed from VIP';
        $this->activityLogService->logCustomerEvent($customer, 'updated', "Customer {$customer->full_name} {$status}.");

        return response()->json([
            'success' => true,
            'message' => 'VIP status updated successfully.'
        ]);
    }

    public function ajaxSearch(Request $request)
    {
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        $search = $request->get('q');
        $limit = $request->get('limit', 15);
        
        $query = \App\Modules\Customers\Models\Customer::where('company_id', $tenantId);
        
        if ($search) {
            $query->filter(['search' => $search]);
        }
        
        $customers = $query->paginate($limit);
        
        $formatted = collect($customers->items())->map(function($customer) {
            return [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'full_name' => $customer->full_name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'identifier' => $customer->identifier,
                'cin' => $customer->cin,
                'file_number' => $customer->file_number,
                'plate_number' => $customer->plate_number,
            ];
        });
        
        return response()->json([
            'results' => $formatted,
            'pagination' => [
                'more' => $customers->hasMorePages()
            ]
        ]);
    }
}
