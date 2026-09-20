<?php

use App\Domains\Order\Models\ConfirmationProductAssignment;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Products\Product;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Services\Stores\StoreProductScopeService;
use App\Support\StoreRoles;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Illuminate\Support\Str;
use Livewire\Volt\Volt;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
});

function scopeStore(User $owner, string $suffix): Store
{
    return Store::create([
        'user_id' => $owner->id,
        'name'    => "Scope Store {$suffix}",
        'slug'    => "scope-store-{$suffix}-".uniqid(),
        'status'  => 'active',
    ]);
}

function scopeMembership(Store $store, User $user, User $inviter, StoreRoleEnum $role): StoreMembership
{
    return StoreMembership::create([
        'store_id'   => $store->id,
        'user_id'    => $user->id,
        'invited_by' => $inviter->id,
        'is_active'  => true,
        'role'       => $role->value,
    ]);
}

function scopeProduct(Store $store, string $name): Product
{
    return Product::create([
        'store_id'  => $store->id,
        'name'      => $name,
        'slug'      => Str::slug($name).'-'.uniqid(),
        'sku'       => 'SKU-'.strtoupper(Str::random(6)),
        'type'      => 'simple',
        'price'     => 100,
        'is_active' => true,
    ]);
}

function scopeSyncPermissions(StoreMembership $membership, StoreRoleEnum $role): StoreMembership
{
    $membership->syncPermissions(StoreRoles::permissions($role));

    return $membership;
}

function scopeActAs(User $user, Store $store): void
{
    test()->actingAs($user);
    test()->withSession(['current_store_id' => $store->id]);
    app(\App\Support\StoreContext::class)->clear();
}

it('assigns a product to a manager membership of the same store (idempotent upsert)', function () {
    $owner = roleUser('merchant');
    $store = scopeStore($owner, 'A');
    $manager = roleUser('merchant');
    $membership = scopeMembership($store, $manager, $owner, StoreRoleEnum::MANAGER);
    $product = scopeProduct($store, 'Widget');

    app(StoreProductScopeService::class)->assign($store, $membership, $product);
    app(StoreProductScopeService::class)->assign($store, $membership, $product);

    $rows = ConfirmationProductAssignment::query()
        ->where('store_id', $store->id)
        ->where('membership_id', $membership->id)
        ->where('product_id', $product->id)
        ->count();

    expect($rows)->toBe(1)
        ->and(app(StoreProductScopeService::class)->assignedProductIds($store, $membership))->toBe([$product->id]);
});

it('rejects assign/revoke for a non-manager membership (owner, admin, staff)', function () {
    $owner = roleUser('merchant');
    $store = scopeStore($owner, 'Role');
    $ownerMembership = scopeMembership($store, $owner, $owner, StoreRoleEnum::OWNER);
    $product = scopeProduct($store, 'Widget');

    $nonManagers = [$ownerMembership];

    foreach ([StoreRoleEnum::ADMIN, StoreRoleEnum::STAFF] as $role) {
        $user = roleUser('merchant');
        $nonManagers[] = scopeMembership($store, $user, $owner, $role);
    }

    foreach ($nonManagers as $membership) {
        expect(fn () => app(StoreProductScopeService::class)->assign($store, $membership, $product))
            ->toThrow(\InvalidArgumentException::class, __('teams.product_scope_role_only'));

        expect(fn () => app(StoreProductScopeService::class)->revoke($store, $membership, $product))
            ->toThrow(\InvalidArgumentException::class, __('teams.product_scope_role_only'));
    }

    expect(app(StoreProductScopeService::class)->assignedProductIds($store, $ownerMembership))->toBe([]);
});

it('rejects cross-store memberships and cross-store products', function () {
    $owner = roleUser('merchant');
    $storeA = scopeStore($owner, 'A');
    $storeB = scopeStore($owner, 'B');

    $manager = roleUser('merchant');
    $managerA = scopeMembership($storeA, $manager, $owner, StoreRoleEnum::MANAGER);
    $managerB = scopeMembership($storeB, $manager, $owner, StoreRoleEnum::MANAGER);

    $productA = scopeProduct($storeA, 'Widget A');
    $productB = scopeProduct($storeB, 'Widget B');

    expect(fn () => app(StoreProductScopeService::class)->assign($storeB, $managerA, $productB))
        ->toThrow(\InvalidArgumentException::class, __('teams.product_scope_cross_store'));

    expect(fn () => app(StoreProductScopeService::class)->assign($storeA, $managerB, $productA))
        ->toThrow(\InvalidArgumentException::class, __('teams.product_scope_cross_store'));

    expect(fn () => app(StoreProductScopeService::class)->assign($storeA, $managerA, $productB))
        ->toThrow(\InvalidArgumentException::class, __('teams.product_scope_cross_store'));
});

it('revokes only the exact assignment row for the given product', function () {
    $owner = roleUser('merchant');
    $store = scopeStore($owner, 'Rev');
    $manager = roleUser('merchant');
    $membership = scopeMembership($store, $manager, $owner, StoreRoleEnum::MANAGER);
    $productA = scopeProduct($store, 'Widget A');
    $productB = scopeProduct($store, 'Widget B');

    app(StoreProductScopeService::class)->assign($store, $membership, $productA);
    app(StoreProductScopeService::class)->assign($store, $membership, $productB);

    app(StoreProductScopeService::class)->revoke($store, $membership, $productA);

    expect(app(StoreProductScopeService::class)->assignedProductIds($store, $membership))->toBe([$productB->id]);

    expect(ConfirmationProductAssignment::query()
        ->where('store_id', $store->id)
        ->where('membership_id', $membership->id)
        ->where('product_id', $productA->id)
        ->count())->toBe(0);
});

it('blocks add-product for a non-manager target via the Volt action guard', function () {
    $owner = roleUser('merchant');
    $store = scopeStore($owner, 'Guard');
    $staff = roleUser('merchant');
    $staffMembership = scopeMembership($store, $staff, $owner, StoreRoleEnum::STAFF);
    $product = scopeProduct($store, 'Widget');
    scopeMembership($store, $owner, $owner, StoreRoleEnum::OWNER);

    scopeActAs($owner, $store);

    Volt::test('merchant.teams.partials.product-scope-modal', ['membershipId' => $staffMembership->id])
        ->assertOk()
        ->assertDontSee(__('teams.product_scope_title'))
        ->call('addProductScope', $product->id);

    expect(ConfirmationProductAssignment::query()->count())->toBe(0);
});

it('fills the product scope modal for an active manager and add/remove works', function () {
    $admin = roleUser('merchant');
    $store = scopeStore($admin, 'Fill');
    scopeMembership($store, $admin, $admin, StoreRoleEnum::ADMIN);

    $manager = roleUser('merchant');
    $managerMembership = scopeMembership($store, $manager, $admin, StoreRoleEnum::MANAGER);

    $productA = scopeProduct($store, 'Widget Alpha');
    $productB = scopeProduct($store, 'Widget Beta');

    app(StoreProductScopeService::class)->assign($store, $managerMembership, $productA);

    scopeActAs($admin, $store);

    Volt::test('merchant.teams.partials.product-scope-modal', ['membershipId' => $managerMembership->id])
        ->assertOk()
        ->assertSee($manager->name)
        ->assertSee($productA->name)
        ->assertDontSee(__('teams.product_scope_unrestricted'))
        ->set('search', 'beta')
        ->assertSee($productB->name)
        ->call('removeProductScope', $productA->id)
        ->call('addProductScope', $productB->id);

    expect(app(StoreProductScopeService::class)->assignedProductIds($store, $managerMembership))->toBe([$productB->id]);
});

it('shows the unrestricted empty state for a manager with no assignments', function () {
    $admin = roleUser('merchant');
    $store = scopeStore($admin, 'Empty');
    scopeMembership($store, $admin, $admin, StoreRoleEnum::ADMIN);

    $manager = roleUser('merchant');
    $managerMembership = scopeMembership($store, $manager, $admin, StoreRoleEnum::MANAGER);

    scopeActAs($admin, $store);

    Volt::test('merchant.teams.partials.product-scope-modal', ['membershipId' => $managerMembership->id])
        ->assertOk()
        ->assertSee(__('teams.product_scope_unrestricted'));

    expect(app(StoreProductScopeService::class)->assignedProductIds($store, $managerMembership))->toBe([]);
});

it('opens the product scope modal from the teams page for an active manager', function () {
    $admin = roleUser('merchant');
    $store = scopeStore($admin, 'Open');
    scopeSyncPermissions(scopeMembership($store, $admin, $admin, StoreRoleEnum::ADMIN), StoreRoleEnum::ADMIN);

    $manager = roleUser('merchant');
    $managerMembership = scopeMembership($store, $manager, $admin, StoreRoleEnum::MANAGER);

    scopeActAs($admin, $store);

    Volt::test('merchant.teams.index')
        ->assertOk()
        ->set('productScopeMembershipId', $managerMembership->id)
        ->assertSee(__('teams.product_scope_title'))
        ->assertSee($manager->name);
});

it('does not expose the product scope for the owner membership from the teams page', function () {
    $owner = roleUser('merchant');
    $store = scopeStore($owner, 'Hide');
    scopeSyncPermissions(scopeMembership($store, $owner, $owner, StoreRoleEnum::OWNER), StoreRoleEnum::OWNER);

    $ownerMembership = StoreMembership::query()->where('store_id', $store->id)->where('user_id', $owner->id)->first();

    scopeActAs($owner, $store);

    Volt::test('merchant.teams.index')
        ->assertOk()
        ->set('productScopeMembershipId', $ownerMembership->id)
        ->assertDontSee(__('teams.product_scope_title'));
});