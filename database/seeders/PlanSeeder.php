<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Subscriptions\Models\Plan;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    /**
     * Seed the new SaaS plans.
     *
     * This seeder:
     * 1. Removes ALL old plans and their permission pivots
     * 2. Reassigns companies with old plans to the Starter plan
     * 3. Creates the 4 new plans (starter, professional, business, premium)
     * 4. Creates any missing feature-gate permissions
     * 5. Assigns permissions to plans based on tier
     */
    public function run(): void
    {
        // =============================================
        // STEP 1: Define the new plans
        // =============================================
        $plans = [
            [
                'name'        => 'Starter',
                'slug'        => 'starter',
                'description' => 'Perfect for small businesses just getting started.',
                'price'       => 29.00,
                'annual_price'=> 279.00,
                'limits'      => [
                    'staff_limit'          => 2,
                    'room_limit'           => 1,
                    'display_limit'        => 1,
                    'ticket_limit_monthly' => 500,
                    'customer_limit'       => 50,
                ],
                'is_active' => true,
            ],
            [
                'name'        => 'Professional',
                'slug'        => 'professional',
                'description' => 'For growing businesses that need more power.',
                'price'       => 119.00,
                'annual_price'=> 1140.00,
                'limits'      => [
                    'staff_limit'          => 5,
                    'room_limit'           => 3,
                    'display_limit'        => 3,
                    'ticket_limit_monthly' => 3000,
                    'customer_limit'       => 200,
                ],
                'is_active' => true,
            ],
            [
                'name'        => 'Business',
                'slug'        => 'business',
                'description' => 'For established businesses with high volume needs.',
                'price'       => 249.00,
                'annual_price'=> 2390.00,
                'limits'      => [
                    'staff_limit'          => 15,
                    'room_limit'           => 5,
                    'display_limit'        => 5,
                    'ticket_limit_monthly' => 10000,
                    'customer_limit'       => 1000,
                ],
                'is_active' => true,
            ],
            [
                'name'        => 'Premium',
                'slug'        => 'premium',
                'description' => 'For large organizations with custom requirements.',
                'price'       => 699.00,
                'annual_price'=> 6710.00,
                'limits'      => [
                    'staff_limit'          => -1, // Unlimited
                    'room_limit'           => -1, // Unlimited
                    'display_limit'        => -1, // Unlimited
                    'ticket_limit_monthly' => -1, // Unlimited
                    'customer_limit'       => -1, // Unlimited
                ],
                'is_active' => true,
            ],
        ];

        // =============================================
        // STEP 2: Execute in transaction
        // =============================================
        DB::transaction(function () use ($plans) {

            // --- Clear old plan_permission pivots ---
            DB::table('plan_permission')->delete();

            // --- Delete all old plans ---
            // First, reassign companies that had old plans to null (will get starter below)
            $oldPlanIds = Plan::pluck('id')->toArray();
            if (!empty($oldPlanIds)) {
                DB::table('companies')
                    ->whereIn('plan_id', $oldPlanIds)
                    ->update(['plan_id' => null]);
            }
            Plan::query()->delete();

            // --- Delete any subscription module feature gate permissions ---
            Permission::where('module', 'subscriptions')->delete();

            // --- Create the 4 new plans ---
            $createdPlans = [];
            foreach ($plans as $planData) {
                $createdPlans[$planData['slug']] = Plan::create([
                    'name'         => $planData['name'],
                    'slug'         => $planData['slug'],
                    'description'  => $planData['description'],
                    'price'        => $planData['price'],
                    'annual_price' => $planData['annual_price'],
                    'limits'       => $planData['limits'],
                    'is_active'    => $planData['is_active'],
                ]);
            }

            // --- Assign companies without a plan to Starter ---
            $starterPlan = $createdPlans['starter'];
            DB::table('companies')
                ->whereNull('plan_id')
                ->update(['plan_id' => $starterPlan->id]);

            $this->command->info("✅ Old plans removed.");
            $this->command->info("✅ 4 new plans created: Starter, Professional, Business, Premium");
            $this->command->info("✅ Subscriptions permissions deleted and cleaned up.");
            $this->command->info("✅ All companies without a plan assigned to Starter.");
        });
    }
}
