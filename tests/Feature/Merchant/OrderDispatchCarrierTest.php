<?php

use App\Domains\Shipping\Models\ShippingProvider;
use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Orders\OrderEvent;
use App\Models\Orders\OrderItem;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Support\StoreContext;
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
 * ROLES_PERMISSIONS.md §6.5b — order.dispatch.carrier, store-wide, split out of
 * order.manage. The shape of this phase is unusual and every test below exists
 * to defend one specific edge:
 *
 *   1+2  $submitConfirmAndSend  <-> confirm-drawer button
 *   3    $confirmBulkSend       <-> bulk-actions-bar send button
 *   4+5  $sendConfirmedOrder    <-> orders-table-actions-column send button
 *   6    bulk-actions-bar @if split (the highest-risk item)
 *
 * `order.manage` is never granted anywhere in this file except where a test is
 * explicitly a non-regression check, and `canStore()` memoises per user id, so
 * every scenario acts as its own distinct membership.
 *
 * Every grant below also carries team.view. That is NOT part of §6.5b: it only
 * satisfies Order::visibleTo(), which otherwise hides every row from a plain
 * staff membership and would make the row-level assertions vacuously true.
 * team.view is unrelated to the three permissions under test, so it cannot mask
 * any of them.
 */
const ODC_VIEW = [
    StorePermissionEnum::ORDER_VIEW->value,
    StorePermissionEnum::TEAM_VIEW->value,
];

const ODC_CARRIER_ONLY = [
    StorePermissionEnum::ORDER_VIEW->value,
    StorePermissionEnum::TEAM_VIEW->value,
    StorePermissionEnum::ORDER_DISPATCH_CARRIER->value,
];

const ODC_CONFIRM_ONLY = [
    StorePermissionEnum::ORDER_VIEW->value,
    StorePermissionEnum::TEAM_VIEW->value,
    StorePermissionEnum::ORDER_CONFIRM->value,
];

/** Both halves of the compound AND, still no order.manage. */
const ODC_CONFIRM_AND_CARRIER = [
    StorePermissionEnum::ORDER_VIEW->value,
    StorePermissionEnum::TEAM_VIEW->value,
    StorePermissionEnum::ORDER_CONFIRM->value,
    StorePermissionEnum::ORDER_DISPATCH_CARRIER->value,
];

const ODC_STATUS_OWN_ONLY = [
    StorePermissionEnum::ORDER_VIEW->value,
    StorePermissionEnum::TEAM_VIEW->value,
    StorePermissionEnum::ORDER_STATUS_MANAGE_OWN->value,
];

function odcOwner(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name'    => 'Dispatch Carrier Store',
        'slug'    => 'odc-'.uniqid(),
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

/**
 * A fresh staff membership carrying exactly $permissions. Staff is deliberate:
 * it is the least-privileged role, so it satisfies no implicit management
 * shortcut and every result comes from the explicit set.
 *
 * Returns a NEW user each call — canStore() memoises per user id for the whole
 * request, so one user cannot be reused across two permission sets.
 */
function odcMember(Store $store, array $permissions, string $name = 'Rep'): array
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

function odcProvider(Store $store, string $name = 'Odc Carrier'): ShippingProvider
{
    return ShippingProvider::create([
        'store_id'    => $store->id,
        'name'        => $name,
        'code'        => 'odc-'.Str::random(6),
        'credentials' => [],
        'is_active'   => true,
        'is_default'  => true,
        'flat_rate'   => 600,
    ]);
}

function odcVariant(Store $store): ProductVariant
{
    $product = Product::create([
        'store_id'  => $store->id,
        'name'      => 'Odc Product',
        'slug'      => 'odc-pr-'.uniqid(),
        'sku'       => 'ODC-'.strtoupper(Str::random(6)),
        'type'      => 'simple',
        'price'     => 400,
        'is_active' => true,
    ]);

    return ProductVariant::create([
        'store_id'   => $store->id,
        'product_id' => $product->id,
        'name'       => 'Default',
        'sku'        => 'odc-v-'.uniqid(),
        'price'      => 400,
        'stock'      => 10,
        'is_active'  => true,
    ]);
}

/**
 * A shippable order: linked customer, home address, geography, one item and a
 * carrier. OrderCompleteness refuses anything less, so the send paths would
 * always bail on a missing-fields warning and the happy path would be untestable.
 */
function odcOrder(Store $store, string $statusKey = 'confirmed', ?ShippingProvider $provider = null): Order
{
    $status = Status::system()->forType('order')->where('key', $statusKey)->firstOrFail();

    $country = Country::firstOrCreate(['code' => 'DZ'], ['name' => 'Algeria', 'arabic_name' => 'الجزائر', 'is_active' => true]);
    $state = State::firstOrCreate(['country_id' => $country->id, 'state_code' => '01'], ['name' => 'Adrar', 'arabic_name' => 'أدرار', 'is_active' => true]);
    $city = City::firstOrCreate(['state_id' => $state->id, 'name' => 'Adrar Centre'], ['post_code' => '01000', 'is_active' => true]);

    $customer = Customer::create([
        'store_id' => $store->id,
        'name'     => 'ODC Customer '.Str::random(5),
        'phone'    => '0553'.fake()->unique()->numerify('######'),
        'status'   => true,
    ]);

    $order = Order::create([
        'store_id'            => $store->id,
        'customer_id'         => $customer->id,
        'status_id'           => $status->id,
        'number'              => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount'        => 400,
        'shipping_cost'       => 0,
        'state_id'            => $state->id,
        'city_id'             => $city->id,
        'address'             => 'Rue des Cedres',
        'delivery_type'       => 'home',
        'stopdesk_point_id'   => null,
        'shipping_provider_id' => ($provider ?? odcProvider($store))->id,
    ]);

    OrderItem::create([
        'store_id'           => $store->id,
        'order_id'           => $order->id,
        'product_id'         => null,
        'product_variant_id' => odcVariant($store)->id,
        'quantity'           => 1,
        'price'              => 400,
        'subtotal'           => 400,
    ]);

    return $order->fresh();
}

function odcVolt(User $user, Store $store)
{
    actingAs($user)->withSession(['current_store_id' => $store->id]);
    app(StoreContext::class)->clear();

    return Volt::test('merchant.orders.index');
}

function odcStatusKey(Order $order): ?string
{
    return $order->refresh()->status?->key;
}

function odcToast(array $params): array
{
    return ($params[0] ?? null) && is_array($params[0]) ? $params[0] : $params;
}

// ---------------------------------------------------------------------------
// Items 1+2 — the compound AND and its confirm-drawer twin
// ---------------------------------------------------------------------------

it('lets order.confirm + order.dispatch.carrier confirm and send without order.manage', function () {
    [, $store] = odcOwner();
    [$bothUser, $both] = odcMember($store, ODC_CONFIRM_AND_CARRIER, 'ConfirmAndSend');
    $order = odcOrder($store, 'pending');

    expect($both->can(StorePermissionEnum::ORDER_MANAGE->value))->toBeFalse()
        ->and($both->can(StorePermissionEnum::ORDER_CONFIRM->value))->toBeTrue()
        ->and($both->can(StorePermissionEnum::ORDER_DISPATCH_CARRIER->value))->toBeTrue();

    odcVolt($bothUser, $store)
        ->call('openConfirmModal', $order->id)
        ->assertSet('showConfirmModal', true)
        ->call('submitConfirmAndSend')
        ->assertDispatched('swal:toast', fn ($n, $p) => odcToast($p)['icon'] === 'success');

    expect(odcStatusKey($order))->toBe('shipped')
        ->and(OrderEvent::where('order_id', $order->id)->where('event_type', 'sent_to_carrier')->exists())->toBeTrue();
});

it('refuses order.confirm alone on the send action, proving the condition is an AND', function () {
    [, $store] = odcOwner();
    [$confirmUser, $confirm] = odcMember($store, ODC_CONFIRM_ONLY, 'Confirmer');
    $order = odcOrder($store, 'pending');

    expect($confirm->can(StorePermissionEnum::ORDER_CONFIRM->value))->toBeTrue()
        ->and($confirm->can(StorePermissionEnum::ORDER_DISPATCH_CARRIER->value))->toBeFalse();

    // The other half of the AND is missing, so the whole condition must fail.
    odcVolt($confirmUser, $store)
        ->call('openConfirmModal', $order->id)
        ->assertSet('showConfirmModal', true)
        ->call('submitConfirmAndSend')
        ->assertDispatched('swal:toast', fn ($n, $p) => odcToast($p)['title'] === __('messages.permission_denied'));

    expect(odcStatusKey($order))->toBe('pending');
});

it('refuses order.dispatch.carrier alone on the send action', function () {
    // The mirror of the case above, and the doc's D2 test: holding one half of
    // the AND is not enough to confirm, so dispatch.carrier never becomes a
    // back door to confirmation.
    [, $store] = odcOwner();
    [$carrierUser, $carrier] = odcMember($store, ODC_CARRIER_ONLY, 'Carrier');
    $order = odcOrder($store, 'pending');

    expect($carrier->can(StorePermissionEnum::ORDER_DISPATCH_CARRIER->value))->toBeTrue()
        ->and($carrier->can(StorePermissionEnum::ORDER_CONFIRM->value))->toBeFalse();

    odcVolt($carrierUser, $store)
        ->call('openConfirmModal', $order->id)
        ->assertSet('showConfirmModal', false);

    odcVolt($carrierUser, $store)
        ->call('submitConfirmAndSend')
        ->assertDispatched('swal:toast', fn ($n, $p) => odcToast($p)['title'] === __('messages.permission_denied'));

    expect(odcStatusKey($order))->toBe('pending');
});

it('shows the confirm-and-send button only to members that satisfy both halves', function () {
    [, $store] = odcOwner();
    $order = odcOrder($store, 'pending');

    [$bothUser] = odcMember($store, ODC_CONFIRM_AND_CARRIER, 'Both');
    [$confirmUser] = odcMember($store, ODC_CONFIRM_ONLY, 'ConfirmOnly');

    // Twin of $submitConfirmAndSend: present for the full grant...
    odcVolt($bothUser, $store)
        ->call('openConfirmModal', $order->id)
        ->assertSee('submitConfirmAndSend', escape: false)
        ->assertSee(__('order_flow.confirm_and_send'))
        ->assertSee('submitConfirmOnly', escape: false);

    // ...and hidden for a half grant, while the confirm-only button remains,
    // so a confirm-only member does not lose the action they are entitled to.
    odcVolt($confirmUser, $store)
        ->call('openConfirmModal', $order->id)
        ->assertDontSee('submitConfirmAndSend', escape: false)
        ->assertDontSee(__('order_flow.confirm_and_send'))
        ->assertSee('submitConfirmOnly', escape: false)
        ->assertSee(__('order_flow.confirm_only'));
});

// ---------------------------------------------------------------------------
// Items 3+4 — the simple ORs
// ---------------------------------------------------------------------------

it('lets a dispatch.carrier-only member bulk-send confirmed orders', function () {
    [, $store] = odcOwner();
    [$carrierUser, $carrier] = odcMember($store, ODC_CARRIER_ONLY, 'Carrier');
    $a = odcOrder($store, 'confirmed');
    $b = odcOrder($store, 'confirmed');

    expect($carrier->can(StorePermissionEnum::ORDER_MANAGE->value))->toBeFalse();

    // The opener gates the same action as $confirmBulkSend, so it has to accept
    // dispatch.carrier too — otherwise the newly visible button would 403.
    odcVolt($carrierUser, $store)
        ->set('selectedOrders', [$a->id, $b->id])
        ->call('openBulkSendModal')
        ->assertSet('showBulkSendModal', true)
        ->call('confirmBulkSend')
        ->assertOk();

    expect(odcStatusKey($a))->toBe('shipped')
        ->and(odcStatusKey($b))->toBe('shipped');
});

it('lets a dispatch.carrier-only member single-send a confirmed order', function () {
    [, $store] = odcOwner();
    [$carrierUser, $carrier] = odcMember($store, ODC_CARRIER_ONLY, 'Carrier');
    $order = odcOrder($store, 'confirmed');

    odcVolt($carrierUser, $store)
        ->call('sendConfirmedOrder', $order->id)
        ->assertDispatched('swal:toast', fn ($n, $p) => odcToast($p)['icon'] === 'success');

    expect(odcStatusKey($order))->toBe('shipped')
        ->and(OrderEvent::where('order_id', $order->id)->where('event_type', 'sent_to_carrier')->exists())->toBeTrue();
});

it('keeps the send actions closed for a member with neither permission', function () {
    [, $store] = odcOwner();
    [$plainUser, $plain] = odcMember($store, ODC_VIEW, 'Viewer');
    $order = odcOrder($store, 'confirmed');

    expect($plain->can(StorePermissionEnum::ORDER_DISPATCH_CARRIER->value))->toBeFalse()
        ->and($plain->can(StorePermissionEnum::ORDER_MANAGE->value))->toBeFalse();

    $denied = fn ($n, $p) => odcToast($p)['title'] === __('messages.permission_denied');

    odcVolt($plainUser, $store)
        ->call('sendConfirmedOrder', $order->id)
        ->assertDispatched('swal:toast', $denied);

    odcVolt($plainUser, $store)
        ->set('selectedOrders', [$order->id])
        ->call('openBulkSendModal')
        ->assertDispatched('swal:toast', $denied)
        ->call('confirmBulkSend')
        ->assertDispatched('swal:toast', $denied);

    expect(odcStatusKey($order))->toBe('confirmed');
});

it('leaves order.manage holders unrestricted on every send path', function () {
    [, $store] = odcOwner();
    [$manageUser, $manage] = odcMember($store, array_merge(ODC_VIEW, [
        StorePermissionEnum::ORDER_MANAGE->value,
    ]), 'Manager');
    $order = odcOrder($store, 'confirmed');

    expect($manage->can(StorePermissionEnum::ORDER_DISPATCH_CARRIER->value))->toBeFalse()
        ->and($manage->can(StorePermissionEnum::ORDER_MANAGE->value))->toBeTrue();

    odcVolt($manageUser, $store)
        ->call('sendConfirmedOrder', $order->id)
        ->assertDispatched('swal:toast', fn ($n, $p) => odcToast($p)['icon'] === 'success');

    expect(odcStatusKey($order))->toBe('shipped');
});

// ---------------------------------------------------------------------------
// Item 5 — the table actions twin
// ---------------------------------------------------------------------------

it('shows the send-to-carrier row button for a dispatch.carrier-only member', function () {
    [, $store] = odcOwner();
    [$carrierUser] = odcMember($store, ODC_CARRIER_ONLY, 'Carrier');
    $confirmed = odcOrder($store, 'confirmed');
    odcOrder($store, 'pending');

    odcVolt($carrierUser, $store)
        ->assertSee($confirmed->number, escape: false)
        ->assertSee("sendConfirmedOrder('" . $confirmed->id . "')", escape: false)
        ->assertSee(__('order_flow.send_to_carrier'));
});

it('hides the send-to-carrier row button from a confirm-only member', function () {
    [, $store] = odcOwner();
    [$confirmUser] = odcMember($store, ODC_CONFIRM_ONLY, 'Confirmer');
    $confirmed = odcOrder($store, 'confirmed');

    odcVolt($confirmUser, $store)
        ->assertSee($confirmed->number, escape: false)
        ->assertDontSee("sendConfirmedOrder('" . $confirmed->id . "')", escape: false);
});

it('still gates the send row button on the order status, for a dispatcher', function () {
    // Item 5 is an ADDITION to the existing condition, not a replacement: a
    // pending order has no send button even for a dispatch.carrier holder.
    [, $store] = odcOwner();
    [$carrierUser] = odcMember($store, ODC_CARRIER_ONLY, 'Carrier');
    $pending = odcOrder($store, 'pending');

    odcVolt($carrierUser, $store)
        ->assertSee($pending->number, escape: false)
        ->assertDontSee("sendConfirmedOrder('" . $pending->id . "')", escape: false);
});

// ---------------------------------------------------------------------------
// Item 6 — the @if split. THE decisive test lives first.
// ---------------------------------------------------------------------------

it('DECISIVE: a status.manage.own-only member still sees the bulk status button after the split', function () {
    [, $store] = odcOwner();
    [$repUser, $rep] = odcMember($store, ODC_STATUS_OWN_ONLY, 'Rep');
    $order = odcOrder($store, 'pending', null);

    expect($rep->can(StorePermissionEnum::ORDER_MANAGE->value))->toBeFalse()
        ->and($rep->can(StorePermissionEnum::ORDER_STATUS_MANAGE_OWN->value))->toBeTrue();

    // The button must be there, and the send button must NOT be: if the two
    // conditions had been merged back, or swapped, one of these fails.
    // Probe wire:click, not the bare method name — the bar's spinner and
    // loading overlay name openBulkSendModal in wire:target for everyone.
    odcVolt($repUser, $store)
        ->set('selectedOrders', [$order->id])
        ->assertSee('wire:click="openBulkStatusModal"', escape: false)
        ->assertSee(__('order_flow.bulk_status_title'))
        ->assertDontSee('wire:click="openBulkSendModal"', escape: false)
        ->assertDontSee(__('merchant.bulk_send_carrier'));

    // And the action behind that button still works for them.
    odcVolt($repUser, $store)
        ->set('selectedOrders', [$order->id])
        ->call('openBulkStatusModal')
        ->assertSet('showBulkStatusModal', true);
});

it('hides the bulk status button from a dispatch.carrier-only member', function () {
    // The other direction of the split: dispatch.carrier must not have leaked
    // into the status gate.
    [, $store] = odcOwner();
    [$carrierUser, $carrier] = odcMember($store, ODC_CARRIER_ONLY, 'Carrier');
    $order = odcOrder($store, 'confirmed');

    expect($carrier->can(StorePermissionEnum::ORDER_STATUS_MANAGE_OWN->value))->toBeFalse();

    odcVolt($carrierUser, $store)
        ->set('selectedOrders', [$order->id])
        ->assertSee('wire:click="openBulkSendModal"', escape: false)
        ->assertSee(__('merchant.bulk_send_carrier'))
        ->assertDontSee('wire:click="openBulkStatusModal"', escape: false);

    odcVolt($carrierUser, $store)
        ->set('selectedOrders', [$order->id])
        ->call('openBulkStatusModal')
        ->assertSet('showBulkStatusModal', false)
        ->assertDispatched('swal:toast', fn ($n, $p) => odcToast($p)['title'] === __('messages.permission_denied'));
});

it('keeps both bulk buttons for an order.manage holder', function () {
    [, $store] = odcOwner();
    [$manageUser] = odcMember($store, array_merge(ODC_VIEW, [
        StorePermissionEnum::ORDER_MANAGE->value,
    ]), 'Manager');
    $order = odcOrder($store, 'confirmed');

    odcVolt($manageUser, $store)
        ->set('selectedOrders', [$order->id])
        ->assertSee('wire:click="openBulkSendModal"', escape: false)
        ->assertSee('wire:click="openBulkStatusModal"', escape: false);
});

it('shows neither bulk button to a plain order.view member', function () {
    [, $store] = odcOwner();
    [$plainUser] = odcMember($store, ODC_VIEW, 'Viewer');
    $order = odcOrder($store, 'confirmed');

    odcVolt($plainUser, $store)
        ->set('selectedOrders', [$order->id])
        ->assertDontSee('wire:click="openBulkSendModal"', escape: false)
        ->assertDontSee('wire:click="openBulkStatusModal"', escape: false)
        ->assertDontSee(__('merchant.bulk_send_carrier'));
});

// ---------------------------------------------------------------------------
// Items 7 + 8 — the deliberate non-changes
// ---------------------------------------------------------------------------

it('adds none of the six new permissions to any role template', function () {
    // ROLES_PERMISSIONS.md §7: every new permission is granted individually
    // through the Permission Hub. MANAGER keeps order.manage (which already
    // satisfies every new OR), and no template gains anything.
    $new = [
        StorePermissionEnum::ORDER_STATUS_MANAGE_OWN->value,
        StorePermissionEnum::ORDER_EDIT_IDENTITY->value,
        StorePermissionEnum::ORDER_EDIT_PRODUCTS->value,
        StorePermissionEnum::ORDER_EDIT_GEOGRAPHY->value,
        StorePermissionEnum::ORDER_DISPATCH_RIDER->value,
        StorePermissionEnum::ORDER_DISPATCH_CARRIER->value,
    ];

    foreach ([StoreRoleEnum::MANAGER, StoreRoleEnum::STAFF] as $role) {
        expect(StoreRoles::permissions($role))->not->toContain(...$new);
    }

    // OWNER/ADMIN are built from values(), so they pick all six up with zero
    // StoreRoles.php lines.
    foreach ([StoreRoleEnum::OWNER, StoreRoleEnum::ADMIN] as $role) {
        expect(StoreRoles::permissions($role))->toContain(...$new);
    }
});

it('does not let dispatch.carrier cancel a shipment', function () {
    // §3.6 / item 8: sending and cancelling are not symmetric. The cancel paths
    // (CancelsShipmentFromOrdersTable, TrackingDrawerConcern, TrackingTrashConcern)
    // stay on the broad permission and must gain no dispatch.carrier term.
    [, $store] = odcOwner();
    [$carrierUser, $carrier] = odcMember($store, ODC_CARRIER_ONLY, 'Carrier');
    $order = odcOrder($store, 'shipped');

    expect($carrier->can(StorePermissionEnum::ORDER_MANAGE->value))->toBeFalse();

    odcVolt($carrierUser, $store)
        ->call('cancelShipment', $order->id, 'changed my mind')
        ->assertForbidden();

    expect(odcStatusKey($order))->toBe('shipped');
});
