<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check() || !auth()->user()->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. Lack of permission: ' . $permission], 403);
            }
            abort(403, 'Unauthorized. You do not have the required permission.');
        }

        return $next($request);
    }
}
