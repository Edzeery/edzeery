<?php

use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Orders\OrderTrackingHistory;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Facades\Http;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function sasOwner(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Sync All Tracking Store',
        'slug' => 'sync-all-'.uniqid(),
        'status' => 'active',
    ]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => StoreRoleEnum::OWNER->value,
    ]);

    return [$user, $store];
}

function sasProvider(Store $store): ShippingProvider
{
    $platform = CarrierPlatform::create(['name' => 'Noest TW', 'slug' => 'noest-tw-'.uniqid(), 'is_active' => true]);

    $carrier = Carrier::create([
        'platform_id' => $platform->id,
        'name' => 'NOEST TW',
        'code' => 'noest',
        'credential_fields' => [],
        'is_active' => true,
    ]);

    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'NOEST TW',
        'code' => 'noest',
        'carrier_id' => $carrier->id,
        'credentials' => ['api_token' => 'token-'.uniqid(), 'guid' => 'guid-'.uniqid(), 'api_base' => 'https://noest.test/api/public'],
        'is_active' => true,
        'shipment_types_enabled' => ['delivery'],
    ]);
}

function sasOrder(Store $store, ShippingProvider $provider): Order
{
    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Sync All '.fake()->unique()->firstName(),
        'phone' => '0550'.fake()->unique()->numerify('######'),
        'status' => true,
    ]);

    $status = Status::system()->forType('order')->where('key', 'shipped')->firstOrFail();

    return Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => $status->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 900,
        'shipping_cost' => 0,
        'shipping_provider_id' => $provider->id,
        'delivery_type' => 'home',
    ]);
}

function sasTracking(Store $store, Order $order, ShippingProvider $provider, string $trackingNumber, ?string $status = null): OrderTracking
{
    return OrderTracking::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'shipping_provider_id' => $provider->id,
        'tracking_number' => $trackingNumber,
        'tracking_status' => $status,
    ]);
}

function sasResponse(string ...$trackingNumbers): array
{
    $data = [];

    foreach ($trackingNumbers as $number) {
        $data[$number] = [
            'OrderInfo' => ['id' => '1', 'tracking' => $number],
            'activity' => [
                ['date' => now()->subDay()->toDateTimeString(), 'event' => 'En cours', 'event_key' => 'validation_reception'],
                ['date' => now()->toDateTimeString(), 'event' => 'Livré', 'event_key' => 'livre'],
            ],
        ];
    }

    return $data;
}

function sasVolt(array $ctx): object
{
    [$user, $store] = $ctx;
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    return Volt::test('merchant.tracking.index');
}

test('syncAllTracking includes a provider-backed numbered tracking whose status is NULL', function () {
    [$user, $store] = sasOwner();
    $provider = sasProvider($store);
    $order = sasOrder($store, $provider);

    $tracking = sasTracking($store, $order, $provider, 'YESH-28B-20576482', null);

    Http::fake([
        'noest.test/*' => Http::response(sasResponse('YESH-28B-20576482')),
    ]);

    sasVolt([$user, $store])
        ->call('syncAllTracking')
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success');

    $tracking->refresh();

    expect($tracking->tracking_status)->toBe(OrderTrackingStatus::DELIVERED->value)
        ->and($tracking->delivered_at)->not->toBeNull()
        ->and($tracking->last_synced_at)->not->toBeNull()
        ->and(OrderTrackingHistory::where('order_tracking_id', $tracking->id)->where('status', OrderTrackingStatus::DELIVERED->value)->exists())->toBeTrue();

    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/get/trackings/info'));
});

test('syncAllTracking polls NULL-status and open-status rows together in a single run', function () {
    [$user, $store] = sasOwner();
    $provider = sasProvider($store);

    $nullTracking = sasTracking($store, sasOrder($store, $provider), $provider, 'YESH-28B-20576482', null);
    $openTracking = sasTracking($store, sasOrder($store, $provider), $provider, 'YESH-28B-20600000', OrderTrackingStatus::IN_TRANSIT->value);

    Http::fake([
        'noest.test/*' => Http::response(sasResponse('YESH-28B-20576482', 'YESH-28B-20600000')),
    ]);

    sasVolt([$user, $store])
        ->call('syncAllTracking')
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success');

    expect($nullTracking->refresh()->tracking_status)->toBe(OrderTrackingStatus::DELIVERED->value)
        ->and($openTracking->refresh()->tracking_status)->toBe(OrderTrackingStatus::DELIVERED->value);
});
