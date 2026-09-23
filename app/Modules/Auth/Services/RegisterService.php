<?php

namespace App\Modules\Auth\Services;

use App\Models\User;
use App\Modules\Companies\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterService
{
    /**
     * Handle the registration of a new company and its owner.
     *
     * @param array $data
     * @return array
     */
    public function registerCompany(array $data)
    {
        return DB::transaction(function () use ($data) {
            // 0. Find Plan
            $plan = null;
            if (!empty($data['plan'])) {
                $plan = \App\Modules\Subscriptions\Models\Plan::where('slug', $data['plan'])->first();
            }

            // 1. Create the Company (Tenant)
            // We set plan_id to null initially so they are forced to wait for payment approval
            $company = Company::create([
                'name' => $data['company_name'],
                'slug' => Str::slug($data['company_name']),
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'timezone' => $data['timezone'] ?? 'UTC',
                'plan_id' => null, 
            ]);

            // 2. Create the Owner User
            $user = User::create([
                'company_id' => $company->id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
            ]);

            // 3. Assign Role (Custom RBAC)
            $user->assignRole('company-admin');

            // 4. Generate Token
            $token = $user->createToken('auth_token')->plainTextToken;

            // 5. Trigger Subscription creation if a plan was selected
            if ($plan) {
                app(\App\Modules\Payments\Services\PaymentLifecycleService::class)
                    ->createSubscriptionRequest($company, $plan, 'monthly', 'manual');
            }

            return [
                'user' => $user,
                'company' => $company,
                'token' => $token,
            ];
        });
    }
}
