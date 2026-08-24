<?php

use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
});

function createDeepLinkMember(string $storeRole): array
{
    $user = roleUser('merchant');

    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Deep Link Store',
        'slug' => 'deep-link-store-' . uniqid(),
        'status' => 'active',
    ]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
    ]);

    return [$user, $store];
}

test('deep link without prior session resolves store from slug', function () {
    [$user, $store] = createDeepLinkMember('owner');

    // No withSession(): simulates a fresh browser hitting a bookmarked link.
    $response = $this->actingAs($user)
        ->get(route('merchant.storefront-settings', ['store' => $store->slug]))
        ->assertOk();

    $html = $response->getContent();

    // Root Alpine scope must be present and initialized server-side.
    expect($html)->toContain('activeTab')
        ->and($html)->not->toContain('showPreview');

    // Component state must be read from the x-data ROOT element ($root):
    // $el inside methods resolves to the triggering element, which broke
    // the preview popup (empty URL -> early return).
    expect($html)->toContain('$root.dataset.previewUrl')
        ->and($html)->not->toContain('$el.dataset.previewUrl');

    // No Blade JS-directive may ever leak into the served markup.
    expect($html)->not->toContain('@js(')
        ->and($html)->not->toContain('@json(');

    // Copy button passes its URL through a plain data attribute.
    expect($html)->toContain('data-copy-url=' . '"' . $store->public_url . '"');

    // Regression guard: no Alpine (colon-prefixed) attribute may carry raw
    // Arabic text as an expression — that only happens when a translation
    // is interpolated without quoting.
    expect(preg_match('/\s:[a-z-]+="[^"]*\p{Arabic}[^"]*"/u', $html))->toBe(0)
        // Section editors must expose their label as a static (quoted) attribute.
        ->and(substr_count($html, 'role="region" aria-label='))->toBe(7);
});

test('deep link still enforces permission gates for restricted members', function () {
    [$user, $store] = createDeepLinkMember('staff');

    $this->actingAs($user)
        ->get(route('merchant.storefront-settings', ['store' => $store->slug]))
        ->assertForbidden();
});

test('deep link is rejected for authenticated users without membership', function () {
    [, $store] = createDeepLinkMember('owner');

    $outsider = roleUser('merchant');

    $this->actingAs($outsider)
        ->get(route('merchant.storefront-settings', ['store' => $store->slug]))
        ->assertForbidden();
});

test('deep link to unknown slug returns 404', function () {
    [$user,] = createDeepLinkMember('owner');

    $this->actingAs($user)
        ->get(route('merchant.storefront-settings', ['store' => 'no-such-store-slug']))
        ->assertNotFound();
});

test('deep link to soft-deleted store returns 404', function () {
    [$user, $store] = createDeepLinkMember('owner');

    $store->delete();

    $this->actingAs($user)
        ->get(route('merchant.storefront-settings', ['store' => $store->slug]))
        ->assertNotFound();
});

test('resolved deep link persists store in session for subsequent livewire calls', function () {
    [$user, $store] = createDeepLinkMember('owner');

    $this->actingAs($user)
        ->get(route('merchant.storefront-settings', ['store' => $store->slug]))
        ->assertOk();

    expect(session('current_store_id'))->toBe($store->id);
});
