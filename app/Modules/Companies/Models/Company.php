<?php

namespace App\Modules\Companies\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'logo',
        'settings',
        'is_active',
        'timezone',
        'plan_id',
        'code',
        'secure_public_token',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'id' => 'integer',
        'plan_id' => 'integer',
        'company_id' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function booted()
    {
        static::creating(function ($company) {
            if (empty($company->uuid)) {
                $company->uuid = (string) Str::uuid();
            }
            if (empty($company->code)) {
                $company->code = static::generateUniqueCode();
            }
            if (empty($company->secure_public_token)) {
                $company->secure_public_token = static::generateSecureToken();
            }
        });
    }

    /**
     * Generate a unique secure token for tracking.
     */
    public static function generateSecureToken(): string
    {
        do {
            $token = 'trk_' . \Illuminate\Support\Str::random(10);
        } while (static::where('secure_public_token', $token)->exists());

        return $token;
    }

    /**
     * Generate a unique company code (4 numbers, 2 letters).
     */
    public static function generateUniqueCode(): string
    {
        do {
            $code = rand(1000, 9999) . strtoupper(\Illuminate\Support\Str::random(2));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    /**
     * Get the branding colors from settings.
     */
    public function getBrandingColors(): array
    {
        return $this->settings['colors'] ?? [
            'primary' => '#22c55e',
            'secondary' => '#06b6d4',
            'gradient' => 'linear-gradient(135deg, #22c55e, #06b6d4)'
        ];
    }

    /**
     * Get the logo URL.
     */
    public function getLogoUrl(): string
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }
        
        return asset('frontend/img/logo-placeholder.png'); // Fallback placeholder
    }

    /**
     * Get the plan assigned to the company.
     */
    public function plan()
    {
        return $this->belongsTo('App\Modules\Subscriptions\Models\Plan', 'plan_id');
    }

    /**
     * Helper to get a specific limit from the plan.
     */
    public function getLimit(string $key, $default = null)
    {
        if (!$this->plan) return $default;
        return $this->plan->getLimit($key, $default);
    }

    /**
     * Get the users that belong to the company.
     */
    public function users()
    {
        return $this->hasMany('App\Models\User', 'company_id');
    }

    /**
     * Get the queue priority scoring rules.
     */
    public function getQueueRules(): array
    {
        $defaults = [
            'vip' => 1,
            'on_time_appointment' => 2,
            'in_grace_appointment' => 3,
            'walk_in' => 4,
        ];

        $rules = $this->settings['queue_rules'] ?? [];
        $rules = array_replace($defaults, is_array($rules) ? $rules : []);

        // Preserve a stable, usable order if legacy settings are incomplete or invalid.
        $values = array_values($rules);
        if (count(array_unique($values)) !== count($values)
            || array_diff($values, [1, 2, 3, 4])
            || array_diff([1, 2, 3, 4], $values)) {
            return $defaults;
        }

        return array_map('intval', $rules);
    }

    /**
     * Get the queue mode for the company.
     * Enforces 'simple' if the plan does not support advanced features.
     */
    public function getQueueMode(): string
    {
        $mode = $this->settings['queue_mode'] ?? 'advanced';

        // If company is on a plan that doesn't allow advanced queue, force simple
        if ($mode === 'advanced' && !$this->hasFeature('queue.advanced')) {
            return 'simple';
        }

        return $mode;
    }

    /**
     * Check if the company uses Simple Queue mode.
     */
    public function isSimpleQueue(): bool
    {
        return $this->getQueueMode() === 'simple';
    }

    /**
     * Check if the company uses Advanced Queue mode.
     */
    public function isAdvancedQueue(): bool
    {
        return $this->getQueueMode() === 'advanced';
    }

    /**
     * Check if the current company plan has a specific feature.
     */
    public function hasFeature(string $slug): bool
    {
        if (!$this->plan) {
            return false;
        }

        return $this->plan->hasFeature($slug);
    }

    /**
     * Get the roles that belong to the company (Many-to-Many).
     */
    public function roles()
    {
        return $this->belongsToMany('App\Models\Role', 'role_company');
    }

    /**
     * Get the rooms that belong to the company.
     */
    public function rooms()
    {
        return $this->hasMany('App\Modules\Rooms\Models\Room', 'company_id');
    }

    /**
     * Get the services that belong to the company.
     */
    public function services()
    {
        return $this->hasMany('App\Modules\Services\Models\Service', 'company_id');
    }

    /**
     * Get the permissions explicitly assigned to the company.
     */
    public function permissions()
    {
        return $this->belongsToMany('App\Models\Permission', 'permission_company');
    }

    /**
     * Get the daily tracking code for customers.
     * Regenerates every day based on company ID and date.
     */
    public function getDailyTrackingCode(): string
    {
        $date = date('Y-m-d');
        $salt = config('app.key'); // Use app key as salt for semi-uniqueness per installation
        
        // Generate a 4-digit numeric code
        $hash = md5($this->id . $date . $salt);
        // Take first 8 chars of hash, convert hex to dec, and take last 4 digits
        $numeric = (string) hexdec(substr($hash, 0, 8));
        return substr($numeric, -4);
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

    /**
     * Get the subscriptions for the company.
     */
    public function subscriptions()
    {
        return $this->hasMany(\App\Modules\Payments\Models\Subscription::class);
    }

    /**
     * Get the payments for the company.
     */
    public function payments()
    {
        return $this->hasMany(\App\Modules\Payments\Models\Payment::class);
    }

    /**
     * Get the invoices for the company.
     */
    public function invoices()
    {
        return $this->hasMany(\App\Modules\Payments\Models\Invoice::class);
    }

    /**
     * Set the phone attribute with normalization.
     */
    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = \App\Modules\Customers\Models\Customer::normalizePhone($value);
    }
}
