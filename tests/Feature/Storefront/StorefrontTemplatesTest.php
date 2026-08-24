<?php

use App\Models\Products\Product;
use App\Models\Stores\Store;
use App\Models\Stores\StoreThemeSetting;
use App\Support\Storefront\StorefrontSections;

/**
 * Storefront template rendering guards (Phase 0 fixes):
 *  - no raw icon names leaked as visible text (social proof)
 *  - FAQ accordion uses per-item literal Alpine state, single chevron
 *  - legacy/partial theme rows are normalized by every template
 */

function makeTemplateStore(string $template, ?array $sectionContent = null): Store
{
    $user = \App\Models\User::factory()->create();

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Template Store',
        'slug' => 'tpl-' . uniqid(),
        'status' => 'active',
        'landing_template' => $template,
    ]);

    if ($sectionContent !== null) {
        StoreThemeSetting::create([
            'store_id' => $store->id,
            // Legacy row: partial shape, rogue keys, missing list items.
            'primary_color' => '#112233',
            'secondary_color' => '#445566',
            'font_family' => 'Cairo',
            'homepage_sections' => ['hero', 'social_proof', 'faq', 'rogue_section'],
            'section_content' => $sectionContent,
        ]);
    }

    return $store;
}

function makeTemplateProduct(Store $store): Product
{
    return Product::create([
        'store_id' => $store->id,
        'name' => 'Hero Product',
        'slug' => 'hero-' . uniqid(),
        'sku' => 'SKU-' . uniqid(),
        'type' => 'simple',
        'price' => 1500,
        'is_active' => true,
    ]);
}

function makeCartItem(Store $store, Product $product): void
{
    $variant = \App\Models\Products\ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'VAR-' . uniqid(),
        'price' => 1500,
        'stock' => 10,
    ]);

    app(\App\Domains\Cart\Services\CartService::class)->addItem($store->id, $variant->id, 1);
}

function renderStorefrontHome(Store $store)
{
    config(['app.domain' => 'example.test']);

    return test()->get('http://' . $store->slug . '.example.test/');
}

test('default sections fallback is centralized per landing template', function () {
    expect(StorefrontSections::defaultSectionsFor('single_product'))
        ->toBe(StorefrontSections::DEFAULT_ENABLED)
        ->and(StorefrontSections::defaultSectionsFor('catalog'))
        ->toBe(['hero', 'categories', 'social_proof'])
        ->and(StorefrontSections::defaultSectionsFor('brand'))
        ->toBe(['hero', 'brands', 'social_proof'])
        ->and(StorefrontSections::defaultSectionsFor('unknown'))
        ->toBe(StorefrontSections::DEFAULT_ENABLED);
});

test('single product page never leaks raw icon names and renders a sane faq', function () {
    $store = makeTemplateStore('single_product', [
        // Deliberately partial: normalization must pad faq to 3 items and
        // fill the missing icon fields.
        'faq' => ['items' => [['question' => 'Custom Q?', 'answer' => 'Custom A.']]],
    ]);
    makeTemplateProduct($store);

    $html = renderStorefrontHome($store)->assertOk()->getContent();

    // Regression: icon names were printed as visible text above each item.
    expect($html)->not->toContain('>shield-check<')
        ->and($html)->not->toContain('>truck<')
        ->and($html)->not->toContain('>refresh<');

    // The broken FAQ signature (Alpine expression passed as Blade icon
    // name, duplicated status icon) must be gone entirely.
    expect($html)->not->toContain('name="openFaq')
        ->and($html)->not->toContain('status="expanded"');

    // New accordion: independent state per normalized item. (The storefront
    // header also ships a generic `{ open: false }` scope, so we anchor on
    // FAQ-only markers instead of counting x-data blocks.)
    expect(substr_count($html, ':aria-expanded="open"'))->toBe(3)
        // Chevron toggles via rotation on the same svg.
        ->and(substr_count($html, "x-bind:class=\"open ? 'rotate-180' : ''\""))->toBe(3);

    // Normalization kept the custom first question and padded the rest.
    expect($html)->toContain('Custom Q?')
        // Default truck icon SVG path proves icon fields were filled.
        ->and($html)->toContain('M8.25 18.75a1.5');
});

test('catalog page falls back to centralized defaults and normalizes content', function () {
    $store = makeTemplateStore('catalog');
    makeTemplateProduct($store); // catalog lists products; keep one active

    $html = renderStorefrontHome($store)->assertOk()->getContent();

    // Defaults applied -> social proof renders with default truck icon path.
    expect($html)->toContain('M8.25 18.75a1.5');
});

test('cart confirm handlers never ship statement expressions or @js leaks', function () {
    $store = makeTemplateStore('single_product');
    $product = makeTemplateProduct($store);

    // First request establishes the session the cart service writes into.
    renderStorefrontHome($store);
    makeCartItem($store, $product);

    $html = renderStorefrontHome($store)->assertOk()->getContent();

    // A populated cart must render the confirm buttons...
    expect($html)->toContain('data-confirm-title=')
        ->and($html)->toContain('$el.dataset.confirmText');

    // ...but NEVER with the broken signature: a bare `if (await ...)` as
    // the whole handler. Livewire wraps x-on values as expressions
    // (`return (SOURCE)`), so a leading statement is a SyntaxError.
    expect(preg_match("/=['\\\"]if \(await /", $html))->toBe(0)
        ->and($html)->not->toContain('@js(');
});

test('brand page normalizes legacy rows without fatal errors', function () {
    $store = makeTemplateStore('brand', [
        'brands' => ['title' => 'Collections'],
        'social_proof' => ['items' => [['title' => 'Trust', 'description' => 'Us', 'icon' => 'ribbon']]],
    ]);

    renderStorefrontHome($store)->assertOk();
});

test('catalog chips pass values via dataset and empty states differentiate filters', function () {
    $store = makeTemplateStore('catalog');
    $product = makeTemplateProduct($store);

    $category = \App\Models\Category::create([
        'store_id' => $store->id,
        'name' => 'Chairs',
        'slug' => 'chairs-' . uniqid(),
        'is_active' => true,
    ]);
    // Chips only render for categories that actually have active products.
    $category->products()->attach($product->id, ['store_id' => $store->id]);

    $html = renderStorefrontHome($store)->assertOk()->getContent();

    expect($html)->toContain('data-category-id="' . $category->id . '"')
        ->and($html)->toContain("\$wire.set('category_id', \$el.dataset.categoryId)")
        // The old Blade-interpolation-in-handler signature is gone.
        ->and($html)->not->toContain("set('category_id', '{{");

    // Windows: avoid compiled-view rename collisions between the HTTP render
    // above and the component re-render below.
    test()->artisan('view:clear');
    test()->withSession(['current_store_id' => $store->id]);
    $component = \Livewire\Volt\Volt::test('storefront.templates.catalog')
        ->set('search', 'zzz-no-match')
        ->assertSee(__('storefront.no_results_found'))
        ->assertSee('data-clear-filters');

    $component->call('clearFilters');

    expect($component->get('search'))->toBe('')
        ->and($component->get('category_id'))->toBe('');
});

test('empty catalog shows the generic empty state without a clear button', function () {
    $store = makeTemplateStore('catalog'); // no products at all

    $html = renderStorefrontHome($store)->assertOk()->getContent();

    expect($html)->toContain(__('storefront.no_products_found'))
        ->and($html)->not->toContain('data-clear-filters');
});

test('brand cards derive price and discounts from variants like catalog', function () {
    $store = makeTemplateStore('brand');
    $product = makeTemplateProduct($store);

    \App\Models\Products\ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Small',
        'sku' => 'VAR-A-' . uniqid(),
        'price' => 500,
        'compare_price' => 1000,
        'stock' => 5,
    ]);
    \App\Models\Products\ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Large',
        'sku' => 'VAR-B-' . uniqid(),
        'price' => 800,
        'compare_price' => 900,
        'stock' => 5,
    ]);

    $html = renderStorefrontHome($store)->assertOk()->getContent();

    // Discount badge derives from min(variant price) vs max(compare): -50%.
    expect($html)->toContain('-50%')
        ->and($html)->toContain(currency(500))
        // The dead non-existent-attribute code path must be gone.
        ->and($html)->not->toContain('min_price ??');
});

test('single product variant selector passes ids via dataset and reprices', function () {
    $store = makeTemplateStore('single_product');
    $product = makeTemplateProduct($store);

    $small = \App\Models\Products\ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Small',
        'sku' => 'SP-A-' . uniqid(),
        'price' => 500,
        'compare_price' => 1000,
        'stock' => 5,
    ]);
    $large = \App\Models\Products\ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Large',
        'sku' => 'SP-B-' . uniqid(),
        'price' => 800,
        'stock' => 5,
    ]);

    $html = renderStorefrontHome($store)->assertOk()->getContent();

    expect($html)->toContain('data-variant-id="' . $small->id . '"')
        ->and($html)->toContain('data-variant-id="' . $large->id . '"')
        ->and($html)->toContain("\$wire.selectVariant(\$el.dataset.variantId)")
        // The old interpolation-in-handler signature is gone.
        ->and($html)->not->toContain("selectVariant('{{");

    test()->artisan('view:clear');
    test()->withSession(['current_store_id' => $store->id]);
    $component = \Livewire\Volt\Volt::test('storefront.templates.single-product');

    // Default selection is the first variant; hero shows its price/discount.
    expect($component->get('selectedVariant.id'))->toBe($small->id);

    $component->call('selectVariant', (string) $large->id)
        ->assertSet('selectedVariant.id', $large->id)
        ->assertSee(currency(800));
});
