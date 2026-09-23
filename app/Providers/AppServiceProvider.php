<?php

namespace App\Providers;

use App\Services\TimezoneService;
use App\Services\TenantManager;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantManager::class, function () {
            return new TenantManager();
        });

        $this->app->singleton(TimezoneService::class, function () {
            return new TimezoneService();
        });

        $this->app->bind(
            \App\Modules\Coupons\Repositories\CouponRepositoryInterface::class,
            \App\Modules\Coupons\Repositories\CouponRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::if('permission', function ($permission) {
            return auth()->check() && auth()->user()->hasPermission($permission);
        });

        Paginator::useBootstrapFive();

        // @localtime($carbonInstance) — converts UTC datetime to user's local timezone and formats it
        Blade::directive('localtime', function ($expression) {
            return "<?php echo app(\App\Services\TimezoneService::class)->formatForDisplay($expression); ?>";
        });

        // @localtimeFormat($carbonInstance, 'H:i') — with custom format
        Blade::directive('localtimeFormat', function ($expression) {
            return "<?php echo app(\App\Services\TimezoneService::class)->formatForDisplay($expression); ?>";
        });

        // Register custom sqlite functions
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Database\Events\ConnectionEstablished::class, function ($event) {
            if ($event->connection->getDriverName() === 'sqlite') {
                $event->connection->getPdo()->sqliteCreateFunction('UUID', function() {
                    return (string) \Illuminate\Support\Str::uuid();
                });
            }
        });
    }
}

