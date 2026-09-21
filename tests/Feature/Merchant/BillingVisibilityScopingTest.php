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

// ————— Fixtures —————

function bvsUser(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name'    => 'Visibility Store',
        'slug'    => 'billing-vis-'.uniqid(),
        'status'  => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id'   => $store->id,
        'user_id'    => $user->id,
        'invited_by' => $user->id,
        'is_active'  => true,
        'role'       => StoreRoleEnum::OWNER->value,
    ]);

    $membership->syncPermissions(StoreRoles::permissions(StoreRoleEnum::OWNER));

    return [$user, $store, $membership];
}

function bvsMembership(Store $store, StoreRoleEnum $role, ?array $permissions = null): StoreMembership
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

    $membership->syncPermissions($permissions ?? StoreRoles::permissions($role));

    return $membership;
}

function bvsKillSubscriptions(User $user): void
{
    Subscription::where('user_id', $user->id)->delete();
}

function bvsGrantActiveSubscription(User $user, Plan $plan): void
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

// ————— 1. The shared helper —————

it('resolves canViewStoreBilling for owner, staff, and a granted member', function () {
    [$owner, $store] = bvsUser();
    $staff = bvsMembership($store, StoreRoleEnum::STAFF);
    $granted = bvsMembership($store, StoreRoleEnum::MANAGER, array_merge(
        StoreRoles::permissions(StoreRoleEnum::MANAGER),
        [StorePermissionEnum::STORE_BILLING_MANAGE->value],
    ));

    expect(canViewStoreBilling($store, $owner))->toBeTrue()
        ->and(canViewStoreBilling($store, $staff->user))->toBeFalse()
        ->and(canViewStoreBilling($store, $granted->user))->toBeTrue();
});

// ————— 2. choose-store —————

it('hides every subscription/plan/billing element on choose-store for a staff membership without billing permission', function () {
    [$owner, $store] = bvsUser();
    $plan = Plan::first();
    bvsGrantActiveSubscription($owner, $plan);
    $staff = bvsMembership($store, StoreRoleEnum::STAFF);

    actingAs($staff->user);

    Volt::test('merchant.choose-store')
        ->assertSet('canViewBilling', false)
        ->assertSet('stores.0.billing_visible', false)
        ->assertDontSee($plan->name)
        ->assertDontSee(__('buttons.billing'))
        ->assertDontSee(__('merchant_panel.no_active_subscription'))
        ->assertDontSee(__('stores.upgrade_plan'));
});

it('shows subscription/plan/billing elements on choose-store for the owning user', function () {
    [$owner, $store] = bvsUser();
    $plan = Plan::first();
    bvsGrantActiveSubscription($owner, $plan);
    bvsMembership($store, StoreRoleEnum::STAFF);

    actingAs($owner);

    Volt::test('merchant.choose-store')
        ->assertSet('canViewBilling', true)
        ->assertSet('stores.0.billing_visible', true)
        ->assertSee($plan->name)
        ->assertSee(__('buttons.billing'));
});

it('shows subscription/plan elements on choose-store for a member explicitly granted billing permission', function () {
    [$owner, $store] = bvsUser();
    $plan = Plan::first();
    bvsGrantActiveSubscription($owner, $plan);
    $granted = bvsMembership($store, StoreRoleEnum::MANAGER, array_merge(
        StoreRoles::permissions(StoreRoleEnum::MANAGER),
        [StorePermissionEnum::STORE_BILLING_MANAGE->value],
    ));

    actingAs($granted->user);

    Volt::test('merchant.choose-store')
        ->assertSet('canViewBilling', true)
        ->assertSet('stores.0.billing_visible', true)
        ->assertSee($plan->name);
});

// ————— 3. Store-panel banner (components/layouts/panel.blade.php) —————

it('renders the expired-subscription banner for the owner but hides it from staff without billing permission', function () {
    [$owner, $store] = bvsUser();
    $staff = bvsMembership($store, StoreRoleEnum::STAFF);

    // Every fresh user gets an automatic trial subscription (User::booted),
    // so remove it to simulate an expired/no-subscription state.
    bvsKillSubscriptions($owner);
    bvsKillSubscriptions($staff->user);

    // Owner holds no active subscription → banner must render.
    actingAs($owner)->withSession(['current_store_id' => $store->id])
        ->get("/merchant/{$store->slug}/dashboard")
        ->assertOk()
        ->assertSee(__('messages.go_to_billing'));

    // Staff member (no billing permission) → banner must not render.
    actingAs($staff->user)->withSession(['current_store_id' => $store->id])
        ->get("/merchant/{$store->slug}/dashboard")
        ->assertOk()
        ->assertDontSee(__('messages.go_to_billing'))
        ->assertDontSee(__('messages.subscription_expired_text'));
});

// ————— 4. Account stores list —————

it('hides the plan tile on the account stores list for staff and shows it for the owner', function () {
    [$owner, $store] = bvsUser();
    $plan = Plan::first();
    bvsGrantActiveSubscription($owner, $plan);
    $staff = bvsMembership($store, StoreRoleEnum::STAFF);

    actingAs($staff->user)
        ->get('/merchant/account/stores')
        ->assertOk()
        ->assertSee($store->name)
        ->assertDontSee($plan->name);

    actingAs($owner)
        ->get('/merchant/account/stores')
        ->assertOk()
        ->assertSee($plan->name);
});