<?php

use App\Domains\Order\Services\OrderService;
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
use App\Support\StoreOrderPermissions;
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
 * The narrow grant §6.1 is about: order.view (the mandatory prerequisite that
 * PermissionGroupMeta pulls in automatically when the row is toggled) plus
 * order.status.manage.own, and deliberately NOT order.manage / order.confirm /
 * order.cancel. canStore() honours a membership's stored set exclusively
 * (helpers.php:155-159), so anything absent here is genuinely absent.
 */
const OWN_GRANT = [
    StorePermissionEnum::ORDER_VIEW->value,
    StorePermissionEnum::ORDER_STATUS_MANAGE_OWN->value,
];

function smoOwner(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name'    => 'Status Own Store',
        'slug'    => 'smo-'.uniqid(),
        'status'  => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id'   => $store->id,
        'user_id'    => $user->id,
        'invited_by' => $user->id,
        'is_active'  => true,
        'role'       => StoreRoleEnum::OWNER->value,
    ]);
    $membership->syncPermissions(\App\Support\StoreRoles::permissions(StoreRoleEnum::OWNER));

    return [$user, $store, $membership];
}

/**
 * A staff membership carrying exactly $permissions. Staff is deliberate: it is
 * the least-privileged role, so it holds neither TEAM_VIEW nor TEAM_VIEW_OWN
 * and scopeVisibleTo() lands on its final branch — orders assigned to itself.
 *
 * Returns [user, membership]: every scenario must act AS the rep, since both
 * canStore() and currentMembership() resolve from the authenticated user.
 */
function smoStaff(Store $store, array $permissions, string $name = 'Rep'): array
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

function smoOrder(Store $store, string $statusKey = 'pending', ?StoreMembership $assignee = null): Order
{
    $status = Status::system()->forType('order')->where('key', $statusKey)->firstOrFail();

    // Location fields are required by OrderCompleteness, which submitConfirmOnly
    // consults before confirming. Without them the confirm path always bails on
    // a missing-fields warning and the happy path is untestable.
    $country = Country::firstOrCreate(['code' => 'DZ'], ['name' => 'Algeria', 'arabic_name' => 'الجزائر', 'is_active' => true]);
    $state = State::firstOrCreate(['country_id' => $country->id, 'state_code' => '01'], ['name' => 'Adrar', 'arabic_name' => 'أدرار', 'is_active' => true]);
    $city = City::firstOrCreate(['state_id' => $state->id, 'name' => 'Adrar Centre'], ['post_code' => '01000', 'is_active' => true]);

    $customer = Customer::create([
        'store_id' => $store->id,
        'name'     => 'SMO Customer '.Str::random(5),
        'phone'    => '0553'.fake()->unique()->numerify('######'),
        'status'   => true,
    ]);

    $product = Product::create([
        'store_id'  => $store->id,
        'name'      => 'SMO Product',
        'slug'      => 'smo-pr-'.uniqid(),
        'sku'       => 'SMO-'.strtoupper(Str::random(6)),
        'type'      => 'simple',
        'price'     => 400,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'store_id'  => $store->id,
        'product_id' => $product->id,
        'name'      => 'Default',
        'sku'       => 'smo-v-'.uniqid(),
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
        'product_variant_id' => $variant->id,
        'product_id'         => $product->id,
        'quantity'           => 1,
        'price'              => 400,
        'subtotal'           => 400,
    ]);

    return $order->fresh();
}

function smoKey(Order $order): ?string
{
    return Order::find($order->id)?->status?->key;
}

function smoVolt(User $user, Store $store)
{
    actingAs($user)->withSession(['current_store_id' => $store->id]);
    app(\App\Support\StoreContext::class)->clear();

    return Volt::test('merchant.orders.index');
}

/**
 * canStore() returns false without store context (helpers.php:118), so even a
 * direct canTransitionStatus() call has to authenticate the actor first.
 */
function smoActAs(User $user, Store $store): void
{
    actingAs($user)->withSession(['current_store_id' => $store->id]);
    app(\App\Support\StoreContext::class)->clear();
}

// ————— 0. forStatus() is untouched —————

it('leaves forStatus mapping exactly as documented', function () {
    foreach (StoreOrderPermissions::CONFIRM_STATUSES as $key) {
        expect(StoreOrderPermissions::forStatus($key))->toBe(StorePermissionEnum::ORDER_CONFIRM->value);
    }

    foreach (StoreOrderPermissions::CANCEL_STATUSES as $key) {
        expect(StoreOrderPermissions::forStatus($key))->toBe(StorePermissionEnum::ORDER_CANCEL->value);
    }

    // The :61 fallback must remain order.manage, not the new permission.
    foreach (['shipped', 'delivering', 'delivered', 'returned', 'follow_up'] as $key) {
        expect(StoreOrderPermissions::forStatus($key))->toBe(StorePermissionEnum::ORDER_MANAGE->value);
    }
});

// ————— 1. canTransitionStatus truth table —————

it('grants the status.manage.own branch only inside the member visibleTo scope', function () {
    [$user, $store] = smoOwner();
    [$repUser, $rep] = smoStaff($store, OWN_GRANT, 'Rep');
    [$peerUser, $peer] = smoStaff($store, OWN_GRANT, 'Peer');

    $mine = smoOrder($store, 'pending', $rep);
    $theirs = smoOrder($store, 'pending', $peer);

    smoActAs($repUser, $store);

    // D1: the OR branch spans EVERY status key — confirm set, cancel/call-outcome
    // set, and the forStatus() :61 fallback alike. Only scope limits it.
    foreach (['confirmed', 'preparing', 'on_hold', 'no_answer_1', 'postponed', 'duplicate', 'shipped', 'delivering'] as $key) {
        expect(StoreOrderPermissions::canTransitionStatus(
            $mine->id, $key, $rep, (string) $store->id
        ))->toBeTrue("own order should pass for {$key}");
    }

    foreach (['confirmed', 'no_answer_1', 'postponed', 'duplicate', 'shipped'] as $key) {
        expect(StoreOrderPermissions::canTransitionStatus(
            $theirs->id, $key, $rep, (string) $store->id
        ))->toBeFalse("colleague's order should be denied for {$key}");
    }
})->group('smo');

it('never lets the status.manage.own branch widen an ordinary member', function () {
    [$user, $store] = smoOwner();

    // order.view but not status.manage.own.
    [$plainUser, $plain] = smoStaff($store, [StorePermissionEnum::ORDER_VIEW->value], 'Plain');
    $mine = smoOrder($store, 'pending', $plain);

    smoActAs($plainUser, $store);

    expect(StoreOrderPermissions::canTransitionStatus($mine->id, 'no_answer_1', $plain, (string) $store->id))->toBeFalse();

    // A null membership must not unlock the branch. scopeVisibleTo() treats
    // null as "no scoping", so without the guard this would match any order in
    // the store — the single most dangerous shape this feature could take.
    [$ownUser, $ownOnly] = smoStaff($store, OWN_GRANT, 'Own');
    smoActAs($ownUser, $store);

    expect(StoreOrderPermissions::canTransitionStatus($mine->id, 'no_answer_1', null, (string) $store->id))->toBeFalse();

    // An unknown order id is simply absent, never a crash.
    expect(StoreOrderPermissions::canTransitionStatus('99999999', 'no_answer_1', $ownOnly, (string) $store->id))->toBeFalse();
});

// ————— 2. transitionOrder (step 2) —————

it('passes the rep through the transitionOrder gate for confirm but not the drawer', function () {
    [$user, $store] = smoOwner();
    [$repUser, $rep] = smoStaff($store, OWN_GRANT, 'Rep');
    [$peerUser, $peer] = smoStaff($store, OWN_GRANT, 'Peer');

    $mine = smoOrder($store, 'pending', $rep);
    $theirs = smoOrder($store, 'pending', $peer);

    // The wrapper itself does clear the rep for their own order (step 2 works).
    smoActAs($repUser, $store);
    expect(StoreOrderPermissions::canTransitionStatus($mine->id, 'confirmed', $rep, (string) $store->id))->toBeTrue()
        ->and(StoreOrderPermissions::canTransitionStatus($theirs->id, 'confirmed', $rep, (string) $store->id))->toBeFalse();

    // But the confirm branch of $transitionOrder delegates to $openConfirmModal,
    // which holds its own hard canStore(ORDER_CONFIRM) gate at
    // orders/index.blade.php:1761 — a site outside §6.1's five steps. So the
    // drawer does not open for the rep. This records the real end state; it is
    // NOT an endorsement, and it fails acceptance criterion "can confirm their
    // own order" until that gate is addressed.
    smoVolt($repUser, $store)
        ->call('transitionOrder', $mine->id, 'confirmed')
        ->assertSet('showConfirmModal', false)
        ->assertDispatched('swal:toast', fn ($n, $p) => ($p[0]['title'] ?? null) === __('messages.permission_denied'));

    expect(smoKey($mine))->toBe('pending');

    // A colleague's order is refused at the same point, for the same outcome.
    smoVolt($repUser, $store)
        ->call('transitionOrder', $theirs->id, 'confirmed')
        ->assertSet('showConfirmModal', false);

    expect(smoKey($theirs))->toBe('pending');
});

it('lets the rep record a call outcome on their own order and denies a colleague', function () {
    [$user, $store] = smoOwner();
    [$repUser, $rep] = smoStaff($store, OWN_GRANT, 'Rep');
    [$peerUser, $peer] = smoStaff($store, OWN_GRANT, 'Peer');

    $mine = smoOrder($store, 'pending', $rep);
    $theirs = smoOrder($store, 'pending', $peer);

    // no_answer_1 is a CANCEL_STATUS key, so forStatus() demands order.cancel,
    // which the rep does not hold. This is the D1 daily-driver case.
    expect(StoreOrderPermissions::forStatus('no_answer_1'))->toBe(StorePermissionEnum::ORDER_CANCEL->value);

    smoVolt($repUser, $store)
        ->call('transitionOrder', $mine->id, 'no_answer_1')
        ->assertDispatched('swal:toast', fn ($n, $p) => ($p[0]['icon'] ?? null) === 'success');

    expect(smoKey($mine))->toBe('no_answer_1');

    smoVolt($repUser, $store)
        ->call('transitionOrder', $theirs->id, 'no_answer_1')
        ->assertDispatched('swal:toast', fn ($n, $p) => ($p[0]['icon'] ?? null) === 'error');

    expect(smoKey($theirs))->toBe('pending');
});

// ————— 3. markOrderDuplicate (step 3) —————

it('lets the rep mark their own order duplicate and denies a colleague', function () {
    [$user, $store] = smoOwner();
    [$repUser, $rep] = smoStaff($store, OWN_GRANT, 'Rep');
    [$peerUser, $peer] = smoStaff($store, OWN_GRANT, 'Peer');

    $mine = smoOrder($store, 'pending', $rep);
    $theirs = smoOrder($store, 'pending', $peer);

    smoVolt($repUser, $store)->call('markOrderDuplicate', $mine->id);

    expect(smoKey($mine))->toBe('duplicate');

    smoVolt($repUser, $store)
        ->call('markOrderDuplicate', $theirs->id)
        ->assertDispatched('swal:toast', fn ($n, $p) => ($p[0]['icon'] ?? null) === 'error');

    expect(smoKey($theirs))->toBe('pending');
});

it('denies the duplicate branch identically whether or not the order exists', function () {
    [$user, $store] = smoOwner();
    [$repUser, $rep] = smoStaff($store, OWN_GRANT, 'Rep');
    [$peerUser, $peer] = smoStaff($store, OWN_GRANT, 'Peer');

    // Step 3 moved findOrFail() below the permission check precisely so a denied
    // member cannot distinguish "exists but not mine" from "does not exist".
    $theirs = smoOrder($store, 'pending', $peer);
    $titles = [];

    foreach ([$theirs->id, '99999999'] as $id) {
        $seen = null;
        smoVolt($repUser, $store)
            ->call('markOrderDuplicate', $id)
            ->assertDispatched('swal:toast', function ($n, $p) use (&$seen) {
                $seen = $p[0]['title'] ?? null;

                return true;
            });
        $titles[] = $seen;
    }

    expect($titles[0])->toBe(__('messages.permission_denied'))
        ->and($titles[1])->toBe(__('messages.permission_denied'));
});

// ————— 4/5. Bulk status (steps 4 and 5) —————

it('opens the bulk status window for the rep', function () {
    [$user, $store] = smoOwner();
    [$repUser, $rep] = smoStaff($store, OWN_GRANT, 'Rep');
    $mine = smoOrder($store, 'pending', $rep);

    smoVolt($repUser, $store)
        ->set('selectedOrders', [$mine->id])
        ->call('openBulkStatusModal')
        ->assertSet('showBulkStatusModal', true);
});

it('keeps the bulk status window closed for a member without order.manage', function () {
    [$user, $store] = smoOwner();
    [$plainUser, $plain] = smoStaff($store, [StorePermissionEnum::ORDER_VIEW->value], 'Plain');
    $mine = smoOrder($store, 'pending', $plain);

    smoVolt($plainUser, $store)
        ->set('selectedOrders', [$mine->id])
        ->call('openBulkStatusModal')
        ->assertSet('showBulkStatusModal', false)
        ->assertDispatched('swal:toast', fn ($n, $p) => ($p[0]['icon'] ?? null) === 'error');
});

it('skips a colleague order in a bulk submission instead of failing it', function () {
    [$user, $store] = smoOwner();
    [$repUser, $rep] = smoStaff($store, OWN_GRANT, 'Rep');
    [$peerUser, $peer] = smoStaff($store, OWN_GRANT, 'Peer');

    $mineA = smoOrder($store, 'pending', $rep);
    $mineB = smoOrder($store, 'pending', $rep);
    $theirs = smoOrder($store, 'pending', $peer);

    // THE decisive assertion of §6.1: a mixed batch completes partially.
    // done=2, skipped=1 — the colleague's order is counted and left alone.
    smoVolt($repUser, $store)
        ->set('selectedOrders', [$mineA->id, $theirs->id, $mineB->id])
        ->call('openBulkStatusModal')
        ->set('bulkStatusTarget', 'postponed')
        ->call('submitBulkStatus')
        ->assertDispatched('swal:toast', fn ($n, $p) => $p[0]['title'] === __('order_flow.bulk_status_done', ['done' => 2, 'skipped' => 1]));

    expect(smoKey($mineA))->toBe('postponed')
        ->and(smoKey($mineB))->toBe('postponed')
        ->and(smoKey($theirs))->toBe('pending');
});

it('leaves a fully out-of-scope batch entirely skipped', function () {
    [$user, $store] = smoOwner();
    [$repUser, $rep] = smoStaff($store, OWN_GRANT, 'Rep');
    [$peerUser, $peer] = smoStaff($store, OWN_GRANT, 'Peer');

    $theirs = smoOrder($store, 'pending', $peer);

    smoVolt($repUser, $store)
        ->set('selectedOrders', [$theirs->id])
        ->call('openBulkStatusModal')
        ->set('bulkStatusTarget', 'postponed')
        ->call('submitBulkStatus')
        ->assertDispatched('swal:toast', fn ($n, $p) => $p[0]['title'] === __('order_flow.bulk_status_done', ['done' => 0, 'skipped' => 1]));

    expect(smoKey($theirs))->toBe('pending');
});

// ————— Zero regression for existing holders —————

it('leaves an order.manage holder unrestricted and on their historical forStatus behaviour', function () {
    [$user, $store] = smoOwner();
    [$repUser, $rep] = smoStaff($store, OWN_GRANT, 'Rep');

    // Two separate actors on purpose: canStore() memoizes per user id for the
    // whole request, so mutating one membership's permissions mid-test would be
    // masked by the earlier negative.
    [$manageOnlyUser, $manageOnly] = smoStaff($store, array_merge(OWN_GRANT, [
        StorePermissionEnum::ORDER_MANAGE->value,
    ]), 'ManageOnly');
    [$fullUser, $full] = smoStaff($store, array_merge(OWN_GRANT, [
        StorePermissionEnum::ORDER_MANAGE->value,
        StorePermissionEnum::ORDER_CANCEL->value,
    ]), 'Full');

    $theirs = smoOrder($store, 'pending', $rep);

    // forStatus() maps 'shipped' to order.manage (the :61 fallback), so the
    // first branch short-circuits before any scope is consulted — even for an
    // order this member has no visibility of. Unchanged from before the phase.
    smoActAs($manageOnlyUser, $store);
    expect(StoreOrderPermissions::canTransitionStatus($theirs->id, 'shipped', $manageOnly, (string) $store->id))->toBeTrue();

    // The historical nuance: order.manage has never implied order.cancel, so a
    // call outcome was denied before this phase and still is. This guards
    // against the new OR branch silently widening it via the scope fallback.
    expect(StoreOrderPermissions::canTransitionStatus($theirs->id, 'no_answer_1', $manageOnly, (string) $store->id))->toBeFalse();

    // Holding order.cancel makes the same action work, again unchanged.
    smoActAs($fullUser, $store);
    expect(StoreOrderPermissions::canTransitionStatus($theirs->id, 'no_answer_1', $full, (string) $store->id))->toBeTrue();

    smoVolt($fullUser, $store)
        ->call('transitionOrder', $theirs->id, 'no_answer_1')
        ->assertDispatched('swal:toast', fn ($n, $p) => ($p[0]['icon'] ?? null) === 'success');

    expect(smoKey($theirs))->toBe('no_answer_1');

    // And the per-order filter must not engage for an order.manage holder: a
    // mixed batch across two members runs whole.
    $other = smoOrder($store, 'pending', $rep);
    smoVolt($fullUser, $store)
        ->set('selectedOrders', [$theirs->id, $other->id])
        ->call('openBulkStatusModal')
        ->set('bulkStatusTarget', 'postponed')
        ->call('submitBulkStatus');

    expect(smoKey($other))->toBe('postponed');
});

it('keeps order.confirm and order.cancel holders on their historical forStatus behaviour', function () {
    [$user, $store] = smoOwner();
    [$confirmerUser, $confirmer] = smoStaff($store, [
        StorePermissionEnum::ORDER_VIEW->value,
        StorePermissionEnum::ORDER_CONFIRM->value,
    ], 'Confirmer');
    [$cancellerUser, $canceller] = smoStaff($store, [
        StorePermissionEnum::ORDER_VIEW->value,
        StorePermissionEnum::ORDER_CANCEL->value,
    ], 'Canceller');

    $a = smoOrder($store, 'pending', $confirmer);
    $b = smoOrder($store, 'pending', $canceller);

    // order.confirm holder: confirms, still cannot record a call outcome.
    smoActAs($confirmerUser, $store);
    expect(StoreOrderPermissions::canTransitionStatus($a->id, 'confirmed', $confirmer, (string) $store->id))->toBeTrue()
        ->and(StoreOrderPermissions::canTransitionStatus($a->id, 'no_answer_1', $confirmer, (string) $store->id))->toBeFalse();

    // order.cancel holder: call outcome, still cannot reach the :61 fallback.
    smoActAs($cancellerUser, $store);
    expect(StoreOrderPermissions::canTransitionStatus($b->id, 'no_answer_1', $canceller, (string) $store->id))->toBeTrue()
        ->and(StoreOrderPermissions::canTransitionStatus($b->id, 'shipped', $canceller, (string) $store->id))->toBeFalse();

    // Neither holds the new permission, so the OR branch must stay shut for them.
    expect($confirmer->can(StorePermissionEnum::ORDER_STATUS_MANAGE_OWN->value))->toBeFalse()
        ->and($canceller->can(StorePermissionEnum::ORDER_STATUS_MANAGE_OWN->value))->toBeFalse();
});

it('still denies a member with no order permission at all', function () {
    [$user, $store] = smoOwner();
    [$nobodyUser, $nobody] = smoStaff($store, [StorePermissionEnum::ORDER_VIEW->value], 'Nobody');
    $mine = smoOrder($store, 'pending', $nobody);

    smoVolt($nobodyUser, $store)
        ->call('transitionOrder', $mine->id, 'confirmed')
        ->assertSet('showConfirmModal', false);

    smoVolt($nobodyUser, $store)
        ->call('markOrderDuplicate', $mine->id)
        ->assertDispatched('swal:toast', fn ($n, $p) => ($p[0]['icon'] ?? null) === 'error');

    expect(smoKey($mine))->toBe('pending');
});

// ————— Documented gap: $submitConfirmOnly is a 6th gate outside §6.1 —————

it('documents that the confirm drawer itself is still gated on order.confirm', function () {
    [$user, $store] = smoOwner();
    [$repUser, $rep] = smoStaff($store, OWN_GRANT, 'Rep');
    $mine = smoOrder($store, 'pending', $rep);

    // §6.1 covers 5 sites. $submitConfirmOnly (orders/index.blade.php:1841)
    // holds its own hard canStore(ORDER_CONFIRM) gate and is not among them, so
    // the rep gets through the transitionOrder gate and then stops here. This
    // test records the current behaviour; it is NOT an endorsement.
    smoVolt($repUser, $store)
        ->set('confirmOrderId', $mine->id)
        ->set('showConfirmModal', true)
        ->call('submitConfirmOnly')
        ->assertDispatched('swal:toast', fn ($n, $p) => ($p[0]['icon'] ?? null) === 'error');

    expect(smoKey($mine))->toBe('pending');
});

it('confirms normally for a member who does hold order.confirm', function () {
    [$user, $store] = smoOwner();
    [$confirmerUser, $confirmer] = smoStaff($store, [
        StorePermissionEnum::ORDER_VIEW->value,
        StorePermissionEnum::ORDER_CONFIRM->value,
    ], 'Confirmer');
    $mine = smoOrder($store, 'pending', $confirmer);

    smoVolt($confirmerUser, $store)
        ->set('confirmOrderId', $mine->id)
        ->set('showConfirmModal', true)
        ->call('submitConfirmOnly')
        ->assertSet('showConfirmModal', false);

    expect(smoKey($mine))->toBe('confirmed');
});
