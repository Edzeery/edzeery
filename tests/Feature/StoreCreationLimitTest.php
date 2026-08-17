<?php

use App\Domains\Plan\Services\FeatureUsageService;
use App\Models\billing\Subscription;
use App\Models\Plans\Plan;
use App\Models\Plans\PlanFeature;
use App\Models\Plans\PlanPrice;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;

use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(PlansSeeder::class);
});

function createMerchantWithPlan(string $planSlug, int $storeCount = 0): \App\Models\User
{
    $user = roleUser('merchant');

    // Delete the auto-created trial subscription from User::booted()
    Subscription::where('user_id', $user->id)->delete();

    $plan = Plan::where('slug', $planSlug)->first();
    $price = PlanPrice::where('plan_id', $plan->id)->where('billing_period', 'monthly')->first();

    $subscription = Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'plan_price_id' => $price?->id,
        'status' => 'active',
        'starts_at' => now(),
    ]);

    $featureService = app(FeatureUsageService::class);

    for ($i = 0; $i < $storeCount; $i++) {
        $store = Store::create([
            'user_id' => $user->id,
            'name' => "Store {$i}",
            'slug' => "store-{$user->id}-{$i}",
            'status' => 'active',
        ]);

        StoreMembership::create([
            'store_id' => $store->id,
            'user_id' => $user->id,
            'invited_by' => $user->id,
            'is_active' => true,
        ]);

        $featureService->consume($subscription, 'stores_max');
    }

    return $user;
}

test('merchant can create store under limit', function () {
    $user = createMerchantWithPlan('basic', 0);

    $this->actingAs($user);

    $subscription = $user->latestSubscription();
    $featureService = app(FeatureUsageService::class);

    expect($featureService->canUse($subscription, 'stores_max'))->toBeTrue();
});

test('merchant is blocked at store limit', function () {
    $user = createMerchantWithPlan('basic', 1);

    $this->actingAs($user);

    $subscription = $user->latestSubscription();
    $featureService = app(FeatureUsageService::class);

    expect($featureService->canUse($subscription, 'stores_max'))->toBeFalse();
});

test('unlimited custom plan bypasses limit', function () {
    $user = createMerchantWithPlan('enterprise', 10);

    $this->actingAs($user);

    $subscription = $user->latestSubscription();
    $featureService = app(FeatureUsageService::class);

    expect($featureService->canUse($subscription, 'stores_max'))->toBeTrue();
});

test('store deletion frees quota', function () {
    $user = createMerchantWithPlan('pro', 3);

    $subscription = $user->latestSubscription();
    $featureService = app(FeatureUsageService::class);

    expect($featureService->canUse($subscription, 'stores_max'))->toBeTrue();

    $store = $user->storesOwned()->first();
    $store->delete();

    $featureService->decrement($subscription, 'stores_max');

    expect($featureService->canUse($subscription, 'stores_max'))->toBeTrue();
});

test('stores_max feature values match plan max_stores column', function () {
    $plans = [
        'trial' => '1',
        'basic' => '1',
        'pro' => '5',
        'enterprise' => 'unlimited',
    ];

    foreach ($plans as $slug => $expectedValue) {
        $plan = Plan::where('slug', $slug)->first();
        expect($plan->getFeatureValue('stores_max'))->toBe($expectedValue);
    }
});

test('custom plan has is_custom flag', function () {
    $plan = Plan::create([
        'name' => 'Custom Test',
        'slug' => 'custom-test-' . uniqid(),
        'is_custom' => true,
        'is_active' => true,
    ]);

    expect($plan->is_custom)->toBeTrue();
    expect(Plan::public()->where('id', $plan->id)->exists())->toBeFalse();
});
