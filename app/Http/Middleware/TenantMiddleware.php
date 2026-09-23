<?php

namespace App\Http\Middleware;

use App\Modules\Companies\Models\Company;
use App\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Identifies the current tenant by subdomain or authenticated user.
     * The application timezone ALWAYS stays UTC — timezone conversions
     * are handled exclusively by App\Services\TimezoneService.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenantManager = app(TenantManager::class);

        // 1. Identify by Subdomain
        /**
        $host = $request->getHost();
        $baseHost = parse_url(config('app.url'), PHP_URL_HOST);
        
        if ($host !== $baseHost && !empty($baseHost)) {
            $subdomain = current(explode('.', $host));
            $company = Company::where('slug', $subdomain)->first();
            
            if ($company) {
                $tenantManager->setTenant($company);
                return $next($request);
            }
        }
        */
        // 2. Identify by Authenticated User
        if (!$tenantManager->hasTenant() && $request->user()) {
            $tenantManager->setTenant($request->user()->company);
        }

        // NOTE: Application timezone is NEVER changed from UTC.
        // All date conversions for display use TimezoneService::toLocal().
        // All user input conversions use TimezoneService::toUTC().

        return $next($request);
    }
}
