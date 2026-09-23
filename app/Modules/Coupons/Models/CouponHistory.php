<?php

namespace App\Modules\Coupons\Models;

use Illuminate\Database\Eloquent\Model;

class CouponHistory extends Model
{
    protected $table = 'coupon_history';
    
    // Immutable logs: disable auto Laravel updated_at, manually manage timestamps
    public $timestamps = false;

    protected $fillable = [
        'coupon_id',
        'company_id',
        'user_id',
        'action',
        'status',
        'failure_reason',
        'ip_address',
        'created_at',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->created_at = $model->created_at ?? now();
        });
    }

    protected $casts = [
        'created_at' => 'datetime',
        'is_valid' => 'boolean',
        'id' => 'integer',
        'coupon_id' => 'integer',
        'company_id' => 'integer',
        'user_id' => 'integer',
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
}
