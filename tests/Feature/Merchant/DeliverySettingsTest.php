<?php

use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use App\Domains\Shipping\Models\DeliveryRate;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Models\Locations\State;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Database\Seeders\CarrierCatalogSeeder;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(CarrierCatalogSeeder::class);
});

function createDeliveryStore(string $storeRole = 'owner'): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Delivery Store',
        'slug' => 'delivery-store-' . uniqid(),
        'status' => 'active',
    ]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
    ]);

    return [$user, $store];
}

function deliverySeedLocations(): void
{
    test()->seed(Database\Seeders\Locations\ArabCountriesSeeder::class);
    test()->seed(Database\Seeders\Locations\AlgerianStatesSeeder::class);
}

test('owner can open the delivery page', function () {
    [$user, $store] = createDeliveryStore('owner');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.delivery')
        ->assertOk()
        ->assertSee(__('merchant_panel.delivery_settings'))
        ->assertSee(__('merchant_panel.tab_providers'));
});

test('staff without delivery permission is blocked from the page', function () {
    [$user, $store] = createDeliveryStore('staff');

    $this->actingAs($user)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.delivery', $store->slug))
        ->assertForbidden();
});

test('owner connects a carrier through the two-level platform/carrier select', function () {
    [$user, $store] = createDeliveryStore('owner');
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $platform = CarrierPlatform::where('slug', 'zr-express')->first();
    $carrier = Carrier::where('code', 'zrexpress_v2')->first();

    expect($carrier->credential_fields)->toBeArray()
        ->and(array_column($carrier->credential_fields, 'key'))->toContain('secret_key', 'tenant_id');

    Volt::test('merchant.delivery')
        ->call('openProviderModal')
        ->call('selectProviderPlatform', $platform->id)
        ->assertSet('providerForm.platform_id', $platform->id)
        ->assertSet('providerForm.carrier_id', '')
        ->call('selectProviderCarrier', $carrier->id)
        ->assertSet('providerForm.name', 'ZR Express v2')
        ->assertSet('providerForm.credential_values.secret_key', '')
        ->assertSet('providerForm.credential_values.tenant_id', '');

    $component = Volt::test('merchant.delivery');
    $component
        ->call('openProviderModal')
        ->call('selectProviderPlatform', $platform->id)
        ->call('selectProviderCarrier', $carrier->id)
        ->set('providerForm.name', 'ZR Express v2')
        ->set('providerForm.credential_values.secret_key', 'sk_test_123')
        ->set('providerForm.credential_values.tenant_id', 'tenant-1')
        ->call('saveProvider')
        ->assertDispatched('swal', type: 'success');

    $provider = ShippingProvider::where('store_id', $store->id)->first();

    expect($provider)->not->toBeNull()
        ->and($provider->name)->toBe('ZR Express v2')
        ->and($provider->carrier_id)->toBe($carrier->id)
        ->and($provider->carrier_platform_id)->toBe($platform->id)
        ->and($provider->credentials['secret_key'])->toBe('sk_test_123')
        ->and($provider->credentials['tenant_id'])->toBe('tenant-1');
});

test('carrier credentials marked required block saving when missing', function () {
    [$user, $store] = createDeliveryStore('owner');
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $platform = CarrierPlatform::where('slug', 'ecotrack')->first();
    $carrier = Carrier::where('code', 'ecotrack')->first();

    Volt::test('merchant.delivery')
        ->call('openProviderModal')
        ->call('selectProviderPlatform', $platform->id)
        ->call('selectProviderCarrier', $carrier->id)
        ->set('providerForm.name', 'Ecotrack')
        ->call('saveProvider')
        ->assertHasErrors('providerForm.credential_values.api_token');

    expect(ShippingProvider::where('store_id', $store->id)->count())->toBe(0);
});

test('owner manages per-state office/home delivery pricing', function () {
    [$user, $store] = createDeliveryStore('owner');
    actingAs($user)->withSession(['current_store_id' => $store->id]);
    deliverySeedLocations();
    $state = State::first();

    $provider = ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'Ecotrack',
        'carrier_platform_id' => CarrierPlatform::where('slug', 'ecotrack')->first()->id,
        'carrier_id' => Carrier::where('code', 'ecotrack')->first()->id,
        'credentials' => ['api_token' => 'tok_1'],
        'is_active' => true,
    ]);

    Volt::test('merchant.delivery')
        ->call('openRateModal')
        ->set('rateForm.shipping_provider_id', $provider->id)
        ->set('rateForm.state_id', $state->id)
        ->set('rateForm.office_cost', '250')
        ->set('rateForm.home_cost', '400')
        ->call('saveRate')
        ->assertDispatched('swal', type: 'success');

    $rate = DeliveryRate::where('store_id', $store->id)->first();

    expect($rate)->not->toBeNull()
        ->and($rate->state_id)->toBe($state->id)
        ->and((float) $rate->office_cost)->toBe(250.0)
        ->and((float) $rate->home_cost)->toBe(400.0);

    // Re-saving the same provider+state updates instead of duplicating.
    Volt::test('merchant.delivery')
        ->call('openRateModal')
        ->set('rateForm.shipping_provider_id', $provider->id)
        ->set('rateForm.state_id', $state->id)
        ->set('rateForm.home_cost', '450')
        ->call('saveRate')
        ->assertDispatched('swal', type: 'success');

    expect(DeliveryRate::where('store_id', $store->id)->count())->toBe(1)
        ->and((float) DeliveryRate::where('store_id', $store->id)->first()->home_cost)->toBe(450.0);
});

test('rate requires a provider, a state, and at least one office/home cost', function () {
    [$user, $store] = createDeliveryStore('owner');
    actingAs($user)->withSession(['current_store_id' => $store->id]);
    deliverySeedLocations();
    $state = State::first();

    $provider = ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'Ecotrack',
        'carrier_platform_id' => CarrierPlatform::where('slug', 'ecotrack')->first()->id,
        'carrier_id' => Carrier::where('code', 'ecotrack')->first()->id,
        'credentials' => ['api_token' => 'tok_1'],
        'is_active' => true,
    ]);

    // Missing everything.
    Volt::test('merchant.delivery')
        ->call('openRateModal')
        ->call('saveRate')
        ->assertHasErrors('rateForm.shipping_provider_id')
        ->assertHasErrors('rateForm.state_id');

    // Provider + state set, but no office or home cost.
    Volt::test('merchant.delivery')
        ->call('openRateModal')
        ->set('rateForm.shipping_provider_id', $provider->id)
        ->set('rateForm.state_id', $state->id)
        ->set('rateForm.office_cost', '')
        ->set('rateForm.home_cost', '')
        ->call('saveRate')
        ->assertHasErrors('rateForm.office_cost')
        ->assertHasErrors('rateForm.home_cost');

    expect(DeliveryRate::where('store_id', $store->id)->count())->toBe(0);
});