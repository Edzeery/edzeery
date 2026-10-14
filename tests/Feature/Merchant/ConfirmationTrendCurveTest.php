<?php

use App\Enums\Store\StoreRoleEnum;
use App\Models\Orders\Order;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Support\StoreRoles;
use Carbon\CarbonImmutable;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Database\Seeders\SystemStatusesSeeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

/*
|--------------------------------------------------------------------------
| PHASE 37-N4 - the confirmation trend is a curve, never a bar
|--------------------------------------------------------------------------
|
| The confirmation trend draws one smooth monotone curve per group plus a
| browser-derived total band, and an all-zero confirmation window falls back
| to the empty panel. PHASE 37-N9 gives the delivery view the same smooth
| curves (delivered / returned plus the revenue line) while the confirmation
| legend keeps its per-curve toggle, so these tests pin both behaviours.
|
*/

beforeEach(function () {
    // PHASE 37-N6: freeze mid-day so the "today"/trend windows are stable.
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-10 12:00:00', 'UTC'));

    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(SystemStatusesSeeder::class);
    $this->seed(PlansSeeder::class);
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

function ctcOwnerStore(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Curve Store',
        'slug' => 'ctc-'.uniqid(),
        'status' => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => StoreRoleEnum::OWNER->value,
    ]);

    $membership->syncPermissions(StoreRoles::permissions(StoreRoleEnum::OWNER));

    return [$user, $store, $membership];
}

function ctcStatusId(string $key): string
{
    return (string) DB::table('statuses')->where('type', 'order')->where('key', $key)->value('id');
}

function ctcOrder(Store $store, string $membershipId, string $statusKey, string $number): Order
{
    return Order::create([
        'store_id' => $store->id,
        'number' => $number,
        'total_amount' => 500,
        'status_id' => ctcStatusId($statusKey),
        'delivery_type' => 'home',
        'assigned_to_membership_id' => $membershipId,
    ]);
}

test('the confirmation trend is monotone curves with a derived total band', function () {
    $source = file_get_contents(
        resource_path('views/livewire/merchant/dashboard/partials/charts.blade.php')
    );

    expect($source)
        ->toContain("cubicInterpolationMode: 'monotone'")
        ->toContain('_isTotal: true')
        ->toContain('trendTooltip.filter')
        ->toContain('window.__dashCharts.toggleTrend')
        ->toContain("const isConfirmation = data.trendView !== 'delivery'")
        // The confirmation legend toggles its own curve; the delivery branch
        // keeps its per-point nearest hit.
        ->toContain("? { mode: 'index', intersect: false }")
        ->toContain(": { mode: 'nearest', intersect: true }")
        ->not->toContain('stackedBars');
});

test('an all-zero confirmation window shows the empty panel while delivery keeps its axis', function () {
    [$user, $store] = ctcOwnerStore();

    $confirmation = $this->actingAs($user)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug]))
        ->assertOk()
        ->getContent();

    expect(str_contains($confirmation, 'id="salesChart"'))->toBeFalse();

    $delivery = $this->actingAs($user)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug, 'md' => 'delivery']))
        ->assertOk()
        ->getContent();

    expect(str_contains($delivery, 'id="salesChart"'))->toBeTrue();
});

test('the confirmation trend draws a canvas once a group carries a value', function () {
    [$user, $store, $membership] = ctcOwnerStore();
    ctcOrder($store, $membership->id, 'confirmed', 'CTC-1');

    $html = $this->actingAs($user)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug]))
        ->assertOk()
        ->getContent();

    expect(str_contains($html, 'id="salesChart"'))->toBeTrue();
});
