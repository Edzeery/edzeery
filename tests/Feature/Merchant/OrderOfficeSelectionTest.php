<?php

use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\StopdeskPoint;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function officeUser(string $storeRole = 'owner'): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Office Store',
        'slug' => 'office-'.uniqid(),
        'status' => 'active',
    ]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => $storeRole,
    ]);

    return [$user, $store];
}

function officeGeography(): array
{
    $country = Country::create([
        'name' => 'Algeria',
        'code' => 'DZ',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $state = State::create([
        'country_id' => $country->id,
        'state_code' => '16',
        'name' => 'Alger',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $city = City::create([
        'state_id' => $state->id,
        'name' => 'Bab Ezzouar',
        'post_code' => '16028',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    return [$state, $city];
}

function officeProvider(Store $store): ShippingProvider
{
    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'NOEST',
        'code' => 'noest',
        'credentials' => [],
        'is_active' => true,
        'is_default' => true,
    ]);
}

function officeVariant(Store $store, int $stock = 10): array
{
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Office Product',
        'slug' => 'office-pr-'.uniqid(),
        'sku' => 'office-sku-'.uniqid(),
        'type' => 'variable',
        'price' => 500,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'office-v-'.uniqid(),
        'price' => 500,
        'stock' => $stock,
    ]);

    return [$product, $variant];
}

function officePoint(Store $store, ShippingProvider $provider, State $state, City $city): StopdeskPoint
{
    return StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'name' => 'Point Bab Ezzouar',
        'address' => 'Cité 300 logts',
        'is_active' => true,
    ]);
}

function officeStopdeskOrder(Store $store, ShippingProvider $provider, StopdeskPoint $point, State $state, City $city): Order
{
    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Office Customer',
        'phone' => '0550000000',
        'status' => true,
    ]);

    $status = \App\Models\Status::system()->forType('order')->where('key', 'pending')->first();

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => $status?->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 500,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'delivery_type' => 'stopdesk',
        'shipping_provider_id' => $provider->id,
        'stopdesk_point_id' => $point->id,
        'payment_method' => 'cod',
        'shipping_cost' => 0,
    ]);

    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Office Order Product',
        'slug' => 'office-op-'.uniqid(),
        'sku' => 'office-op-sku-'.uniqid(),
        'type' => 'variable',
        'price' => 500,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'office-op-v-'.uniqid(),
        'price' => 500,
        'stock' => 10,
        'is_active' => true,
    ]);

    $order->items()->create([
        'store_id' => $store->id,
        'product_variant_id' => $variant->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => 500,
        'subtotal' => 500,
    ]);

    return $order->refresh();
}

function officeVolt(array $userStore)
{
    [$user, $store] = $userStore;
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    return Volt::test('merchant.orders.index');
}

test('a stopdesk order is created with the selected carrier and office persisted', function () {
    [$user, $store] = officeUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = officeGeography();
    $provider = officeProvider($store);
    $point = officePoint($store, $provider, $state, $city);
    [$product, $variant] = officeVariant($store);

    officeVolt([$user, $store])
        ->set('form', [
            'customer_name' => 'New Customer',
            'customer_phone' => '0550123456',
            'phone_secondary' => '',
            'address' => '',
            'state_id' => $state->id,
            'city_id' => $city->id,
            'delivery_type' => 'stopdesk',
            'shipping_provider_id' => $provider->id,
            'stopdesk_point_id' => $point->id,
            'shipment_type' => 'delivery',
            'payment_method' => 'cod',
            'discount_type' => null,
            'discount_value' => null,
            'discount_reason' => '',
            'notes' => '',
            'weight_kg' => '',
            'items' => [
                ['product_variant_id' => $variant->id, 'product_id' => $product->id, 'quantity' => 1, 'price' => 500],
            ],
        ])
        ->call('submitCreate')
        ->assertSet('showCreateModal', false)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success');

    $created = Order::where('store_id', $store->id)->latest('created_at')->first();

    expect($created)->not->toBeNull()
        ->and($created->delivery_type)->toBe(Order::DELIVERY_STOPDESK)
        ->and($created->shipping_provider_id)->toBe($provider->id)
        ->and($created->stopdesk_point_id)->toBe($point->id)
        ->and($created->shippingProvider->id)->toBe($provider->id)
        ->and($created->stopdeskPoint->id)->toBe($point->id);
});

test('a stopdesk order without an office is rejected and nothing is created', function () {
    [$user, $store] = officeUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = officeGeography();
    $provider = officeProvider($store);
    [$product, $variant] = officeVariant($store);

    $volt = officeVolt([$user, $store])
        ->set('form', [
            'customer_name' => 'New Customer',
            'customer_phone' => '0550123456',
            'phone_secondary' => '',
            'address' => '',
            'state_id' => $state->id,
            'city_id' => $city->id,
            'delivery_type' => 'stopdesk',
            'shipping_provider_id' => $provider->id,
            'stopdesk_point_id' => '',
            'shipment_type' => 'delivery',
            'payment_method' => 'cod',
            'discount_type' => null,
            'discount_value' => null,
            'discount_reason' => '',
            'notes' => '',
            'weight_kg' => '',
            'items' => [
                ['product_variant_id' => $variant->id, 'product_id' => $product->id, 'quantity' => 1, 'price' => 500],
            ],
        ])
        ->call('submitCreate');

    $volt->assertHasErrors(['stopdesk_point_id']);

    expect(Order::where('store_id', $store->id)->count())->toBe(0);
});

test('the edit modal restores the assigned carrier and office as picker options', function () {
    [$user, $store] = officeUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = officeGeography();
    $provider = officeProvider($store);
    $point = officePoint($store, $provider, $state, $city);
    $order = officeStopdeskOrder($store, $provider, $point, $state, $city);

    officeVolt([$user, $store])
        ->call('openEditModal', $order->id)
        ->assertSet('form.shipping_provider_id', $provider->id)
        ->assertSet('form.stopdesk_point_id', $point->id)
        ->assertSet('form.delivery_type', 'stopdesk');

    $volt = officeVolt([$user, $store])->call('openEditModal', $order->id);
    $offices = data_get($volt->get('formOffices'), '*.value');

    expect(collect($offices))->toContain($point->id);
});

test('switching an order to home delivery clears the persisted office', function () {
    [$user, $store] = officeUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = officeGeography();
    $provider = officeProvider($store);
    $point = officePoint($store, $provider, $state, $city);
    $order = officeStopdeskOrder($store, $provider, $point, $state, $city);

    officeVolt([$user, $store])
        ->call('openEditModal', $order->id)
        ->set('form.address', 'Rue Nationale 12')
        ->set('form.delivery_type', 'home')
        ->call('submitEdit')
        ->assertSet('showEditModal', false);

    expect($order->fresh()->delivery_type)->toBe('home')
        ->and($order->fresh()->stopdesk_point_id)->toBeNull();
});

test('a home-delivery order is created with the selected carrier and no office', function () {
    [$user, $store] = officeUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = officeGeography();
    $provider = officeProvider($store);
    [$product, $variant] = officeVariant($store);

    officeVolt([$user, $store])
        ->set('form', [
            'customer_name' => 'Home Customer',
            'customer_phone' => '0550987654',
            'phone_secondary' => '',
            'address' => 'Rue Didouche Mourad 45',
            'state_id' => $state->id,
            'city_id' => $city->id,
            'delivery_type' => 'home',
            'shipping_provider_id' => $provider->id,
            'stopdesk_point_id' => '',
            'shipment_type' => 'delivery',
            'payment_method' => 'cod',
            'discount_type' => null,
            'discount_value' => null,
            'discount_reason' => '',
            'notes' => '',
            'weight_kg' => '',
            'items' => [
                ['product_variant_id' => $variant->id, 'product_id' => $product->id, 'quantity' => 1, 'price' => 500],
            ],
        ])
        ->call('submitCreate')
        ->assertSet('showCreateModal', false)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success');

    $created = Order::where('store_id', $store->id)->latest('created_at')->first();

    expect($created)->not->toBeNull()
        ->and($created->delivery_type)->toBe('home')
        ->and($created->shipping_provider_id)->toBe($provider->id)
        ->and($created->stopdesk_point_id)->toBeNull()
        ->and($created->shippingProvider->id)->toBe($provider->id);
});

test('choosing a carrier for a home-delivery order does not load or require an office', function () {
    [$user, $store] = officeUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = officeGeography();
    $provider = officeProvider($store);
    officePoint($store, $provider, $state, $city);

    officeVolt([$user, $store])
        ->set('form.delivery_type', 'home')
        ->set('form.state_id', $state->id)
        ->set('form.city_id', $city->id)
        ->call('loadFormOffices', $provider->id)
        ->assertSet('form.shipping_provider_id', $provider->id)
        ->assertSet('formOffices', [])
        ->assertSet('form.stopdesk_point_id', '');
});

// ——— Delivery quick-edit modal (30.2) ———

function officeHomeOrder(Store $store, ShippingProvider $provider, State $state, City $city): Order
{
    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Home Customer',
        'phone' => '0551111111',
        'status' => true,
    ]);

    $status = \App\Models\Status::system()->forType('order')->where('key', 'pending')->first();

    return Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => $status?->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 500,
        'address' => 'Rue Nationale 12',
        'state_id' => $state->id,
        'city_id' => $city->id,
        'delivery_type' => 'home',
        'shipping_provider_id' => $provider->id,
        'stopdesk_point_id' => null,
        'payment_method' => 'cod',
        'shipping_cost' => 0,
    ]);
}

test('the delivery quick-edit modal restores carrier, office and destination', function () {
    [$user, $store] = officeUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = officeGeography();
    $provider = officeProvider($store);
    $point = officePoint($store, $provider, $state, $city);
    $order = officeStopdeskOrder($store, $provider, $point, $state, $city);

    $volt = officeVolt([$user, $store])
        ->call('openDeliveryModal', $order->id)
        ->assertSet('showDeliveryModal', true)
        ->assertSet('deliveryOrderId', $order->id)
        ->assertSet('form.shipping_provider_id', $provider->id)
        ->assertSet('form.stopdesk_point_id', $point->id)
        ->assertSet('form.delivery_type', 'stopdesk')
        ->assertSet('form.state_id', $state->id)
        ->assertSet('form.city_id', $city->id);

    $offices = data_get($volt->get('formOffices'), '*.value');
    expect(collect($offices))->toContain($point->id);
});

test('saving the delivery quick-edit modal switches a stopdesk order to home and clears the office', function () {
    [$user, $store] = officeUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = officeGeography();
    $provider = officeProvider($store);
    $point = officePoint($store, $provider, $state, $city);
    $order = officeStopdeskOrder($store, $provider, $point, $state, $city);

    officeVolt([$user, $store])
        ->call('openDeliveryModal', $order->id)
        ->set('form.delivery_type', 'home')
        ->call('changeDeliveryType', 'home')
        ->call('saveDeliveryModal')
        ->assertSet('showDeliveryModal', false)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success');

    expect($order->fresh()->delivery_type)->toBe('home')
        ->and($order->fresh()->stopdesk_point_id)->toBeNull()
        ->and($order->fresh()->shipping_provider_id)->toBe($provider->id);
});

test('saving the delivery quick-edit modal persists a new carrier and office', function () {
    [$user, $store] = officeUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = officeGeography();
    $provider = officeProvider($store);
    $point = officePoint($store, $provider, $state, $city);
    $order = officeStopdeskOrder($store, $provider, $point, $state, $city);

    $providerB = ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'YTUR',
        'code' => 'ytur',
        'credentials' => [],
        'is_active' => true,
    ]);
    $pointB = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $providerB->id,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'name' => 'Point YTUR Bab Ezzouar',
        'address' => 'Cité 300 logts',
        'is_active' => true,
    ]);

    officeVolt([$user, $store])
        ->call('openDeliveryModal', $order->id)
        ->set('form.shipping_provider_id', $providerB->id)
        ->call('loadFormOffices', $providerB->id)
        ->set('form.stopdesk_point_id', $pointB->id)
        ->call('saveDeliveryModal')
        ->assertSet('showDeliveryModal', false);

    expect($order->fresh()->shipping_provider_id)->toBe($providerB->id)
        ->and($order->fresh()->stopdesk_point_id)->toBe($pointB->id);
});

test('the delivery quick-edit modal rejects an office that does not match the carrier', function () {
    [$user, $store] = officeUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = officeGeography();
    $provider = officeProvider($store);
    $point = officePoint($store, $provider, $state, $city);
    $order = officeStopdeskOrder($store, $provider, $point, $state, $city);

    $otherProvider = ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'Other',
        'code' => 'other',
        'credentials' => [],
        'is_active' => true,
    ]);
    $otherPoint = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $otherProvider->id,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'name' => 'Other Point',
        'address' => '',
        'is_active' => true,
    ]);

    officeVolt([$user, $store])
        ->call('openDeliveryModal', $order->id)
        ->set('form.stopdesk_point_id', $otherPoint->id)
        ->call('saveDeliveryModal')
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'error');

    expect($order->fresh()->stopdesk_point_id)->toBe($point->id);
});

test('the delivery quick-edit modal is blocked for shipped orders', function () {
    [$user, $store] = officeUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = officeGeography();
    $provider = officeProvider($store);
    $point = officePoint($store, $provider, $state, $city);
    $order = officeStopdeskOrder($store, $provider, $point, $state, $city);

    $shipped = \App\Models\Status::system()->forType('order')->where('key', 'shipped')->first();
    if ($shipped) {
        $order->update(['status_id' => $shipped->id]);
    }

    officeVolt([$user, $store])
        ->call('openDeliveryModal', $order->id)
        ->assertSet('showDeliveryModal', false)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'error');
});

test('the delivery quick-edit modal merges the commune offices with the wilaya-wide hubs and excludes other communes', function () {
    [$user, $store] = officeUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = officeGeography();

    $cityB = City::create([
        'state_id' => $state->id,
        'name' => 'Cheraga',
        'post_code' => '16027',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $provider = officeProvider($store);
    $pointCityA = officePoint($store, $provider, $state, $city);
    $pointCityB = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'city_id' => $cityB->id,
        'name' => 'Point Cheraga',
        'address' => '',
        'is_active' => true,
    ]);
    $pointRegional = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'city_id' => null,
        'name' => 'Regional Hub',
        'address' => '',
        'is_active' => true,
    ]);

    $order = officeStopdeskOrder($store, $provider, $pointCityA, $state, $city);

    $volt = officeVolt([$user, $store])
        ->call('openDeliveryModal', $order->id)
        ->set('form.city_id', $cityB->id)
        ->call('rebuildFormOffices');

    $offerOfficeIds = collect(data_get($volt->get('formOffices'), '*.value'));

    expect($offerOfficeIds)->toContain($pointCityB->id)
        ->and($offerOfficeIds)->toContain($pointRegional->id)
        ->and($offerOfficeIds)->not->toContain($pointCityA->id);
});

// ——— 30.2: never revert a previously valid office silently ———

test('changing the commune to one without an office clears the selection with a visible toast', function () {
    [$user, $store] = officeUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = officeGeography();

    $cityB = City::create([
        'state_id' => $state->id,
        'name' => 'Cheraga',
        'post_code' => '16027',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $provider = officeProvider($store);
    $pointCityA = officePoint($store, $provider, $state, $city);
    $order = officeStopdeskOrder($store, $provider, $pointCityA, $state, $city);

    $volt = officeVolt([$user, $store])
        ->call('openDeliveryModal', $order->id)
        ->assertSet('form.stopdesk_point_id', $pointCityA->id)
        ->set('form.city_id', $cityB->id)
        ->call('rebuildFormOffices');

    expect($volt->get('form.stopdesk_point_id'))->toBe('');

    $volt->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'warning'
        && ($params[0]['title'] ?? null) === __('order_flow.office_reset_for_destination'));
});

test('changing the wilaya clears a no-longer-available office with a visible toast', function () {
    [$user, $store] = officeUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = officeGeography();

    $country = \App\Models\Locations\Country::firstOrNew(['code' => 'DZ'], ['name' => 'Algeria', 'is_active' => true, 'is_cod_available' => true]);
    $country->save();

    $stateB = State::create([
        'country_id' => $country->id,
        'state_code' => '31',
        'name' => 'Oran',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $cityB = City::create([
        'state_id' => $stateB->id,
        'name' => 'Oran City',
        'post_code' => '31000',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $provider = officeProvider($store);
    $pointCityA = officePoint($store, $provider, $state, $city);
    $order = officeStopdeskOrder($store, $provider, $pointCityA, $state, $city);

    $volt = officeVolt([$user, $store])
        ->call('openDeliveryModal', $order->id)
        ->assertSet('form.stopdesk_point_id', $pointCityA->id)
        ->set('form.state_id', $stateB->id)
        ->call('loadCities', (string) $stateB->id);

    expect($volt->get('form.stopdesk_point_id'))->toBe('');

    $volt->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'warning'
        && ($params[0]['title'] ?? null) === __('order_flow.office_reset_for_destination'));
});

test('a still-valid office is kept and no reset toast is emitted', function () {
    [$user, $store] = officeUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = officeGeography();

    $cityB = City::create([
        'state_id' => $state->id,
        'name' => 'Cheraga',
        'post_code' => '16027',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $provider = officeProvider($store);
    $pointCityA = officePoint($store, $provider, $state, $city);
    $pointCityB1 = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'city_id' => $cityB->id,
        'name' => 'Point Cheraga 1',
        'address' => '',
        'is_active' => true,
    ]);
    $pointCityB2 = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'city_id' => $cityB->id,
        'name' => 'Point Cheraga 2',
        'address' => '',
        'is_active' => true,
    ]);

    $order = officeStopdeskOrder($store, $provider, $pointCityA, $state, $city);

    $volt = officeVolt([$user, $store])
        ->call('openDeliveryModal', $order->id)
        ->set('form.city_id', $cityB->id)
        ->set('form.stopdesk_point_id', $pointCityB1->id)
        ->call('rebuildFormOffices');

    expect($volt->get('form.stopdesk_point_id'))->toBe($pointCityB1->id)
        ->and($volt->get('formOffices'))->not->toBe([]);

    $volt->assertNotDispatched('swal:toast');
});

// ——— Strict geo scoping of pick-up points (B) ———

test('the inline stopdesk editor offers the order commune offices plus the wilaya-wide hubs and excludes other communes', function () {
    [$user, $store] = officeUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = officeGeography();

    $cityB = City::create([
        'state_id' => $state->id,
        'name' => 'Cheraga',
        'post_code' => '16027',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $provider = officeProvider($store);
    $point = officePoint($store, $provider, $state, $city);
    $pointOtherCity = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'city_id' => $cityB->id,
        'name' => 'Point Cheraga',
        'address' => '',
        'is_active' => true,
    ]);
    $pointRegional = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'city_id' => null,
        'name' => 'Regional Hub',
        'address' => '',
        'is_active' => true,
    ]);

    $order = officeStopdeskOrder($store, $provider, $point, $state, $city);

    $volt = officeVolt([$user, $store])->call('startOrderStopdeskEdit', $order->id);

    $optionIds = collect(data_get($volt->get('editStopdeskOptions'), '*.value'));

    expect($optionIds)->toContain((string) $point->id)
        ->and($optionIds)->toContain((string) $pointRegional->id)
        ->and($optionIds)->not->toContain((string) $pointOtherCity->id);
});

test('the inline stopdesk editor hides offices of a different state', function () {
    [$user, $store] = officeUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = officeGeography();

    $country = \App\Models\Locations\Country::firstOrNew(['code' => 'DZ'], ['name' => 'Algeria', 'is_active' => true, 'is_cod_available' => true]);
    $country->save();

    $stateB = State::create([
        'country_id' => $country->id,
        'state_code' => '31',
        'name' => 'Oran',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $cityB = City::create([
        'state_id' => $stateB->id,
        'name' => 'Oran City',
        'post_code' => '31000',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $provider = officeProvider($store);
    $point = officePoint($store, $provider, $state, $city);
    $pointOtherState = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $stateB->id,
        'city_id' => $cityB->id,
        'name' => 'Point Oran',
        'address' => '',
        'is_active' => true,
    ]);

    $order = officeStopdeskOrder($store, $provider, $point, $state, $city);

    $volt = officeVolt([$user, $store])->call('startOrderStopdeskEdit', $order->id);

    $optionIds = collect(data_get($volt->get('editStopdeskOptions'), '*.value'));

    expect($optionIds)->toContain((string) $point->id)
        ->and($optionIds)->not->toContain((string) $pointOtherState->id);
});

test('the create form offers no offices until a wilaya is chosen', function () {
    [$user, $store] = officeUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = officeGeography();
    $provider = officeProvider($store);
    $point = officePoint($store, $provider, $state, $city);

    $volt = officeVolt([$user, $store])->call('loadFormOffices', $provider->id);

    expect($volt->get('formOffices'))->toBe([])
        ->and($volt->get('form.stopdesk_point_id'))->toBe('');
});

test('the create form scopes offices to the chosen wilaya when no commune is set', function () {
    [$user, $store] = officeUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = officeGeography();

    $cityB = City::create([
        'state_id' => $state->id,
        'name' => 'Cheraga',
        'post_code' => '16027',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $provider = officeProvider($store);
    $pointA = officePoint($store, $provider, $state, $city);
    $pointB = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'city_id' => $cityB->id,
        'name' => 'Point Cheraga',
        'address' => '',
        'is_active' => true,
    ]);

    $volt = officeVolt([$user, $store])
        ->set('form.delivery_type', 'stopdesk')
        ->set('form.state_id', $state->id)
        ->call('loadFormOffices', $provider->id);

    $optionIds = collect(data_get($volt->get('formOffices'), '*.value'));

    expect($optionIds)->toContain($pointA->id)
        ->and($optionIds)->toContain($pointB->id)
        ->and($volt->get('form.shipping_provider_id'))->toBe($provider->id);
});

test('the create-form office list ranks the commune offices before the wilaya-wide hubs and hides other communes', function () {
    [$user, $store] = officeUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = officeGeography();

    $cityB = City::create([
        'state_id' => $state->id,
        'name' => 'Cheraga',
        'post_code' => '16027',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $provider = officeProvider($store);
    $pointCommune = officePoint($store, $provider, $state, $city);
    $pointHub = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'city_id' => null,
        'name' => 'Regional Hub',
        'address' => '',
        'is_active' => true,
    ]);
    $pointOtherCity = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'city_id' => $cityB->id,
        'name' => 'Point Cheraga',
        'address' => '',
        'is_active' => true,
    ]);

    $volt = officeVolt([$user, $store])
        ->set('form.delivery_type', 'stopdesk')
        ->set('form.state_id', $state->id)
        ->set('form.city_id', $city->id)
        ->call('loadFormOffices', $provider->id);

    $optionIds = collect(data_get($volt->get('formOffices'), '*.value'));

    expect($optionIds)->toContain((string) $pointCommune->id)
        ->and($optionIds)->toContain((string) $pointHub->id)
        ->and($optionIds)->not->toContain((string) $pointOtherCity->id)
        ->and((string) $optionIds->first())->toBe((string) $pointCommune->id);
});