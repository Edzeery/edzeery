<?php

use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Products\Product;
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

function filterOwner(): array
{
    $owner = roleUser('merchant');
    $owner->assignRole(\Spatie\Permission\Models\Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $owner->id,
        'name'    => 'Filter Store',
        'slug'    => 'filter-store-' . uniqid(),
        'status'  => 'active',
    ]);

    StoreMembership::create([
        'store_id'   => $store->id,
        'user_id'    => $owner->id,
        'invited_by' => $owner->id,
        'is_active'  => true,
        'role'       => StoreRoleEnum::OWNER->value,
    ])->syncPermissions(StoreRoles::permissions(StoreRoleEnum::OWNER));

    Auth::login($owner);
    session(['current_store_id' => $store->id]);

    return [$owner, $store];
}

function filterProduct(Store $store, array $overrides = []): Product
{
    return Product::create(array_merge([
        'store_id'  => $store->id,
        'name'      => 'Filter Product ' . uniqid(),
        'slug'      => 'filter-product-' . uniqid(),
        'sku'       => 'FILTER-' . uniqid(),
        'type'      => 'simple',
        'price'     => 1500,
        'is_active' => true,
    ], $overrides));
}

it('renders the drill-down filter trigger with a zero-count fresh state', function () {
    [$user, $store] = filterOwner();

    $volt = Volt::test('merchant.products.index');

    $volt->assertSee(__('merchant_panel.filters'))
        ->assertSeeHtml("edz-filter-open")
        ->assertSeeHtml("key: 'root'")
        ->assertDontSeeHtml('inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-[10px]');
});

it('shows the active filter count badge when filters are applied', function () {
    [$user, $store] = filterOwner();
    $brand = Brand::create(['store_id' => $store->id, 'name' => 'Nike', 'slug' => 'nike-' . uniqid()]);
    filterProduct($store, ['brand_id' => $brand->id]);

    $volt = Volt::test('merchant.products.index')
        ->call('setFilter', 'brand_id', $brand->id);

    expect($volt->instance()->activeFilterCount())->toBe(1);
});

it('filters products by brand via setFilter and resets page', function () {
    [$user, $store] = filterOwner();
    $nike = Brand::create(['store_id' => $store->id, 'name' => 'Nike', 'slug' => 'nike-' . uniqid()]);
    $adidas = Brand::create(['store_id' => $store->id, 'name' => 'Adidas', 'slug' => 'adidas-' . uniqid()]);

    $nikeProduct = filterProduct($store, ['brand_id' => $nike->id, 'name' => 'Nike Shoe']);
    filterProduct($store, ['brand_id' => $adidas->id, 'name' => 'Adidas Shoe']);

    $volt = Volt::test('merchant.products.index');

    $volt->assertSee('Adidas Shoe');

    $volt->call('setFilter', 'brand_id', $nike->id)
        ->assertSee('Nike Shoe')
        ->assertDontSee('Adidas Shoe');

    $volt->call('setFilter', 'brand_id', '')
        ->assertSee('Nike Shoe')
        ->assertSee('Adidas Shoe');
});

it('filters products by primary category via setFilter', function () {
    [$user, $store] = filterOwner();
    $clothing = Category::create(['store_id' => $store->id, 'name' => 'Clothing', 'slug' => 'clothing-' . uniqid()]);
    $shoes = Category::create(['store_id' => $store->id, 'name' => 'Shoes', 'slug' => 'shoes-' . uniqid()]);

    filterProduct($store, ['primary_category_id' => $clothing->id, 'name' => 'Cotton Tee']);
    $shoeProduct = filterProduct($store, ['primary_category_id' => $shoes->id, 'name' => 'Running Shoe']);

    $volt = Volt::test('merchant.products.index')
        ->call('setFilter', 'category_id', $shoes->id);

    $volt->assertSee('Running Shoe')
        ->assertDontSee('Cotton Tee');

    expect($volt->instance()->activeFilterCount())->toBe(1);
});

it('filters by status and featured flags', function () {
    [$user, $store] = filterOwner();
    filterProduct($store, ['name' => 'Active One', 'is_active' => true]);
    filterProduct($store, ['name' => 'Inactive One', 'is_active' => false]);
    filterProduct($store, ['name' => 'Featured One', 'is_featured' => true]);

    $volt = Volt::test('merchant.products.index');

    $volt->call('setFilter', 'is_active', '1')->assertSee('Active One')->assertDontSee('Inactive One');
    $volt->call('setFilter', 'is_active', '0')->assertSee('Inactive One')->assertDontSee('Active One');

    $volt->call('setFilter', 'is_featured', '1')
        ->call('setFilter', 'is_active', '')
        ->assertSee('Featured One')
        ->assertDontSee('Active One');
});

it('filters by created_from and created_to date range', function () {
    [$user, $store] = filterOwner();

    $old = filterProduct($store, ['name' => 'Old Product']);
    Product::query()->where('id', $old->id)->update(['created_at' => now()->subDays(10)]);
    $new = filterProduct($store, ['name' => 'New Product']);
    Product::query()->where('id', $new->id)->update(['created_at' => now()->subDays(2)]);

    $volt = Volt::test('merchant.products.index');

    $volt->call('setFilter', 'created_from', now()->subDays(5)->format('Y-m-d'))
        ->assertSee('New Product')
        ->assertDontSee('Old Product');

    $volt->call('setFilter', 'created_from', '')
        ->call('setFilter', 'created_to', now()->subDays(5)->format('Y-m-d'))
        ->assertSee('Old Product')
        ->assertDontSee('New Product');
});

it('clearFilters resets every filter and the active count', function () {
    [$user, $store] = filterOwner();
    $brand = Brand::create(['store_id' => $store->id, 'name' => 'Nike', 'slug' => 'nike-' . uniqid()]);

    filterProduct($store, ['name' => 'Survivor']);

    $volt = Volt::test('merchant.products.index')
        ->call('setFilter', 'brand_id', $brand->id)
        ->call('setFilter', 'is_active', '1')
        ->call('setFilter', 'created_from', now()->subDays(3)->format('Y-m-d'));

    expect($volt->instance()->activeFilterCount())->toBe(3);

    $volt->call('clearFilters');

    expect($volt->instance()->activeFilterCount())->toBe(0)
        ->and($volt->instance()->brand_id)->toBe('')
        ->and($volt->instance()->category_id)->toBe('')
        ->and($volt->instance()->is_active)->toBe('')
        ->and($volt->instance()->is_featured)->toBe('')
        ->and($volt->instance()->created_from)->toBe('')
        ->and($volt->instance()->created_to)->toBe('');

    $volt->assertSee('Survivor');
});

it('lists categories in the portal with their full hierarchy name', function () {
    [$user, $store] = filterOwner();
    $parent = Category::create(['store_id' => $store->id, 'name' => 'Men', 'slug' => 'men-' . uniqid()]);
    $child = Category::create([
        'store_id' => $store->id,
        'parent_id' => $parent->id,
        'name' => 'T-Shirts',
        'slug' => 't-shirts-' . uniqid(),
    ]);

    $volt = Volt::test('merchant.products.index');

    $volt->assertSee('Men > T-Shirts')
        ->assertSeeHtml("setFilter('category_id', '")
        ->assertSee(__('products.all_categories'));
});

it('wire:target lists include every filter property for skeleton feedback', function () {
    [$user, $store] = filterOwner();

    $volt = Volt::test('merchant.products.index');

    $volt->assertSeeHtml('wire:target="search,brand_id,category_id,is_active,is_featured,created_from,created_to"');
});