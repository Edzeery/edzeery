<?php

use App\Domains\Product\Support\ProductWizardSteps;
use App\Enums\Store\ProductOptionInputType;
use App\Models\Products\Product;
use App\Models\Products\ProductImage;
use App\Models\Products\ProductOption;
use App\Models\Products\ProductOptionValue;
use App\Models\Products\ProductVariant;
use App\Models\Stores\Store;
use App\Services\ProductService;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Volt\Volt;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(PlansSeeder::class);
});

function wizardStepLabels(): array
{
    return array_map(
        fn (string $label) => str_replace('&', '&amp;', $label),
        [
            __('products.step_basic_info'),
            __('products.step_images'),
            __('products.step_pricing'),
            __('products.step_options'),
            __('products.step_inventory'),
            __('products.step_review'),
        ],
    );
}

function wizardNav(string $html): string
{
    $navStart = strpos($html, 'aria-label="Progress"');
    $navEnd = strpos($html, '</nav>', $navStart);

    expect($navStart)->not->toBeFalse()
        ->and($navEnd)->not->toBeFalse();

    return substr($html, $navStart, $navEnd - $navStart);
}

test('simple product wizard renders five tabs and never shows the options tab', function () {
    [$user, $store] = skuUser();

    $nav = wizardNav(Volt::test('merchant.products.form')->html());

    $position = -1;
    foreach (array_merge(array_slice(wizardStepLabels(), 0, 3), array_slice(wizardStepLabels(), 4, 2)) as $label) {
        $pos = strpos($nav, $label);
        expect($pos)->not->toBeFalse("Step label \"{$label}\" not rendered in the nav");
        expect($pos)->toBeGreaterThan($position);
        $position = $pos;
    }

    expect($nav)->not->toContain(str_replace('&', '&amp;', __('products.step_options')));
});

test('variable product wizard renders all six tabs in canonical order', function () {
    [$user, $store] = skuUser();

    $volt = Volt::test('merchant.products.form')->set('has_variants', true);

    $nav = wizardNav($volt->html());

    $position = -1;
    foreach (wizardStepLabels() as $label) {
        $pos = strpos($nav, $label);
        expect($pos)->not->toBeFalse("Step label \"{$label}\" not rendered in the nav");
        expect($pos)->toBeGreaterThan($position);
        $position = $pos;
    }
});

test('a locked step cannot be navigated to at fresh mount', function () {
    [$user, $store] = skuUser();

    $volt = Volt::test('merchant.products.form');

    $volt->call('goToStep', ProductWizardSteps::STEP_REVIEW);

    $volt->assertSet('currentStep', ProductWizardSteps::STEP_BASIC)
        ->assertNotDispatched('error')
        ->assertHasNoErrors()
        ->assertDispatched('swal');
});

test('completed steps unlock and free navigation preserves state', function () {
    [$user, $store] = skuUser();

    $volt = Volt::test('merchant.products.form')->set('has_variants', true);

    // Step 4 (options) and step 2 (images) are unreachable before basic info.
    $volt->call('goToStep', ProductWizardSteps::STEP_OPTIONS);
    $volt->assertSet('currentStep', ProductWizardSteps::STEP_BASIC);

    $volt->call('goToStep', ProductWizardSteps::STEP_IMAGES);
    $volt->assertSet('currentStep', ProductWizardSteps::STEP_BASIC);

    // Validate step 1 and advance: Images becomes the active step right after it.
    $volt->set(['name' => 'Free Nav Product', 'slug' => 'free-nav-product'])
        ->call('nextStep');

    $volt->assertSet('currentStep', ProductWizardSteps::STEP_IMAGES)
        ->assertSet('validated_steps', [ProductWizardSteps::STEP_BASIC]);

    // Pricing (step 3) is still locked until Images itself is validated.
    $volt->call('goToStep', ProductWizardSteps::STEP_PRICING);
    $volt->assertSet('currentStep', ProductWizardSteps::STEP_IMAGES);

    // Options (step 4) stays locked too.
    $volt->call('goToStep', ProductWizardSteps::STEP_OPTIONS);
    $volt->assertSet('currentStep', ProductWizardSteps::STEP_IMAGES);

    // Passing the empty Images rule set unlocks Pricing.
    $volt->call('nextStep');

    $volt->assertSet('currentStep', ProductWizardSteps::STEP_PRICING)
        ->assertSet('validated_steps', [
            ProductWizardSteps::STEP_BASIC,
            ProductWizardSteps::STEP_IMAGES,
        ]);

    // Step 4 still locked, step 1 unlocked (backwards navigation ok).
    $volt->call('goToStep', ProductWizardSteps::STEP_OPTIONS);
    $volt->assertSet('currentStep', ProductWizardSteps::STEP_PRICING);

    $volt->call('goToStep', ProductWizardSteps::STEP_BASIC);
    $volt->assertSet('currentStep', ProductWizardSteps::STEP_BASIC);

    // Forward to step 3 again and validate it.
    $volt->call('goToStep', ProductWizardSteps::STEP_PRICING);
    $volt->assertSet('currentStep', ProductWizardSteps::STEP_PRICING)
        ->assertSet('validated_steps', [
            ProductWizardSteps::STEP_BASIC,
            ProductWizardSteps::STEP_IMAGES,
            ProductWizardSteps::STEP_PRICING,
        ]);

    // Step 4 is now reachable.
    $volt->set(['price' => 100, 'cost_price' => 50])
        ->call('goToStep', ProductWizardSteps::STEP_OPTIONS);

    $volt->assertSet('currentStep', ProductWizardSteps::STEP_OPTIONS);
});

test('edit mode pre-unlocks every step for free navigation', function () {
    [$user, $store] = skuUser();
    $service = app(ProductService::class);

    $product = $service->create($store, dataShape('edit-free-nav'));

    $volt = Volt::test('merchant.products.form', ['product' => $product]);

    $volt->assertSet('validated_steps', ProductWizardSteps::ids());

    $volt->call('goToStep', ProductWizardSteps::STEP_REVIEW);

    $volt->assertSet('currentStep', ProductWizardSteps::STEP_REVIEW)
        ->assertNotDispatched('swal');
});

test('the images step renders persisted images and the upload input', function () {
    [$user, $store] = skuUser();

    $volt = Volt::test('merchant.products.form');

    $volt->set('images', ['products/cover.jpg', 'products/alt.jpg']);

    $html = $volt->html();

    expect($html)->toContain('products/cover.jpg')
        ->and($html)->toContain('products/alt.jpg')
        ->and($html)->toContain('type="file"')
        ->and($html)->toContain('accept="image/*"')
        ->and($html)->toContain('wire:model="images"')
        ->and($html)->toContain(__('products.main_image'))
        ->and($html)->toContain('wire:click="makeMainImage(1)"');
});

test('makeMainImage moves the chosen image to the front', function () {
    [$user, $store] = skuUser();

    $volt = Volt::test('merchant.products.form');

    $volt->set('images', ['a.jpg', 'b.jpg', 'c.jpg']);

    $volt->call('makeMainImage', 2);

    expect($volt->get('images'))->toBe(['c.jpg', 'a.jpg', 'b.jpg']);
});

test('makeMainImage ignores the already-main image and out-of-range indexes', function () {
    [$user, $store] = skuUser();

    $volt = Volt::test('merchant.products.form');

    $volt->set('images', ['a.jpg', 'b.jpg']);

    $volt->call('makeMainImage', 0)
        ->call('makeMainImage', 7);

    expect($volt->get('images'))->toBe(['a.jpg', 'b.jpg']);
});

test('uploading new images appends to an existing saved gallery instead of replacing it', function () {
    [$user, $store] = skuUser();

    $volt = Volt::test('merchant.products.form');

    $volt->set('images', ['products/kept.jpg']);

    $volt->upload('images', [UploadedFile::fake()->image('new.png')], true);

    $images = $volt->get('images');

    expect($images)->toHaveCount(2)
        ->and($images[0])->toBe('products/kept.jpg')
        ->and($images[1])->toBeInstanceOf(TemporaryUploadedFile::class);
});

test('uploading a second batch in create mode appends instead of replacing the first one', function () {
    [$user, $store] = skuUser();

    $volt = Volt::test('merchant.products.form');

    $volt->upload('images', [UploadedFile::fake()->image('first.png')], true);

    expect($volt->get('images'))->toHaveCount(1);

    $volt->upload('images', [UploadedFile::fake()->image('second.png')], true);

    $images = $volt->get('images');

    expect($images)->toHaveCount(2)
        ->and($images[0])->toBeInstanceOf(TemporaryUploadedFile::class)
        ->and($images[1])->toBeInstanceOf(TemporaryUploadedFile::class);
});

test('saving the wizard persists the primary image as the first product image', function () {
    [$user, $store] = skuUser();

    $volt = Volt::test('merchant.products.form');

    $volt->set('name', 'Main Image Product')
        ->set('slug', 'main-image-product')
        ->set('images', ['products/main.jpg', 'products/second.jpg'])
        ->set('price', 100)
        ->set('cost_price', 50)
        ->set('stock', 10)
        ->set('low_stock_threshold', 5);

    $volt->call('save');

    $product = Product::where('store_id', $store->id)->where('slug', 'main-image-product')->first();

    expect($product)->not->toBeNull();

    $paths = $product->images()->orderBy('sort_order')->pluck('path')->all();

    expect($paths)->toBe(['products/main.jpg', 'products/second.jpg'])
        ->and($product->images()->first()->path)->toBe('products/main.jpg');
});

test('saving the wizard keeps the promoted image first after makeMainImage', function () {
    [$user, $store] = skuUser();

    $volt = Volt::test('merchant.products.form');

    $volt->set('name', 'Promoted Image')
        ->set('slug', 'promoted-image')
        ->set('images', ['products/first.jpg', 'products/second.jpg', 'products/third.jpg'])
        ->set('price', 100)
        ->set('cost_price', 50)
        ->set('stock', 10)
        ->set('low_stock_threshold', 5);

    $volt->call('makeMainImage', 2)
        ->call('save');

    $product = Product::where('store_id', $store->id)->where('slug', 'promoted-image')->first();

    expect($product)->not->toBeNull();

    $paths = $product->images()->orderBy('sort_order')->pluck('path')->all();

    expect($paths)->toBe(['products/third.jpg', 'products/first.jpg', 'products/second.jpg']);
});

function walkWizardToInventory($volt, string $name, string $slug): void
{
    // Simple product (has_variants off): Basic → Images → Pricing → Inventory;
    // the hidden Options step is skipped by the engine.
    $volt->set(['name' => $name, 'slug' => $slug])
        ->call('nextStep')
        ->call('nextStep')
        ->call('nextStep');
}

test('empty sku with auto-generation off blocks advancing past inventory', function () {
    [$user, $store] = skuUser();

    $volt = Volt::test('merchant.products.form');

    walkWizardToInventory($volt, 'Sku Blocked', 'sku-blocked');

    $volt->assertSet('currentStep', ProductWizardSteps::STEP_INVENTORY);

    $volt->set('auto_generate_sku', false)->call('nextStep');

    $volt->assertSet('currentStep', ProductWizardSteps::STEP_INVENTORY)
        ->assertSet('validated_steps', [
            ProductWizardSteps::STEP_BASIC,
            ProductWizardSteps::STEP_IMAGES,
            ProductWizardSteps::STEP_PRICING,
        ])
        ->assertHasErrors(['sku']);

    expect($volt->html())->toContain('edz-field__error');
});

test('enabling automatic sku generation clears the inventory block', function () {
    [$user, $store] = skuUser();

    $volt = Volt::test('merchant.products.form');

    walkWizardToInventory($volt, 'Sku Auto', 'sku-auto');

    $volt->set('auto_generate_sku', false)->call('nextStep');

    $volt->assertSet('currentStep', ProductWizardSteps::STEP_INVENTORY)
        ->assertHasErrors(['sku']);

    $volt->set('auto_generate_sku', true)->call('nextStep');

    $volt->assertSet('currentStep', ProductWizardSteps::STEP_REVIEW)
        ->assertHasNoErrors(['sku'])
        ->assertSet('validated_steps', [
            ProductWizardSteps::STEP_BASIC,
            ProductWizardSteps::STEP_IMAGES,
            ProductWizardSteps::STEP_PRICING,
            ProductWizardSteps::STEP_INVENTORY,
        ]);
});

test('a save-time sku validation failure jumps back to the inventory step', function () {
    [$user, $store] = skuUser();
    $service = app(ProductService::class);

    $product = $service->create($store, dataShape('jump-edit-target'));

    $volt = Volt::test('merchant.products.form', ['product' => $product]);

    $volt->set('auto_generate_sku', true)->set('sku', '')->call('goToStep', ProductWizardSteps::STEP_REVIEW);

    $volt->assertSet('currentStep', ProductWizardSteps::STEP_REVIEW);

    $volt->set(['auto_generate_sku' => false, 'sku' => ''])->call('save');

    $volt->assertSet('currentStep', ProductWizardSteps::STEP_INVENTORY)
        ->assertHasErrors(['sku']);
});

function makeVariableProduct(Store $store, string $slug, array $imageByVariant = []): array
{
    $product = Product::create([
        'store_id' => $store->id,
        'name' => "Variable {$slug}",
        'slug' => $slug,
        'sku' => 'VAR-' . strtoupper($slug),
        'type' => 'variable',
        'price' => 100,
        'is_active' => true,
    ]);

    $option = ProductOption::create([
        'store_id' => $store->id,
        'name' => 'Size',
        'type' => ProductOptionInputType::SELECT->value,
    ]);

    $variants = collect();

    foreach (['S', 'M'] as $index => $label) {
        $value = ProductOptionValue::create([
            'store_id' => $store->id,
            'product_option_id' => $option->id,
            'value' => $label,
            'sort_order' => $index,
        ]);

        $variant = ProductVariant::create([
            'store_id' => $store->id,
            'product_id' => $product->id,
            'name' => $label,
            'sku' => "VAR-" . strtoupper($slug) . "-{$index}",
            'price' => 100,
            'cost_price' => 50,
            'stock' => 10,
            'is_default' => $index === 0,
        ]);

        $variant->optionValues()->sync([$value->id => ['product_option_id' => $option->id]]);

        $variants->push($variant);
    }

    foreach ($imageByVariant as $variantIndex => $path) {
        $variants[$variantIndex]->images()->create([
            'path' => $path,
            'store_id' => $store->id,
            'is_primary' => true,
            'sort_order' => 0,
        ]);
    }

    return [$product, $variants];
}

test('variant images are persisted scoped to their variant on create', function () {
    [$user, $store] = skuUser();
    Storage::fake('public');

    $option = ProductOption::create([
        'store_id' => $store->id,
        'name' => 'Size',
        'type' => ProductOptionInputType::SELECT->value,
    ]);

    $valueS = ProductOptionValue::create(['store_id' => $store->id, 'product_option_id' => $option->id, 'value' => 'S', 'sort_order' => 0]);
    $valueM = ProductOptionValue::create(['store_id' => $store->id, 'product_option_id' => $option->id, 'value' => 'M', 'sort_order' => 1]);

    $volt = Volt::test('merchant.products.form');

    $volt->set([
        'name' => 'Variant Upload Create',
        'slug' => 'variant-upload-create',
        'has_variants' => true,
        'auto_generate_sku' => true,
        'options' => [[
            'product_option_id' => $option->id,
            'type' => ProductOptionInputType::SELECT->value,
            'values' => [$valueS->id, $valueM->id],
        ]],
    ])->call('valuesChanged', 0);

    $volt->assertCount('variants_preview', 2);

    $volt->set('variants_preview.0.price', 100)
        ->set('variants_preview.0.cost_price', 50)
        ->set('variants_preview.1.price', 120)
        ->set('variants_preview.1.cost_price', 60);

    $volt->upload('variants_preview.0.new_image', [UploadedFile::fake()->image('variant-1.jpg')]);

    $volt->call('save');

    $product = Product::where('slug', 'variant-upload-create')->first();

    expect($product)->not->toBeNull()
        ->and($product->variants()->count())->toBe(2);

    $images = ProductImage::where('imageable_type', ProductVariant::class)
        ->whereIn('imageable_id', $product->variants()->pluck('id'))
        ->get();

    expect($images)->toHaveCount(1);

    $imagedVariant = $product->variants()->find($images->first()->imageable_id);

    expect($imagedVariant->optionValues->pluck('value')->contains('S'))->toBeTrue();

    $other = $product->variants()->where('id', '!=', $imagedVariant->id)->get();

    expect($other)->toHaveCount(1)
        ->and($other->first()->images()->count())->toBe(0);
});

test('editing loads variant image paths and replacing removes the old record', function () {
    [$user, $store] = skuUser();
    Storage::fake('public');

    [$product, $variants] = makeVariableProduct($store, 'variant-edit-replace', [0 => 'products/variant-cover.jpg']);

    $volt = Volt::test('merchant.products.form', ['product' => $product]);

    $volt->assertSet('variants_preview.0.image', 'products/variant-cover.jpg')
        ->assertSet('variants_preview.1.image', null);

    $volt->upload('variants_preview.0.new_image', [UploadedFile::fake()->image('new-variant.jpg')])
        ->call('save');

    expect($variants[0]->images()->count())->toBe(1)
        ->and($variants[0]->images()->first()->path)->not->toBe('products/variant-cover.jpg')
        ->and($variants[1]->images()->count())->toBe(0);
});

test('a variant without an image renders a neutral placeholder instead of a broken thumbnail', function () {
    [$user, $store] = skuUser();

    [$product, $variants] = makeVariableProduct($store, 'variant-placeholder');

    $volt = Volt::test('merchant.products.form', ['product' => $product]);

    $html = $volt->html();

    expect($html)->toContain('wire:model="variants_preview.0.new_image"')
        ->and($html)->toContain('wire:model="variants_preview.1.new_image"')
        ->and($html)->toContain(__('products.variant_image'))
        ->and($html)->toContain('overflow-x-auto')
        ->and($html)->not->toContain('removeVariantImage')
        ->and($html)->not->toContain('temporaryUrl(');
});

test('unchanged variants do not trigger per-variant image queries on save', function () {
    [$user, $store] = skuUser();

    [$product, $variants] = makeVariableProduct($store, 'variant-query-count', [0 => 'products/cover.jpg']);

    $volt = Volt::test('merchant.products.form', ['product' => $product]);

    DB::flushQueryLog();
    DB::enableQueryLog();

    $volt->call('save');

    $imageQueries = collect(DB::getQueryLog())
        ->filter(fn ($q) => str_contains($q['query'], 'product_images'));

    expect($imageQueries->count())->toBe(1);
});

test('visible drops options without variants and restores it with variants', function () {
    expect(ProductWizardSteps::visibleIds(['has_variants' => false]))
        ->toBe([1, 2, 3, 5, 6])
        ->and(count(ProductWizardSteps::visible(['has_variants' => false])))->toBe(5)
        ->and(ProductWizardSteps::isStepVisible(ProductWizardSteps::STEP_OPTIONS, ['has_variants' => false]))->toBeFalse();

    expect(ProductWizardSteps::visibleIds(['has_variants' => true]))
        ->toBe([1, 2, 3, 4, 5, 6])
        ->and(count(ProductWizardSteps::visible(['has_variants' => true])))->toBe(6)
        ->and(ProductWizardSteps::isStepVisible(ProductWizardSteps::STEP_OPTIONS, ['has_variants' => true]))->toBeTrue();
});

test('nextStep and prevStep skip the hidden options step for simple products', function () {
    [$user, $store] = skuUser();

    $volt = Volt::test('merchant.products.form');

    $volt->set(['name' => 'Simple Skip', 'slug' => 'simple-skip'])
        ->call('nextStep')
        ->call('nextStep');

    $volt->assertSet('currentStep', ProductWizardSteps::STEP_PRICING);

    $volt->call('nextStep');

    // Options (4) is skipped entirely: the next visible step is Inventory (5).
    $volt->assertSet('currentStep', ProductWizardSteps::STEP_INVENTORY)
        ->assertSet('validated_steps', [
            ProductWizardSteps::STEP_BASIC,
            ProductWizardSteps::STEP_IMAGES,
            ProductWizardSteps::STEP_PRICING,
        ]);

    // Going back from Inventory skips Options too, landing straight on Pricing.
    $volt->call('prevStep');

    $volt->assertSet('currentStep', ProductWizardSteps::STEP_PRICING);

    $volt->call('nextStep')->call('nextStep');

    $volt->assertSet('currentStep', ProductWizardSteps::STEP_REVIEW);
});

test('openCreateOption without an index appends a blank draft row and opens the modal', function () {
    [$user, $store] = skuUser();

    $volt = Volt::test('merchant.products.form')->set('has_variants', true);

    $volt->call('openCreateOption');

    $volt->assertSet('optionModalRow', 0)
        ->assertSet('optionModalCreatedId', null)
        ->assertSet('options.0.product_option_id', null)
        ->assertSet('options.0.values', []);

    // Calling it again reuses the already-empty row rather than duplicating it.
    $volt->call('openCreateOption');

    $volt->assertSet('optionModalRow', 0)
        ->assertCount('options', 1);
});

test('openCreateOption resolves the requested draft row index', function () {
    [$user, $store] = skuUser();
    $option = ProductOption::create([
        'store_id' => $store->id,
        'name' => 'Size',
        'type' => ProductOptionInputType::SELECT->value,
    ]);

    $volt = Volt::test('merchant.products.form')->set('has_variants', true);

    $volt->set('options', [
        0 => ['product_option_id' => $option->id, 'type' => 'select', 'values' => []],
        1 => ['product_option_id' => null, 'type' => null, 'values' => []],
    ])->call('openCreateOption', 1);

    $volt->assertSet('optionModalRow', 1);
});

test('createOptionInline validates input and wires a created option into the draft row', function () {
    [$user, $store] = skuUser();

    $volt = Volt::test('merchant.products.form')->set('has_variants', true)->call('openCreateOption');

    $volt->call('createOptionInline');

    $volt->assertHasErrors(['optionModal.name', 'optionModal.type']);

    $volt->set('optionModal', ['name' => 'Color', 'type' => ProductOptionInputType::SELECT->value])
        ->call('createOptionInline');

    expect(ProductOption::where('store_id', $store->id)->where('name', 'Color')->exists())->toBeTrue();

    $option = ProductOption::where('store_id', $store->id)->where('name', 'Color')->first();

    $volt->assertSet('optionModalCreatedId', $option->id)
        ->assertSet('options.0.product_option_id', $option->id)
        ->assertSet('options.0.type', ProductOptionInputType::SELECT->value)
        ->assertSet('options.0.values', []);
});

test('addOptionValueInline creates and immediately selects the value in the draft row', function () {
    [$user, $store] = skuUser();

    $volt = Volt::test('merchant.products.form')->set('has_variants', true)->call('openCreateOption');

    $volt->set('optionModal', ['name' => 'Color', 'type' => ProductOptionInputType::SELECT->value])
        ->call('createOptionInline');

    $option = ProductOption::where('store_id', $store->id)->where('name', 'Color')->first();

    $volt->call('addOptionValueInline');

    expect(ProductOptionValue::where('product_option_id', $option->id)->count())->toBe(0)
        ->and($volt->get('options.0.values'))->toBe([]);

    $volt->set('optionNewValue', 'Red')->call('addOptionValueInline');

    $value = ProductOptionValue::where('product_option_id', $option->id)->where('value', 'Red')->first();

    expect($value)->not->toBeNull()
        ->and($volt->get('optionNewValue'))->toBe('')
        ->and($volt->get('options.0.values'))->toBe([$value->id]);

    // Adding the same value again neither duplicates the row not the draft selection.
    $volt->set('optionNewValue', 'Red')->call('addOptionValueInline');

    $volt->assertCount('options.0.values', 1);
});

test('removeOptionValueInline deletes an unused value and strips it from the draft', function () {
    [$user, $store] = skuUser();

    $volt = Volt::test('merchant.products.form')->set('has_variants', true)->call('openCreateOption');

    $volt->set('optionModal', ['name' => 'Color', 'type' => ProductOptionInputType::SELECT->value])
        ->call('createOptionInline');

    $option = ProductOption::where('store_id', $store->id)->where('name', 'Color')->first();

    $volt->set('optionNewValue', 'Red')->call('addOptionValueInline');
    $volt->set('optionNewValue', 'Blue')->call('addOptionValueInline');

    $red = ProductOptionValue::where('product_option_id', $option->id)->where('value', 'Red')->first();
    $blue = ProductOptionValue::where('product_option_id', $option->id)->where('value', 'Blue')->first();

    $volt->call('removeOptionValueInline', $blue->id);

    expect(ProductOptionValue::find($blue->id))->toBeNull()
        ->and($volt->get('options.0.values'))->toBe([$red->id]);
});

test('removeOptionValueInline is blocked when the value is attached to variants', function () {
    [$user, $store] = skuUser();

    $option = ProductOption::create([
        'store_id' => $store->id,
        'name' => 'Size',
        'type' => ProductOptionInputType::SELECT->value,
    ]);

    $value = ProductOptionValue::create([
        'store_id' => $store->id,
        'product_option_id' => $option->id,
        'value' => 'S',
        'sort_order' => 0,
    ]);

    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Used Value Product',
        'slug' => 'used-value-product',
        'sku' => 'USED-S',
        'type' => 'variable',
        'price' => 100,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'S',
        'sku' => 'USED-S-0',
        'price' => 100,
        'stock' => 1,
    ]);

    $variant->optionValues()->sync([$value->id => ['product_option_id' => $option->id]]);

    $volt = Volt::test('merchant.products.form');

    $volt->set('has_variants', true)
        ->set('optionModalRow', 0)
        ->set('optionModalCreatedId', $option->id)
        ->set('options', [[
            'product_option_id' => $option->id,
            'type' => ProductOptionInputType::SELECT->value,
            'values' => [$value->id],
        ]])
        ->call('removeOptionValueInline', $value->id);

    expect(ProductOptionValue::find($value->id))->not->toBeNull()
        ->and($volt->get('options.0.values'))->toBe([$value->id]);

    $volt->assertDispatched('swal', type: 'error');
});

test('quickAddValue creates a value in the row option and auto-selects it', function () {
    [$user, $store] = skuUser();

    $option = ProductOption::create([
        'store_id' => $store->id,
        'name' => 'Size',
        'type' => ProductOptionInputType::SELECT->value,
    ]);

    $volt = Volt::test('merchant.products.form');

    $volt->set('has_variants', true)
        ->set('options', [[
            'product_option_id' => $option->id,
            'type' => ProductOptionInputType::SELECT->value,
            'values' => [],
        ]]);

    // An empty draft is a silent no-op: nothing is created.
    $volt->call('quickAddValue', 0);

    expect(ProductOptionValue::where('product_option_id', $option->id)->count())->toBe(0);

    $volt->set('quickValueDraft', 'XL')->call('quickAddValue', 0);

    $value = ProductOptionValue::where('product_option_id', $option->id)->where('value', 'XL')->first();

    expect($value)->not->toBeNull()
        ->and($volt->get('quickValueDraft'))->toBe('')
        ->and($volt->get('options.0.values'))->toBe([$value->id]);

    // valuesChanged runs for the row: the variant preview is rebuilt.
    $volt->assertCount('variants_preview', 1);
});

test('quickAddValue on a row without an option is ignored', function () {
    [$user, $store] = skuUser();

    $volt = Volt::test('merchant.products.form');

    $volt->set('has_variants', true)
        ->set('options', [[
            'product_option_id' => null,
            'type' => null,
            'values' => [],
        ]])
        ->set('quickValueDraft', 'Anything')
        ->call('quickAddValue', 0);

    expect($volt->get('options.0.values'))->toBe([])
        ->and(ProductOptionValue::count())->toBe(0);
});

test('editing a variable product never wipes an existing variant sku to null', function () {
    [$user, $store] = skuUser();

    [$product, $variants] = makeVariableProduct($store, 'keep-variant-sku');

    $variants[0]->update(['barcode' => 'BAR-KEEP-VARIANT-SKU-0']);
    $variants[1]->update(['barcode' => 'BAR-KEEP-VARIANT-SKU-1']);

    $volt = Volt::test('merchant.products.form', ['product' => $product]);

    // Preview rows rebuilt server-side carry a null/skeleton sku; save must
    // keep the stored codes instead of forcing UPDATE product_variants sku = null.
    $volt->set('variants_preview.0.sku', null)
        ->set('variants_preview.0.barcode', null)
        ->set('variants_preview.0.price', 6800)
        ->set('variants_preview.0.cost_price', 3500)
        ->set('variants_preview.0.stock', 25)
        ->set('variants_preview.1.sku', null);

    $volt->call('save');

    expect($variants[0]->fresh()->sku)->toBe('VAR-KEEP-VARIANT-SKU-0')
        ->and($variants[0]->fresh()->barcode)->toBe('BAR-KEEP-VARIANT-SKU-0')
        ->and($variants[0]->fresh()->price)->toBe('6800.00')
        ->and($variants[0]->fresh()->cost_price)->toBe('3500.00')
        ->and($variants[0]->fresh()->stock)->toBe(25)
        ->and($variants[1]->fresh()->sku)->toBe('VAR-KEEP-VARIANT-SKU-1')
        ->and($variants[1]->fresh()->barcode)->toBe('BAR-KEEP-VARIANT-SKU-1');

    // No new variants were created and none were deleted.
    expect($product->variants()->count())->toBe(2);
});

test('the options step renders the header new-option trigger and the searchable pickers', function () {
    [$user, $store] = skuUser();

    $option = ProductOption::create([
        'store_id' => $store->id,
        'name' => 'Size',
        'type' => ProductOptionInputType::SELECT->value,
    ]);

    $valueS = ProductOptionValue::create(['store_id' => $store->id, 'product_option_id' => $option->id, 'value' => 'S', 'sort_order' => 0]);
    $valueM = ProductOptionValue::create(['store_id' => $store->id, 'product_option_id' => $option->id, 'value' => 'M', 'sort_order' => 1]);

    $volt = Volt::test('merchant.products.form');

    $volt->set('has_variants', true)
        ->set('options', [[
            'product_option_id' => $option->id,
            'type' => ProductOptionInputType::SELECT->value,
            'values' => [$valueS->id, $valueM->id],
        ]]);

    $html = $volt->html();

    expect($html)->toContain(__('products.new_option'))
        ->and($html)->toContain('wire:click="openCreateOption"')
        ->and($html)->toContain('edz-select')
        ->and($html)->toContain('edz-multi-select')
        ->and($html)->toContain(__('products.select_values'))
        ->and($html)->toContain('wire:model="options.0.product_option_id"')
        ->and($html)->toContain('wire:model="options.0.values"')
        ->and($html)->toContain('wire:click="quickAddValue(0)"')
        ->and($html)->toContain('@keydown.enter.prevent="$wire.quickAddValue(0)"')
        ->and($html)->toContain(__('products.new_value'))
        ->and(str_contains($html, '<form wire:submit="quickAddValue'))
        ->toBeFalse();
});

test('unchecking variants while on the options step relocates to pricing and drops it from validated steps', function () {
    [$user, $store] = skuUser();

    [$product, $variants] = makeVariableProduct($store, 'toggle-off-variants');

    $volt = Volt::test('merchant.products.form', ['product' => $product]);

    $volt->assertSet('has_variants', true)
        ->call('goToStep', ProductWizardSteps::STEP_OPTIONS)
        ->assertSet('currentStep', ProductWizardSteps::STEP_OPTIONS);

    // Toggling off while standing on Options immediately relocates to Pricing.
    $volt->set('has_variants', false);

    $volt->assertSet('currentStep', ProductWizardSteps::STEP_PRICING)
        ->assertSet('validated_steps', [
            ProductWizardSteps::STEP_BASIC,
            ProductWizardSteps::STEP_IMAGES,
            ProductWizardSteps::STEP_PRICING,
            ProductWizardSteps::STEP_INVENTORY,
            ProductWizardSteps::STEP_REVIEW,
        ])
        ->assertSet('options', [])
        ->assertSet('variants_preview', []);

    // A hidden step is not merely locked — jumping to it is a silent no-op.
    $volt->call('goToStep', ProductWizardSteps::STEP_OPTIONS)
        ->assertSet('currentStep', ProductWizardSteps::STEP_PRICING)
        ->assertNotDispatched('swal');

    // Re-enabling brings the Options tab back, freely reachable again without
    // re-validating already-passed steps.
    $volt->set('has_variants', true)
        ->call('goToStep', ProductWizardSteps::STEP_OPTIONS)
        ->assertSet('currentStep', ProductWizardSteps::STEP_OPTIONS);

    expect(in_array(ProductWizardSteps::STEP_OPTIONS, $volt->instance()->validated_steps, true))->toBeTrue();
});