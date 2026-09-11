<?php

use App\Domains\Order\Services\OrderTrackingService;
use App\Domains\Shipping\Adapters\NoestIntegrationAdapter;
use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Services\OrderShippingGateway;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Orders\OrderTrackingHistory;
use App\Models\Stores\Store;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function ocvdStore(): Store
{
    return Store::create([
        'user_id' => \App\Models\User::factory()->create()->id,
        'name' => 'Valid Dispatch Store',
        'slug' => 'ocvd-'.uniqid(),
        'status' => 'active',
    ]);
}

function ocvdProvider(Store $store, ?string $carrierCode = 'noest'): ShippingProvider
{
    $platform = CarrierPlatform::updateOrCreate(
        ['slug' => $carrierCode],
        ['name' => strtoupper($carrierCode), 'is_active' => true],
    );

    $carrier = Carrier::updateOrCreate(
        ['code' => $carrierCode],
        [
            'platform_id' => $platform->id,
            'name' => strtoupper($carrierCode),
            'is_active' => true,
        ],
    );

    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => strtoupper($carrierCode).' DZ',
        'code' => $carrierCode,
        'carrier_id' => $carrier->id,
        'credentials' => [
            'api_token' => 'tok-'.uniqid(),
            'guid' => 'guid-'.uniqid(),
            'api_base' => 'https://noest.test/api/public',
        ],
        'is_active' => true,
    ]);
}

function ocvdOrder(Store $store, ShippingProvider $provider, ?string $trackingNumber = null, array $extra = []): Order
{
    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Dispatch Customer',
        'phone' => '0550000000',
        'status' => true,
    ]);

    $order = Order::create(array_merge([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'number' => 'OCVD-'.substr(uniqid(), -6),
        'total_amount' => 1500,
        'shipping_provider_id' => $provider->id,
        'payment_method' => 'cod',
        'shipping_cost' => 0,
    ], $extra));

    if ($trackingNumber !== null) {
        OrderTracking::create([
            'store_id' => $store->id,
            'order_id' => $order->id,
            'shipping_provider_id' => $provider->id,
            'tracking_number' => $trackingNumber,
            'shipped_at' => now(),
        ]);
    }

    return $order->fresh();
}

test('validateOrder posts to NOEST /valid/order and returns ok on success', function () {
    $store = ocvdStore();
    $provider = ocvdProvider($store);

    Http::fake([
        'noest.test/*' => Http::response(['success' => true]),
    ]);

    $result = app(NoestIntegrationAdapter::class)->validateOrder($provider, 'TRK123456');

    expect($result['ok'])->toBeTrue();

    Http::assertSent(function (Request $request) use ($provider): bool {
        return str_ends_with($request->url(), '/valid/order')
            && $request['user_guid'] === $provider->credentials['guid']
            && $request['tracking'] === 'TRK123456';
    });
});

test('validateOrder surfaces NOEST logical failures even on HTTP 200', function () {
    $store = ocvdStore();
    $provider = ocvdProvider($store);

    Http::fake([
        'noest.test/*' => Http::response(['success' => false, 'message' => 'Commande déjà validée']),
    ]);

    $result = app(NoestIntegrationAdapter::class)->validateOrder($provider, 'TRK123456');

    expect($result['ok'])->toBeFalse()
        ->and($result['message'])->toBe('Commande déjà validée');
});

test('validateOrder guards against missing credentials', function () {
    $store = ocvdStore();
    $provider = ocvdProvider($store);
    $provider->update(['credentials' => []]);

    Http::fake();

    $result = app(NoestIntegrationAdapter::class)->validateOrder($provider, 'TRK123456');

    expect($result['ok'])->toBeFalse();

    Http::assertNothingSent();
});

test('validateOrders normalizes a fully passed batch', function () {
    $store = ocvdStore();
    $provider = ocvdProvider($store);

    Http::fake([
        'noest.test/*' => Http::response([
            'success' => true,
            'passed' => ['T1' => true, 'T2' => true],
            'failed' => [],
        ]),
    ]);

    $result = app(NoestIntegrationAdapter::class)->validateOrders($provider, ['T1', 'T2']);

    expect($result['ok'])->toBeTrue()
        ->and($result['validated'])->toBe(['T1', 'T2'])
        ->and($result['failed'])->toBe([]);

    Http::assertSent(function (Request $request): bool {
        $sent = (array) $request['trackings'];
        $expected = ['T1', 'T2'];
        sort($sent);
        sort($expected);

        return str_ends_with($request->url(), '/valid/orders')
            && $sent === $expected;
    });
});

test('validateOrders normalizes partial failure with nested validation messages', function () {
    $store = ocvdStore();
    $provider = ocvdProvider($store);

    Http::fake([
        'noest.test/*' => Http::response([
            'success' => false,
            'passed' => ['T1' => true],
            'failed' => [
                'T2' => ['tracking' => ['The selected tracking is invalid.']],
                'T3' => 'Insufficient stock for this order',
            ],
        ]),
    ]);

    $result = app(NoestIntegrationAdapter::class)->validateOrders($provider, ['T1', 'T2', 'T3']);

    expect($result['ok'])->toBeFalse()
        ->and($result['validated'])->toBe(['T1'])
        ->and($result['failed']['T2'])->toContain('The selected tracking is invalid.')
        ->and($result['failed']['T3'])->toBe('Insufficient stock for this order');
});

test('gateway validate persists carrier_validated_at and history on success', function () {
    $store = ocvdStore();
    $provider = ocvdProvider($store);
    $order = ocvdOrder($store, $provider, 'TRK-SHIP-1');

    Http::fake([
        'noest.test/*' => Http::response(['success' => true]),
    ]);

    $result = app(OrderShippingGateway::class)->validate($order);

    expect($result['ok'])->toBeTrue();

    $tracking = app(OrderTrackingService::class)->currentTracking($order->fresh());

    expect($tracking)->not->toBeNull()
        ->and($tracking->isCarrierValidated())->toBeTrue()
        ->and($tracking->carrier_validation_error)->toBeNull();

    $history = OrderTrackingHistory::where('order_id', $order->id)->latest('created_at')->first();

    expect($history->status)->toBe('carrier_validated');
});

test('gateway validate persists carrier_validation_error on adapter failure', function () {
    $store = ocvdStore();
    $provider = ocvdProvider($store);
    $order = ocvdOrder($store, $provider, 'TRK-SHIP-2');

    Http::fake([
        'noest.test/*' => Http::response(['success' => false, 'message' => 'Stock insuffisant']),
    ]);

    $result = app(OrderShippingGateway::class)->validate($order);

    expect($result['ok'])->toBeFalse()
        ->and($result['message'])->toBe('Stock insuffisant');

    $tracking = app(OrderTrackingService::class)->currentTracking($order->fresh());

    expect($tracking->isCarrierValidated())->toBeFalse()
        ->and($tracking->carrier_validation_error)->toBe('Stock insuffisant');
});

test('gateway validate refuses already validated shipments without calling the carrier', function () {
    $store = ocvdStore();
    $provider = ocvdProvider($store);
    $order = ocvdOrder($store, $provider, 'TRK-SHIP-3');

    app(OrderTrackingService::class)->currentTracking($order)->update([
        'carrier_validated_at' => now(),
    ]);

    Http::fake();

    $result = app(OrderShippingGateway::class)->validate($order->fresh());

    expect($result['ok'])->toBeFalse()
        ->and($result['error'])->toBe(__('order_flow.shipment_already_validated'));

    Http::assertNothingSent();
});

test('gateway validate blocks rider-leg orders without a carrier', function () {
    $store = ocvdStore();
    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => Customer::create([
            'store_id' => $store->id,
            'name' => 'Rider Leg',
            'phone' => '0550000001',
            'status' => true,
        ])->id,
        'number' => 'OCVD-'.substr(uniqid(), -6),
        'total_amount' => 1500,
        'payment_method' => 'cod',
        'shipping_cost' => 0,
    ]);

    Http::fake();

    $result = app(OrderShippingGateway::class)->validate($order);

    expect($result['ok'])->toBeFalse()
        ->and($result['error'])->toBe(__('order_flow.validation_carrier_required'));

    Http::assertNothingSent();
});

test('gateway validate requires a carrier tracking number', function () {
    $store = ocvdStore();
    $provider = ocvdProvider($store);
    $order = ocvdOrder($store, $provider, null);

    Http::fake();

    $result = app(OrderShippingGateway::class)->validate($order);

    expect($result['ok'])->toBeFalse()
        ->and($result['error'])->toBe(__('order_flow.validation_tracking_required'));

    Http::assertNothingSent();
});

test('gateway validate hard-fails when the carrier does not support validation', function () {
    $store = ocvdStore();
    $provider = ocvdProvider($store, 'ups');
    $order = ocvdOrder($store, $provider, 'TRK-UPS-1');

    Http::fake();

    $result = app(OrderShippingGateway::class)->validate($order);

    expect($result['ok'])->toBeFalse()
        ->and($result['error'])->toBe(__('order_flow.carrier_validation_not_supported'));

    Http::assertNothingSent();
});
