<?php

namespace App\Modules\Coupons\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Coupons\Enums\CouponType;
use App\Modules\Coupons\Enums\CouponStatus;
use App\Modules\Coupons\Enums\CouponCategory;
use App\Modules\Coupons\Enums\PromotionDuration;
use Illuminate\Validation\Rules\Enum;

class SaveCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->is_system_admin;
    }

    public function rules(): array
    {
        $couponId = $this->route('coupon')?->id;

        return [
            'code'                    => 'required|string|max:100|unique:coupons,code,' . ($couponId ?? 'NULL') . ',id',
            'name'                    => 'required|string|max:255',
            'description'             => 'nullable|string',
            'type'                    => ['required', new Enum(CouponType::class)],
            'promotion_type'          => 'nullable|string|max:50',
            'value'                   => 'nullable|numeric|min:0',
            'duration_type'           => ['nullable', new Enum(PromotionDuration::class)],
            'duration_value'          => 'nullable|integer|min:1',
            'duration_unit'           => 'nullable|in:days,months,cycles',
            'free_until_date'         => 'nullable|date',
            'extension_value'         => 'nullable|integer|min:1',
            'extension_unit'          => 'nullable|in:days,months',
            'duration_in_months'      => 'nullable|integer|min:1',
            'target_plan_id'          => 'nullable|exists:plans,id',
            'status'                  => ['required', new Enum(CouponStatus::class)],
            'starts_at'               => 'nullable|date',
            'expires_at'              => 'nullable|date',
            'timezone'                => 'required|string',
            'max_total_uses'          => 'nullable|integer|min:1',
            'max_uses_per_tenant'     => 'nullable|integer|min:1',
            'max_uses_per_user'       => 'nullable|integer|min:1',
            'is_unlimited'            => 'boolean',
            'min_purchase_amount'     => 'nullable|numeric|min:0',
            'max_discount_amount'     => 'nullable|numeric|min:0',
            'billing_cycle'           => 'nullable|in:monthly,annual,both',
            'country'                 => 'nullable|string|max:3',
            'currency'                => 'nullable|string|max:3',
            'customer_type'           => 'required|in:both,new_customers,existing_customers',
            'payment_type'            => 'required|in:both,first_payment,renewals',
            'is_stackable'            => 'boolean',
            'is_hidden'               => 'boolean',
            'coupon_category'         => ['required', new Enum(CouponCategory::class)],
            'allowed_emails'          => 'nullable|array',
            'allowed_emails.*'        => 'email',
            'allowed_domains'         => 'nullable|array',
            'allowed_domains.*'       => 'string',
            'allowed_companies'       => 'nullable|array',
            'allowed_companies.*'     => 'string',
            'tenant_restriction_type' => 'required|in:none,whitelist,blacklist',
            'plan_ids'                => 'nullable|array',
            'plan_ids.*'              => 'exists:plans,id',
            'company_ids'             => 'nullable|array',
            'company_ids.*'           => 'exists:companies,id',
            'admin_notes'             => 'nullable|string',
        ];
    }
}
