<?php

use App\Domains\Order\Models\ConfirmationProductAssignment;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Products\Product;
use App\Models\Stores\Store;
use App\Models\Stores\Team\MembershipProductScope;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Services\Stores\StoreProductScopeService;
use App\Support\StoreRoles;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Illuminate\Support\Str;
use Livewire\Volt\Volt;

/**
 * Owner-facing review of the non-destructive backfill: a manager holding the
 * same product both as a visibility scope and as a legacy confirmation
 * specialist row gets a banner, and the owner can drop the specialist duplicate.
 */
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
});

function dupStore(User $owner, User $admin): Store
{
    $store = Store::create([
        'user_id' => $owner->id,
        'name' => 'Dup Store',
        'slug' => 'dup-'.uniqid(),
        'status' => 'active',
    ]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $admin->id,
        'invited_by' => $owner->id,
        'is_active' => true,
        'role' => StoreRoleEnum::ADMIN->value,
    ])->syncPermissions(StoreRoles::permissions(StoreRoleEnum::ADMIN));

    test()->actingAs($admin);
    test()->withSession(['current_store_id' => $store->id]);
    app(\App\Support\StoreContext::class)->clear();

    return $store;
}

function dupMembership(Store $store, StoreRoleEnum $role): StoreMembership
{
    return StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => User::factory()->create()->id,
        'invited_by' => $store->user_id,
        'is_active' => true,
        'role' => $role->value,
    ]);
}

function dupProduct(Store $store, string $name): Product
{
    return Product::create([
        'store_id' => $store->id,
        'name' => $name,
        'slug' => Str::slug($name).'-'.uniqid(),
        'sku' => 'DUP-'.strtoupper(Str::random(6)),
        'type' => 'simple',
        'price' => 100,
        'is_active' => true,
    ]);
}

it('flags the duplicated manager row and removes only the specialist side', function () {
    $admin = roleUser('merchant');
    $store = dupStore(roleUser('merchant'), $admin);
    $manager = dupMembership($store, StoreRoleEnum::MANAGER);
    $product = dupProduct($store, 'Ambiguous Product');

    app(StoreProductScopeService::class)->assign($store, $manager, $product);

    ConfirmationProductAssignment::create([
        'store_id' => $store->id,
        'membership_id' => $manager->id,
        'product_id' => $product->id,
    ]);

    Volt::test('merchant.teams.partials.product-scope-modal', ['membershipId' => $manager->id])
        ->assertOk()
        ->assertSee(__('teams.product_scope_duplicate_title'))
        ->assertSee($product->name)
        ->call('removeSpecialistDuplicate', $product->id)
        ->assertDontSee(__('teams.product_scope_duplicate_title'));

    // Scope kept, specialist duplicate gone.
    expect(MembershipProductScope::query()->where('membership_id', $manager->id)->count())->toBe(1)
        ->and(ConfirmationProductAssignment::query()->where('membership_id', $manager->id)->count())->toBe(0);
});

it('never shows the banner for a scope-only manager and leaves staff rows alone', function () {
    $admin = roleUser('merchant');
    $store = dupStore(roleUser('merchant'), $admin);
    $manager = dupMembership($store, StoreRoleEnum::MANAGER);
    $staff = dupMembership($store, StoreRoleEnum::STAFF);

    $scopedProduct = dupProduct($store, 'Scope Only');
    $staffProduct = dupProduct($store, 'Staff Specialist');

    app(StoreProductScopeService::class)->assign($store, $manager, $scopedProduct);

    ConfirmationProductAssignment::create([
        'store_id' => $store->id,
        'membership_id' => $staff->id,
        'product_id' => $staffProduct->id,
    ]);

    Volt::test('merchant.teams.partials.product-scope-modal', ['membershipId' => $manager->id])
        ->assertOk()
        ->assertSee($scopedProduct->name)
        ->assertDontSee(__('teams.product_scope_duplicate_title'))
        // Even naming the staff row's product leaves it untouched.
        ->call('removeSpecialistDuplicate', $staffProduct->id);

    expect(ConfirmationProductAssignment::query()
        ->where('membership_id', $staff->id)
        ->where('product_id', $staffProduct->id)
        ->count())->toBe(1)
        ->and(MembershipProductScope::query()->count())->toBe(1);
});
