<?php

use App\Domains\Order\Services\OrderService;
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
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function ogcUser(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate('owner', 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Confirm Gate Store',
        'slug' => 'ogc-'.uniqid(),
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

function ogcOrder(Store $store, string $statusKey = 'pending'): Order
{
    $country = Country::firstOrCreate(
        ['code' => 'DZ'],
        ['name' => 'Algeria', 'arabic_name' => 'الجزائر', 'is_active' => true],
    );

    $state = State::firstOrCreate(
        ['country_id' => $country->id, 'state_code' => '01'],
        ['name' => 'Adrar', 'arabic_name' => 'أدرار', 'is_active' => true],
    );

    $city = City::firstOrCreate(
        ['state_id' => $state->id, 'name' => 'Adrar Centre'],
        ['post_code' => '01000', 'is_active' => true],
    );

    $status = Status::system()
        ->forType('order')
        ->where('key', $statusKey)
        ->firstOrFail();

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => null,
        'status_id' => $status->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 400,
        'shipping_cost' => 0,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'address' => 'Rue des Cedres',
        'delivery_type' => 'home',
    ]);

    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Gate Customer',
        'phone' => '0557'.fake()->unique()->numerify('######'),
        'status' => true,
    ]);

    $order->update(['customer_id' => $customer->id]);

    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Gate Product',
        'slug' => 'ogc-pr-'.uniqid(),
        'sku' => 'ogc-sku-'.uniqid(),
        'type' => 'simple',
        'price' => 400,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'ogc-v-'.uniqid(),
        'price' => 400,
        'stock' => 10,
        'is_active' => true,
    ]);

    OrderItem::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'product_variant_id' => $variant->id,
        'quantity' => 1,
        'price' => 400,
        'subtotal' => 400,
    ]);

    return $order->fresh();
}

function ogcVolt(array $userStore)
{
    [$user, $store] = $userStore;
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    return Volt::test('merchant.orders.index');
}

it('keeps the state machine intact while routing direct confirm attempts to the drawer', function () {
    [$user, $store] = ogcUser();
    $order = ogcOrder($store, 'pending');

    $service = app(OrderService::class);
    expect($service->availableTransitions($order))->toContain('confirmed');

    ogcVolt([$user, $store])
        ->call('transitionOrder', $order->id, 'confirmed')
        ->assertSet('showConfirmModal', true);

    expect(Order::find($order->id)->status?->key)->toBe('pending');
});

it('hides the direct confirmed shortcut from the pending status menu while offering postponed', function () {
    [$user, $store] = ogcUser();
    $order = ogcOrder($store, 'pending');

    $html = ogcVolt([$user, $store])->html();

    preg_match_all('/transitionOrder\([^)]*confirmed[^)]*\)/u', $html, $confirmedHits);
    preg_match_all('/transitionOrder\([^)]*postponed[^)]*\)/u', $html, $postponedHits);

    expect($confirmedHits[0])->toBe([])
        ->and($postponedHits[0])->not->toBeEmpty();
});

it('confirms a pending order through the drawer', function () {
    [$user, $store] = ogcUser();
    $order = ogcOrder($store, 'pending');

    ogcVolt([$user, $store])
        ->set('confirmOrderId', $order->id)
        ->set('showConfirmModal', true)
        ->call('submitConfirmOnly')
        ->assertSet('showConfirmModal', false);

    expect(Order::find($order->id)->status?->key)->toBe('confirmed');
});

it('confirms an on-hold order through the drawer', function () {
    [$user, $store] = ogcUser();
    $order = ogcOrder($store, 'on_hold');

    ogcVolt([$user, $store])
        ->set('confirmOrderId', $order->id)
        ->set('showConfirmModal', true)
        ->call('submitConfirmOnly')
        ->assertSet('showConfirmModal', false);

    expect(Order::find($order->id)->status?->key)->toBe('confirmed');
});

it('offers a single postponed transition from pending', function () {
    [$user, $store] = ogcUser();
    $order = ogcOrder($store, 'pending');

    $service = app(OrderService::class);
    expect($service->availableTransitions($order))->toContain('postponed');

    ogcVolt([$user, $store])
        ->call('transitionOrder', $order->id, 'postponed')
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success');

    expect(Order::find($order->id)->status?->key)->toBe('postponed');
});

it('renders the confirmed system status as success (green)', function () {
    ogcUser();

    $status = Status::system()
        ->forType('order')
        ->where('key', 'confirmed')
        ->first();

    expect($status)->not->toBeNull()
        ->and($status->color)->toBe('success');
});