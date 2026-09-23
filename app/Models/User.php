<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Modules\Core\Traits\BelongsToTenant;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Modules\Core\Traits\HasRBAC;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\CanResetPassword;

class User extends Authenticatable implements CanResetPasswordContract
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, BelongsToTenant, HasRBAC, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'first_name',
        'last_name',
        'username',
        'email',
        'google_id',
        'phone',
        'password',
        'requires_password_change',
        'is_system_admin',
        'locale',
        'timezone',
        'is_active',
        'avatar',
        'assigned_room_id',
        'assigned_service_id',
    ];

    protected $casts = [
        'id' => 'integer',
        'company_id' => 'integer',
        'assigned_room_id' => 'integer',
        'assigned_service_id' => 'integer',
    ];


    /**
     * Set the phone attribute with normalization.
     */
    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = \App\Modules\Customers\Models\Customer::normalizePhone($value);
    }

    /**
     * Boot the model: auto-populate timezone from tenant on creation.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (User $user) {
            // If no timezone set, inherit from company
            if (empty($user->timezone) && $user->company_id) {
                $company = \App\Modules\Companies\Models\Company::find($user->company_id);
                if ($company && !empty($company->timezone)) {
                    $user->timezone = $company->timezone;
                }
            }
            // Auto-generate UUID if not set
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get the effective timezone using the fallback chain:
     * user.timezone → company.timezone → UTC
     */
    public function getEffectiveTimezone(): string
    {
        if (!empty($this->timezone)) {
            return $this->timezone;
        }

        if ($this->company && !empty($this->company->timezone)) {
            return $this->company->timezone;
        }

        return 'UTC';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the room assigned to the user.
     */
    public function assignedRoom()
    {
        return $this->belongsTo(\App\Modules\Rooms\Models\Room::class, 'assigned_room_id');
    }

    /**
     * Get the company this user belongs to.
     */
    public function company()
    {
        return $this->belongsTo(\App\Modules\Companies\Models\Company::class, 'company_id');
    }

    /**
     * Get the service assigned to the user.
     */
    public function assignedService()
    {
        return $this->belongsTo(\App\Modules\Services\Models\Service::class, 'assigned_service_id');
    }

    /**
     * Scope a query to filter users.
     */
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        });

        $query->when($filters['role'] ?? null, function ($query, $role) {
            $query->whereHas('roles', function ($query) use ($role) {
                $query->where('roles.id', $role);
            });
        });

        $query->when(isset($filters['status']), function ($query) use ($filters) {
            if ($filters['status'] === 'active') $query->where('is_active', true);
            if ($filters['status'] === 'inactive') $query->where('is_active', false);
        });

        $query->when($filters['room'] ?? null, function ($query, $room) {
            $query->where('assigned_room_id', $room);
        });

        $query->when($filters['service'] ?? null, function ($query, $service) {
            $query->where('assigned_service_id', $service);
        });
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
