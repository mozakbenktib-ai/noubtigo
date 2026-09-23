<?php

namespace App\Modules\Displays\Models;

use App\Models\User;
use App\Modules\Companies\Models\Company;
use App\Modules\Core\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class DisplayContent extends Model
{
    use SoftDeletes, BelongsToTenant;

    // Supported content types
    const TYPE_INFORMATION = 'information';
    const TYPE_PROMOTION = 'promotion';
    const TYPE_QR_TRACKING = 'qr_tracking';
    const TYPE_ANNOUNCEMENT = 'announcement';

    // Extensible future types
    const TYPE_VIDEO = 'video';
    const TYPE_CAROUSEL = 'carousel';
    const TYPE_EMERGENCY = 'emergency_notice';

    // Target types
    const TARGET_ALL = 'all';
    const TARGET_SELECTED = 'selected';

    // Priority levels
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_EMERGENCY = 'emergency';

    protected $fillable = [
        'company_id',
        'title',
        'description',
        'type',
        'image_path',
        'link_url',
        'qr_url',
        'target_type',
        'duration',
        'sort_order',
        'priority',
        'is_active',
        'starts_at',
        'ends_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'duration' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'company_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    /**
     * Get available content types list.
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_INFORMATION => [
                'name' => __('ui.content_type_information') ?: 'Information',
                'icon' => 'bi-info-circle-fill',
                'badge' => 'bg-info bg-opacity-10 text-info',
                'description' => __('ui.content_type_information_desc') ?: 'General guidelines, visitor tips, instructions',
            ],
            self::TYPE_PROMOTION => [
                'name' => __('ui.content_type_promotion') ?: 'Promotion',
                'icon' => 'bi-megaphone-fill',
                'badge' => 'bg-success bg-opacity-10 text-success',
                'description' => __('ui.content_type_promotion_desc') ?: 'Visual services, campaigns, offers',
            ],
            self::TYPE_QR_TRACKING => [
                'name' => __('ui.content_type_qr_tracking') ?: 'QR / Tracking',
                'icon' => 'bi-qr-code-scan',
                'badge' => 'bg-primary bg-opacity-10 text-primary',
                'description' => __('ui.content_type_qr_tracking_desc') ?: 'Live mobile ticket status & remote waiting',
            ],
            self::TYPE_ANNOUNCEMENT => [
                'name' => __('ui.content_type_announcement') ?: 'Announcement',
                'icon' => 'bi-bell-fill',
                'badge' => 'bg-warning bg-opacity-10 text-warning',
                'description' => __('ui.content_type_announcement_desc') ?: 'Important updates, hours, alerts',
            ],
        ];
    }

    /**
     * Relationship: Company.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relationship: Assigned Display Devices (when target_type = 'selected').
     */
    public function devices(): BelongsToMany
    {
        return $this->belongsToMany(
            DisplayDevice::class,
            'display_content_device',
            'display_content_id',
            'display_device_id'
        )->withTimestamps();
    }

    /**
     * Relationship: Created by user.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Updated by user.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope: Active content only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Content valid by schedule for given UTC time.
     */
    public function scopeValidSchedule($query, ?Carbon $now = null)
    {
        $now = $now ?? now('UTC');

        return $query->where(function ($q) use ($now) {
            $q->whereNull('starts_at')
              ->orWhere('starts_at', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('ends_at')
              ->orWhere('ends_at', '>=', $now);
        });
    }

    /**
     * Accessor: Full image URL from storage.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        if (!Storage::disk('public')->exists($this->image_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->image_path);
    }

    /**
     * Accessor: Resolved QR code link URL.
     */
    public function getResolvedQrUrlAttribute(): string
    {
        if (!empty($this->qr_url)) {
            return $this->qr_url;
        }

        if ($this->company && !empty($this->company->secure_public_token)) {
            return route('queue.track.hub', $this->company->secure_public_token);
        }

        return url('/track/status');
    }

    /**
     * Accessor: Dynamic QR Image API URL.
     */
    public function getQrImageUrlAttribute(): string
    {
        $target = $this->resolved_qr_url;
        return 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&margin=4&data=' . urlencode($target);
    }

    /**
     * Accessor: Status badge details.
     */
    public function getStatusDetailsAttribute(): array
    {
        if (!$this->is_active) {
            return [
                'status' => 'inactive',
                'label' => __('ui.inactive') ?: 'Inactive',
                'badge' => 'bg-secondary bg-opacity-10 text-secondary',
                'icon' => 'bi-pause-circle-fill',
            ];
        }

        $now = now('UTC');

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return [
                'status' => 'scheduled',
                'label' => __('ui.scheduled') ?: 'Scheduled',
                'badge' => 'bg-info bg-opacity-10 text-info',
                'icon' => 'bi-calendar-event-fill',
            ];
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return [
                'status' => 'expired',
                'label' => __('ui.expired') ?: 'Expired',
                'badge' => 'bg-danger bg-opacity-10 text-danger',
                'icon' => 'bi-clock-history',
            ];
        }

        return [
            'status' => 'active',
            'label' => __('ui.active') ?: 'Active',
            'badge' => 'bg-success bg-opacity-10 text-success',
            'icon' => 'bi-check-circle-fill',
        ];
    }
}
