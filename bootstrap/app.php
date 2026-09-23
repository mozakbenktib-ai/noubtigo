<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\TenantMiddleware::class,
            \App\Http\Middleware\EnsurePasswordIsChanged::class,
        ]);
        
        $middleware->api(append: [
            \App\Http\Middleware\TenantMiddleware::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            '/webhook/whatsapp',
            '/logout',
            '/customer/logout',
        ]);

        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'system_admin' => \App\Http\Middleware\SystemAdminMiddleware::class,
            'queue_mode' => \App\Http\Middleware\EnsureQueueMode::class,
            'subscription.valid' => \App\Http\Middleware\CheckSubscriptionValid::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'session_expired' => true,
                    'message' => 'Your session has expired. Please login again.',
                    'redirect' => route('login'),
                ], 401);
            }
        });

        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, Request $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'session_expired' => true,
                    'message' => 'Your session has expired. Please login again.',
                    'redirect' => route('login'),
                ], 419);
            } else {
                return redirect()->guest(route('login'));
            }
        });
    })->create();
