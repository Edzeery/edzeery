<?php

use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Support\PermissionGroupMeta;
use App\Support\StoreRoles;

use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
});

/**
 * The six permissions introduced by Phase 36.12.0 group zero.
 *
 * Group zero is metadata only: the permissions exist in the enum, the hub and
 * the dependency map, but no application call site checks them yet. That is
 * deliberate — wiring the call sites is §6.1-§6.6 of the plan, later phases.
 */
function groupZeroPermissions(): array
{
    return [
        'order.status.manage.own',
        'order.edit.identity',
        'order.edit.products',
        'order.edit.geography',
        'order.dispatch.rider',
        'order.dispatch.carrier',
    ];
}

function groupZeroStore(User $owner): Store
{
    return Store::create([
        'user_id' => $owner->id,
        'name'    => 'Group Zero',
        'slug'    => 'group-zero-'.uniqid(),
        'status'  => 'active',
    ]);
}

it('exposes exactly 52 store permissions, up from 46', function () {
    $values = StorePermissionEnum::values();

    expect($values)->toHaveCount(52)
        ->and(array_unique($values))->toHaveCount(52);

    foreach (groupZeroPermissions() as $permission) {
        expect($values)->toContain($permission);
    }
});

it('keeps the six new permissions out of the manager and staff templates', function () {
    // OWNER and ADMIN are expected to pick them up through the
    // StorePermissionEnum::values() sweep; the written templates are not.
    $new = groupZeroPermissions();

    foreach ([StoreRoleEnum::MANAGER, StoreRoleEnum::STAFF] as $role) {
        expect(array_intersect(StoreRoles::permissions($role), $new))->toBe([]);
    }

    expect(StoreRoles::permissions(StoreRoleEnum::OWNER))->toHaveCount(52);
});

it('lists the permission as a row of the order hub group', function (string $permission) {
    // Mirrors teams/index.blade.php:240-241, which groups the whole enum by
    // the first dot segment and hands each group to the hub card.
    $grouped = collect(StorePermissionEnum::values())
        ->groupBy(fn (string $p) => explode('.', $p)[0]);

    expect(PermissionGroupMeta::order())->toContain('order')
        ->and($grouped->get('order'))->toContain($permission)
        ->and(StorePermissionEnum::from($permission)->group())->toBe('order');
})->with(groupZeroPermissions());

it('renders the row with a translated label and a non-empty description', function (string $locale, string $permission) {
    // The two cells the row is built from, per teams/index.blade.php:297-298.
    app()->setLocale($locale);

    $label = PermissionGroupMeta::label($permission);
    $description = PermissionGroupMeta::description($permission);

    expect($label)->toBeString()
        ->and(trim($label))->not->toBe('')
        // Neither the raw key nor the enum's title-cased fallback: either would
        // mean the translation is missing rather than merely identical.
        ->and($label)->not->toBe($permission)
        ->and($label)->not->toBe(StorePermissionEnum::from($permission)->label())
        ->and($description)->toBeString()
        ->and(trim((string) $description))->not->toBe('')
        ->and(strlen((string) $description))->toBeGreaterThan(40);
})->with([
    ['ar', 'order.status.manage.own'],
    ['ar', 'order.dispatch.carrier'],
    ['en', 'order.status.manage.own'],
    ['en', 'order.dispatch.carrier'],
    ['fr', 'order.edit.identity'],
    ['es', 'order.edit.geography'],
    ['en', 'order.edit.products'],
    ['en', 'order.dispatch.rider'],
]);

it('resolves a label and description for every new permission in every locale', function (string $locale) {
    app()->setLocale($locale);

    foreach (groupZeroPermissions() as $permission) {
        expect(PermissionGroupMeta::label($permission))->not->toBe($permission)
            ->and(PermissionGroupMeta::description($permission))->not->toBeNull();
    }
})->with(['ar', 'en', 'fr', 'es']);

it('grants the permission to a membership without leaking order.manage', function (string $permission) {
    $owner = roleUser('merchant');
    $store = groupZeroStore($owner);
    $member = User::factory()->create();

    $membership = StoreMembership::create([
        'store_id'   => $store->id,
        'user_id'    => $member->id,
        'invited_by' => $owner->id,
        'is_active'  => true,
        'role'       => StoreRoleEnum::STAFF->value,
    ]);
    $membership->syncPermissions([$permission]);

    $stored = $membership->fresh()->permissions()->pluck('permission')->all();

    expect($stored)->toBe([$permission])
        ->and($stored)->not->toContain(StorePermissionEnum::ORDER_MANAGE->value);

    // The seeder is enum-driven, so the hub's permission record exists too.
    expect(\Spatie\Permission\Models\Permission::where('name', $permission)->exists())->toBeTrue();
})->with(groupZeroPermissions());

it('auto-includes order.view when the row is toggled', function (string $permission) {
    // teams/index.blade.php:325 returns early for COMING_SOON rows, which would
    // make the dependency below unreachable and the row untoggleable. Group zero
    // must stay live even though no call site consumes the permission yet.
    expect(PermissionGroupMeta::isComingSoon($permission))->toBeFalse();

    // teams/index.blade.php:331-340 pushes each dependency, then the permission.
    $granted = [];
    foreach (PermissionGroupMeta::dependencies($permission) as $required) {
        if (! in_array($required, $granted, true)) {
            $granted[] = $required;
        }
    }
    $granted[] = $permission;

    expect(PermissionGroupMeta::dependencies($permission))->toBe(['order.view'])
        ->and($granted)->toContain(StorePermissionEnum::ORDER_VIEW->value)
        ->and($granted)->toContain($permission)
        ->and($granted)->toHaveCount(2);
})->with(groupZeroPermissions());

it('keeps the sibling order labels resolving to their own strings', function () {
    // Regression guard for §2.3: 'dispatch', 'status' and the widened 'edit'
    // array are new group-level keys nested inside the existing 'order' array.
    // A group-level read would flatten these siblings into a sub-array.
    app()->setLocale('en');

    expect(PermissionGroupMeta::label('order.manage'))->toBe('Manage Orders')
        ->and(PermissionGroupMeta::label('order.view'))->toBe('Order View')
        ->and(PermissionGroupMeta::label('order.assign'))->toBe('Assign Orders')
        ->and(PermissionGroupMeta::label('order.confirm'))->toBe('Confirm Order')
        ->and(PermissionGroupMeta::label('order.cancel'))->toBe('Cancel Order')
        ->and(PermissionGroupMeta::label('order.delete'))->toBe('Delete Order')
        ->and(PermissionGroupMeta::label('order.delete.final'))->toBe('Delete Order (Final)')
        ->and(PermissionGroupMeta::label('order.edit.price'))->toBe('Edit Order Product Prices')
        ->and(PermissionGroupMeta::label('order.dispatch_validate'))->toBe('Validate Order Dispatch');
});

it('exposes the new group keys as arrays and no caller reads them', function () {
    // §2.3's load-bearing guarantee. The three keys added to the 'order' array
    // are group-level, so they resolve to arrays rather than strings. Reading
    // one would make PermissionGroupMeta::label() flatten a whole sub-array
    // into a single label, so nothing may reference them directly.
    foreach (['order.edit', 'order.status', 'order.dispatch'] as $groupKey) {
        expect(__("permissions.{$groupKey}"))->toBeArray();
    }

    $sources = collect([
        ...\Illuminate\Support\Facades\File::allFiles(base_path('app')),
        ...\Illuminate\Support\Facades\File::allFiles(base_path('resources')),
    ])->filter(fn ($file) => in_array($file->getExtension(), ['php', 'blade.php'], true));

    expect($sources)->not->toBeEmpty();

    foreach ($sources as $file) {
        $lines = file($file->getPathname(), FILE_IGNORE_NEW_LINES) ?: [];

        foreach ($lines as $number => $line) {
            // Strip comments so documentation cannot satisfy or break the guard.
            $code = trim(preg_replace('~//.*~', '', $line));
            if (str_starts_with($code, '*') || str_starts_with($code, '/*') || str_starts_with($code, '#')) {
                continue;
            }

            foreach (['order.edit', 'order.status', 'order.dispatch'] as $groupKey) {
                expect($code)
                    ->not->toContain("__('permissions.{$groupKey}')")
                    ->not->toContain("__(\"permissions.{$groupKey}\")")
                    ->not->toContain("permissions.{$groupKey}'")
                    ->not->toContain("permissions.{$groupKey}\"");
            }
        }
    }

    // The pre-existing 'order.delete' array shows the reader tolerates a
    // group-level array, so the guard above is about callers, not the reader.
    expect(is_array(__('permissions.order.delete')))->toBeTrue()
        ->and(PermissionGroupMeta::label('order.delete'))->toBe('Delete Order');
});

it('expands the order.manage description in every locale', function (string $locale) {
    app()->setLocale($locale);

    $description = (string) PermissionGroupMeta::description('order.manage');

    expect(trim($description))->not->toBe('')
        // The replaced one-liner was under 80 characters in every locale.
        ->and(strlen($description))->toBeGreaterThan(200);
})->with(['ar', 'en', 'fr', 'es']);
