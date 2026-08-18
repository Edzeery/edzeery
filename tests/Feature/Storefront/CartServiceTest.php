<?php

use App\Domains\Cart\Services\CartService;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Stores\Store;
use App\Models\User;

use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(PlansSeeder::class);

    $this->user = User::factory()->create();
    $this->store = Store::create([
        'user_id' => $this->user->id,
        'name' => 'Test Store',
        'slug' => 'test-store-' . uniqid(),
        'status' => 'active',
    ]);

    $this->product = Product::create([
        'store_id' => $this->store->id,
        'name' => 'Test Product',
        'slug' => 'test-product-' . uniqid(),
        'sku' => 'SKU-' . uniqid(),
        'type' => 'simple',
        'price' => 1500,
        'is_active' => true,
    ]);

    $this->variant = ProductVariant::create([
        'product_id' => $this->product->id,
        'store_id' => $this->store->id,
        'name' => 'Default',
        'sku' => 'SKU-V-' . uniqid(),
        'price' => 1500,
        'stock' => 10,
        'is_active' => true,
        'is_default' => true,
    ]);

    $this->cart = app(CartService::class);
    $this->storeId = $this->store->id;
});

test('add item to cart', function () {
    $cart = $this->cart->addItem($this->storeId, $this->variant->id, 2);

    expect($cart['items'])->toHaveKey($this->variant->id);
    expect($cart['items'][$this->variant->id]['quantity'])->toBe(2);
    expect($cart['items'][$this->variant->id]['price'])->toBe(1500.0);
});

test('get count returns sum of quantities', function () {
    $this->cart->addItem($this->storeId, $this->variant->id, 3);

    expect($this->cart->getCount($this->storeId))->toBe(3);
});

test('get subtotal sums price * quantity', function () {
    $this->cart->addItem($this->storeId, $this->variant->id, 2);

    expect($this->cart->getSubtotal($this->storeId))->toBe(3000.0);
});

test('get total adds shipping cost', function () {
    $this->cart->addItem($this->storeId, $this->variant->id, 1);

    expect($this->cart->getTotal($this->storeId, 500))->toBe(2000.0);
});

test('update quantity', function () {
    $this->cart->addItem($this->storeId, $this->variant->id, 1);
    $this->cart->updateQuantity($this->storeId, $this->variant->id, 5);

    expect($this->cart->getCount($this->storeId))->toBe(5);
});

test('update quantity to 0 removes item', function () {
    $this->cart->addItem($this->storeId, $this->variant->id, 3);
    $this->cart->updateQuantity($this->storeId, $this->variant->id, 0);

    expect($this->cart->isEmpty($this->storeId))->toBeTrue();
});

test('remove item', function () {
    $this->cart->addItem($this->storeId, $this->variant->id, 2);
    $this->cart->removeItem($this->storeId, $this->variant->id);

    expect($this->cart->isEmpty($this->storeId))->toBeTrue();
});

test('clear entire cart', function () {
    $this->cart->addItem($this->storeId, $this->variant->id, 2);
    $this->cart->clear($this->storeId);

    expect($this->cart->isEmpty($this->storeId))->toBeTrue();
});

test('stock cap prevents over-ordering', function () {
    $this->variant->update(['stock' => 3]);
    $this->cart->addItem($this->storeId, $this->variant->id, 10);

    expect($this->cart->getCount($this->storeId))->toBe(3);
});

test('per-store isolation', function () {
    $store2 = Store::create([
        'user_id' => $this->user->id,
        'name' => 'Store 2',
        'slug' => 'store-2-' . uniqid(),
        'status' => 'active',
    ]);

    $product2 = Product::create([
        'store_id' => $store2->id,
        'name' => 'Store 2 Product',
        'slug' => 'store-2-product-' . uniqid(),
        'sku' => 'SKU-P2-' . uniqid(),
        'type' => 'simple',
        'price' => 2000,
        'is_active' => true,
    ]);

    $variant2 = ProductVariant::create([
        'product_id' => $product2->id,
        'store_id' => $store2->id,
        'name' => 'Default',
        'sku' => 'SKU-V2-' . uniqid(),
        'price' => 2000,
        'stock' => 5,
        'is_active' => true,
        'is_default' => true,
    ]);

    $this->cart->addItem($this->storeId, $this->variant->id, 2);
    $this->cart->addItem($store2->id, $variant2->id, 5);

    expect($this->cart->getCount($this->storeId))->toBe(2);
    expect($this->cart->getCount($store2->id))->toBe(5);
});

test('is empty when no items', function () {
    expect($this->cart->isEmpty($this->storeId))->toBeTrue();
});

test('is not empty after adding item', function () {
    $this->cart->addItem($this->storeId, $this->variant->id, 1);

    expect($this->cart->isEmpty($this->storeId))->toBeFalse();
});

test('to array includes count and subtotal', function () {
    $this->cart->addItem($this->storeId, $this->variant->id, 3);
    $array = $this->cart->toArray($this->storeId);

    expect($array)->toHaveKeys(['items', 'coupon_code', 'count', 'subtotal']);
    expect($array['count'])->toBe(3);
    expect($array['subtotal'])->toBe(4500.0);
});

test('apply coupon persists coupon code', function () {
    $this->cart->addItem($this->storeId, $this->variant->id, 1);
    $this->cart->applyCoupon($this->storeId, 'SAVE10');

    $array = $this->cart->toArray($this->storeId);
    expect($array['coupon_code'])->toBe('SAVE10');
});

test('add item aborts 404 for invalid variant', function () {
    $this->cart->addItem($this->storeId, '01HXYZ000000000000000000000', 1);
})->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
