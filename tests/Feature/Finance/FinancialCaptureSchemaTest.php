<?php

use App\Models\Orders\Order;
use App\Models\Orders\OrderStatusHistory;
use App\Models\Orders\OrderTracking;
use App\Models\Status;
use App\Models\Stores\Store;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function fcaStore(): array
{
    $user = roleUser('merchant');

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Capture Store',
        'slug' => 'capture-'.uniqid(),
        'status' => 'active',
    ]);

    return [$user, $store];
}

test('the capture columns and their indexes exist after the 38-C migrations', function () {
    expect(Schema::hasColumn('orders', 'confirmed_at'))->toBeTrue()
        ->and(Schema::hasColumn('orders', 'confirmed_by_membership_id'))->toBeTrue()
        ->and(Schema::hasColumn('orders', 'delivered_at'))->toBeTrue()
        ->and(Schema::hasColumn('orders', 'delivery_evidence_at'))->toBeTrue()
        ->and(Schema::hasColumn('orders', 'returned_at'))->toBeTrue()
        ->and(Schema::hasColumn('orders', 'return_reason_key'))->toBeTrue()
        ->and(Schema::hasIndex('orders', 'orders_store_confirmed_by_at_index'))->toBeTrue()
        ->and(Schema::hasIndex('orders', 'orders_store_delivered_at_index'))->toBeTrue();

    expect(Schema::hasColumn('order_status_histories', 'from_status'))->toBeTrue()
        ->and(Schema::hasColumn('order_status_histories', 'source'))->toBeTrue();

    expect(Schema::hasColumn('order_trackings', 'tracked_by_membership_id'))->toBeTrue()
        ->and(Schema::hasColumn('order_trackings', 'cod_amount'))->toBeTrue();

    expect(Schema::hasColumn('store_settings', 'finance_capture_started_at'))->toBeTrue();

    expect(Schema::hasColumn('statuses', 'stage'))->toBeTrue();
});

test('the migration backfilled the resolver stage for every system order status', function () {
    $expected = [
        'pending' => 'pending',
        'confirmed' => 'confirmed',
        'preparing' => 'confirmed',
        'shipped' => 'in_delivery',
        'in_transit' => 'in_delivery',
        'out_for_delivery' => 'in_delivery',
        'delivered' => 'delivered',
        'returned' => 'returned',
        'cancelled' => 'canceled',
        'canceled' => 'canceled',
        'refunded' => 'confirmed',
    ];

    foreach ($expected as $key => $stage) {
        expect(DB::table('statuses')->where('type', 'order')->where('key', $key)->value('stage'))
            ->toBe($stage, "order status [{$key}] should resolve to stage [{$stage}]");
    }

    // Non-order statuses (tracking / inventory / payment / shipment) landed in
    // the neutral bucket so the column is never NULL in practice.
    expect(DB::table('statuses')->where('type', '!=', 'order')->whereNull('stage')->count())->toBe(0);
});

test('every store settings row carries a finance_capture_started_at (backfill or at creation)', function () {
    [, $store] = fcaStore();
    [, $other] = fcaStore();

    $storeIds = [$store->id, $other->id];

    expect(DB::table('store_settings')->whereIn('store_id', $storeIds)->count())->toBe(2)
        ->and(DB::table('store_settings')->whereIn('store_id', $storeIds)->whereNull('finance_capture_started_at')->count())
        ->toBe(0);
});

test('the capture migrations roll back and forward cleanly preserving pre-existing rows', function () {
    [, $store] = fcaStore();

    $pending = Status::system()->forType('order')->where('key', 'pending')->firstOrFail();

    $order = Order::create([
        'store_id' => $store->id,
        'status_id' => $pending->id,
    ]);

    $history = OrderStatusHistory::create([
        'order_id' => $order->id,
        'status_id' => $pending->id,
    ]);

    $tracking = OrderTracking::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'tracking_status' => 'shipped',
        'shipped_at' => now(),
    ]);

    // Rolling back the additive capture columns must not destroy pre-existing
    // rows. SQLite recreates a table on DROP COLUMN, and rebuilding the orders
    // table fires the FK ON DELETE CASCADE it declares to child tables, so row-
    // preservation is only asserted for the tables whose capture columns are
    // rolled back directly (tracking/history/store settings). The orders/status
    // migrations are asserted for reversibility only.
    expect(Artisan::call('migrate:rollback', ['--step' => 4, '--force' => true]))->toBe(0);

    expect(Schema::hasColumn('order_status_histories', 'source'))->toBeFalse()
        ->and(Schema::hasColumn('order_trackings', 'cod_amount'))->toBeFalse()
        ->and(Schema::hasColumn('store_settings', 'finance_capture_started_at'))->toBeFalse()
        ->and(DB::table('orders')->where('id', $order->id)->count())->toBe(1)
        ->and(DB::table('order_status_histories')->where('id', $history->id)->count())->toBe(1)
        ->and(DB::table('order_trackings')->where('id', $tracking->id)->count())->toBe(1);

    expect(Artisan::call('migrate', ['--force' => true]))->toBe(0);

    // The pre-existing rows survived untouched — no backfill of stamps, and the
    // re-added source/amount columns stay NULL for them.
    $order->refresh();
    expect($order->fresh()->confirmed_at)->toBeNull()
        ->and($order->fresh()->delivered_at)->toBeNull()
        ->and($order->fresh()->returned_at)->toBeNull()
        ->and(DB::table('order_status_histories')->where('id', $history->id)->whereNull('from_status')->whereNull('source')->count())->toBe(1)
        ->and(DB::table('order_trackings')->where('id', $tracking->id)->whereNull('tracked_by_membership_id')->whereNull('cod_amount')->count())->toBe(1);

    // Re-applying the migrations backfilled the bookkeeping columns again.
    expect(DB::table('store_settings')->where('store_id', $store->id)->whereNotNull('finance_capture_started_at')->count())->toBe(1)
        ->and(DB::table('statuses')->where('type', 'order')->whereNull('stage')->count())->toBe(0)
        ->and(Schema::hasColumn('orders', 'confirmed_at'))->toBeTrue();
});
