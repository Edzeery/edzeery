<?php

use App\Domains\Order\Exceptions\OrderIncompleteException;
use App\Domains\Order\Services\OrderCompleteness;
use App\Domains\Order\Services\OrderService;
use App\Domains\Order\Services\OrderTrackingService;
use App\Domains\Shipping\Models\DeliveryRate;
use App\Domains\Shipping\Models\DeliveryRider;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\StopdeskPoint;
use App\Domains\Shipping\Services\OrderShippingGateway;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderTracking;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Status;
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

function ocoUser(string $storeRole = 'owner'): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Oco Store',
        'slug' => 'oco-'.uniqid(),
        'status' => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => $storeRole,
    ]);

    return [$user, $store, $membership];
}

function ocoProvider(Store $store): ShippingProvider
{
    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'Oco Carrier',
        'code' => 'oco-local',
        'credentials' => [],
        'is_active' => true,
        'is_default' => true,
        'flat_rate' => 600,
    ]);
}

function ocoGeography(): array
{
    $country = Country::firstOrCreate(
        ['code' => 'DZ'],
        ['name' => 'Algeria', 'arabic_name' => 'الجزائر', 'is_active' => true],
    );

    $state = State::firstOrCreate(
        ['country_id' => $country->id, 'state_code' => '01'],
        ['name' => 'Adrar', 'arabic_name' => 'أدرار', 'is_active' => true],
    );

    $city = City::firstOrCreate(
        ['state_id' => $state->id, 'name' => 'Adrar Centre'],
        ['post_code' => '01000', 'is_active' => true],
    );

    return [$state, $city];
}

function ocoVariant(Store $store): ProductVariant
{
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Oco Product',
        'slug' => 'oco-pr-'.uniqid(),
        'sku' => 'oco-sku-'.uniqid(),
        'type' => 'simple',
        'price' => 400,
        'is_active' => true,
    ]);

    return ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'oco-v-'.uniqid(),
        'price' => 400,
        'stock' => 10,
        'is_active' => true,
    ]);
}

function ocoToast(array $params): ?array
{
    $payload = ($params[0] ?? null) && is_array($params[0]) ? $params[0] : $params;

    return is_array($payload) ? $payload : null;
}

function ocoOrder(Store $store, string $statusKey = 'pending', array $opts = []): Order
{
    $status = Status::system()
        ->forType('order')
        ->where('key', $statusKey)
        ->firstOrFail();

    [$state, $city] = ocoGeography();

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => null,
        'status_id' => $status->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 400,
        'shipping_cost' => 0,
        'state_id' => ($opts['with_geography'] ?? true) ? $state->id : null,
        'city_id' => ($opts['with_geography'] ?? true) ? $city->id : null,
        'address' => ($opts['with_address'] ?? true) ? 'Rue des Cedres' : null,
        'delivery_type' => $opts['delivery_type'] ?? 'home',
        'stopdesk_point_id' => $opts['stopdesk_point_id'] ?? null,
        'shipping_provider_id' => ($opts['with_provider'] ?? true) ? ocoProvider($store)->id : null,
    ]);

    if ($opts['with_customer'] ?? true) {
        $customer = Customer::create([
            'store_id' => $store->id,
            'name' => $opts['customer_name'] ?? 'Oco Customer',
            'phone' => '0559'.fake()->unique()->numerify('######'),
            'status' => true,
        ]);
        $order->update(['customer_id' => $customer->id]);
    }

    if ($opts['with_items'] ?? true) {
        OrderItem::create([
            'store_id' => $store->id,
            'order_id' => $order->id,
            'product_variant_id' => ocoVariant($store)->id,
            'quantity' => 1,
            'price' => 400,
            'subtotal' => 400,
        ]);
    }

    return $order->fresh();
}

test('the completeness service lists every missing field for an incomplete order', function () {
    [$user, $store, $membership] = ocoUser(StoreRoleEnum::OWNER->value);
    $order = ocoOrder($store, 'pending', [
        'with_customer' => false,
        'with_geography' => false,
        'with_address' => false,
        'with_items' => false,
        'with_provider' => false,
    ]);

    $service = app(OrderCompleteness::class);

    $keys = array_column($service->missing($order, true), 'key');

    expect($keys)->toBe([
        'carrier_not_configured',
        'customer_name',
        'customer_phone',
        'state',
        'city',
        'items',
        'delivery_address',
        'carrier',
    ])->and($service->isComplete($order, true))->toBeFalse()
        ->and($service->missingLabels($order, true))->toContain(__('order_flow.confirm_partner'));
});

test('confirming does not require a carrier, sending does', function () {
    [$user, $store, $membership] = ocoUser(StoreRoleEnum::OWNER->value);
    $order = ocoOrder($store, 'pending', ['with_provider' => false]);

    $service = app(OrderCompleteness::class);

    expect($service->isComplete($order, false))->toBeTrue()
        ->and($service->isComplete($order, true))->toBeFalse();
});

test('stopdesk delivery requires a stopdesk point when sending, even if an address exists', function () {
    [$user, $store, $membership] = ocoUser(StoreRoleEnum::OWNER->value);
    $order = ocoOrder($store, 'pending', [
        'delivery_type' => 'stopdesk',
        'stopdesk_point_id' => null,
    ]);

    $confirmKeys = array_column(app(OrderCompleteness::class)->missing($order, false), 'key');
    $sendKeys = array_column(app(OrderCompleteness::class)->missing($order, true), 'key');

    expect($confirmKeys)->not->toContain('stopdesk_point')
        ->and($sendKeys)->toContain('stopdesk_point')
        ->and($sendKeys)->not->toContain('delivery_address');
});

test('OrderService::confirm throws OrderIncompleteException for an incomplete order', function () {
    [$user, $store, $membership] = ocoUser(StoreRoleEnum::OWNER->value);
    $order = ocoOrder($store, 'pending', [
        'with_items' => false,
        'with_provider' => false,
    ]);

    try {
        app(OrderService::class)->confirm($order, $membership);
        test()->fail('Expected OrderIncompleteException was not thrown.');
    } catch (OrderIncompleteException $e) {
        expect($e->labels())->toContain(__('merchant_panel.items'));
    }

    expect($order->fresh()->status?->key)->toBe('pending');
});

test('OrderService::confirm approves a complete order', function () {
    [$user, $store, $membership] = ocoUser(StoreRoleEnum::OWNER->value);
    $order = ocoOrder($store, 'pending');

    $confirmed = app(OrderService::class)->confirm($order, $membership);

    expect($confirmed->status?->key)->toBe('confirmed');
});

test('the shipping gateway refuses an incomplete order', function () {
    [$user, $store, $membership] = ocoUser(StoreRoleEnum::OWNER->value);
    $order = ocoOrder($store, 'confirmed', ['with_address' => false]);

    expect(fn () => app(OrderShippingGateway::class)->send($order, $order->shipping_provider_id, null, $membership))
        ->toThrow(OrderIncompleteException::class);

    expect($order->fresh()->status?->key)->toBe('confirmed');
});

test('the shipping gateway ships a complete order', function () {
    [$user, $store, $membership] = ocoUser(StoreRoleEnum::OWNER->value);
    $order = ocoOrder($store, 'confirmed');

    $result = app(OrderShippingGateway::class)->send($order, $order->shipping_provider_id, null, $membership);

    expect($result['order']->fresh()->status?->key)->toBe('shipped');
});

test('confirm-only refuses an incomplete order and keeps the drawer open', function () {
    [$user, $store, $membership] = ocoUser(StoreRoleEnum::OWNER->value);
    $order = ocoOrder($store, 'pending', [
        'with_items' => false,
        'with_provider' => false,
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->set('confirmOrderId', $order->id)
        ->set('showConfirmModal', true)
        ->call('submitConfirmOnly')
        ->assertSet('showConfirmModal', true)
        ->assertDispatched('swal:toast', function ($name, $params) {
            $title = ocoToast($params)['title'] ?? '';

            return ocoToast($params)['icon'] === 'warning' && str_contains($title, __('merchant_panel.items'));
        });

    expect($order->fresh()->status?->key)->toBe('pending');
});

test('confirm-and-send refuses an incomplete order without any transition', function () {
    [$user, $store, $membership] = ocoUser(StoreRoleEnum::OWNER->value);
    $order = ocoOrder($store, 'pending', [
        'with_items' => false,
        'with_provider' => false,
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->set('confirmOrderId', $order->id)
        ->call('submitConfirmAndSend')
        ->assertDispatched('swal:toast', function ($name, $params) {
            $title = ocoToast($params)['title'] ?? '';

            return ocoToast($params)['icon'] === 'warning'
                && (str_contains($title, __('order_flow.confirm_requires_partner')) || str_contains($title, __('merchant_panel.items')));
        });

    expect($order->fresh()->status?->key)->toBe('pending');
});

// ——— Dispatch readiness: the STORE must have an active company or rider ———

test('sending is blocked when the store has no active company and no rider', function () {
    [$user, $store, $membership] = ocoUser(StoreRoleEnum::OWNER->value);
    $order = ocoOrder($store, 'confirmed');

    ShippingProvider::where('store_id', $store->id)->update(['is_active' => false]);

    try {
        app(OrderShippingGateway::class)->send($order, $order->shipping_provider_id, null, $membership);
        test()->fail('Expected OrderIncompleteException was not thrown.');
    } catch (OrderIncompleteException $e) {
        expect($e->labels())->toContain(__('order_flow.carrier_not_configured'));
    }

    expect($order->fresh()->status?->key)->toBe('confirmed');
});

test('an inactive provider does not satisfy dispatch readiness', function () {
    [$user, $store] = ocoUser(StoreRoleEnum::OWNER->value);
    $service = app(OrderCompleteness::class);

    $provider = ocoProvider($store);
    $provider->update(['is_active' => false]);

    $probe = new Order(['store_id' => $store->id]);

    expect($service->missing($probe, true))->toContain(['key' => 'carrier_not_configured', 'label' => __('order_flow.carrier_not_configured')]);
});

test('an active rider satisfies store readiness', function () {
    [$user, $store, $membership] = ocoUser(StoreRoleEnum::OWNER->value);
    $order = ocoOrder($store, 'confirmed', ['with_provider' => false]);

    DeliveryRider::create([
        'store_id' => $store->id,
        'name' => 'Rider A',
        'phone' => '0550111222',
        'is_active' => true,
    ]);
    $order->update(['delivery_rider_id' => DeliveryRider::where('store_id', $store->id)->value('id')]);

    $result = app(OrderShippingGateway::class)->send($order, null, null, $membership);

    expect($result['order']->fresh()->status?->key)->toBe('shipped')
        ->and($result['rate_note'])->toBeNull();
});

// ——— Rider leg: the order ADDRESS drives completeness (never the office) ———

test('a rider stopdesk-leg order requires the address, not the office, when sending', function () {
    [$user, $store, $membership] = ocoUser(StoreRoleEnum::OWNER->value);
    $order = ocoOrder($store, 'pending', [
        'delivery_type' => 'stopdesk',
        'stopdesk_point_id' => null,
        'with_provider' => false,
    ]);

    $rider = DeliveryRider::create([
        'store_id' => $store->id,
        'name' => 'Rider Stop',
        'phone' => '0551111222',
        'is_active' => true,
    ]);
    $order->update(['delivery_rider_id' => $rider->id]);

    $sendKeys = array_column(app(OrderCompleteness::class)->missing($order, true), 'key');

    expect($sendKeys)->not->toContain('stopdesk_point')
        ->and($sendKeys)->not->toContain('delivery_address');
});

test('a rider order without an address is incomplete when sending', function () {
    [$user, $store, $membership] = ocoUser(StoreRoleEnum::OWNER->value);
    $order = ocoOrder($store, 'confirmed', [
        'with_address' => false,
        'with_provider' => false,
    ]);

    $rider = DeliveryRider::create([
        'store_id' => $store->id,
        'name' => 'Rider NoAddr',
        'phone' => '0552222333',
        'is_active' => true,
    ]);
    $order->update(['delivery_rider_id' => $rider->id]);

    $sendKeys = array_column(app(OrderCompleteness::class)->missing($order, true), 'key');

    expect($sendKeys)->toContain('delivery_address')
        ->and(app(OrderCompleteness::class)->isComplete($order, true))->toBeFalse();
});

// ——— Rider tracking number: HM/SD prefix by delivery type, store-unique ———

test('generateRiderTrackingNumber prefixes HM for home and SD for stopdesk', function () {
    [$user, $store, $membership] = ocoUser(StoreRoleEnum::OWNER->value);
    $service = app(OrderTrackingService::class);
    $order = ocoOrder($store, 'pending');

    expect(str_starts_with($service->generateRiderTrackingNumber($order), 'HM-'))->toBeTrue();

    $order->update(['delivery_type' => 'stopdesk']);

    expect(str_starts_with($service->generateRiderTrackingNumber($order), 'SD-'))->toBeTrue();
});

test('generateRiderTrackingNumber never collides with existing tracking numbers', function () {
    [$user, $store, $membership] = ocoUser(StoreRoleEnum::OWNER->value);
    $order = ocoOrder($store, 'pending');
    $service = app(OrderTrackingService::class);

    $existing = collect(range(1, 20))->map(fn () => $service->generateRiderTrackingNumber($order));
    OrderTracking::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'tracking_number' => $existing->first(),
        'tracking_status' => 'shipped',
    ]);

    $next = $service->generateRiderTrackingNumber($order);

    expect($existing->doesntContain($next))->toBeTrue();
});

// ——— Confirm-and-send to a rider through the actual drawer component ———

test('confirm-and-send to a rider ships the order and backfills an HM/SD tracking number', function () {
    [$user, $store, $membership] = ocoUser(StoreRoleEnum::OWNER->value);
    $order = ocoOrder($store, 'confirmed', ['with_provider' => false]);

    $rider = DeliveryRider::create([
        'store_id' => $store->id,
        'name' => 'Rider Volt',
        'phone' => '0553333444',
        'is_active' => true,
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->set('confirmOrderId', $order->id)
        ->set('showConfirmModal', true)
        ->set('confirmPartnerType', 'rider')
        ->set('confirmRiderId', $rider->id)
        ->call('submitConfirmAndSend')
        ->assertDispatched('swal:toast', fn ($name, $params) => ocoToast($params)['icon'] === 'success');

    $shipped = $order->fresh();

    expect($shipped->status?->key)->toBe('shipped')
        ->and($shipped->delivery_rider_id)->toBe($rider->id)
        ->and($shipped->shipping_provider_id)->toBeNull();

    $tracking = OrderTracking::where('order_id', $order->id)->first();

    expect($tracking)->not->toBeNull()
        ->and($tracking->tracking_number)->toMatch('/^HM-/');
});

test('confirm-and-send refuses an inactive or foreign rider', function () {
    [$user, $store, $membership] = ocoUser(StoreRoleEnum::OWNER->value);
    $order = ocoOrder($store, 'confirmed', ['with_provider' => false]);

    [$otherUser, $otherStore] = ocoUser(StoreRoleEnum::OWNER->value);
    $inactiveRider = DeliveryRider::create([
        'store_id' => $store->id,
        'name' => 'Rider Off',
        'phone' => '0554444555',
        'is_active' => false,
    ]);
    $foreignRider = DeliveryRider::create([
        'store_id' => $otherStore->id,
        'name' => 'Rider Foreign',
        'phone' => '0555555666',
        'is_active' => true,
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->set('confirmOrderId', $order->id)
        ->set('showConfirmModal', true)
        ->set('confirmPartnerType', 'rider')
        ->set('confirmRiderId', $inactiveRider->id)
        ->call('submitConfirmAndSend')
        ->assertDispatched('swal:toast', fn ($name, $params) => ocoToast($params)['icon'] === 'error');

    expect($order->fresh()->status?->key)->toBe('confirmed');

    Volt::test('merchant.orders.index')
        ->set('confirmOrderId', $order->id)
        ->set('showConfirmModal', true)
        ->set('confirmPartnerType', 'rider')
        ->set('confirmRiderId', $foreignRider->id)
        ->call('submitConfirmAndSend')
        ->assertDispatched('swal:toast', fn ($name, $params) => ocoToast($params)['icon'] === 'error');

    expect($order->fresh()->status?->key)->toBe('confirmed');
});

// ——— Rate note: unpriced / zero-cost announced prices are flagged at send ———

test('the gateway flags a carrier send with no announced price as unpriced', function () {
    [$user, $store, $membership] = ocoUser(StoreRoleEnum::OWNER->value);
    $order = ocoOrder($store, 'confirmed');

    $result = app(OrderShippingGateway::class)->send($order, $order->shipping_provider_id, null, $membership);

    expect($result['order']->fresh()->status?->key)->toBe('shipped')
        ->and($result['rate_note'])->toBe('unpriced');
});

test('the gateway flags a carrier send whose announced price is zero', function () {
    [$user, $store, $membership] = ocoUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = ocoGeography();

    $order = ocoOrder($store, 'confirmed');

    DeliveryRate::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $order->shipping_provider_id,
        'state_id' => $state->id,
        'home_cost' => 0,
        'office_cost' => 0,
        'source' => 'manual',
        'is_active' => true,
    ]);

    $result = app(OrderShippingGateway::class)->send($order, $order->shipping_provider_id, null, $membership);

    expect($result['rate_note'])->toBe('zero_cost');
});

test('the gateway resolves a priced announced rate without a warning', function () {
    [$user, $store, $membership] = ocoUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = ocoGeography();

    $order = ocoOrder($store, 'confirmed');

    DeliveryRate::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $order->shipping_provider_id,
        'state_id' => $state->id,
        'home_cost' => 900,
        'office_cost' => 350,
        'source' => 'manual',
        'is_active' => true,
    ]);

    $result = app(OrderShippingGateway::class)->send($order, $order->shipping_provider_id, null, $membership);

    expect($result['rate_note'])->toBeNull();
});

test('a disabled announced rate is treated as unpriced at send', function () {
    [$user, $store, $membership] = ocoUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = ocoGeography();

    $order = ocoOrder($store, 'confirmed');

    DeliveryRate::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $order->shipping_provider_id,
        'state_id' => $state->id,
        'home_cost' => 900,
        'office_cost' => 350,
        'source' => 'manual',
        'is_active' => false,
    ]);

    $result = app(OrderShippingGateway::class)->send($order, $order->shipping_provider_id, null, $membership);

    expect($result['rate_note'])->toBe('unpriced');
});