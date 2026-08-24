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

    $response = $this->get(route('merchant.storefront-settings', ['store' => $store->slug]))
        ->assertOk()
        ->assertSee(__('merchant_panel.storefront_template'), false);

    if (getenv('DUMP_SETTINGS_HTML')) {
        file_put_contents(storage_path('app/debug-settings.html'), $response->getContent());
    }
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

test('icon picker whitelist only contains glyphs that exist in x-edz.icon map', function () {
    $source = file_get_contents(__DIR__ . '/../../../resources/views/components/edz/icon.blade.php');

    preg_match_all("/'([a-z0-9-]+)'\s*=>\s*(?:null|'<)/", $source, $matches);

    $defined = array_unique($matches[1]);

    expect(StorefrontSections::ICONS)->not->toBeEmpty()
        ->and(array_diff(StorefrontSections::ICONS, $defined))->toBe([], 'Every picker icon must exist in the x-edz.icon component map.');
});

test('invalid social proof icon is rejected and nothing persists', function () {
    [$user, $store] = createStoreMember('owner');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $content = StorefrontSections::normalize(null);
    $content['social_proof']['items'][0]['icon'] = 'rocket-launch';

    Volt::test('merchant.storefront-settings')
        ->set('section_content', $content)
        ->call('save')
        ->assertHasErrors(['section_content.social_proof.items.0.icon']);

    expect($store->fresh()->theme)->toBeNull();
});

test('valid social proof icon choice persists through the contract', function () {
    [$user, $store] = createStoreMember('owner');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $content = StorefrontSections::normalize(null);
    $content['social_proof']['items'][2]['icon'] = 'star';
    $content['social_proof']['title'] = 'Why us';

    Volt::test('merchant.storefront-settings')
        ->set('section_content', $content)
        ->call('save')
        ->assertDispatched('swal', type: 'success');

    $theme = $store->fresh()->theme;

    expect($theme->section_content['social_proof']['items'][2]['icon'])->toBe('star')
        ->and($theme->section_content['social_proof']['title'])->toBe('Why us');
});

test('resetSection restores a single section to translated defaults', function () {
    [$user, $store] = createStoreMember('owner');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $content = StorefrontSections::normalize(null);
    $content['social_proof']['title'] = 'Custom Title';
    $content['faq']['title'] = 'Custom FAQ';

    $component = Volt::test('merchant.storefront-settings')
        ->set('section_content', $content)
        ->call('resetSection', 'social_proof');

    expect($component->get('section_content')['social_proof']['title'])->toBe(__('storefront.why_customers_love_us'))
        ->and($component->get('section_content')['social_proof']['items'][0]['icon'])->toBe('shield-check')
        ->and($component->get('section_content')['faq']['title'])->toBe('Custom FAQ');
});

test('resetSection ignores unknown section keys', function () {
    [$user, $store] = createStoreMember('owner');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $component = Volt::test('merchant.storefront-settings');

    $before = $component->get('section_content');
    $component->call('resetSection', 'rogue_key');

    expect($component->get('section_content'))->toBe($before);
});

test('settings page renders icon picker options for every social proof item', function () {
    [$user, $store] = createStoreMember('owner');

    $this->actingAs($user)->withSession(['current_store_id' => $store->id]);

    $response = $this->get(route('merchant.storefront-settings', ['store' => $store->slug]))
        ->assertOk();

    foreach (StorefrontSections::ICONS as $icon) {
        $response->assertSee('data-icon="' . $icon . '"', false);
    }

    // Three picker instances (one per item), plus a reset button per section.
    expect(substr_count($response->getContent(), 'data-item-index='))->toBe(3)
        ->and(substr_count($response->getContent(), 'data-reset-key='))->toBe(count(StorefrontSections::ALL));
});

test('editor html maxlengths mirror the TEXT_LIMITS contract exactly', function () {
    [$user, $store] = createStoreMember('owner');

    $this->actingAs($user)->withSession(['current_store_id' => $store->id]);

    $html = $this->get(route('merchant.storefront-settings', ['store' => $store->slug]))
        ->assertOk()
        ->getContent();

    preg_match_all('/id="([a-z0-9.-]+)"[^>]*?maxlength="(\d+)"/', $html, $matches, PREG_SET_ORDER);

    $actual = [];
    foreach ($matches as $match) {
        $actual[$match[1]] = $match[2];
    }

    $limits = StorefrontSections::TEXT_LIMITS;

    $expected = [
        'hero-title' => $limits['title'],
        'hero-description' => $limits['description'],
        'hero-button-text' => $limits['button_text'],
        'cta-title' => $limits['title'],
        'cta-description' => $limits['description'],
        'cta-button-text' => $limits['button_text'],
        'faq-title' => $limits['title'],
        'social-proof-title' => $limits['title'],
        'categories-title' => $limits['title'],
        'brands-title' => $limits['title'],
        'description-title' => $limits['title'],
    ];

    foreach ([0, 1, 2] as $i) {
        $expected['faq-item-' . $i . '-question'] = $limits['question'];
        $expected['faq-item-' . $i . '-answer'] = $limits['answer'];
        $expected['social-proof-item-' . $i . '-title'] = $limits['item_title'];
        $expected['social-proof-item-' . $i . '-description'] = $limits['item_description'];
    }

    // Exact population match: any extra, missing, or drifted maxlength fails.
    expect($actual)->toHaveCount(count($expected));

    foreach ($expected as $id => $max) {
        expect($actual)->toHaveKey($id, (string) $max, "Field [{$id}] must carry the contract limit {$max}.");
    }
});
