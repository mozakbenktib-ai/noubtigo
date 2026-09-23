<?php

namespace App\Modules\Displays\Models;

use App\Modules\Companies\Models\Company;
use App\Modules\Rooms\Models\Room;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DisplayDevice extends Model
{
    use SoftDeletes, \App\Modules\Core\Traits\BelongsToTenant;

    const SHOW_BOTH = 'both';
    const SHOW_WALK_IN = 'walk_in';
    const SHOW_APPOINTMENT = 'appointment';

    protected $fillable = [
        'company_id',
        'room_id',
        'name',
        'show_type',
        'theme',
        'language',
        'uid',
        'pairing_code',
        'device_token',
        'paired_at',
        'is_active',
        'last_seen_at',
        'current_session_id',
        'session_started_at',
        'session_ip',
        'session_user_agent',
    ];

    protected $casts = [
        'paired_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'session_started_at' => 'datetime',
        'is_active' => 'boolean',
        'id' => 'integer',
        'company_id' => 'integer',
        'room_id' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($device) {
            if (!$device->uid) {
                $device->uid = (string) Str::uuid();
            }
            if (!$device->pairing_code) {
                $device->pairing_code = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Get the company that owns the device.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the room assigned to the device.
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Check if the device is paired.
     */
    public function isPaired(): bool
    {
        return !empty($this->device_token) && !empty($this->paired_at);
    }

    /**
     * Get display contents explicitly assigned to this device.
     */
    public function displayContents(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            DisplayContent::class,
            'display_content_device',
            'display_device_id',
            'display_content_id'
        )->withTimestamps();
    }

    /**
     * Check if another session is currently active (within 25 seconds).
     */
    public function hasActiveSession(?string $excludeSessionId = null): bool
    {
        if (empty($this->current_session_id) || empty($this->last_seen_at)) {
            return false;
        }

        if ($excludeSessionId && $this->current_session_id === $excludeSessionId) {
            return false;
        }

        // Active if heartbeat seen within 25 seconds
        return $this->last_seen_at->gt(now()->subSeconds(25));
    }

    /**
     * Claim this display device for a specific session ID (take over or new session).
     */
    public function claimSession(string $sessionId, ?string $ip = null, ?string $userAgent = null): void
    {
        $this->current_session_id = $sessionId;
        $this->session_started_at = now();
        $this->last_seen_at = now();
        $this->session_ip = $ip;
        $this->session_user_agent = $userAgent ? substr($userAgent, 0, 500) : null;
        $this->saveQuietly();
    }

    /**
     * Touch session to keep heartbeat alive.
     */
    public function touchSession(string $sessionId): bool
    {
        if ($this->current_session_id !== $sessionId) {
            return false;
        }

        $this->last_seen_at = now();
        $this->saveQuietly();
        return true;
    }

    /**
     * Release active session.
     */
    public function releaseSession(?string $sessionId = null): void
    {
        if ($sessionId === null || $this->current_session_id === $sessionId) {
            $this->current_session_id = null;
            $this->session_started_at = null;
            $this->session_ip = null;
            $this->session_user_agent = null;
            $this->last_seen_at = null;
            $this->saveQuietly();
        }
    }
}

