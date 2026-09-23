<?php

namespace App\Modules\Payments\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Companies\Models\Company;
use App\Modules\Subscriptions\Models\Plan;

class Subscription extends Model
{
    protected $fillable = [
        'company_id',
        'plan_id',
        'billing_cycle',
        'status',
        'starts_at',
        'ends_at',
        'canceled_at',
        'coupon_id',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'canceled_at' => 'datetime',
        'id' => 'integer',
        'company_id' => 'integer',
        'plan_id' => 'integer',
        'coupon_id' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function coupon()
    {
        return $this->belongsTo(\App\Modules\Coupons\Models\Coupon::class);
    }
}
