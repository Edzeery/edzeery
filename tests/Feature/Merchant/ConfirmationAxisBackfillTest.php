<?php

use App\Models\Orders\Order;
use App\Models\Stores\Store;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Database\Seeders\SystemStatusesSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * PHASE 37-N1 (D1): the backfill stamps the confirmation axis for the rows
 * that predate the column — frozen at confirmed_at when the order was
 * confirmed, otherwise the most recent status change, otherwise created_at.
 */
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(SystemStatusesSeeder::class);
    $this->seed(PlansSeeder::class);
});

function cabStore(): Store
{
    $user = roleUser('merchant');

    return Store::create([
        'user_id' => $user->id,
        'name' => 'Axis Backfill Store',
        'slug' => 'axis-backfill-'.uniqid(),
        'status' => 'active',
    ]);
}

function cabStatusId(string $key): string
{
    return DB::table('statuses')
        ->where('type', 'order')
        ->where('key', $key)
        ->value('id');
}

function cabOrder(Store $store, string $statusKey, Carbon $createdAt, string $number): Order
{
    $order = Order::create([
        'store_id' => $store->id,
        'number' => $number,
        'total_amount' => 500,
        'status_id' => cabStatusId($statusKey),
        'delivery_type' => 'home',
    ]);

    $order->forceFill([
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
        'confirmation_status_changed_at' => null,
    ])->save();

    return $order->fresh();
}

function cabHistory(Order $order, string $statusKey, Carbon $at): void
{
    DB::table('order_status_histories')->insert([
        'id' => (string) Str::ulid(),
        'order_id' => $order->id,
        'status_id' => cabStatusId($statusKey),
        'changed_by_membership_id' => null,
        'reason' => null,
        'created_at' => $at,
        'updated_at' => $at,
    ]);
}

function cabRunBackfill(): void
{
    (require database_path('migrations/2026_10_13_000002_backfill_confirmation_status_changed_at_on_orders.php'))->up();
}

it('freezes a confirmed order at confirmed_at and moves-on orders at their last change', function () {
    $store = cabStore();

    $base = Carbon::parse('2026-09-01 08:00:00');

    $confirmed = cabOrder($store, 'preparing', $base, 'CAB-1');
    $confirmed->forceFill(['confirmed_at' => $base->copy()->addHours(10)])->save();
    cabHistory($confirmed, 'confirmed', $base->copy()->addHours(10));
    cabHistory($confirmed, 'preparing', $base->copy()->addHours(30));

    $moved = cabOrder($store, 'no_answer_2', $base, 'CAB-2');
    cabHistory($moved, 'no_answer_1', $base->copy()->addHours(5));
    cabHistory($moved, 'no_answer_2', $base->copy()->addHours(12));

    $untouched = cabOrder($store, 'pending', $base, 'CAB-3');

    cabRunBackfill();

    expect($confirmed->fresh()->confirmation_status_changed_at->format('Y-m-d H:i:s'))
        ->toBe('2026-09-01 18:00:00')
        // The preparing history at +30h never restates it.
        ->and($moved->fresh()->confirmation_status_changed_at->format('Y-m-d H:i:s'))
        ->toBe('2026-09-01 20:00:00')
        ->and($untouched->fresh()->confirmation_status_changed_at->format('Y-m-d H:i:s'))
        ->toBe('2026-09-01 08:00:00');
});

it('down nulls the axis so the migration round-trips', function () {
    $store = cabStore();
    cabOrder($store, 'pending', Carbon::parse('2026-09-01 08:00:00'), 'CAB-4');

    $migration = require database_path('migrations/2026_10_13_000002_backfill_confirmation_status_changed_at_on_orders.php');
    $migration->up();
    $migration->down();

    expect(Order::withoutGlobalScopes()->where('store_id', $store->id)
        ->whereNull('confirmation_status_changed_at')->count())->toBe(1);
});
