<?php

namespace App\Modules\Services\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Companies\Models\Company;
use App\Modules\Services\Models\Service;
use App\Modules\Subscriptions\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();

        $services = Service::where('company_id', $tenantId)->get();

        return view('pages.services', compact('services'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        $company = Company::findOrFail($tenantId);

        if (!app(SubscriptionService::class)->canCreateService($company)) {
            return response()->json([
                'success' => false,
                'message' => "Your plan's room limit has been reached. Please upgrade to add more services."
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'prefix' => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $validated['company_id'] = $tenantId;
        $validated['slug'] = Str::slug($validated['name']);
        
        if (empty($validated['prefix'])) {
            $validated['prefix'] = strtoupper(substr($validated['name'], 0, 1));
        }

        // Handle unique slug within company
        $count = Service::where('company_id', $tenantId)->where('slug', 'like', $validated['slug'] . '%')->count();
        if ($count > 0) {
            $validated['slug'] .= '-' . time();
        }

        $service = Service::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Service created successfully.',
            'service' => $service
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        if ($service->company_id !== $tenantId) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'prefix' => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($service->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']);
            $count = Service::where('company_id', $tenantId)
                ->where('id', '!=', $service->id)
                ->where('slug', 'like', $validated['slug'] . '%')
                ->count();
            if ($count > 0) {
                $validated['slug'] .= '-' . time();
            }
        }

        $service->update($validated);

        // Required if the frontend needs translation arrays
        $service->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully.',
            'service' => $service
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();
        if ($service->company_id !== $tenantId) {
            abort(403);
        }

        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service deleted successfully.'
        ]);
    }
}
