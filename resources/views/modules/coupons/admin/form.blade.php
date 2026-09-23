@extends('layouts.dashboard')
@section('title', isset($coupon) ? 'Edit Coupon' : 'Create Coupon & Campaign')
@section('header_title', isset($coupon) ? 'Edit Coupon' : 'Create Promotion Campaign')
@section('breadcrumb_parent', 'Coupons')
@section('breadcrumb_parent_url', route('admin.coupons.index'))

@section('content')
<form method="POST" action="{{ isset($coupon) ? route('admin.coupons.update', $coupon->uuid) : route('admin.coupons.store') }}" id="couponForm">
    @csrf
    @if(isset($coupon)) @method('PUT') @endif

    <div class="row g-4">
        {{-- Left Column: Main Config --}}
        <div class="col-lg-8">
            {{-- 1. Basic Info --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 slide-up stagger-2">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-info-circle text-primary me-2"></i>1. Basic Information</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Coupon Code <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="code" id="couponCodeField" class="form-control rounded-start-3 @error('code') is-invalid @enderror" value="{{ old('code', $coupon->code ?? $randomCode ?? '') }}" required style="text-transform:uppercase;font-family:monospace;letter-spacing:2px">
                                <button type="button" class="btn btn-outline-secondary rounded-end-3" id="generateCodeBtn" title="Generate Random Code"><i class="bi bi-shuffle"></i></button>
                            </div>
                            @error('code') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Internal Campaign Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-3 @error('name') is-invalid @enderror" value="{{ old('name', $coupon->name ?? '') }}" placeholder="e.g. Startup Launch 50% Off" required>
                            @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Description / Terms</label>
                            <textarea name="description" class="form-control rounded-3" rows="2" placeholder="Public promotion details or internal notes...">{{ old('description', $coupon->description ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Promotion & Duration Configuration --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 slide-up stagger-3">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-gift text-primary me-2"></i>2. Promotion & Duration</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        {{-- Promotion Type --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Promotion Type <span class="text-danger">*</span></label>
                            @php
                                $currentType = old('type', isset($coupon) ? ($coupon->type->value ?? 'percentage_discount') : 'percentage_discount');
                                if ($currentType === 'percentage') $currentType = 'percentage_discount';
                                if ($currentType === 'fixed') $currentType = 'fixed_discount';
                                if ($currentType === 'free_subscription') $currentType = 'free_period';
                                if ($currentType === 'trial_extension') $currentType = 'extended_subscription';
                            @endphp
                            <select name="type" id="promotionTypeSelect" class="form-select rounded-3 @error('type') is-invalid @enderror" required>
                                <optgroup label="Modern Promotions">
                                    <option value="percentage_discount" {{ $currentType === 'percentage_discount' ? 'selected' : '' }}>Percentage Discount (e.g. 50% OFF)</option>
                                    <option value="fixed_discount" {{ $currentType === 'fixed_discount' ? 'selected' : '' }}>Fixed Amount Discount (e.g. 100 DH OFF)</option>
                                    <option value="free_period" {{ $currentType === 'free_period' ? 'selected' : '' }}>Free Period (e.g. 3 Months Free Trial)</option>
                                    <option value="extended_subscription" {{ $currentType === 'extended_subscription' ? 'selected' : '' }}>Subscription Extension (e.g. +3 Months Free)</option>
                                    <option value="free_until_date" {{ $currentType === 'free_until_date' ? 'selected' : '' }}>Free Access Until Specific Date</option>
                                </optgroup>
                                <optgroup label="Legacy & Advanced Types">
                                    <option value="lifetime" {{ $currentType === 'lifetime' ? 'selected' : '' }}>Lifetime Discount</option>
                                    <option value="custom_price" {{ $currentType === 'custom_price' ? 'selected' : '' }}>Custom Fixed Plan Price</option>
                                    <option value="plan_upgrade" {{ $currentType === 'plan_upgrade' ? 'selected' : '' }}>Plan Upgrade</option>
                                </optgroup>
                            </select>
                            <input type="hidden" name="promotion_type" id="hiddenPromotionType" value="{{ $currentType }}">
                            @error('type') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        {{-- Category --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Campaign Category</label>
                            <select name="coupon_category" class="form-select rounded-3" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->value }}" {{ old('coupon_category', $coupon->coupon_category->value ?? 'general') === $cat->value ? 'selected' : '' }}>{{ $cat->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Section A: Value for Percentage / Fixed / Custom Price --}}
                        <div class="col-md-6" id="field-discount-value">
                            <label class="form-label small fw-semibold" id="valueLabel">Discount Value</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" name="value" id="fieldValueInput" class="form-control rounded-start-3 @error('value') is-invalid @enderror" value="{{ old('value', $coupon->value ?? '') }}" placeholder="e.g. 50">
                                <span class="input-group-text rounded-end-3" id="valueAddon">%</span>
                            </div>
                            <div class="form-text small text-muted" id="valueHint">Percentage discount applied to each billing cycle.</div>
                            @error('value') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        {{-- Duration Configuration for Discounts --}}
                        <div class="col-md-6" id="field-duration-type">
                            <label class="form-label small fw-semibold">Discount Duration</label>
                            @php
                                $curDurType = old('duration_type', isset($coupon) ? ($coupon->duration_type ?? 'one_time') : 'one_time');
                            @endphp
                            <select name="duration_type" id="durationTypeSelect" class="form-select rounded-3">
                                <option value="one_time" {{ $curDurType === 'one_time' ? 'selected' : '' }}>One Time (1st billing cycle only)</option>
                                <option value="number_of_billing_cycles" {{ $curDurType === 'number_of_billing_cycles' ? 'selected' : '' }}>Number of Billing Cycles (e.g. first 3 cycles)</option>
                                <option value="number_of_months" {{ $curDurType === 'number_of_months' ? 'selected' : '' }}>Number of Months (calendar duration)</option>
                                <option value="number_of_days" {{ $curDurType === 'number_of_days' ? 'selected' : '' }}>Number of Days</option>
                                <option value="until_date" {{ $curDurType === 'until_date' ? 'selected' : '' }}>Until Specific Date</option>
                                <option value="lifetime" {{ $curDurType === 'lifetime' ? 'selected' : '' }}>Lifetime (Every renewal)</option>
                            </select>
                        </div>

                        {{-- Duration Value Input --}}
                        <div class="col-md-6" id="field-duration-value">
                            <label class="form-label small fw-semibold" id="durationValueLabel">Duration Count</label>
                            <input type="number" min="1" name="duration_value" class="form-control rounded-3" value="{{ old('duration_value', $coupon->duration_value ?? $coupon->duration_in_months ?? 3) }}" placeholder="e.g. 3">
                            <div class="form-text small text-muted" id="durationValueHint">Applies discount for this number of cycles/months.</div>
                        </div>

                        {{-- Section B: Free Period Duration (Free Trial) --}}
                        <div class="col-md-6" id="field-free-period" style="display:none;">
                            <label class="form-label small fw-semibold">Free Period Length</label>
                            <div class="input-group">
                                <input type="number" min="1" name="free_period_val" id="freePeriodVal" class="form-control rounded-start-3" value="{{ old('duration_value', $coupon->duration_value ?? $coupon->duration_in_months ?? 3) }}" placeholder="e.g. 3">
                                <select name="duration_unit" class="form-select rounded-end-3" style="max-width:120px">
                                    <option value="months" {{ old('duration_unit', $coupon->duration_unit ?? 'months') === 'months' ? 'selected' : '' }}>Months</option>
                                    <option value="days" {{ old('duration_unit', $coupon->duration_unit ?? '') === 'days' ? 'selected' : '' }}>Days</option>
                                </select>
                            </div>
                            <div class="form-text small text-muted">Customer pays 0 DH during this period. Normal billing starts after.</div>
                        </div>

                        {{-- Section C: Extension Amount --}}
                        <div class="col-md-6" id="field-extension-config" style="display:none;">
                            <label class="form-label small fw-semibold">Subscription Extension</label>
                            <div class="input-group">
                                <input type="number" min="1" name="extension_value" class="form-control rounded-start-3" value="{{ old('extension_value', $coupon->extension_value ?? $coupon->duration_in_months ?? 3) }}" placeholder="e.g. 3">
                                <select name="extension_unit" class="form-select rounded-end-3" style="max-width:120px">
                                    <option value="months" {{ old('extension_unit', $coupon->extension_unit ?? 'months') === 'months' ? 'selected' : '' }}>Months</option>
                                    <option value="days" {{ old('extension_unit', $coupon->extension_unit ?? '') === 'days' ? 'selected' : '' }}>Days</option>
                                </select>
                            </div>
                            <div class="form-text small text-muted">Extends active customer expiration date directly.</div>
                        </div>

                        {{-- Section D: Free Until Date --}}
                        <div class="col-md-6" id="field-free-until" style="display:none;">
                            <label class="form-label small fw-semibold">Free Access Until Date</label>
                            <input type="date" name="free_until_date" class="form-control rounded-3" value="{{ old('free_until_date', isset($coupon) && $coupon->free_until_date ? $coupon->free_until_date->format('Y-m-d') : '') }}">
                            <div class="form-text small text-muted">Subscription is free (0 DH) until the specified date.</div>
                        </div>

                        {{-- Section E: Target Plan for Upgrade --}}
                        <div class="col-md-6" id="field-target-plan" style="display:none;">
                            <label class="form-label small fw-semibold">Target Upgrade Plan</label>
                            <select name="target_plan_id" class="form-select rounded-3">
                                <option value="">— Select Target Plan —</option>
                                @foreach($plans as $p)
                                    <option value="{{ $p->id }}" {{ old('target_plan_id', $coupon->target_plan_id ?? '') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Eligibility & Restrictions --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 slide-up stagger-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-shield-check text-primary me-2"></i>3. Eligibility & Restrictions</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Customer Eligibility</label>
                            <select name="customer_type" class="form-select rounded-3" required>
                                <option value="both" {{ old('customer_type', $coupon->customer_type ?? 'both') === 'both' ? 'selected' : '' }}>All Customers (New & Existing)</option>
                                <option value="new_customers" {{ old('customer_type', $coupon->customer_type ?? '') === 'new_customers' ? 'selected' : '' }}>New Customers Only</option>
                                <option value="existing_customers" {{ old('customer_type', $coupon->customer_type ?? '') === 'existing_customers' ? 'selected' : '' }}>Existing Customers Only</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Subscription Eligibility</label>
                            <select name="payment_type" class="form-select rounded-3" required>
                                <option value="both" {{ old('payment_type', $coupon->payment_type ?? 'both') === 'both' ? 'selected' : '' }}>Both (New & Renewals)</option>
                                <option value="first_payment" {{ old('payment_type', $coupon->payment_type ?? '') === 'first_payment' ? 'selected' : '' }}>New Subscription Only</option>
                                <option value="renewals" {{ old('payment_type', $coupon->payment_type ?? '') === 'renewals' ? 'selected' : '' }}>Renewals Only</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Billing Cycle Compatibility</label>
                            <select name="billing_cycle" class="form-select rounded-3">
                                <option value="both" {{ old('billing_cycle', $coupon->billing_cycle ?? 'both') === 'both' ? 'selected' : '' }}>Both Monthly & Annual</option>
                                <option value="monthly" {{ old('billing_cycle', $coupon->billing_cycle ?? '') === 'monthly' ? 'selected' : '' }}>Monthly Billing Only</option>
                                <option value="annual" {{ old('billing_cycle', $coupon->billing_cycle ?? '') === 'annual' ? 'selected' : '' }}>Annual Billing Only</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Applicable Plans</label>
                            <select name="plan_ids[]" class="form-select rounded-3" multiple size="3">
                                @foreach($plans as $p)
                                    <option value="{{ $p->id }}" {{ in_array($p->id, old('plan_ids', isset($coupon) ? $coupon->plans->pluck('id')->toArray() : [])) ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text small text-muted">Hold Ctrl / Cmd to select multiple. Leave unselected for all plans.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Min Purchase (DH)</label>
                            <input type="number" step="0.01" name="min_purchase_amount" class="form-control rounded-3" value="{{ old('min_purchase_amount', $coupon->min_purchase_amount ?? '') }}" placeholder="0.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Max Discount Cap (DH)</label>
                            <input type="number" step="0.01" name="max_discount_amount" class="form-control rounded-3" value="{{ old('max_discount_amount', $coupon->max_discount_amount ?? '') }}" placeholder="No limit">
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. Tenant Whitelist / Blacklist --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 slide-up stagger-5">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-building text-primary me-2"></i>4. Tenant Whitelist / Blacklist</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Restriction Type</label>
                            <select name="tenant_restriction_type" class="form-select rounded-3" required>
                                <option value="none" {{ old('tenant_restriction_type', $coupon->tenant_restriction_type ?? 'none') === 'none' ? 'selected' : '' }}>No Restriction (Open to All)</option>
                                <option value="whitelist" {{ old('tenant_restriction_type', $coupon->tenant_restriction_type ?? '') === 'whitelist' ? 'selected' : '' }}>Whitelist (Only Selected Tenants)</option>
                                <option value="blacklist" {{ old('tenant_restriction_type', $coupon->tenant_restriction_type ?? '') === 'blacklist' ? 'selected' : '' }}>Blacklist (Exclude Selected Tenants)</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Select Tenants</label>
                            <select name="company_ids[]" class="form-select rounded-3" multiple size="3">
                                @foreach($companies as $c)
                                    <option value="{{ $c->id }}" {{ in_array($c->id, old('company_ids', isset($coupon) ? $coupon->companies->pluck('id')->toArray() : [])) ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Allowed Email Addresses (one per line)</label>
                            <textarea name="allowed_emails_raw" class="form-control rounded-3" rows="2" placeholder="e.g. ceo@startup.com">{{ old('allowed_emails_raw', isset($coupon) && $coupon->allowed_emails ? implode("\n", $coupon->allowed_emails) : '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Limits, Validity, Status, Code Generator --}}
        <div class="col-lg-4">
            {{-- Usage Limits --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 slide-up stagger-2">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-speedometer text-primary me-2"></i>Usage Limits</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Max Total Uses</label>
                        <input type="number" name="max_total_uses" id="maxTotalUses" class="form-control rounded-3" value="{{ old('max_total_uses', $coupon->max_total_uses ?? '') }}" placeholder="Leave blank for unlimited">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Max Per Customer / Tenant</label>
                        <input type="number" name="max_uses_per_tenant" class="form-control rounded-3" value="{{ old('max_uses_per_tenant', $coupon->max_uses_per_tenant ?? '1') }}" placeholder="1">
                        <div class="form-text small text-muted">Default: 1 use per customer.</div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="is_unlimited" id="isUnlimited" value="1" {{ old('is_unlimited', $coupon->is_unlimited ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold small" for="isUnlimited">Unlimited Total Redemptions</label>
                    </div>
                </div>
            </div>

            {{-- Validity Window --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 slide-up stagger-3">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-calendar-range text-primary me-2"></i>Redemption Window</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select rounded-3" required>
                            @foreach($statuses as $s)
                                <option value="{{ $s->value }}" {{ old('status', $coupon->status->value ?? 'active') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Starts At (Redeemable From)</label>
                        <input type="datetime-local" name="starts_at" class="form-control rounded-3" value="{{ old('starts_at', isset($coupon) && $coupon->starts_at ? $coupon->starts_at->format('Y-m-d\TH:i') : '') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Expires At (Redeemable Until)</label>
                        <input type="datetime-local" name="expires_at" class="form-control rounded-3" value="{{ old('expires_at', isset($coupon) && $coupon->expires_at ? $coupon->expires_at->format('Y-m-d\TH:i') : '') }}">
                        <div class="form-text small text-muted">Controls redemption window. Does not cancel active promos.</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Timezone</label>
                        <input type="text" name="timezone" class="form-control rounded-3" value="{{ old('timezone', $coupon->timezone ?? 'UTC') }}" required>
                    </div>
                </div>
            </div>

            {{-- Flags & Admin Notes --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 slide-up stagger-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-0 text-slate-800"><i class="bi bi-sliders text-primary me-2"></i>Campaign Settings</h6>
                </div>
                <div class="card-body p-4">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_hidden" id="isHidden" value="1" {{ old('is_hidden', $coupon->is_hidden ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold small" for="isHidden">Hidden coupon (exclusive link/manual entry)</label>
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">Admin Notes</label>
                        <textarea name="admin_notes" class="form-control rounded-3" rows="3" placeholder="Internal campaign objectives...">{{ old('admin_notes', $coupon->admin_notes ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Submit Actions --}}
            <div class="d-grid gap-2 slide-up stagger-5">
                <button type="submit" class="btn text-white fw-bold rounded-4 py-2.5 shadow-sm" style="background:var(--primary-gradient)">
                    <i class="bi bi-check-lg me-1"></i> {{ isset($coupon) ? 'Save Campaign Changes' : 'Create Promotion Campaign' }}
                </button>
                <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary rounded-4 py-2">Cancel</a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function() {
    const promoTypeSelect = document.getElementById('promotionTypeSelect');
    const hiddenPromoType = document.getElementById('hiddenPromotionType');
    const discountValDiv = document.getElementById('field-discount-value');
    const durationTypeDiv = document.getElementById('field-duration-type');
    const durationValDiv = document.getElementById('field-duration-value');
    const freePeriodDiv = document.getElementById('field-free-period');
    const extensionDiv = document.getElementById('field-extension-config');
    const freeUntilDiv = document.getElementById('field-free-until');
    const targetPlanDiv = document.getElementById('field-target-plan');
    const valueLabel = document.getElementById('valueLabel');
    const valueAddon = document.getElementById('valueAddon');
    const valueHint = document.getElementById('valueHint');
    const durationTypeSelect = document.getElementById('durationTypeSelect');

    function syncPromotionUI() {
        const type = promoTypeSelect.value;
        if (hiddenPromoType) hiddenPromoType.value = type;

        // Reset display
        discountValDiv.style.display = 'none';
        durationTypeDiv.style.display = 'none';
        durationValDiv.style.display = 'none';
        freePeriodDiv.style.display = 'none';
        extensionDiv.style.display = 'none';
        freeUntilDiv.style.display = 'none';
        targetPlanDiv.style.display = 'none';

        if (type === 'percentage_discount' || type === 'percentage') {
            discountValDiv.style.display = 'block';
            valueLabel.textContent = 'Discount Percentage';
            valueAddon.textContent = '%';
            valueHint.textContent = 'Percentage off the subscription price (e.g. 50%).';
            durationTypeDiv.style.display = 'block';
            syncDurationValueUI();
        } else if (type === 'fixed_discount' || type === 'fixed') {
            discountValDiv.style.display = 'block';
            valueLabel.textContent = 'Fixed Discount Amount';
            valueAddon.textContent = 'DH';
            valueHint.textContent = 'Fixed amount subtracted from the price (e.g. 100 DH). Final price will never be negative.';
            durationTypeDiv.style.display = 'block';
            syncDurationValueUI();
        } else if (type === 'free_period' || type === 'free_subscription') {
            freePeriodDiv.style.display = 'block';
        } else if (type === 'extended_subscription' || type === 'trial_extension') {
            extensionDiv.style.display = 'block';
        } else if (type === 'free_until_date') {
            freeUntilDiv.style.display = 'block';
        } else if (type === 'lifetime') {
            discountValDiv.style.display = 'block';
            valueLabel.textContent = 'Lifetime Discount %';
            valueAddon.textContent = '%';
            valueHint.textContent = 'Permanent discount applied to all renewals.';
        } else if (type === 'custom_price') {
            discountValDiv.style.display = 'block';
            valueLabel.textContent = 'Custom Fixed Price';
            valueAddon.textContent = 'DH';
            valueHint.textContent = 'Customer pays this exact fixed amount.';
        } else if (type === 'plan_upgrade') {
            targetPlanDiv.style.display = 'block';
        }
    }

    function syncDurationValueUI() {
        const durType = durationTypeSelect ? durationTypeSelect.value : 'one_time';
        const durValDiv = document.getElementById('field-duration-value');
        const durValLabel = document.getElementById('durationValueLabel');
        const durValHint = document.getElementById('durationValueHint');

        if (durType === 'number_of_billing_cycles') {
            durValDiv.style.display = 'block';
            durValLabel.textContent = 'Number of Billing Cycles';
            durValHint.textContent = 'Discounts this exact number of consecutive billing cycles (e.g. 3 cycles).';
        } else if (durType === 'number_of_months') {
            durValDiv.style.display = 'block';
            durValLabel.textContent = 'Number of Months';
            durValHint.textContent = 'Discounts subscriptions for this number of calendar months.';
        } else if (durType === 'number_of_days') {
            durValDiv.style.display = 'block';
            durValLabel.textContent = 'Number of Days';
            durValHint.textContent = 'Discounts subscriptions for this number of days.';
        } else if (durType === 'until_date') {
            durValDiv.style.display = 'none';
            freeUntilDiv.style.display = 'block';
        } else {
            durValDiv.style.display = 'none';
        }
    }

    promoTypeSelect.addEventListener('change', syncPromotionUI);
    if (durationTypeSelect) {
        durationTypeSelect.addEventListener('change', syncDurationValueUI);
    }

    syncPromotionUI();

    // Random code generator
    const generateBtn = document.getElementById('generateCodeBtn');
    const codeField = document.getElementById('couponCodeField');
    if (generateBtn) {
        generateBtn.addEventListener('click', () => {
            fetch('{{ route("admin.coupons.generate-code") }}')
                .then(r => r.json())
                .then(d => { if (d.code) codeField.value = d.code; });
        });
    }

    // Sync free period value to hidden inputs before submit
    const form = document.getElementById('couponForm');
    form.addEventListener('submit', function() {
        const type = promoTypeSelect.value;
        if (type === 'free_period') {
            const fpVal = document.getElementById('freePeriodVal').value;
            let hiddenDurVal = document.querySelector('input[name="duration_value"]');
            if (hiddenDurVal) hiddenDurVal.value = fpVal;
        }

        // Parse textarea arrays
        ['allowed_emails', 'allowed_domains', 'allowed_companies'].forEach(field => {
            const raw = document.querySelector(`[name="${field}_raw"]`);
            if (raw && raw.value.trim()) {
                const values = raw.value.trim().split('\n').map(v => v.trim()).filter(Boolean);
                values.forEach(v => {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = field + '[]';
                    hidden.value = v;
                    form.appendChild(hidden);
                });
            }
        });
    });
})();
</script>
@endpush
