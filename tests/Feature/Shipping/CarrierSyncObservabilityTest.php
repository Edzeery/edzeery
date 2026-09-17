<?php

use App\Domains\Shipping\Jobs\SyncNoestTrackingJob;
use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use App\Domains\Shipping\Models\CarrierSyncRun;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Stores\Store;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function csrEnv(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Sync Observability Store',
        'slug' => 'csr-store-'.uniqid(),
        'status' => 'active',
    ]);

    $platform = CarrierPlatform::create(['name' => 'Noest', 'slug' => 'csr-noest-'.uniqid(), 'is_active' => true]);

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
        'credentials' => ['api_token' => 'csr-token-'.uniqid(), 'api_base' => 'https://noest.test/api/public'],
        'is_active' => true,
    ]);

    return [$user, $store, $provider];
}

function csrOrder(Store $store): Order
{
    return Order::create([
        'store_id' => $store->id,
        'number' => 'CSR-'.substr(uniqid(), -6),
        'total_amount' => 1000.00,
    ]);
}

function csrTracking(Store $store, ShippingProvider $provider, string $trackingNumber): OrderTracking
{
    return OrderTracking::create([
        'store_id' => $store->id,
        'order_id' => csrOrder($store)->id,
        'shipping_provider_id' => $provider->id,
        'tracking_number' => $trackingNumber,
        'tracking_status' => OrderTrackingStatus::SHIPPED->value,
    ]);
}

function csrActivityEntry(string $trackingNumber): array
{
    return [
        $trackingNumber => [
            'OrderInfo' => [
                'tracking' => $trackingNumber,
                'reference' => 'REF',
                'client' => 'Ahmed Benali',
                'phone' => '0550505050',
                'wilaya_id' => 16,
                'commune' => 'Bab Ezzouar',
                'montant' => '1000.00',
            ],
            'recipientName' => 'Ahmed Benali',
            'activity' => [
                ['date' => '2024-01-15 12:00:00', 'event' => 'Uploaded to system', 'event_key' => 'upload'],
                ['date' => '2024-01-15 12:05:00', 'event' => 'Out for delivery', 'event_key' => 'fdr_activated'],
            ],
            'deliveryAttempts' => [],
        ],
    ];
}

test('a sync run persists one metrics row with per-outcome counters and logs the summary', function () {
    [$user, $store, $provider] = csrEnv();

    // 25 due trackings split as: chunk 1 = 20 (14 answered + 6 unknown on the
    // carrier side), chunk 2 = 5 whose request fails. Every counter must be
    // accumulated in-memory — the row is written once, after the loop.
    $trackings = collect(range(1, 25))
        ->map(fn ($i) => csrTracking($store, $provider, 'CSR-TRK-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT)));

    $answered = $trackings->take(14)->pluck('tracking_number')->mapWithKeys(fn ($n) => csrActivityEntry($n))->all();

    Http::fake([
        'noest.test/*' => Http::sequence()
            ->push($answered, 200)
            ->push(['message' => 'Upstream down'], 503),
    ]);

    (new SyncNoestTrackingJob($store->id))->handle();

    $run = CarrierSyncRun::where('shipping_provider_id', $provider->id)->get();

    expect($run)->toHaveCount(1);

    $run = $run->first();

    expect((string) $run->store_id)->toBe((string) $store->id)
        ->and($run->attempted)->toBe(25)
        ->and($run->updated)->toBe(14)
        ->and($run->unknown)->toBe(6)
        ->and($run->failed)->toBe(5)
        ->and($run->started_at)->not->toBeNull()
        ->and($run->finished_at)->not->toBeNull()
        ->and($run->finished_at->greaterThanOrEqualTo($run->started_at))->toBeTrue();

    // The 6 unanswered numbers are marked unknown on the carrier side.
    $trackings->slice(14, 6)->each(fn ($t) => expect($t->refresh()->carrier_unknown_at)->not->toBeNull());
    $trackings->take(14)->each(fn ($t) => expect($t->refresh()->carrier_unknown_at)->toBeNull());
    $trackings->slice(20, 5)->each(fn ($t) => expect($t->refresh()->carrier_unknown_at)->toBeNull());
});

test('empty due sets still record a zero-count run row', function () {
    [$user, $store, $provider] = csrEnv();

    Http::fake();

    (new SyncNoestTrackingJob($store->id))->handle();

    $run = CarrierSyncRun::where('shipping_provider_id', $provider->id)->first();

    expect($run)->not->toBeNull()
        ->and($run->attempted)->toBe(0)
        ->and($run->updated)->toBe(0)
        ->and($run->unknown)->toBe(0)
        ->and($run->failed)->toBe(0);
});

test('the carrier-sync:report command summarizes runs per provider', function () {
    [$user, $store, $provider] = csrEnv();

    CarrierSyncRun::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'started_at' => now()->subMinutes(5),
        'finished_at' => now()->subMinutes(5)->addSeconds(3),
        'attempted' => 10,
        'updated' => 8,
        'unknown' => 1,
        'failed' => 1,
    ]);

    $this->artisan('carrier-sync:report', ['--hours' => 24])
        ->expectsOutputToContain('NOEST DZ')
        ->expectsOutputToContain('8')
        ->assertSuccessful();
});

test('debug report output', function () {
    [$user, $store, $provider] = csrEnv();

    CarrierSyncRun::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'started_at' => now()->subMinutes(5),
        'finished_at' => now()->subMinutes(5)->addSeconds(3),
        'attempted' => 10,
        'updated' => 8,
        'unknown' => 1,
        'failed' => 1,
    ]);

    Artisan::call('carrier-sync:report', ['--hours' => 24]);
    dump(Artisan::output());
});