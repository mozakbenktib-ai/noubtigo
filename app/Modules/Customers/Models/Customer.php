<?php

namespace App\Modules\Customers\Models;

use App\Modules\Core\Traits\BelongsToTenant;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Authenticatable
{
    use SoftDeletes, BelongsToTenant, Notifiable;

    /**
     * Normalize phone number to international format (e.g., 212600000000).
     */
    public static function normalizePhone(?string $phone): ?string
    {
        if (!$phone) return null;

        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Handle Morocco local format (starts with 06 or 07)
        if (preg_match('/^0[567]/', $phone)) {
            $phone = '212' . substr($phone, 1);
        }

        // Handle redundant prefix (21206...)
        if (preg_match('/^2120[567]/', $phone)) {
            $phone = '212' . substr($phone, 4);
        }

        return $phone;
    }

    /**
     * Set the phone attribute with normalization.
     */
    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = self::normalizePhone($value);
    }

    protected $fillable = [
        'company_id',
        'first_name',
        'last_name',
        'identifier',
        'cin',
        'file_number',
        'plate_number',
        'phone',
        'email',
        'password',
        'avatar',
        'is_vip',
        'last_company_id',
        'last_user_message_at',
        'locale',
        'points',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_vip' => 'boolean',
        'email_verified_at' => 'datetime',
        'id' => 'integer',
        'company_id' => 'integer',
        'last_company_id' => 'integer',
        'service_id' => 'integer',
    ];

    protected $appends = [
        'full_name',
    ];

    /**
     * Get the full name.
     */
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get the tickets for the customer.
     */
    protected static function booted()
    {
        static::creating(function ($customer) {
            if (empty($customer->uuid)) {
                $customer->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the tickets for the customer.
     */
    public function tickets()
    {
        return $this->hasMany(\App\Modules\Queue\Models\Ticket::class);
    }

    /**
     * Favorite companies.
     */
    public function favoriteCompanies()
    {
        return $this->belongsToMany(\App\Modules\Companies\Models\Company::class, 'customer_favorites');
    }

    /**
     * Scope a query to filter customers.
     */
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('identifier', 'like', "%{$search}%")
                    ->orWhere('cin', 'like', "%{$search}%")
                    ->orWhere('file_number', 'like', "%{$search}%")
                    ->orWhere('plate_number', 'like', "%{$search}%");
            });
        });

        $query->when($filters['vip'] ?? null, function ($query, $vip) {
            if ($vip === 'yes') $query->where('is_vip', true);
            if ($vip === 'no') $query->where('is_vip', false);
        });

        $query->when($filters['service'] ?? null, function ($query, $service) {
            $query->whereHas('tickets', function ($query) use ($service) {
                $query->where('service_id', $service);
            });
        });

        $query->when($filters['status'] ?? null, function ($query, $status) {
            $query->whereHas('tickets', function ($query) use ($status) {
                $query->where('status', $status);
            });
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
