<?php

namespace App\Modules\Customers\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Customers\Models\Customer;

class PortalUser extends Authenticatable
{
    use SoftDeletes, Notifiable;

    protected $table = 'portal_users';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'avatar',
        'points',
        'locale',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'points' => 'integer',
        'id' => 'integer',
        'portal_user_id' => 'integer',
        'company_id' => 'integer',
    ];

    /**
     * Normalize phone number (reuse logic from Customer or centralize it).
     */
    public static function normalizePhone(?string $phone): ?string
    {
        return Customer::normalizePhone($phone);
    }

    /**
     * Set the phone attribute with normalization.
     */
    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = self::normalizePhone($value);
    }

    /**
     * Get the full name.
     */
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Favorite companies.
     */
    public function favoriteCompanies()
    {
        return $this->belongsToMany(\App\Modules\Companies\Models\Company::class, 'portal_user_favorites', 'portal_user_id', 'company_id');
    }
}
