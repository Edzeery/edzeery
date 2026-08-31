<?php

use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Support\StoreRoles;

use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
});

function roleScopeStore(User $owner, string $suffix): Store
{
    return Store::create([
        'user_id'   => $owner->id,
        'name'      => "Role Scope {$suffix}",
        'slug'      => "role-scope-{$suffix}-".uniqid(),
        'status'    => 'active',
    ]);
}

it('isolates custom permissions per store membership (decision #6)', function () {
    $owner = roleUser('merchant');

    $storeA = roleScopeStore($owner, 'A');
    $storeB = roleScopeStore($owner, 'B');

    $member = User::factory()->create();

    // Member is poweredby STAFF in store A (confirm-only)…
    $membershipA = StoreMembership::create([
        'store_id'   => $storeA->id,
        'user_id'    => $member->id,
        'invited_by' => $owner->id,
        'is_active'  => true,
        'role'       => StoreRoleEnum::STAFF->value,
    ]);
    $membershipA->syncPermissions([
        StorePermissionEnum::ORDER_VIEW->value,
        StorePermissionEnum::ORDER_CONFIRM->value,
    ]);

    // …and a full OWNER in store B.
    $membershipB = StoreMembership::create([
        'store_id'   => $storeB->id,
        'user_id'    => $member->id,
        'invited_by' => $owner->id,
        'is_active'  => true,
        'role'       => StoreRoleEnum::OWNER->value,
    ]);
    $membershipB->syncPermissions(StoreRoles::permissions(StoreRoleEnum::OWNER));

    // Act within store A context.
    $this->actingAs($member);
    $this->withSession(['current_store_id' => $storeA->id]);
    app(\App\Support\StoreContext::class)->clear();

    expect(canStore(StorePermissionEnum::ORDER_VIEW->value))->toBeTrue()
        ->and(canStore(StorePermissionEnum::ORDER_CONFIRM->value))->toBeTrue()
        ->and(canStore(StorePermissionEnum::ORDER_MANAGE->value))->toBeFalse()
        ->and(canStore(StorePermissionEnum::ORDER_CANCEL->value))->toBeFalse()
        ->and(hasStoreRole(StoreRoleEnum::STAFF))->toBeTrue()
        ->and(hasStoreRole(StoreRoleEnum::OWNER))->toBeFalse();

    // Switch to store B context — same user, full owner powers now.
    $this->withSession(['current_store_id' => $storeB->id]);
    app(\App\Support\StoreContext::class)->clear();

    expect(hasStoreRole(StoreRoleEnum::OWNER))->toBeTrue()
        ->and(hasStoreRole(StoreRoleEnum::STAFF))->toBeFalse()
        ->and(canStore(StorePermissionEnum::ORDER_MANAGE->value))->toBeTrue()
        ->and(canStore(StorePermissionEnum::STORE_SETTINGS_SENSITIVE->value))->toBeTrue();

    // Editing store A permissions must NOT leak into store B.
    $membershipA->syncPermissions([StorePermissionEnum::ORDER_VIEW->value]);
    $this->withSession(['current_store_id' => $storeB->id]);
    app(\App\Support\StoreContext::class)->clear();
    expect(canStore(StorePermissionEnum::ORDER_MANAGE->value))->toBeTrue();
});

it('resolves storage-scoped permissions for a confirm-only staff member', function () {
    $owner = roleUser('merchant');
    $store = roleScopeStore($owner, 'C');

    $staff = User::factory()->create();
    $membership = StoreMembership::create([
        'store_id'   => $store->id,
        'user_id'    => $staff->id,
        'invited_by' => $owner->id,
        'is_active'  => true,
        'role'       => StoreRoleEnum::STAFF->value,
    ]);
    // Give the staff ONLY order.view + order.confirm (R4 scenario).
    $membership->syncPermissions([
        StorePermissionEnum::ORDER_VIEW->value,
        StorePermissionEnum::ORDER_CONFIRM->value,
    ]);

    $this->actingAs($staff);
    $this->withSession(['current_store_id' => $store->id]);
    app(\App\Support\StoreContext::class)->clear();

    // R2 sidebar: staff must see orders, but NOT team / store-settings.
    expect(\App\Support\StoreRoles::permissions(StoreRoleEnum::STAFF))->toContain(StorePermissionEnum::ORDER_VIEW->value)
        ->and(canStore(StorePermissionEnum::ORDER_VIEW->value))->toBeTrue()
        ->and(canStore(StorePermissionEnum::ORDER_CONFIRM->value))->toBeTrue()
        ->and(canStore(StorePermissionEnum::ORDER_MANAGE->value))->toBeFalse()
        ->and(canStore(StorePermissionEnum::ORDER_CANCEL->value))->toBeFalse()
        // Team + store settings hidden for staff (no permission granted).
        ->and(canStore(StorePermissionEnum::TEAM_VIEW->value))->toBeFalse()
        ->and(canStore(StorePermissionEnum::STORE_SETTINGS_SENSITIVE->value))->toBeFalse()
        // R3 dashboard: no financial KPI permission (stats.*).
        ->and(canStore(StorePermissionEnum::STATS_TOP_KPIS->value))->toBeFalse();
});
