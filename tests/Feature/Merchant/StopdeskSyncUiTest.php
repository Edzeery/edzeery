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

test('the sidebar lists every company while sync targets only carrier-backed ones', function () {
    [$user, $store] = sdEnv();
    $manual = sdProvider($store);
    $carrier = sdProvider($store, 'noest');

    $volt = sdVolt([$user, $store]);

    $candidates = collect($volt->get('syncCandidates'));

    expect($candidates->pluck('id')->all())->toBe([$carrier->id])
        ->and($candidates->pluck('id'))->not->toContain($manual->id)
        ->and($volt->assertSee($carrier->name)->assertSee($manual->name));

    // The sync action lives inside the chosen company panel and only appears
    // for companies with a configured carrier integration.
    $volt->call('selectProvider', (string) $carrier->id)
        ->assertSee(__('merchant_panel.stopdesk_sync_button'))
        ->assertSee(__('merchant_panel.new_stopdesk'));

    $volt->call('selectProvider', (string) $manual->id)
        ->assertDontSee(__('merchant_panel.stopdesk_sync_button'));
});

test('carrier-synced points render a synced badge while manual points do not', function () {
    [$user, $store] = sdEnv();
    $carrier = sdProvider($store, 'noest');

    $country = \App\Models\Locations\Country::create(['name' => 'Algeria', 'code' => 'DZ', 'is_active' => true]);
    $state = \App\Models\Locations\State::create([
        'country_id' => $country->id,
        'state_code' => '16',
        'name' => 'Algiers',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $synced = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $carrier->id,
        'state_id' => $state->id,
        'name' => 'Synced Desk',
        'address' => '',
        'external_code' => '16',
        'is_active' => true,
    ]);

    $manual = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $carrier->id,
        'state_id' => $state->id,
        'name' => 'Manual Desk',
        'address' => '',
        'external_code' => null,
        'is_active' => true,
    ]);

    $volt = sdVolt([$user, $store]);

    // No provider selected yet → no grouped grid.
    expect($volt->get('stateRows'))->toBe([]);

    $volt->call('selectProvider', (string) $carrier->id);

    $points = collect($volt->get('pointsByState'))->get($state->id);

    expect($points)->toHaveCount(2)
        ->and(collect($points)->firstWhere('id', $synced->id)['synced'])->toBeTrue()
        ->and(collect($points)->firstWhere('id', $manual->id)['synced'])->toBeFalse();

    $volt->call('openOfficesPopup', (string) $state->id)
        ->assertSee('Synced Desk')
        ->assertSee('Manual Desk')
        ->assertSee(__('merchant_panel.stopdesk_synced'));
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
        ->call('selectProvider', (string) $carrier->id)
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

    // This fixture seeds no states → desk codes map to no wilaya and every
    // point lands under the "unassigned" bucket, flagged as synced.
    $desks = collect($volt->get('pointsByState'))->get('__unassigned__');

    expect($desks)->toHaveCount(2)
        ->and(collect($desks)->every(fn ($point) => $point['synced']))->toBeTrue();
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
        ->call('selectProvider', (string) $provider->id)
        ->set('selectedSyncProviderId', (string) $provider->id)
        ->call('syncStopdesk')
        ->assertDispatched('swal', type: 'info');
});

test('editing an office from the state popup keeps the popup open and refreshes its list', function () {
    [$user, $store] = sdEnv();
    $provider = sdProvider($store);

    $country = \App\Models\Locations\Country::create(['name' => 'Deskland', 'code' => 'DL', 'is_active' => true]);
    $state = \App\Models\Locations\State::create([
        'country_id' => $country->id,
        'state_code' => '16',
        'name' => 'Algiers',
        'is_active' => true,
        'is_cod_available' => true,
    ]);
    $city = \App\Models\Locations\City::create([
        'state_id' => $state->id,
        'name' => 'Bab Ezzouar',
        'post_code' => '16028',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $point = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'name' => 'Desk Bab Ezzouar',
        'address' => '',
        'is_active' => true,
    ]);

    $volt = sdVolt([$user, $store])
        ->call('selectProvider', (string) $provider->id)
        ->call('openOfficesPopup', (string) $state->id);

    expect($volt->get('showOfficesPopup'))->toBeTrue();

    $volt->call('openStopdeskModal', (string) $point->id);

    expect($volt->get('showOfficesPopup'))->toBeTrue()
        ->and($volt->get('showStopdeskModal'))->toBeTrue();

    $volt->set('stopdeskForm.name', 'Desk Edited')
        ->call('saveStopdesk');

    expect($volt->get('showStopdeskModal'))->toBeFalse()
        ->and($volt->get('showOfficesPopup'))->toBeTrue()
        ->and(collect($volt->get('popupOffices'))->pluck('name'))->toContain('Desk Edited')
        ->and($point->refresh()->name)->toBe('Desk Edited');
});

test('the state offices popup groups offices by municipality and exposes the state-wide group', function () {
    [$user, $store] = sdEnv();
    $provider = sdProvider($store);

    $country = \App\Models\Locations\Country::create(['name' => 'Deskana', 'code' => 'DA', 'is_active' => true]);
    $state = \App\Models\Locations\State::create([
        'country_id' => $country->id,
        'state_code' => '16',
        'name' => 'Algiers',
        'is_active' => true,
        'is_cod_available' => true,
    ]);
    $cityA = \App\Models\Locations\City::create([
        'state_id' => $state->id,
        'name' => 'Bab Ezzouar',
        'post_code' => '16028',
        'is_active' => true,
        'is_cod_available' => true,
    ]);
    $cityB = \App\Models\Locations\City::create([
        'state_id' => $state->id,
        'name' => 'Cheraga',
        'post_code' => '16027',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'city_id' => $cityA->id,
        'name' => 'Desk Bab Ezzouar',
        'address' => '',
        'is_active' => true,
    ]);
    StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'city_id' => $cityB->id,
        'name' => 'Desk Cheraga',
        'address' => '',
        'is_active' => true,
    ]);
    StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'city_id' => null,
        'name' => 'Regional Hub',
        'address' => '',
        'is_active' => true,
    ]);

    $volt = sdVolt([$user, $store])
        ->call('selectProvider', (string) $provider->id)
        ->call('openOfficesPopup', (string) $state->id);

    $volt->assertSee('Bab Ezzouar')
        ->assertSee('Cheraga');

    expect($volt->html())->toContain(__('merchant_panel.stopdesk_state_wide'));
});