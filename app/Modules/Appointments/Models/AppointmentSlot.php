<?php

namespace App\Modules\Appointments\Models;

use App\Modules\Core\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AppointmentSlot extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'service_id',
        'room_id',
        'day_of_week',
        'start_time',
        'end_time',
        'interval_minutes',
        'capacity',
        'overbooking_limit',
        'grace_minutes',
        'is_active',
    ];

    protected $casts = [
        'interval_minutes'  => 'integer',
        'day_of_week'       => 'integer',
        'capacity'          => 'integer',
        'overbooking_limit' => 'integer',
        'grace_minutes'     => 'integer',
        'is_active'         => 'boolean',
        'id' => 'integer',
        'company_id' => 'integer',
        'service_id' => 'integer',
        'room_id' => 'integer',
        'slot_id' => 'integer',
    ];

    // ─── Day labels ────────────────────────────────────────────────────────────

    public const DAYS = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

    public function getDayNameAttribute(): string
    {
        return self::DAYS[$this->day_of_week] ?? 'Unknown';
    }

    public function getTimeRangeAttribute(): string
    {
        $tz = app(\App\Services\TimezoneService::class);
        $companyTz = $this->company->timezone ?? $tz->resolve();

        $start = $tz->toLocal($this->start_time, $companyTz);
        $end   = $tz->toLocal($this->end_time, $companyTz);

        return $start->format('H:i') . ' – ' . $end->format('H:i');
    }

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function service()
    {
        return $this->belongsTo('App\Modules\Services\Models\Service');
    }

    public function room()
    {
        return $this->belongsTo('App\\Modules\\Rooms\\Models\\Room');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'slot_id');
    }

    // ─── Capacity helpers ───────────────────────────────────────────────────────

    /**
     * Count confirmed/pending bookings for this slot on a specific datetime.
     */
    public function bookedCount(Carbon $datetime, $excludeId = null): int
    {
        $q = $this->appointments()
            ->where('appointment_date', $datetime->format('Y-m-d H:i:s'))
            ->whereIn('status', ['pending', 'confirmed', 'checked_in']);
            
        if ($excludeId) {
            $q->where('id', '!=', $excludeId);
        }
        
        return $q->count();
    }

    /**
     * Total seats available (capacity + overbooking_limit).
     */
    public function totalCapacity(): int
    {
        return $this->capacity + $this->overbooking_limit;
    }

    /**
     * Remaining seats for a given exact datetime.
     */
    public function remainingCapacity(Carbon $datetime, $excludeId = null): int
    {
        return max(0, $this->totalCapacity() - $this->bookedCount($datetime, $excludeId));
    }

    public function isFull(Carbon $datetime, $excludeId = null): bool
    {
        return $this->remainingCapacity($datetime, $excludeId) <= 0;
    }

    public function isOverbooking(Carbon $datetime, $excludeId = null): bool
    {
        return $this->bookedCount($datetime, $excludeId) >= $this->capacity;
    }
}
