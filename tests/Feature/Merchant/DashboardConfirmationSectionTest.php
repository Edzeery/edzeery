<?php

use App\Domains\Analytics\Support\OrderStatusIdMap;
use App\Enums\Store\OrderStatus;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Stores\Store;
use Carbon\CarbonImmutable;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Database\Seeders\SystemStatusesSeeder;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| PHASE 39 - M4 confirmation section and product filter (D3/D4/D7/D10)
|--------------------------------------------------------------------------
|
| M4 lands the confirmation tab itself: a header with the two base tabs, the
| count tab's headline pair (all orders + trash) and the rate tab's single rate
| with its six ratio groups. The carrier filter is deliberately absent in this
| view (D4), the date basis pills and the product picker are confirmation-only,
| and the product pick narrows the section via an EXISTS clause (D3/D10).
|
*/

beforeEach(function () {
    // PHASE 37-N6: pin the clock mid-day. "today" is built in the store's
    // timezone (Africa/Algiers, UTC+1); an implicit created_at = now() could
    // otherwise fall past the store-day boundary between creating an order and
    // querying it, dropping it out of the window and flipping the count.
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-10 12:00:00', 'UTC'));

    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(SystemStatusesSeeder::class);
    $this->seed(PlansSeeder::class);
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

/**
 * An order in a given status, using the seeded status rows the way the
 * analytics queries expect them (orders.status_id -> statuses.key).
 */
function dscOrder(Store $store, OrderStatus $status, array $extra = []): Order
{
    return Order::query()->create($extra + [
        'store_id' => $store->id,
        'number' => 'DSC-'.uniqid(),
        'total_amount' => 1500,
        'status_id' => app(OrderStatusIdMap::class)->id($status),
        'delivery_type' => 'home',
        // PHASE 37-N6: stamp the frozen clock explicitly so the row can never
        // straddle the store-day boundary of the default "today" window.
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

test('the confirmation section renders its headline, trash figure and every group card', function () {
    [$owner, $store] = dfwOwnerStore();

    $html = $this->actingAs($owner)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug]))
        ->assertOk()
        ->getContent();

    // The section header and its two base tabs.
    expect($html)
        ->toContain(__('dashboard.confirmation_stats_title'))
        ->toContain(__('dashboard.confirmation_tab_count'))
        ->toContain(__('dashboard.confirmation_tab_rate'))
        ->toContain('aria-selected="true"');

    // Count tab: the headline pair only. The per-status breakdown belongs to
    // the rate tab, so the eight group cards no longer sit here.
    expect($html)
        ->toContain(__('dashboard.total_orders'))
        ->toContain(__('dashboard.trash_orders'))
        ->toContain(__('dashboard.group_confirmed').': 0')
        // D7: deleted orders render between brackets, never in a ratio.
        ->toContain(__('dashboard.deleted_brackets', ['count' => 0]))
        // The rate-only explainer and group hints belong to the rate tab.
        ->not->toContain(__('dashboard.outside_rate'))
        ->not->toContain(__('dashboard.group_confirmed_hint'))
        ->not->toContain(__('dashboard.group_other_hint'));

    // The delivery view does not own this section.
    $delivery = $this->get(route('merchant.dashboard', ['store' => $store->slug, 'md' => 'delivery']))
        ->assertOk()
        ->getContent();

    expect($delivery)->not->toContain(__('dashboard.confirmation_stats_title'));
});

test('the rate tab renders the rate, its basis and only the six ratio groups', function () {
    [$owner, $store] = dfwOwnerStore();

    // Default basis (created) is the gross rate: 1 confirmed of all 3 orders
    // created in the period => 1 / 3 = 33%.
    dscOrder($store, OrderStatus::CONFIRMED);
    dscOrder($store, OrderStatus::CANCELED);
    dscOrder($store, OrderStatus::PENDING);

    $html = $this->actingAs($owner)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug, 'ct' => 'rate']))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain(__('dashboard.confirmation_tab_rate'))
        ->toContain('33%')
        ->toContain(__('dashboard.confirmation_rate_gross'))
        ->toContain(__('dashboard.confirmation_rate_gross_basis', ['confirmed' => 1, 'denominator' => 3]));

    // The rate tab keeps the rate groups only: no delivery group, no
    // unclassified bucket.
    expect($html)
        ->toContain(__('dashboard.group_confirmed'))
        ->toContain(__('dashboard.group_canceled'))
        ->toContain(__('dashboard.group_pending'))
        ->toContain(__('dashboard.group_failed'))
        ->toContain(__('dashboard.group_duplicate'))
        ->toContain(__('dashboard.group_deferred'))
        ->toContain(__('dashboard.group_other'))
        ->not->toContain('passed_to_delivery');
});

test('confirmed orders resolve into the section and soft-deleted ones stay bracketed', function () {
    [$owner, $store] = dfwOwnerStore();

    dscOrder($store, OrderStatus::CONFIRMED);
    dscOrder($store, OrderStatus::CONFIRMED);
    // A soft-deleted confirmed order still counts in the total but is bracketed.
    $trashed = dscOrder($store, OrderStatus::CONFIRMED);
    $trashed->delete();

    $html = $this->actingAs($owner)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug]))
        ->assertOk()
        ->getContent();

    expect($html)
        // The live confirmed orders (2) and their soft-deleted sibling (1) come
        // from the same scan, so the headline and the bracket agree.
        ->toContain(__('dashboard.confirmed_of_total', ['total' => 2]).', '.__('dashboard.group_confirmed').': 2')
        ->toContain(__('dashboard.deleted_brackets', ['count' => 1]))
        ->toContain(__('dashboard.trash_orders'));
});

test('the date basis pills are confirmation and delivery, live-bound', function () {
    [$owner, $store] = dfwOwnerStore();

    $html = $this->actingAs($owner)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug]))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain(__('dashboard.date_basis'))
        ->toContain(__('dashboard.date_basis_created'))
        ->toContain(__('dashboard.date_basis_last_status'))
        ->toContain('wire:model.live="dateBasis"');

    // The confirmation-only product filter control is present with the pills.
    expect($html)->toContain('id="dashboard-product-filter"');

    // A non-default basis is surfaced as a chip with a reset affordance.
    $overridden = $this->get(route('merchant.dashboard', ['store' => $store->slug, 'db' => 'last_status_changed']))
        ->assertOk()
        ->getContent();

    expect($overridden)
        ->toContain(__('dashboard.active_filters'))
        ->toContain(__('dashboard.date_basis_last_status'));

    $delivery = $this->get(route('merchant.dashboard', ['store' => $store->slug, 'md' => 'delivery']))
        ->assertOk()
        ->getContent();

    // PHASE 37-N7 (E): the delivery view carries the same date basis and
    // product picker controls the confirmation view does.
    expect($delivery)
        ->toContain('wire:model.live="dateBasis"')
        ->toContain('id="dashboard-product-filter"');
});

test('the product filter opens a modal and toggles url-backed picks', function () {
    [$owner, $store] = dfwOwnerStore();
    $this->actingAs($owner)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.dashboard')
        ->call('openProductFilter')
        ->assertSet('showProductFilterModal', true)
        ->assertSee(__('dashboard.product_filter_title'))
        ->assertSee(__('dashboard.search_products'))
        // Picking a product appends it to the url-backed list...
        ->call('toggleFilterProduct', 'prod-1')
        ->assertSet('productIds', ['prod-1'])
        ->assertSee('1 '.__('dashboard.products_selected'))
        // ...and toggling it again removes it (no duplicates).
        ->call('toggleFilterProduct', 'prod-1')
        ->assertSet('productIds', [])
        ->call('toggleFilterProduct', 'prod-1')
        ->call('toggleFilterProduct', 'prod-2')
        ->assertSet('productIds', ['prod-1', 'prod-2'])
        // Closing the modal keeps the picks and hides its markup.
        ->call('closeProductFilter')
        ->assertSet('showProductFilterModal', false)
        ->assertDontSee(__('dashboard.product_filter_title'));
});

test('the product filter narrows the confirmation section to the picked product', function () {
    [$owner, $store] = dfwOwnerStore();

    $product = Product::query()->create([
        'store_id' => $store->id,
        'name' => 'M4 Filter Product',
        'slug' => 'm4-filter-product-'.uniqid(),
        'sku' => 'm4-sku-'.uniqid(),
        'price' => 1000,
        'is_active' => true,
        'type' => 'simple',
    ]);

    $variant = ProductVariant::query()->create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'm4-variant-'.uniqid(),
        'price' => 1000,
        'stock' => 5,
    ]);

    dscOrder($store, OrderStatus::CONFIRMED);
    $withProduct = dscOrder($store, OrderStatus::CONFIRMED);

    OrderItem::query()->create([
        'store_id' => $store->id,
        'order_id' => $withProduct->id,
        'product_variant_id' => $variant->id,
        'product_id' => $product->id,
        'name' => $product->name,
        'price' => 1000,
        'quantity' => 1,
        'subtotal' => 1000,
    ]);

    $all = $this->actingAs($owner)->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug]))
        ->assertOk()->getContent();

    expect($all)->toContain(__('dashboard.group_confirmed').': 2');

    $filtered = $this->get(route('merchant.dashboard', ['store' => $store->slug, 'pr[]' => $product->id]))
        ->assertOk()->getContent();

    // Only the order that holds the picked product remains in the section.
    expect($filtered)
        ->toContain(__('dashboard.group_confirmed').': 1')
        ->toContain('1 '.__('dashboard.products_selected'))
        ->toContain(__('dashboard.active_filters'));
});

test('the confirmation tab setter guards invalid values and ignores repeats', function () {
    [$owner, $store] = dfwOwnerStore();
    $this->actingAs($owner)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.dashboard')
        ->set('confirmationTab', 'count')
        ->call('setConfirmationTab', 'rate')
        ->assertSet('confirmationTab', 'rate')
        // Re-requesting the active tab is a no-op.
        ->call('setConfirmationTab', 'rate')
        ->assertSet('confirmationTab', 'rate')
        // Anything outside the two base tabs is ignored.
        ->call('setConfirmationTab', 'members')
        ->assertSet('confirmationTab', 'rate')
        ->call('setConfirmationTab', 'count')
        ->assertSet('confirmationTab', 'count');
});

test('a shipped order stays inside the confirmation rate', function () {
    [$owner, $store] = dfwOwnerStore();

    // 2 confirmed + 1 delivered + 1 canceled => 3 / 4 = 75%. Shipping the third
    // order (a confirmed one leaving the desk) never moves the line, because
    // the delivered outcome stays inside the confirmed group (D2).
    dscOrder($store, OrderStatus::CONFIRMED);
    dscOrder($store, OrderStatus::CONFIRMED);
    dscOrder($store, OrderStatus::DELIVERED);
    dscOrder($store, OrderStatus::CANCELED);

    $html = $this->actingAs($owner)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug, 'ct' => 'rate']))
        ->assertOk()->getContent();

    expect($html)
        ->toContain('75%')
        ->toContain(__('dashboard.confirmation_rate_gross_basis', ['confirmed' => 3, 'denominator' => 4]))
        ->toContain(__('dashboard.group_confirmed'))
        ->not->toContain('passed_to_delivery');
});

test('the confirmation dashboard blades carry no dark:, raw gray, or washed-out text', function () {
    $files = array_merge(
        [resource_path('views/livewire/merchant/dashboard.blade.php')],
        glob(resource_path('views/livewire/merchant/dashboard/partials/*.blade.php')) ?: [],
    );

    foreach ($files as $file) {
        $name = basename($file);
        $source = (string) file_get_contents($file);

        expect($source)
            // T1: the theme already resolves the light/dark pair, so a blade
            // must never branch on dark: (D3 group colours are theme tokens).
            ->not->toContain('dark:', "{$name} ships a dark: variant")
            // Raw Tailwind grays bypass the theme tokens entirely.
            ->not->toContain('gray-', "{$name} ships a raw gray-* utility")
            // T1: a translucent text token drops below its 4.5:1 contrast.
            ->not->toContain('text-ink-muted/', "{$name} dims a text token with opacity")
            ->not->toContain('text-ink-soft/', "{$name} dims a text token with opacity")
            ->not->toContain('text-ink/', "{$name} dims a text token with opacity");
    }
});
