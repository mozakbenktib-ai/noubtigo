<?php

namespace App\Modules\Queue\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Queue\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Display the global activity logs page (Admin only).
     */
    public function index(Request $request)
    {
        $tenantId = app(\App\Services\TenantManager::class)->getTenantId();

        $query = ActivityLog::with(['user'])
            ->where('company_id', $tenantId)
            ->orderBy('created_at', 'desc');

        // Filter by model type
        if ($modelType = $request->get('model_type')) {
            $query->where('model_type', $modelType);
        }

        // Filter by action
        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }

        // Filter by user (supports UUID or ID)
        if ($userParam = $request->get('user')) {
            $user = \App\Models\User::where('uuid', $userParam)->orWhere('id', $userParam)->first();
            if ($user) {
                $query->where('user_id', $user->id);
            }
        }

        // Filter by date
        if ($date = $request->get('date')) {
            $query->whereDate('created_at', $date);
        }

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('model_type', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(30);

        return view('queue.activity-logs', compact('logs'));
    }
}
