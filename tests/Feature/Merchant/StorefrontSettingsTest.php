<?php

use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Support\Storefront\StorefrontSections;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
});

function createStoreMember(string $storeRole): array
{
    $user = roleUser('merchant');

    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Settings Store',
        'slug' => 'settings-store-' . uniqid(),
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

test('owner can save template and theme with normalized contract shape', function () {
    [$user, $store] = createStoreMember('owner');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.storefront-settings')
        ->set('template', 'catalog')
        ->set('primary_color', '#ff0000')
        ->set('secondary_color', '#00ff00')
        ->set('font_family', 'Tajawal')
        ->set('sections', ['hero', 'faq', 'rogue_section'])
        ->set('section_content', [
            'hero' => ['title' => 'My Hero', 'description' => 'Desc', 'button_text' => 'Buy'],
            'unknown_injected' => ['title' => 'nope'],
        ])
        ->call('save')
        ->assertDispatched('swal', type: 'success');

    $theme = $store->fresh()->theme;

    expect($store->fresh()->landing_template->value)->toBe('catalog')
        ->and($theme->primary_color)->toBe('#ff0000')
        ->and($theme->secondary_color)->toBe('#00ff00')
        ->and($theme->font_family)->toBe('Tajawal')
        // rogue section key dropped, known keys preserved
        ->and($theme->homepage_sections)->toBe(['hero', 'faq'])
        // unknown injected content section stripped, full shape guaranteed
        ->and($theme->section_content)->not->toHaveKey('unknown_injected')
        ->and(array_keys($theme->section_content))->toBe(array_keys(StorefrontSections::defaults()))
        ->and($theme->section_content['hero']['title'])->toBe('My Hero')
        ->and($theme->section_content['faq']['items'])->toHaveCount(3);
});

test('invalid color or font is rejected and nothing is persisted', function () {
    [$user, $store] = createStoreMember('owner');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.storefront-settings')
        ->set('primary_color', 'javascript:alert(1)')
        ->call('save')
        ->assertHasErrors(['primary_color']);

    Volt::test('merchant.storefront-settings')
        ->set('font_family', 'Comic Sans MS')
        ->call('save')
        ->assertHasErrors(['font_family']);

    // landing_template column has a DB-level default; assert it was untouched.
    expect($store->fresh()->landing_template->value)->toBe('single_product')
        ->and($store->fresh()->theme)->toBeNull();
});

test('members without store.update permission are blocked from opening the page', function () {
    [$user, $store] = createStoreMember('staff');

    $this->actingAs($user)->withSession(['current_store_id' => $store->id]);

    $this->get(route('merchant.storefront-settings', ['store' => $store->slug]))
        ->assertForbidden();
});

test('owner can open the page and the rebuilt view renders', function () {
    [$user, $store] = createStoreMember('owner');

    $this->actingAs($user)->withSession(['current_store_id' => $store->id]);

    $this->get(route('merchant.storefront-settings', ['store' => $store->slug]))
        ->assertOk()
        ->assertSee(__('merchant_panel.storefront_template'), false);
});

test('mount normalizes legacy or partial theme payloads', function () {
    [$user, $store] = createStoreMember('owner');

    $store->theme()->create([
        'primary_color' => '#123123',
        'homepage_sections' => ['hero', 'stale_key'],
        'section_content' => ['cta' => ['title' => 'Legacy CTA']],
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $component = Volt::test('merchant.storefront-settings');

    expect($component->get('sections'))->toBe(['hero'])
        ->and(array_keys($component->get('section_content')))->toBe(array_keys(StorefrontSections::defaults()))
        ->and($component->get('section_content')['cta']['title'])->toBe('Legacy CTA')
        ->and($component->get('primary_color'))->toBe('#123123');
});
