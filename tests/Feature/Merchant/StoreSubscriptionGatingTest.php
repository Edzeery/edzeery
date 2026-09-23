<?php

use App\Enums\billing\StatusSubscriptionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\billing\Subscription;
use App\Models\Plans\Plan;
use App\Models\Plans\PlanPrice;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Database\Seeders\SystemStatusesSeeder;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(SystemStatusesSeeder::class);
    $this->seed(PlansSeeder::class);
});

// ————— Fixtures —————

function ssegOwner(): array
{
    $owner = User::factory()->create();
    $owner->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $owner->id,
        'name'    => 'Subscription Gating Store',
        'slug'    => 'sub-gate-'.uniqid(),
        'status'  => 'active',
    ]);

    StoreMembership::create([
        'store_id'   => $store->id,
        'user_id'    => $owner->id,
        'invited_by' => $owner->id,
        'is_active'  => true,
        'role'       => StoreRoleEnum::OWNER->value,
    ]);

    return [$owner, $store];
}

function ssegStaff(Store $store): StoreMembership
{
    $staff = User::factory()->create();
    $staff->assignRole(Role::findOrCreate(StoreRoleEnum::STAFF->value, 'merchant'));

    return StoreMembership::create([
        'store_id'   => $store->id,
        'user_id'    => $staff->id,
        'invited_by' => $store->user_id,
        'is_active'  => true,
        'role'       => StoreRoleEnum::STAFF->value,
    ]);
}

function ssegKillSubscriptions(User $user): void
{
    Subscription::where('user_id', $user->id)->delete();
}

function ssegGrantExpiredSubscription(User $user, Plan $plan): void
{
    ssegKillSubscriptions($user);

    Subscription::create([
        'user_id'       => $user->id,
        'plan_id'       => $plan->id,
        'plan_price_id' => $plan->prices()->first()?->id,
        'status'        => StatusSubscriptionEnum::EXPIRED,
        'starts_at'     => now()->subMonth(),
        'ends_at'       => now()->subDay(),
    ]);
}

function ssegGrantActiveSubscription(User $user, Plan $plan): void
{
    ssegKillSubscriptions($user);

    Subscription::create([
        'user_id'       => $user->id,
        'plan_id'       => $plan->id,
        'plan_price_id' => $plan->prices()->first()?->id,
        'status'        => StatusSubscriptionEnum::ACTIVE,
        'starts_at'     => now(),
        'ends_at'       => now()->addMonth(),
    ]);
}

function ssegGrantPersonalTrial(User $user, Plan $plan): void
{
    ssegKillSubscriptions($user);

    Subscription::create([
        'user_id'       => $user->id,
        'plan_id'       => $plan->id,
        'plan_price_id' => $plan->prices()->first()?->id,
        'status'        => StatusSubscriptionEnum::ACTIVE,
        'is_trial'      => true,
        'starts_at'     => now()->subDay(),
        'ends_at'       => now()->addDays(13),
    ]);
}

function ssegTeamsUrl(Store $store): string
{
    return route('merchant.teams.index', ['store' => $store->slug]);
}

// ————— 1. The exact bug scenario —————
// Owner's subscription expired, but the staff member holds a valid personal trial.
// The middleware must gate on the STORE OWNER's subscription, not the staff member's.

it('redirects a staff member to account.billing when the store OWNER subscription is expired even though the staff member has a valid personal trial', function () {
    [$owner, $store] = ssegOwner();
    $plan = Plan::where('slug', 'trial')->first();
    $staff = ssegStaff($store);

    ssegGrantExpiredSubscription($owner, $plan);
    ssegGrantPersonalTrial($staff->user, $plan);

    actingAs($staff->user);

    $this->get(ssegTeamsUrl($store))
        ->assertRedirect(route('account.billing'));
});

// ————— 2. No over-blocking —————————
// Same store, but the owner's subscription is now active → staff must NOT be locked out.

it('does not redirect a staff member when the store owner subscription is active', function () {
    [$owner, $store] = ssegOwner();
    $plan = Plan::where('slug', 'trial')->first();
    $staff = ssegStaff($store);

    ssegGrantActiveSubscription($owner, $plan);
    ssegGrantPersonalTrial($staff->user, $plan);

    actingAs($staff->user);

    $this->get(ssegTeamsUrl($store))
        ->assertOk();
});

// ————— 3. Owner sanity ——————————————
// Owner with an active subscription can reach the page themselves.

it('does not redirect the store owner when their own subscription is active', function () {
    [$owner, $store] = ssegOwner();
    $plan = Plan::where('slug', 'trial')->first();

    ssegGrantActiveSubscription($owner, $plan);

    actingAs($owner);

    $this->get(ssegTeamsUrl($store))
        ->assertOk();
});
