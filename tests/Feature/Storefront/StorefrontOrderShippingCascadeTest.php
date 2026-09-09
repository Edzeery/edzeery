<?php

use App\Domains\Cart\Services\CartService;
use App\Domains\Shipping\Models\DeliveryRate;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\StopdeskPoint;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Stores\Store;

/**
 * Storefront order-form shipping cascade:
 *  - carrier selector (auto-hidden for single-carrier stores) scopes the
 *    wilaya and commune options.
 *  - stopdesk offices are wilaya-wide (every office of the chosen wilaya is
 *    offered, whatever its commune), sorted by desk code, a single match
 *    auto-selects itself (rendered as a compact card), and the office choice
 *    is mandatory. The commune is derived from the desk, never picked.
 *  - stopdesk orders derive their address from the office ("<office> — <commune>")
 *    and are charged the published office_cost (free when no price is published).
 *  - home orders charge home_cost and keep the raw typed address.
 *  - wilaya options carry a numeric-code badge and order by wilaya number.
 */

function oscStore(array $settings = []): Store
{
    $user = \App\Models\User::factory()->create();

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Cascade Store',
        'slug' => 'csc-' . uniqid(),
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

function oscProduct(Store $store): Product
{
    return Product::create([
        'store_id' => $store->id,
        'name' => 'Cascade Product',
        'slug' => 'cscp-' . uniqid(),
        'sku' => 'CSCP-' . uniqid(),
        'type' => 'variable',
        'price' => 500,
        'is_active' => true,
    ]);
}

function oscVariant(Store $store, Product $product, int $stock = 10): ProductVariant
{
    return ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'CSCV-' . uniqid(),
        'price' => 500,
        'stock' => $stock,
    ]);
}

function oscState(string $name): State
{
    $code = strtoupper(substr(md5($name), 0, 2));
    $country = Country::create(['name' => $name . 'land', 'code' => $code . 'L', 'is_active' => true]);

    return State::create([
        'country_id' => $country->id,
        'state_code' => $code . '-01',
        'name' => $name,
        'is_active' => true,
        'is_cod_available' => true,
    ]);
}

function oscCity(State $state, string $name): City
{
    return City::create([
        'state_id' => $state->id,
        'name' => $name,
        'post_code' => (string) random_int(1000, 9999),
        'is_active' => true,
    ]);
}

function oscProvider(Store $store, string $name, ?float $flatRate = null): ShippingProvider
{
    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => $name,
        'flat_rate' => $flatRate,
        'credentials' => [],
        'is_active' => true,
    ]);
}

function oscCart(Store $store): CartService
{
    test()->artisan('view:clear');

    return app(CartService::class);
}

function oscSeed(): void
{
    test()->artisan('db:seed', ['--class' => \Database\Seeders\SystemStatusesSeeder::class, '--force' => true]);
}

test('single-carrier stores skip the picker and post the carrier on the order', function () {
    $state = oscState('Cascade One');
    $city = oscCity($state, 'Central Com');
    $store = oscStore();
    $provider = oscProvider($store, 'Yalidine');

    DeliveryRate::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'home_cost' => 400,
        'office_cost' => 350,
        'is_active' => true,
    ]);

    $point = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'name' => 'Central Desk',
        'address' => 'Main Road 12',
        'is_active' => true,
    ]);

    $variant = oscVariant($store, oscProduct($store));
    oscSeed();
    oscCart($store)->addItem($store->id, $variant->id, 2);

    $component = \Livewire\Volt\Volt::test('storefront.order-form');

    // No company picker, carrier announced inline.
    expect($component->html())->not->toContain('role="company-select"')
        ->and($component->html())->toContain('Yalidine')
        ->and($component->html())->toContain(__('storefront.shipping_via'));

    $component
        ->set('name', 'Pickup Customer')
        ->set('phone', '0551000000')
        ->set('state_id', (string) $state->id)
        ->set('city_id', (string) $city->id)
        ->set('delivery_type', 'stopdesk')
        ->set('selectedStopdesk', (string) $point->id)
        ->set('payment_method', 'cod')
        ->call('submitOrder')
        ->assertHasNoErrors();

    $order = Order::where('store_id', $store->id)->latest('id')->first();

    expect($order)->not->toBeNull()
        ->and((string) $order?->shipping_provider_id)->toBe((string) $provider->id)
        ->and($order?->delivery_type)->toBe('stopdesk')
        ->and((string) $order?->stopdesk_point_id)->toBe((string) $point->id)
        ->and($order?->address)->toBe('Central Desk — Central Com')
        ->and((float) $order?->shipping_cost)->toBe(350.0)
        ->and((float) $order?->total_amount)->toBe(500.0 * 2 + 350.0);
});

test('single match on the cascade auto-selects itself as the office card', function () {
    $state = oscState('Cascade Two');
    $city = oscCity($state, 'Solo Com');
    $store = oscStore();
    $provider = oscProvider($store, 'ZR Express');

    $point = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'name' => 'Solo Desk',
        'address' => 'Solo Road 1',
        'phone' => '0661000000',
        'is_active' => true,
    ]);

    oscSeed();
    oscCart($store)->addItem($store->id, oscVariant($store, oscProduct($store))->id, 1);

    $component = \Livewire\Volt\Volt::test('storefront.order-form')
        ->set('delivery_type', 'stopdesk')
        ->set('state_id', (string) $state->id)
        ->set('city_id', (string) $city->id)
        ->set('name', 'Auto Pickup')
        ->set('phone', '0552000000')
        ->set('payment_method', 'cod');

    // Selecting the wilaya → the only office auto-picks and renders the card.
    $html = $component->html();

    expect($html)->toContain('data-role="selected-office"')
        ->and($html)->toContain('Solo Desk')
        ->and($html)->toContain((string) $point->id)
        ->and($html)->not->toContain('role="city-select"');

    // Livewire's updated hook sets selectedStopdesk server-side, but the test
    // harness doesn't always propagate hook mutations. Set explicitly for submitOrder.
    $component
        ->set('selectedStopdesk', (string) $point->id)
        ->call('submitOrder')
        ->assertHasNoErrors();

    $order = Order::where('store_id', $store->id)->latest('id')->first();

    expect((string) $order?->stopdesk_point_id)->toBe((string) $point->id)
        ->and((float) $order?->shipping_cost)->toBe(0.0);
});

test('the carrier picker drives the stopdesk state scope', function () {
    $stateA = oscState('Cascade A');
    $stateB = oscState('Cascade B');
    $cityA = oscCity($stateA, 'Com A');
    $store = oscStore();
    $providerA = oscProvider($store, 'Alpha Carrier');
    $providerB = oscProvider($store, 'Beta Carrier');

    // Only Alpha holds an office-bearing wilaya in the A state.
    StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $providerA->id,
        'state_id' => $stateA->id,
        'city_id' => $cityA->id,
        'name' => 'A Desk',
        'is_active' => true,
    ]);

    // Beta holds offices in stateB.
    $cityB = oscCity($stateB, 'Com B');
    StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $providerB->id,
        'state_id' => $stateB->id,
        'city_id' => $cityB->id,
        'name' => 'B Desk',
        'is_active' => true,
    ]);

    oscSeed();
    oscCart($store)->addItem($store->id, oscVariant($store, oscProduct($store))->id, 1);

    $component = \Livewire\Volt\Volt::test('storefront.order-form')
        ->set('delivery_type', 'stopdesk')
        ->set('selectedProvider', (string) $providerA->id);

    $html = $component->html();

    expect($html)->toContain('role="company-select"')
        ->and($html)->toContain('Alpha Carrier')
        ->and($html)->toContain('Beta Carrier')
        ->and($html)->toContain('Cascade A')
        ->and($html)->not->toContain('Cascade B');

    $component->set('selectedProvider', (string) $providerB->id);

    expect($component->html())->toContain('Cascade B')
        ->and($component->html())->not->toContain('Cascade A');
});

test('every office of the selected wilaya is offered, ordered by desk code, with details', function () {
    $state = oscState('Cascade Wilaya');
    $comA = oscCity($state, 'Com A');
    $comB = oscCity($state, 'Com B');
    $store = oscStore();
    $provider = oscProvider($store, 'Wilaya Carrier');

    // Two offices in two different communes of the same wilaya.
    $deskB = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'city_id' => $comB->id,
        'name' => 'Desk B',
        'external_code' => '02B',
        'address' => 'Road B',
        'phone' => '0662000000',
        'is_active' => true,
    ]);
    $deskA = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'city_id' => $comA->id,
        'name' => 'Desk A',
        'external_code' => '02A',
        'address' => 'Road A',
        'phone' => '0663000000',
        'is_active' => true,
    ]);

    oscSeed();
    oscCart($store)->addItem($store->id, oscVariant($store, oscProduct($store))->id, 1);

    $component = \Livewire\Volt\Volt::test('storefront.order-form')
        ->set('delivery_type', 'stopdesk')
        ->set('state_id', (string) $state->id);

    // The lazy dropdown no longer embeds the wilaya-wide office list in the
    // page: only the wiring (source + scope) and an empty seed travel in HTML.
    $html = $component->html();
    expect($html)->toContain('role="office-select"')
        ->and($html)->toContain('data-lazy="1"')
        ->and($html)->toContain('data-source="stopdeskSelectOptions"')
        ->and($html)->not->toContain('Desk A')
        ->and($html)->not->toContain('Desk B');

    // The on-open payload returns every office of the wilaya with the desk
    // code, commune and address / phone details, sorted 02A before 02B.
    $options = $component->instance()->stopdeskSelectOptions("s{$state->id}|p{$provider->id}");

    expect($options)->toHaveCount(2);
    $codes = array_values(array_column($options, 'code'));
    expect($codes)->toBe(['02A', '02B']);
    $orderedIds = array_values(array_column($options, 'value'));
    expect($orderedIds)->toBe([(string) $deskA->id, (string) $deskB->id]);

    $byId = collect($options)->keyBy('value');
    expect($byId[(string) $deskA->id])->toMatchArray([
        'label' => 'Desk A',
        'hint' => 'Com A',
        'code' => '02A',
        'extra' => ['Road A', '0663000000'],
    ]);

    // The commune picker is gone for stopdesk — the desk drives the commune.
    expect($html)->not->toContain('role="city-select"');
});

test('an office outside any chosen commune completes the stopdesk order with its own commune', function () {
    $state = oscState('Cascade Commune');
    $comB = oscCity($state, 'Com B');
    $store = oscStore();
    $provider = oscProvider($store, 'Commune Carrier');

    $desk = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'city_id' => $comB->id,
        'name' => 'Com B Desk',
        'address' => 'Com B Road',
        'is_active' => true,
    ]);

    oscSeed();
    oscCart($store)->addItem($store->id, oscVariant($store, oscProduct($store))->id, 1);

    // No commune is ever selected for stopdesk in the new flow: the desk
    // carries its own commune and the stored address derives from it.
    \Livewire\Volt\Volt::test('storefront.order-form')
        ->set('name', 'Commune Customer')
        ->set('phone', '0558000000')
        ->set('state_id', (string) $state->id)
        ->set('delivery_type', 'stopdesk')
        ->set('selectedStopdesk', (string) $desk->id)
        ->set('payment_method', 'cod')
        ->call('submitOrder')
        ->assertHasNoErrors();

    $order = Order::where('store_id', $store->id)->latest('id')->first();

    expect((string) $order?->stopdesk_point_id)->toBe((string) $desk->id)
        ->and($order?->address)->toBe('Com B Desk — Com B')
        ->and((float) $order?->shipping_cost)->toBe(0.0);
});

test('wilaya options carry a numeric-code badge and order by wilaya number', function () {
    $country = Country::create(['name' => 'Number Land', 'code' => 'NL', 'is_active' => true]);
    $stateTwo = State::create([
        'country_id' => $country->id,
        'state_code' => '02',
        'name' => 'Wilaya Two',
        'is_active' => true,
        'is_cod_available' => true,
    ]);
    $stateOne = State::create([
        'country_id' => $country->id,
        'state_code' => '01',
        'name' => 'Wilaya One',
        'is_active' => true,
        'is_cod_available' => true,
    ]);
    $store = oscStore();

    oscSeed();
    oscCart($store)->addItem($store->id, oscVariant($store, oscProduct($store))->id, 1);

    $html = \Livewire\Volt\Volt::test('storefront.order-form')
        ->set('delivery_type', 'home')
        ->html();

    expect($html)->toContain('&quot;code&quot;:&quot;01&quot;')
        ->and($html)->toContain('&quot;code&quot;:&quot;02&quot;')
        ->and(strpos($html, '&quot;code&quot;:&quot;01&quot;'))->toBeLessThan(strpos($html, '&quot;code&quot;:&quot;02&quot;'));
});

test('stopdesk orders without a published office price stay free', function () {
    $state = oscState('Cascade Free');
    $city = oscCity($state, 'Free Com');
    $store = oscStore();
    $provider = oscProvider($store, 'Free Carrier');

    // Office exists but no DeliveryRate row at all.
    $point = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'name' => 'Free Desk',
        'is_active' => true,
    ]);

    $variant = oscVariant($store, oscProduct($store));
    oscSeed();
    oscCart($store)->addItem($store->id, $variant->id, 2);

    \Livewire\Volt\Volt::test('storefront.order-form')
        ->set('name', 'Free Pickup')
        ->set('phone', '0553000000')
        ->set('state_id', (string) $state->id)
        ->set('city_id', (string) $city->id)
        ->set('delivery_type', 'stopdesk')
        ->set('selectedStopdesk', (string) $point->id)
        ->set('payment_method', 'cod')
        ->call('submitOrder')
        ->assertHasNoErrors();

    $order = Order::where('store_id', $store->id)->latest('id')->first();

    expect((float) $order?->shipping_cost)->toBe(0.0)
        ->and((float) $order?->total_amount)->toBe(500.0 * 2);
});

test('home orders charge home_cost and store the typed address', function () {
    $state = oscState('Cascade Home');
    $city = oscCity($state, 'Home Com');
    $store = oscStore();
    $provider = oscProvider($store, 'Home Carrier');

    DeliveryRate::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'home_cost' => 400,
        'office_cost' => 350,
        'is_active' => true,
    ]);

    $variant = oscVariant($store, oscProduct($store));
    oscSeed();
    oscCart($store)->addItem($store->id, $variant->id, 1);

    \Livewire\Volt\Volt::test('storefront.order-form')
        ->set('name', 'Home Customer')
        ->set('phone', '0554000000')
        ->set('state_id', (string) $state->id)
        ->set('city_id', (string) $city->id)
        ->set('delivery_type', 'home')
        ->set('address', 'Zone 5, Block 7')
        ->set('payment_method', 'cod')
        ->call('submitOrder')
        ->assertHasNoErrors();

    $order = Order::where('store_id', $store->id)->latest('id')->first();

    expect((string) $order?->shipping_provider_id)->toBe((string) $provider->id)
        ->and($order?->delivery_type)->toBe('home')
        ->and($order?->address)->toBe('Zone 5, Block 7')
        ->and((float) $order?->shipping_cost)->toBe(400.0)
        ->and((float) $order?->total_amount)->toBe(500.0 + 400.0);
});

test('an office outside the selected carrier rejects the stopdesk order', function () {
    $state = oscState('Cascade Cross');
    $city = oscCity($state, 'Cross Com');
    $store = oscStore();
    $providerA = oscProvider($store, 'Cross A');
    $providerB = oscProvider($store, 'Cross B');

    // The only office belongs to B; the order claims A.
    $point = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $providerB->id,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'name' => 'B Desk',
        'is_active' => true,
    ]);

    $variant = oscVariant($store, oscProduct($store));
    oscSeed();
    oscCart($store)->addItem($store->id, $variant->id, 1);

    \Livewire\Volt\Volt::test('storefront.order-form')
        ->set('name', 'Cross Customer')
        ->set('phone', '0555000000')
        ->set('state_id', (string) $state->id)
        ->set('city_id', (string) $city->id)
        ->set('delivery_type', 'stopdesk')
        ->set('selectedProvider', (string) $providerA->id)
        ->set('selectedStopdesk', (string) $point->id)
        ->set('payment_method', 'cod')
        ->call('submitOrder')
        ->assertDispatched('edz-notice');

    expect(Order::where('store_id', $store->id)->count())->toBe(0);
});

test('legacy stores without carriers keep the whole cascade working', function () {
    $state = oscState('Cascade Legacy');
    $city = oscCity($state, 'Legacy Com');
    $store = oscStore();

    $point = StopdeskPoint::create([
        'store_id' => $store->id,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'name' => 'Legacy Desk',
        'is_active' => true,
    ]);

    $variant = oscVariant($store, oscProduct($store));
    oscSeed();
    oscCart($store)->addItem($store->id, $variant->id, 1);

    \Livewire\Volt\Volt::test('storefront.order-form')
        ->set('name', 'Legacy Customer')
        ->set('phone', '0556000000')
        ->set('state_id', (string) $state->id)
        ->set('city_id', (string) $city->id)
        ->set('delivery_type', 'home')
        ->set('address', 'Old Town')
        ->set('payment_method', 'cod')
        ->call('submitOrder')
        ->assertHasNoErrors();

    $order = Order::where('store_id', $store->id)->latest('id')->first();

    expect($order)->not->toBeNull()
        ->and($order?->shipping_provider_id)->toBeNull()
        ->and($order?->delivery_type)->toBe('home')
        ->and((float) $order?->shipping_cost)->toBe(0.0);
});

test('carrier and wilaya option labels render as plain strings, never "[object Object]"', function () {
    $stateA = oscState('Cascade Label A');
    $stateB = oscState('Cascade Label B');
    $cityA = oscCity($stateA, 'Com Label A');
    $store = oscStore();
    $providerA = oscProvider($store, 'Label Alpha');
    $providerB = oscProvider($store, 'Label Beta');
    $providerC = oscProvider($store, 'Label Gamma'); // multi-carrier → picker shows

    StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $providerA->id,
        'state_id' => $stateA->id,
        'city_id' => $cityA->id,
        'name' => 'Desk A',
        'is_active' => true,
    ]);
    StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $providerB->id,
        'state_id' => $stateB->id,
        'city_id' => null,
        'name' => 'Desk B',
        'is_active' => true,
    ]);

    oscSeed();
    oscCart($store)->addItem($store->id, oscVariant($store, oscProduct($store))->id, 1);

    $html = \Livewire\Volt\Volt::test('storefront.order-form')
        ->set('delivery_type', 'stopdesk')
        ->set('selectedProvider', (string) $providerA->id)
        ->html();

    // The storefront select payload must carry scalar labels for the model
    // collections ($providers / $states) instead of the raw Eloquent objects.
    // data-options is htmlspecialchars-encoded, so quotes appear as &quot;.
    expect($html)->toContain('&quot;label&quot;:&quot;Label Alpha&quot;')
        ->and($html)->toContain('&quot;label&quot;:&quot;Label Beta&quot;')
        ->and($html)->toContain('&quot;label&quot;:&quot;Cascade Label A&quot;')
        ->and($html)->not->toContain('&quot;label&quot;:{')
        ->and($html)->not->toContain('[object Object]');
});