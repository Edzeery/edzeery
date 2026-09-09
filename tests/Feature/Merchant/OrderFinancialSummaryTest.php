<?php

use App\Domains\Shipping\Models\DeliveryRate;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
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

function summaryUser(string $storeRole = 'owner'): array
{
    $user = roleUser('merchant');
    $user->assignRole(\Spatie\Permission\Models\Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Summary Store',
        'slug' => 'summary-'.uniqid(),
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

function summaryGeography(): array
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

function summaryVariant(Store $store, float $price, int $stock = 100): array
{
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Summary Product '.$price,
        'slug' => 'summary-pr-'.uniqid(),
        'sku' => 'summary-sku-'.uniqid(),
        'type' => 'variable',
        'price' => $price,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'summary-v-'.uniqid(),
        'price' => $price,
        'stock' => $stock,
        'weight' => 0,
        'is_active' => true,
    ]);

    return [$product, $variant];
}

function summaryVolt(array $userStore)
{
    [$user, $store] = $userStore;
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    return Volt::test('merchant.orders.index');
}

test('the create modal renders the shared financial grid with subtotal, weight, discount and total', function () {
    [$user, $store] = summaryUser(StoreRoleEnum::OWNER->value);
    [, $variant] = summaryVariant($store, 500);

    $html = summaryVolt([$user, $store])
        ->call('openCreateModal')
        ->call('addFormItem', $variant->id)
        ->call('addFormItem', $variant->id)
        ->html();

    // Single source grid + its responsive breakpoints (5 cols @1440, 2 @768, 1 @375).
    expect($html)->toContain('data-financial-grid')
        ->and($html)->toContain('min-[1440px]:grid-cols-5')
        ->and($html)->toContain('md:grid-cols-2')
        ->and($html)->toContain('data-financial-subtotal')
        ->and($html)->toContain('data-financial-weight')
        ->and($html)->toContain('data-financial-delivery')
        ->and($html)->toContain('data-financial-discount')
        ->and($html)->toContain('data-financial-total');

    // Subtotal = 2 × 500, delivery price shows the "please select" hint while
    // no carrier/destination is set (never a misleading free), no discount.
    expect($html)->toContain(currency(1000))
        ->and($html)->toContain('0.00 kg')
        ->and($html)->toContain(__('merchant_panel.shipping_hint_delivery'))
        ->and($html)->toContain('—');
});

test('the financial grid tracks item quantity changes live (single source)', function () {
    [$user, $store] = summaryUser(StoreRoleEnum::OWNER->value);
    [, $variant] = summaryVariant($store, 350);

    $volt = summaryVolt([$user, $store])
        ->call('openCreateModal')
        ->call('addFormItem', $variant->id);

    expect($volt->html())->toContain(currency(350));

    $volt->call('updateFormItemQty', 0, 4);

    expect($volt->html())->toContain(currency(1400));
});

test('an amount discount is reflected in the grid and persisted on the order', function () {
    [$user, $store] = summaryUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = summaryGeography();
    [, $variant] = summaryVariant($store, 500);

    $volt = summaryVolt([$user, $store])
        ->call('openCreateModal')
        ->call('addFormItem', $variant->id)
        ->set('form.discount_type', 'amount')
        ->set('form.discount_value', 100)
        ->set('form.discount_reason', 'coupon');

    $html = $volt->html();

    expect($html)->toContain('-'.currency(100))
        ->and($html)->toContain(currency(400));

    $volt->set('form.customer_name', 'Summary Customer')
        ->set('form.customer_phone', '0550987654')
        ->set('form.address', 'Rue Nationale 12')
        ->set('form.delivery_type', 'home')
        ->set('form.state_id', $state->id)
        ->set('form.city_id', $city->id)
        ->set('form.shipment_type', 'delivery')
        ->set('form.payment_method', 'cod')
        ->call('submitCreate')
        ->assertSet('showCreateModal', false);

    $order = \App\Models\Orders\Order::where('store_id', $store->id)->first();
    expect($order)->not->toBeNull()
        ->and($order->discount_type)->toBe('amount')
        ->and((float) $order->discount_value)->toBe(100.0)
        ->and((float) $order->total_amount)->toBe(500.0)
        ->and((float) $order->grand_total)->toBe(400.0);
});

test('a percent discount is converted to an amount in the grid', function () {
    [$user, $store] = summaryUser(StoreRoleEnum::OWNER->value);
    [, $variant] = summaryVariant($store, 500);

    $html = summaryVolt([$user, $store])
        ->call('openCreateModal')
        ->call('addFormItem', $variant->id)
        ->call('addFormItem', $variant->id)
        ->set('form.discount_type', 'percent')
        ->set('form.discount_value', 10)
        ->html();

    expect($html)->toContain('-'.currency(100))
        ->and($html)->toContain(currency(900));
});

test('the grid shows the announced delivery rate read-only when a rate exists', function () {
    [$user, $store] = summaryUser(StoreRoleEnum::OWNER->value);
    $this->seed(Database\Seeders\CarrierCatalogSeeder::class);

    [, $variant] = summaryVariant($store, 500);
    [$state, $city] = summaryGeography();

    $provider = ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'Ecotrack',
        'carrier_platform_id' => \App\Domains\Shipping\Models\CarrierPlatform::where('slug', 'ecotrack')->first()->id,
        'carrier_id' => \App\Domains\Shipping\Models\Carrier::where('code', 'ecotrack')->first()->id,
        'credentials' => ['api_token' => 'tok_1'],
        'is_active' => true,
    ]);

    DeliveryRate::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'home_cost' => 400,
        'office_cost' => 250,
        'is_active' => true,
    ]);

    $html = summaryVolt([$user, $store])
        ->call('openCreateModal')
        ->call('addFormItem', $variant->id)
        ->set('form.delivery_type', 'home')
        ->set('form.state_id', $state->id)
        ->set('form.city_id', $city->id)
        ->html();

    expect($html)->toContain(currency(400))
        ->and($html)->toContain(__('merchant_panel.delivery_cost'))
        ->and($html)->toContain('data-financial-delivery');
});

test('the delivery cost cell stays free when no rate is configured', function () {
    [$user, $store] = summaryUser(StoreRoleEnum::OWNER->value);

    [, $variant] = summaryVariant($store, 500);
    [$state, $city] = summaryGeography();

    $html = summaryVolt([$user, $store])
        ->call('openCreateModal')
        ->call('addFormItem', $variant->id)
        ->set('form.delivery_type', 'home')
        ->set('form.state_id', $state->id)
        ->set('form.city_id', $city->id)
        ->html();

    expect($html)->toContain(__('merchant_panel.free'));
});

test('the edit modal reuses the same financial grid (single source for edit too)', function () {
    [$user, $store] = summaryUser(StoreRoleEnum::OWNER->value);

    [$product, $variant] = summaryVariant($store, 500);

    $order = \App\Models\Orders\Order::create([
        'store_id' => $store->id,
        'customer_id' => \App\Models\Customer::create(['store_id' => $store->id, 'name' => 'Edit Customer', 'phone' => '0550'.fake()->unique()->numerify('######'), 'status' => true])->id,
        'status_id' => \App\Models\Status::system()->forType('order')->where('key', 'confirmed')->firstOrFail()->id,
        'number' => (new \App\Models\Orders\Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 1500,
        'shipping_cost' => 0,
    ]);

    \App\Models\Orders\OrderItem::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'quantity' => 3,
        'price' => 500,
        'subtotal' => 1500,
    ]);

    $html = summaryVolt([$user, $store])
        ->call('openEditModal', $order->id)
        ->html();

    expect($html)->toContain('data-financial-grid')
        ->and($html)->toContain(currency(1500));
});