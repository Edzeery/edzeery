<?php

use App\Enums\Store\StorePermissionEnum;
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
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(SystemStatusesSeeder::class);
    $this->seed(PlansSeeder::class);
});

// ————— Fixtures (prefix: exeg, unique across the suite) —————
// Every fresh user auto-creates a personal TRIAL subscription on User::created
// (User::booted → created hook). That is the entire premise of the original bug:
// a staff member holds their OWN valid personal trial, so a middleware gating on the
// API user's OWN latestSubscription() let them straight through — even while the store
// OWNER's subscription had expired hmv.

function exegUser(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name'    => 'Subscription Gate Store',
        'slug'    => 'sub-gate-'.uniqid(),
        'status'  => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id'   => $store->id,
        'user_id'    => $user->id,
        'invited_by' => $store->user_id,
        'is_active'  => true,
        'role'       => StoreRoleEnum::OWNER->value,
    ]);

    $membership->syncPermissions(StoreRoles::permissions(StoreRoleEnum::OWNER));

    return [$user, $store, $membership];
}

function exegMembership(Store $store, StoreRoleEnum $role): StoreMembership
{
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate($role->value, 'merchant'));

    $membership = StoreMembership::create([
        'store_id'   => $store->id,
        'user_id'    => $user->id,
        'invited_by' => $store->user_id,
        'is_active'  => true,
        'role'       => $role->value,
    ]);

    $membership->syncPermissions(StoreRoles::permissions($role));

    return $membership;
}

function exegPlan(): Plan
{
    return Plan::where('slug', 'trial')->first() ?? Plan::first();
}

function exegGrantExpiredSubscription(User $user, Plan $plan): void
{
    Subscription::where('user_id', $user->id)->delete();

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

function exegGrantActiveSubscription(User $user, Plan $plan): void
{
    Subscription::where('user_id', $user->id)->delete();

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

function exegTeamsUrl(Store $store): string
{
    return route('merchant.teams.index', ['store' => $store->slug]);
}

// ————— 1. The exact bug scenario —————
// Store OWNER subscription EXPIRED. Staff member holds their OWN valid personal trial
// (kept from creation). The middleware must gate on the OWNER subscription — so the
// staff member gets redirected to account.billing. Pre-fix, the staff member's own
// valid personal trial used to let them straight through.

it('redirects a staff member to account.billing when the store owner subscription is expired, even while the staff member holds their own valid personal trial', function () {
    [$owner, $store, $ownerMembership] = exegUser();
    $plan = exegPlan();
    $staffMembership = exegMembership($store, StoreRoleEnum::STAFF);

    exegGrantExpiredSubscription($owner, $plan);
    // Staff keeps their own auto-created personal trial (still within its window).

    actingAs($staffMembership->user);

    $this->get(exegTeamsUrl($store))
        ->assertRedirect(route('account.billing'));
});

// ————— 2. No over-blocking —————
// Same store, same non-owner member — but the OWNER subscription is ACTIVE. This member
// (a MANAGER: non-owner, keeps their own relevant personal trial, but holds the teams
// permission by role) must NOT be redirected. Guards against the fix over-blocking
// everyone who is not the owner.

it('does not redirect a manager to account.billing when the store owner subscription is active, even while the manager holds their own valid personal trial', function () {
    [$owner, $store, $ownerMembership] = exegUser();
    $plan = exegPlan();
    // A manager is a non-owner member who HAS the teams permission by role,
    // so any resulting 403/redirect can only come from the gating middleware.
    $managerMembership = exegMembership($store, StoreRoleEnum::MANAGER);

    exegGrantActiveSubscription($owner, $plan);
    // Manager keeps their own auto-created personal trial, which must now be irrelevant.

    actingAs($managerMembership->user);

    $this->get(exegTeamsUrl($store))
        ->assertOk();
});

// ————— 3. Owner-as-owner sanity —————
// The OWNER themself with an active subscription must not be redirected.

it('does not redirect the store owner themself when their own subscription is active', function () {
    [$owner, $store, $ownerMembership] = exegUser();
    $plan = exegPlan();

    exegGrantActiveSubscription($owner, $plan);

    actingAs($owner);

    $this->get(exegTeamsUrl($store))
        ->assertOk();
});
