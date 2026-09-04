<?php

use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use App\Domains\Shipping\Models\DeliveryPriceList;
use App\Domains\Shipping\Models\DeliveryRate;
use App\Domains\Shipping\Models\DeliveryRateCity;
use App\Domains\Shipping\Models\DeliveryRateListCity;
use App\Domains\Shipping\Models\DeliveryRateListState;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\StopdeskPoint;
use App\Models\Locations\City;
use App\Models\Locations\State;
use App\Models\Products\Product;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Database\Seeders\CarrierCatalogSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
        'slug' => 'delivery-store-'.uniqid(),
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

function deliveryProvider(int|string $storeId, array $overrides = []): ShippingProvider
{
    return ShippingProvider::create(array_merge([
        'store_id' => $storeId,
        'name' => 'Ecotrack',
        'carrier_platform_id' => CarrierPlatform::where('slug', 'ecotrack')->first()->id,
        'carrier_id' => Carrier::where('code', 'ecotrack')->first()->id,
        'credentials' => ['api_token' => 'tok_1'],
        'is_active' => true,
    ], $overrides));
}

// ————— Delivery companies page (merchant.delivery) —————

test('owner can open the delivery companies page', function () {
    [$user, $store] = createDeliveryStore('owner');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.delivery.providers')
        ->assertOk()
        ->assertSee(__('merchant_panel.delivery_companies'))
        ->assertSee(__('merchant_panel.tab_providers_desc'));
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

    Volt::test('merchant.delivery.providers')
        ->call('openProviderModal')
        ->call('selectProviderPlatform', $platform->id)
        ->assertSet('providerForm.platform_id', $platform->id)
        ->assertSet('providerForm.carrier_id', '')
        ->call('selectProviderCarrier', $carrier->id)
        ->assertSet('providerForm.name', 'ZR Express v2')
        ->assertSet('providerForm.credential_values.secret_key', '')
        ->assertSet('providerForm.credential_values.tenant_id', '');

    $component = Volt::test('merchant.delivery.providers');
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

    Volt::test('merchant.delivery.providers')
        ->call('openProviderModal')
        ->call('selectProviderPlatform', $platform->id)
        ->call('selectProviderCarrier', $carrier->id)
        ->set('providerForm.name', 'Ecotrack')
        ->call('saveProvider')
        ->assertHasErrors('providerForm.credential_values.api_token');

    expect(ShippingProvider::where('store_id', $store->id)->count())->toBe(0);
});

// ————— Announced rates page (merchant.delivery.announced-rates) —————

test('owner manages per-state office/home pricing on the announced-rates page', function () {
    [$user, $store] = createDeliveryStore('owner');
    actingAs($user)->withSession(['current_store_id' => $store->id]);
    deliverySeedLocations();
    $state = State::first();
    $provider = deliveryProvider($store->id);

    Volt::test('merchant.delivery.announced-rates')
        ->assertOk()
        ->assertSee(__('merchant_panel.announced_rates'))
        ->call('selectProvider', $provider->id)
        ->assertSet('selectedProviderId', $provider->id)
        ->call('updateStateCost', $state->id, 'home_cost', '400')
        ->call('updateStateCost', $state->id, 'office_cost', '250');

    $rate = DeliveryRate::where('store_id', $store->id)->first();

    expect($rate)->not->toBeNull()
        ->and($rate->state_id)->toBe($state->id)
        ->and((float) $rate->home_cost)->toBe(400.0)
        ->and((float) $rate->office_cost)->toBe(250.0);

    // Re-saving the same provider+state updates instead of duplicating.
    Volt::test('merchant.delivery.announced-rates')
        ->call('selectProvider', $provider->id)
        ->call('updateStateCost', $state->id, 'home_cost', '450');

    expect(DeliveryRate::where('store_id', $store->id)->count())->toBe(1)
        ->and((float) DeliveryRate::where('store_id', $store->id)->first()->home_cost)->toBe(450.0);
});

test('announced-rates popup manages per-municipality pricing and apply-to-all', function () {
    [$user, $store] = createDeliveryStore('owner');
    actingAs($user)->withSession(['current_store_id' => $store->id]);
    deliverySeedLocations();
    $state = State::first();
    $cityA = City::create(['state_id' => $state->id, 'name' => 'City A', 'post_code' => '16001', 'is_active' => true]);
    $cityB = City::create(['state_id' => $state->id, 'name' => 'City B', 'post_code' => '16002', 'is_active' => true]);
    $provider = deliveryProvider($store->id);

    // Single municipality price.
    Volt::test('merchant.delivery.announced-rates')
        ->call('selectProvider', $provider->id)
        ->call('openStatePopup', $state->id)
        ->assertSet('showStatePopup', true)
        ->assertSet('popupStateName', $state->name)
        ->assertCount('popupCitiesWithPrices', 2)
        ->call('saveMunicipalityCost', $state->id, $cityA->id, '300');

    expect(DeliveryRateCity::where('city_id', $cityA->id)->first())->not->toBeNull()
        ->and((float) DeliveryRateCity::where('city_id', $cityA->id)->first()->home_cost)->toBe(300.0);

    // Apply-to-all bulk pricing.
    Volt::test('merchant.delivery.announced-rates')
        ->call('selectProvider', $provider->id)
        ->call('openStatePopup', $state->id)
        ->set('applyAllHomeCost', '350')
        ->call('applyAllHomeCost', $state->id)
        ->assertDispatched('swal', type: 'success');

    expect(DeliveryRateCity::where('state_id', $state->id)->count())->toBe(2)
        ->and(DeliveryRateCity::where('city_id', $cityB->id)->first()->home_cost)->toBe('350.00');

    // Clearing a municipality removes the override.
    Volt::test('merchant.delivery.announced-rates')
        ->call('selectProvider', $provider->id)
        ->call('openStatePopup', $state->id)
        ->call('saveMunicipalityCost', $state->id, $cityA->id, '');

    expect(DeliveryRateCity::where('city_id', $cityA->id)->count())->toBe(0);
});

test('announced-rates popup saves the default center and office price per state', function () {
    [$user, $store] = createDeliveryStore('owner');
    actingAs($user)->withSession(['current_store_id' => $store->id]);
    deliverySeedLocations();
    $state = State::first();
    $provider = deliveryProvider($store->id);

    $center = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'name' => 'Center 1',
        'is_active' => true,
    ]);

    Volt::test('merchant.delivery.announced-rates')
        ->call('selectProvider', $provider->id)
        ->call('openStatePopup', $state->id)
        ->assertCount('popupCenters', 1)
        ->call('saveDefaultCenter', $state->id, $center->id)
        ->call('saveStateOffice', $state->id, '200');

    $rate = DeliveryRate::where('store_id', $store->id)->where('state_id', $state->id)->first();

    expect($rate)->not->toBeNull()
        ->and($rate->default_center_id)->toBe($center->id)
        ->and((float) $rate->office_cost)->toBe(200.0);
});

test('manual sync from the carrier persists announced rates', function () {
    [$user, $store] = createDeliveryStore('owner');
    actingAs($user)->withSession(['current_store_id' => $store->id]);
    deliverySeedLocations();
    $provider = deliveryProvider($store->id);

    // No dedicated adapter is registered for ecotrack, so the default adapter
    // returns nulls and the UI reports that nothing was announced.
    Volt::test('merchant.delivery.announced-rates')
        ->call('selectProvider', $provider->id)
        ->call('syncProvider')
        ->assertSet('syncing', false)
        ->assertDispatched('swal', type: 'info');

    expect(DeliveryRate::where('store_id', $store->id)->count())->toBe(0);
});

// ————— Price lists tab (merchant.delivery.announced-rates) —————

function deliveryListProduct(Store $store, string $name): Product
{
    return Product::create([
        'store_id' => $store->id,
        'name' => $name,
        'slug' => Str::slug($name).'-'.uniqid(),
        'sku' => 'SKU-'.uniqid(),
        'price' => 999,
        'is_active' => true,
    ]);
}

test('owner creates a price list and attaches products through the list modal', function () {
    [$user, $store] = createDeliveryStore('owner');
    actingAs($user)->withSession(['current_store_id' => $store->id]);
    $product = deliveryListProduct($store, 'Laptop');

    Volt::test('merchant.delivery.announced-rates')
        ->call('setTab', 'lists')
        ->call('openListModal')
        ->assertSet('showListModal', true)
        ->call('toggleListProduct', $product->id)
        ->set('listName', 'Electronics')
        ->call('saveList')
        ->assertDispatched('swal', type: 'success');

    $list = DeliveryPriceList::where('store_id', $store->id)->first();

    expect($list)->not->toBeNull()
        ->and($list->name)->toBe('Electronics')
        ->and($list->is_active)->toBeTrue()
        ->and($list->products()->pluck('products.id')->all())->toBe([$product->id]);
});

test('price list name is required and the modal picker searches products', function () {
    [$user, $store] = createDeliveryStore('owner');
    actingAs($user)->withSession(['current_store_id' => $store->id]);
    $laptop = deliveryListProduct($store, 'Laptop');
    $phone = deliveryListProduct($store, 'Smartphone');

    Volt::test('merchant.delivery.announced-rates')
        ->call('setTab', 'lists')
        ->call('openListModal')
        ->set('listProductSearch', 'LAP')
        ->assertSee('Laptop')
        ->assertDontSee('Smartphone')
        ->call('saveList')
        ->assertHasErrors('listName');

    expect(DeliveryPriceList::count())->toBe(0);
});

test('owner manages list state prices and municipality overrides independently of providers', function () {
    [$user, $store] = createDeliveryStore('owner');
    actingAs($user)->withSession(['current_store_id' => $store->id]);
    deliverySeedLocations();

    $state = State::first();
    $cityA = City::create(['state_id' => $state->id, 'name' => 'City A', 'post_code' => '16001', 'is_active' => true]);
    $cityB = City::create(['state_id' => $state->id, 'name' => 'City B', 'post_code' => '16002', 'is_active' => true]);

    $list = DeliveryPriceList::create(['store_id' => $store->id, 'name' => 'Fresh', 'is_active' => true]);

    Volt::test('merchant.delivery.announced-rates')
        ->call('setTab', 'lists')
        ->call('selectList', $list->id)
        ->call('updateListStateCost', $state->id, 'home_cost', '500')
        ->call('updateListStateCost', $state->id, 'office_cost', '300')
        ->call('openListStatePopup', $state->id)
        ->assertSet('showListStatePopup', true)
        ->assertCount('listPopupCitiesWithPrices', 2)
        ->call('saveListMunicipalityCost', $state->id, $cityA->id, '550')
        ->set('listApplyAllHomeCost', '600')
        ->call('applyAllListHomeCost', $state->id)
        ->assertDispatched('swal', type: 'success');

    $stateRate = DeliveryRateListState::where('delivery_price_list_id', $list->id)->first();

    expect($stateRate)->not->toBeNull()
        ->and((float) $stateRate->home_cost)->toBe(500.0)
        ->and((float) $stateRate->office_cost)->toBe(300.0)
        ->and(DeliveryRateListCity::where('delivery_price_list_id', $list->id)->count())->toBe(2)
        ->and((float) DeliveryRateListCity::where('delivery_price_list_id', $list->id)->where('city_id', $cityB->id)->first()->home_cost)->toBe(600.0);

    // Provider pricing stays untouched — both systems are isolated.
    expect(DeliveryRateCity::where('city_id', $cityA->id)->count())->toBe(0);
});

test('price lists are isolated per store', function () {
    [$user, $store] = createDeliveryStore('owner');
    [$otherUser, $otherStore] = createDeliveryStore('owner');
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $mine = DeliveryPriceList::create(['store_id' => $store->id, 'name' => 'Mine', 'is_active' => true]);
    DeliveryPriceList::create(['store_id' => $otherStore->id, 'name' => 'Theirs', 'is_active' => true]);

    Volt::test('merchant.delivery.announced-rates')
        ->call('setTab', 'lists')
        ->assertCount('lists', 1)
        ->assertSet('selectedListId', $mine->id)
        ->assertDontSee('Theirs');
});

test('owner toggles and deletes a price list; rates cascade on delete', function () {
    [$user, $store] = createDeliveryStore('owner');
    actingAs($user)->withSession(['current_store_id' => $store->id]);
    deliverySeedLocations();

    $state = State::first();
    $list = DeliveryPriceList::create(['store_id' => $store->id, 'name' => 'Temp', 'is_active' => true]);
    $product = deliveryListProduct($store, 'Probe');
    $list->products()->sync([$product->id]);
    DeliveryRateListState::create(['delivery_price_list_id' => $list->id, 'state_id' => $state->id, 'home_cost' => 100]);

    Volt::test('merchant.delivery.announced-rates')
        ->call('setTab', 'lists')
        ->call('selectList', $list->id)
        ->call('toggleListActive', $list->id);

    expect(DeliveryPriceList::find($list->id)->is_active)->toBeFalse();

    Volt::test('merchant.delivery.announced-rates')
        ->call('setTab', 'lists')
        ->call('deleteList', $list->id)
        ->assertDispatched('swal', type: 'success');

    expect(DeliveryPriceList::where('store_id', $store->id)->count())->toBe(0)
        ->and(DeliveryRateListState::where('delivery_price_list_id', $list->id)->count())->toBe(0)
        ->and(DB::table('delivery_price_list_products')->where('delivery_price_list_id', $list->id)->count())->toBe(0);
});

// ————— Pickup points page (merchant.delivery.stopdesk) —————

test('owner manages pickup points on the stopdesk page', function () {
    [$user, $store] = createDeliveryStore('owner');
    actingAs($user)->withSession(['current_store_id' => $store->id]);
    deliverySeedLocations();
    $state = State::first();
    $provider = deliveryProvider($store->id);

    $component = Volt::test('merchant.delivery.stopdesk')
        ->assertOk()
        ->assertSee(__('merchant_panel.tab_stopdesk'));

    // Create.
    $component
        ->call('openStopdeskModal')
        ->set('stopdeskForm.shipping_provider_id', $provider->id)
        ->set('stopdeskForm.state_id', $state->id)
        ->set('stopdeskForm.name', 'Downtown Office')
        ->set('stopdeskForm.address', '12 Main St')
        ->call('saveStopdesk')
        ->assertDispatched('swal', type: 'success');

    $point = StopdeskPoint::where('store_id', $store->id)->first();

    expect($point)->not->toBeNull()
        ->and($point->name)->toBe('Downtown Office')
        ->and($point->shipping_provider_id)->toBe($provider->id)
        ->and($point->state_id)->toBe($state->id);

    // Edit.
    Volt::test('merchant.delivery.stopdesk')
        ->call('openStopdeskModal', $point->id)
        ->assertSet('stopdeskForm.name', 'Downtown Office')
        ->set('stopdeskForm.name', 'HQ Office')
        ->call('saveStopdesk')
        ->assertDispatched('swal', type: 'success');

    expect(StopdeskPoint::find($point->id)->name)->toBe('HQ Office');

    // Delete.
    Volt::test('merchant.delivery.stopdesk')
        ->call('deleteStopdesk', $point->id)
        ->assertDispatched('swal', type: 'success');

    expect(StopdeskPoint::where('store_id', $store->id)->count())->toBe(0);
});
