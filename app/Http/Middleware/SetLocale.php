<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = config('app.locale');

        if (Auth::check()) {
            $locale = Auth::user()->locale;
        } elseif ($request->hasSession() && $request->session()->has('locale')) {
            $locale = $request->session()->get('locale');
        } elseif ($request->hasHeader('Accept-Language')) {
            $locale = substr($request->header('Accept-Language'), 0, 2);
        }

        // Check if the locale is supported by our system (from the languages table)
        // For now, let's just make sure it's one of our expected languages.
        $supportedLocales = ['en', 'fr', 'ar'];
        
        if (!in_array($locale, $supportedLocales)) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
