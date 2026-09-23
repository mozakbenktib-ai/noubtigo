<?php

namespace App\Http\Middleware;

use App\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureQueueMode
{
    /**
     * Ensure the current company is operating in the required queue mode.
     *
     * Usage in routes: ->middleware('queue_mode:advanced')
     *
     * This prevents Simple Queue companies from accessing advanced-only
     * routes even via direct URL navigation. Backend enforcement is mandatory.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $requiredMode): Response
    {
        $tenant = app(TenantManager::class)->getTenant();

        if (!$tenant) {
            abort(403, 'No tenant context.');
        }

        $currentMode = $tenant->getQueueMode();

        if ($currentMode !== $requiredMode) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('ui.queue_mode_restricted', ['mode' => $requiredMode]),
                ], 403);
            }

            abort(403, __('ui.queue_mode_restricted', ['mode' => $requiredMode]));
        }

        return $next($request);
    }
}
