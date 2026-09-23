<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Companies\Models\Company;
use App\Models\User;
use App\Modules\Subscriptions\Models\Plan;
use App\Modules\Coupons\Models\Coupon;
use App\Modules\Coupons\Models\CouponRedemption;
use App\Modules\Coupons\Models\CouponUsage;
use App\Modules\Coupons\Enums\CouponType;
use App\Modules\Coupons\Enums\CouponStatus;
use App\Modules\Coupons\Enums\PromotionDuration;
use App\Modules\Coupons\Services\CouponService;
use App\Modules\Payments\Services\PaymentLifecycleService;
use App\Modules\Payments\Models\Subscription;
use App\Modules\Payments\Models\Payment;
use Carbon\Carbon;

class CouponSystemTest extends TestCase
{
    use RefreshDatabase;

    protected $company;
    protected $adminUser;
    protected $plan;
    protected $couponService;
    protected $lifecycleService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create standard plan
        $this->plan = Plan::create([
            'name' => 'Business Plan',
            'slug' => 'business',
            'price' => 499.00,
            'annual_price' => 4990.00,
            'is_active' => true,
        ]);

        // Create company
        $this->company = Company::create([
            'name' => 'Acme Corp',
            'slug' => 'acme',
            'plan_id' => $this->plan->id,
            'billing_cycle' => 'monthly',
            'trial_ends_at' => now()->addDays(14),
        ]);

        // Create user
        $this->adminUser = User::create([
            'name' => 'Admin User',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@acme.com',
            'password' => bcrypt('password'),
            'company_id' => $this->company->id,
            'is_system_admin' => false,
        ]);

        $this->couponService = app(CouponService::class);
        $this->lifecycleService = app(PaymentLifecycleService::class);
    }

    /** @test */
    public function it_validates_percentage_coupon_correctly()
    {
        $coupon = Coupon::create([
            'code' => 'PCT50',
            'name' => '50% Off Promo',
            'type' => CouponType::PERCENTAGE_DISCOUNT,
            'value' => 50.00,
            'status' => CouponStatus::ACTIVE,
            'is_unlimited' => true,
        ]);

        $result = $this->couponService->validateCoupon('PCT50', $this->company, $this->plan, 'monthly', 499.00);
        $this->assertTrue($result['valid']);

        $discount = $this->couponService->calculateDiscount('PCT50', 499.00, $this->company, $this->plan, 'monthly');
        $this->assertEquals(249.50, $discount);
    }

    /** @test */
    public function it_rejects_expired_coupons()
    {
        $coupon = Coupon::create([
            'code' => 'EXPIRED',
            'name' => 'Expired Promo',
            'type' => CouponType::PERCENTAGE_DISCOUNT,
            'value' => 10.00,
            'status' => CouponStatus::ACTIVE,
            'is_unlimited' => true,
            'expires_at' => now()->subDay(),
        ]);

        $result = $this->couponService->validateCoupon('EXPIRED', $this->company, $this->plan, 'monthly', 499.00);

        $this->assertFalse($result['valid']);
        $this->assertEquals('Coupon has expired.', $result['message']);
    }

    /** @test */
    public function it_rejects_disabled_coupons()
    {
        $coupon = Coupon::create([
            'code' => 'DISABLED',
            'name' => 'Disabled Promo',
            'type' => CouponType::PERCENTAGE_DISCOUNT,
            'value' => 10.00,
            'status' => CouponStatus::DISABLED,
            'is_unlimited' => true,
        ]);

        $result = $this->couponService->validateCoupon('DISABLED', $this->company, $this->plan, 'monthly', 499.00);

        $this->assertFalse($result['valid']);
        $this->assertEquals('Coupon is not active.', $result['message']);
    }

    /** @test */
    public function it_enforces_whitelist_restriction()
    {
        $coupon = Coupon::create([
            'code' => 'EXCLUSIVE',
            'name' => 'Exclusive Promo',
            'type' => CouponType::PERCENTAGE_DISCOUNT,
            'value' => 10.00,
            'status' => CouponStatus::ACTIVE,
            'is_unlimited' => true,
            'tenant_restriction_type' => 'whitelist',
        ]);

        $result = $this->couponService->validateCoupon('EXCLUSIVE', $this->company, $this->plan, 'monthly', 499.00);
        $this->assertFalse($result['valid']);

        $coupon->companies()->attach($this->company->id);

        $result2 = $this->couponService->validateCoupon('EXCLUSIVE', $this->company, $this->plan, 'monthly', 499.00);
        $this->assertTrue($result2['valid']);
    }

    /** @test */
    public function it_calculates_fixed_discount_correctly_and_never_negative()
    {
        $coupon = Coupon::create([
            'code' => 'FIXED100',
            'name' => '100 DH Off',
            'type' => CouponType::FIXED_DISCOUNT,
            'value' => 100.00,
            'status' => CouponStatus::ACTIVE,
            'is_unlimited' => true,
        ]);

        $discount = $this->couponService->calculateDiscount('FIXED100', 499.00, $this->company, $this->plan, 'monthly');
        $this->assertEquals(100.00, $discount);

        $couponBig = Coupon::create([
            'code' => 'FIXED1000',
            'name' => '1000 DH Off',
            'type' => CouponType::FIXED_DISCOUNT,
            'value' => 1000.00,
            'status' => CouponStatus::ACTIVE,
            'is_unlimited' => true,
        ]);

        $discountBig = $this->couponService->calculateDiscount('FIXED1000', 499.00, $this->company, $this->plan, 'monthly');
        $this->assertEquals(499.00, $discountBig);
        $this->assertEquals(0.00, 499.00 - $discountBig);
    }

    /** @test */
    public function it_handles_test3_100_percent_free_for_3_months()
    {
        $coupon = Coupon::create([
            'code' => 'TEST3',
            'name' => '100% Free for 3 months',
            'type' => CouponType::FREE_PERIOD,
            'promotion_type' => 'free_period',
            'duration_type' => PromotionDuration::NUMBER_OF_MONTHS->value,
            'duration_value' => 3,
            'duration_unit' => 'months',
            'value' => 3,
            'status' => CouponStatus::ACTIVE,
            'is_unlimited' => true,
        ]);

        $validation = $this->couponService->validateCoupon('TEST3', $this->company, $this->plan, 'monthly', 499.00);
        $this->assertTrue($validation['valid']);
        $this->assertTrue($validation['preview']['is_free_period']);

        $discount = $this->couponService->calculateDiscount('TEST3', 499.00, $this->company, $this->plan, 'monthly');
        $this->assertEquals(499.00, $discount);

        // Checkout & auto-activation
        $result = $this->lifecycleService->createSubscriptionRequest(
            $this->company,
            $this->plan,
            'monthly',
            'virement',
            null,
            'TEST3'
        );

        $this->assertTrue($result['auto_activated']);
        $this->assertEquals('active', $result['subscription']->status);
        $this->assertEquals(0.00, $result['payment']->amount);
        $this->assertEquals('paid', $result['payment']->status);

        // Check 3 months ends_at calculation
        $expectedEnd = Carbon::now()->addMonths(3);
        $this->assertEquals($expectedEnd->format('Y-m-d'), $result['subscription']->ends_at->format('Y-m-d'));
    }

    /** @test */
    public function it_handles_startup50_50_percent_off_for_3_billing_cycles()
    {
        $coupon = Coupon::create([
            'code' => 'STARTUP50',
            'name' => '50% Off 3 Cycles',
            'type' => CouponType::PERCENTAGE_DISCOUNT,
            'promotion_type' => 'percentage_discount',
            'value' => 50.00,
            'duration_type' => PromotionDuration::NUMBER_OF_BILLING_CYCLES->value,
            'duration_value' => 3,
            'status' => CouponStatus::ACTIVE,
            'is_unlimited' => true,
        ]);

        $validation = $this->couponService->validateCoupon('STARTUP50', $this->company, $this->plan, 'monthly', 499.00);
        $this->assertTrue($validation['valid']);
        $this->assertEquals('STARTUP50', $validation['preview']['code']);

        $discount = $this->couponService->calculateDiscount('STARTUP50', 499.00, $this->company, $this->plan, 'monthly');
        $this->assertEquals(249.50, $discount);

        $result = $this->lifecycleService->createSubscriptionRequest(
            $this->company,
            $this->plan,
            'monthly',
            'virement',
            'receipts/test.png',
            'STARTUP50'
        );

        $this->assertEquals(249.50, $result['payment']->amount);
        $this->assertEquals(249.50, $result['payment']->discount);

        // Check snapshot on CouponRedemption
        $redemption = CouponRedemption::where('coupon_id', $coupon->id)->where('company_id', $this->company->id)->first();
        $this->assertNotNull($redemption);
        $this->assertEquals('percentage_discount', $redemption->promotion_type);
        $this->assertEquals(3, $redemption->max_discounted_cycles);
        $this->assertEquals(1, $redemption->billing_cycles_discounted);
        $this->assertTrue($redemption->isPromotionActive());
    }

    /** @test */
    public function it_handles_summer50_one_time_billing_cycle()
    {
        $coupon = Coupon::create([
            'code' => 'SUMMER50',
            'name' => 'Summer 50% One Time',
            'type' => CouponType::PERCENTAGE_DISCOUNT,
            'promotion_type' => 'percentage_discount',
            'value' => 50.00,
            'duration_type' => PromotionDuration::ONE_TIME->value,
            'status' => CouponStatus::ACTIVE,
            'is_unlimited' => true,
        ]);

        $discount = $this->couponService->calculateDiscount('SUMMER50', 499.00, $this->company, $this->plan, 'monthly');
        $this->assertEquals(249.50, $discount);
    }

    /** @test */
    public function it_handles_extra3_subscription_extension_without_unnecessary_invoice()
    {
        // First create active subscription for company
        $sub = Subscription::create([
            'company_id' => $this->company->id,
            'plan_id' => $this->plan->id,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'starts_at' => Carbon::now(),
            'ends_at' => Carbon::now()->addMonth(),
        ]);

        $initialEndsAt = $sub->ends_at->copy();

        $coupon = Coupon::create([
            'code' => 'EXTRA3',
            'name' => '+3 Months Extension',
            'type' => CouponType::EXTENDED_SUBSCRIPTION,
            'promotion_type' => 'extended_subscription',
            'extension_value' => 3,
            'extension_unit' => 'months',
            'value' => 3,
            'status' => CouponStatus::ACTIVE,
            'is_unlimited' => true,
        ]);

        $validation = $this->couponService->validateCoupon('EXTRA3', $this->company, $this->plan, 'monthly', 499.00);
        $this->assertTrue($validation['valid']);
        $this->assertTrue($validation['preview']['is_extension']);

        $result = $this->lifecycleService->createSubscriptionRequest(
            $this->company,
            $this->plan,
            'monthly',
            'virement',
            null,
            'EXTRA3'
        );

        $this->assertTrue($result['trial_extended']);
        
        $sub->refresh();
        $expectedNewEndsAt = $initialEndsAt->addMonths(3);
        $this->assertEquals($expectedNewEndsAt->format('Y-m-d'), $sub->ends_at->format('Y-m-d'));
    }

    /** @test */
    public function it_handles_free30_days()
    {
        $coupon = Coupon::create([
            'code' => 'FREE30',
            'name' => 'Free 30 Days',
            'type' => CouponType::FREE_PERIOD,
            'promotion_type' => 'free_period',
            'duration_type' => PromotionDuration::NUMBER_OF_DAYS->value,
            'duration_value' => 30,
            'duration_unit' => 'days',
            'value' => 30,
            'status' => CouponStatus::ACTIVE,
            'is_unlimited' => true,
        ]);

        $discount = $this->couponService->calculateDiscount('FREE30', 499.00, $this->company, $this->plan, 'monthly');
        $this->assertEquals(499.00, $discount);

        $result = $this->lifecycleService->createSubscriptionRequest(
            $this->company,
            $this->plan,
            'monthly',
            'virement',
            null,
            'FREE30'
        );

        $this->assertTrue($result['auto_activated']);
        $expectedEnd = Carbon::now()->addDays(30);
        $this->assertEquals($expectedEnd->format('Y-m-d'), $result['subscription']->ends_at->format('Y-m-d'));
    }

    /** @test */
    public function it_handles_launch_free_until_date()
    {
        $targetDate = Carbon::now()->addMonths(2)->endOfMonth();

        $coupon = Coupon::create([
            'code' => 'LAUNCH',
            'name' => 'Free Until Launch',
            'type' => CouponType::FREE_UNTIL_DATE,
            'promotion_type' => 'free_until_date',
            'free_until_date' => $targetDate,
            'status' => CouponStatus::ACTIVE,
            'is_unlimited' => true,
        ]);

        $discount = $this->couponService->calculateDiscount('LAUNCH', 499.00, $this->company, $this->plan, 'monthly');
        $this->assertEquals(499.00, $discount);

        $result = $this->lifecycleService->createSubscriptionRequest(
            $this->company,
            $this->plan,
            'monthly',
            'virement',
            null,
            'LAUNCH'
        );

        $this->assertTrue($result['auto_activated']);
        $this->assertEquals($targetDate->format('Y-m-d'), $result['subscription']->ends_at->format('Y-m-d'));
    }

    /** @test */
    public function it_enforces_customer_eligibility_new_vs_existing()
    {
        $couponNewOnly = Coupon::create([
            'code' => 'NEWONLY',
            'name' => 'New Customers Only',
            'type' => CouponType::PERCENTAGE_DISCOUNT,
            'value' => 20.00,
            'customer_type' => 'new_customers',
            'status' => CouponStatus::ACTIVE,
            'is_unlimited' => true,
        ]);

        // Brand new company -> valid
        $res1 = $this->couponService->validateCoupon('NEWONLY', $this->company, $this->plan, 'monthly', 499.00);
        $this->assertTrue($res1['valid']);

        // Record a paid payment for company
        Payment::create([
            'company_id' => $this->company->id,
            'amount' => 499.00,
            'status' => 'paid',
            'currency' => 'USD',
            'payment_method' => 'virement',
        ]);

        // Existing company -> rejected
        $res2 = $this->couponService->validateCoupon('NEWONLY', $this->company, $this->plan, 'monthly', 499.00);
        $this->assertFalse($res2['valid']);
        $this->assertEquals('Coupon is only valid for new customers.', $res2['message']);
    }

    /** @test */
    public function it_preserves_snapshot_integrity_when_admin_edits_master_coupon()
    {
        $coupon = Coupon::create([
            'code' => 'STARTUP50',
            'name' => '50% Off for 3 months',
            'type' => CouponType::PERCENTAGE_DISCOUNT,
            'promotion_type' => 'percentage_discount',
            'value' => 50.00,
            'duration_type' => PromotionDuration::NUMBER_OF_MONTHS->value,
            'duration_value' => 3,
            'status' => CouponStatus::ACTIVE,
            'is_unlimited' => true,
        ]);

        // Redeem coupon
        $this->lifecycleService->createSubscriptionRequest(
            $this->company,
            $this->plan,
            'monthly',
            'virement',
            'receipts/test.png',
            'STARTUP50'
        );

        $redemption = CouponRedemption::where('coupon_id', $coupon->id)->first();
        $this->assertEquals(50.00, $redemption->discount_value);

        // Later Admin modifies master coupon to 20%
        $coupon->update([
            'value' => 20.00,
            'status' => CouponStatus::DISABLED,
        ]);

        // Existing customer's snapshot redemption remains 50.00%
        $redemption->refresh();
        $this->assertEquals(50.00, $redemption->discount_value);
        $this->assertEquals('active', $redemption->status);
    }
}
