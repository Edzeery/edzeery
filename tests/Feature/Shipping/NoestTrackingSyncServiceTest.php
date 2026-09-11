<?php

use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Services\NoestTrackingSyncService;
use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Orders\OrderTrackingHistory;
use App\Models\Stores\Store;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function ntssEnv(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Noest Service Store',
        'slug' => 'ntss-'.uniqid(),
        'status' => 'active',
    ]);

    $platform = CarrierPlatform::create(['name' => 'Noest', 'slug' => 'noest-'.uniqid(), 'is_active' => true]);

    $carrier = Carrier::create([
        'platform_id' => $platform->id,
        'name' => 'NOEST',
        'code' => 'noest',
        'credential_fields' => [],
        'is_active' => true,
    ]);

    $provider = ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'NOEST DZ',
        'code' => 'noest',
        'carrier_id' => $carrier->id,
        'credentials' => ['api_token' => 'token-'.uniqid(), 'api_base' => 'https://noest.test/api/public'],
        'is_active' => true,
    ]);

    return [$user, $store, $provider];
}

function ntssTracking(Store $store, ShippingProvider $provider, string $trackingNumber, ?string $status = null): OrderTracking
{
    $order = Order::create([
        'store_id' => $store->id,
        'number' => 'NOEST-'.substr(uniqid(), -6),
        'total_amount' => 1200.00,
    ]);

    return OrderTracking::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'shipping_provider_id' => $provider->id,
        'tracking_number' => $trackingNumber,
        'tracking_status' => $status ?? OrderTrackingStatus::SHIPPED->value,
    ]);
}

function ntssEntry(ShippingProvider $provider, string $trackingNumber, array $rows): array
{
    $activity = [];

    foreach (array_values($rows) as $index => $row) {
        $activity[] = [
            'event' => $row['event'],
            'event_key' => $row['event_key'] ?? '',
            'causer' => $row['causer'] ?? 'NOEST',
            'badge-class' => 'badge-primary',
            'date' => '2024-02-10 '.str_pad((string) (10 + $index), 2, '0', STR_PAD_LEFT).':'.str_pad((string) ($index * 5), 2, '0', STR_PAD_LEFT).':00',
        ];
    }

    return [
        (string) $trackingNumber => [
            'OrderInfo' => [
                'tracking' => (string) $trackingNumber,
                'reference' => 'REF0002',
                'client' => 'Sami Cherif',
                'phone' => '0660000000',
                'wilaya_id' => 16,
                'commune' => 'Kouba',
                'montant' => '1200.00',
            ],
            'recipientName' => 'Sami Cherif',
            'activity' => $activity,
            'deliveryAttempts' => [],
        ],
    ];
}

test('syncOne advances a single tracking to delivered and stamps delivered_at plus history', function () {
    [$user, $store, $provider] = ntssEnv();

    $tracking = ntssTracking($store, $provider, 'TRK-SVC-123456');

    Http::fake([
        'noest.test/*' => Http::response(ntssEntry($provider, 'TRK-SVC-123456', [
            ['event' => 'Uploaded to system', 'event_key' => 'upload'],
            ['event' => 'Validated', 'event_key' => 'customer_validation'],
            ['event' => 'Delivered', 'event_key' => 'livre'],
        ])),
    ]);

    $result = app(NoestTrackingSyncService::class)->syncOne($tracking);

    $tracking->refresh();

    expect($result['ok'])->toBeTrue()
        ->and($tracking->tracking_status)->toBe(OrderTrackingStatus::DELIVERED->value)
        ->and($tracking->carrier_status)->toBe('livre')
        ->and($tracking->delivered_at)->not->toBeNull()
        ->and($tracking->delivered_at->isSameDay('2024-02-10'))->toBeTrue()
        ->and($tracking->last_synced_at)->not->toBeNull()
        ->and(OrderTrackingHistory::where('order_tracking_id', $tracking->id)->where('status', OrderTrackingStatus::DELIVERED->value)->exists())->toBeTrue();
});

test('syncOne maps the return flow to returned and stamps returned_at', function () {
    [$user, $store, $provider] = ntssEnv();

    $tracking = ntssTracking($store, $provider, 'TRK-SVC-654321');

    Http::fake([
        'noest.test/*' => Http::response(ntssEntry($provider, 'TRK-SVC-654321', [
            ['event' => 'Return requested by partner', 'event_key' => 'return_asked_by_customer'],
            ['event' => 'Return in transit', 'event_key' => 'return_asked_by_hub'],
            ['event' => 'Return dispatched to partner', 'event_key' => 'return_dispatched_to_partenaire'],
        ])),
    ]);

    $result = app(NoestTrackingSyncService::class)->syncOne($tracking);

    $tracking->refresh();

    expect($result['ok'])->toBeTrue()
        ->and($tracking->tracking_status)->toBe(OrderTrackingStatus::RETURNED->value)
        ->and($tracking->returned_at)->not->toBeNull()
        ->and(OrderTrackingHistory::where('order_tracking_id', $tracking->id)->where('status', OrderTrackingStatus::RETURNED->value)->exists())->toBeTrue();
});

test('syncOne with unmapped events leaves the status untouched but refreshes last_synced_at', function () {
    [$user, $store, $provider] = ntssEnv();

    $tracking = ntssTracking($store, $provider, 'TRK-SVC-555555', OrderTrackingStatus::OUT_FOR_DELIVERY->value);

    Http::fake([
        'noest.test/*' => Http::response(ntssEntry($provider, 'TRK-SVC-555555', [
            ['event' => 'Price modified', 'event_key' => 'edit_price'],
        ])),
    ]);

    $result = app(NoestTrackingSyncService::class)->syncOne($tracking);

    $tracking->refresh();

    expect($result['ok'])->toBeTrue()
        ->and($tracking->tracking_status)->toBe(OrderTrackingStatus::OUT_FOR_DELIVERY->value)
        ->and($tracking->delivered_at)->toBeNull()
        ->and($tracking->returned_at)->toBeNull()
        ->and($tracking->last_synced_at)->not->toBeNull()
        ->and(OrderTrackingHistory::where('order_tracking_id', $tracking->id)->count())->toBe(0);
});

test('syncOne with unmapped events on a blank-status row falls back to in-transit so the shipment is never blank', function () {
    [$user, $store, $provider] = ntssEnv();

    $tracking = ntssTracking($store, $provider, 'TRK-SVC-444444');
    $tracking->update(['tracking_status' => null]);

    Http::fake([
        'noest.test/*' => Http::response(ntssEntry($provider, 'TRK-SVC-444444', [
            ['event' => 'Price modified', 'event_key' => 'edit_price'],
        ])),
    ]);

    $result = app(NoestTrackingSyncService::class)->syncOne($tracking);

    $tracking->refresh();

    expect($result['ok'])->toBeTrue()
        ->and($tracking->tracking_status)->toBe(OrderTrackingStatus::IN_TRANSIT->value)
        ->and($tracking->delivered_at)->toBeNull()
        ->and($tracking->returned_at)->toBeNull()
        ->and($tracking->last_synced_at)->not->toBeNull()
        ->and(OrderTrackingHistory::where('order_tracking_id', $tracking->id)->where('status', OrderTrackingStatus::IN_TRANSIT->value)->exists())->toBeTrue();

    // Re-polling with the same unmapped events adds no second history row.
    app(NoestTrackingSyncService::class)->syncOne($tracking);

    expect(OrderTrackingHistory::where('order_tracking_id', $tracking->id)->where('status', OrderTrackingStatus::IN_TRANSIT->value)->count())->toBe(1);
});

test('syncOne without a tracking number fails cleanly without side effects', function () {
    [$user, $store, $provider] = ntssEnv();

    $tracking = ntssTracking($store, $provider, 'TRK-SVC-NUM');

    $tracking->update(['tracking_number' => null]);

    $result = app(NoestTrackingSyncService::class)->syncOne($tracking);

    expect($result['ok'])->toBeFalse()
        ->and($result['error'])->toBe('no_number')
        ->and($tracking->refresh()->tracking_status)->toBe(OrderTrackingStatus::SHIPPED->value)
        ->and($tracking->last_synced_at)->toBeNull()
        ->and(OrderTrackingHistory::where('order_tracking_id', $tracking->id)->count())->toBe(0);
});

test('syncOne surfaces a failed provider request without touching the row', function () {
    [$user, $store, $provider] = ntssEnv();

    $tracking = ntssTracking($store, $provider, 'TRK-SVC-999999');

    Http::fake([
        'noest.test/*' => Http::response(['message' => 'Upstream down'], 503),
    ]);

    $result = app(NoestTrackingSyncService::class)->syncOne($tracking);

    expect($result['ok'])->toBeFalse()
        ->and($result['error'])->toBe('request_failed')
        ->and($tracking->refresh()->tracking_status)->toBe(OrderTrackingStatus::SHIPPED->value)
        ->and($tracking->last_synced_at)->toBeNull();
});

test('syncOne is idempotent — reapplying the same terminal status adds no history row', function () {
    [$user, $store, $provider] = ntssEnv();

    $tracking = ntssTracking($store, $provider, 'TRK-SVC-777777');

    Http::fake([
        'noest.test/*' => Http::response(ntssEntry($provider, 'TRK-SVC-777777', [
            ['event' => 'Delivered', 'event_key' => 'livre'],
        ])),
    ]);

    $service = app(NoestTrackingSyncService::class);

    $service->syncOne($tracking);
    $tracking->refresh();

    $firstSync = $tracking->last_synced_at;

    $service->syncOne($tracking);

    expect(OrderTrackingHistory::where('order_tracking_id', $tracking->id)->where('status', OrderTrackingStatus::DELIVERED->value)->count())->toBe(1)
        ->and($tracking->refresh()->last_synced_at->equalTo($firstSync))->toBeTrue();
});