<?php

use App\Domains\Cart\Support\OrderRules;
use App\Domains\Shipping\Models\ShippingProvider;
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

function qtyUser(string $storeRole = 'owner'): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Qty Store',
        'slug' => 'qty-'.uniqid(),
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

function qtyGeography(): array
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

function qtyVariant(Store $store, int $stock): array
{
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Qty Product',
        'slug' => 'qty-pr-'.uniqid(),
        'sku' => 'qty-sku-'.uniqid(),
        'type' => 'variable',
        'price' => 500,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'qty-v-'.uniqid(),
        'price' => 500,
        'stock' => $stock,
        'is_active' => true,
    ]);

    return [$product, $variant];
}

function qtyVolt(array $userStore)
{
    [$user, $store] = $userStore;
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    return Volt::test('merchant.orders.index');
}

function qtyForm(Store $store, State $state, City $city, Product $product, ProductVariant $variant, int $quantity, ?int $cap = null, bool $preorder = false): array
{
    return [
        'customer_name' => 'Qty Customer',
        'customer_phone' => '0550123456',
        'phone_secondary' => '',
        'address' => 'Rue Nationale 12',
        'state_id' => $state->id,
        'city_id' => $city->id,
        'delivery_type' => 'home',
        'shipping_provider_id' => '',
        'stopdesk_point_id' => '',
        'shipment_type' => 'delivery',
        'payment_method' => 'cod',
        'discount_type' => null,
        'discount_value' => null,
        'discount_reason' => '',
        'notes' => '',
        'weight_kg' => '',
        'items' => [[
            'product_variant_id' => $variant->id,
            'product_id' => $product->id,
            'name' => "{$product->name} — {$variant->name}",
            'sku' => $variant->sku,
            'price' => 500,
            'quantity' => $quantity,
            'stock' => $variant->stock,
            'weight' => 0,
            'cap' => $cap,
            'preorder' => $preorder,
            'image_url' => asset('img/icons/noimg.png'),
        ]],
    ];
}

test('submitCreate rejects quantities above available stock when backorders are disabled', function () {
    [$user, $store] = qtyUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = qtyGeography();
    $store->settings()->updateOrCreate([], [
        'inventory_tracking' => true,
        'allow_backorder' => false,
    ]);

    [$product, $variant] = qtyVariant($store, 3);

    expect(OrderRules::lineCap($variant, $store))->toBe(3);

    qtyVolt([$user, $store])
        ->call('openCreateModal')
        ->set('form', qtyForm($store, $state, $city, $product, $variant, 5, cap: 3))
        ->call('submitCreate')
        ->assertDispatched('swal', type: 'error')
        ->assertSet('showCreateModal', true);

    expect(Order::where('store_id', $store->id)->count())->toBe(0);
});

test('backorder-enabled stores accept quantities beyond available stock', function () {
    [$user, $store] = qtyUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = qtyGeography();
    $store->settings()->updateOrCreate([], [
        'inventory_tracking' => true,
        'allow_backorder' => true,
    ]);

    [$product, $variant] = qtyVariant($store, 3);

    expect(OrderRules::allowsBackorder($store))->toBeTrue();

    qtyVolt([$user, $store])
        ->set('form', qtyForm($store, $state, $city, $product, $variant, 5, cap: null, preorder: true))
        ->call('submitCreate')
        ->assertSet('showCreateModal', false)
        ->assertDispatched('swal', type: 'success');

    $created = Order::where('store_id', $store->id)->first();
    expect($created)->not->toBeNull()
        ->and($created->items()->first()->quantity)->toBe(5);
});

test('the add-instead spinner caps at the available stock when tracking inventory', function () {
    [$user, $store] = qtyUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = qtyGeography();
    $store->settings()->updateOrCreate([], [
        'inventory_tracking' => true,
        'allow_backorder' => false,
    ]);

    [$product, $variant] = qtyVariant($store, 3);

    $volt = qtyVolt([$user, $store]);

    foreach (range(1, 3) as $i) {
        $volt->call('addFormItem', $variant->id);
    }

    $volt->call('addFormItem', $variant->id)
        ->assertDispatched('swal', type: 'error');

    $items = $volt->get('form.items');
    expect($items)->toHaveCount(1)
        ->and($items[0]['quantity'])->toBe(3);
});

test('updateFormItemQty clamps to the hard cap', function () {
    [$user, $store] = qtyUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = qtyGeography();
    $store->settings()->updateOrCreate([], [
        'inventory_tracking' => true,
        'allow_backorder' => false,
    ]);

    [$product, $variant] = qtyVariant($store, 3);

    $volt = qtyVolt([$user, $store])
        ->call('addFormItem', $variant->id)
        ->call('updateFormItemQty', 0, 100);

    $items = $volt->get('form.items');
    expect($items[0]['quantity'])->toBe(3)
        ->and($items[0]['cap'])->toBe(3);
});

test('adding an out-of-stock variant is blocked when backorders are disabled', function () {
    [$user, $store] = qtyUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = qtyGeography();
    $store->settings()->updateOrCreate([], [
        'inventory_tracking' => true,
        'allow_backorder' => false,
    ]);

    [$product, $variant] = qtyVariant($store, 0);

    qtyVolt([$user, $store])
        ->call('addFormItem', $variant->id)
        ->assertDispatched('swal', type: 'error');

    $volt = qtyVolt([$user, $store])->call('addFormItem', $variant->id);
    expect($volt->get('form.items'))->toHaveCount(0);
});