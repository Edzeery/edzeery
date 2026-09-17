<?php

use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Services\OrderShippingGateway;
use App\Enums\Store\OrderTrackingStatus;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Orders\OrderTrackingHistory;
use App\Models\Status;
use App\Models\Stores\Store;
use Illuminate\Support\Facades\Http;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

// ---------------------------------------------------------------------------
// Per-file fixtures (Pest loads helpers globally — keep the scu* prefix).
// ---------------------------------------------------------------------------

function scuStore(): Store
{
    return Store::create([
        'user_id' => \App\Models\User::factory()->create()->id,
        'name' => 'SCU Store',
        'slug' => 'scu-'.uniqid(),
        'status' => 'active',
    ]);
}

function scuProvider(Store $store): ShippingProvider
{
    $platform = CarrierPlatform::updateOrCreate(
        ['slug' => 'noest'],
        ['name' => 'NOEST', 'is_active' => true],
    );

    $carrier = Carrier::updateOrCreate(
        ['code' => 'noest'],
        ['platform_id' => $platform->id, 'name' => 'NOEST', 'is_active' => true],
    );

    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'NOEST DZ',
        'code' => 'noest',
        'carrier_id' => $carrier->id,
        'credentials' => [
            'api_token' => 'tok-'.uniqid(),
            'guid' => 'guid-'.uniqid(),
            'api_base' => 'https://noest.test/api/public',
        ],
        'is_active' => true,
    ]);
}

function scuOrder(Store $store, ShippingProvider $provider, string $statusKey = 'shipped'): Order
{
    $status = Status::system()
        ->forType('order')
        ->where('key', $statusKey)
        ->firstOrFail();

    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'SCU Customer',
        'phone' => '0550000001',
        'status' => true,
    ]);

    return Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => $status->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 1500,
        'shipping_provider_id' => $provider->id,
        'delivery_type' => 'home',
    ]);
}

function scuTracking(Order $order, ShippingProvider $provider, string $trackingNumber, bool $unknown = false): OrderTracking
{
    return OrderTracking::create([
        'store_id' => $order->store_id,
        'order_id' => $order->id,
        'shipping_provider_id' => $provider->id,
        'tracking_number' => $trackingNumber,
        'tracking_status' => OrderTrackingStatus::SHIPPED->value,
        'carrier_unknown_at' => $unknown ? now() : null,
    ]);
}

/* ───────────────────────── Cancel file ───────────────────────── */

test('cancel completes locally and records the fact when the tracking is marked unknown at the carrier', function () {
    [$store] = [scuStore()];
    $provider = scuProvider($store);
    $order = scuOrder($store, $provider, 'shipped');
    scuTracking($order, $provider, 'TRK-CANCEL-UNKNOWN', true);

    Http::fake([
        'noest.test/*' => Http::response(['success' => false, 'message' => 'The given data was invalid.'], 422),
    ]);

    $result = app(OrderShippingGateway::class)->cancel($order);

    expect($result['ok'])->toBeTrue()
        ->and($result['notice'] ?? null)->toBe('carrier_unknown');

    $tracking = OrderTracking::where('order_id', $order->id)->firstOrFail();

    expect($tracking->tracking_number)->toBeNull()
        ->and($tracking->tracking_status)->toBeNull()
        ->and($tracking->carrier_status)->toBe('cancelled')
        ->and($order->refresh()->status->key)->toBe('confirmed');

    $history = OrderTrackingHistory::where('order_tracking_id', $tracking->id)->where('status', 'cancelled')->firstOrFail();

    expect(($history->payload['carrier_not_found_at_cancel'] ?? false))->toBeTrue();

    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/delete/order')
        && $request['tracking'] === 'TRK-CANCEL-UNKNOWN');
});

test('cancel proceeds with a notice when the carrier itself reports the tracking not found', function () {
    [$store] = [scuStore()];
    $provider = scuProvider($store);
    $order = scuOrder($store, $provider, 'shipped');
    scuTracking($order, $provider, 'TRK-CANCEL-NF');

    Http::fake([
        'noest.test/*' => Http::response(['success' => false, 'message' => 'The given data was invalid.'], 422),
    ]);

    $result = app(OrderShippingGateway::class)->cancel($order);

    expect($result['ok'])->toBeTrue()
        ->and($result['notice'] ?? null)->toBe('carrier_unknown')
        ->and(OrderTracking::where('order_id', $order->id)->firstOrFail()->tracking_number)->toBeNull();
});

test('cancel stays hard-blocked for generic carrier failures on a still-known tracking', function () {
    [$store] = [scuStore()];
    $provider = scuProvider($store);
    $order = scuOrder($store, $provider, 'shipped');
    $tracking = scuTracking($order, $provider, 'TRK-CANCEL-LIVE');

    Http::fake([
        'noest.test/*' => Http::response(['success' => false, 'message' => 'Upstream down'], 500),
    ]);

    $result = app(OrderShippingGateway::class)->cancel($order);

    expect($result['ok'])->toBeFalse()
        ->and($result['error'] ?? null)->toBe('Upstream down')
        ->and(OrderTracking::where('order_id', $order->id)->firstOrFail()->tracking_number)->toBe('TRK-CANCEL-LIVE')
        ->and($order->refresh()->status->key)->toBe('shipped')
        ->and(OrderTrackingHistory::where('order_tracking_id', $tracking->id)->where('status', 'cancelled')->count())->toBe(0);
});