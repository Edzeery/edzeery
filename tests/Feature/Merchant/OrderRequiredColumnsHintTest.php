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

function rchUser(string $storeRole = 'owner'): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Required Columns Store',
        'slug' => 'rch-' . uniqid(),
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

function rchProvider(Store $store, string $name, bool $isDefault = false): ShippingProvider
{
    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => $name,
        'code' => 'rch-' . strtolower(\Illuminate\Support\Str::slug($name)) . '-' . substr(uniqid(), -4),
        'credentials' => [],
        'is_active' => true,
        'is_default' => $isDefault,
        'flat_rate' => 600,
    ]);
}

function rchVariant(Store $store): ProductVariant
{
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Rch Product',
        'slug' => 'rch-pr-' . uniqid(),
        'sku' => 'rch-sku-' . uniqid(),
        'type' => 'variable',
        'price' => 400,
        'is_active' => true,
    ]);

    return ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'rch-v-' . uniqid(),
        'price' => 400,
        'stock' => 10,
        'is_active' => true,
    ]);
}

function rchOrder(Store $store, string $statusKey = 'pending', array $opts = []): Order
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

    $opts += [
        'state_id' => $state->id,
        'city_id' => $city->id,
        'address' => 'Rue des Cedres',
        'delivery_type' => 'home',
        'stopdesk_point_id' => null,
        'shipping_provider_id' => null,
    ];

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => null,
        'status_id' => $status->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 400,
        'shipping_cost' => 0,
        'state_id' => $opts['state_id'],
        'city_id' => $opts['city_id'],
        'address' => $opts['address'],
        'delivery_type' => $opts['delivery_type'],
        'stopdesk_point_id' => $opts['stopdesk_point_id'],
        'shipping_provider_id' => $opts['shipping_provider_id'],
    ]);

    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Rch Customer',
        'phone' => '0551' . fake()->unique()->numerify('######'),
        'status' => true,
    ]);
    $order->update(['customer_id' => $customer->id]);

    OrderItem::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'product_variant_id' => rchVariant($store)->id,
        'product_id' => null,
        'quantity' => 1,
        'price' => 400,
        'subtotal' => 400,
    ]);

    return $order->fresh();
}

$hintSpan = fn (string $text) => '<span class="text-warning font-medium">' . $text . '</span>';

test('wilaya column shows a select hint for a required empty cell', function () use ($hintSpan) {
    [$user, $store, $membership] = rchUser(StoreRoleEnum::OWNER->value);
    $order = rchOrder($store, 'pending', ['state_id' => null, 'city_id' => null]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->set('visibleColumns', ['wilaya'])
        ->assertSeeHtml($hintSpan(__('order_flow.please_select_state')));
});

test('city column shows a select hint for a required empty cell', function () use ($hintSpan) {
    [$user, $store, $membership] = rchUser(StoreRoleEnum::OWNER->value);
    $order = rchOrder($store, 'pending', ['state_id' => null, 'city_id' => null]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->set('visibleColumns', ['city'])
        ->assertSeeHtml($hintSpan(__('order_flow.please_select_city')));
});

test('shipping provider column shows a select hint for a required empty cell', function () use ($hintSpan) {
    [$user, $store, $membership] = rchUser(StoreRoleEnum::OWNER->value);
    $order = rchOrder($store, 'pending', ['shipping_provider_id' => null]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->set('visibleColumns', ['shipping_provider'])
        ->assertSeeHtml($hintSpan(__('order_flow.please_select_shipping_provider')));
});

test('stopdesk column shows a select hint when delivery type is stopdesk and empty', function () use ($hintSpan) {
    [$user, $store, $membership] = rchUser(StoreRoleEnum::OWNER->value);
    $order = rchOrder($store, 'pending', ['delivery_type' => 'stopdesk']);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->set('visibleColumns', ['stopdesk_point'])
        ->assertSeeHtml($hintSpan(__('order_flow.please_select_stopdesk')));
});

test('stopdesk column does NOT hint when delivery type is home', function () use ($hintSpan) {
    [$user, $store, $membership] = rchUser(StoreRoleEnum::OWNER->value);
    $order = rchOrder($store, 'pending', ['delivery_type' => 'home']);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->set('visibleColumns', ['stopdesk_point'])
        ->assertDontSeeHtml($hintSpan(__('order_flow.please_select_stopdesk')));
});

test('optional columns keep their plain dash instead of a hint', function () use ($hintSpan) {
    [$user, $store, $membership] = rchUser(StoreRoleEnum::OWNER->value);
    $order = rchOrder($store, 'pending');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->set('visibleColumns', ['notes'])
        ->assertDontSeeHtml($hintSpan(__('order_flow.please_select_state')))
        ->assertDontSeeHtml($hintSpan(__('order_flow.please_select_city')))
        ->assertDontSeeHtml($hintSpan(__('order_flow.please_select_delivery_type')));
});

test('mobile card shows a prominent confirm button for a confirmable order', function () {
    [$user, $store, $membership] = rchUser(StoreRoleEnum::OWNER->value);
    rchProvider($store, 'Default Carrier', true);
    $order = rchOrder($store, 'pending', ['shipping_provider_id' => null]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->assertSee("openConfirmModal('" . $order->id . "')", escape: false)
        ->assertSeeHtml('edz-btn edz-btn--primary edz-btn--sm')
        ->assertSee(__('order_flow.confirm_title'));
});

test('mobile card hides the confirm button once the order is no longer confirmable', function () {
    [$user, $store, $membership] = rchUser(StoreRoleEnum::OWNER->value);
    $order = rchOrder($store, 'shipped');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->assertDontSee("openConfirmModal('" . $order->id . "')", escape: false);
});

test('bulk tasks dropdown appears in the toolbar only when orders are selected', function () {
    [$user, $store, $membership] = rchUser(StoreRoleEnum::OWNER->value);
    $order = rchOrder($store, 'pending');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->assertDontSee(__('merchant.bulk_tasks'))
        ->set('selectedOrders', [$order->id])
        ->assertSee(__('merchant.bulk_tasks'))
        ->assertSee(__('merchant.bulk_assign_agent'))
        ->assertSee(__('merchant.bulk_send_carrier'))
        ->assertSee(__('merchant.bulk_delete'));
});