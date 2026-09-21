<?php

use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Support\PermissionGroupMeta;
use App\Support\StoreRoles;

it('manager template no longer defaults the five phase 36.7 removals', function () {
    $manager = StoreRoles::permissions(StoreRoleEnum::MANAGER);

    expect($manager)
        ->not->toContain(StorePermissionEnum::ORDER_CONFIRM->value)
        ->not->toContain(StorePermissionEnum::CRM_ORDER_TRACKING->value)
        ->not->toContain('crm.orders.confirm')
        ->not->toContain(StorePermissionEnum::PRODUCT_DELETE->value)
        ->not->toContain(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value);

    // Scoped operations and explicit-grant workflows the manager keeps.
    expect($manager)
        ->toContain(StorePermissionEnum::ORDER_MANAGE->value)
        ->toContain(StorePermissionEnum::ORDER_CANCEL->value)
        ->toContain(StorePermissionEnum::ORDER_ASSIGN->value)
        ->toContain(StorePermissionEnum::TEAM_VIEW_OWN->value);
});

it('staff template no longer defaults the three phase 36.7 removals', function () {
    $staff = StoreRoles::permissions(StoreRoleEnum::STAFF);

    expect($staff)
        ->not->toContain(StorePermissionEnum::ORDER_CANCEL->value)
        ->not->toContain(StorePermissionEnum::CRM_ORDER_TRACKING->value)
        ->not->toContain('crm.orders.confirm')
        ->toContain(StorePermissionEnum::ORDER_VIEW->value)
        ->toContain(StorePermissionEnum::ORDER_CONFIRM->value);
});

it('crm.orders.confirm and its enum case no longer exist anywhere in the enum', function () {
    expect(collect(StorePermissionEnum::values()))
        ->not->toContain('crm.orders.confirm');
});

it('resolves every phase 36.7 permission key to a translated string in all locales', function (string $locale) {
    app()->setLocale($locale);

    foreach ([
        'order.assign',
        'order.dispatch_validate',
        'order.delete.final',
        'order.delete',
        'order.edit.price',
        'team.view',
    ] as $permission) {
        expect(PermissionGroupMeta::label($permission))
            ->toBeString()
            ->not->toBe($permission);
    }
})->with(['en', 'ar', 'fr', 'es']);

it('does not resolve order.cancel into the order.delete nested array', function () {
    // Regression guard for the nested `order.delete => ['label', 'final']`
    // restructure: sibling scalar keys must keep resolving to their own strings.
    app()->setLocale('en');

    expect(PermissionGroupMeta::label('order.cancel'))->toBe('Cancel Order')
        ->and(PermissionGroupMeta::label('order.delete'))->toBe('Delete Order')
        ->and(PermissionGroupMeta::label('order.delete.final'))->toBe('Delete Order (Final)')
        ->and(PermissionGroupMeta::label('team.view'))->toBe('View Team');
});