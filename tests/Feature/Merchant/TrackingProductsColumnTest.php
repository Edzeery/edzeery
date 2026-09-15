<?php

use App\Domains\Shipping\Models\ShippingProvider;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderTracking;
use App\Models\Products\Product;
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

function tpcOwner(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Tracking Products Store',
        'slug' => 'tpc-'.uniqid(),
        'status' => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => StoreRoleEnum::OWNER->value,
    ]);

    return [$user, $store, $membership];
}

function tpcCarrierStatus(): Status
{
    return Status::system()
        ->forType('order')
        ->where('key', 'shipped')
        ->firstOrFail();
}

function tpcProduct(Store $store, string $name, string $slug): Product
{
    return Product::create([
        'store_id' => $store->id,
        'name' => $name,
        'slug' => $slug,
        'sku' => strtoupper(substr($name, 0, 3)).'-'.fake()->unique()->numerify('######'),
        'price' => 1000,
        'is_active' => true,
    ]);
}

function tpcOrder(Store $store, ShippingProvider $provider, string $trackingNumber, array $products): Order
{
    $customer = Customer::firstOrCreate(
        ['store_id' => $store->id, 'phone' => '0560'.fake()->unique()->numerify('######')],
        ['name' => 'TPC '.fake()->unique()->firstName(), 'status' => true],
    );

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => tpcCarrierStatus()->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 900,
        'shipping_cost' => 0,
        'shipping_provider_id' => $provider->id,
    ]);

    OrderTracking::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'shipping_provider_id' => $provider->id,
        'tracking_number' => $trackingNumber,
        'tracking_status' => 'shipped',
    ]);

    foreach ($products as $productId => $qty) {
        $variant = \App\Models\Products\ProductVariant::firstOrCreate(
            ['store_id' => $store->id, 'product_id' => $productId, 'name' => 'Default'],
            ['sku' => 'VAR-'.uniqid(), 'price' => 1000, 'stock' => 10],
        );

        OrderItem::create([
            'store_id' => $store->id,
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'product_id' => $productId,
            'quantity' => $qty,
            'price' => 1000,
            'subtotal' => 1000 * $qty,
        ]);
    }

    return $order;
}

function tpcProvider(Store $store, string $name): ShippingProvider
{
    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => $name,
        'code' => strtoupper(substr($name, 0, 3)),
        'is_active' => true,
        'credentials' => [],
        'shipment_types_enabled' => ['delivery'],
    ]);
}

test('the products column is required, in the default order and never hideable', function () {
    [$user, $store] = tpcOwner();
    $provider = tpcProvider($store, 'Col A');
    tpcOrder($store, $provider, 'TRK-P1', [
        tpcProduct($store, 'Shirt A', 'shirt-a-'.uniqid())->id => 1,
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->assertSet('visibleColumns', fn ($cols) => ($cols[5] ?? null) === 'products')
        ->call('openTableSettings')
        ->call('toggleDraftColumn', 'products')
        ->assertSet('draftColumns', fn ($cols) => in_array('products', $cols, true))
        ->call('saveTableSettings')
        ->assertSet('visibleColumns', fn ($cols) => in_array('products', $cols, true));
});

test('each grid row exposes grouped products with summed quantities', function () {
    [$user, $store] = tpcOwner();
    $provider = tpcProvider($store, 'Col B');
    $shirt = tpcProduct($store, 'Shirt B', 'shirt-b-'.uniqid());
    $jeans = tpcProduct($store, 'Jeans B', 'jeans-b-'.uniqid());
    tpcOrder($store, $provider, 'TRK-P2', [
        $shirt->id => 2,
        $jeans->id => 1,
    ]);
    tpcOrder($store, $provider, 'TRK-P3', [
        $shirt->id => 1,
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->assertSet('shipments', fn ($rows) => collect($rows)->firstWhere('tracking_number', 'TRK-P2')['products']
            === [['name' => 'Shirt B', 'qty' => 2], ['name' => 'Jeans B', 'qty' => 1]])
        ->assertSet('allProducts', fn ($products) => collect($products)->pluck('name')->sort()->values()->all() === ['Jeans B', 'Shirt B']);
});

test('an order with no items exposes an empty products list (renders an em-dash)', function () {
    [$user, $store] = tpcOwner();
    $provider = tpcProvider($store, 'Col C');
    $order = tpcOrder($store, $provider, 'TRK-P4', []);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->assertSet('shipments', fn ($rows) => collect($rows)->firstWhere('id', (string) $order->id)['products'] === []);
});

test('the products header filter combines selections with OR semantics', function () {
    [$user, $store] = tpcOwner();
    $provider = tpcProvider($store, 'Col D');
    $socks = tpcProduct($store, 'Socks D', 'socks-d-'.uniqid());
    $cap = tpcProduct($store, 'Cap D', 'cap-d-'.uniqid());
    $o1 = tpcOrder($store, $provider, 'TRK-P5', [$socks->id => 1]);
    $o2 = tpcOrder($store, $provider, 'TRK-P6', [$cap->id => 2]);
    $o3 = tpcOrder($store, $provider, 'TRK-P7', [$socks->id => 3, $cap->id => 1]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->call('toggleProductFilter', (string) $socks->id)
        ->assertSet('shipments', fn ($rows) => collect($rows)->pluck('id')->sort()->values()->all()
            === collect([$o1->id, $o3->id])->sort()->values()->all())
        ->call('toggleProductFilter', (string) $cap->id)
        ->assertSet('shipments', fn ($rows) => count($rows) === 3)
        ->call('setFilter', 'products', [])
        ->assertSet('shipments', fn ($rows) => count($rows) === 3);
});