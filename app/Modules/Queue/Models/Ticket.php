<?php

namespace App\Modules\Queue\Models;

use App\Modules\Core\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Modules\Queue\Models\ActivityLog;

class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'service_id',
        'room_id',
        'user_id',
        'customer_id',
        'customer_identifier',
        'appointment_id',
        'ticket_number',
        'status',
        'source',
        'priority_score',
        'is_vip',
        'position',
        'waited_since',
        'called_at',
        'started_at',
        'finished_at',
        'uuid',
        'hold_reason',
        'hold_note',
        'hold_at',
        'hold_by',
        'resumed_at',
        'resumed_by',
        'cancellation_reason',
        'cancellation_note',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $casts = [
        'is_vip' => 'boolean',
        'priority_score' => 'integer',
        'position' => 'integer',
        'waited_since' => 'datetime',
        'called_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'hold_at' => 'datetime',
        'resumed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'id' => 'integer',
        'company_id' => 'integer',
        'service_id' => 'integer',
        'room_id' => 'integer',
        'user_id' => 'integer',
        'customer_id' => 'integer',
        'appointment_id' => 'integer',
        'model_id' => 'integer',
    ];

    protected $attributes = [
        'status' => 'waiting',
    ];

    /**
     * Boot: add tenant scope, UUID generation, and ticket number logic.
     */
    protected static function booted()
    {
        // Tenant scope
        static::addGlobalScope(new TenantScope);

        static::creating(function ($ticket) {
            // UUID generation
            if (empty($ticket->uuid)) {
                $ticket->uuid = (string) Str::uuid();
            }
            // Auto-set company_id from tenant context if not already set
            if (empty($ticket->company_id)) {
                $tenantManager = app(\App\Services\TenantManager::class);
                if ($tenantManager->hasTenant()) {
                    $ticket->company_id = $tenantManager->getTenantId();
                }
            }
            // Generate ticket number (e.g., A-001)
            $prefix = 'T';
            if ($ticket->service_id && $ticket->service) {
                $prefix = $ticket->service->prefix ?? strtoupper(substr($ticket->service->name, 0, 1));
            }
            $lastTicket = static::withoutGlobalScopes()
                ->where('service_id', $ticket->service_id)
                ->where('company_id', $ticket->company_id)
                ->whereDate('created_at', today())
                ->orderBy('id', 'desc')
                ->first();
            $sequence = $lastTicket ? ((int) substr($lastTicket->ticket_number, -3)) + 1 : 1;
            $ticket->ticket_number = $prefix . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);

            // Set initial position
            $ticket->position = static::withoutGlobalScopes()
                ->where('company_id', $ticket->company_id)
                ->where('status', 'waiting')
                ->max('position') + 1;

            $ticket->waited_since = now();
        });
    }

    /** Relationships **/
    public function company()
    {
        return $this->belongsTo('App\Modules\Companies\Models\Company', 'company_id');
    }

    public function service()
    {
        return $this->belongsTo('App\Modules\Services\Models\Service', 'service_id');
    }

    /**
     * Get the room assigned.
     */
    public function room()
    {
        return $this->belongsTo('App\Modules\Rooms\Models\Room', 'room_id');
    }

    /**
     * Get the operator assigned.
     */
    public function operator()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    /**
     * Get the operator who put the ticket on hold.
     */
    public function holder()
    {
        return $this->belongsTo('App\Models\User', 'hold_by');
    }

    /**
     * Get the operator who resumed the ticket.
     */
    public function resumer()
    {
        return $this->belongsTo('App\Models\User', 'resumed_by');
    }

    /**
     * Get the operator who cancelled the ticket.
     */
    public function canceller()
    {
        return $this->belongsTo('App\Models\User', 'cancelled_by');
    }



    /**
     * Get the customer requested.
     */
    public function customer()
    {
        return $this->belongsTo('App\Modules\Customers\Models\Customer', 'customer_id');
    }

    /**
     * Get the appointment requested.
     */
    public function appointment()
    {
        return $this->belongsTo('App\Modules\Appointments\Models\Appointment', 'appointment_id');
    }

    /**
     * Get the activity logs for this ticket.
     */
    public function activityLogs()
    {
        return ActivityLog::where('model_type', 'Ticket')
            ->where('model_id', $this->id)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    // ── Scopes ─────────────────────────────────────────────────────────
    
    /**
     * Scope for on hold tickets.
     */
    public function scopeOnHold($query)
    {
        return $query->where('status', 'on_hold');
    }

    /**
     * Scope for waiting tickets.
     */
    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    /**
     * Scope for active tickets (serving or called).
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['called', 'serving']);
    }

    /**
     * Scope for the real-time queue (all non-terminal statuses).
     */
    public function scopeQueue($query)
    {
        return $query->whereIn('status', ['waiting', 'called', 'serving']);
    }

    /**
     * Scope for history (all terminal statuses).
     */
    public function scopeHistory($query)
    {
        return $query->whereIn('status', ['done', 'cancelled', 'no_show']);
    }

    /**
     * Scope for applying standard queue ordering (Priority -> Appointment Time -> Arrival).
     */
    public function scopeOrderByQueueOrder($query)
    {
        return $query
            ->orderBy('priority_score', 'desc')
            ->orderByRaw("COALESCE((SELECT appointment_date FROM appointments WHERE appointments.id = tickets.appointment_id LIMIT 1), tickets.created_at) ASC")
            ->orderBy('position', 'asc');
    }

    /**
     * Calculate display weight for sorting (lower = called first).
     */
    public function getWeightAttribute()
    {
        // Subtract priority_score so higher scores sort first (lower weight)
        return $this->position - ($this->priority_score ?? 0);
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
