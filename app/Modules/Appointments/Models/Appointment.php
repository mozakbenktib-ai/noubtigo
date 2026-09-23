<?php

namespace App\Modules\Appointments\Models;

use App\Modules\Core\Traits\BelongsToTenant;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'user_id',
        'service_id',
        'room_id',
        'slot_id',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'appointment_date',
        'duration_minutes',
        'status',
        'notes',
        'checked_in_at',
        'grace_until',
        'is_overbooked',
        'cancelled_reason',
    ];

    protected static function booted()
    {
        parent::booted();
        static::creating(function ($appointment) {
            if (empty($appointment->uuid)) {
                $appointment->uuid = (string) Str::uuid();
            }
        });
    }

    protected $casts = [
        'appointment_date' => 'datetime',
        'checked_in_at'    => 'datetime',
        'grace_until'      => 'datetime',
        'duration_minutes' => 'integer',
        'is_overbooked'    => 'boolean',
        'id' => 'integer',
        'company_id' => 'integer',
        'user_id' => 'integer',
        'service_id' => 'integer',
        'room_id' => 'integer',
        'slot_id' => 'integer',
        'customer_id' => 'integer',
    ];

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function service()
    {
        return $this->belongsTo('App\Modules\Services\Models\Service');
    }

    public function room()
    {
        return $this->belongsTo('App\Modules\Rooms\Models\Room');
    }

    public function staff()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function slot()
    {
        return $this->belongsTo(AppointmentSlot::class, 'slot_id');
    }

    public function customer()
    {
        return $this->belongsTo('App\Modules\Customers\Models\Customer', 'customer_id');
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

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

    public function isCheckedIn(): bool
    {
        return !is_null($this->checked_in_at);
    }

    public function isInGracePeriod(): bool
    {
        return $this->grace_until && now()->lessThan($this->grace_until);
    }

    public function isNoShow(): bool
    {
        return $this->status === 'no_show';
    }

    public function graceRemainingSeconds(): int
    {
        if (!$this->grace_until) return 0;
        return max(0, (int) now()->diffInSeconds($this->grace_until, false));
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────────

    /**
     * Scope: appointments for "today" in the user's local timezone.
     * Converts local today boundaries to UTC for the DB query.
     */
    public function scopeToday($q)
    {
        $tz = app(\App\Services\TimezoneService::class);
        [$start, $end] = $tz->localTodayRange();

        return $q->whereBetween('appointment_date', [$start, $end]);
    }

    /**
     * Scope: upcoming appointments from "now" in UTC.
     */
    public function scopeUpcoming($q)
    {
        return $q->where('appointment_date', '>=', now())->orderBy('appointment_date');
    }

    public function scopeByService($q, int $serviceId)
    {
        return $q->where('service_id', $serviceId);
    }

    public function scopeByStatus($q, string $status)
    {
        return $q->where('status', $status);
    }

    // ─── Status badge helper ────────────────────────────────────────────────────

    public function statusBadge(): array
    {
        return match ($this->status) {
            'confirmed'  => ['class' => 'bg-success-subtle text-success',  'label' => 'Confirmed'],
            'pending'    => ['class' => 'bg-warning-subtle text-warning',  'label' => 'Pending'],
            'checked_in' => ['class' => 'bg-info-subtle text-info',        'label' => 'Checked In'],
            'completed'  => ['class' => 'bg-secondary-subtle text-secondary', 'label' => 'Completed'],
            'cancelled'  => ['class' => 'bg-danger-subtle text-danger',    'label' => 'Cancelled'],
            'no_show'    => ['class' => 'status-no-show',                  'label' => 'No Show'],
            default      => ['class' => 'bg-secondary-subtle text-secondary', 'label' => ucfirst($this->status)],
        };
    }

    // ─── FullCalendar event color ────────────────────────────────────────────────

    public function calendarColor(): string
    {
        return match ($this->status) {
            'confirmed'  => '#22c55e',
            'pending'    => '#f59e0b',
            'checked_in' => '#06b6d4',
            'completed'  => '#64748b',
            'cancelled'  => '#ef4444',
            'no_show'    => '#f97316',
            default      => '#8b5cf6',
        };
    }
}
