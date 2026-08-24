<?php

use App\Domains\Cart\Services\CartService;
use App\Models\Products\Product;
use App\Models\Products\ProductOption;
use App\Models\Products\ProductOptionValue;
use App\Models\Products\ProductVariant;
use App\Models\Stores\Store;

/**
 * Phase 7R: merchant-driven variant ordering.
 *  - Option Type drives the storefront UX (radio/select = exclusive pick).
 *  - Min/max order qty: product override -> store default -> (1, unlimited).
 *  - Stock is enforced unless backorder / tracking settings say otherwise.
 */

function makeMatrixStore(): Store
{
    $user = \App\Models\User::factory()->create();

    return Store::create([
        'user_id' => $user->id,
        'name' => 'Matrix Store',
        'slug' => 'mx-' . uniqid(),
        'status' => 'active',
        'landing_template' => 'catalog',
    ]);
}

function makeMatrixProduct(Store $store, array $overrides = []): Product
{
    return Product::create(array_merge([
        'store_id' => $store->id,
        'name' => 'Matrix Product',
        'slug' => 'mtx-' . uniqid(),
        'sku' => 'MTX-' . uniqid(),
        'type' => 'variable',
        'price' => 1000,
        'is_active' => true,
    ], $overrides));
}

/**
 * One option of a given Type with one active variant per value.
 *
 * @return array{0: ProductOption, 1: \Illuminate\Support\Collection<ProductVariant>}
 */
function makeOptionWithVariants(Store $store, Product $product, string $type, array $values): array
{
    $option = ProductOption::create([
        'store_id' => $store->id,
        'name' => ucfirst($type) . ' Option',
        'type' => $type,
    ]);

    $variants = collect();

    foreach ($values as $index => $valueName) {
        $value = ProductOptionValue::create([
            'store_id' => $store->id,
            'product_option_id' => $option->id,
            'value' => $valueName,
            'sort_order' => $index,
        ]);

        $variant = ProductVariant::create([
            'store_id' => $store->id,
            'product_id' => $product->id,
            'name' => $valueName,
            'sku' => 'OP-' . uniqid(),
            'price' => 1000,
            'stock' => 5,
        ]);

        $variant->optionValues()->sync([$value->id => ['product_option_id' => $option->id]]);

        $variants->push($variant);
    }

    return [$option, $variants];
}

function matrixComponent(Store $store, Product $product)
{
    config(['app.domain' => 'example.test']);
    test()->artisan('view:clear');
    test()->withSession(['current_store_id' => $store->id]);

    return \Livewire\Volt\Volt::test('storefront.variant-matrix', ['productId' => (string) $product->id]);
}

test('radio option renders an exclusive single-dimension group', function () {
    $store = makeMatrixStore();
    $product = makeMatrixProduct($store);
    [$option, $variants] = makeOptionWithVariants($store, $product, 'radio', ['Red', 'Blue']);

    $html = matrixComponent($store, $product)
        ->assertSee(__('storefront.variants_matrix_title'))
        ->html();

    expect($html)->toContain('data-exclusive-group="' . $option->id . '"')
        ->and($html)->toContain('data-matrix-root')
        ->and($html)->not->toContain('data-exclusive-group="combo"');
});

test('exclusive radio rejects picking two values server-side', function () {
    $store = makeMatrixStore();
    $product = makeMatrixProduct($store);
    [, $variants] = makeOptionWithVariants($store, $product, 'radio', ['Red', 'Blue']);

    matrixComponent($store, $product)
        ->set('quantities.' . $variants[0]->id, 1)
        ->set('quantities.' . $variants[1]->id, 1)
        ->call('addAllToCart')
        ->assertDispatched('swal', type: 'error');

    expect(app(CartService::class)->getCount($store->id))->toBe(0);
});

test('checkbox options allow multiple picks with quantities', function () {
    $store = makeMatrixStore();
    $product = makeMatrixProduct($store);
    [, $variants] = makeOptionWithVariants($store, $product, 'checkbox', ['Red', 'Blue']);

    matrixComponent($store, $product)
        ->set('quantities.' . $variants[0]->id, 2)
        ->set('quantities.' . $variants[1]->id, 3)
        ->call('addAllToCart')
        ->assertDispatched('cart-updated');

    expect(app(CartService::class)->getCount($store->id))->toBe(5);
});

test('product level minimum order quantity is enforced', function () {
    $store = makeMatrixStore();
    $product = makeMatrixProduct($store, ['min_order_qty' => 2]);
    [, $variants] = makeOptionWithVariants($store, $product, 'checkbox', ['Red']);

    matrixComponent($store, $product)
        ->set('quantities.' . $variants[0]->id, 1)
        ->call('addAllToCart')
        ->assertDispatched('swal', type: 'error');

    expect(app(CartService::class)->getCount($store->id))->toBe(0);

    matrixComponent($store, $product)
        ->set('quantities.' . $variants[0]->id, 2)
        ->call('addAllToCart');

    expect(app(CartService::class)->getCount($store->id))->toBe(2);
});

test('store level defaults apply when the product has no override', function () {
    $store = makeMatrixStore();
    $store->settings()->updateOrCreate([], ['min_order_qty' => 3]);

    $product = makeMatrixProduct($store);
    [, $variants] = makeOptionWithVariants($store, $product, 'checkbox', ['Red']);

    matrixComponent($store, $product)
        ->set('quantities.' . $variants[0]->id, 2)
        ->call('addAllToCart')
        ->assertDispatched('swal', type: 'error');

    expect(app(CartService::class)->getCount($store->id))->toBe(0);
});

test('maximum order quantity clamps oversized requests', function () {
    $store = makeMatrixStore();
    $product = makeMatrixProduct($store, ['max_order_qty' => 3]);
    [, $variants] = makeOptionWithVariants($store, $product, 'checkbox', ['Red']);

    matrixComponent($store, $product)
        ->set('quantities.' . $variants[0]->id, 10)
        ->call('addAllToCart');

    expect(app(CartService::class)->getItems($store->id)->first()['quantity'])->toBe(3);
});

test('stock caps lines until backorder is enabled', function () {
    $store = makeMatrixStore();
    $product = makeMatrixProduct($store);

    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Only',
        'sku' => 'ST-' . uniqid(),
        'price' => 500,
        'stock' => 2,
    ]);

    matrixComponent($store, $product)
        ->set('quantities.' . $variant->id, 10)
        ->call('addAllToCart');

    expect(app(CartService::class)->getItems($store->id)->first()['quantity'])->toBe(2);

    // Merchant enables backorder in the inventory tab.
    $store->settings()->updateOrCreate([], ['allow_backorder' => true]);
    app()->forgetInstance(CartService::class);

    matrixComponent($store, $product)
        ->set('quantities.' . $variant->id, 10)
        ->call('addAllToCart');

    expect((int) app(CartService::class)->getItems($store->id)->sum('quantity'))->toBe(12);
});

test('backorder shows a pre-order badge instead of blocking sold out rows', function () {
    $store = makeMatrixStore();
    $store->settings()->updateOrCreate([], ['allow_backorder' => true]);

    $product = makeMatrixProduct($store);
    ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Ghost',
        'sku' => 'GH-' . uniqid(),
        'price' => 500,
        'stock' => 0,
    ]);

    $html = matrixComponent($store, $product)
        ->assertSee(__('storefront.pre_order'))
        ->html();

    expect($html)->toContain(__('storefront.pre_order'));
});

test('inventory tracking disabled removes all stock caps', function () {
    $store = makeMatrixStore();
    $store->settings()->updateOrCreate([], ['inventory_tracking' => false]);

    $product = makeMatrixProduct($store);
    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Untracked',
        'sku' => 'UT-' . uniqid(),
        'price' => 500,
        'stock' => 1,
    ]);

    matrixComponent($store, $product)
        ->set('quantities.' . $variant->id, 25)
        ->call('addAllToCart');

    expect(app(CartService::class)->getItems($store->id)->first()['quantity'])->toBe(25);
});

test('direct buy now enforces limits before jumping to checkout', function () {
    $store = makeMatrixStore();
    $product = makeMatrixProduct($store, ['min_order_qty' => 2]);
    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Solo',
        'sku' => 'DN-' . uniqid(),
        'price' => 900,
        'stock' => 8,
    ]);

    matrixComponent($store, $product)
        ->set('direct', true)
        ->set('quantities.' . $variant->id, 1)
        ->call('buyNowFromMatrix')
        ->assertDispatched('swal', type: 'error');

    expect(app(CartService::class)->getCount($store->id))->toBe(0);

    matrixComponent($store, $product)
        ->set('direct', true)
        ->set('quantities.' . $variant->id, 2)
        ->call('buyNowFromMatrix')
        ->assertRedirect(route('storefront.checkout', ['store' => $store->slug]));

    expect(app(CartService::class)->getCount($store->id))->toBe(2);
});
