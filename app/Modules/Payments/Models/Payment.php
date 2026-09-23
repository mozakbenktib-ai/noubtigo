<?php

namespace App\Modules\Payments\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Companies\Models\Company;

class Payment extends Model
{
    protected $fillable = [
        'company_id',
        'subscription_id',
        'amount',
        'discount',
        'coupon_id',
        'currency',
        'payment_method',
        'receipt_path',
        'status',
    ];

    protected $casts = [
        'id' => 'integer',
        'company_id' => 'integer',
        'subscription_id' => 'integer',
        'coupon_id' => 'integer',
        'discount' => 'decimal:2',
    ];


    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
