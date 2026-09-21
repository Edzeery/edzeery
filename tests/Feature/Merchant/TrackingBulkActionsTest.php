<?php

use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Support\StoreRoles;
use Illuminate\Http\Client\Request;
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

function tbaOwner(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Tracking Bulk Store',
        'slug' => 'tracking-bulk-'.uniqid(),
        'status' => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => StoreRoleEnum::OWNER->value,
    ]);
    $membership->syncPermissions(StoreRoles::permissions(StoreRoleEnum::OWNER));

    return [$user, $store];
}

/**
 * A team member with the given merchant-guard role on a specific store. The
 * membership is seeded like StoreRolesAndPermissionsSeeder does (Decision #6),
 * so stored membership permissions take precedence inside canStore().
 */
function tbaMember(Store $store, string $role, array $permissions): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($role, 'merchant'));

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $store->user_id,
        'is_active' => true,
        'role' => $role,
    ]);
    $membership->syncPermissions($permissions);

    return [$user, $membership];
}

function tbaStatus(string $key): Status
{
    return Status::system()->forType('order')->where('key', $key)->firstOrFail();
}

function tbaNoestProvider(Store $store, string $name = 'NOEST TBA'): App\Domains\Shipping\Models\ShippingProvider
{
    $platform = App\Domains\Shipping\Models\CarrierPlatform::create(['name' => 'Noest TBA', 'slug' => 'noest-tba-'.uniqid(), 'is_active' => true]);

    $carrier = App\Domains\Shipping\Models\Carrier::create([
        'platform_id' => $platform->id,
        'name' => 'NOEST TBA',
        'code' => 'noest',
        'credential_fields' => [],
        'is_active' => true,
    ]);

    return App\Domains\Shipping\Models\ShippingProvider::create([
        'store_id' => $store->id,
        'name' => $name,
        'code' => 'noest',
        'carrier_id' => $carrier->id,
        'credentials' => ['api_token' => 'token-'.uniqid(), 'guid' => 'guid-'.uniqid(), 'api_base' => 'https://noest.test/api/public'],
        'is_active' => true,
        'shipment_types_enabled' => ['delivery'],
    ]);
}

function tbaOrder(Store $store, App\Domains\Shipping\Models\ShippingProvider $provider, string $trackingNumber, string $statusKey): Order
{
    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'TBA '.fake()->unique()->firstName(),
        'phone' => '0560'.fake()->unique()->numerify('######'),
        'status' => true,
    ]);

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => tbaStatus($statusKey)->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 900,
        'shipping_cost' => 0,
        'shipping_provider_id' => $provider->id,
        'delivery_type' => 'home',
    ]);

    OrderTracking::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'shipping_provider_id' => $provider->id,
        'tracking_number' => $trackingNumber,
        'tracking_status' => OrderTrackingStatus::IN_TRANSIT->value,
    ]);

    return $order;
}

function tbaVolt(array $ctx): object
{
    [$user, $store] = $ctx;
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    return Volt::test('merchant.tracking.index');
}

/* ───────────────────────── Selection state ───────────────────────── */

test('selection toggling and select-all keep the visible page in sync', function () {
    [$user, $store] = tbaOwner();
    $provider = tbaNoestProvider($store);
    $a = tbaOrder($store, $provider, 'TRK-BA-SEL-1', 'shipped');
    $b = tbaOrder($store, $provider, 'TRK-BA-SEL-2', 'shipped');

    $volt = tbaVolt([$user, $store]);

    $volt->call('toggleSelectOrder', (string) $a->id)
        ->assertSet('selectedShipments', [(string) $a->id])
        ->assertSet('selectModeActive', true)
        ->assertSet('selectAllChecked', false)
        ->call('toggleSelectOrder', (string) $b->id)
        ->assertSet('selectAllChecked', true)
        ->call('toggleSelectAll', false)
        ->assertSet('selectedShipments', [])
        ->assertSet('selectModeActive', false)
        ->call('toggleSelectAll', true)
        ->assertSet('selectedShipments', fn ($ids) => count($ids) === 2)
        ->call('toggleSelectOrder', (string) $a->id)
        ->assertSet('selectedShipments', fn ($ids) => count($ids) === 1)
        ->call('toggleSelectOrder', 'no-such-id')
        ->assertSet('selectedShipments', fn ($ids) => count($ids) === 1);
});

test('entering the trash clears the bulk selection', function () {
    [$user, $store] = tbaOwner();
    $provider = tbaNoestProvider($store);
    $a = tbaOrder($store, $provider, 'TRK-BA-CLR-1', 'shipped');

    $volt = tbaVolt([$user, $store]);

    $volt->call('toggleSelectOrder', (string) $a->id)
        ->assertSet('selectModeActive', true)
        ->call('toggleTrash')
        ->assertSet('selectedShipments', [])
        ->assertSet('selectModeActive', false)
        ->assertSet('showTrash', true);
});

/* ───────────────────────── Bulk soft delete ───────────────────────── */

test('bulkDeleteOrders soft deletes every selected order and clears the selection', function () {
    [$user, $store] = tbaOwner();
    $provider = tbaNoestProvider($store);
    $a = tbaOrder($store, $provider, 'TRK-BA-DEL-1', 'shipped');
    $b = tbaOrder($store, $provider, 'TRK-BA-DEL-2', 'shipped');

    Http::fake(['noest.test/*' => Http::response(['success' => true, 'message' => 'deleted'])]);

    $volt = tbaVolt([$user, $store]);

    $volt->call('toggleSelectOrder', (string) $a->id)
        ->call('toggleSelectOrder', (string) $b->id)
        ->call('bulkDeleteOrders')
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success')
        ->assertSet('selectedShipments', [])
        ->assertSet('trashCount', 2);

    expect(Order::withTrashed()->find($a->id)->trashed())->toBeTrue()
        ->and(Order::withTrashed()->find($b->id)->trashed())->toBeTrue();
});

test('bulkDeleteOrders reports the blocked count when one carrier delete fails', function () {
    [$user, $store] = tbaOwner();
    $provider = tbaNoestProvider($store);
    $fails = tbaOrder($store, $provider, 'TRK-BA-BLOCK-1', 'shipped');
    $passes = tbaOrder($store, $provider, 'TRK-BA-BLOCK-2', 'shipped');

    Http::fake(function (Request $request) use ($fails) {
        if (data_get($request, 'tracking') === 'TRK-BA-BLOCK-1') {
            return Http::response(['success' => false, 'message' => 'carrier offline']);
        }

        return Http::response(['success' => true, 'message' => 'deleted']);
    });

    $volt = tbaVolt([$user, $store]);

    $volt->call('toggleSelectOrder', (string) $fails->id)
        ->call('toggleSelectOrder', (string) $passes->id)
        ->call('bulkDeleteOrders')
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'warning')
        ->assertSet('selectedShipments', [])
        ->assertSet('trashCount', 1);

    expect(Order::withTrashed()->find($fails->id)->trashed())->toBeFalse()
        ->and(Order::withTrashed()->find($passes->id)->trashed())->toBeTrue();
});

test('bulkDeleteOrders is refused for staff without ORDER_DELETE and leaves the orders untouched', function () {
    [$owner, $store] = tbaOwner();
    $provider = tbaNoestProvider($store);
    $order = tbaOrder($store, $provider, 'TRK-BA-NO-1', 'shipped');

    [$staff] = tbaMember($store, 'staff', [
        StorePermissionEnum::ORDER_VIEW->value,
        StorePermissionEnum::ORDER_CONFIRM->value,
        StorePermissionEnum::CRM_ORDER_TRACKING->value,
    ]);

    $volt = tbaVolt([$staff, $store]);

    $volt->call('toggleSelectOrder', (string) $order->id)
        ->call('bulkDeleteOrders')
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'error');

    expect(Order::find($order->id)->trashed())->toBeFalse();
});

/* ───────────────────────── Bulk reassign ───────────────────────── */

test('staff stay blocked from reassigning even with an explicit ORDER_ASSIGN', function () {
    [$owner, $store] = tbaOwner();
    $provider = tbaNoestProvider($store);
    $order = tbaOrder($store, $provider, 'TRK-BA-RS-1', 'shipped');

    [$staff] = tbaMember($store, 'staff', [
        StorePermissionEnum::ORDER_VIEW->value,
        StorePermissionEnum::ORDER_ASSIGN->value,
        StorePermissionEnum::CRM_ORDER_TRACKING->value,
    ]);

    $volt = tbaVolt([$staff, $store]);

    expect(canStore(StorePermissionEnum::ORDER_ASSIGN->value))->toBeTrue();

    $volt->call('openTrackingReassignModal', (string) $order->id)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'error')
        ->assertSet('trackingReassignOpen', false);

    $volt->call('toggleSelectOrder', (string) $order->id)
        ->call('openBulkTrackingReassignModal')
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'error')
        ->assertSet('trackingReassignOpen', false);

    expect(OrderTracking::where('order_id', $order->id)->firstOrFail()->assigned_to_membership_id)->toBeNull();
});

test('a manager can bulk reassign the selected trackings to a colleague', function () {
    [$owner, $store] = tbaOwner();
    $provider = tbaNoestProvider($store);
    $a = tbaOrder($store, $provider, 'TRK-BA-RB-1', 'shipped');
    $b = tbaOrder($store, $provider, 'TRK-BA-RB-2', 'shipped');

    // Phase 36.7: tracking is no longer a manager default — grant it
    // explicitly so the two managers remain valid reassign candidates.
    $managerPerms = [
        ...StoreRoles::permissions(StoreRoleEnum::MANAGER),
        StorePermissionEnum::CRM_ORDER_TRACKING->value,
    ];
    [$manager] = tbaMember($store, 'manager', $managerPerms);
    [, $target] = tbaMember($store, 'manager', $managerPerms);

    $volt = tbaVolt([$manager, $store]);

    $volt->call('toggleSelectOrder', (string) $a->id)
        ->call('toggleSelectOrder', (string) $b->id)
        ->call('openBulkTrackingReassignModal')
        ->assertSet('trackingReassignOpen', true)
        ->assertSet('trackingReassignBulk', true)
        ->set('trackingReassignMembershipId', (string) $target->id)
        ->call('submitTrackingReassign')
        ->assertSet('trackingReassignOpen', false)
        ->assertSet('selectedShipments', [])
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success'
            && str_contains((string) ($params[0]['title'] ?? ''), '2'));

    expect(OrderTracking::where('order_id', $a->id)->firstOrFail()->assigned_to_membership_id)->toBe($target->id)
        ->and(OrderTracking::where('order_id', $b->id)->firstOrFail()->assigned_to_membership_id)->toBe($target->id);
});

/* ───────────────────────── Permanent purge gating ───────────────────────── */

test('the permanent purge requires ORDER_DELETE_FINAL: admins are refused by default, owners are allowed', function () {
    [$owner, $store] = tbaOwner();
    $provider = tbaNoestProvider($store);
    $order = tbaOrder($store, $provider, 'TRK-BA-FD-1', 'shipped');
    $order->delete();

    [$admin] = tbaMember($store, 'admin', StoreRoles::permissions(StoreRoleEnum::ADMIN));

    $this->actingAs($admin);
    $this->withSession(['current_store_id' => $store->id]);
    app(\App\Support\StoreContext::class)->clear();

    expect(canFinalDeleteOrders())->toBeFalse()
        ->and(canStore(StorePermissionEnum::ORDER_DELETE_FINAL->value))->toBeFalse();

    $volt = tbaVolt([$admin, $store]);
    $volt->call('toggleTrash')
        ->assertSet('showTrash', true)
        ->call('forceDeleteOrder', (string) $order->id);

    expect(Order::withTrashed()->find($order->id))->not->toBeNull();

    // The owner can force-delete the very same order.
    Http::fake(['noest.test/*' => Http::response(['success' => true, 'message' => 'deleted'])]);

    $ownerFlow = tbaVolt([$owner, $store]);
    $ownerFlow->call('toggleTrash')
        ->call('forceDeleteOrder', (string) $order->id)
        ->assertSet('trashCount', 0)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success');

    expect(Order::withTrashed()->find($order->id))->toBeNull();
});

test('admins can permanently delete once granted an explicit ORDER_DELETE_FINAL', function () {
    [$owner, $store] = tbaOwner();
    $provider = tbaNoestProvider($store);
    $order = tbaOrder($store, $provider, 'TRK-BA-FD-2', 'shipped');
    $order->delete();

    $adminPerms = StoreRoles::permissions(StoreRoleEnum::ADMIN);
    $adminPerms[] = StorePermissionEnum::ORDER_DELETE_FINAL->value;

    [$admin] = tbaMember($store, 'admin', $adminPerms);

    Http::fake(['noest.test/*' => Http::response(['success' => true, 'message' => 'deleted'])]);

    $volt = tbaVolt([$admin, $store]);

    $volt->call('toggleTrash')
        ->call('forceDeleteOrder', (string) $order->id)
        ->assertSet('trashCount', 0);

    expect(Order::withTrashed()->find($order->id))->toBeNull();
});

/* ───────────────────────── Bulk validation scope ───────────────────────── */

test('runBulkValidate validates exactly the selection, not other page rows', function () {
    [$owner, $store] = tbaOwner();
    $provider = tbaNoestProvider($store);
    $a = tbaOrder($store, $provider, 'TRK-BA-BV-1', 'shipped');
    $b = tbaOrder($store, $provider, 'TRK-BA-BV-2', 'shipped');

    [$admin] = tbaMember($store, 'admin', StoreRoles::permissions(StoreRoleEnum::ADMIN));

    $sent = [];
    Http::fake(function (Request $request) use (&$sent) {
        if (str_contains($request->url(), '/valid/orders')) {
            $sent = $request->data()['trackings'] ?? [];
        }

        return Http::response(['success' => true]);
    });

    $volt = tbaVolt([$admin, $store]);

    $volt->call('toggleSelectOrder', (string) $a->id)
        ->call('runBulkValidate')
        ->assertSet('showBulkValidateResults', true)
        ->assertSet('bulkValidateResults', fn ($rows) => count($rows) === 1
            && $rows[0]['order_id'] === (string) $a->id
            && $rows[0]['ok'] === true);

    expect($sent)->toBe(['TRK-BA-BV-1'])
        ->and(app(\App\Domains\Order\Services\OrderTrackingService::class)
            ->currentTracking($a->fresh())->isCarrierValidated())->toBeTrue()
        ->and(app(\App\Domains\Order\Services\OrderTrackingService::class)
            ->currentTracking($b->fresh())->isCarrierValidated())->toBeFalse();
});