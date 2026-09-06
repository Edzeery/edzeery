<?php

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
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function ifeUser(string $storeRole = 'owner'): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Inline Field Store',
        'slug' => 'inline-field-'.uniqid(),
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

function ifeGeography(Store $store): array
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

function ifeVariant(Store $store, string $seed = 'a'): ProductVariant
{
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'IFE '.$seed,
        'slug' => 'ife-pr-'.$seed.'-'.uniqid(),
        'sku' => 'ife-sku-'.$seed.'-'.uniqid(),
        'type' => 'variable',
        'price' => 400,
        'is_active' => true,
    ]);

    return ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'ife-v-'.$seed.'-'.uniqid(),
        'price' => 400,
        'stock' => 10,
    ]);
}

function ifeOrder(
    Store $store,
    State $state,
    ?City $city = null,
    string $statusKey = 'pending',
    bool $withItems = false,
): Order {
    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Field Customer',
        'phone' => '0551112223',
        'status' => true,
    ]);

    $status = \App\Models\Status::system()->forType('order')->where('key', $statusKey)->first();

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => $status?->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 1000,
        'state_id' => $state->id,
        'city_id' => $city?->id,
        'delivery_type' => 'home',
        'address' => 'Rue 12, Alger',
        'payment_method' => 'cod',
        'shipping_cost' => 0,
    ]);

    if ($withItems) {
        $variant = ifeVariant($store);
        $order->items()->create([
            'store_id' => $store->id,
            'product_variant_id' => $variant->id,
            'product_id' => $variant->product_id,
            'quantity' => 1,
            'price' => $variant->price,
            'subtotal' => $variant->price,
        ]);
    }

    return $order;
}

function ifeVolt(\App\Models\User $user, Store $store)
{
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    return Volt::test('merchant.orders.index');
}

test('owner can inline-edit the delivery address and the audit event is recorded', function () {
    [$user, $store] = ifeUser(StoreRoleEnum::OWNER->value);
    [$stateA, $cityA] = ifeGeography($store);

    $order = ifeOrder($store, $stateA, $cityA);

    ifeVolt($user, $store)
        ->call('startOrderAddressEdit', $order->id)
        ->assertSet('editingField', 'order.address')
        ->call('saveOrderAddress', 'Rue 99, Hydra')
        ->assertSet('editingField', null)
        ->assertSet('editingError', null);

    $order->refresh();

    expect($order->address)->toBe('Rue 99, Hydra');

    $activity = Activity::query()->where('event', 'order_address_updated')->latest()->first();

    expect($activity)->not->toBeNull()
        ->and($activity->description)->toBe('Applied order address')
        ->and((string) $activity->subject_id)->toBe((string) $order->id);
});

test('saving a blank address clears it', function () {
    [$user, $store] = ifeUser(StoreRoleEnum::OWNER->value);
    [$stateA, $cityA] = ifeGeography($store);

    $order = ifeOrder($store, $stateA, $cityA);
    $order->update(['address' => 'Rue 12, Alger']);

    ifeVolt($user, $store)
        ->call('startOrderAddressEdit', $order->id)
        ->call('saveOrderAddress', '')
        ->assertSet('editingField', null);

    expect($order->refresh()->address)->toBeNull();
});

test('owner can inline-edit the weight and the audit event is recorded', function () {
    [$user, $store] = ifeUser(StoreRoleEnum::OWNER->value);
    [$stateA, $cityA] = ifeGeography($store);

    $order = ifeOrder($store, $stateA, $cityA);

    ifeVolt($user, $store)
        ->call('startOrderWeightEdit', $order->id)
        ->assertSet('editingField', 'order.weight')
        ->call('saveOrderWeight', '1.25')
        ->assertSet('editingField', null)
        ->assertSet('editingError', null);

    $order->refresh();

    expect($order->weight_kg)->toBe('1.25')
        ->and(Activity::query()->where('event', 'order_weight_updated')->exists())->toBeTrue();
});

test('a negative weight is rejected inline with a validation_failed audit', function () {
    [$user, $store] = ifeUser(StoreRoleEnum::OWNER->value);
    [$stateA, $cityA] = ifeGeography($store);

    $order = ifeOrder($store, $stateA, $cityA);

    ifeVolt($user, $store)
        ->call('startOrderWeightEdit', $order->id)
        ->call('saveOrderWeight', '-5')
        ->assertSet('editingField', 'order.weight')
        ->assertNotSet('editingError', null);

    expect($order->refresh()->weight_kg)->toBeNull();

    expect(Activity::query()->where('event', 'order_weight_updated_validation_failed')->exists())->toBeTrue();
});

test('owner can inline-edit an amount-based discount with reason', function () {
    [$user, $store] = ifeUser(StoreRoleEnum::OWNER->value);
    [$stateA, $cityA] = ifeGeography($store);

    $order = ifeOrder($store, $stateA, $cityA);

    ifeVolt($user, $store)
        ->call('startOrderDiscountEdit', $order->id)
        ->assertSet('editingField', 'order.discount')
        ->set('discountEditType', 'amount')
        ->set('discountEditValue', '100')
        ->set('discountEditReason', 'coupon XMAS')
        ->call('saveOrderDiscount')
        ->assertSet('editingField', null)
        ->assertSet('editingError', null);

    $order->refresh();

    expect($order->discount_type)->toBe('amount')
        ->and($order->discount_value)->toBe('100.00')
        ->and($order->discount_reason)->toBe('coupon XMAS');

    $activity = Activity::query()->where('event', 'order_discount_updated')->latest()->first();

    expect($activity)->not->toBeNull()
        ->and($activity->description)->toBe('Applied order discount');
});

test('a percent discount above 100 is rejected inline', function () {
    [$user, $store] = ifeUser(StoreRoleEnum::OWNER->value);
    [$stateA, $cityA] = ifeGeography($store);

    $order = ifeOrder($store, $stateA, $cityA);

    ifeVolt($user, $store)
        ->call('startOrderDiscountEdit', $order->id)
        ->set('discountEditType', 'percent')
        ->set('discountEditValue', '150')
        ->call('saveOrderDiscount')
        ->assertSet('editingField', 'order.discount')
        ->assertSet('editingError', __('merchant_panel.discount_percent_max'));

    expect($order->refresh()->discount_type)->toBeNull();

    expect(Activity::query()->where('event', 'order_discount_updated_validation_failed')->exists())->toBeTrue();
});

test('clearing the discount type removes the stored discount', function () {
    [$user, $store] = ifeUser(StoreRoleEnum::OWNER->value);
    [$stateA, $cityA] = ifeGeography($store);

    $order = ifeOrder($store, $stateA, $cityA);
    $order->update(['discount_type' => 'amount', 'discount_value' => 50, 'discount_reason' => 'manual']);

    ifeVolt($user, $store)
        ->call('startOrderDiscountEdit', $order->id)
        ->set('discountEditType', '')
        ->set('discountEditValue', '50')
        ->call('saveOrderDiscount')
        ->assertSet('editingField', null);

    $order->refresh();

    expect($order->discount_type)->toBeNull()
        ->and($order->discount_value)->toBeNull()
        ->and($order->discount_reason)->toBeNull();
});

test('owner can toggle send-from-carrier-warehouse and it logs the audit event', function () {
    [$user, $store] = ifeUser(StoreRoleEnum::OWNER->value);
    [$stateA, $cityA] = ifeGeography($store);

    $order = ifeOrder($store, $stateA, $cityA);

    ifeVolt($user, $store)
        ->call('toggleSendFromWarehouse', $order->id);

    expect($order->refresh()->send_from_carrier_warehouse)->toBeTrue()
        ->and(Activity::query()->where('event', 'order_send_from_warehouse_updated')->exists())->toBeTrue();

    ifeVolt($user, $store)
        ->call('toggleSendFromWarehouse', $order->id);

    expect($order->refresh()->send_from_carrier_warehouse)->toBeFalse();
});

test('address, weight and discount stay blocked for shipped orders while the warehouse toggle works', function () {
    [$user, $store] = ifeUser(StoreRoleEnum::OWNER->value);
    [$stateA, $cityA] = ifeGeography($store);

    $order = ifeOrder($store, $stateA, $cityA, 'shipped');

    ifeVolt($user, $store)
        ->call('startOrderAddressEdit', $order->id)
        ->call('saveOrderAddress', 'Changed')
        ->assertSet('editingField', 'order.address');

    ifeVolt($user, $store)
        ->call('startOrderWeightEdit', $order->id)
        ->call('saveOrderWeight', '5')
        ->assertSet('editingField', 'order.weight');

    ifeVolt($user, $store)
        ->call('startOrderDiscountEdit', $order->id)
        ->set('discountEditType', 'amount')
        ->set('discountEditValue', '10')
        ->call('saveOrderDiscount')
        ->assertSet('editingField', 'order.discount');

    $order->refresh();

    expect($order->address)->toBe('Rue 12, Alger')
        ->and($order->weight_kg)->toBeNull()
        ->and($order->discount_type)->toBeNull();

    ifeVolt($user, $store)
        ->call('toggleSendFromWarehouse', $order->id);

    expect($order->refresh()->send_from_carrier_warehouse)->toBeTrue();
});

test('the missing-fields badge jumps to the carrier editor for a confirmed order lacking a carrier', function () {
    [$user, $store] = ifeUser(StoreRoleEnum::OWNER->value);
    [$stateA, $cityA] = ifeGeography($store);

    $order = ifeOrder($store, $stateA, $cityA, 'confirmed', withItems: true);

    ifeVolt($user, $store)
        ->call('startMissingFieldEdit', $order->id)
        ->assertSet('editingField', 'order.shipping_provider');
});

test('the missing-fields badge opens the edit modal when only items are missing', function () {
    [$user, $store] = ifeUser(StoreRoleEnum::OWNER->value);
    [$stateA, $cityA] = ifeGeography($store);

    $order = ifeOrder($store, $stateA, $cityA, statusKey: 'pending', withItems: false);

    ifeVolt($user, $store)
        ->call('startMissingFieldEdit', $order->id)
        ->assertSet('editingOrderId', $order->id);
});

test('staff without order.manage permission is forbidden from the 31.4 inline actions', function () {
    [$staff, $store] = ifeUser(StoreRoleEnum::STAFF->value);
    [$stateA, $cityA] = ifeGeography($store);

    $order = ifeOrder($store, $stateA, $cityA);

    foreach (['startOrderAddressEdit', 'startOrderWeightEdit', 'startOrderDiscountEdit'] as $method) {
        ifeVolt($staff, $store)
            ->call($method, $order->id)
            ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'error'
                && ($params[0]['title'] ?? null) === __('messages.permission_denied'));
    }

    ifeVolt($staff, $store)
        ->call('toggleSendFromWarehouse', $order->id)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'error'
            && ($params[0]['title'] ?? null) === __('messages.permission_denied'));

    expect(Activity::query()->count())->toBe(0)
        ->and($order->fresh()->send_from_carrier_warehouse)->toBeFalse();
});