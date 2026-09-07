<?php

use App\Domains\Shipping\Models\ShippingProvider;
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
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function ospUser(string $storeRole = 'owner'): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Provider Column Store',
        'slug' => 'osp-' . uniqid(),
        'status' => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => $storeRole,
    ]);

    return [$user, $store, $membership];
}

function ospProvider(Store $store, string $name, bool $isDefault = false): ShippingProvider
{
    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => $name,
        'code' => 'osp-' . strtolower(\Illuminate\Support\Str::slug($name)) . '-' . substr(uniqid(), -4),
        'credentials' => [],
        'is_active' => true,
        'is_default' => $isDefault,
        'flat_rate' => 600,
    ]);
}

function ospVariant(Store $store): ProductVariant
{
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Osp Product',
        'slug' => 'osp-pr-' . uniqid(),
        'sku' => 'osp-sku-' . uniqid(),
        'type' => 'variable',
        'price' => 400,
        'is_active' => true,
    ]);

    return ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'osp-v-' . uniqid(),
        'price' => 400,
        'stock' => 10,
        'is_active' => true,
    ]);
}

function ospOrder(Store $store, string $statusKey = 'pending', array $opts = []): Order
{
    $status = Status::system()
        ->forType('order')
        ->where('key', $statusKey)
        ->firstOrFail();

    $country = Country::firstOrCreate(
        ['code' => 'DZ'],
        ['name' => 'Algeria', 'arabic_name' => 'الجزائر', 'is_active' => true],
    );

    $state = State::firstOrCreate(
        ['country_id' => $country->id, 'state_code' => '01'],
        ['name' => 'Adrar', 'arabic_name' => 'أدرار', 'is_active' => true],
    );

    $city = City::firstOrCreate(
        ['state_id' => $state->id, 'name' => 'Timimoun', 'arabic_name' => 'تيميمون'],
        ['post_code' => '01001', 'is_active' => true],
    );

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
        'stopdesk_point_id' => null,
        'shipping_provider_id' => $opts['shipping_provider_id'] ?? null,
    ]);

    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Osp Customer',
        'phone' => '0551' . fake()->unique()->numerify('######'),
        'status' => true,
    ]);
    $order->update(['customer_id' => $customer->id]);

    OrderItem::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'product_variant_id' => ospVariant($store)->id,
        'product_id' => null,
        'quantity' => 1,
        'price' => 400,
        'subtotal' => 400,
    ]);

    return $order->fresh();
}

test('opening the provider editor prefills the store default when the order has none', function () {
    [$user, $store, $membership] = ospUser(StoreRoleEnum::OWNER->value);
    ospProvider($store, 'Aaa Carrier');
    $default = ospProvider($store, 'Bbb Carrier', true);
    $order = ospOrder($store, 'pending', ['shipping_provider_id' => null]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->call('startOrderProviderEdit', $order->id)
        ->assertSet('editingValue', (string) $default->id);
});

test('opening the provider editor keeps the order own provider when set', function () {
    [$user, $store, $membership] = ospUser(StoreRoleEnum::OWNER->value);
    $pinned = ospProvider($store, 'Aaa Carrier');
    ospProvider($store, 'Bbb Carrier', true);
    $order = ospOrder($store, 'pending', ['shipping_provider_id' => $pinned->id]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->call('startOrderProviderEdit', $order->id)
        ->assertSet('editingValue', (string) $pinned->id);
});

test('saving a picked provider persists it on the order', function () {
    [$user, $store, $membership] = ospUser(StoreRoleEnum::OWNER->value);
    $pinned = ospProvider($store, 'Aaa Carrier');
    ospProvider($store, 'Bbb Carrier', true);
    $order = ospOrder($store, 'pending', ['shipping_provider_id' => null]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->call('startOrderProviderEdit', $order->id)
        ->set('editingValue', (string) $pinned->id)
        ->call('saveOrderProvider');

    expect($order->fresh()->shipping_provider_id)->toBe($pinned->id);
});

test('the provider column shows a select hint instead of a dash for required empty cells', function () {
    [$user, $store, $membership] = ospUser(StoreRoleEnum::OWNER->value);
    ospProvider($store, 'Bbb Carrier', true);
    $order = ospOrder($store, 'pending', ['shipping_provider_id' => null]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->set('visibleColumns', ['shipping_provider'])
        ->assertSeeHtml('<span class="text-warning font-medium">'.__('order_flow.select_shipping_provider').'</span>');
});

test('the provider column renders the assigned provider name instead of a hint', function () {
    [$user, $store, $membership] = ospUser(StoreRoleEnum::OWNER->value);
    $provider = ospProvider($store, 'Osp Alpha Carrier');
    $order = ospOrder($store, 'pending', ['shipping_provider_id' => $provider->id]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->set('visibleColumns', ['shipping_provider'])
        ->assertSee('Osp Alpha Carrier');
});