<?php

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Domains\Analytics\Services\StoreDashboardAnalyticsService;
use App\Domains\Analytics\Support\DashboardFilterFactory;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * The values of one metric inside a trend payload. Every bucket the axis
 * drew is present, so summing them gives that metric's window total.
 */
function dftValues(array $trend, string $key): array
{
    $series = collect($trend['series'])->firstWhere('key', $key);

    return $series['values'] ?? [];
}

/*
 * Fixtures deliberately cover every dimension the dashboard groups by (period,
 * store, carrier, confirmation member, delivery member, status, state, delivery
 * type), so a block that silently drops one of them shows up as a wrong count.
 */

beforeEach(function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-10 12:00:00', 'UTC'));

    $this->user = User::query()->create([
        'name' => 'Analyst',
        'email' => 'analyst-'.uniqid().'@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->store = Store::query()->create([
        'name' => 'Analytics Store',
        'slug' => 'analytics-'.uniqid(),
        'currency_code' => 'SAR',
        'locale' => 'ar',
        'is_active' => true,
        'user_id' => $this->user->id,
    ]);

    // A store_settings row is created with the store; the timezone lives there.
    $this->store->settings()->update(['timezone' => 'Africa/Algiers']);

    // Statuses are global reference rows and are not seeded by RefreshDatabase.
    $this->status = collect(['pending', 'confirmed', 'delivered', 'returned'])
        ->mapWithKeys(function ($key) {
            $id = (string) Str::ulid();
            DB::table('statuses')->insert([
                'id' => $id,
                'store_id' => null,
                'type' => 'order',
                'key' => $key,
                'label' => ucfirst($key),
                'is_system' => true,
            ]);

            return [$key => $id];
        });

    $this->country = makeLocation('countries', ['name' => 'Algeria', 'code' => 'DZ']);
    $this->stateAlgiers = makeState('Algiers', '16');
    $this->stateOran = makeState('Oran', '31');

    $this->carrierA = makeCarrier('Carrier A');
    $this->carrierB = makeCarrier('Carrier B');

    $this->memberA = makeMembership();
    $this->memberB = makeMembership();

    // A membership of a different store, used to prove isolation.
    $this->foreignStore = Store::query()->create([
        'name' => 'Foreign Store',
        'slug' => 'foreign-'.uniqid(),
        'currency_code' => 'SAR',
        'locale' => 'ar',
        'is_active' => true,
        'user_id' => $this->user->id,
    ]);
    $this->foreignMember = StoreMembership::query()->create([
        'store_id' => $this->foreignStore->id,
        'user_id' => $this->user->id,
        'role' => 'OWNER',
        'is_active' => true,
    ]);

    // The viewer can see the whole team and both analytics capabilities, so a
    // requested dimension is honoured and an unselected member means "everyone".
    $this->viewer = makeMembership();
    $this->viewer->syncPermissions([
        StorePermissionEnum::STATS_TEAM_VIEW->value,
        StorePermissionEnum::STATS_CONFIRMATION->value,
        StorePermissionEnum::STATS_DELIVERY->value,
    ]);

    /**
     * Builds the filter exactly as the Volt dashboard does: through the factory,
     * with the viewer's membership.
     */
    $this->filter = fn (array $input = []) => app(DashboardFilterFactory::class)->make(
        // array_merge, not +, because "+" keeps the left operand's key and would
        // silently pin every call to the default period.
        array_merge(['period' => 'today'], $input),
        $this->viewer,
    );

    $this->order = function (array $attributes = []): Order {
        $createdAt = $attributes['created_at'] ?? CarbonImmutable::parse('2026-03-10 08:00:00', 'UTC');

        $order = Order::query()->create(array_merge([
            'store_id' => $this->store->id,
            'number' => 'ORD-'.uniqid(),
            'total_amount' => 100,
            'status_id' => $this->status['delivered'],
            'state_id' => $this->stateAlgiers,
            'delivery_type' => 'home',
        ], $attributes));

        return $order->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])
            ->save() ? $order : $order;
    };

    $this->service = new StoreDashboardAnalyticsService($this->store->id);
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

function makeLocation(string $table, array $attributes): string
{
    $id = (string) Str::ulid();
    DB::table($table)->insert(['id' => $id] + $attributes + [
        'created_at' => now(), 'updated_at' => now(),
    ]);

    return $id;
}

function makeState(string $name, string $code): string
{
    return makeLocation('states', [
        'country_id' => test()->country,
        'state_code' => $code,
        'name' => $name,
    ]);
}

function makeCarrier(string $name): ShippingProvider
{
    return ShippingProvider::query()->create([
        'store_id' => test()->store->id,
        'name' => $name,
        'credentials' => [],
        'is_active' => true,
    ]);
}

function makeMembership(): StoreMembership
{
    return StoreMembership::query()->create([
        'store_id' => test()->store->id,
        'user_id' => test()->user->id,
        'role' => 'STAFF',
        'is_active' => true,
    ]);
}

/** A product with one default variant, returning the variant id. */
function makeProduct(string $name): string
{
    $suffix = str($name)->slug()->value().'-'.uniqid();

    $productId = (string) Str::ulid();
    DB::table('products')->insert([
        'id' => $productId,
        'store_id' => test()->store->id,
        'name' => $name,
        'slug' => $suffix,
        'sku' => strtoupper($suffix),
        'type' => 'simple',
        'price' => 10,
        'is_active' => true,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $variantId = (string) Str::ulid();
    DB::table('product_variants')->insert([
        'id' => $variantId,
        'store_id' => test()->store->id,
        'product_id' => $productId,
        'name' => 'Default',
        'sku' => strtoupper($suffix).'-V',
        'price' => 10,
        'stock' => 100,
        'low_stock_threshold' => 5,
        'is_active' => true,
        'is_default' => true,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    return $variantId;
}

function addItem(Order $order, string $variantId, int $quantity, float $price): void
{
    DB::table('order_items')->insert([
        'id' => (string) Str::ulid(),
        'store_id' => $order->store_id,
        'order_id' => $order->id,
        'product_variant_id' => $variantId,
        'quantity' => $quantity,
        'price' => $price,
        'subtotal' => $quantity * $price,
        'created_at' => now(), 'updated_at' => now(),
    ]);
}

it('builds an unrestricted filter from the factory for a team viewer', function () {
    $filter = ($this->filter)();

    expect($filter)->toBeInstanceOf(DashboardFilter::class)
        ->and($filter->period)->toBe('today')
        ->and($filter->timezone)->toBe('Africa/Algiers')
        ->and($filter->utcOffsetSeconds)->toBe(3600)
        // Local midnight through the end of the local day, handed over in UTC.
        ->and($filter->from->format('Y-m-d H:i:s'))->toBe('2026-03-09 23:00:00')
        ->and($filter->to->format('Y-m-d H:i:s'))->toBe('2026-03-10 22:59:59')
        // No pick means no member restriction at all.
        ->and($filter->memberScopeIds)->toBeNull()
        ->and($filter->memberLocked)->toBeFalse()
        ->and($filter->memberDimension)->toBe('confirmation')
        ->and($filter->carrierId)->toBeNull()
        ->and($filter->storeId)->toBe($this->store->id);
});

it('applies the period to every block', function () {
    ($this->order)();
    ($this->order)([
        'total_amount' => 250,
        'created_at' => CarbonImmutable::parse('2026-03-09 08:00:00', 'UTC'),
    ]);
    ($this->order)([
        'created_at' => CarbonImmutable::parse('2026-02-05 08:00:00', 'UTC'),
    ]);

    $today = ($this->filter)();

    expect($this->service->summary($today)['total_orders'])->toBe(1)
        ->and($this->service->statusBreakdown($today)->sum('count'))->toBe(1)
        ->and(array_sum(dftValues($this->service->trendSeries($today), 'received')))->toBe(1)
        ->and($this->service->ordersByState($today)->sum('count'))->toBe(1)
        ->and($this->service->deliveryTypeBreakdown($today)->sum('count'))->toBe(1);

    // Yesterday sees only the March 9 order.
    expect($this->service->summary(($this->filter)(['period' => 'yesterday']))['total_orders'])->toBe(1);

    // The 7 day window covers March 4 through March 10, so both March orders.
    expect($this->service->summary(($this->filter)(['period' => 'week']))['total_orders'])->toBe(2)
        ->and($this->service->summary(($this->filter)(['period' => 'month']))['total_orders'])->toBe(2);

    // "All" is unbounded.
    expect($this->service->summary(($this->filter)(['period' => 'all']))['total_orders'])->toBe(3);
});

it('never counts another store', function () {
    ($this->order)();
    ($this->order)([
        'store_id' => $this->foreignStore->id,
        'number' => 'ORD-'.uniqid(),
        'status_id' => $this->status['delivered'],
    ]);

    $filter = ($this->filter)();

    expect($this->service->summary($filter)['total_orders'])->toBe(1)
        ->and(array_sum(dftValues($this->service->trendSeries($filter), 'received')))->toBe(1)
        ->and($this->service->statusBreakdown($filter)->sum('count'))->toBe(1)
        ->and($this->service->ordersByState($filter)->sum('count'))->toBe(1)
        ->and($this->service->deliveryTypeBreakdown($filter)->sum('count'))->toBe(1)
        ->and($this->service->pendingConfirmationOrders($filter))->toBeEmpty();
});

it('excludes soft deleted orders and order items', function () {
    $kept = ($this->order)();
    $deleted = ($this->order)();
    $deleted->delete();

    $filter = ($this->filter)();

    expect($this->service->summary($filter)['total_orders'])->toBe(1)
        ->and($this->service->statusBreakdown($filter)->sum('count'))->toBe(1)
        ->and(array_sum(dftValues($this->service->trendSeries($filter), 'received')))->toBe(1)
        ->and($kept->trashed())->toBeFalse()
        ->and($deleted->trashed())->toBeTrue();
});

it('breaks orders down by status, state and delivery type', function () {
    ($this->order)(['state_id' => $this->stateAlgiers, 'delivery_type' => 'home']);
    ($this->order)(['state_id' => $this->stateOran, 'delivery_type' => 'pickup']);
    ($this->order)(['status_id' => $this->status['pending'], 'state_id' => $this->stateOran]);

    $filter = ($this->filter)();

    // The doughnut maps only the statuses that actually have orders, and D2
    // collapses the confirmed group: both delivered orders read as one
    // "confirmed" slice, matching the confirmation-rate KPI beside it.
    $statuses = $this->service->statusBreakdown($filter);
    expect($statuses)->toHaveCount(2)
        ->and($statuses->pluck('key')->sort()->values()->all())->toBe(['confirmed', 'pending'])
        ->and($statuses->firstWhere('key', 'confirmed')->count)->toBe(2)
        ->and($statuses->firstWhere('key', 'confirmed')->label)->not->toBeEmpty()
        // Integer percentages that always add up to the whole chart.
        ->and($statuses->pluck('percent')->sum())->toBe(100);

    $states = $this->service->ordersByState($filter);
    expect($states)->toHaveCount(2)
        ->and($states->pluck('name')->sort()->values()->all())->toBe(['Algiers', 'Oran'])
        ->and($states->firstWhere('name', 'Oran')->count)->toBe(2)
        ->and((float) $states->firstWhere('name', 'Oran')->revenue)->toEqual(200.0);

    $types = $this->service->deliveryTypeBreakdown($filter)->keyBy('delivery_type');
    expect($types)->toHaveCount(2)
        ->and($types['home']->count)->toBe(2)
        ->and($types['pickup']->count)->toBe(1);

    // An empty window empties every breakdown.
    $empty = ($this->filter)(['period' => 'custom', 'dateFrom' => '2026-01-01', 'dateTo' => '2026-01-02']);
    expect($this->service->statusBreakdown($empty))->toBeEmpty()
        ->and($this->service->ordersByState($empty))->toBeEmpty()
        ->and($this->service->deliveryTypeBreakdown($empty))->toBeEmpty()
        ->and($this->service->summary($empty)['total_orders'])->toBe(0);
});

it('leaves orders without a state out of the state breakdown only', function () {
    ($this->order)(['state_id' => $this->stateAlgiers]);
    ($this->order)(['state_id' => null]);

    $filter = ($this->filter)();

    // The state breakdown inner-joins states, so a state-less order cannot be
    // attributed to a state. Every other block still counts it.
    expect($this->service->ordersByState($filter)->sum('count'))->toBe(1)
        ->and($this->service->summary($filter)['total_orders'])->toBe(2)
        ->and($this->service->deliveryTypeBreakdown($filter)->sum('count'))->toBe(2)
        ->and(array_sum(dftValues($this->service->trendSeries($filter), 'received')))->toBe(2);
});

it('filters every block by carrier and discards a carrier from another store', function () {
    ($this->order)(['shipping_provider_id' => $this->carrierA->id]);
    ($this->order)(['shipping_provider_id' => $this->carrierB->id]);

    $forA = ($this->filter)(['carrierId' => $this->carrierA->id]);
    expect($forA->carrierId)->toBe($this->carrierA->id)
        ->and($this->service->summary($forA)['total_orders'])->toBe(1)
        ->and($this->service->statusBreakdown($forA)->sum('count'))->toBe(1)
        ->and(array_sum(dftValues($this->service->trendSeries($forA), 'received')))->toBe(1)
        ->and($this->service->ordersByState($forA)->sum('count'))->toBe(1)
        ->and($this->service->deliveryTypeBreakdown($forA)->sum('count'))->toBe(1);

    // A real carrier id that belongs to another store is dropped, not applied.
    $foreignCarrier = ShippingProvider::query()->create([
        'store_id' => $this->foreignStore->id,
        'name' => 'Foreign Carrier',
        'credentials' => [],
        'is_active' => true,
    ]);

    $discarded = ($this->filter)(['carrierId' => $foreignCarrier->id]);
    expect($discarded->carrierId)->toBeNull()
        ->and($this->service->summary($discarded)['total_orders'])->toBe(2);
});

it('filters the confirmation dimension by the confirmed member', function () {
    $mine = ($this->order)(['confirmed_by_membership_id' => $this->memberA->id]);
    ($this->order)(['confirmed_by_membership_id' => $this->memberB->id]);

    $filter = ($this->filter)(['memberId' => $this->memberA->id, 'memberDimension' => 'confirmation']);

    expect($filter->memberScopeIds)->toBe([$this->memberA->id])
        ->and($this->service->summary($filter)['total_orders'])->toBe(1)
        ->and($this->service->statusBreakdown($filter)->sum('count'))->toBe(1)
        ->and(array_sum(dftValues($this->service->trendSeries($filter), 'received')))->toBe(1)
        ->and($this->service->ordersByState($filter)->sum('count'))->toBe(1)
        ->and($this->service->deliveryTypeBreakdown($filter)->sum('count'))->toBe(1)
        // The other member's order is untouched on disk.
        ->and($mine->fresh()->confirmed_by_membership_id)->toBe($this->memberA->id);
});

it('filters the delivery dimension by the confirmed member', function () {
    $mine = ($this->order)(['confirmed_by_membership_id' => $this->memberA->id]);
    ($this->order)(['confirmed_by_membership_id' => $this->memberB->id]);

    // The delivery pipeline only reads tracked orders (D3), and the member
    // scope stays the confirmed member: the delivery KPI row must agree with
    // the team table, not with who handled the shipment.
    OrderTracking::query()->create([
        'store_id' => $this->store->id,
        'order_id' => $mine->id,
        'shipping_provider_id' => $this->carrierA->id,
        'assigned_to_membership_id' => $this->memberB->id,
    ]);

    $forA = ($this->filter)(['memberId' => $this->memberA->id, 'memberDimension' => 'delivery']);
    expect($forA->memberDimension)->toBe('delivery')
        ->and($forA->memberScopeIds)->toBe([$this->memberA->id])
        ->and($this->service->summary($forA)['total_orders'])->toBe(1)
        ->and(array_sum(dftValues($this->service->trendSeries($forA), 'delivered')))->toBe(1)
        ->and($this->service->statusBreakdown($forA)->sum('count'))->toBe(1)
        // The other member's order is untouched on disk.
        ->and($mine->fresh()->confirmed_by_membership_id)->toBe($this->memberA->id);
});

it('keeps pending confirmations outside the date window but inside carrier and member', function () {
    $oldPending = ($this->order)([
        'status_id' => $this->status['pending'],
        'assigned_to_membership_id' => $this->memberA->id,
        'shipping_provider_id' => $this->carrierA->id,
        'created_at' => CarbonImmutable::parse('2025-11-02 08:00:00', 'UTC'),
    ]);
    $todayPending = ($this->order)([
        'status_id' => $this->status['pending'],
        'assigned_to_membership_id' => $this->memberB->id,
        'shipping_provider_id' => $this->carrierB->id,
    ]);
    // Delivered orders are never pending, whatever their date.
    ($this->order)([
        'shipping_provider_id' => $this->carrierA->id,
        'created_at' => CarbonImmutable::parse('2026-03-10 08:00:00', 'UTC'),
    ]);

    // A "today" window still surfaces the November order: nothing is left waiting.
    $pending = $this->service->pendingConfirmationOrders(($this->filter)());
    expect($pending)->toHaveCount(2)
        ->and($pending->pluck('id')->sort()->values()->all())
        ->toBe(collect([$oldPending->id, $todayPending->id])->sort()->values()->all())
        ->and($pending->pluck('number')->all())->toContain($oldPending->number, $todayPending->number);

    // Carrier still applies even though the date window does not.
    expect($this->service->pendingConfirmationOrders(
        ($this->filter)(['carrierId' => $this->carrierA->id])
    )->pluck('id')->all())->toBe([$oldPending->id]);

    // And so does the confirmation member.
    expect($this->service->pendingConfirmationOrders(
        ($this->filter)(['memberId' => $this->memberB->id, 'memberDimension' => 'confirmation'])
    )->pluck('id')->all())->toBe([$todayPending->id]);
});

it('ranks top selling products inside the scoped window', function () {
    $widget = makeProduct('Widget');
    $gadget = makeProduct('Gadget');

    $today = ($this->order)();
    $lastMonth = ($this->order)([
        'created_at' => CarbonImmutable::parse('2026-02-05 08:00:00', 'UTC'),
    ]);

    addItem($today, $widget, quantity: 5, price: 10);
    addItem($today, $gadget, quantity: 1, price: 100);
    // A much bigger sale, but outside the selected window.
    addItem($lastMonth, $gadget, quantity: 50, price: 100);

    // Before the fix this card ignored the period and always used the app clock's
    // current month, so "Gadget" would have won with 51 units.
    $top = $this->service->topSellingProducts(($this->filter)());

    expect($top)->toHaveCount(2)
        ->and($top->first()->name)->toBe('Widget')
        ->and((int) $top->first()->total_qty)->toBe(5)
        ->and((float) $top->first()->total_revenue)->toEqual(50.0)
        ->and($top->last()->name)->toBe('Gadget')
        ->and((int) $top->last()->total_qty)->toBe(1);

    // Widening the window to "all" brings the bigger sale back.
    $all = $this->service->topSellingProducts(($this->filter)(['period' => 'all']));
    expect($all->first()->name)->toBe('Gadget')
        ->and((int) $all->first()->total_qty)->toBe(51);
});

it('scopes top selling products by carrier and confirmation member', function () {
    $widget = makeProduct('Widget');

    $mine = ($this->order)([
        'confirmed_by_membership_id' => $this->memberA->id,
        'shipping_provider_id' => $this->carrierA->id,
    ]);
    $theirs = ($this->order)([
        'confirmed_by_membership_id' => $this->memberB->id,
        'shipping_provider_id' => $this->carrierB->id,
    ]);

    addItem($mine, $widget, quantity: 3, price: 10);
    addItem($theirs, $widget, quantity: 9, price: 10);

    $unfiltered = $this->service->topSellingProducts(($this->filter)());
    expect((int) $unfiltered->first()->total_qty)->toBe(12);

    $byMember = $this->service->topSellingProducts(
        ($this->filter)(['memberId' => $this->memberA->id, 'memberDimension' => 'confirmation'])
    );
    expect($byMember)->toHaveCount(1)
        ->and((int) $byMember->first()->total_qty)->toBe(3);

    $byCarrier = $this->service->topSellingProducts(($this->filter)(['carrierId' => $this->carrierB->id]));
    expect((int) $byCarrier->first()->total_qty)->toBe(9);

    // A foreign member resolves to "no restriction", because the factory only
    // keeps ids that belong to the store.
    $foreign = ($this->filter)(['memberId' => $this->foreignMember->id, 'memberDimension' => 'confirmation']);
    expect($foreign->memberId)->toBeNull()
        ->and($foreign->memberScopeIds)->toBeNull()
        ->and((int) $this->service->topSellingProducts($foreign)->first()->total_qty)->toBe(12);
});

it('excludes soft deleted order items from the product ranking', function () {
    $widget = makeProduct('Widget');
    $gadget = makeProduct('Gadget');

    $order = ($this->order)();

    addItem($order, $widget, quantity: 2, price: 10);
    DB::table('order_items')->insert([
        'id' => (string) Str::ulid(),
        'store_id' => $order->store_id,
        'order_id' => $order->id,
        'product_variant_id' => $gadget,
        'quantity' => 40,
        'price' => 10,
        'subtotal' => 400,
        'deleted_at' => now(),
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $top = $this->service->topSellingProducts(($this->filter)());

    expect($top)->toHaveCount(1)
        ->and($top->first()->name)->toBe('Widget')
        ->and((int) $top->first()->total_qty)->toBe(2);
});

it('reports store level counters that ignore the filter', function () {
    makeProduct('Widget');
    makeProduct('Gadget');
    DB::table('products')->where('store_id', $this->store->id)->update(['is_active' => false]);

    ($this->order)();

    $summary = $this->service->summary(($this->filter)());

    // Products and members are inventory facts about the store, not period facts.
    expect($summary['total_products'])->toBe(2)
        ->and($summary['active_products'])->toBe(0)
        // Every membership in this fixture belongs to the same user, and the
        // counter counts distinct users rather than memberships.
        ->and($summary['total_members'])->toBe(1);
});

it('counts pending and canceled orders inside the same scope', function () {
    // Stores configure the cancelled state under either spelling, so both rows
    // exist here and both must land in the same counter.
    $cancelled = (string) Str::ulid();
    $canceled = (string) Str::ulid();

    foreach (['cancelled' => $cancelled, 'canceled' => $canceled] as $key => $id) {
        DB::table('statuses')->insert([
            'id' => $id,
            'store_id' => null,
            'type' => 'order',
            'key' => $key,
            'label' => ucfirst($key),
            'is_system' => true,
        ]);
    }

    ($this->order)(['status_id' => $this->status['pending']]);
    ($this->order)(['status_id' => $this->status['pending']]);
    ($this->order)(['status_id' => $cancelled]);
    ($this->order)(['status_id' => $canceled]);
    ($this->order)(['status_id' => $this->status['delivered']]);

    $summary = $this->service->summary(($this->filter)());

    expect($summary['pending_count'])->toBe(2)
        ->and($summary['canceled_count'])->toBe(2)
        // These two are new fields, not a rename of the existing counters.
        ->and($summary['total_orders'])->toBe(5);

    // Scoped exactly like every other block: the same orders fall outside
    // yesterday's window in the store timezone.
    $scoped = $this->service->summary(($this->filter)(['period' => 'yesterday']));

    expect($scoped['pending_count'])->toBe(0)
        ->and($scoped['canceled_count'])->toBe(0);

    // And a store that has not defined a cancelled status at all must not
    // error out, it just has nothing to count.
    expect($this->service->summary(($this->filter)(['period' => 'yesterday']))['canceled_count'])->toBe(0);
});

it('plots one point per bucket for the selected period', function () {
    expect($this->service->trendSeries(($this->filter)())['labels'])->toHaveCount(24)
        ->and($this->service->trendSeries(($this->filter)(['period' => 'week']))['labels'])->toHaveCount(7)
        ->and($this->service->trendSeries(($this->filter)(['period' => 'month']))['labels'])->toHaveCount(10);

    // An empty window still draws the axis, so the chart does not collapse.
    $empty = $this->service->trendSeries(
        ($this->filter)(['period' => 'custom', 'dateFrom' => '2026-01-01', 'dateTo' => '2026-01-02'])
    );
    expect($empty['labels'])->toHaveCount(2)
        ->and(array_sum(dftValues($empty, 'received')))->toBe(0);
});
