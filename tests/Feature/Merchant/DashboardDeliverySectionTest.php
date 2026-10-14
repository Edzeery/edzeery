<?php

use App\Domains\Analytics\Support\OrderStatusIdMap;
use App\Enums\Store\OrderStatus;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Stores\Store;
use Carbon\CarbonImmutable;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Database\Seeders\SystemStatusesSeeder;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| PHASE 37-N8 - the delivery status breakdown (count/rate body)
|--------------------------------------------------------------------------
|
| The delivery view grows the same count/rate switch the confirmation view
| already has: the count tab is the fulfilment headline (total, delivered,
| returned, in progress and trash), and the rate tab is the per-status
| breakdown the two rate cards fold from. Every bucket comes from the one
| delivery scan, so a card can never contradict the rate.
|
*/

beforeEach(function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-10 12:00:00', 'UTC'));

    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(SystemStatusesSeeder::class);
    $this->seed(PlansSeeder::class);
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

function ddsOrder(Store $store, OrderStatus $status, array $extra = []): Order
{
    $order = Order::query()->create($extra + [
        'store_id' => $store->id,
        'number' => 'DDS-'.uniqid(),
        'total_amount' => 1500,
        'status_id' => app(OrderStatusIdMap::class)->id($status),
        'delivery_type' => 'home',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // PHASE 37-N9: the delivery cohort is orders carrying a tracking row.
    OrderTracking::create(['store_id' => $store->id, 'order_id' => $order->id]);

    return $order;
}

test('the delivery section renders its header, its two base tabs and the count pair', function () {
    [$owner, $store] = dfwOwnerStore();

    $html = $this->actingAs($owner)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug, 'md' => 'delivery']))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain(__('dashboard.delivery_stats_title'))
        ->toContain(__('dashboard.delivery_tab_count'))
        ->toContain(__('dashboard.delivery_tab_rate'))
        ->toContain('aria-selected="true"');

    // Count tab: the fulfilment headline figures, one card each. The rate tab
    // still owns the "preparing" bucket and the "outside the rate" tagging.
    expect($html)
        ->toContain(__('dashboard.total_orders'))
        ->toContain(__('dashboard.delivered'))
        ->toContain(__('dashboard.returned'))
        ->toContain(__('dashboard.delivery_group_in_progress'))
        ->toContain(__('dashboard.trash_orders'))
        // PHASE 37-N9: every count card explains its figure on hover.
        ->toContain(__('dashboard.delivery_total_hint'))
        ->toContain(__('dashboard.delivery_group_delivered_hint'))
        ->toContain(__('dashboard.delivery_group_returned_hint'))
        ->toContain(__('dashboard.delivery_group_in_progress_hint'))
        ->toContain(__('dashboard.delivery_trash_hint'))
        // The per-status split (and the "outside the rate" tagging) lives on
        // the rate tab.
        ->not->toContain(__('dashboard.delivery_group_preparing'))
        ->not->toContain(__('dashboard.outside_rate'));
});

test('the delivery rate tab renders every status bucket and the rate basis', function () {
    [$owner, $store] = dfwOwnerStore();

    ddsOrder($store, OrderStatus::DELIVERED);
    ddsOrder($store, OrderStatus::RETURNED);
    ddsOrder($store, OrderStatus::SHIPPED);
    ddsOrder($store, OrderStatus::CONFIRMED);
    ddsOrder($store, OrderStatus::CANCELED);
    ddsOrder($store, OrderStatus::COMPLETED);

    $html = $this->actingAs($owner)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug, 'md' => 'delivery', 'dv' => 'rate']))
        ->assertOk()
        ->getContent();

    // Every bucket paints, including the "other" bucket that holds the
    // completed order and a custom status would fall into.
    expect($html)
        ->toContain(__('dashboard.delivery_group_delivered'))
        ->toContain(__('dashboard.delivery_group_returned'))
        ->toContain(__('dashboard.delivery_group_in_progress'))
        ->toContain(__('dashboard.delivery_group_preparing'))
        ->toContain(__('dashboard.delivery_group_canceled'))
        ->toContain(__('dashboard.delivery_group_other'))
        ->toContain(__('dashboard.delivery_group_in_progress_hint'))
        ->toContain(__('dashboard.delivery_rate_gross'));

    // The two leading cards agree with the rate: 1 delivered of 6 orders.
    expect($html)
        ->toContain('17%');
});

test('the delivery tab setter guards invalid values and ignores repeats', function () {
    [$owner, $store] = dfwOwnerStore();
    $this->actingAs($owner)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.dashboard')
        ->set('deliveryTab', 'count')
        ->call('setDeliveryTab', 'rate')
        ->assertSet('deliveryTab', 'rate')
        // Re-requesting the active tab is a no-op.
        ->call('setDeliveryTab', 'rate')
        ->assertSet('deliveryTab', 'rate')
        // Anything outside the two base tabs is ignored.
        ->call('setDeliveryTab', 'members')
        ->assertSet('deliveryTab', 'rate')
        ->call('setDeliveryTab', 'count')
        ->assertSet('deliveryTab', 'count');
});
