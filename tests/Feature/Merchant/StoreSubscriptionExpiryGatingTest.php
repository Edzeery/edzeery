<?php

use App\Enums\Store\StoreRoleEnum;
use App\Models\billing\Subscription;
use App\Models\Plans\Plan;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Support\StoreRoles;
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

// ————— Fixtures (prefix: segg, unique across the suite) —————

function seggGrantKillSubscriptions(User $user): void
{
    Subscription::where('user_id', $user->id)->delete();
}

function seggOwner(): array
{
    $owner = User::factory()->create();
    $owner->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $owner->id,
        'name'    => 'Subscription Gating Store',
        'slug'    => 'sub-gating-'.uniqid(),
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

function seggStaff(Store $store): StoreMembership
{
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::STAFF->value, 'merchant'));

    $membership = StoreMembership::create([
        'store_id'   => $store->id,
        'user_id'    => $user->id,
        'invited_by' => $store->user_id,
        'is_active'  => true,
        'role'       => StoreRoleEnum::STAFF->value,
    ]);

    $membership->syncPermissions(StoreRoles::permissions(StoreRoleEnum::STAFF));

    return $membership;
}

function seggTrialPlan(): Plan
{
    return Plan::where('slug', 'trial')->first() ?? Plan::first();
}

function seggGrantExpiredSubscription(User $user, Plan $plan): void
{
    seggGrantKillSubscriptions($user);

    Subscription::create([
        'user_id'       => $user->id,
        'plan_id'       => $plan->id,
        'plan_price_id' => $plan->prices()->first()?->id,
        'status'        => 'expired',
        'is_trial'      => false,
        'starts_at'     => now()->subMonth(),
        'ends_at'       => now()->subDay(),
    ]);
}

function seggGrantActiveSubscription(User $user, Plan $plan): void
{
    seggGrantKillSubscriptions($user);

    Subscription::create([
        'user_id'       => $user->id,
        'plan_id'       => $plan->id,
        'plan_price_id' => $plan->prices()->first()?->id,
        'status'        => 'active',
        'is_trial'      => false,
        'starts_at'     => now(),
        'ends_at'       => now()->addMonth(),
    ]);
}

function seggTeamsUrl(Store $store): string
{
    return route('merchant.teams.index', ['store' => $store->slug]);
}

// ————— 1. The exact bug scenario —————
// The store OWNER subscription is expired, but the staff member holds their own valid
// personal trial (every fresh user gets one via User::booted on created).
// The middleware must gate on the OWNER subscription — staff gets redirected.

it('redirects a staff member to account.billing when the store owner subscription is expired, even when the staff member holds their own valid personal trial', function () {
    [$owner, $store] = seggOwner();
    $plan = seggTrialPlan();
    $staffMembership = seggStaff($store);

    seggGrantExpiredSubscription($owner, $planPAY);
    // Staff keeps their auto-created personal trial (still in its trial window), so the
    // pre-fix `user()?->latestSubscription()` check would have let them straight through.

    actingAs($staffMembership->user);

    $this->get(seggTeamsUrl($store))
        ->assertRedirect(route('account.billing'));
});

// ————— 2. No over-blocking for staff —————
// Same store, same staff member, but the OWNER subscription is active → staff must not
// be redirected (guards against the fix over-blocking everyone.

it('does not redirect a staff member when the store owner subscription is active, even though the staff member is still on their own personal trial', function () {
    [$owner, $store] = seggOwner();
    $plan = seggTrialPlan();
    $staffMembership = seggStaff($store);

    seggGrantActiveSubscription($owner, $plan);
    actingAs($staffMembership->user);

    $this->get(seggTeamsUrl($store))
        ->assertOk();
});

// ————— 3. Owner-as-owner sanity —————
// When the OWNER themselves holds an active (non-trial) subscription they are not
// redirected to account.billing from their own store panel.

it('does not redirect the store owner themself when their own subscription is active', function () {
    [$owner, $store] = seggOwner();
    $plan = seggTrialPlan();

    seggGrantActiveSubscription($owner, $plan);

    actingAs($owner);

    $this->get(seggTeamsUrl($store))
        ->assertOk();
});
