<?php

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

function otlUser(string $storeRole = 'owner'): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Tooltip Store',
        'slug' => 'tip-' . uniqid(),
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

function otlVariant(Store $store): ProductVariant
{
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Tooltip Sneaker',
        'slug' => 'tip-pr-' . uniqid(),
        'sku' => 'tip-sku-' . uniqid(),
        'type' => 'variable',
        'price' => 400,
        'is_active' => true,
    ]);

    return ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'tip-v-' . uniqid(),
        'price' => 400,
        'stock' => 10,
        'is_active' => true,
    ]);
}

function otlOrder(Store $store, string $statusKey = 'pending'): Order
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

    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Tooltip Customer',
        'phone' => '0551' . fake()->unique()->numerify('######'),
        'status' => true,
    ]);

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => $status->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 400,
        'shipping_cost' => 0,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'address' => 'Rue des Cedres',
        'delivery_type' => 'home',
        'stopdesk_point_id' => null,
        'shipping_provider_id' => null,
    ]);

    OrderItem::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'product_variant_id' => otlVariant($store)->id,
        'product_id' => null,
        'quantity' => 2,
        'price' => 400,
        'subtotal' => 800,
    ]);

    return $order->fresh();
}

test('desktop compact actions column renders tooltips and aria-labels instead of native titles', function () {
    [$user, $store, $membership] = otlUser(StoreRoleEnum::OWNER->value);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $html = view('livewire.merchant.orders.partials.orders-table-actions-column', [
        'orderId' => 1,
        'order' => [
            'status_key' => 'pending',
            'can_confirm' => false,
            'can_view_events' => false,
        ],
        'transitions' => [],
        'showTrash' => false,
        'layout' => 'compact',
    ])->render();

    expect($html)
        ->toContain('edz-tooltip')
        ->toContain('aria-label="' . __('merchant.order_details') . '"')
        ->not->toContain('title="' . __('merchant.order_details') . '"')
        ->toContain('aria-label="' . __('merchant_panel.edit') . '"')
        ->toContain('aria-label="' . __('merchant_panel.reassign') . '"')
        ->not->toContain('title="' . __('merchant_panel.edit') . '"');
});

test('list actions column keeps its visible labels and carries no tooltip wrappers', function () {
    [$user, $store, $membership] = otlUser(StoreRoleEnum::OWNER->value);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $html = view('livewire.merchant.orders.partials.orders-table-actions-column', [
        'orderId' => 1,
        'order' => [
            'status_key' => 'preparing',
            'can_confirm' => false,
            'can_view_events' => false,
        ],
        'transitions' => [],
        'showTrash' => false,
        'layout' => 'list',
    ])->render();

    expect($html)
        ->not->toContain('edz-tooltip')
        ->toContain(__('order_flow.send_to_carrier'))
        ->toContain(__('merchant_panel.edit'))
        ->toContain(__('merchant.delete_permanently'));
});

test('orders index wires aria-labels and the x-edz.tooltip in the desktop actions column', function () {
    [$user, $store, $membership] = otlUser(StoreRoleEnum::OWNER->value);
    otlOrder($store);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $html = Volt::test('merchant.orders.index')->html();

    expect($html)
        ->toContain('aria-label="' . __('merchant.order_details') . '"')
        ->toContain('aria-label="' . __('order_flow.order_timeline') . '"')
        ->toContain('edz-tooltip');
});

test('orders index deploys block tooltips for truncated products cells', function () {
    [$user, $store, $membership] = otlUser(StoreRoleEnum::OWNER->value);
    otlOrder($store);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $html = Volt::test('merchant.orders.index')
        ->set('visibleColumns', ['products'])
        ->html();

    expect($html)
        ->toContain('edz-tooltip--block')
        ->toContain('aria-label="' . __('merchant_panel.edit_items') . '"')
        ->toContain('Default ×2');
});

test('orders index tools up the icon-only warehouse toggle with a tooltip', function () {
    [$user, $store, $membership] = otlUser(StoreRoleEnum::OWNER->value);
    otlOrder($store);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $html = Volt::test('merchant.orders.index')
        ->set('visibleColumns', ['send_from_carrier_warehouse'])
        ->html();

    expect($html)
        ->toContain('aria-label="' . __('merchant_panel.send_from_carrier_warehouse') . '"')
        ->toContain('edz-tooltip');
});