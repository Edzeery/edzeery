<?php

use App\Domains\Cart\Services\CartService;
use App\Models\InventoryMovement;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Stores\Store;

/**
 * Storefront cart/order limits outside the variant matrix:
 *  - Catalog / Brand quick-add and mini-cart go through CartService, so the
 *    shared OrderRules (min bump, max cap, stock policy) must apply there too.
 *  - A successful checkout decreases variant stock and records a SALE movement.
 *  - Priority: product override -> store default -> ignored.
 */

function colStore(array $settings = []): Store
{
    $user = \App\Models\User::factory()->create();

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Limits Store',
        'slug' => 'lim-' . uniqid(),
        'status' => 'active',
        'landing_template' => 'catalog',
    ]);

    if ($settings !== []) {
        $store->settings()->updateOrCreate([], $settings);
    }

    config(['app.domain' => 'example.test']);
    test()->withSession(['current_store_id' => $store->id]);

    return $store;
}

function colProduct(Store $store, array $overrides = []): Product
{
    return Product::create(array_merge([
        'store_id' => $store->id,
        'name' => 'Limits Product',
        'slug' => 'lpr-' . uniqid(),
        'sku' => 'LPR-' . uniqid(),
        'type' => 'variable',
        'price' => 500,
        'is_active' => true,
    ], $overrides));
}

function colVariant(Store $store, Product $product, int $stock = 10): ProductVariant
{
    return ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'LVR-' . uniqid(),
        'price' => 500,
        'stock' => $stock,
    ]);
}

function colCart(Store $store): CartService
{
    test()->artisan('view:clear');

    return app(CartService::class);
}

function colCheckout(): void
{
    test()->artisan('db:seed', ['--class' => \Database\Seeders\SystemStatusesSeeder::class, '--force' => true]);
}

test('quick add raises a brand-new line to the store default minimum', function () {
    $store = colStore(['min_order_qty' => 3]);
    $variant = colVariant($store, colProduct($store));

    $cart = colCart($store)->addItem($store->id, $variant->id, 1);

    expect($cart['items'][$variant->id]['quantity'])->toBe(3)
        ->and(colCart($store)->getCount($store->id))->toBe(3)
        ->and(colCart($store)->takeNotice())->toMatchArray(['key' => 'order_limit_min', 'limit' => 3]);
});

test('merchant max order qty still caps when inventory tracking is off', function () {
    $store = colStore(['inventory_tracking' => false]);
    // Store default max is high; product override wins with a tight cap.
    $product = colProduct($store, ['max_order_qty' => 2]);
    $variant = colVariant($store, $product, 100);

    $cart = colCart($store);

    for ($i = 0; $i < 5; $i++) {
        $cart->addItem($store->id, $variant->id, 1);
    }

    expect($cart->getCount($store->id))->toBe(2)
        ->and($cart->takeNotice())->toMatchArray(['key' => 'order_limit_max', 'limit' => 2]);
});

test('mini-cart quantity updates snap into the min..cap window', function () {
    $store = colStore(['min_order_qty' => 3, 'max_order_qty' => 8]);
    $variant = colVariant($store, colProduct($store), 50);

    $cart = colCart($store);
    $cart->addItem($store->id, $variant->id, 1);

    // Below minimum: snapped up.
    $cart->updateQuantity($store->id, $variant->id, 1);
    expect($cart->getItems($store->id)->first()['quantity'])->toBe(3)
        ->and($cart->takeNotice()['key'])->toBe('order_limit_min');

    // Above maximum: clamped down.
    $cart->updateQuantity($store->id, $variant->id, 20);
    expect($cart->getItems($store->id)->first()['quantity'])->toBe(8)
        ->and($cart->takeNotice()['key'])->toBe('order_limit_max');
});

test('successful checkout creates pending order; confirmation reserves stock', function () {
    $country = Country::create(['name' => 'Testland', 'code' => 'TS', 'is_active' => true]);
    $state = State::create([
        'country_id' => $country->id,
        'state_code' => 'TS-01',
        'name' => 'Test State',
        'is_active' => true,
        'is_cod_available' => true,
    ]);
    $city = City::create(['state_id' => $state->id, 'name' => 'Test City', 'post_code' => '0000', 'is_active' => true]);

    $store = colStore();
    $product = colProduct($store);
    $variant = colVariant($store, $product, 10);

    colCheckout();

    colCart($store)->addItem($store->id, $variant->id, 3);

    \Livewire\Volt\Volt::test('storefront.order-form')
        ->set('name', 'Walk-in Customer')
        ->set('phone', '0550000000')
        ->set('state_id', (string) $state->id)
        ->set('city_id', (string) $city->id)
        ->set('address', 'Street 1')
        ->set('delivery_type', 'home')
        ->set('payment_method', 'cod')
        ->call('submitOrder')
        ->assertHasNoErrors();

    $order = Order::where('store_id', $store->id)->latest('id')->first();

    // pending → no inventory movement yet
    expect($order)->not->toBeNull()
        ->and($order?->items()->count())->toBe(1)
        ->and($variant->fresh()->stock)->toBe(10)
        ->and(InventoryMovement::where('product_variant_id', $variant->id)->count())->toBe(0);

    // confirm → RESERVE (stock decreases)
    app(\App\Domains\Order\Services\OrderService::class)->confirm($order);

    expect($variant->fresh()->stock)->toBe(7);

    $movement = InventoryMovement::where('product_variant_id', $variant->id)->latest('id')->first();

    expect($movement)->not->toBeNull()
        ->and($movement?->type->value)->toBe('reserve')
        ->and($movement?->quantity)->toBe(3)
        ->and((int) $movement?->balance_after)->toBe(7)
        ->and($movement?->source_type)->toBe(Order::class)
        ->and($movement?->source_id)->toBe($order?->id);
});

test('backorder checkout beyond stock succeeds without touching the ledger', function () {
    $country = Country::create(['name' => 'Backorderland', 'code' => 'BL', 'is_active' => true]);
    $state = State::create([
        'country_id' => $country->id,
        'state_code' => 'BL-01',
        'name' => 'Backorder State',
        'is_active' => true,
        'is_cod_available' => true,
    ]);
    $city = City::create(['state_id' => $state->id, 'name' => 'Backorder City', 'post_code' => '0000', 'is_active' => true]);

    $store = colStore(['allow_backorder' => true]);
    $variant = colVariant($store, colProduct($store), 2);

    colCheckout();

    colCart($store)->addItem($store->id, $variant->id, 5);

    \Livewire\Volt\Volt::test('storefront.order-form')
        ->set('name', 'Patient Customer')
        ->set('phone', '0551111111')
        ->set('state_id', (string) $state->id)
        ->set('city_id', (string) $city->id)
        ->set('address', 'Street 2')
        ->set('delivery_type', 'home')
        ->set('payment_method', 'cod')
        ->call('submitOrder')
        ->assertHasNoErrors();

    expect(Order::where('store_id', $store->id)->count())->toBe(1)
        ->and($variant->fresh()->stock)->toBe(2)
        ->and(InventoryMovement::where('product_variant_id', $variant->id)->count())->toBe(0);
});

test('inventory tracking off leaves stock and ledger untouched on checkout', function () {    $country = Country::create(['name' => 'Freeland', 'code' => 'FL', 'is_active' => true]);
    $state = State::create([
        'country_id' => $country->id,
        'state_code' => 'FL-01',
        'name' => 'Free State',
        'is_active' => true,
        'is_cod_available' => true,
    ]);
    $city = City::create(['state_id' => $state->id, 'name' => 'Free City', 'post_code' => '0000', 'is_active' => true]);

    $store = colStore(['inventory_tracking' => false]);
    $variant = colVariant($store, colProduct($store), 10);

    colCheckout();

    colCart($store)->addItem($store->id, $variant->id, 4);

    \Livewire\Volt\Volt::test('storefront.order-form')
        ->set('name', 'Untracked Customer')
        ->set('phone', '0552222222')
        ->set('state_id', (string) $state->id)
        ->set('city_id', (string) $city->id)
        ->set('address', 'Street 3')
        ->set('delivery_type', 'home')
        ->set('payment_method', 'cod')
        ->call('submitOrder')
        ->assertHasNoErrors();

    expect(Order::where('store_id', $store->id)->count())->toBe(1)
        ->and($variant->fresh()->stock)->toBe(10)
        ->and(InventoryMovement::where('product_variant_id', $variant->id)->count())->toBe(0);
});

test('limit adjustments travel through the edz-notice channel', function () {
    // One click on an empty cart must bump to the minimum AND warn.
    $store = colStore(['min_order_qty' => 3]);
    $variant = colVariant($store, colProduct($store), 50);

    \Livewire\Volt\Volt::test('storefront.templates.catalog')
        ->call('addToCart', $variant->id)
        ->assertDispatched('edz-notice')
        ->assertDispatched('cart-updated');

    expect(colCart($store)->getCount($store->id))->toBe(3);

    // Happy path stays silent: within limits => no notice event.
    $store2 = colStore();
    $variant2 = colVariant($store2, colProduct($store2), 50);

    \Livewire\Volt\Volt::test('storefront.templates.catalog')
        ->call('addToCart', $variant2->id)
        ->assertNotDispatched('edz-notice');
});

test('product page exposes the effective cap and disables increment at it', function () {
    config(['app.domain' => 'example.test']);

    $store = colStore(['max_order_qty' => 4]);
    $product = colProduct($store);
    colVariant($store, $product, 30);

    $html = test()->get('http://' . $store->slug . '.example.test/product/' . $product->slug)
        ->assertOk()
        ->getContent();

    // Cap travels inside the JSON payload (HTML-encoded attribute).
    expect($html)->toContain('&quot;cap&quot;')
        ->and($html)->toContain('maxQty !== null && $wire.quantity >= maxQty');
});

test('stopdesk without a state shows visible guidance instead of failing silently', function () {
    $country = Country::create(['name' => 'Deskland', 'code' => 'DL', 'is_active' => true]);

    $store = colStore();
    $variant = colVariant($store, colProduct($store), 5);

    colCheckout();

    colCart($store)->addItem($store->id, $variant->id, 1);

    $component = \Livewire\Volt\Volt::test('storefront.order-form')
        ->set('name', 'Desk Customer')
        ->set('phone', '0553333333')
        ->set('delivery_type', 'stopdesk')
        ->call('submitOrder');

    $component->assertHasErrors(['state_id', 'city_id']);

    // The guidance renders even though the desks list is empty.
    expect($component->html())->toContain(__('storefront.select_state_for_desks'));
});

test('stopdesk order with an active point succeeds and is linked', function () {
    $country = Country::create(['name' => 'Deskok', 'code' => 'DK', 'is_active' => true]);
    $state = State::create([
        'country_id' => $country->id,
        'state_code' => 'DK-01',
        'name' => 'Desk State',
        'is_active' => true,
        'is_cod_available' => true,
    ]);
    $city = City::create([
        'state_id' => $state->id,
        'name' => 'Desk City',
        'post_code' => '0000',
        'is_active' => true,
    ]);

    $store = colStore();
    $product = colProduct($store);
    $variant = colVariant($store, $product, 6);

    $point = \App\Domains\Shipping\Models\StopdeskPoint::create([
        'store_id' => $store->id,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'name' => 'Central Desk',
        'address' => 'Main Road 12',
        'is_active' => true,
    ]);

    colCheckout();

    colCart($store)->addItem($store->id, $variant->id, 2);

    \Livewire\Volt\Volt::test('storefront.order-form')
        ->set('name', 'Pickup Customer')
        ->set('phone', '0554444444')
        ->set('state_id', (string) $state->id)
        ->set('city_id', (string) $city->id)
        ->set('delivery_type', 'stopdesk')
        ->set('selectedStopdesk', (string) $point->id)
        ->set('payment_method', 'cod')
        ->call('submitOrder')
        ->assertHasNoErrors();

    $order = Order::where('store_id', $store->id)->latest('id')->first();

    expect($order)->not->toBeNull()
        ->and($order?->delivery_type)->toBe('stopdesk')
        ->and((string) $order?->stopdesk_point_id)->toBe((string) $point->id)
        ->and($variant->fresh()->stock)->toBe(6);

    // confirm → RESERVE
    app(\App\Domains\Order\Services\OrderService::class)->confirm($order);
    expect($variant->fresh()->stock)->toBe(4);
});

test('desk choice is optional and the list is scoped to the commune with carrier labels', function () {
    $country = Country::create(['name' => 'Communia', 'code' => 'CM', 'is_active' => true]);
    $state = State::create([
        'country_id' => $country->id,
        'state_code' => 'CM-01',
        'name' => 'Commune State',
        'is_active' => true,
        'is_cod_available' => true,
    ]);
    $city = City::create([
        'state_id' => $state->id,
        'name' => 'Target Commune',
        'post_code' => '0000',
        'is_active' => true,
    ]);
    $otherCity = City::create([
        'state_id' => $state->id,
        'name' => 'Far Commune',
        'post_code' => '0001',
        'is_active' => true,
    ]);

    $store = colStore();
    $product = colProduct($store);
    $variant = colVariant($store, $product, 3);

    $provider = \App\Domains\Shipping\Models\ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'Yalidine',
        'credentials' => [],
        'is_active' => true,
    ]);

    $nearDesk = \App\Domains\Shipping\Models\StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'name' => 'Near Desk',
        'address' => 'Near Road 1',
        'is_active' => true,
    ]);

    // Same wilaya, different commune: must never be offered.
    $farDesk = \App\Domains\Shipping\Models\StopdeskPoint::create([
        'store_id' => $store->id,
        'state_id' => $state->id,
        'city_id' => $otherCity->id,
        'name' => 'Far Desk',
        'address' => 'Far Road 9',
        'is_active' => true,
    ]);

    colCheckout();

    colCart($store)->addItem($store->id, $variant->id, 1);

    $component = \Livewire\Volt\Volt::test('storefront.order-form')
        ->set('delivery_type', 'stopdesk')
        ->set('state_id', (string) $state->id)
        ->set('city_id', (string) $city->id);

    $html = $component->html();

    expect($html)->toContain((string) $nearDesk->id)
        ->and($html)->toContain('Near Desk')
        ->and($html)->toContain('Yalidine')
        ->and($html)->not->toContain((string) $farDesk->id)
        ->and($html)->not->toContain('Far Desk');

    // Leaving the optional desk empty still completes a stopdesk order.
    \Livewire\Volt\Volt::test('storefront.order-form')
        ->set('name', 'Optional Desk Customer')
        ->set('phone', '0555555555')
        ->set('state_id', (string) $state->id)
        ->set('city_id', (string) $city->id)
        ->set('delivery_type', 'stopdesk')
        ->set('selectedStopdesk', '')
        ->set('payment_method', 'cod')
        ->call('submitOrder')
        ->assertHasNoErrors();

    $order = Order::where('store_id', $store->id)->latest('id')->first();

    expect($order?->delivery_type)->toBe('stopdesk')
        ->and($order?->stopdesk_point_id)->toBeNull()
        ->and($order?->shipping_provider_id)->toBeNull()
        ->and($variant->fresh()->stock)->toBe(3);

    // confirm → RESERVE
    app(\App\Domains\Order\Services\OrderService::class)->confirm($order);
    expect($variant->fresh()->stock)->toBe(2);
});
