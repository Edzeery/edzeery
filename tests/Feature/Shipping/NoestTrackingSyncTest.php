<?php

use App\Domains\Shipping\Jobs\SyncNoestTrackingJob;
use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use App\Domains\Shipping\Models\ShippingProvider;
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

function ntEnv(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Noest Tracking Store',
        'slug' => 'nt-tracking-'.uniqid(),
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

function ntOrder(Store $store): Order
{
    return Order::create([
        'store_id' => $store->id,
        'number' => 'NOEST-'.substr(uniqid(), -6),
        'total_amount' => 1500.00,
    ]);
}

function ntTracking(Store $store, ShippingProvider $provider, string $trackingNumber, ?string $status = null): OrderTracking
{
    return OrderTracking::create([
        'store_id' => $store->id,
        'order_id' => ntOrder($store)->id,
        'shipping_provider_id' => $provider->id,
        'tracking_number' => $trackingNumber,
        'tracking_status' => $status ?? OrderTrackingStatus::SHIPPED->value,
    ]);
}

function ntEntry(ShippingProvider $provider, string $trackingNumber, array $rows): array
{
    $activity = [];

    foreach (array_values($rows) as $index => $row) {
        $activity[] = [
            'event' => $row['event'],
            'event_key' => $row['event_key'] ?? '',
            'causer' => $row['causer'] ?? 'NOEST',
            'badge-class' => 'badge-primary',
            'date' => '2024-01-15 '.str_pad((string) (12 + $index), 2, '0', STR_PAD_LEFT).':'.str_pad((string) ($index * 5), 2, '0', STR_PAD_LEFT).':00',
        ];
    }

    return [
        (string) $trackingNumber => [
            'OrderInfo' => [
                'tracking' => (string) $trackingNumber,
                'reference' => 'REF0001',
                'client' => 'Ahmed Benali',
                'phone' => '0550505050',
                'wilaya_id' => 16,
                'commune' => 'Bab Ezzouar',
                'montant' => '1500.00',
            ],
            'recipientName' => 'Ahmed Benali',
            'activity' => $activity,
            'deliveryAttempts' => [],
        ],
    ];
}

test('sync advances an open tracking to delivered and stamps delivered_at', function () {
    [$user, $store, $provider] = ntEnv();

    $tracking = ntTracking($store, $provider, 'TRK123456789');

    Http::fake([
        'noest.test/*' => Http::response(ntEntry($provider, 'TRK123456789', [
            ['event' => 'Uploaded to system', 'event_key' => 'upload'],
            ['event' => 'Validated', 'event_key' => 'customer_validation'],
            ['event' => 'Out for delivery', 'event_key' => 'fdr_activated'],
            ['event' => 'Delivery attempt', 'event_key' => 'mise_a_jour'],
            ['event' => 'Delivered', 'event_key' => 'livre'],
        ])),
    ]);

    (new SyncNoestTrackingJob($store->id))->handle();

    $tracking->refresh();

    expect($tracking->tracking_status)->toBe(OrderTrackingStatus::DELIVERED->value)
        ->and($tracking->carrier_label)->toBe('Delivered')
        ->and($tracking->carrier_status)->toBe('livre')
        ->and($tracking->delivered_at)->not->toBeNull()
        ->and($tracking->delivered_at->isSameDay('2024-01-15'))->toBeTrue()
        ->and($tracking->last_synced_at)->not->toBeNull()
        ->and($tracking->carrier_raw)->toBeArray()
        ->and(OrderTrackingHistory::where('order_tracking_id', $tracking->id)->where('status', OrderTrackingStatus::DELIVERED->value)->exists())->toBeTrue();
});

test('sync maps the return flow to returned and stamps returned_at', function () {
    [$user, $store, $provider] = ntEnv();

    $tracking = ntTracking($store, $provider, 'TRK987654321');

    Http::fake([
        'noest.test/*' => Http::response(ntEntry($provider, 'TRK987654321', [
            ['event' => 'Return requested by partner', 'event_key' => 'return_asked_by_customer'],
            ['event' => 'Return in transit', 'event_key' => 'return_asked_by_hub'],
            ['event' => 'Return dispatched to partner', 'event_key' => 'return_dispatched_to_partenaire'],
            ['event' => 'Return package transmitted to partner', 'event_key' => 'colis_retour_transmit_to_partner'],
        ])),
    ]);

    (new SyncNoestTrackingJob($store->id))->handle();

    $tracking->refresh();

    expect($tracking->tracking_status)->toBe(OrderTrackingStatus::RETURNED->value)
        ->and($tracking->returned_at)->not->toBeNull()
        ->and($tracking->returned_at->isSameDay('2024-01-15'))->toBeTrue()
        ->and(OrderTrackingHistory::where('order_tracking_id', $tracking->id)->where('status', OrderTrackingStatus::RETURNED->value)->exists())->toBeTrue();
});

test('unmapped events leave the previous status untouched', function () {
    [$user, $store, $provider] = ntEnv();

    $tracking = ntTracking($store, $provider, 'TRK555555555', OrderTrackingStatus::OUT_FOR_DELIVERY->value);

    Http::fake([
        'noest.test/*' => Http::response(ntEntry($provider, 'TRK555555555', [
            ['event' => 'Price modified', 'event_key' => 'edit_price'],
            ['event' => 'Information modified', 'event_key' => 'edited_informations'],
        ])),
    ]);

    (new SyncNoestTrackingJob($store->id))->handle();

    expect($tracking->refresh()->tracking_status)->toBe(OrderTrackingStatus::OUT_FOR_DELIVERY->value)
        ->and($tracking->delivered_at)->toBeNull()
        ->and($tracking->returned_at)->toBeNull()
        ->and(OrderTrackingHistory::where('order_tracking_id', $tracking->id)->count())->toBe(0);
});

test('a failed provider request is swallowed and the run continues', function () {
    [$user, $store, $provider] = ntEnv();

    $tracking = ntTracking($store, $provider, 'TRK999999999');

    Http::fake([
        'noest.test/*' => Http::response(['message' => 'Upstream down'], 503),
    ]);

    (new SyncNoestTrackingJob($store->id))->handle();

    expect($tracking->refresh()->tracking_status)->toBe(OrderTrackingStatus::SHIPPED->value)
        ->and($tracking->last_synced_at)->toBeNull();
});

test('terminal orders are not regressed and not re-polled once fresh', function () {
    [$user, $store, $provider] = ntEnv();

    $tracking = ntTracking($store, $provider, 'TRK777777777');

    Http::fake([
        'noest.test/*' => Http::response(ntEntry($provider, 'TRK777777777', [
            ['event' => 'Delivered', 'event_key' => 'livre'],
        ])),
    ]);

    (new SyncNoestTrackingJob($store->id))->handle();

    $tracking->refresh();
    expect($tracking->tracking_status)->toBe(OrderTrackingStatus::DELIVERED->value);

    $firstSync = $tracking->last_synced_at;

    Http::preventStrayRequests();

    (new SyncNoestTrackingJob($store->id))->handle();

    expect(OrderTrackingHistory::where('order_tracking_id', $tracking->id)->where('status', OrderTrackingStatus::DELIVERED->value)->count())->toBe(1)
        ->and($tracking->refresh()->last_synced_at->equalTo($firstSync))->toBeTrue();
});

test('the adapter posts the bearer-authorized tracking batch', function () {
    [$user, $store, $provider] = ntEnv();

    Http::fake([
        'noest.test/*' => Http::response(ntEntry($provider, 'TRK123456789', [
            ['event' => 'Uploaded to system', 'event_key' => 'upload'],
        ])),
    ]);

    $result = app(\App\Domains\Shipping\Adapters\NoestIntegrationAdapter::class)
        ->trackingsInfo($provider, ['TRK123456789']);

    expect($result)->toHaveKey('TRK123456789');

    Http::assertSent(fn ($request) => $request->url() === 'https://noest.test/api/public/get/trackings/info'
        && $request->hasHeader('Authorization', 'Bearer '.$provider->credentials['api_token'])
        && $request['trackings'] === ['TRK123456789']);
});