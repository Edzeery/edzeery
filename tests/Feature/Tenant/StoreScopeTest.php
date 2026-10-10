<?php

use App\Exceptions\MissingStoreContextException;
use App\Models\Products\Product;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Scopes\StoreOrGlobalScope;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function ssStore(string $name): Store
{
    $owner = roleUser('merchant');

    return Store::create([
        'user_id' => $owner->id,
        'name' => $name,
        'slug' => Str::slug($name).'-'.uniqid(),
        'status' => 'active',
    ]);
}

function ssProduct(Store $store, string $name): Product
{
    return Product::create([
        'store_id' => $store->id,
        'name' => $name,
        'slug' => Str::slug($name).'-'.uniqid(),
        'sku' => 'SKU-'.uniqid(),
        'type' => 'simple',
        'price' => 100,
        'is_active' => true,
    ]);
}

beforeEach(function () {
    app(StoreContext::class)->clear();
});

it('reads nothing without a store context for a non-admin', function () {
    $store = ssStore('A');
    ssProduct($store, 'P1');

    expect(Product::query()->count())->toBe(0);
});

it('reads only the active store rows inside a context', function () {
    $a = ssStore('A');
    $b = ssStore('B');
    ssProduct($a, 'A1');
    ssProduct($a, 'A2');
    ssProduct($b, 'B1');

    actingInStore($a);

    expect(Product::query()->count())->toBe(2);
});

it('lets a platform admin read across stores when there is no context', function () {
    $a = ssStore('A');
    $b = ssStore('B');
    ssProduct($a, 'A1');
    ssProduct($b, 'B1');

    actingAs(roleUser('super_admin'));

    expect(Product::query()->count())->toBe(2);
});

it('filters a platform admin too when a store context is explicit', function () {
    $a = ssStore('A');
    $b = ssStore('B');
    ssProduct($a, 'A1');
    ssProduct($b, 'B1');

    actingAs(roleUser('admin'));
    actingInStore($a);

    expect(Product::query()->count())->toBe(1);
});

it('throws when creating without a store_id or a context', function () {
    expect(fn () => Product::create([
        'name' => 'Unscoped',
        'slug' => 'unscoped-'.uniqid(),
        'sku' => 'SKU-'.uniqid(),
        'type' => 'simple',
        'price' => 10,
        'is_active' => true,
    ]))->toThrow(MissingStoreContextException::class);
});

it('fills store_id from the context on create', function () {
    $store = ssStore('A');
    actingInStore($store);

    $product = Product::create([
        'name' => 'Auto',
        'slug' => 'auto-'.uniqid(),
        'sku' => 'SKU-'.uniqid(),
        'type' => 'simple',
        'price' => 10,
        'is_active' => true,
    ]);

    expect($product->store_id)->toBe($store->id);
});

it('honours an explicit store_id even without a context', function () {
    $store = ssStore('A');

    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Explicit',
        'slug' => 'explicit-'.uniqid(),
        'sku' => 'SKU-'.uniqid(),
        'type' => 'simple',
        'price' => 10,
        'is_active' => true,
    ]);

    expect($product->store_id)->toBe($store->id);
});

it('reads across stores with the withoutStoreScope escape hatch', function () {
    $a = ssStore('A');
    $b = ssStore('B');
    ssProduct($a, 'A1');
    ssProduct($b, 'B1');

    expect(Product::withoutStoreScope()->count())->toBe(2);
});

it('runs a callback inside a store context and restores the previous one', function () {
    $a = ssStore('A');
    $b = ssStore('B');
    $context = app(StoreContext::class);

    $context->set($b);

    $captured = StoreContext::runAs($a, fn () => $context->id());

    expect($captured)->toBe($a->id)
        ->and($context->get()?->id)->toBe($b->id);
});

it('restores the context even when the callback throws', function () {
    $a = ssStore('A');
    $context = app(StoreContext::class);

    try {
        StoreContext::runAs($a, function () {
            throw new RuntimeException('boom');
        });
    } catch (RuntimeException) {
        // expected
    }

    expect($context->get())->toBeNull();
});

it('scopes nullable store rows to the store plus the global rows', function () {
    $a = ssStore('A');

    Status::create(['store_id' => null, 'type' => 'order', 'key' => 'g', 'label' => 'Global', 'is_system' => true]);
    Status::create(['store_id' => $a->id, 'type' => 'order', 'key' => 'a', 'label' => 'Store A', 'is_system' => false]);

    actingInStore($a);

    $rows = Status::query()
        ->withGlobalScope('store_or_global', new StoreOrGlobalScope)
        ->count();

    expect($rows)->toBe(2);
});

it('shows only global rows to a non-admin with no context', function () {
    $a = ssStore('A');

    Status::create(['store_id' => null, 'type' => 'order', 'key' => 'g', 'label' => 'Global', 'is_system' => true]);
    Status::create(['store_id' => $a->id, 'type' => 'order', 'key' => 'a', 'label' => 'Store A', 'is_system' => false]);

    $rows = Status::query()
        ->withGlobalScope('store_or_global', new StoreOrGlobalScope)
        ->count();

    expect($rows)->toBe(1);
});

it('lets a platform admin see every nullable store row with no context', function () {
    $a = ssStore('A');

    Status::create(['store_id' => null, 'type' => 'order', 'key' => 'g', 'label' => 'Global', 'is_system' => true]);
    Status::create(['store_id' => $a->id, 'type' => 'order', 'key' => 'a', 'label' => 'Store A', 'is_system' => false]);

    actingAs(roleUser('super_admin'));

    $rows = Status::query()
        ->withGlobalScope('store_or_global', new StoreOrGlobalScope)
        ->count();

    expect($rows)->toBe(2);
});
