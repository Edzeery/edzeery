<?php

use App\Domains\Cart\Services\CartService;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Stores\Store;

/**
 * Checkout access control & buyer identity:
 *  - The /checkout route requires a non-empty cart: an empty cart redirects
 *    back to the store home instead of rendering a dead form.
 *  - The buyer's identity (name/phone/email) is remembered per store in the
 *    session after a successful order and pre-filled on the next checkout;
 *    a logged-in platform user's own profile still takes precedence.
 */
function cawStore(array $settings = []): Store
{
    $user = \App\Models\User::factory()->create();

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Access Store',
        'slug' => 'caw-'.uniqid(),
        'status' => 'active',
        'landing_template' => 'catalog',
    ]);

    if ($settings !== []) {
        $store->settings()->updateOrCreate([], $settings);
    }

    config(['app.domain' => 'example.test']);

    return $store;
}

function cawProduct(Store $store): Product
{
    return Product::create([
        'store_id' => $store->id,
        'name' => 'Access Product',
        'slug' => 'cawp-'.uniqid(),
        'sku' => 'CAWP-'.uniqid(),
        'type' => 'variable',
        'price' => 500,
        'is_active' => true,
    ]);
}

function cawVariant(Store $store, Product $product, int $stock = 10): ProductVariant
{
    return ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'CAWV-'.uniqid(),
        'price' => 500,
        'stock' => $stock,
    ]);
}

test('checkout redirects to the store home when the cart is empty', function () {
    $store = cawStore();

    test()->withSession(['current_store_id' => $store->id]);

    test()->get('http://'.$store->slug.'.example.test/checkout')
        ->assertRedirect(route('storefront.home', ['store' => $store->slug]));
});

test('checkout renders normally while the cart has items', function () {
    $store = cawStore();
    $variant = cawVariant($store, cawProduct($store));

    test()->withSession(['current_store_id' => $store->id]);
    app(CartService::class)->addItem($store->id, $variant->id, 1);

    test()->get('http://'.$store->slug.'.example.test/checkout')
        ->assertOk();
});

test('checkout prefills the buyer identity remembered in the session', function () {
    $store = cawStore();
    cawVariant($store, cawProduct($store));

    $remembered = [
        'name' => 'Returning Customer',
        'phone' => '0551234567',
        'email' => 'returning@example.test',
    ];

    test()->withSession([
        'current_store_id' => $store->id,
        'storefront_customer_' . $store->id => $remembered,
    ]);

    \Livewire\Volt\Volt::test('storefront.order-form')
        ->assertSet('name', 'Returning Customer')
        ->assertSet('phone', '0551234567')
        ->assertSet('email', 'returning@example.test');
});

test('a successful order remembers the buyer for their next checkout', function () {
    $country = Country::create(['name' => 'Memoryland', 'code' => 'ME', 'is_active' => true]);
    $state = State::create([
        'country_id' => $country->id,
        'state_code' => 'ME-01',
        'name' => 'Memory State',
        'is_active' => true,
        'is_cod_available' => true,
    ]);
    $city = City::create(['state_id' => $state->id, 'name' => 'Memory City', 'post_code' => '0000', 'is_active' => true]);

    $store = cawStore();
    $variant = cawVariant($store, cawProduct($store));

    test()->artisan('db:seed', ['--class' => \Database\Seeders\SystemStatusesSeeder::class, '--force' => true]);
    test()->withSession(['current_store_id' => $store->id]);
    app(CartService::class)->addItem($store->id, $variant->id, 2);

    \Livewire\Volt\Volt::test('storefront.order-form')
        ->set('name', 'Loyal Customer')
        ->set('phone', '0559998888')
        ->set('email', 'loyal@example.test')
        ->set('delivery_type', 'home')
        ->set('state_id', (string) $state->id)
        ->set('city_id', (string) $city->id)
        ->set('address', 'Memory Street')
        ->set('payment_method', 'cod')
        ->call('submitOrder')
        ->assertHasNoErrors();

    expect(Order::where('store_id', $store->id)->count())->toBe(1)
        ->and(session('storefront_customer_' . $store->id))->toMatchArray([
            'name' => 'Loyal Customer',
            'phone' => '0559998888',
            'email' => 'loyal@example.test',
        ]);
});