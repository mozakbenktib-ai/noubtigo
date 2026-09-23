<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class TimezoneService
{
    /**
     * Resolve the effective timezone using the fallback chain:
     * user.timezone → tenant.timezone → UTC
     *
     * @param  User|null  $user  Specific user, or null for current auth user
     * @return string  IANA timezone identifier (e.g. "Europe/Paris")
     */
    public function resolve(?User $user = null): string
    {
        $user = $user ?? auth()->user();

        if ($user) {
            // 1. User-level timezone
            if (!empty($user->timezone)) {
                return $user->timezone;
            }

            // 2. Tenant (company) timezone
            $company = $user->company ?? app(TenantManager::class)->getTenant();
            if ($company && !empty($company->timezone)) {
                return $company->timezone;
            }
        }

        // 3. Fallback: try TenantManager directly (for unauthenticated contexts)
        $tenant = app(TenantManager::class)->getTenant();
        if ($tenant && !empty($tenant->timezone)) {
            return $tenant->timezone;
        }

        // 4. Ultimate fallback
        return 'UTC';
    }

    /**
     * Convert a local datetime to UTC for database storage.
     *
     * Use this when receiving user input (forms, API requests).
     * The datetime is interpreted in the given timezone (or resolved timezone)
     * and converted to UTC.
     *
     * @param  string|Carbon  $datetime  Date/time string or Carbon instance
     * @param  string|null    $timezone  Source timezone. Null = auto-resolve.
     * @return Carbon  UTC Carbon instance ready for DB storage
     */
    public function toUTC(string|Carbon $datetime, ?string $timezone = null): Carbon
    {
        $timezone = $timezone ?? $this->resolve();

        if ($datetime instanceof Carbon) {
            return $datetime->copy()->setTimezone('UTC');
        }

        return Carbon::parse($datetime, $timezone)->setTimezone('UTC');
    }

    /**
     * Convert a UTC datetime to the user's local timezone for display.
     *
     * Use this when reading from the database for UI output.
     *
     * @param  string|Carbon  $datetime  UTC datetime from DB
     * @param  string|null    $timezone  Target timezone. Null = auto-resolve.
     * @return Carbon  Carbon instance in the local timezone
     */
    public function toLocal(string|Carbon $datetime, ?string $timezone = null): Carbon
    {
        $timezone = $timezone ?? $this->resolve();

        if ($datetime instanceof Carbon) {
            return $datetime->copy()->setTimezone($timezone);
        }

        return Carbon::parse($datetime, 'UTC')->setTimezone($timezone);
    }

    /**
     * Format a UTC datetime for display in the user's local timezone.
     *
     * @param  string|Carbon  $datetime  UTC datetime from DB
     * @param  string         $format    PHP date format string
     * @param  string|null    $timezone  Target timezone. Null = auto-resolve.
     * @return string  Formatted local datetime string
     */
    public function formatForDisplay(string|Carbon $datetime, string $format = 'M d, Y H:i', ?string $timezone = null): string
    {
        return $this->toLocal($datetime, $timezone)->format($format);
    }

    /**
     * Get "now" in the resolved local timezone.
     *
     * Useful for "today's appointments" queries where you need to know
     * what "today" means in the user's local time.
     *
     * @param  string|null  $timezone  Override timezone. Null = auto-resolve.
     * @return Carbon  Current moment in local timezone
     */
    public function localNow(?string $timezone = null): Carbon
    {
        $timezone = $timezone ?? $this->resolve();

        return Carbon::now('UTC')->setTimezone($timezone);
    }

    /**
     * Get today's start-of-day and end-of-day in UTC,
     * computed from the local timezone's "today".
     *
     * Use this for DB queries like "all appointments for today"
     * where "today" should be interpreted in the user's local timezone
     * but the query runs against UTC columns.
     *
     * @param  string|null  $timezone  Override timezone. Null = auto-resolve.
     * @return array{Carbon, Carbon}  [startOfDayUTC, endOfDayUTC]
     */
    public function localTodayRange(?string $timezone = null): array
    {
        $timezone = $timezone ?? $this->resolve();

        $localNow = Carbon::now('UTC')->setTimezone($timezone);

        $startOfDay = $localNow->copy()->startOfDay()->setTimezone('UTC');
        $endOfDay   = $localNow->copy()->endOfDay()->setTimezone('UTC');

        return [$startOfDay, $endOfDay];
    }

    /**
     * Get date range in UTC for a given local date.
     *
     * @param  string|Carbon  $date      A date (e.g. '2026-04-09')
     * @param  string|null    $timezone  Override timezone. Null = auto-resolve.
     * @return array{Carbon, Carbon}  [startOfDayUTC, endOfDayUTC]
     */
    public function dateRangeInUTC(string|Carbon $date, ?string $timezone = null): array
    {
        $timezone = $timezone ?? $this->resolve();

        $localDate = Carbon::parse($date, $timezone);

        $startOfDay = $localDate->copy()->startOfDay()->setTimezone('UTC');
        $endOfDay   = $localDate->copy()->endOfDay()->setTimezone('UTC');

        return [$startOfDay, $endOfDay];
    }
}
