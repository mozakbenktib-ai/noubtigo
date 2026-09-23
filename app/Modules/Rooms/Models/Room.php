<?php

namespace App\Modules\Rooms\Models;

use App\Modules\Core\Traits\BelongsToTenant;
use Illuminate\Support\Str;
use App\Modules\Core\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use SoftDeletes, BelongsToTenant, HasTranslations;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'description',
        'capacity',
        'is_active',
        'appointments_enabled',
        'appointment_duration_minutes',
        'appointment_slot_interval_minutes',
        'appointment_schedule',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'is_active' => 'boolean',
        'appointments_enabled' => 'boolean',
        'appointment_duration_minutes' => 'integer',
        'appointment_slot_interval_minutes' => 'integer',
        'appointment_schedule' => 'array',
        'id' => 'integer',
        'company_id' => 'integer',
    ];

    protected static function booted()
    {
        parent::booted();
        static::creating(function ($room) {
            if (empty($room->uuid)) {
                $room->uuid = (string) Str::uuid();
            }
        });
    }

    /** The room is the source of truth for appointment availability. */
    public function scheduleForDay(int $dayOfWeek): array
    {
        return $this->appointment_schedule[(string) $dayOfWeek] ?? [];
    }

    public function isWorkingDay(int $dayOfWeek): bool
    {
        return $this->appointments_enabled && count($this->scheduleForDay($dayOfWeek)) > 0;
    }

    /**
     * Use UUID for route model binding generation.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Resolve the route binding to accept either ID or UUID.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if (\Illuminate\Support\Str::isUuid($value)) {
            return $this->where('uuid', $value)->firstOrFail();
        }
        return $this->where($field ?? 'id', $value)->firstOrFail();
    }
}
