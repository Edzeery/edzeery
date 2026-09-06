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
        ->and($html)->toContain('lg:hidden divide-y divide-surface-border');
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