<?php

namespace App\Modules\Coupons\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Modules\Coupons\Enums\CouponType;
use App\Modules\Coupons\Enums\CouponStatus;
use App\Modules\Coupons\Enums\CouponCategory;
use App\Modules\Coupons\Enums\PromotionDuration;
use Carbon\Carbon;

class Coupon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'code',
        'name',
        'description',
        'type',
        'promotion_type',
        'value',
        'duration_type',
        'duration_value',
        'duration_unit',
        'free_until_date',
        'extension_value',
        'extension_unit',
        'target_plan_id',
        'status',
        'starts_at',
        'expires_at',
        'timezone',
        'max_total_uses',
        'max_uses_per_tenant',
        'max_uses_per_user',
        'current_uses',
        'is_unlimited',
        'min_purchase_amount',
        'max_discount_amount',
        'billing_cycle',
        'country',
        'currency',
        'customer_type',
        'payment_type',
        'is_stackable',
        'is_hidden',
        'coupon_category',
        'allowed_emails',
        'allowed_domains',
        'allowed_companies',
        'tenant_restriction_type',
        'created_by',
        'admin_notes',
        'duration_in_months',
    ];

    protected $casts = [
        'type' => CouponType::class,
        'status' => CouponStatus::class,
        'coupon_category' => CouponCategory::class,
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'free_until_date' => 'datetime',
        'value' => 'decimal:2',
        'min_purchase_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'is_unlimited' => 'boolean',
        'is_stackable' => 'boolean',
        'is_hidden' => 'boolean',
        'allowed_emails' => 'array',
        'allowed_domains' => 'array',
        'allowed_companies' => 'array',
        'id' => 'integer',
        'target_plan_id' => 'integer',
        'duration_value' => 'integer',
        'extension_value' => 'integer',
        'duration_in_months' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($coupon) {
            if (empty($coupon->uuid)) {
                $coupon->uuid = (string) Str::uuid();
            }
            if (empty($coupon->code)) {
                $coupon->code = strtoupper(Str::random(8));
            }
        });
    }

    /**
     * Use UUID for route model binding.
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
        if (Str::isUuid($value)) {
            return $this->where('uuid', $value)->firstOrFail();
        }
        return $this->where($field ?? 'id', $value)->firstOrFail();
    }

    // Relationships
    public function plans()
    {
        return $this->belongsToMany(\App\Modules\Subscriptions\Models\Plan::class, 'coupon_plan');
    }

    public function companies()
    {
        return $this->belongsToMany(\App\Modules\Companies\Models\Company::class, 'coupon_tenant');
    }

    public function redemptions()
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function logs()
    {
        return $this->hasMany(CouponLog::class);
    }

    public function history()
    {
        return $this->hasMany(CouponHistory::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function targetPlan()
    {
        return $this->belongsTo(\App\Modules\Subscriptions\Models\Plan::class, 'target_plan_id');
    }

    // Helpers
    public function isActive(): bool
    {
        return $this->status === CouponStatus::ACTIVE;
    }

    public function isDisabled(): bool
    {
        return $this->status === CouponStatus::DISABLED;
    }

    public function isArchived(): bool
    {
        return $this->status === CouponStatus::ARCHIVED;
    }

    /**
     * Get normalized promotion type string.
     */
    public function getPromotionTypeString(): string
    {
        $rawType = $this->promotion_type ?: ($this->type instanceof CouponType ? $this->type->value : (string)$this->type);

        return match ($rawType) {
            'percentage', 'percentage_discount' => 'percentage_discount',
            'fixed', 'fixed_discount' => 'fixed_discount',
            'free_subscription', 'free_period' => 'free_period',
            'trial_extension', 'extended_subscription' => 'extended_subscription',
            'free_until_date' => 'free_until_date',
            'custom_price' => 'custom_price',
            'plan_upgrade' => 'plan_upgrade',
            'lifetime' => 'lifetime',
            default => $rawType,
        };
    }

    /**
     * Human-readable promotion label for UI tables.
     */
    public function getPromotionLabel(): string
    {
        $type = $this->getPromotionTypeString();
        $val = (float)$this->value;

        return match ($type) {
            'percentage_discount' => $val . '% OFF',
            'fixed_discount' => number_format($val, 2) . ' DH OFF',
            'free_period' => ($this->duration_value ?: $this->duration_in_months ?: (int)$val ?: 1) . ' ' . ucfirst($this->duration_unit ?: 'months') . ' FREE',
            'extended_subscription' => '+' . ($this->extension_value ?: $this->duration_in_months ?: (int)$val ?: 1) . ' ' . ucfirst($this->extension_unit ?: 'months'),
            'free_until_date' => 'Free Until ' . ($this->free_until_date ? $this->free_until_date->format('M d, Y') : 'Date'),
            'custom_price' => number_format($val, 2) . ' DH Fixed Price',
            'plan_upgrade' => 'Upgrade to ' . ($this->targetPlan?->name ?? 'Plan'),
            'lifetime' => $val . '% Lifetime OFF',
            default => $this->type?->label() ?? 'Discount',
        };
    }

    /**
     * Human-readable duration description for UI tables.
     */
    public function getDurationDescription(): string
    {
        $type = $this->getPromotionTypeString();

        if ($type === 'extended_subscription') {
            return 'One time';
        }

        if ($type === 'free_until_date') {
            return $this->free_until_date ? 'Until ' . $this->free_until_date->format('M d, Y') : 'Until specific date';
        }

        $durType = $this->duration_type ?: 'one_time';
        $durVal = $this->duration_value ?: $this->duration_in_months;

        return match ($durType) {
            'one_time' => '1 billing cycle',
            'number_of_billing_cycles' => ($durVal ?: 1) . ' billing cycle' . (($durVal > 1) ? 's' : ''),
            'number_of_months' => ($durVal ?: 1) . ' month' . (($durVal > 1) ? 's' : ''),
            'number_of_days' => ($durVal ?: 1) . ' day' . (($durVal > 1) ? 's' : ''),
            'until_date' => $this->free_until_date ? 'Until ' . $this->free_until_date->format('M d, Y') : 'Until date',
            'lifetime' => 'Lifetime',
            default => $durVal ? $durVal . ' ' . ($this->duration_unit ?: 'months') : '1 cycle',
        };
    }

    /**
     * Human-readable plans summary.
     */
    public function getPlansSummary(): string
    {
        if ($this->plans->isEmpty()) {
            return 'All plans';
        }
        return $this->plans->pluck('name')->join(', ');
    }

    /**
     * Human-readable validity string.
     */
    public function getValiditySummary(): string
    {
        if (!$this->starts_at && !$this->expires_at) {
            return 'Permanent';
        }
        if ($this->starts_at && $this->expires_at) {
            return $this->starts_at->format('M d') . ' → ' . $this->expires_at->format('M d, Y');
        }
        if ($this->expires_at) {
            return 'Until ' . $this->expires_at->format('M d, Y');
        }
        return 'From ' . $this->starts_at->format('M d, Y');
    }
}
