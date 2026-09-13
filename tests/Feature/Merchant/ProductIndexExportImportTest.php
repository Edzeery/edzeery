<?php

use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Support\StoreRoles;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Volt;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
});

function indexStore(User $owner, string $suffix): Store
{
    return Store::create([
        'user_id' => $owner->id,
        'name'    => "Index Store {$suffix}",
        'slug'    => 'index-store-' . strtolower($suffix) . '-' . uniqid(),
        'status'  => 'active',
    ]);
}

function indexMembership(Store $store, User $user, User $inviter, StoreRoleEnum $role, array $permissions): StoreMembership
{
    $membership = StoreMembership::create([
        'store_id'   => $store->id,
        'user_id'    => $user->id,
        'invited_by' => $inviter->id,
        'is_active'  => true,
        'role'       => $role->value,
    ]);

    $membership->syncPermissions(array_values($permissions));

    return $membership;
}

function indexOwner(): array
{
    $owner = roleUser('merchant');
    $owner->assignRole(\Spatie\Permission\Models\Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = indexStore($owner, 'Owner');
    indexMembership($store, $owner, $owner, StoreRoleEnum::OWNER, StoreRoles::permissions(StoreRoleEnum::OWNER));

    Auth::login($owner);
    session(['current_store_id' => $store->id]);

    return [$owner, $store];
}

it('shows export and import triggers as Alpine-scope toggles for a user with view and create rights', function () {
    [$user, $store] = indexOwner();

    $volt = Volt::test('merchant.products.index');

    $volt->assertSeeHtml('@click="exportOpen = true"')
        ->assertSeeHtml('@click="importOpen = true"')
        ->assertSee('Export')
        ->assertSee('Import')
        ->assertSee('New product');
});

it('shows only export for a view-only staff member, gating import behind products.create', function () {
    $owner = roleUser('merchant');
    $store = indexStore($owner, 'ViewOnly');

    $staff = roleUser('merchant');
    $staff->assignRole(\Spatie\Permission\Models\Role::findOrCreate(StoreRoleEnum::STAFF->value, 'merchant'));
    indexMembership($store, $staff, $owner, StoreRoleEnum::STAFF, [
        StorePermissionEnum::PRODUCT_VIEW->value,
    ]);

    Auth::login($staff);
    session(['current_store_id' => $store->id]);

    $volt = Volt::test('merchant.products.index');

    expect(canStore(StorePermissionEnum::PRODUCT_VIEW->value))->toBeTrue()
        ->and(canStore(StorePermissionEnum::PRODUCT_CREATE->value))->toBeFalse();

    $volt->assertSeeHtml('@click="exportOpen = true"')
        ->assertDontSeeHtml('@click="importOpen = true"')
        ->assertDontSee('New product');
});

it('returns 403 for anyone without products.view', function () {
    $owner = roleUser('merchant');
    $store = indexStore($owner, 'NoView');

    $staff = roleUser('merchant');
    $staff->assignRole(\Spatie\Permission\Models\Role::findOrCreate(StoreRoleEnum::STAFF->value, 'merchant'));
    indexMembership($store, $staff, $owner, StoreRoleEnum::STAFF, [
        StorePermissionEnum::ORDER_VIEW->value,
    ]);

    expect(canStore(StorePermissionEnum::PRODUCT_VIEW->value))->toBeFalse();

    $this->actingAs($staff)->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.products.index', ['store' => $store->slug]))
        ->assertForbidden();
});

it('renders the export modal hidden in the DOM, Alpine-toggled, with disabled actions and a coming soon note', function () {
    [$user, $store] = indexOwner();

    $volt = Volt::test('merchant.products.index');

    $volt->assertSee('Export products')
        ->assertSeeHtml('x-show="exportOpen"')
        ->assertSeeHtml('x-cloak')
        ->assertSeeHtml("document.body.style.overflow = (exportOpen || importOpen)")
        ->assertSee('coming soon', false)
        ->assertSeeHtml('grid grid-cols-1 gap-2 sm:grid-cols-2')
        ->assertSeeHtml('grid grid-cols-1 gap-2 sm:grid-cols-3')
        ->assertSeeHtml('accent-accent-600')
        ->assertSeeHtml('<button type="button" disabled class="edz-btn edz-btn--primary edz-btn--sm">')
        ->assertSeeHtml('@click="exportOpen = false"')
        ->assertSee('Selected (0)');
});

it('renders the import modal hidden in the DOM, with a dropzone and disabled actions', function () {
    [$user, $store] = indexOwner();

    $volt = Volt::test('merchant.products.index');

    $volt->assertSee('Import products')
        ->assertSeeHtml('x-show="importOpen"')
        ->assertSeeHtml('x-cloak')
        ->assertSee('Download template')
        ->assertSee('Browse files')
        ->assertSee('coming soon', false)
        ->assertSeeHtml('<button type="button" disabled class="edz-btn edz-btn--primary edz-btn--sm">')
        ->assertSeeHtml('edz-btn edz-btn--secondary edz-btn--sm mt-3')
        ->assertSeeHtml('@click="importOpen = false"');
});

it('keeps both modals always mounted but toggled independently via the root Alpine scope', function () {
    [$user, $store] = indexOwner();

    $volt = Volt::test('merchant.products.index');

    $volt->assertSeeHtml('x-show="exportOpen"')
        ->assertSeeHtml('x-show="importOpen"')
        ->assertSeeHtml('@click="exportOpen = true"')
        ->assertSeeHtml('@click="importOpen = true"')
        ->assertSee('Export products')
        ->assertSee('Import products')
        ->assertSee('Download template');
});