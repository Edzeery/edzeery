<?php

use App\Models\Stores\Store;
use Database\Seeders\DemoStoreSeeder;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Database\Seeders\SystemStatusesSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| PHASE 37-K.3 - demo store seeding keeps working under the reserved-slug
|--------------------------------------------------------------------------
|
| The model-level reserved-slug guard must not break the platform's own demo
| store, which legitimately lives at the reserved "demo" slug via the single
| greppable Store::withReservedSlug() exemption. These tests run the real
| DemoStoreSeeder against a fresh database (the exact production path).
|
*/

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(SystemStatusesSeeder::class);
    $this->seed(PlansSeeder::class);
});

test('a fresh database can seed the platform demo store at the reserved demo slug', function () {
    $this->seed(DemoStoreSeeder::class);

    expect(Store::where('slug', 'demo')->count())->toBe(1);
    expect(Store::where('slug', 'demo')->value('name'))->toBe('Edzeery Demo Store');
});

test('re-running the demo store seeder is idempotent and keeps the reserved demo slug', function () {
    $this->seed(DemoStoreSeeder::class);
    $this->seed(DemoStoreSeeder::class);

    expect(Store::where('slug', 'demo')->count())->toBe(1);
    expect(Store::where('slug', 'demo')->exists())->toBeTrue();
});

test('demo trackings assign only crm-holding agents with auto assignment semantics', function () {
    $this->seed(DemoStoreSeeder::class);

    $store = Store::where('slug', 'demo')->sole();
    $trackings = \App\Models\Orders\OrderTracking::where('store_id', $store->id)
        ->whereNotNull('assigned_to_membership_id')
        ->get();

    expect($trackings)->not->toBeEmpty();

    $assigneeIds = [];

    foreach ($trackings as $tracking) {
        expect($tracking->assignedTo?->can(\App\Enums\Store\StorePermissionEnum::CRM_ORDER_TRACKING))->toBeTrue()
            ->and($tracking->assignment_method)->toBe('auto')
            ->and($tracking->over_capacity)->toBeFalse()
            ->and($tracking->assigned_by_membership_id)->toBeNull();

        $assigneeIds[] = $tracking->assigned_to_membership_id;
    }

    // Both the pure tracker and the dual-role member stay exercised.
    expect(array_unique($assigneeIds))->toHaveCount(2);
});

test('confirmed demo orders carry the credit key and unconfirmed ones stay unattributed', function () {
    $this->seed(DemoStoreSeeder::class);

    $store = Store::where('slug', 'demo')->sole();

    $orderByNumber = fn (string $number) => \App\Models\Orders\Order::withoutGlobalScopes()
        ->where('store_id', $store->id)
        ->where('number', $number)
        ->sole();

    // PHASE 35.4 — credit is keyed on orders.confirmed_by_membership_id, so a
    // confirmed order must name its confirming member (and the 38-C event time).
    $confirmed = $orderByNumber('21006');
    expect($confirmed->confirmed_by_membership_id)->not->toBeNull()
        ->and($confirmed->confirmed_at)->not->toBeNull()
        ->and(
            \App\Models\Stores\Team\StoreMembership::where('store_id', $store->id)
                ->whereKey($confirmed->confirmed_by_membership_id)
                ->exists()
        )->toBeTrue();

    // The confirmer is read from the order's own "confirmed" transition, not
    // from the assignment — 21008 was confirmed by the dual member.
    $dual = \App\Models\Stores\Team\StoreMembership::where('store_id', $store->id)
        ->whereHas('user', fn ($q) => $q->where('email', 'demo.dual@edzeery.com'))
        ->sole();
    expect($orderByNumber('21008')->confirmed_by_membership_id)->toBe($dual->id);

    // Nothing was confirmed yet, so the credit stays unattributed («بلا رصيد»).
    $pending = $orderByNumber('21001');
    expect($pending->confirmed_by_membership_id)->toBeNull()
        ->and($pending->confirmed_at)->toBeNull();
});

test('demo order assignments only reference confirmation-role members', function () {
    $this->seed(DemoStoreSeeder::class);

    $store = Store::where('slug', 'demo')->sole();

    $orders = \App\Models\Orders\Order::withoutGlobalScopes()
        ->where('store_id', $store->id)
        ->whereNotNull('assigned_to_membership_id')
        ->with('assignedMembership')
        ->get();

    expect($orders)->not->toBeEmpty();

    foreach ($orders as $order) {
        // orders.assigned_to_membership_id is the confirmation-scope key, so
        // the assignee must hold ORDER_CONFIRM — never a tracking-only agent
        // (tracker) leaking into the confirmation cohort / dashboard work tab.
        expect($order->assignedMembership?->can(\App\Enums\Store\StorePermissionEnum::ORDER_CONFIRM))
            ->toBeTrue();
    }
});
