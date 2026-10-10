<?php

use App\Domains\Shipping\Models\ShippingProvider;
use App\Models\Orders\Order;
use App\Models\Orders\OrderStatusHistory;
use App\Models\Orders\OrderTracking;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

/* =========================
 | Helpers (fch prefix)
 ========================= */

function fchStore(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate('owner', 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Health Store',
        'slug' => 'health-'.uniqid(),
        'status' => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => 'owner',
    ]);

    // Capture starts a month ago so freshly created records are in scope.
    $store->settings()->update(['finance_capture_started_at' => now()->subMonth()]);

    return [$user, $store, $membership];
}

function fchStatus(string $statusKey): Status
{
    return Status::system()->forType('order')->where('key', $statusKey)->firstOrFail();
}

function fchOrder(Store $store, string $statusKey): Order
{
    return Order::create([
        'store_id' => $store->id,
        'status_id' => fchStatus($statusKey)->id,
        'total_amount' => 500,
        'shipping_cost' => 50,
        'payment_method' => 'cod',
    ]);
}

function fchHistory(Order $order, string $statusKey, ?string $source = null, ?string $fromStatus = null, ?string $actorId = null): OrderStatusHistory
{
    return OrderStatusHistory::create([
        'order_id' => $order->id,
        'status_id' => fchStatus($statusKey)->id,
        'source' => $source,
        'from_status' => $fromStatus,
        'changed_by_membership_id' => $actorId,
    ]);
}

function fchCustomStatus(Store $store, string $key, string $label = 'Custom'): Status
{
    return Status::create([
        'store_id' => $store->id,
        'type' => 'order',
        'key' => $key,
        'label' => $label,
        'is_system' => false,
        'sort_order' => 99,
    ]);
}

function fchProvider(Store $store): ShippingProvider
{
    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'Health Carrier',
        'code' => 'health-local',
        'credentials' => [],
        'is_active' => true,
    ]);
}

function fchTracking(Order $order, ?string $providerId = null, ?string $actorId = null): OrderTracking
{
    return OrderTracking::create([
        'store_id' => $order->store_id,
        'order_id' => $order->id,
        'shipping_provider_id' => $providerId,
        'tracking_status' => 'shipped',
        'shipped_at' => now(),
        'tracked_by_membership_id' => $actorId,
    ]);
}

/* =========================
 | Baseline
 ========================= */

test('a store with no violations exits zero and reports its name', function () {
    [, $store] = fchStore();
    fchOrder($store, 'pending');

    $this->artisan('finance:capture-health', ['--store' => $store->id])
        ->expectsOutputToContain($store->name)
        ->expectsOutputToContain('Capture health: all scanned store(s) are healthy.')
        ->assertExitCode(0);
});

test('an unknown --store selector exits non-zero', function () {
    $this->artisan('finance:capture-health', ['--store' => 'missing-store'])
        ->assertExitCode(1);
});

/* =========================
 | Metric 1 — confirmed-or-later without confirmed_at
 ========================= */

test('metric 1 — a confirmed-or-later order with no confirmed_at fails the check', function () {
    [, $store] = fchStore();
    fchOrder($store, 'confirmed');

    $this->artisan('finance:capture-health', ['--store' => $store->id])
        ->expectsOutputToContain('1. Confirmed-or-later without confirmed_at')
        ->assertExitCode(1);
});

/* =========================
 | Metric 2 — unattributed confirmations by history source
 ========================= */

test('metric 2 — manual/bulk confirmations with a NULL actor fail; other sources are reported only', function () {
    [, $store] = fchStore();

    fchHistory(fchOrder($store, 'confirmed'), 'confirmed', 'manual', 'pending');
    fchHistory(fchOrder($store, 'confirmed'), 'confirmed', 'bulk', 'pending');
    fchHistory(fchOrder($store, 'confirmed'), 'confirmed', 'storefront', 'pending');

    $this->artisan('finance:capture-health', ['--store' => $store->id])
        ->expectsOutputToContain('manual')
        ->expectsOutputToContain('bulk')
        ->expectsOutputToContain('storefront')
        ->assertExitCode(1);
});

/* =========================
 | Metric 3 / 3b — delivered stamps
 ========================= */

test('metric 3 — a delivered order with no delivered_at fails; missing evidence is informational', function () {
    [, $store, $membership] = fchStore();

    // Violation: delivered status, no delivered_at stamp (metric 1 excluded by stamping confirmation).
    $missing = fchOrder($store, 'delivered');
    $missing->update([
        'confirmed_at' => now(),
        'confirmed_by_membership_id' => $membership->id,
    ]);

    // Informational: delivered with delivered_at but no carrier evidence.
    $manual = fchOrder($store, 'delivered');
    $manual->update([
        'confirmed_at' => now(),
        'confirmed_by_membership_id' => $membership->id,
        'delivered_at' => now(),
    ]);
    fchHistory($manual, 'delivered', 'manual', 'shipped');

    $this->artisan('finance:capture-health', ['--store' => $store->id])
        ->expectsOutputToContain('3b. Delivered without delivery evidence')
        ->expectsOutputToContain('manual')
        ->expectsOutputToContain('VIOLATION')
        ->assertExitCode(1);
});

/* =========================
 | Metric 4 — returned stamp
 ========================= */

test('metric 4 — a returned order with no returned_at fails the check', function () {
    [, $store, $membership] = fchStore();

    $order = fchOrder($store, 'returned');
    $order->update([
        'confirmed_at' => now(),
        'confirmed_by_membership_id' => $membership->id,
    ]);

    $this->artisan('finance:capture-health', ['--store' => $store->id])
        ->expectsOutputToContain('4. Returned without returned_at')
        ->assertExitCode(1);
});

/* =========================
 | Metric 5 — history provenance
 ========================= */

test('metric 5 — a legitimate creation row (NULL from_status, source set) is not a violation', function () {
    [, $store] = fchStore();

    $order = fchOrder($store, 'pending');
    fchHistory($order, 'pending', 'storefront', null);

    $this->artisan('finance:capture-health', ['--store' => $store->id])
        ->assertExitCode(0);
});

test('metric 5 — a NULL source or a NULL from_status on a non-creation row fails the check', function () {
    [, $store] = fchStore();

    // Broken: second transition with NULL from_status (creation row is exempt).
    $lostProvenance = fchOrder($store, 'pending');
    fchHistory($lostProvenance, 'pending', 'storefront', null);
    fchHistory($lostProvenance, 'confirmed', 'manual', null);

    // Broken: a NULL source is always a violation, even on the creation row.
    $nullSource = fchOrder($store, 'pending');
    fchHistory($nullSource, 'pending', null, null);

    $this->artisan('finance:capture-health', ['--store' => $store->id])
        ->expectsOutputToContain('5. Histories with NULL source / from_status')
        ->assertExitCode(1);
});

/* =========================
 | Metric 6 — tracking creator (informational)
 ========================= */

test('metric 6 — trackings without a creator are reported by path and never fail', function () {
    [, $store, $membership] = fchStore();

    $viaOrder = fchOrder($store, 'shipped');
    $viaOrder->update([
        'confirmed_at' => now(),
        'confirmed_by_membership_id' => $membership->id,
    ]);
    fchHistory($viaOrder, 'shipped', 'manual', 'confirmed');
    fchTracking($viaOrder);

    $direct = fchOrder($store, 'shipped');
    $direct->update([
        'confirmed_at' => now(),
        'confirmed_by_membership_id' => $membership->id,
    ]);
    fchTracking($direct, fchProvider($store)->id);

    $this->artisan('finance:capture-health', ['--store' => $store->id])
        ->expectsOutputToContain('order:transit:manual')
        ->expectsOutputToContain('carrier:direct')
        ->assertExitCode(0);
});

/* =========================
 | Metric 7 — “other”-stage statuses in use (informational)
 ========================= */

test('metric 7 — custom statuses used by orders are listed and never fail', function () {
    [, $store] = fchStore();

    $custom = fchCustomStatus($store, 'awaiting_part', 'Awaiting Part');
    Order::create([
        'store_id' => $store->id,
        'status_id' => $custom->id,
        'total_amount' => 300,
        'payment_method' => 'cod',
    ]);

    $this->artisan('finance:capture-health', ['--store' => $store->id])
        ->expectsOutputToContain('awaiting_part')
        ->assertExitCode(0);
});

/* =========================
 | Metric 8 — system statuses without a stage
 ========================= */

test('metric 8 — a system order status whose stored stage drifts from the resolver fails', function () {
    [, $store] = fchStore();

    // The stage column is NOT NULL, so "lacking a stage" shows up as a drift:
    // a system ORDER status parked on the fallback bucket while the resolver
    // (OrderStatusStage::forKey) says it should be a real lifecycle stage.
    Status::create([
        'store_id' => $store->id,
        'type' => 'order',
        'key' => 'delivered',
        'label' => 'Delivered (store copy)',
        'is_system' => true,
        'stage' => 'other',
    ]);

    $this->artisan('finance:capture-health', ['--store' => $store->id])
        ->expectsOutputToContain('8. System statuses without stage')
        ->assertExitCode(1);
});

/* =========================
 | --since override
 ========================= */

test('--since overrides the store capture start and excludes earlier records', function () {
    [, $store] = fchStore();
    fchOrder($store, 'confirmed');

    $this->artisan('finance:capture-health', [
        '--store' => $store->id,
        '--since' => Carbon::now()->addDay()->toDateTimeString(),
    ])->assertExitCode(0);
});
