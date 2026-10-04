<?php

use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\OrderStatusHistory;
use App\Models\Stores\Team\StoreMembership;
use App\Support\StoreOrderPermissions;
use Database\Seeders\DemoStoreSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Database\Seeders\SystemStatusesSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(SystemStatusesSeeder::class);
    $this->seed(DemoStoreSeeder::class);
});

$sets = [
    'demo.confirmer@edzeery.com' => ['order.view', 'order.confirm', 'returns.verify.barcode', 'returns.process', 'stats.confirmation', 'inventory.view', 'order.status.manage.own'],
    'demo.tracker@edzeery.com' => ['order.view', 'crm.orders.track', 'delivery.riders.view', 'stats.delivery', 'stats.top.kpis', 'inventory.view', 'order.status.manage.own', 'order.dispatch.carrier', 'order.dispatch.rider', 'order.dispatch_validate'],
    'demo.dual@edzeery.com' => ['order.view', 'order.confirm', 'crm.orders.track', 'returns.verify.barcode', 'stats.confirmation', 'stats.delivery', 'inventory.view', 'order.status.manage.own', 'order.dispatch.carrier'],
];
function demoMembership(string $email): StoreMembership
{
    return StoreMembership::whereHas('user', fn ($q) => $q->where('email', $email))->firstOrFail();
}
it('stores exactly the Phase 36 grant sets, never ORDER_MANAGE', function () use ($sets) {
    foreach ($sets as $email => $expected) {
        $stored = demoMembership($email)->permissions()->pluck('permission')->sort()->values()->all();
        expect($stored)->toBe(collect($expected)->sort()->values()->all())
            ->and(demoMembership($email)->hasPermission('order.manage'))->toBeFalse();
    }
});

it('keeps demo.confirmer without the tracking capability', function () {
    expect(demoMembership('demo.confirmer@edzeery.com')->hasPermission(StorePermissionEnum::CRM_ORDER_TRACKING))->toBeFalse();
});

it('keeps every seeded history row within its author grants', function () use ($sets) {
    OrderStatusHistory::with(['status', 'changedBy.user'])->get()->each(function (OrderStatusHistory $h) use ($sets) {
        $membership = $h->changedBy;
        if (! $membership || ! in_array($membership->user->email, array_keys($sets), true)) {
            return;
        }
        expect($membership->hasPermission(StoreOrderPermissions::forStatus($h->status->key))
            || $membership->hasPermission('order.status.manage.own'))->toBeTrue();
    });
});

it('authors no status history demo.staff could not move to', function () {
    $staff = demoMembership('demo.staff@edzeery.com');
    $rolePerms = \App\Support\StoreRoles::permissions(\App\Enums\Store\StoreRoleEnum::STAFF);
    $rows = OrderStatusHistory::with('status')->where('changed_by_membership_id', $staff->id)->get();
    expect($rows->count())->toBe(0)
        ->and($rows->every(fn ($h) => in_array(StoreOrderPermissions::forStatus($h->status->key), $rolePerms, true)))->toBeTrue();
});