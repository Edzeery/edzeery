<?php

use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function dcmUser(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate('owner', 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Mobile Card Parity Store',
        'slug' => 'mcp-'.uniqid(),
        'status' => 'active',
    ]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => 'owner',
    ]);

    return [$user, $store];
}

function dcmOrder(Store $store, string $statusKey = 'pending'): Order
{
    $customer = Customer::firstOrCreate(
        ['store_id' => $store->id, 'phone' => '0550123456'],
        ['name' => 'Mobile Parity Customer', 'status' => true],
    );

    $status = \App\Models\Status::system()
        ->forType('order')
        ->where('key', $statusKey)
        ->firstOrFail();

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => $status->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 500,
        'shipping_cost' => 0,
    ]);

    return $order;
}

function dcmVolt(array $userStore)
{
    [$user, $store] = $userStore;
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    return Volt::test('merchant.orders.index');
}

// P29.8 — the mobile card must expose the same interactive affordances as the
// desktop row: the status transitions dropdown and the inline phone/wilaya
// editors. Both layouts are server-rendered (CSS hides one at each breakpoint),
// so parity is verified by the card-specific markers below.

it('mobile card mirrors the desktop status transitions dropdown', function () {
    [$user, $store] = dcmUser();
    $order = dcmOrder($store, 'pending');

    $html = dcmVolt([$user, $store])->html();

    // One openStatusMenu trigger per row layout (desktop <tr> + mobile card).
    expect(substr_count($html, 'openStatusMenu()'))->toBe(2)
        ->and($html)->toContain('lg:hidden grid grid-cols-1 divide-y divide-surface-border');
});

it('mobile card phone editor renders in the card and cancels cleanly', function () {
    [$user, $store] = dcmUser();
    $order = dcmOrder($store, 'pending');

    $t = dcmVolt([$user, $store]);

    expect($t->html())->not->toContain('phone-inline-card-'.$order->id);

    $t->call('startOrderPhoneEdit', $order->id);
    expect($t->html())->toContain('phone-inline-card-'.$order->id);

    $t->call('cancelOrderPhoneEdit');
    expect($t->html())->not->toContain('phone-inline-card-'.$order->id);
});

it('mobile card wilaya editor renders in the card and cancels cleanly', function () {
    [$user, $store] = dcmUser();
    $order = dcmOrder($store, 'pending');

    $t = dcmVolt([$user, $store]);

    expect($t->html())->not->toContain('wilaya-inline-card-'.$order->id);

    $t->call('startOrderWilayaEdit', $order->id);
    expect($t->html())->toContain('wilaya-inline-card-'.$order->id);

    $t->call('cancelOrderEdit');
    expect($t->html())->not->toContain('wilaya-inline-card-'.$order->id);
});

it('mobile card phone affordance is reachable with order.manage permission', function () {
    [$user, $store] = dcmUser();
    $order = dcmOrder($store, 'pending');

    $html = dcmVolt([$user, $store])->html();

    // Desktop phone cell + card phone line both render the editable display.
    expect(substr_count($html, 'startOrderPhoneEdit'))->toBe(2);
});

it('mobile card renders the missing parity fields when their columns are visible', function () {
    [$user, $store] = dcmUser();
    $order = dcmOrder($store, 'pending');

    $t = dcmVolt([$user, $store]);

    // The four parity fields are opt-in via the column registry; expose them so
    // the card face has to render each inline editor (weight/shipment_type),
    // the carrier-warehouse toggle, and the meta key:value list.
    $t->set('visibleColumns', [
        'customer', 'phone', 'status',
        'weight', 'shipment_type', 'send_from_carrier_warehouse', 'meta',
    ]);
    $html = $t->html();

    // Every sheet now reuses the shared mobile-bottom-sheet chrome, bound to the
    // caller's Alpine menuStyle: status + overflow + events on the card, plus the
    // events dropdown on the desktop row = 4 anchored panels per row (other page
    // components bind menuStyle too, so only the lower bound is asserted).
    expect(substr_count($html, ':style="menuStyle"'))->toBeGreaterThanOrEqual(4);

    // Weight / shipment type / carrier-warehouse toggle / meta all render in
    // the mobile card when their columns are on (defaults via visibleColumns).
    expect($html)->toContain('startOrderWeightEdit')
        ->toContain('startOrderShipmentTypeEdit')
        ->toContain('toggleSendFromWarehouse')
        ->toContain('send_from_carrier_warehouse');

    // The weight editor surfaces its "كغ" suffix hint alongside the numeric
    // input, mirroring the desktop row's unit labeling.
    expect($html)->toContain('wire:click="startOrderWeightEdit')
        ->and($html)->toContain('كغ');

    // All three card chrome instances survive the refactor: the status dropdown
    // keeps its trigger ref, the overflow menu and the events menu their own.
    expect($html)->toContain('x-ref="trigger"')
        ->toContain('x-ref="moreTrigger"')
        ->toContain('x-ref="evTrigger"');
});