<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create coupons table
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type'); // percentage, fixed, free_subscription, trial_extension, custom_price, plan_upgrade
            $table->decimal('value', 10, 2)->nullable();
            $table->unsignedBigInteger('target_plan_id')->nullable(); // For plan upgrades or custom price plans
            $table->string('status')->default('active'); // active, disabled, archived
            
            // Validity Dates
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('timezone')->default('UTC');
            
            // Limits
            $table->integer('max_total_uses')->nullable();
            $table->integer('max_uses_per_tenant')->nullable();
            $table->integer('max_uses_per_user')->nullable();
            $table->integer('current_uses')->default(0);
            $table->boolean('is_unlimited')->default(false);
            
            // Financial Rules
            $table->decimal('min_purchase_amount', 10, 2)->nullable();
            $table->decimal('max_discount_amount', 10, 2)->nullable();
            
            // Constraints
            $table->string('billing_cycle')->nullable(); // monthly, annual, both
            $table->string('country', 3)->nullable();
            $table->string('currency', 3)->nullable();
            $table->string('customer_type')->default('both'); // both, new_customers, existing_customers
            $table->string('payment_type')->default('both'); // both, first_payment, renewals
            $table->boolean('is_stackable')->default(false);
            $table->boolean('is_hidden')->default(false);
            $table->string('coupon_category')->default('general'); // general, referral, affiliate, email_campaign, seasonal, etc.
            
            // Whitelist lists
            $table->json('allowed_emails')->nullable();
            $table->json('allowed_domains')->nullable();
            $table->json('allowed_companies')->nullable(); // company name match
            
            // Restrictions Type for Tenants pivot
            $table->string('tenant_restriction_type')->default('none'); // none, whitelist, blacklist
            
            $table->unsignedBigInteger('created_by')->nullable();
            $table->text('admin_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexing for search
            $table->index('code');
            $table->index('status');
        });

        // 2. Pivot: coupon_plan
        Schema::create('coupon_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // 3. Pivot: coupon_tenant
        Schema::create('coupon_tenant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // 4. Pivot: coupon_redemptions (Active discount link on company/subscription level)
        Schema::create('coupon_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('active'); // active, completed, cancelled
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('redeemed_at')->useCurrent();
            $table->timestamps();
        });

        // 5. Audit: coupon_usages (Tracks every charge/invoice application)
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        // 6. Audit: coupon_history (Validation attempts log)
        Schema::create('coupon_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action'); // validate, apply, reject
            $table->string('status'); // success, failed
            $table->text('failure_reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 7. Audit: coupon_logs (Admin activity edit logging)
        Schema::create('coupon_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action'); // created, updated, deleted, enabled, disabled, archived
            $table->json('changes')->nullable(); // {before: {...}, after: {...}}
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 8. Modify existing tables
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->after('is_active');
            }
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('subscriptions', 'coupon_id')) {
                $table->foreignId('coupon_id')->nullable()->after('plan_id')->constrained()->nullOnDelete();
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'coupon_id')) {
                $table->foreignId('coupon_id')->nullable()->after('subscription_id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('payments', 'discount')) {
                $table->decimal('discount', 10, 2)->default(0)->after('amount');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'coupon_id')) {
                $table->foreignId('coupon_id')->nullable()->after('payment_id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('invoices', 'discount')) {
                $table->decimal('discount', 10, 2)->default(0)->after('subtotal');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove columns
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('coupon_id');
            $table->dropColumn('discount');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('coupon_id');
            $table->dropColumn('discount');
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('coupon_id');
        });
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('trial_ends_at');
        });

        Schema::dropIfExists('coupon_logs');
        Schema::dropIfExists('coupon_history');
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupon_redemptions');
        Schema::dropIfExists('coupon_tenant');
        Schema::dropIfExists('coupon_plan');
        Schema::dropIfExists('coupons');
    }
};
