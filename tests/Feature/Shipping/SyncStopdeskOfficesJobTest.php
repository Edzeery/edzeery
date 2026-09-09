<?php

use App\Domains\Shipping\Jobs\SyncStopdeskOfficesJob;
use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\StopdeskPoint;
use App\Models\Stores\Store;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\assertDatabaseCount;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function soStore(): Store
{
    return Store::create([
        'user_id' => \App\Models\User::factory()->create()->id,
        'name' => 'Office Sync Store',
        'slug' => 'office-sync-'.uniqid(),
        'status' => 'active',
    ]);
}

function soProvider(Store $store, ?string $carrierCode = null, bool $hasToken = true): ShippingProvider
{
    $carrier = null;

    if ($carrierCode) {
        $platform = CarrierPlatform::create(['name' => 'Noest', 'slug' => 'noest-'.uniqid(), 'is_active' => true]);
        $carrier = Carrier::create([
            'platform_id' => $platform->id,
            'name' => 'NOEST',
            'code' => $carrierCode,
            'credential_fields' => [],
            'is_active' => true,
        ]);
    }

    $credentials = $carrierCode && $hasToken
        ? ['api_token' => 'token-'.uniqid(), 'api_base' => 'https://noest.test/api/public']
        : [];

    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => $carrierCode ? 'NOEST DZ' : 'Manual',
        'code' => $carrierCode ?? 'manual-'.uniqid(),
        'carrier_id' => $carrier?->id,
        'credentials' => $credentials,
        'is_active' => true,
        'is_default' => false,
    ]);
}

test('the job pulls carrier desks for a carrier-backed provider', function () {
    $store = soStore();
    $provider = soProvider($store, 'noest');

    Http::fake([
        'noest.test/*' => Http::response([
            ['code' => '16', 'name' => 'Desk Alger', 'commune' => 'Alger', 'address' => '', 'phones' => ''],
            ['code' => '31', 'name' => 'Desk Oran', 'commune' => 'Oran', 'address' => '', 'phones' => ''],
        ]),
    ]);

    (new SyncStopdeskOfficesJob($store->id, $provider->id))->handle();

    $points = StopdeskPoint::where('store_id', $store->id)->where('shipping_provider_id', $provider->id)->get();

    expect($points->count())->toBe(2)
        ->and($points->pluck('external_code')->all())->toBe(['16', '31']);
});

test('the job skips providers without a carrier integration', function () {
    $store = soStore();
    $provider = soProvider($store);

    (new SyncStopdeskOfficesJob($store->id, $provider->id))->handle();

    expect(StopdeskPoint::where('store_id', $store->id)->count())->toBe(0);
});

test('a provider without credentials fails silently and the job completes', function () {
    $store = soStore();
    $provider = soProvider($store, 'noest', hasToken: false);

    $job = new SyncStopdeskOfficesJob($store->id, $provider->id);

    expect(fn () => $job->handle())->not->toThrow(\Throwable::class);

    assertDatabaseCount('stopdesk_points', 0);
});

test('a carrier failure isolates and does not kill remaining providers', function () {
    $store = soStore();
    $naked = soProvider($store, 'noest', hasToken: false);
    $healthy = soProvider($store, 'noest', hasToken: true);

    Http::fake([
        'noest.test/*' => Http::response([
            ['code' => '16', 'name' => 'Desk Alger', 'commune' => 'Alger', 'address' => '', 'phones' => ''],
            ['code' => '31', 'name' => 'Desk Oran', 'commune' => 'Oran', 'address' => '', 'phones' => ''],
        ]),
    ]);

    $job = new SyncStopdeskOfficesJob($store->id);

    expect(fn () => $job->handle())->not->toThrow(\Throwable::class);

    $healthyPoints = StopdeskPoint::where('store_id', $store->id)
        ->where('shipping_provider_id', $healthy->id)
        ->count();

    $nakedPoints = StopdeskPoint::where('store_id', $store->id)
        ->where('shipping_provider_id', $naked->id)
        ->count();

    expect($healthyPoints)->toBe(2)
        ->and($nakedPoints)->toBe(0);
});

test('inactive providers are excluded from the scheduled pull', function () {
    $store = soStore();
    $provider = soProvider($store, 'noest');

    Http::fake(['noest.test/*' => Http::response([])]);
    $provider->update(['is_active' => false]);

    (new SyncStopdeskOfficesJob($store->id, $provider->id))->handle();

    expect(StopdeskPoint::where('store_id', $store->id)->count())->toBe(0);
});