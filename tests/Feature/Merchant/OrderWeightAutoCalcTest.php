<?php

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

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function weightUser(string $storeRole = 'owner'): array
{
    $user = roleUser('merchant');
    $user->assignRole(\Spatie\Permission\Models\Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Weight Store',
        'slug' => 'weight-'.uniqid(),
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

function weightGeography(): array
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

function weightVariant(Store $store, float $weight, int $stock = 100): array
{
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Weight Product '.$weight,
        'slug' => 'weight-pr-'.uniqid(),
        'sku' => 'weight-sku-'.uniqid(),
        'type' => 'variable',
        'price' => 500,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'weight-v-'.uniqid(),
        'price' => 500,
        'stock' => $stock,
        'weight' => $weight,
        'is_active' => true,
    ]);

    return [$product, $variant];
}

function weightVolt(array $userStore)
{
    [$user, $store] = $userStore;
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    return Volt::test('merchant.orders.index');
}

test('addFormItem auto-calculates weight_kg from variant weights', function () {
    [$user, $store] = weightUser(StoreRoleEnum::OWNER->value);

    [, $light] = weightVariant($store, 0.5);
    [, $heavy] = weightVariant($store, 2.25);

    $volt = weightVolt([$user, $store]);

    $volt->call('addFormItem', $light->id);

    expect((float) $volt->get('form.weight_kg'))->toBe(0.5);

    $volt->call('addFormItem', $heavy->id);

    expect((float) $volt->get('form.weight_kg'))->toBe(2.75);
});

test('updateFormItemQty multiplies the item weight into weight_kg', function () {
    [$user, $store] = weightUser(StoreRoleEnum::OWNER->value);

    [, $variant] = weightVariant($store, 1.5);

    $volt = weightVolt([$user, $store])
        ->call('addFormItem', $variant->id)
        ->call('updateFormItemQty', 0, 3);

    expect((float) $volt->get('form.weight_kg'))->toBe(4.5);
});

test('removeFormItem recalculates weight_kg', function () {
    [$user, $store] = weightUser(StoreRoleEnum::OWNER->value);

    [, $light] = weightVariant($store, 0.5);
    [, $heavy] = weightVariant($store, 2.0);

    $volt = weightVolt([$user, $store])
        ->call('addFormItem', $light->id)
        ->call('addFormItem', $heavy->id);

    expect((float) $volt->get('form.weight_kg'))->toBe(2.5);

    $volt->call('removeFormItem', 0);

    expect((float) $volt->get('form.weight_kg'))->toBe(2.0);
});

test('addFormItemByBarcode recalculates weight_kg', function () {
    [$user, $store] = weightUser(StoreRoleEnum::OWNER->value);

    [, $variant] = weightVariant($store, 1.75);

    $volt = weightVolt([$user, $store])
        ->call('addFormItemByBarcode', $variant->sku);

    expect((float) $volt->get('form.weight_kg'))->toBe(1.75);
});

test('updateFormItemPrice does not overwrite a manual weight_kg override', function () {
    [$user, $store] = weightUser(StoreRoleEnum::OWNER->value);

    [, $variant] = weightVariant($store, 1.0);

    $volt = weightVolt([$user, $store])
        ->call('addFormItem', $variant->id);

    expect((float) $volt->get('form.weight_kg'))->toBe(1.0);

    $volt->set('form.weight_kg', 9.5)
        ->call('updateFormItemPrice', 0, 700);

    expect((float) $volt->get('form.weight_kg'))->toBe(9.5);
});

test('a created order persists the auto-calculated weight_kg', function () {
    [$user, $store] = weightUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = weightGeography();

    [, $light] = weightVariant($store, 0.5);
    [, $heavy] = weightVariant($store, 2.25);

    $volt = weightVolt([$user, $store])
        ->call('addFormItem', $light->id)
        ->call('addFormItem', $heavy->id);

    expect((float) $volt->get('form.weight_kg'))->toBe(2.75);

    $volt->set('form.customer_name', 'Weight Customer')
        ->set('form.customer_phone', '0550987654')
        ->set('form.address', 'Rue Nationale 12')
        ->set('form.delivery_type', 'home')
        ->set('form.state_id', $state->id)
        ->set('form.city_id', $city->id)
        ->set('form.shipment_type', 'delivery')
        ->set('form.payment_method', 'cod')
        ->call('submitCreate')
        ->assertSet('showCreateModal', false);

    $created = Order::where('store_id', $store->id)->first();
    expect($created)->not->toBeNull()
        ->and((float) $created->weight_kg)->toBe(2.75)
        ->and($created->delivery_type)->toBe('home');
});