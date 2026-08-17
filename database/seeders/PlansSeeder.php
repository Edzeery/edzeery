<?php

namespace Database\Seeders;

use App\Domains\Billing\Services\BillingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Plans\Plan;
use App\Models\Plans\PlanFeature;
use App\Models\Plans\PlanPrice;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Enums\Platform\UserRoleEnum;
use App\Enums\Store\StoreRoleEnum;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('Seeding Plan Features...');

        // 1️⃣ Plan Features
        $features = [
            ['slug' => 'products_limit', 'name' => 'plans.nbr_products', 'type' => 'number', 'unit' => 'products'],
            ['slug' => 'staff_limit', 'name' =>  'plans.nbr_staff_limit', 'type' => 'number', 'unit' => 'users'],
            ['slug' => 'daily_orders_limit', 'name' => 'plans.nbr_daily_orders', 'type' => 'number', 'unit' => 'orders'],
            ['slug' => 'delivery_agents_limit', 'name' => 'plans.nbr_delivery_agents', 'type' => 'number', 'unit' => 'agents'],
            ['slug' => 'delivery_companies_limit', 'name' => 'plans.nbr_delivery_companies', 'type' => 'number', 'unit' => 'companies'],
            ['slug' => 'store_orders_limit', 'name' => 'plans.nbr_store_orders', 'type' => 'number', 'unit' => 'orders'],
            ['slug' => 'stores_max', 'name' => 'plans.max_stores', 'type' => 'number', 'unit' => 'stores', 'consumable' => true, 'quota' => true],
            ['slug' => 'analytics', 'name' => 'plans.advanced_analytics', 'type' => 'boolean', 'unit' => null],
            ['slug' => 'priority_support', 'name' => 'plans.priority_support', 'type' => 'boolean', 'unit' => null],
            ['slug' => 'integrations', 'name' => 'plans.future_integrations', 'type' => 'boolean', 'unit' => null],
        ];

        foreach ($features as $feature) {
            PlanFeature::updateOrCreate(['slug' => $feature['slug']], $feature);
        }

        $this->command?->info('Seeding Plans and Prices...');

        // 2️⃣ Plans Data
        $plansData = [
            'trial' => [
                'name' => 'Trial',
                'trial_days' => 14,
                'max_stores' => 1,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'features' => [
                    'products_limit' => 50,
                    'staff_limit' => 1,
                    'daily_orders_limit' => 20,
                    'delivery_agents_limit' => 1,
                    'delivery_companies_limit' => 1,
                    'store_orders_limit' => 50,
                    'stores_max' => 1,
                    'analytics' => false,
                    'priority_support' => false,
                    'integrations' => false,
                ],
            ],
            'basic' => [
                'name' => 'Basic',
                'trial_days' => 0,
                'max_stores' => 1,
                'price_monthly' => 3000,
                'price_yearly' => 32400,
                'features' => [
                    'products_limit' => 500,
                    'staff_limit' => 3,
                    'daily_orders_limit' => 100,
                    'delivery_agents_limit' => 5,
                    'delivery_companies_limit' => 2,
                    'store_orders_limit' => 500,
                    'stores_max' => 1,
                    'analytics' => true,
                    'priority_support' => false,
                    'integrations' => false,
                ],
            ],
            'pro' => [
                'name' => 'Pro',
                'trial_days' => 0,
                'max_stores' => 5,
                'price_monthly' => 7000,
                'price_yearly' => 75600,
                'features' => [
                    'products_limit' => 2000,
                    'staff_limit' => 10,
                    'daily_orders_limit' => 500,
                    'delivery_agents_limit' => 20,
                    'delivery_companies_limit' => 5,
                    'store_orders_limit' => 2000,
                    'stores_max' => 5,
                    'analytics' => true,
                    'priority_support' => true,
                    'integrations' => true,
                ],
            ],
            'enterprise' => [
                'name' => 'Enterprise',
                'trial_days' => 0,
                'max_stores' => 50,
                'price_monthly' => 15000,
                'price_yearly' => 162000,
                'features' => [
                    'products_limit' => 'unlimited',
                    'staff_limit' => 'unlimited',
                    'daily_orders_limit' => 'unlimited',
                    'delivery_agents_limit' => 'unlimited',
                    'delivery_companies_limit' => 'unlimited',
                    'store_orders_limit' => 'unlimited',
                    'stores_max' => 'unlimited',
                    'analytics' => true,
                    'priority_support' => true,
                    'integrations' => true,
                ],
            ],
        ];

        foreach ($plansData as $slug => $data) {

            // Plan
            $plan = Plan::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'trial_days' => $data['trial_days'],
                    'max_stores' => $data['max_stores'],
                    'upgrade_to_plan_id' => null,
                    'is_active' => true,
                    'is_default' => $slug === 'trial',
                ]
            );

            // Plan Prices
            PlanPrice::updateOrCreate(
                ['plan_id' => $plan->id, 'billing_period' => 'monthly'],
                ['price' => $data['price_monthly'], 'duration' => 30, 'currency' => 'DZD']
            );

            PlanPrice::updateOrCreate(
                ['plan_id' => $plan->id, 'billing_period' => 'yearly'],
                ['price' => $data['price_yearly'], 'duration' => 365, 'currency' => 'DZD']
            );

            // Features
            foreach ($data['features'] as $featureSlug => $value) {
                $feature = PlanFeature::where('slug', $featureSlug)->first();
                if ($feature) {
                    $plan->features()->syncWithoutDetaching([
                        $feature->id => ['value' => $value]
                    ]);
                }
            }

            $this->command?->info("Plan {$plan->name} seeded successfully.");
        }

        // ✅ Example: User, Store, Subscription & Payment
        $user = User::firstOrCreate(
            ['email' => 'seedermerchant@edzeery.com'],
            [
                'name' => 'Seeder Merchant',
                'password' => Hash::make('password'),
            ]
        );

        if (!$user->hasRole(UserRoleEnum::MERCHANT)) {
            $user->assignRole(UserRoleEnum::MERCHANT);
        }

        $store = Store::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Demo Store', 'slug' => 'demo-store']
        );

        $Membership =    StoreMembership::create([
            'store_id' => $store->id,
            'user_id'  => $store->user_id,
            'invited_by' => $store->user_id,
            'is_active' => true,
        ]);


        if ($Membership && !$user->merchant()->hasRole(StoreRoleEnum::OWNER)) {
            $user->merchant()->assignRole(StoreRoleEnum::OWNER);
        }


        // Trial Subscription
        $trialPlan = Plan::where('slug', 'trial')->first();
        $trialPrice = $trialPlan->prices()->where('billing_period', 'monthly')->first();

        $billing = app(BillingService::class);
        $billing->subscribeUser(
            user: $user,
            plan: $plan,
            price: $trialPrice,
            trial: false
        );


        // $subscription = Subscription::create([
        //     'user_id' => $user->id,
        //     'store_id' => $store->id,
        //     'plan_id' => $trialPlan->id,
        //     'plan_price_id' => $trialPrice->id,
        //     'starts_at' => now(),
        //     'ends_at' => now()->addDays($trialPlan->trial_days),
        //     'trial_ends_at' => now()->addDays($trialPlan->trial_days),
        //     'is_trial' => true,
        // ]);

        // Payment::create([
        //     'user_id' => $user->id,
        //     'store_id' => $store->id,
        //     'subscription_id' => $subscription->id,
        //     'plan_id' => $trialPlan->id,
        //     'plan_price_id' => $trialPrice->id,
        //     'gateway' => 'chargily',
        //     'transaction_id' => 'TRX_DEMO_001',
        //     'status' => 'paid',
        //     'amount' => $trialPrice->price,
        //     'currency' => $trialPrice->currency,
        //     'paid_at' => now(),
        //     'meta' => ['demo' => true],
        // ]);

        $this->command?->info('Plans, Features, Prices, Store, Subscription and Payment seeded successfully!');
    }
}
