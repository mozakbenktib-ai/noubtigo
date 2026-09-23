<?php

namespace App\Modules\Companies\Controllers;

use App\Http\Controllers\Controller;
use App\Services\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CompanySettingsController extends Controller
{
    protected $tenantManager;

    public function __construct(TenantManager $tenantManager)
    {
        $this->tenantManager = $tenantManager;
    }

    /**
     * Display company settings.
     */
    public function index()
    {
        $company = $this->tenantManager->getTenant();

        if (!$company && auth()->user()->is_system_admin) {
            return redirect()->route('rbac.master.index')->with('info', 'System Admins do not have a default company. Use Master Control to manage tenants.');
        }

        $timezones = \DateTimeZone::listIdentifiers();

        return view('modules.companies.settings', compact('company', 'timezones'));
    }

    /**
     * Update company settings and branding.
     */
    public function update(Request $request)
    {
        $company = $this->tenantManager->getTenant();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'primary_color' => 'required|string|size:7',
            'secondary_color' => 'required|string|size:7',
            'timezone' => 'required|string',
            'queue_mode' => 'nullable|in:simple,advanced',
            'queue_rules_vip' => 'required|integer|between:1,4',
            'queue_rules_on_time' => 'required|integer|between:1,4',
            'queue_rules_grace' => 'required|integer|between:1,4',
            'queue_rules_walk_in' => 'required|integer|between:1,4',
        ]);

        $data = $request->only(['name', 'email', 'phone', 'address']);
        
        // Handle Logo Upload
        if ($request->hasFile('logo')) {
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        // Handle Settings (Branding)
        $settings = $company->settings ?? [];
        $settings['colors'] = [
            'primary' => $request->primary_color,
            'secondary' => $request->secondary_color,
            'gradient' => "linear-gradient(135deg, {$request->primary_color}, {$request->secondary_color})"
        ];

        // Handle Queue Mode
        $requestedMode = $request->input('queue_mode', $company->getQueueMode());
        
        // Backend enforcement: If plan doesn't allow advanced, force simple
        if ($requestedMode === 'advanced' && !$company->hasFeature('queue.advanced')) {
            $requestedMode = 'simple';
        }
        
        $settings['queue_mode'] = $requestedMode;

        // Each priority level must be used exactly once. This keeps the queue
        // ordering deterministic and prevents accidental ties.
        $queueRules = [
            'vip' => (int) $request->input('queue_rules_vip', 1),
            'on_time_appointment' => (int) $request->input('queue_rules_on_time', 2),
            'in_grace_appointment' => (int) $request->input('queue_rules_grace', 3),
            'walk_in' => (int) $request->input('queue_rules_walk_in', 4),
        ];

        if (count(array_unique($queueRules)) !== count($queueRules)) {
            throw ValidationException::withMessages([
                'queue_rules_vip' => 'Each queue priority must have a different position.',
            ]);
        }

        $settings['queue_rules'] = $queueRules;
        
        $data['settings'] = $settings;
        $data['timezone'] = $request->timezone;

        $oldTimezone = $company->timezone;
        
        $company->update($data);

        // Update timezone for all users in the company if it changed
        if ($oldTimezone !== $request->timezone) {
            $company->users()->update(['timezone' => $request->timezone]);
        }

        // Recalculate priority scores for existing waiting tickets based on new rules
        app(\App\Modules\Queue\Services\QueueService::class)->recalculateAllWaitingPriorities($company->id);

        return redirect()->back()->with('success', 'Company settings updated successfully.');
    }

    /**
     * Show printable QR poster.
     */
    public function printQr()
    {
        $company = $this->tenantManager->getTenant();
        
        if (!$company && auth()->user()->is_system_admin) {
            abort(403, 'System Admins must select a company first.');
        }

        return view('modules.companies.print-qr', compact('company'));
    }
}
