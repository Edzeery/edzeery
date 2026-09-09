<?php

use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\StopdeskPoint;
use App\Enums\Store\StoreRoleEnum;
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

function sdEnv(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Stopdesk Sync Store',
        'slug' => 'stopdesk-sync-'.uniqid(),
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

function sdProvider(Store $store, ?string $carrierCode = null): ShippingProvider
{
    $carrier = null;

    if ($carrierCode) {
        $platform = CarrierPlatform::create(['name' => ucfirst($carrierCode), 'slug' => $carrierCode.'-'.uniqid(), 'is_active' => true]);
        $carrier = Carrier::create([
            'platform_id' => $platform->id,
            'name' => strtoupper($carrierCode),
            'code' => $carrierCode,
            'credential_fields' => [],
            'is_active' => true,
        ]);
    }

    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => $carrierCode ? 'NOEST DZ' : 'Local Rider',
        'code' => $carrierCode ?? 'manual-rider',
        'carrier_id' => $carrier?->id,
        'credentials' => $carrierCode ? ['api_token' => 'token-'.uniqid(), 'api_base' => 'https://noest.test/api/public'] : [],
        'is_active' => true,
        'is_default' => false,
    ]);
}

function sdVolt(array $userStore)
{
    [$user, $store] = $userStore;
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    return Volt::test('merchant.delivery.stopdesk');
}

test('the sync card lists only carrier-backed companies with a configured integration', function () {
    [$user, $store] = sdEnv();
    $manual = sdProvider($store);
    $carrier = sdProvider($store, 'noest');

    $volt = sdVolt([$user, $store]);

    $candidates = collect($volt->get('syncCandidates'));

    expect($candidates->pluck('id')->all())->toBe([$carrier->id])
        ->and($volt->assertSee('Sync pickup points')->assertSee($carrier->name))
        ->and($candidates->pluck('id'))->not->toContain($manual->id);
});

test('carrier-synced points render a synced badge while manual points do not', function () {
    [$user, $store] = sdEnv();
    $carrier = sdProvider($store, 'noest');

    $synced = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $carrier->id,
        'name' => 'Synced Desk',
        'address' => '',
        'external_code' => '16',
        'is_active' => true,
    ]);

    $manual = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $carrier->id,
        'name' => 'Manual Desk',
        'address' => '',
        'external_code' => null,
        'is_active' => true,
    ]);

    $volt = sdVolt([$user, $store]);

    $points = collect($volt->get('stopdeskPoints'))->keyBy('id');

    expect($points[$synced->id]['synced'])->toBeTrue()
        ->and($points[$manual->id]['synced'])->toBeFalse()
        ->and($volt->assertSee('Synced Desk')->assertSee('Manual Desk')->assertSee('Synced'));
});

test('clicking sync pulls the carrier desks and upserts the local points', function () {
    [$user, $store] = sdEnv();
    $carrier = sdProvider($store, 'noest');

    $existing = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $carrier->id,
        'name' => 'Stale Desk Name',
        'address' => '',
        'external_code' => '16',
        'is_active' => true,
    ]);

    Http::fake([
        'noest.test/*' => Http::response([
            ['code' => '16', 'name' => 'Desk Bab Ezzouar', 'commune' => 'Bab Ezzouar', 'address' => 'Rue 01', 'phones' => '0551234567'],
            ['code' => '31', 'name' => 'Desk Oran', 'commune' => 'Oran', 'address' => 'Rue 02', 'phones' => '0550000001'],
        ]),
    ]);

    $volt = sdVolt([$user, $store])
        ->set('selectedSyncProviderId', (string) $carrier->id)
        ->call('syncStopdesk')
        ->assertDispatched('swal', type: 'success');

    $points = StopdeskPoint::where('store_id', $store->id)
        ->where('shipping_provider_id', $carrier->id)
        ->orderBy('external_code')
        ->get();

    expect($points->count())->toBe(2)
        ->and($points->pluck('external_code')->all())->toBe(['16', '31'])
        ->and($points->firstWhere('external_code', '16')->name)->toBe('Desk Bab Ezzouar')
        ->and($existing->refresh()->name)->toBe('Desk Bab Ezzouar');

    $volt->assertSet('stopdeskPoints.0.synced', true);
});

test('a company without credentials syncs to an empty info message', function () {
    [$user, $store] = sdEnv();

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
        'name' => 'NOEST DZ (naked)',
        'code' => 'noest',
        'carrier_id' => $carrier->id,
        'credentials' => [],
        'is_active' => true,
    ]);

    sdVolt([$user, $store])
        ->set('selectedSyncProviderId', (string) $provider->id)
        ->call('syncStopdesk')
        ->assertDispatched('swal', type: 'info');
});