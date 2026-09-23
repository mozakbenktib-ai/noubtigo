<?php

namespace App\Modules\Coupons\Models;

use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    protected $table = 'coupon_usages';

    protected $fillable = [
        'coupon_id',
        'company_id',
        'user_id',
        'invoice_id',
        'payment_id',
        'subscription_id',
        'discount_amount',
        'ip_address',
    ];

    protected $casts = [
        'id' => 'integer',
        'coupon_id' => 'integer',
        'company_id' => 'integer',
        'user_id' => 'integer',
        'invoice_id' => 'integer',
        'payment_id' => 'integer',
        'subscription_id' => 'integer',
    ];


    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function company()
    {
        return $this->belongsTo(\App\Modules\Companies\Models\Company::class, 'company_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function invoice()
    {
        return $this->belongsTo(\App\Modules\Payments\Models\Invoice::class);
    }

    public function payment()
    {
        return $this->belongsTo(\App\Modules\Payments\Models\Payment::class);
    }

    public function subscription()
    {
        return $this->belongsTo(\App\Modules\Payments\Models\Subscription::class);
    }
}
