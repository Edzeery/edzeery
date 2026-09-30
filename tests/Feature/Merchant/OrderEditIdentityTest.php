<?php

use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
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
 * §6.2 is about order.edit.identity: order.view (the prerequisite
 * PermissionGroupMeta pulls in automatically) plus order.edit.identity, and
 * deliberately NOT order.manage. Per §2.2 this grant is store-wide, so
 * visibleTo() is never consulted — see the explicit cross-assignee test below.
 */
const IDENTITY_GRANT = [
    StorePermissionEnum::ORDER_VIEW->value,
    StorePermissionEnum::ORDER_EDIT_IDENTITY->value,
];

function oeiOwner(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name'    => 'Identity Store',
        'slug'    => 'oei-'.uniqid(),
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

function oeiStaff(Store $store, array $permissions, string $name = 'Rep'): array
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

function oeiOrder(Store $store, string $statusKey = 'pending', ?StoreMembership $assignee = null): Order
{
    $status = Status::system()->forType('order')->where('key', $statusKey)->firstOrFail();

    $country = Country::firstOrCreate(['code' => 'DZ'], ['name' => 'Algeria', 'arabic_name' => 'الجزائر', 'is_active' => true]);
    $state = State::firstOrCreate(['country_id' => $country->id, 'state_code' => '01'], ['name' => 'Adrar', 'arabic_name' => 'أدرار', 'is_active' => true]);
    $city = City::firstOrCreate(['state_id' => $state->id, 'name' => 'Adrar Centre'], ['post_code' => '01000', 'is_active' => true]);

    $customer = Customer::create([
        'store_id' => $store->id,
        'name'     => 'OEI Customer '.Str::random(5),
        'phone'    => '0553'.fake()->unique()->numerify('######'),
        'status'   => true,
    ]);

    $product = Product::create([
        'store_id'  => $store->id,
        'name'      => 'OEI Product',
        'slug'      => 'oei-pr-'.uniqid(),
        'sku'       => 'OEI-'.strtoupper(Str::random(6)),
        'type'      => 'simple',
        'price'     => 400,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'store_id'  => $store->id,
        'product_id' => $product->id,
        'name'      => 'Default',
        'sku'       => 'oei-v-'.uniqid(),
        'price'     => 400,
        'stock'     => 10,
        'is_active' => true,
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

    OrderItem::create([
        'store_id'           => $store->id,
        'order_id'           => $order->id,
        'product_id'         => $product->id,
        'product_variant_id' => $variant->id,
        'quantity'           => 1,
        'price'              => 400,
        'subtotal'           => 400,
    ]);

    return $order->fresh();
}

function oeiVolt(User $user, Store $store)
{
    actingAs($user)->withSession(['current_store_id' => $store->id]);
    app(\App\Support\StoreContext::class)->clear();

    return Volt::test('merchant.orders.index');
}

function oeiDenied(): Closure
{
    return fn ($n, $p) => ($p[0]['title'] ?? null) === __('messages.permission_denied');
}

// ————— 1. store-wide scope, proven across assignments —————

it('lets an identity-only member edit identity on an order assigned to someone else', function () {
    [$ownerUser, $store] = oeiOwner();
    [$peerUser, $peer] = oeiStaff($store, IDENTITY_GRANT, 'Peer');
    [$repUser, $rep] = oeiStaff($store, IDENTITY_GRANT, 'Rep');

    // Assigned to the PEER, edited by the rep. §2.2 makes this grant store-wide,
    // so a visibleTo() mistake would show up here as a denial.
    $theirs = oeiOrder($store, 'pending', $peer);

    $volt = oeiVolt($repUser, $store)
        ->call('startOrderNameEdit', $theirs->id)
        ->set('nameEditName', 'Rep Renamed')
        ->call('saveOrderName');

    expect($theirs->customer->refresh()->name)->toBe('Rep Renamed');
    expect($volt->get('editingField'))->toBeNull();

    $theirs = oeiOrder($store, 'pending', $peer);
    oeiVolt($repUser, $store)
        ->call('startOrderPhoneEdit', $theirs->id)
        ->set('phoneEditPhone', '0555123456')
        ->set('phoneEditSecondary', '0555987654')
        ->call('saveOrderPhone');

    expect($theirs->customer->refresh()->phone)->toBe('0555123456')
        ->and($theirs->fresh()->phone_secondary)->toBe('0555987654');

    $theirs = oeiOrder($store, 'pending', $peer);
    oeiVolt($repUser, $store)
        ->call('startOrderNotesEdit', $theirs->id)
        ->call('saveOrderNotes', 'note from rep');

    expect($theirs->fresh()->notes)->toBe('note from rep');
});

it('lets an identity-only member edit an unassigned order', function () {
    [$ownerUser, $store] = oeiOwner();
    [$repUser, $rep] = oeiStaff($store, IDENTITY_GRANT, 'Rep');

    $order = oeiOrder($store, 'pending', null);

    oeiVolt($repUser, $store)
        ->call('startOrderNameEdit', $order->id)
        ->set('nameEditName', 'Unassigned Edit')
        ->call('saveOrderName');

    expect($order->customer->refresh()->name)->toBe('Unassigned Edit');
});

// ————— 2. no widening beyond identity —————

it('denies products and geography edits to an identity-only member', function () {
    [$ownerUser, $store] = oeiOwner();
    [$repUser, $rep] = oeiStaff($store, IDENTITY_GRANT, 'Rep');
    $order = oeiOrder($store, 'pending', $rep);

    // §6.3 products
    foreach (['startOrderWeightEdit', 'startOrderShipmentTypeEdit'] as $handler) {
        oeiVolt($repUser, $store)
            ->call($handler, $order->id)
            ->assertDispatched('swal:toast', oeiDenied());
    }

    oeiVolt($repUser, $store)
        ->call('openItemsModal', 'products', $order->id)
        ->assertDispatched('swal:toast', oeiDenied());

    // §6.4 geography
    foreach (['startOrderWilayaEdit', 'startOrderCityEdit'] as $handler) {
        oeiVolt($repUser, $store)
            ->call($handler, $order->id)
            ->assertDispatched('swal:toast', oeiDenied());
    }

    // Nothing entered edit mode, so no field can be written through a stale
    // editingId even if a later save handler were reached.
    expect(oeiVolt($repUser, $store)->call('startOrderWeightEdit', $order->id)->get('editingField'))->toBeNull();
});

// ————— 3. a member with neither permission is denied all three —————

it('denies all three identity edits without order.manage or order.edit.identity', function () {
    [$ownerUser, $store] = oeiOwner();
    [$plainUser, $plain] = oeiStaff($store, [StorePermissionEnum::ORDER_VIEW->value], 'Plain');
    $order = oeiOrder($store, 'pending', $plain);

    foreach (['startOrderNameEdit', 'startOrderPhoneEdit', 'startOrderNotesEdit'] as $handler) {
        oeiVolt($plainUser, $store)
            ->call($handler, $order->id)
            ->assertDispatched('swal:toast', oeiDenied());
    }

    // The save handlers are gated too, so a forced editingId cannot be used to
    // bypass the start-handler denial.
    oeiVolt($plainUser, $store)
        ->set('editingId', $order->id)
        ->set('nameEditName', 'Hijacked')
        ->call('saveOrderName')
        ->assertDispatched('swal:toast', oeiDenied());

    oeiVolt($plainUser, $store)
        ->set('editingId', $order->id)
        ->set('editingValue', 'Hijacked')
        ->call('saveOrderNotes', 'Hijacked')
        ->assertDispatched('swal:toast', oeiDenied());

    expect($order->customer->refresh()->name)->toStartWith('OEI Customer')
        ->and($order->fresh()->notes)->toBeNull();
});

// ————— 4. order.manage holder: zero regression —————

it('keeps an order.manage holder working exactly as before', function () {
    [$ownerUser, $store] = oeiOwner();
    [$mgrUser, $mgr] = oeiStaff($store, [StorePermissionEnum::ORDER_VIEW->value, StorePermissionEnum::ORDER_MANAGE->value], 'Manager');
    $order = oeiOrder($store, 'pending', $mgr);

    oeiVolt($mgrUser, $store)
        ->call('startOrderNameEdit', $order->id)
        ->set('nameEditName', 'Manager Renamed')
        ->call('saveOrderName');

    expect($order->customer->refresh()->name)->toBe('Manager Renamed');

    $order = oeiOrder($store, 'pending', $mgr);
    oeiVolt($mgrUser, $store)
        ->call('startOrderPhoneEdit', $order->id)
        ->set('phoneEditPhone', '0555000000')
        ->call('saveOrderPhone')
        ->assertDispatched('swal:toast', fn ($n, $p) => ($p[0]['icon'] ?? null) === 'success');

    expect($order->customer->refresh()->phone)->toBe('0555000000');

    $order = oeiOrder($store, 'pending', $mgr);
    oeiVolt($mgrUser, $store)
        ->call('startOrderNotesEdit', $order->id)
        ->call('saveOrderNotes', 'manager note');

    expect($order->fresh()->notes)->toBe('manager note');
});

it('lets an order.manage holder keep the products and geography edits it always had', function () {
    [$ownerUser, $store] = oeiOwner();
    [$mgrUser, $mgr] = oeiStaff($store, [StorePermissionEnum::ORDER_VIEW->value, StorePermissionEnum::ORDER_MANAGE->value], 'Manager');
    $order = oeiOrder($store, 'pending', $mgr);

    foreach (['startOrderWeightEdit', 'startOrderShipmentTypeEdit', 'startOrderWilayaEdit', 'startOrderCityEdit'] as $handler) {
        oeiVolt($mgrUser, $store)
            ->call($handler, $order->id)
            ->assertNotDispatched('swal:toast', oeiDenied());
    }
});

// ————— 5. saveEdit() permission resolution: string stays byte-identical —————

it('resolves a plain string permission exactly as before', function () {
    [$ownerUser, $store] = oeiOwner();
    [$mgrUser, $mgr] = oeiStaff($store, [StorePermissionEnum::ORDER_VIEW->value, StorePermissionEnum::ORDER_MANAGE->value], 'Manager');
    [$plainUser, $plain] = oeiStaff($store, [StorePermissionEnum::ORDER_VIEW->value], 'Plain');
    $order = oeiOrder($store, 'pending', $mgr);

    // A string naming a PHP function must NOT be treated as callable: the
    // is_string() arm has to win, or every string permission would execute.
    oeiVolt($mgrUser, $store)
        ->call('startOrderNotesEdit', $order->id)
        ->call('saveOrderNotes', 'string path still works')
        ->assertNotDispatched('swal:toast', oeiDenied());

    expect($order->fresh()->notes)->toBe('string path still works');
});

it('lets the notes handler accept the Closure while a plain member is still refused', function () {
    [$ownerUser, $store] = oeiOwner();
    [$repUser, $rep] = oeiStaff($store, IDENTITY_GRANT, 'Rep');
    $order = oeiOrder($store, 'pending', $rep);

    // guardOrderEditable() runs before saveEdit(), so a shipped order still
    // reports cannot_edit_shipped even though the permission arm is now a
    // Closure — the gate order is unchanged.
    $shipped = oeiOrder($store, 'shipped', $rep);

    oeiVolt($repUser, $store)
        ->call('startOrderNotesEdit', $shipped->id)
        ->call('saveOrderNotes', 'should not apply')
        ->assertDispatched('swal:toast', fn ($n, $p) => ($p[0]['title'] ?? null) === __('merchant_panel.cannot_edit_shipped'));

    expect($shipped->fresh()->notes)->toBeNull();
});

// ————— 6. display twins track the handler permission —————

it('renders both twins for identity-only and order.manage members, and neither for a plain member', function () {
    [$ownerUser, $store] = oeiOwner();
    [$repUser, $rep] = oeiStaff($store, IDENTITY_GRANT, 'Rep');
    [$mgrUser, $mgr] = oeiStaff($store, [StorePermissionEnum::ORDER_VIEW->value, StorePermissionEnum::ORDER_MANAGE->value], 'Manager');
    [$plainUser, $plain] = oeiStaff($store, [StorePermissionEnum::ORDER_VIEW->value], 'Plain');

    $triggers = ['startOrderNameEdit', 'startOrderPhoneEdit', 'startOrderNotesEdit'];

    // The orders list itself is assignment-scoped for staff (the 36.4 scope),
    // independently of this phase's store-wide edit grant, so each viewer needs
    // a row of their own for the twins to be on screen at all.
    foreach (['identity-only' => [$repUser, $rep], 'order.manage' => [$mgrUser, $mgr]] as $label => [$user, $membership]) {
        oeiOrder($store, 'pending', $membership);

        $html = oeiVolt($user, $store)->set('visibleColumns', ['customer', 'phone', 'notes'])->html();

        foreach ($triggers as $trigger) {
            // Mobile card (index.blade.php) + desktop table cell
            // (orders-table-cell.blade.php) both wire this trigger.
            $count = substr_count($html, $trigger);
            expect($count)->toBeGreaterThanOrEqual(2, "{$label} should expose {$trigger} in both twins (saw {$count})");
        }
    }

    oeiOrder($store, 'pending', $plain);
    $html = oeiVolt($plainUser, $store)->set('visibleColumns', ['customer', 'phone', 'notes'])->html();

    foreach ($triggers as $trigger) {
        expect(substr_count($html, $trigger))->toBe(0, "plain member must not see {$trigger}");
    }
});

it('keeps the products and geography twins hidden from an identity-only member', function () {
    [$ownerUser, $store] = oeiOwner();
    [$repUser, $rep] = oeiStaff($store, IDENTITY_GRANT, 'Rep');
    $order = oeiOrder($store, 'pending', $rep);

    $html = oeiVolt($repUser, $store)->html();

    // The neighbouring mobile-card twins are §6.3/§6.4 concerns and must stay
    // ORDER_MANAGE-only. mobile-fields.blade.php:18 aggregates geography and
    // shipping columns under its own $canManage, which is why this phase did not
    // touch it.
    foreach (['startOrderWilayaEdit', 'startOrderWeightEdit', 'startOrderShipmentTypeEdit'] as $trigger) {
        expect(substr_count($html, $trigger))->toBe(0, "identity-only must not see {$trigger}");
    }

    // mobile-fields' own aggregate is untouched, so its geography buttons stay
    // hidden for this member as well.
    expect(substr_count($html, 'startOrderCityEdit'))->toBe(0);
    expect(substr_count($html, 'startOrderAddressEdit'))->toBe(0);
    expect(substr_count($html, 'startOrderStopdeskEdit'))->toBe(0);
});
