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
    // Catalog keeps the mini-cart UI mounted; single-product stores now run
    // in direct-order mode with the drawer hidden entirely.
    $store = makeTemplateStore('catalog');
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

test('single product page orders through one direct matrix without duplication', function () {
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
    \App\Models\Products\ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Large',
        'sku' => 'SP-B-' . uniqid(),
        'price' => 800,
        'stock' => 5,
    ]);

    $html = renderStorefrontHome($store)->assertOk()->getContent();

    // One ordering gateway: the direct matrix. No chips selector, no
    // standalone add/checkout buttons, no duplicated "options" label.
    expect($html)->toContain(__('storefront.variants_matrix_title'))
        ->and($html)->toContain('wire:model="quantities.' . $small->id . '"')
        ->and($html)->toContain(__('storefront.order_now'))
        ->and($html)->not->toContain(__('storefront.options'))
        ->and($html)->not->toContain('wire:click="buyNow"')
        ->and($html)->not->toContain('$wire.selectVariant');
});

test('direct matrix clears leftovers and jumps to checkout', function () {
    $store = makeTemplateStore('single_product');
    $product = makeTemplateProduct($store);
    $variant = \App\Models\Products\ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'BUY-' . uniqid(),
        'price' => 250,
        'stock' => 9,
    ]);

    renderStorefrontHome($store);

    // Pre-existing leftovers from an older template must not leak into the
    // direct order.
    app(\App\Domains\Cart\Services\CartService::class)->addItem($store->id, $variant->id, 3);

    test()->artisan('view:clear');
    test()->withSession(['current_store_id' => $store->id]);

    \Livewire\Volt\Volt::test('storefront.variant-matrix', [
        'productId' => (string) $product->id,
        'direct' => true,
    ])
        ->set('quantities.' . $variant->id, 2)
        ->call('buyNowFromMatrix')
        ->assertRedirect(route('storefront.checkout', ['store' => $store->slug]));

    $cart = app(\App\Domains\Cart\Services\CartService::class);

    expect($cart->getCount($store->id))->toBe(2)
        ->and((string) $cart->getItems($store->id)->first()['quantity'])->toBe('2');
});

test('mini cart purges stale items instead of crashing on a blank slug', function () {
    $user = \App\Models\User::factory()->create();
    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Stale Cart Store',
        'slug' => 'stale-cart-' . uniqid(),
        'status' => 'active',
        'landing_template' => 'catalog',
    ]);

    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Stale Cart Product',
        'slug' => 'stale-' . uniqid(),
        'sku' => 'STALE-' . uniqid(),
        'type' => 'simple',
        'price' => 100,
        'is_active' => true,
    ]);

    // First request establishes the session; then put the variant in the cart.
    renderStorefrontHome($store);
    makeCartItem($store, $product);

    // Simulate the product disappearing AFTER being added to the cart
    // (deleted row): previously this exploded with "Missing required
    // parameter ... [Missing parameter: product]".
    \Illuminate\Support\Facades\DB::table('product_variants')->where('product_id', $product->id)->delete();
    \Illuminate\Support\Facades\DB::table('products')->where('id', $product->id)->delete();

    $html = renderStorefrontHome($store)->assertOk()->getContent();

    expect($html)->not->toContain('Stale Cart Product')
        ->and(app(\App\Domains\Cart\Services\CartService::class)->getCount($store->id))->toBe(0);
});

test('mini cart survives converting a carted simple product to variable', function () {
    $user = \App\Models\User::factory()->create();
    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Convert Store',
        'slug' => 'convert-' . uniqid(),
        'status' => 'active',
        'landing_template' => 'catalog',
    ]);

    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Converted Product',
        'slug' => 'conv-' . uniqid(),
        'sku' => 'CONV-' . uniqid(),
        'type' => 'simple',
        'price' => 100,
        'is_active' => true,
    ]);

    renderStorefrontHome($store);
    makeCartItem($store, $product);

    // UpdateProductAction: converting to variable wipes ALL old variants and
    // creates brand-new ones -> the session cart now holds a dead variant_id
    // (the reported crash path).
    \App\Models\Products\ProductVariant::where('product_id', $product->id)->delete();
    \App\Models\Products\ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'New Large',
        'sku' => 'CONV-V2-' . uniqid(),
        'price' => 120,
        'stock' => 7,
    ]);

    expect(app(\App\Domains\Cart\Services\CartService::class)->getCount($store->id))->toBe(1);

    renderStorefrontHome($store)->assertOk();

    // The stale item was purged during the render, not crashed on.
    expect(app(\App\Domains\Cart\Services\CartService::class)->getCount($store->id))->toBe(0);
});

test('single product page renders the merchant-chosen showcase with fallback', function () {
    $store = makeTemplateStore('single_product', [
        'single_product' => ['product_id' => '999999-stale'],
    ]);

    $alpha = Product::create([
        'store_id' => $store->id,
        'name' => 'Alpha Showcase',
        'slug' => 'alpha-' . uniqid(),
        'sku' => 'SHOW-A-' . uniqid(),
        'type' => 'simple',
        'price' => 100,
        'is_active' => true,
    ]);
    $beta = Product::create([
        'store_id' => $store->id,
        'name' => 'Beta Showcase',
        'slug' => 'beta-' . uniqid(),
        'sku' => 'SHOW-B-' . uniqid(),
        'type' => 'simple',
        'price' => 200,
        'is_active' => true,
    ]);

    // Stale chosen id -> automatic fallback to the first active product.
    $html = renderStorefrontHome($store)->assertOk()->getContent();

    expect($html)->toContain('Alpha Showcase')
        ->and($html)->not->toContain('Beta Showcase');

    // Merchant explicitly picks Beta through the theme contract.
    $store->theme->update([
        'section_content' => ['single_product' => ['product_id' => (string) $beta->id]],
    ]);

    $html = renderStorefrontHome($store)->assertOk()->getContent();

    expect($html)->toContain('Beta Showcase')
        ->and($html)->not->toContain('Alpha Showcase')
        ->and($alpha->fresh()->exists)->toBeTrue();
});

test('single product mode hides the cart entirely', function () {
    $store = makeTemplateStore('single_product');
    $product = makeTemplateProduct($store);
    \App\Models\Products\ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'BUY-' . uniqid(),
        'price' => 250,
        'stock' => 9,
    ]);

    $html = renderStorefrontHome($store)->assertOk()->getContent();

    // Direct-order mode: the mini cart component is gone, "order now" is shown.
    expect($html)->not->toContain('this.$wire.refreshCart()')
        ->and($html)->toContain(__('storefront.order_now'));
});

test('single product stores purge cart items of other products', function () {
    $user = \App\Models\User::factory()->create();
    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Purge Store',
        'slug' => 'purge-' . uniqid(),
        'status' => 'active',
        'landing_template' => 'catalog',
    ]);

    $kept = Product::create([
        'store_id' => $store->id,
        'name' => 'Kept Product',
        'slug' => 'kept-' . uniqid(),
        'sku' => 'KEEP-' . uniqid(),
        'type' => 'simple',
        'price' => 100,
        'is_active' => true,
    ]);

    makeCartItem($store, $kept);

    // Merchant switches the store to the single-product template afterwards.
    $store->update(['landing_template' => 'single_product']);
    $store->theme()->create([
        'section_content' => ['single_product' => ['product_id' => (string) $kept->id]],
    ]);

    renderStorefrontHome($store)->assertOk();

    expect(app(\App\Domains\Cart\Services\CartService::class)->getCount($store->id))->toBe(1);
});

test('variant matrix lets customers order several variants at once', function () {
    $store = makeTemplateStore('single_product');
    $product = makeTemplateProduct($store);

    $red = \App\Models\Products\ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Red',
        'sku' => 'MX-R-' . uniqid(),
        'price' => 500,
        'stock' => 10,
    ]);
    $blue = \App\Models\Products\ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Blue',
        'sku' => 'MX-B-' . uniqid(),
        'price' => 500,
        'stock' => 4,
    ]);
    $empty = \App\Models\Products\ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Sold Out',
        'sku' => 'MX-S-' . uniqid(),
        'price' => 500,
        'stock' => 0,
    ]);

    // Matrix renders on both single-product template and product detail page.
    $html = renderStorefrontHome($store)->assertOk()->getContent();
    expect($html)->toContain(__('storefront.variants_matrix_title'))
        ->and($html)->toContain('wire:model="quantities.' . $red->id . '"')
        // Sold-out rows are listed but gated off.
        ->and($html)->toContain(__('storefront.out_of_stock'));

    test()->artisan('view:clear');
    test()->withSession(['current_store_id' => $store->id]);

    app(\App\Domains\Cart\Services\CartService::class)
        ->addItem($store->id, $empty->id === '' ? '' : $red->id, 0);

    $component = \Livewire\Volt\Volt::test('storefront.variant-matrix', ['productId' => (string) $product->id]);
    $component->set('quantities.' . $red->id, 2);
    $component->set('quantities.' . $blue->id, 3);
    $component->call('addAllToCart');

    $cartService = app(\App\Domains\Cart\Services\CartService::class);
    $items = $cartService->getItems($store->id)->keyBy('variant_id');

    // Red x2, Blue x3; sold-out row ignored; stock caps respected.
    expect((string) $items[$red->id]['quantity'])->toBe('2')
        ->and((string) $items[$blue->id]['quantity'])->toBe('3')
        ->and($items->has($empty->id))->toBeFalse()
        ->and($cartService->getCount($store->id))->toBe(5)
        // Quantities reset after the commit.
        ->and($component->get('quantities'))->toBe([]);

    // Foreign product id through the attribute is rejected.
    [$otherUser, $otherStore] = [\App\Models\User::factory()->create(), null];
    $otherStore = Store::create([
        'user_id' => $otherUser->id,
        'name' => 'Other Store',
        'slug' => 'other-mx-' . uniqid(),
        'status' => 'active',
    ]);
    $foreign = Product::create([
        'store_id' => $otherStore->id,
        'name' => 'Foreign',
        'slug' => 'fmx-' . uniqid(),
        'sku' => 'FMX-' . uniqid(),
        'type' => 'simple',
        'price' => 10,
        'is_active' => true,
    ]);

    renderStorefrontHome($store);
    \Livewire\Volt\Volt::test('storefront.variant-matrix', ['productId' => (string) $foreign->id])
        ->set('quantities.' . $foreign->id, 1)
        ->call('addAllToCart');

    expect(app(\App\Domains\Cart\Services\CartService::class)->getCount($store->id))->toBe(5);
});

test('product page info panel is data-driven and keeps a single ordering flow', function () {
    config(['app.domain' => 'example.test']);

    $store = makeTemplateStore('catalog');
    $product = makeTemplateProduct($store);

    \App\Models\Products\ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Red',
        'sku' => 'PD-A-' . uniqid(),
        'price' => 500,
        'stock' => 5,
    ]);
    \App\Models\Products\ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Blue',
        'sku' => 'PD-B-' . uniqid(),
        'price' => 700,
        'stock' => 5,
    ]);

    $html = test()->get('http://' . $store->slug . '.example.test/product/' . $product->slug)
        ->assertOk()
        ->getContent();

    // The variant payload travels through a plain data attribute; the Alpine
    // expression itself stays literal (no quote collision, no inline JSON).
    expect($html)->toContain('data-variants="{&quot;')
        ->and($html)->toContain('productInfo(JSON.parse($el.dataset.variants')
        ->and($html)->toContain('select($el.dataset.variantId)')
        // Plain JS function bodies have no $wire scope: only this.$wire is
        // legal inside the layout's productInfo() definition.
        ->and($html)->toContain('this.variants[this.$wire.selectedVariantId]')
        ->and($html)->not->toContain('variants[$wire.')
        ->and($html)->not->toContain("= \$wire.set(")
        ->and($html)->not->toContain("x-data='productInfo(")
        ->and($html)->not->toContain("select('{{")
        // Classic selector is the ONLY ordering gateway on regular product
        // pages: the multi-variant matrix stays exclusive to single-product.
        ->and($html)->not->toContain(__('storefront.variants_matrix_title'))
        ->and($html)->toContain(__('storefront.options'));
});
