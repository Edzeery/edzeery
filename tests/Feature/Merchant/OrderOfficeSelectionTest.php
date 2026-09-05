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
        ->assertDispatched('swal', type: 'success');

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