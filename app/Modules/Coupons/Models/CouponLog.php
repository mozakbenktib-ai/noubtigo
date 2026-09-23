<?php

namespace App\Modules\Coupons\Models;

use Illuminate\Database\Eloquent\Model;

class CouponLog extends Model
{
    protected $table = 'coupon_logs';
    
    // Immutable logs
    public $timestamps = false;

    protected $fillable = [
        'coupon_id',
        'user_id',
        'action',
        'changes',
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
        'changes' => 'array',
        'created_at' => 'datetime',
        'id' => 'integer',
        'coupon_id' => 'integer',
        'user_id' => 'integer',
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
