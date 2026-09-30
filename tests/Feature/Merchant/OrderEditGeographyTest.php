<?php

use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\StopdeskPoint;
use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Support\StoreRoles;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Database\Seeders\SystemStatusesSeeder;
use Illuminate\Support\Str;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(SystemStatusesSeeder::class);
});

/**
 * §6.4 grants order.edit.geography store-wide (§2.2), so no visibleTo() is
 * consulted. order.manage must keep every previous behaviour, and the
 * provider sites deliberately take the plain geography OR with no
 * dispatch.rider term (decision 1.3-c).
 */
const GEOGRAPHY_GRANT = [
    StorePermissionEnum::ORDER_VIEW->value,
    StorePermissionEnum::ORDER_EDIT_GEOGRAPHY->value,
];

const MANAGE_GRANT = [
    StorePermissionEnum::ORDER_VIEW->value,
    StorePermissionEnum::ORDER_MANAGE->value,
];

/** §6.5 is a later phase, so a rider-only member must not unlock provider edit. */
const RIDER_ONLY_GRANT = [
    StorePermissionEnum::ORDER_VIEW->value,
    StorePermissionEnum::ORDER_DISPATCH_RIDER->value,
];

function oegOwner(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name'    => 'Geography Store',
        'slug'    => 'oeg-'.uniqid(),
        'status'  => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id'   => $store->id,
        'user_id'    => $user->id,
        'invited_by' => $user->id,
        'is_active'  => true,
        'role'       => StoreRoleEnum::OWNER->value,
    ]);
    $membership->syncPermissions(StoreRoles::permissions(StoreRoleEnum::OWNER));

    return [$user, $store, $membership];
}

function oegStaff(Store $store, array $permissions, string $name = 'Rep'): array
{
    $user = User::factory()->create(['name' => $name]);

    $membership = StoreMembership::create([
        'store_id'   => $store->id,
        'user_id'    => $user->id,
        'invited_by' => $store->user_id,
        'is_active'  => true,
        'role'       => StoreRoleEnum::STAFF->value,
    ]);
    $membership->syncPermissions($permissions);

    return [$user, $membership];
}

/** Geodata the geography handlers validate against. */
function oegPlaces(): array
{
    $country = Country::firstOrCreate(['code' => 'DZ'], ['name' => 'Algeria', 'arabic_name' => 'الجزائر', 'is_active' => true]);
    $state = State::firstOrCreate(['country_id' => $country->id, 'state_code' => '01'], ['name' => 'Adrar', 'arabic_name' => 'أدرار', 'is_active' => true]);
    $city = City::firstOrCreate(['state_id' => $state->id, 'name' => 'Adrar Centre'], ['post_code' => '01000', 'is_active' => true]);

    return [$state, $city];
}

function oegProvider(Store $store, string $name = 'Yalidine'): ShippingProvider
{
    return ShippingProvider::create([
        'store_id'    => $store->id,
        'name'        => $name,
        'code'        => 'sp-'.uniqid(),
        'credentials' => [],
        'is_active'   => true,
    ]);
}

function oegOrder(Store $store, string $statusKey = 'pending', ?StoreMembership $assignee = null, ?ShippingProvider $provider = null): Order
{
    $status = Status::system()->forType('order')->where('key', $statusKey)->firstOrFail();
    [$state, $city] = oegPlaces();

    $customer = Customer::create([
        'store_id' => $store->id,
        'name'     => 'OEG Customer '.Str::random(5),
        'phone'    => '0553'.fake()->unique()->numerify('######'),
        'status'   => true,
    ]);

    $product = Product::create([
        'store_id'  => $store->id,
        'name'      => 'OEG Product',
        'slug'      => 'oeg-pr-'.uniqid(),
        'sku'       => 'OEG-'.strtoupper(Str::random(6)),
        'type'      => 'simple',
        'price'     => 400,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'store_id'   => $store->id,
        'product_id' => $product->id,
        'name'       => 'Default',
        'sku'        => 'oeg-v-'.uniqid(),
        'price'      => 400,
        'stock'      => 10,
        'is_active'  => true,
    ]);

    $order = Order::create([
        'store_id'                  => $store->id,
        'customer_id'               => $customer->id,
        'status_id'                 => $status->id,
        'number'                    => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount'              => 400,
        'shipping_cost'             => 0,
        'assigned_to_membership_id' => $assignee?->id,
        'assigned_at'               => $assignee ? now() : null,
        'assignment_method'         => $assignee ? 'automatic' : null,
        'state_id'                  => $state->id,
        'city_id'                   => $city->id,
        'address'                   => 'Rue des Cedres',
        'delivery_type'             => 'home',
    ]);

    \App\Models\Orders\OrderItem::create([
        'store_id'           => $store->id,
        'order_id'           => $order->id,
        'product_id'         => $product->id,
        'product_variant_id' => $variant->id,
        'quantity'           => 1,
        'price'              => 400,
        'subtotal'           => 400,
    ]);

    if ($provider) {
        $order->update(['shipping_provider_id' => $provider->id]);
    }

    return $order->fresh();
}

function oegVolt(User $user, Store $store)
{
    actingAs($user)->withSession(['current_store_id' => $store->id]);
    app(\App\Support\StoreContext::class)->clear();

    return Volt::test('merchant.orders.index');
}

function oegDenied(): Closure
{
    return fn ($n, $p) => ($p[0]['title'] ?? null) === __('messages.permission_denied');
}

/**
 * orders-mobile-fields.blade.php stamps its affordances with
 * edz-inline-edit__display--touch. The button is not closed on the class line,
 * so the whole element is matched and the handler read from its opening tag.
 * The warehouse toggle is a plain <button> without that class, so it is
 * matched separately.
 */
function oegMobileAffordances(string $html): array
{
    preg_match_all('/<button[^>]*edz-inline-edit__display--touch[^>]*>.*?<\/button>/s', $html, $buttons);

    $handlers = [];
    foreach ($buttons[0] as $button) {
        $openingTag = substr($button, 0, strpos($button, '>'));

        if (preg_match('/(startOrder\w+Edit)/', $openingTag, $handler)) {
            $handlers[] = $handler[1];
        }
    }

    if (preg_match('/<button[^>]*wire:click="toggleSendFromWarehouse\([^"]*\)"[^>]*>/s', $html)) {
        $handlers[] = 'toggleSendFromWarehouse';
    }

    return $handlers;
}

// ————— 1. store-wide geography edits, including unassigned orders —————

it('lets a geography-only member edit every geography field on an unassigned order', function () {
    [$ownerUser, $store] = oegOwner();
    [$geoUser, $geo] = oegStaff($store, GEOGRAPHY_GRANT, 'Geo');
    $provider = oegProvider($store);

    // Unassigned on purpose: §2.2 makes the grant store-wide.
    $order = oegOrder($store, 'pending', null, $provider);
    [$state, $city] = oegPlaces();

    // Each save* handler reads $this->editingId, so the start and the save
    // must share one component instance.
    oegVolt($geoUser, $store)->call('openDeliveryModal', $order->id);

    oegVolt($geoUser, $store)
        ->call('startOrderWilayaEdit', $order->id)
        ->call('saveOrderWilaya', (string) $state->id);
    expect($order->fresh()->state_id)->toEqual($state->id);

    oegVolt($geoUser, $store)
        ->call('startOrderCityEdit', $order->id)
        ->call('saveOrderCity', (string) $city->id);
    expect($order->fresh()->city_id)->toEqual($city->id);

    oegVolt($geoUser, $store)
        ->call('startOrderProviderEdit', $order->id)
        ->call('saveOrderProvider', (string) $provider->id);
    expect($order->fresh()->shipping_provider_id)->toEqual($provider->id);

    oegVolt($geoUser, $store)
        ->call('startOrderDeliveryTypeEdit', $order->id)
        ->call('saveOrderDeliveryType', 'stopdesk');
    expect($order->fresh()->delivery_type)->toBe('stopdesk');

    $point = StopdeskPoint::create([
        'store_id'             => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id'             => $state->id,
        'city_id'              => $city->id,
        'name'                 => 'Point '.uniqid(),
        'is_active'            => true,
    ]);

    oegVolt($geoUser, $store)
        ->call('startOrderStopdeskEdit', $order->id)
        ->call('saveOrderStopdesk', (string) $point->id);
    expect($order->fresh()->stopdesk_point_id)->toEqual($point->id);

    oegVolt($geoUser, $store)
        ->call('startOrderAddressEdit', $order->id)
        ->call('saveOrderAddress', '12 Rue Test');
    expect($order->fresh()->address)->toBe('12 Rue Test');

    expect($order->fresh()->send_from_carrier_warehouse)->toBeFalse();

    oegVolt($geoUser, $store)->call('toggleSendFromWarehouse', $order->id);
    expect($order->fresh()->send_from_carrier_warehouse)->toBeTrue();
});

// ————— 2. provider sites: plain geography OR, no dispatch.rider —————

it('grants the provider sites on order.edit.geography alone', function () {
    [$ownerUser, $store] = oegOwner();
    [$geoUser, $geo] = oegStaff($store, GEOGRAPHY_GRANT, 'Geo');
    $provider = oegProvider($store);
    $order = oegOrder($store, 'pending', $geo, $provider);

    oegVolt($geoUser, $store)
        ->call('startOrderProviderEdit', $order->id)
        ->assertNotDispatched('swal:toast', oegDenied());

    oegVolt($geoUser, $store)
        ->call('saveOrderProvider', (string) $provider->id)
        ->assertNotDispatched('swal:toast', oegDenied());
});

it('does not let order.dispatch.rider unlock the provider sites', function () {
    [$ownerUser, $store] = oegOwner();
    [$riderUser, $rider] = oegStaff($store, RIDER_ONLY_GRANT, 'Rider');
    $provider = oegProvider($store);
    $order = oegOrder($store, 'pending', $rider, $provider);

    // §6.5 has not landed, so dispatch.rider must not reach provider edit.
    oegVolt($riderUser, $store)
        ->call('startOrderProviderEdit', $order->id)
        ->assertDispatched('swal:toast', oegDenied());

    // saveOrderProvider runs guardOrderEditable() first, which returns early
    // without a toast when editingId is empty, so seed it to reach the
    // permission check inside saveEdit().
    oegVolt($riderUser, $store)
        ->set('editingId', $order->id)
        ->call('saveOrderProvider', (string) $provider->id)
        ->assertDispatched('swal:toast', oegDenied());
});

// ————— 3. no widening beyond geography —————

it('denies identity and products fields to a geography-only member', function () {
    [$ownerUser, $store] = oegOwner();
    [$geoUser, $geo] = oegStaff($store, GEOGRAPHY_GRANT, 'Geo');
    $order = oegOrder($store, 'pending', $geo);

    foreach (['startOrderNameEdit', 'startOrderPhoneEdit', 'startOrderNotesEdit', 'startOrderWeightEdit', 'startOrderShipmentTypeEdit', 'startOrderDiscountEdit'] as $handler) {
        oegVolt($geoUser, $store)
            ->call($handler, $order->id)
            ->assertDispatched('swal:toast', oegDenied());
    }

    foreach (['products', 'quantity', 'price'] as $kind) {
        oegVolt($geoUser, $store)
            ->call('openItemsModal', $kind, $order->id)
            ->assertSet('itemsModal', null);
    }

    oegVolt($geoUser, $store)
        ->call('openDeliveryModal', $order->id)
        ->assertNotDispatched('swal:toast', oegDenied());
});

it('denies geography edits to a member with neither order.manage nor order.edit.geography', function () {
    [$ownerUser, $store] = oegOwner();
    [$plainUser, $plain] = oegStaff($store, [StorePermissionEnum::ORDER_VIEW->value], 'Plain');
    $provider = oegProvider($store);
    $order = oegOrder($store, 'pending', $plain, $provider);

    foreach (['openDeliveryModal', 'startOrderWilayaEdit', 'startOrderCityEdit', 'startOrderProviderEdit', 'startOrderDeliveryTypeEdit', 'startOrderStopdeskEdit', 'startOrderAddressEdit', 'toggleSendFromWarehouse'] as $handler) {
        oegVolt($plainUser, $store)
            ->call($handler, $order->id)
            ->assertDispatched('swal:toast', oegDenied());
    }
});

// ————— 4. order.manage holder: zero regression —————

it('keeps an order.manage holder working exactly as before', function () {
    [$ownerUser, $store] = oegOwner();
    [$mgrUser, $mgr] = oegStaff($store, MANAGE_GRANT, 'Manager');
    $provider = oegProvider($store);
    $order = oegOrder($store, 'pending', $mgr, $provider);
    [$state, $city] = oegPlaces();

    oegVolt($mgrUser, $store)->call('openDeliveryModal', $order->id);

    oegVolt($mgrUser, $store)->call('startOrderWilayaEdit', $order->id);
    oegVolt($mgrUser, $store)->call('saveOrderWilaya', (string) $state->id);
    expect($order->fresh()->state_id)->toEqual($state->id);

    oegVolt($mgrUser, $store)->call('startOrderCityEdit', $order->id);
    oegVolt($mgrUser, $store)->call('saveOrderCity', (string) $city->id);
    expect($order->fresh()->city_id)->toEqual($city->id);

    oegVolt($mgrUser, $store)->call('startOrderProviderEdit', $order->id);
    oegVolt($mgrUser, $store)->call('saveOrderProvider', (string) $provider->id);
    expect($order->fresh()->shipping_provider_id)->toEqual($provider->id);

    oegVolt($mgrUser, $store)->call('toggleSendFromWarehouse', $order->id);
    expect($order->fresh()->send_from_carrier_warehouse)->toBeTrue();
});

// ————— 5. desktop twins —————

it('renders the seven geography twins for geography-only and order.manage members, and hides them otherwise', function () {
    [$ownerUser, $store] = oegOwner();
    [$geoUser, $geo] = oegStaff($store, GEOGRAPHY_GRANT, 'Geo');
    [$mgrUser, $mgr] = oegStaff($store, MANAGE_GRANT, 'Manager');
    [$plainUser, $plain] = oegStaff($store, [StorePermissionEnum::ORDER_VIEW->value], 'Plain');

    $columns = ['wilaya', 'city', 'address', 'delivery_type', 'shipping_provider', 'stopdesk_point', 'send_from_carrier_warehouse'];
    $triggers = [
        'startOrderWilayaEdit',
        'startOrderCityEdit',
        'startOrderAddressEdit',
        'startOrderDeliveryTypeEdit',
        'startOrderProviderEdit',
        'startOrderStopdeskEdit',
        'toggleSendFromWarehouse',
    ];

    // The list is assignment-scoped for staff (36.4), so give each viewer a row.
    foreach (['geography' => [$geoUser, $geo], 'order.manage' => [$mgrUser, $mgr]] as $label => [$user, $membership]) {
        oegOrder($store, 'pending', $membership);
        $html = oegVolt($user, $store)->set('visibleColumns', $columns)->html();

        foreach ($triggers as $trigger) {
            $count = substr_count($html, $trigger);
            // wilaya has the inline mobile-card twin as well, hence >= 1.
            expect($count)->toBeGreaterThanOrEqual(1, "{$label} should expose {$trigger} (saw {$count})");
        }
    }

    oegOrder($store, 'pending', $plain);
    $html = oegVolt($plainUser, $store)->set('visibleColumns', $columns)->html();

    foreach ($triggers as $trigger) {
        expect(substr_count($html, $trigger))->toBe(0, "plain member must not see {$trigger}");
    }
});

// ————— 6. mobile card: the split boundary holds both ways —————

it('shows the mobile geography affordances but not weight/shipment to a geography-only member', function () {
    [$ownerUser, $store] = oegOwner();
    [$geoUser, $geo] = oegStaff($store, GEOGRAPHY_GRANT, 'Geo');
    oegOrder($store, 'pending', $geo);

    $columns = ['delivery_type', 'shipping_provider', 'city', 'address', 'weight', 'shipment_type', 'send_from_carrier_warehouse'];
    $affordances = oegMobileAffordances(oegVolt($geoUser, $store)->set('visibleColumns', $columns)->html());

    expect($affordances)->toContain('startOrderDeliveryTypeEdit')
        ->toContain('startOrderProviderEdit')
        ->toContain('startOrderCityEdit')
        ->toContain('startOrderAddressEdit')
        ->toContain('toggleSendFromWarehouse');

    // Still the products tier: 36.12.3's split must not have leaked.
    expect($affordances)->not->toContain('startOrderWeightEdit')
        ->not->toContain('startOrderShipmentTypeEdit');
});

it('shows the mobile weight/shipment but no geography affordances to a products-only member', function () {
    [$ownerUser, $store] = oegOwner();
    [$prodUser, $prod] = oegStaff($store, [
        StorePermissionEnum::ORDER_VIEW->value,
        StorePermissionEnum::ORDER_EDIT_PRODUCTS->value,
    ], 'Prod');
    oegOrder($store, 'pending', $prod);

    $columns = ['delivery_type', 'shipping_provider', 'city', 'address', 'weight', 'shipment_type', 'send_from_carrier_warehouse'];
    $affordances = oegMobileAffordances(oegVolt($prodUser, $store)->set('visibleColumns', $columns)->html());

    expect($affordances)->toContain('startOrderWeightEdit')
        ->toContain('startOrderShipmentTypeEdit');

    foreach (['startOrderDeliveryTypeEdit', 'startOrderProviderEdit', 'startOrderCityEdit', 'startOrderAddressEdit', 'toggleSendFromWarehouse'] as $handler) {
        expect($affordances)->not->toContain($handler, "geography affordance {$handler} must stay order.manage-gated");
    }
});

it('keeps the mobile card unchanged for an order.manage holder and a plain member', function () {
    [$ownerUser, $store] = oegOwner();
    [$mgrUser, $mgr] = oegStaff($store, MANAGE_GRANT, 'Manager');
    [$plainUser, $plain] = oegStaff($store, [StorePermissionEnum::ORDER_VIEW->value], 'Plain');

    $columns = ['delivery_type', 'shipping_provider', 'city', 'address', 'weight', 'shipment_type', 'send_from_carrier_warehouse'];

    oegOrder($store, 'pending', $mgr);
    $affordances = oegMobileAffordances(oegVolt($mgrUser, $store)->set('visibleColumns', $columns)->html());
    expect($affordances)->toContain('startOrderWeightEdit')
        ->toContain('startOrderShipmentTypeEdit')
        ->toContain('startOrderDeliveryTypeEdit')
        ->toContain('startOrderProviderEdit')
        ->toContain('startOrderCityEdit')
        ->toContain('startOrderAddressEdit')
        ->toContain('toggleSendFromWarehouse');

    oegOrder($store, 'pending', $plain);
    $affordances = oegMobileAffordances(oegVolt($plainUser, $store)->set('visibleColumns', $columns)->html());
    expect($affordances)->toBeEmpty();
});
