<?php

use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Support\StoreOrderPermissions;
use App\Support\StoreRoles;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
});

function gateStore(User $owner, string $suffix): Store
{
    return Store::create([
        'user_id' => $owner->id,
        'name'    => "Gate Store {$suffix}",
        'slug'    => "gate-store-{$suffix}-".uniqid(),
        'status'  => 'active',
    ]);
}

function gateMembership(Store $store, User $user, User $inviter, StoreRoleEnum $role): StoreMembership
{
    $membership = StoreMembership::create([
        'store_id'   => $store->id,
        'user_id'    => $user->id,
        'invited_by' => $inviter->id,
        'is_active'  => true,
        'role'       => $role->value,
    ]);
    $membership->syncPermissions(StoreRoles::permissions($role));

    return $membership;
}

it('maps each target order status to the fine-grained transition permission (P1 core)', function () {
    expect(StoreOrderPermissions::forStatus('confirmed'))->toBe(StorePermissionEnum::ORDER_CONFIRM->value)
        ->and(StoreOrderPermissions::forStatus('preparing'))->toBe(StorePermissionEnum::ORDER_CONFIRM->value)
        ->and(StoreOrderPermissions::forStatus('on_hold'))->toBe(StorePermissionEnum::ORDER_CONFIRM->value)
        ->and(StoreOrderPermissions::forStatus('cancelled'))->toBe(StorePermissionEnum::ORDER_CANCEL->value)
        ->and(StoreOrderPermissions::forStatus('no_answer_1'))->toBe(StorePermissionEnum::ORDER_CANCEL->value)
        ->and(StoreOrderPermissions::forStatus('out_of_stock'))->toBe(StorePermissionEnum::ORDER_CANCEL->value)
        ->and(StoreOrderPermissions::forStatus('shipped'))->toBe(StorePermissionEnum::ORDER_MANAGE->value)
        ->and(StoreOrderPermissions::forStatus('delivered'))->toBe(StorePermissionEnum::ORDER_MANAGE->value)
        ->and(StoreOrderPermissions::forStatus('returned'))->toBe(StorePermissionEnum::ORDER_MANAGE->value);
});

it('blocks a confirm-only staff member from shipping but allows them to confirm (P1)', function () {
    $owner = roleUser('merchant');
    $store = gateStore($owner, 'P1');

    $staff = roleUser('merchant');
    $staff->assignRole(Role::findOrCreate('staff', 'merchant'));

    // Confirm-only member: order.view + order.confirm only (no order.manage / order.cancel).
    $membership = StoreMembership::create([
        'store_id'   => $store->id,
        'user_id'    => $staff->id,
        'invited_by' => $owner->id,
        'is_active'  => true,
        'role'       => StoreRoleEnum::STAFF->value,
    ]);
    $membership->syncPermissions([
        StorePermissionEnum::ORDER_VIEW->value,
        StorePermissionEnum::ORDER_CONFIRM->value,
    ]);

    $this->actingAs($staff);
    $this->withSession(['current_store_id' => $store->id]);
    app(\App\Support\StoreContext::class)->clear();

    // Confirming requires order.confirm → present → allowed.
    expect(canStore(StoreOrderPermissions::forStatus('confirmed')))->toBeTrue();

    // Shipping maps to order.manage → absent → the $transitionOrder abort (403) is tripped.
    expect(canStore(StoreOrderPermissions::forStatus('shipped')))->toBeFalse()
        ->and(canStore(StorePermissionEnum::ORDER_MANAGE->value))->toBeFalse()
        ->and(canStore(StorePermissionEnum::ORDER_CANCEL->value))->toBeFalse();
});

it('lets an owner ship because shipping requires order.manage which they hold (P1 positive)', function () {
    $owner = roleUser('merchant');
    $store = gateStore($owner, 'P1p');
    gateMembership($store, $owner, $owner, StoreRoleEnum::OWNER);

    $this->actingAs($owner);
    $this->withSession(['current_store_id' => $store->id]);
    app(\App\Support\StoreContext::class)->clear();

    expect(canStore(StoreOrderPermissions::forStatus('confirmed')))->toBeTrue()
        ->and(canStore(StoreOrderPermissions::forStatus('shipped')))->toBeTrue()
        ->and(canStore(StoreOrderPermissions::forStatus('cancelled')))->toBeTrue();
});

it('lets a manager open the teams page and view their own team (P2)', function () {
    $owner = roleUser('merchant');
    $store = gateStore($owner, 'P2m');

    $manager = roleUser('merchant');
    $manager->assignRole(Role::findOrCreate('manager', 'merchant'));
    gateMembership($store, $manager, $owner, StoreRoleEnum::MANAGER);

    $this->actingAs($manager)->get(route('merchant.teams.index', $store->slug))->assertOk();
});

it('keeps a confirm-only staff member locked out of the teams page (P2 negative)', function () {
    $owner = roleUser('merchant');
    $store = gateStore($owner, 'P2n');

    $staff = roleUser('merchant');
    $staff->assignRole(Role::findOrCreate('staff', 'merchant'));

    $membership = StoreMembership::create([
        'store_id'   => $store->id,
        'user_id'    => $staff->id,
        'invited_by' => $owner->id,
        'is_active'  => true,
        'role'       => StoreRoleEnum::STAFF->value,
    ]);
    $membership->syncPermissions([
        StorePermissionEnum::ORDER_VIEW->value,
        StorePermissionEnum::ORDER_CONFIRM->value,
    ]);

    $this->actingAs($staff)->get(route('merchant.teams.index', $store->slug))->assertForbidden();
});

it('renders the permission matrix for a role whose template includes team.view without crashing', function () {
    // Regression: `permissions.team.view` resolves to the lang GROUP array
    // (['own' => ...]), so the permission-label echo used to crash with
    // "htmlspecialchars(): Argument #1 ... array given". The guard must render it.
    $owner = roleUser('merchant');
    $store = gateStore($owner, 'P2r');
    gateMembership($store, $owner, $owner, StoreRoleEnum::OWNER);

    $manager = roleUser('merchant');
    $manager->assignRole(Role::findOrCreate('manager', 'merchant'));
    gateMembership($store, $manager, $owner, StoreRoleEnum::MANAGER);

    $this->actingAs($manager)->withSession(['current_store_id' => $store->id]);
    app(\App\Support\StoreContext::class)->clear();

    $permissions = StoreRoles::permissions(StoreRoleEnum::ADMIN);
    expect($permissions)->toContain(StorePermissionEnum::TEAM_VIEW->value);

    Volt::test('merchant.teams.index')
        ->call('openCreate')
        ->set('store_role', StoreRoleEnum::ADMIN->value)
        ->set('permissions', $permissions)
        ->assertOk();
});
