<?php

namespace App\Modules\Queue\Models;

use App\Modules\Core\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    /**
     * Activity logs are immutable — no updates allowed.
     * Only created_at is tracked (no updated_at).
     */
    const UPDATED_AT = null;

    protected $table = 'activity_logs';

    protected $fillable = [
        'company_id',
        'user_id',
        'action',
        'model_type',
        'model_id',
        'model_label',
        'description',
        'changes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
        'id' => 'integer',
        'company_id' => 'integer',
        'user_id' => 'integer',
        'model_id' => 'integer',
    ];

    /**
     * Boot: add tenant scoping.
     */
    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);

        // Auto-set company_id on creation
        static::creating(function ($log) {
            if (empty($log->company_id)) {
                $tenantManager = app(\App\Services\TenantManager::class);
                if ($tenantManager->hasTenant()) {
                    $log->company_id = $tenantManager->getTenantId();
                }
            }
        });
    }

    /**
     * Prevent updates — logs are immutable.
     */
    public function save(array $options = [])
    {
        if ($this->exists) {
            return false; // Block updates
        }
        return parent::save($options);
    }

    /**
     * Prevent deletes — logs are immutable.
     */
    public function delete()
    {
        return false;
    }

    // ── Relationships ──────────────────────────────────────────────────

    public function company()
    {
        return $this->belongsTo(\App\Modules\Companies\Models\Company::class, 'company_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function getSubjectAttribute()
    {
        $modelClass = match ($this->model_type) {
            'User' => '\\App\\Models\\User',
            'Ticket' => '\\App\\Modules\\Queue\\Models\\Ticket',
            'Customer' => '\\App\\Modules\\Customers\\Models\\Customer',
            'Room' => '\\App\\Modules\\Rooms\\Models\\Room',
            'Service' => '\\App\\Modules\\Services\\Models\\Service',
            'Appointment' => '\\App\\Modules\\Appointments\\Models\\Appointment',
            default => '\\App\\Modules\\' . \Illuminate\Support\Str::plural($this->model_type) . '\\Models\\' . $this->model_type,
        };
        
        if (class_exists($modelClass)) {
            // Use withTrashed if the model uses SoftDeletes
            if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($modelClass))) {
                return $modelClass::withTrashed()->find($this->model_id);
            }
            return $modelClass::find($this->model_id);
        }
        return null;
    }

    // ── Scopes ─────────────────────────────────────────────────────────

    /**
     * Scope to a specific model (e.g. Ticket #5).
     */
    public function scopeForModel($query, string $modelType, int $modelId)
    {
        return $query->where('model_type', $modelType)
                     ->where('model_id', $modelId);
    }

    /**
     * Scope to a specific action type.
     */
    public function scopeOfAction($query, string $action)
    {
        return $query->where('action', $action);
    }
}
