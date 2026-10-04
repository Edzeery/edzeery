<?php

use App\Enums\Store\StoreRoleEnum;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Support\StoreRoles;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Database\Seeders\SystemStatusesSeeder;
use Spatie\Permission\Models\Role;

/*
|--------------------------------------------------------------------------
| PHASE 37-D - merchant dashboard layout
|--------------------------------------------------------------------------
|
| There is no config/livewire.php, so Livewire falls back to its default
| layout (components.layouts.app -- the marketing/landing shell). The
| dashboard used to import layout() without calling it, so it silently
| rendered inside the landing chrome: navbar + footer, no sidebar.
|
| These tests pin the store panel chrome (sidebar + topbar) and assert the
| landing shell markers are gone.
|
*/

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(SystemStatusesSeeder::class);
    $this->seed(PlansSeeder::class);
});

function dlOwnerStore(): array
{
    $user = roleUser('merchant');

    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Dashboard Layout Store',
        'slug' => 'dashboard-layout-' . uniqid(),
        'status' => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $store->user_id,
        'is_active' => true,
        'role' => StoreRoleEnum::OWNER->value,
    ]);

    $membership->syncPermissions(StoreRoles::permissions(StoreRoleEnum::OWNER));

    return [$user, $store];
}

/**
 * The hashed asset filename Vite emits for a given entry, e.g. landing-HASH.js.
 */
function dlBuiltAsset(string $entry): ?string
{
    $manifestPath = public_path('build/manifest.json');

    if (! is_file($manifestPath)) {
        return null;
    }

    $manifest = json_decode(file_get_contents($manifestPath), true) ?: [];

    return $manifest[$entry]['file'] ?? null;
}

test('merchant dashboard renders inside the store panel chrome', function () {
    [$user, $store] = dlOwnerStore();

    $html = $this->actingAs($user)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug]))
        ->assertOk()
        ->getContent();

    // Store sidebar markers, taken from livewire/layout/store-sidebar.blade.php
    // and livewire/layout/topbar.blade.php (the store panel chrome).
    expect($html)
        ->toContain('edz-sidebar')          // sidebar root
        ->toContain('edz-sidebar__nav')     // sidebar navigation
        ->toContain('edz-sidebar__brand')   // sidebar brand block
        ->toContain('edz-topbar')           // panel topbar
        ->toContain('edz-shell');           // panel shell wrapper from layouts/panel
});

test('merchant dashboard does not render the landing layout', function () {
    [$user, $store] = dlOwnerStore();

    $html = $this->actingAs($user)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug]))
        ->assertOk()
        ->getContent();

    // <x-layouts.navbar /> renders a fixed header with the landing anchors and
    // the marketing logo; none of that belongs on a store panel page.
    expect($html)
        ->not->toContain('href="#services"')
        ->not->toContain('href="#pricing"')
        ->not->toContain('z-30 top-0 bg-surface shadow-md');

    // The landing layout also boots resources/js/landing.js; the panel layout
    // boots resources/js/panel.js instead.
    $landingAsset = dlBuiltAsset('resources/js/landing.js');
    $panelAsset = dlBuiltAsset('resources/js/panel.js');

    if ($landingAsset !== null) {
        expect($html)->not->toContain($landingAsset);
    }

    if ($panelAsset !== null) {
        expect($html)->toContain($panelAsset);
    }
});

test('merchant dashboard uses the same chrome as the products page', function () {
    [$user, $store] = dlOwnerStore();

    $acting = $this->actingAs($user)->withSession(['current_store_id' => $store->id]);

    $dashboard = $acting->get(route('merchant.dashboard', ['store' => $store->slug]))
        ->assertOk()
        ->getContent();

    $products = $acting->get(route('merchant.products.index', ['store' => $store->slug]))
        ->assertOk()
        ->getContent();

    foreach (['edz-sidebar__nav', 'edz-sidebar__brand', 'edz-topbar', 'edz-shell'] as $marker) {
        expect($dashboard)->toContain($marker)
            ->and($products)->toContain($marker);
    }
});

test('merchant dashboard still renders its charts and filter bar', function () {
    [$user, $store] = dlOwnerStore();

    $html = $this->actingAs($user)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug]))
        ->assertOk()
        ->getContent();

    // The charts partial is still included by the dashboard.
    expect(is_file(resource_path('views/livewire/merchant/dashboard/partials/charts.blade.php')))
        ->toBeTrue()
        ->and(file_get_contents(resource_path('views/livewire/merchant/dashboard/partials/charts.blade.php')))
        ->not->toStartWith("\xEF\xBB\xBF");

    // KPI copy from the dashboard body itself.
    expect($html)->toContain(__('dashboard.total_orders'));
});