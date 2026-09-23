<?php

namespace App\Modules\Displays\Services;

use App\Modules\Displays\Models\DisplayContent;
use App\Modules\Displays\Models\DisplayDevice;
use App\Services\TimezoneService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DisplayContentService
{
    protected TimezoneService $timezoneService;

    public function __construct(TimezoneService $timezoneService)
    {
        $this->timezoneService = $timezoneService;
    }

    /**
     * Retrieve all active, scheduled, and visible display contents for a specific display device
     * (or for the general company if no specific device is identified).
     *
     * Resolves: GLOBAL CONTENT + CONTENT SPECIFICALLY ASSIGNED TO THIS DISPLAY.
     *
     * @param DisplayDevice|null $device
     * @param int $companyId
     * @return Collection<int, DisplayContent>
     */
    public function getActiveContentForDisplay(?DisplayDevice $device, ?int $companyId): Collection
    {
        if (!$companyId) {
            return collect();
        }

        $nowUtc = now('UTC');

        $query = DisplayContent::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where(function ($q) use ($nowUtc) {
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', $nowUtc);
            })
            ->where(function ($q) use ($nowUtc) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>=', $nowUtc);
            });

        if ($device && $device->id) {
            // Include ALL displays content PLUS content specifically assigned to this device
            $query->where(function ($q) use ($device) {
                $q->where('target_type', DisplayContent::TARGET_ALL)
                  ->orWhere(function ($sub) use ($device) {
                      $sub->where('target_type', DisplayContent::TARGET_SELECTED)
                          ->whereHas('devices', function ($devQuery) use ($device) {
                              $devQuery->where('display_devices.id', $device->id);
                          });
                  });
            });
        } else {
            // General screen fallback: Global content only
            $query->where('target_type', DisplayContent::TARGET_ALL);
        }

        return $query
            ->orderByRaw("CASE 
                WHEN priority = 'emergency' THEN 1 
                WHEN priority = 'high' THEN 2 
                ELSE 3 
            END ASC")
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Compute display content counts for all devices of a company.
     * Used in admin display management to show content availability per screen.
     *
     * @param int $companyId
     * @return array
     */
    public function getDeviceContentCounts(?int $companyId): array
    {
        if (!$companyId) {
            return [
                'global_count' => 0,
                'devices' => [],
            ];
        }

        $nowUtc = now('UTC');

        // Active valid global count
        $globalCount = DisplayContent::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where('target_type', DisplayContent::TARGET_ALL)
            ->where(function ($q) use ($nowUtc) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $nowUtc);
            })
            ->where(function ($q) use ($nowUtc) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $nowUtc);
            })
            ->count();

        // Specific assigned counts per device
        $specificCounts = DB::table('display_content_device')
            ->join('display_contents', 'display_content_device.display_content_id', '=', 'display_contents.id')
            ->where('display_contents.company_id', $companyId)
            ->where('display_contents.is_active', true)
            ->where('display_contents.target_type', DisplayContent::TARGET_SELECTED)
            ->where(function ($q) use ($nowUtc) {
                $q->whereNull('display_contents.starts_at')->orWhere('display_contents.starts_at', '<=', $nowUtc);
            })
            ->where(function ($q) use ($nowUtc) {
                $q->whereNull('display_contents.ends_at')->orWhere('display_contents.ends_at', '>=', $nowUtc);
            })
            ->whereNull('display_contents.deleted_at')
            ->groupBy('display_content_device.display_device_id')
            ->select('display_content_device.display_device_id', DB::raw('count(*) as count'))
            ->pluck('count', 'display_device_id')
            ->toArray();

        return [
            'global_count' => $globalCount,
            'devices' => $specificCounts,
        ];
    }

    /**
     * Safely sync display device assignments with multi-tenant ownership validation.
     * Prevents any attempt to assign devices belonging to other companies.
     *
     * @param DisplayContent $content
     * @param array $deviceIds
     * @param int $companyId
     * @return void
     */
    public function syncDisplayAssignments(DisplayContent $content, array $deviceIds, int $companyId): void
    {
        if ($content->target_type !== DisplayContent::TARGET_SELECTED) {
            $content->devices()->detach();
            return;
        }

        // Strictly validate that submitted device IDs belong to this company
        $validDeviceIds = DisplayDevice::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->whereIn('id', $deviceIds)
            ->pluck('id')
            ->toArray();

        $content->devices()->sync($validDeviceIds);
    }
}
