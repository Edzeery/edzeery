<?php

use App\Domains\Shipping\Models\ShippingProvider;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Carbon;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function dpfUser(string $storeRole = 'owner'): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Default Provider Store',
        'slug' => 'dpf-'.uniqid(),
        'status' => 'active',
    ]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => $storeRole,
    ]);

    return [$user, $store];
}

function dpfProvider(Store $store, string $name, bool $active = true, bool $isDefault = false): ShippingProvider
{
    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => $name,
        'credentials' => [],
        'is_active' => $active,
        'is_default' => $isDefault,
    ]);
}

function dpfVolt(array $userStore)
{
    [$user, $store] = $userStore;
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    return Volt::test('merchant.orders.index');
}

function dpfOrder(Store $store, string $phone, ?string $providerId = null): Order
{
    $customer = Customer::firstOrCreate(
        ['store_id' => $store->id, 'phone' => $phone],
        ['name' => 'Default Provider Customer', 'status' => true],
    );

    $status = \App\Models\Status::system()
        ->forType('order')
        ->where('key', 'draft')
        ->firstOrFail();

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => $status->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 400,
        'shipping_cost' => 0,
        'shipping_provider_id' => $providerId,
    ]);

    $order->created_at = now();
    $order->save();

    return $order;
}

test('the create modal auto-selects the flagged default shipping company', function () {
    [$user, $store] = dpfUser();
    $default = dpfProvider($store, 'Zitadel', isDefault: true);
    dpfProvider($store, 'Armedex');

    dpfVolt([$user, $store])
        ->call('openCreateModal')
        ->assertSet('form.shipping_provider_id', $default->id);
});

test('the create modal falls back to the first active company when none is flagged default', function () {
    [$user, $store] = dpfUser();
    $first = dpfProvider($store, 'Alpha');
    dpfProvider($store, 'Beta');

    dpfVolt([$user, $store])
        ->call('openCreateModal')
        ->assertSet('form.shipping_provider_id', $first->id);
});

test('the default provider lookup is scoped to the active store', function () {
    [$user, $store] = dpfUser();

    [$otherUser, $otherStore] = dpfUser();
    dpfProvider($otherStore, 'Other', isDefault: true);

    // The active store has no providers: its create modal must stay empty and
    // never leak the default flagged on the other store.
    dpfVolt([$user, $store])
        ->call('openCreateModal')
        ->assertSet('form.shipping_provider_id', '');

    dpfVolt([$otherUser, $otherStore])
        ->call('openCreateModal')
        ->assertSet('form.shipping_provider_id', '');
});

test('the confirmation drawer auto-selects the default company for an order without a carrier', function () {
    [$user, $store] = dpfUser();
    $default = dpfProvider($store, 'Zitadel', isDefault: true);
    $order = dpfOrder($store, '0550600001');

    dpfVolt([$user, $store])
        ->call('openConfirmModal', $order->id)
        ->assertSet('confirmProviderId', $default->id);
});

test('the confirmation drawer keeps the order existing carrier when set', function () {
    [$user, $store] = dpfUser();
    $default = dpfProvider($store, 'Zitadel', isDefault: true);
    $carrier = dpfProvider($store, 'Armedex');
    $order = dpfOrder($store, '0550600002', $carrier->id);

    dpfVolt([$user, $store])
        ->call('openConfirmModal', $order->id)
        ->assertSet('confirmProviderId', $carrier->id);
});

test('a store with a single shipping company hides the selector and auto-selects the company', function () {
    [$user, $store] = dpfUser();
    $solo = dpfProvider($store, 'Solo Carrier');

    dpfVolt([$user, $store])
        ->call('openCreateModal')
        ->assertSet('form.shipping_provider_id', $solo->id)
        ->assertSeeHtml('data-edz-company-single')
        ->assertDontSeeHtml('edz-company-select');
});

test('a store with a single shipping company keeps it when editing an order without a carrier', function () {
    [$user, $store] = dpfUser();
    $solo = dpfProvider($store, 'Solo Carrier');
    $order = dpfOrder($store, '0550600003');

    dpfVolt([$user, $store])
        ->call('openEditModal', $order->id)
        ->assertSet('form.shipping_provider_id', $solo->id)
        ->assertSeeHtml('data-edz-company-single');
});