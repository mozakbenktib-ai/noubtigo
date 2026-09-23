<?php

namespace App\Modules\Coupons\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CouponRedemption extends Model
{
    protected $table = 'coupon_redemptions';

    protected $fillable = [
        'coupon_id',
        'company_id',
        'subscription_id',
        'promotion_type',
        'discount_type',
        'discount_value',
        'duration_type',
        'duration_value',
        'duration_unit',
        'billing_cycles_discounted',
        'max_discounted_cycles',
        'original_price',
        'discount_amount',
        'final_amount',
        'free_until_date',
        'status',
        'starts_at',
        'ends_at',
        'redeemed_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'redeemed_at' => 'datetime',
        'free_until_date' => 'datetime',
        'id' => 'integer',
        'coupon_id' => 'integer',
        'company_id' => 'integer',
        'subscription_id' => 'integer',
        'discount_value' => 'decimal:2',
        'original_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'duration_value' => 'integer',
        'billing_cycles_discounted' => 'integer',
        'max_discounted_cycles' => 'integer',
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function company()
    {
        return $this->belongsTo(\App\Modules\Companies\Models\Company::class, 'company_id');
    }

    public function subscription()
    {
        return $this->belongsTo(\App\Modules\Payments\Models\Subscription::class);
    }

    /**
     * Check if the promotion is currently active.
     */
    public function isPromotionActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        // Duration: until_date / free_until_date
        if ($this->duration_type === 'until_date' || $this->promotion_type === 'free_until_date') {
            $until = $this->free_until_date ?: $this->ends_at;
            return $until ? Carbon::now()->lessThanOrEqualTo($until) : true;
        }

        // Duration: number_of_billing_cycles
        if ($this->duration_type === 'number_of_billing_cycles' || $this->duration_type === 'one_time') {
            $max = $this->max_discounted_cycles ?: ($this->duration_type === 'one_time' ? 1 : $this->duration_value);
            if ($max && $this->billing_cycles_discounted >= $max) {
                return false;
            }
        }

        // Time-based ends_at
        if ($this->ends_at && Carbon::now()->greaterThan($this->ends_at)) {
            return false;
        }

        return true;
    }

    /**
     * Record that a billing cycle was discounted.
     */
    public function recordDiscountedCycle(): void
    {
        $this->increment('billing_cycles_discounted');

        $max = $this->max_discounted_cycles ?: ($this->duration_type === 'one_time' ? 1 : ($this->duration_type === 'number_of_billing_cycles' ? $this->duration_value : null));
        if ($max && $this->billing_cycles_discounted >= $max) {
            $this->update(['status' => 'completed']);
        }
    }
}
