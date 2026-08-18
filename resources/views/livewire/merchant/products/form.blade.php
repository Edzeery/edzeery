<?php

use App\Enums\Store\ProductOptionInputType;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Products\Product;
use App\Models\Products\ProductOption;
use App\Models\Products\ProductOptionValue;
use App\Services\ProductService;
use App\Support\VariantPreviewBuilder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use function Livewire\Volt\action;
use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\protect;
use function Livewire\Volt\rules;
use function Livewire\Volt\state;
use function Livewire\Volt\updated;
use function Livewire\Volt\uses;

uses([WithFileUploads::class]);

layout('components.layouts.store');

state([
    'product' => null,
    'name' => '',
    'slug' => '',
    'sku' => '',
    'barcode' => '',
    'brand_id' => '',
    'categories' => [],
    'unit' => '',
    'short_description' => '',
    'description' => '',
    'meta_title' => '',
    'meta_description' => '',
    'is_active' => true,
    'is_featured' => false,
    'has_variants' => false,
    'auto_generate_sku' => true,
    'auto_generate_barcode' => true,
    'price' => null,
    'compare_price' => null,
    'cost_price' => null,
    'stock' => null,
    'low_stock_threshold' => 5,
    'options' => [],
    'options_changed' => false,
    'variants_preview' => [],
    'images' => [],
    'newImages' => [],
    'apply_all_price' => null,
    'apply_all_cost_price' => null,
    'apply_all_stock' => null,
    'apply_all_low_stock' => null,
    'currentStep' => 1,
]);

mount(function (?Product $product = null): void {
    if ($product?->exists) {
        abort_unless($product->store_id === currentStoreId(), 404);
        abort_unless(canStore(StorePermissionEnum::PRODUCT_UPDATE->value), 403);

        $data = app(ProductService::class)->buildEditFormData($product);

        $this->product = $product;

        foreach (['name', 'slug', 'sku', 'barcode', 'short_description', 'description', 'unit', 'meta_title', 'meta_description', 'is_active', 'is_featured', 'has_variants', 'images', 'options', 'variants_preview'] as $key) {
            $this->{$key} = $data[$key] ?? $this->{$key};
        }

        $this->brand_id = $product->brand_id;
        $this->categories = $product->categories()->pluck('categories.id')->toArray();

        // Keep existing codes stable by default on edit.
        $this->auto_generate_sku = false;
        $this->auto_generate_barcode = false;

        // Simple-product pricing lives on the default variant.
        $single = $product->variants()->where('is_default', true)->first()
            ?? $product->variants()->first();

        if ($single) {
            $this->price = $single->price;
            $this->compare_price = $single->compare_price;
            $this->cost_price = $single->cost_price;
            $this->stock = $single->stock;
        }
    } else {
        abort_unless(canStore(StorePermissionEnum::PRODUCT_CREATE->value), 403);
    }
});

updated([
    'name' => function ($value): void {
        $this->slug = $this->slug ?: Str::slug($value);
    },
    'auto_generate_sku' => function ($value): void {
        if ($value) {
            $this->sku = '';
        }
    },
    'auto_generate_barcode' => function ($value): void {
        if ($value) {
            $this->barcode = '';
        }
    },
    'has_variants' => function ($value): void {
        if (! $value) {
            $this->options = [];
            $this->variants_preview = [];
            $this->options_changed = false;
        }
    },
]);

rules(fn () => [
    'name' => ['required', 'string', 'max:255'],
    'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('products', 'slug')->where('store_id', currentStoreId())->whereNull('deleted_at')->ignore($this->product?->id)],
    'sku' => ['nullable', 'string', 'max:255', Rule::unique('products', 'sku')->where('store_id', currentStoreId())->whereNull('deleted_at')->ignore($this->product?->id)],
    'barcode' => ['nullable', 'string', 'max:255', Rule::unique('products', 'barcode')->where('store_id', currentStoreId())->whereNull('deleted_at')->ignore($this->product?->id)],
    'brand_id' => ['nullable', 'string', 'max:255'],
    'unit' => ['nullable', 'string', 'max:50'],
    'short_description' => ['nullable', 'string', 'max:500'],
    'description' => ['nullable', 'string'],
    'meta_title' => ['nullable', 'string', 'max:255'],
    'meta_description' => ['nullable', 'string', 'max:500'],
    'is_active' => ['nullable', 'boolean'],
    'is_featured' => ['nullable', 'boolean'],
    'has_variants' => ['nullable', 'boolean'],
    'auto_generate_sku' => ['nullable', 'boolean'],
    'auto_generate_barcode' => ['nullable', 'boolean'],
    'price' => ['nullable', 'numeric', 'min:0'],
    'compare_price' => ['nullable', 'numeric', 'min:0'],
    'cost_price' => ['nullable', 'numeric', 'min:0'],
    'stock' => ['nullable', 'integer', 'min:0'],
    'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
]);

$brands = computed(fn () => Brand::query()
    ->where('store_id', currentStoreId())
    ->orderBy('name')
    ->pluck('name', 'id'));

$categoryOptions = computed(fn () => Category::query()
    ->where('store_id', currentStoreId())
    ->orderBy('name')
    ->get()
    ->pluck('full_name', 'id'));

$productOptions = computed(fn () => ProductOption::query()
    ->where('store_id', currentStoreId())
    ->orderBy('name')
    ->get(['id', 'name', 'type']));

$optionValuesByOption = computed(fn () => ProductOptionValue::query()
    ->where('store_id', currentStoreId())
    ->orderBy('value')
    ->get()
    ->groupBy('product_option_id'));

$normalizeOptions = protect(function (array $options): array {
    return collect($options)
        ->map(fn ($o) => [
            'product_option_id' => $o['product_option_id'] ?? null,
            'values' => collect($o['values'] ?? [])
                ->map(fn ($v) => (string) $v)
                ->sort()
                ->values()
                ->all(),
        ])
        ->sortBy('product_option_id')
        ->values()
        ->all();
});

$rebuildPreview = protect(function (): void {
    $this->variants_preview = VariantPreviewBuilder::fromOptions(
        collect($this->options)
            ->filter(fn ($o) => ($o['type'] ?? null) !== ProductOptionInputType::TEXT->value)
            ->values()
            ->all()
    );

    $this->options_changed = true;
});

$optionsChanged = protect(function (): bool {
    if (! $this->product) {
        return false;
    }

    $current = $this->normalizeOptions($this->options);

    $existing = collect($this->product->variants()->with('optionValues.option')->get())
        ->flatMap(fn ($v) => $v->optionValues)
        ->groupBy(fn ($value) => $value->option->id)
        ->map(fn ($group, $optionId) => [
            'product_option_id' => $optionId,
            'values' => $group->pluck('id')->unique()->values()->all(),
        ])
        ->values()
        ->toArray();

    return $current !== $this->normalizeOptions($existing);
});

$syncExistingVariants = protect(function (array $preview): void {
    $fields = ['sku', 'barcode', 'price', 'compare_price', 'cost_price', 'stock', 'low_stock_threshold', 'weight', 'length', 'width', 'height', 'is_active'];

    $existing = $this->product->variants()->get()
        ->map(fn ($v) => [
            'id' => $v->id,
            'combo' => implode('-', $v->optionValues()->pluck('product_option_values.id')->map(fn ($id) => (string) $id)->sort()->values()->all()),
        ])
        ->keyBy('combo');

    foreach ($preview as $row) {
        $combo = implode('-', collect($row['value_ids'] ?? [])
            ->map(fn ($id) => (string) $id)
            ->sort()
            ->values()
            ->all());

        $variant = $this->product->variants()->find($existing->get($combo)['id'] ?? null);

        if (! $variant) {
            continue;
        }

        $variant->update(
            collect($fields)
                ->mapWithKeys(fn ($f) => [$f => $row[$f] ?? null])
                ->all()
        );
    }
});

$fillPreviewFromExisting = protect(function (array $preview): array {
    if (! $this->product) {
        return $preview;
    }

    $fields = ['sku', 'barcode', 'price', 'compare_price', 'cost_price', 'stock', 'low_stock_threshold', 'weight', 'length', 'width', 'height', 'is_active'];

    $existing = $this->product->variants()->with('optionValues')->get()->keyBy('id');

    $comboOf = fn ($v) => implode('-', $v->optionValues->pluck('id')->map(fn ($id) => (string) $id)->sort()->values()->all());

    $byCombo = $existing->mapWithKeys(fn ($v) => [$comboOf($v) => $v]);

    return collect($preview)->map(function (array $row) use ($byCombo, $fields) {
        $combo = implode('-', collect($row['value_ids'] ?? [])
            ->map(fn ($id) => (string) $id)
            ->sort()
            ->values()
            ->all());

        $variant = $byCombo->get($combo);

        if (! $variant) {
            if (empty($row['sku'])) {
                $row['sku'] = \App\Support\SkuGenerator::variant(currentStore()->slug, $this->product->slug, $row['sku_parts'] ?? []);
            }
            if (empty($row['barcode'])) {
                $row['barcode'] = \App\Services\BarcodeService::variant(null);
            }

            return $row;
        }

        foreach ($fields as $f) {
            if (($row[$f] ?? null) === null || $row[$f] === '') {
                $row[$f] = $variant->{$f};
            }
        }

        return $row;
    })->values()->all();
});

$optionChanged = action(function (int $index, string $optionId): void {
    $option = ProductOption::where('store_id', currentStoreId())->find($optionId);

    $this->options[$index]['product_option_id'] = $option?->id;
    $this->options[$index]['type'] = $option?->type->value;
    $this->options[$index]['values'] = [];

    $this->rebuildPreview();
});

$valuesChanged = action(function (int $index): void {
    $this->options[$index]['values'] = array_values(array_filter($this->options[$index]['values'] ?? []));

    $this->rebuildPreview();
});

$addOption = action(function (): void {
    $this->options[] = [
        'product_option_id' => null,
        'type' => null,
        'values' => [],
    ];
});

$removeOption = action(function (int $index): void {
    unset($this->options[$index]);
    $this->options = array_values($this->options);

    $this->rebuildPreview();
});

$removeImage = action(function (int $index): void {
    unset($this->images[$index]);
    $this->images = array_values($this->images);
});

$removeNewImage = action(function (int $index): void {
    unset($this->newImages[$index]);
    $this->newImages = array_values($this->newImages);
});

$applyAll = action(function (): void {
    $fields = ['price', 'cost_price', 'stock', 'low_stock_threshold'];

    foreach ($fields as $field) {
        $value = $this->{'apply_all_'.$field};

        if ($value === null || $value === '') {
            continue;
        }

        foreach ($this->variants_preview as $index => $variant) {
            $this->variants_preview[$index][$field] = $value;
        }

        $this->{'apply_all_'.$field} = null;
    }
});

$stepRules = protect(function (int $step): array {
    return match ($step) {
        1 => [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('products', 'slug')->where('store_id', currentStoreId())->whereNull('deleted_at')->ignore($this->product?->id)],
            'brand_id' => ['nullable', 'string', 'max:255'],
            'categories' => ['nullable', 'array'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
        ],
        2 => [
            'price' => ['nullable', 'numeric', 'min:0'],
            'compare_price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
        ],
        3 => [],
        4 => [
            'stock' => ['nullable', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products', 'sku')->where('store_id', currentStoreId())->whereNull('deleted_at')->ignore($this->product?->id)],
            'barcode' => ['nullable', 'string', 'max:255', Rule::unique('products', 'barcode')->where('store_id', currentStoreId())->whereNull('deleted_at')->ignore($this->product?->id)],
        ],
        default => [],
    };
});

$nextStep = action(function (): void {
    $this->validate($this->stepRules($this->currentStep));
    $this->currentStep = min(5, $this->currentStep + 1);
});

$prevStep = action(function (): void {
    $this->currentStep = max(1, $this->currentStep - 1);
});

$goToStep = action(function (int $step): void {
    if ($step >= 1 && $step <= 5) {
        $this->currentStep = $step;
    }
});

$save = action(function (): void {
    $data = $this->validate();

    $data['has_variants'] = (bool) $this->has_variants;
    $data['auto_generate_sku'] = (bool) $this->auto_generate_sku;
    $data['auto_generate_barcode'] = (bool) $this->auto_generate_barcode;
    $data['is_active'] = (bool) $this->is_active;
    $data['is_featured'] = (bool) $this->is_featured;
    $data['brand_id'] = $this->brand_id ?: null;
    $data['primary_category_id'] = $this->categories[0] ?? null;
    $data['options'] = array_values($this->options);
    $data['variants_preview'] = $this->has_variants ? $this->variants_preview : [];
    $data['images'] = collect($this->images)
        ->concat(collect($this->newImages)->map(fn ($upload) => $upload->store('products', 'public')))
        ->all();

    $service = app(ProductService::class);

    try {
        if ($this->product) {
            if ($data['has_variants']) {
                $data['options_changed'] = $this->optionsChanged();

                if ($data['options_changed']) {
                    $data['variants_preview'] = $this->fillPreviewFromExisting($data['variants_preview']);
                }
            } else {
                $data['options_changed'] = false;
            }

            $product = $service->update($this->product, $data);

            if ($data['has_variants'] && ! $data['options_changed']) {
                $this->syncExistingVariants($data['variants_preview']);
            }

            $message = __('products.product_updated');
        } else {
            $product = $service->create(currentStore(), $data);
            $message = __('products.product_created');
        }
    } catch (\DomainException $e) {
        session()->flash('merchant.error', $e->getMessage());

        return;
    }

    $product->categories()->sync($this->categories);

    session()->flash('merchant.saved', $message);
    $this->redirectRoute('merchant.products.edit', [currentStore(), $product]);
});

$wizardSteps = computed(fn () => [
    ['label' => __('products.step_basic_info'), 'icon' => 'information-circle'],
    ['label' => __('products.step_pricing'), 'icon' => 'currency-dollar'],
    ['label' => __('products.step_options'), 'icon' => 'adjustments'],
    ['label' => __('products.step_inventory'), 'icon' => 'archive-box'],
    ['label' => __('products.step_review'), 'icon' => 'check-circle'],
]);
?>

<div x-data="{ step: @entangle('currentStep') }">
    <div class="edz-page-head">
        <div>
            <h1 class="edz-page-head__title">{{ $product ? __('products.edit_product') : __('products.new_product') }}</h1>
            <p class="edz-page-head__subtitle">{{ __('products.subtitle', ['store' => currentStore()?->name]) }}</p>
        </div>
        <a href="{{ route('merchant.products.index', currentStore()) }}" wire:navigate
           class="edz-btn edz-btn--ghost">{{ __('products.cancel') }}</a>
    </div>

    <x-merchant.wizard-steps :steps="$this->wizardSteps" :currentStep="$currentStep" />

    @if (session('merchant.error'))
        <div class="mb-6 rounded-lg border border-danger-200 bg-danger-50 px-4 py-3 text-sm text-danger-700 dark:border-danger-800 dark:bg-danger-950 dark:text-danger-300">
            {{ session('merchant.error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-danger-200 bg-danger-50 px-4 py-3 text-sm text-danger-700 dark:border-danger-800 dark:bg-danger-950 dark:text-danger-300">
            <p class="font-semibold">{{ __('products.fix_errors') }}</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form wire:submit="save">
        {{-- Step 1: Basic Information --}}
        <div x-show="step === 1" x-transition.opacity>
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <div class="space-y-6 xl:col-span-2">
                    <div class="edz-card">
                        <div class="edz-card__header">
                            <div>
                                <h2 class="edz-card__title">{{ __('products.basic_information') }}</h2>
                                <p class="text-sm text-ink-400">{{ __('products.basic_information_hint') }}</p>
                            </div>
                        </div>
                        <div class="edz-card__body grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="edz-field md:col-span-2">
                                <label class="edz-field__label" for="product-name">{{ __('products.name') }}</label>
                                <input id="product-name" type="text" class="edz-input @error('name') edz-input--error @enderror"
                                       wire:model.live="name" placeholder="e.g. Premium Cotton T-Shirt">
                                @error('name')
                                    <span class="edz-field__error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="edz-field md:col-span-2">
                                <label class="edz-field__label" for="product-slug">{{ __('products.slug') }}</label>
                                <input id="product-slug" type="text" class="edz-input @error('slug') edz-input--error @enderror"
                                       wire:model="slug" placeholder="premium-cotton-t-shirt">
                                @error('slug')
                                    <span class="edz-field__error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="edz-field">
                                <label class="edz-field__label" for="product-brand">{{ __('products.brand') }}</label>
                                <select id="product-brand" class="edz-select" wire:model="brand_id">
                                    <option value="">{{ __('products.no_brand') }}</option>
                                    @foreach ($this->brands as $id => $brandName)
                                        <option value="{{ $id }}" @selected((string) $brand_id === (string) $id)>{{ $brandName }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="edz-field">
                                <label class="edz-field__label" for="product-unit">{{ __('products.unit') }}</label>
                                <input id="product-unit" type="text" class="edz-input" wire:model="unit"
                                       placeholder="e.g. pcs, kg, box">
                            </div>

                            <div class="edz-field md:col-span-2">
                                <label class="edz-field__label" for="product-categories">{{ __('products.categories') }}</label>
                                <select id="product-categories" class="edz-select" wire:model="categories" multiple size="4">
                                    @foreach ($this->categoryOptions as $id => $categoryName)
                                        <option value="{{ $id }}" @selected(in_array($id, $categories))>{{ $categoryName }}</option>
                                    @endforeach
                                </select>
                                <p class="edz-field__hint">{{ __('products.categories_hint') }}</p>
                            </div>

                            <div class="edz-field md:col-span-2">
                                <label class="edz-field__label" for="product-short-description">{{ __('products.short_description') }}</label>
                                <textarea id="product-short-description" class="edz-textarea" wire:model="short_description"
                                          rows="2" placeholder="{{ __('products.short_description_placeholder') }}"></textarea>
                            </div>

                            <div class="edz-field md:col-span-2">
                                <label class="edz-field__label" for="product-description">{{ __('products.description') }}</label>
                                <textarea id="product-description" class="edz-textarea" wire:model="description"
                                          rows="6" placeholder="{{ __('products.description_placeholder') }}"></textarea>
                            </div>

                            <label class="flex items-center gap-2 text-sm font-medium text-ink">
                                <input type="checkbox" wire:model.live="is_active" class="h-4 w-4 rounded border-surface-border text-brand-600">
                                {{ __('products.active') }}
                            </label>
                            <label class="flex items-center gap-2 text-sm font-medium text-ink">
                                <input type="checkbox" wire:model.live="is_featured" class="h-4 w-4 rounded border-surface-border text-brand-600">
                                {{ __('products.featured') }}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="edz-card">
                        <div class="edz-card__header">
                            <div>
                                <h2 class="edz-card__title">{{ __('products.images') }}</h2>
                                <p class="text-sm text-ink-400">{{ __('products.images_hint') }}</p>
                            </div>
                        </div>
                        <div class="edz-card__body space-y-4">
                            @if (count($images) || count($newImages))
                                <div class="grid grid-cols-2 gap-3">
                                    @foreach ($images as $index => $path)
                                        <div class="relative overflow-hidden rounded-lg border border-surface-border">
                                            <img src="{{ Storage::disk('public')->url($path) }}" alt=""
                                                 class="h-24 w-full object-cover">
                                            <button type="button" wire:click="removeImage({{ $index }})"
                                                    class="absolute right-1 top-1 rounded-full bg-surface/90 px-2 py-0.5 text-xs font-semibold text-danger-600 hover:bg-danger-600 hover:text-white">
                                                &times;
                                            </button>
                                        </div>
                                    @endforeach
                                    @foreach ($newImages as $index => $upload)
                                        <div class="relative overflow-hidden rounded-lg border border-surface-border">
                                            <img src="{{ $upload->temporaryUrl() }}" alt=""
                                                 class="h-24 w-full object-cover">
                                            <button type="button" wire:click="removeNewImage({{ $index }})"
                                                    class="absolute right-1 top-1 rounded-full bg-surface/90 px-2 py-0.5 text-xs font-semibold text-danger-600 hover:bg-danger-600 hover:text-white">
                                                &times;
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <input type="file" wire:model="newImages" multiple accept="image/*"
                                   class="block w-full text-sm text-ink-soft file:mr-3 file:rounded-md file:border-0 file:bg-brand-600 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-brand-700">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 2: Pricing --}}
        <div x-show="step === 2" x-transition.opacity>
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <div class="space-y-6 xl:col-span-2">
                    <div class="edz-card">
                        <div class="edz-card__header">
                            <div>
                                <h2 class="edz-card__title">{{ __('products.pricing_stock') }}</h2>
                                <p class="text-sm text-ink-400">{{ __('products.pricing_stock_hint') }}</p>
                            </div>
                        </div>
                        <div class="edz-card__body">
                            <div class="mb-4">
                                <label class="flex items-center gap-2 text-sm font-medium text-ink">
                                    <input type="checkbox" wire:model.live="has_variants" class="h-4 w-4 rounded border-surface-border text-brand-600">
                                    {{ __('products.has_variants') }}
                                </label>
                                <p class="mt-1 text-xs text-ink-muted">{{ __('products.product_type_hint') }}</p>
                            </div>

                            @if (! $has_variants)
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    <div class="edz-field">
                                        <label class="edz-field__label" for="product-price">{{ __('products.price') }}</label>
                                        <input id="product-price" type="number" step="0.01" min="0" class="edz-input"
                                               wire:model="price" placeholder="0.00">
                                    </div>
                                    <div class="edz-field">
                                        <label class="edz-field__label" for="product-compare-price">{{ __('products.compare_at_price') }}</label>
                                        <input id="product-compare-price" type="number" step="0.01" min="0" class="edz-input"
                                               wire:model="compare_price" placeholder="0.00">
                                    </div>
                                    <div class="edz-field">
                                        <label class="edz-field__label" for="product-cost-price">{{ __('products.cost_price') }}</label>
                                        <input id="product-cost-price" type="number" step="0.01" min="0" class="edz-input"
                                               wire:model="cost_price" placeholder="0.00">
                                    </div>
                                    <div class="rounded-lg border border-surface-border bg-surface-secondary/60 p-3 sm:col-span-2 lg:col-span-3">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ __('products.profit_margin') }}</p>
                                        @php
                                            $sp = $price !== null && $price !== '' ? (float) $price : null;
                                            $sc = $cost_price !== null && $cost_price !== '' ? (float) $cost_price : null;
                                            $sProfit = $sp !== null && $sc !== null ? $sp - $sc : null;
                                            $sMargin = $sProfit !== null && $sp > 0 ? round(($sProfit / $sp) * 100, 1) : null;
                                        @endphp
                                        <p class="mt-1 text-sm font-semibold text-ink">
                                            {{ $sProfit !== null ? number_format($sProfit, 2) : '—' }}
                                            <span class="text-ink-muted">({{ $sMargin !== null ? $sMargin.'%' : '—' }})</span>
                                        </p>
                                    </div>
                                </div>
                            @else
                                <div class="rounded-lg border border-surface-border bg-surface-secondary/60 p-4 text-sm text-ink-muted">
                                    {{ __('products.add_options_hint') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 3: Options & Variants --}}
        <div x-show="step === 3" x-transition.opacity>
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <div class="space-y-6 xl:col-span-2">
                    @if ($has_variants)
                        <div class="edz-card">
                            <div class="edz-card__header">
                                <div>
                                    <h2 class="edz-card__title">{{ __('products.options') }}</h2>
                                    <p class="text-sm text-ink-400">{{ __('products.options_hint') }}</p>
                                </div>
                                <button type="button" wire:click="addOption" class="edz-btn edz-btn--secondary edz-btn--sm">{{ __('products.add_option') }}</button>
                            </div>
                            <div class="edz-card__body space-y-4">
                                @forelse ($options as $index => $option)
                                    <div class="grid grid-cols-1 gap-3 rounded-lg border border-surface-border p-4 md:grid-cols-2">
                                        <div class="edz-field">
                                            <label class="edz-field__label" for="option-{{ $index }}">{{ __('products.option') }}</label>
                                            <select id="option-{{ $index }}" class="edz-select"
                                                    wire:change="optionChanged({{ $index }}, $event.target.value)">
                                                <option value="">{{ __('products.select_option') }}</option>
                                                @foreach ($this->productOptions as $opt)
                                                    <option value="{{ $opt->id }}"
                                                            @selected(($option['product_option_id'] ?? null) === $opt->id)>
                                                        {{ $opt->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="edz-field">
                                            <label class="edz-field__label" for="option-values-{{ $index }}">{{ __('products.values') }}</label>
                                            @if (($option['type'] ?? null) === ProductOptionInputType::TEXT->value)
                                                <div class="rounded-md border border-surface-border px-3 py-2 text-sm text-ink-muted">
                                                    {{ __('products.text_options_hint') }}
                                                </div>
                                            @elseif (! empty($option['product_option_id']))
                                                <select id="option-values-{{ $index }}" class="edz-select" multiple size="3"
                                                        wire:model="options.{{ $index }}.values"
                                                        wire:change="valuesChanged({{ $index }})">
                                                    @foreach ($this->optionValuesByOption->get($option['product_option_id'], collect()) as $value)
                                                        <option value="{{ $value->id }}"
                                                                @selected(in_array($value->id, $option['values'] ?? []))>
                                                            {{ $value->value }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <div class="rounded-md border border-surface-border px-3 py-2 text-sm text-ink-muted">
                                                    {{ __('products.select_option_to_configure') }}
                                                </div>
                                            @endif
                                        </div>

                                        <div class="md:col-span-2">
                                            <button type="button" wire:click="removeOption({{ $index }})"
                                                    class="text-sm font-semibold text-danger-600 hover:text-danger-700">
                                                {{ __('products.remove_option') }}
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-ink-muted">{{ __('products.no_options_yet') }}</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="edz-card">
                            <div class="edz-card__header">
                                <div>
                                    <h2 class="edz-card__title">{{ __('products.variants') }}</h2>
                                    <p class="text-sm text-ink-400">{{ __('products.variants_hint') }}</p>
                                </div>
                            </div>
                            <div class="edz-card__body">
                                @if (count($variants_preview) === 0)
                                    <p class="text-sm text-ink-muted">{{ __('products.add_options_hint') }}</p>
                                @else
                                    <div class="mb-4 grid grid-cols-1 gap-3 rounded-lg border border-surface-border bg-surface-secondary/50 p-4 sm:grid-cols-2 lg:grid-cols-5">
                                        <div class="edz-field">
                                            <label class="edz-field__label" for="apply-price">{{ __('products.price') }}</label>
                                            <input id="apply-price" type="number" step="0.01" min="0" class="edz-input"
                                                   wire:model="apply_all_price" placeholder="0.00">
                                        </div>
                                        <div class="edz-field">
                                            <label class="edz-field__label" for="apply-cost">{{ __('products.cost') }}</label>
                                            <input id="apply-cost" type="number" step="0.01" min="0" class="edz-input"
                                                   wire:model="apply_all_cost_price" placeholder="0.00">
                                        </div>
                                        <div class="edz-field">
                                            <label class="edz-field__label" for="apply-stock">{{ __('products.stock') }}</label>
                                            <input id="apply-stock" type="number" min="0" class="edz-input"
                                                   wire:model="apply_all_stock" placeholder="0">
                                        </div>
                                        <div class="edz-field">
                                            <label class="edz-field__label" for="apply-low-stock">{{ __('products.low_stock') }}</label>
                                            <input id="apply-low-stock" type="number" min="0" class="edz-input"
                                                   wire:model="apply_all_low_stock" placeholder="5">
                                        </div>
                                        <div class="flex items-end">
                                            <button type="button" wire:click="applyAll" class="edz-btn edz-btn--secondary w-full">{{ __('products.apply_to_all') }}</button>
                                        </div>
                                    </div>

                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="border-b border-surface-border text-start text-xs uppercase tracking-wider text-ink-muted">
                                                    <th class="px-3 py-2 text-start font-semibold">{{ __('products.variant') }}</th>
                                                    <th class="px-3 py-2 text-start font-semibold">{{ __('products.price') }}</th>
                                                    <th class="px-3 py-2 text-start font-semibold">{{ __('products.cost') }}</th>
                                                    <th class="px-3 py-2 text-start font-semibold">{{ __('products.compare') }}</th>
                                                    <th class="px-3 py-2 text-start font-semibold">{{ __('products.stock') }}</th>
                                                    <th class="px-3 py-2 text-start font-semibold">{{ __('products.low_stock') }}</th>
                                                    <th class="px-3 py-2 text-start font-semibold">{{ __('products.profit') }}</th>
                                                    <th class="px-3 py-2 text-start font-semibold">{{ __('products.margin') }}</th>
                                                    <th class="px-3 py-2 text-start font-semibold">{{ __('products.active') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($variants_preview as $index => $variant)
                                                    <tr class="border-b border-surface-border last:border-0">
                                                        <td class="max-w-48 px-3 py-2 align-top text-xs font-medium text-ink-soft">
                                                            {{ $variant['labels'] ?? $variant['name'] ?? '—' }}
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <input type="number" step="0.01" min="0" class="edz-input min-w-24 px-2 py-1 text-xs"
                                                                   wire:model.blur="variants_preview.{{ $index }}.price">
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <input type="number" step="0.01" min="0" class="edz-input min-w-24 px-2 py-1 text-xs"
                                                                   wire:model.blur="variants_preview.{{ $index }}.cost_price">
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <input type="number" step="0.01" min="0" class="edz-input min-w-24 px-2 py-1 text-xs"
                                                                   wire:model.blur="variants_preview.{{ $index }}.compare_price">
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <input type="number" min="0" class="edz-input min-w-20 px-2 py-1 text-xs"
                                                                   wire:model.blur="variants_preview.{{ $index }}.stock">
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <input type="number" min="0" class="edz-input min-w-20 px-2 py-1 text-xs"
                                                                   wire:model.blur="variants_preview.{{ $index }}.low_stock_threshold">
                                                        </td>
                                                        @php
                                                            $vp = $variant['price'] ?? null;
                                                            $vc = $variant['cost_price'] ?? null;
                                                            $vProfit = ($vp !== null && $vp !== '' && $vc !== null && $vc !== '')
                                                                ? (float) $vp - (float) $vc
                                                                : null;
                                                            $vMargin = $vProfit !== null && (float) $vp > 0
                                                                ? round(($vProfit / (float) $vp) * 100, 1)
                                                                : null;
                                                        @endphp
                                                        <td class="px-3 py-2 whitespace-nowrap text-xs text-ink-soft">
                                                            {{ $vProfit !== null ? number_format($vProfit, 2) : '—' }}
                                                        </td>
                                                        <td class="px-3 py-2 whitespace-nowrap text-xs text-ink-soft">
                                                            {{ $vMargin !== null ? $vMargin.'%' : '—' }}
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <input type="checkbox" class="h-4 w-4 rounded border-surface-border text-brand-600"
                                                                   wire:model="variants_preview.{{ $index }}.is_active">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="edz-card">
                            <div class="edz-card__body">
                                <div class="rounded-lg border border-surface-border bg-surface-secondary/60 p-8 text-center">
                                    <p class="text-sm text-ink-muted">{{ __('products.add_options_hint') }}</p>
                                    <p class="mt-1 text-xs text-ink-muted">{{ __('products.product_type_hint') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Step 4: Inventory --}}
        <div x-show="step === 4" x-transition.opacity>
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <div class="space-y-6 xl:col-span-2">
                    @if (! $has_variants)
                        <div class="edz-card">
                            <div class="edz-card__header">
                                <div>
                                    <h2 class="edz-card__title">{{ __('products.pricing_stock') }}</h2>
                                    <p class="text-sm text-ink-400">{{ __('products.pricing_stock_hint') }}</p>
                                </div>
                            </div>
                            <div class="edz-card__body grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="edz-field">
                                    <label class="edz-field__label" for="product-stock">{{ __('products.stock') }}</label>
                                    <input id="product-stock" type="number" min="0" class="edz-input"
                                           wire:model="stock" placeholder="0">
                                </div>
                                <div class="edz-field">
                                    <label class="edz-field__label" for="product-low-stock">{{ __('products.low_stock_threshold') }}</label>
                                    <input id="product-low-stock" type="number" min="0" class="edz-input"
                                           wire:model="low_stock_threshold" placeholder="5">
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="edz-card">
                            <div class="edz-card__body">
                                <div class="rounded-lg border border-surface-border bg-surface-secondary/60 p-4 text-sm text-ink-muted">
                                    {{ __('products.variants_hint') }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="edz-card">
                        <div class="edz-card__header">
                            <div>
                                <h2 class="edz-card__title">{{ __('products.codes') }}</h2>
                                <p class="text-sm text-ink-400">{{ __('products.codes_hint') }}</p>
                            </div>
                        </div>
                        <div class="edz-card__body grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="edz-field">
                                <label class="edz-field__label" for="product-sku">{{ __('products.sku') }}</label>
                                <input id="product-sku" type="text" class="edz-input @error('sku') edz-input--error @enderror"
                                       wire:model="sku" @disabled($auto_generate_sku) placeholder="{{ __('products.auto_generated') }}">
                                @error('sku')
                                    <span class="edz-field__error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="edz-field">
                                <label class="edz-field__label" for="product-barcode">{{ __('products.barcode') }}</label>
                                <input id="product-barcode" type="text" class="edz-input @error('barcode') edz-input--error @enderror"
                                       wire:model="barcode" @disabled($auto_generate_barcode) placeholder="{{ __('products.auto_generated') }}">
                                @error('barcode')
                                    <span class="edz-field__error">{{ $message }}</span>
                                @enderror
                            </div>

                            <label class="flex items-center gap-2 text-sm font-medium text-ink">
                                <input type="checkbox" wire:model.live="auto_generate_sku" class="h-4 w-4 rounded border-surface-border text-brand-600">
                                {{ __('products.auto_generate_sku') }}
                            </label>
                            <label class="flex items-center gap-2 text-sm font-medium text-ink">
                                <input type="checkbox" wire:model.live="auto_generate_barcode" class="h-4 w-4 rounded border-surface-border text-brand-600">
                                {{ __('products.auto_generate_barcode') }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 5: Review & Publish --}}
        <div x-show="step === 5" x-transition.opacity>
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <div class="space-y-6 xl:col-span-2">
                    <div class="edz-card">
                        <div class="edz-card__header">
                            <div>
                                <h2 class="edz-card__title">{{ __('products.review_section') }}</h2>
                                <p class="text-sm text-ink-400">{{ __('products.review_section_desc') }}</p>
                            </div>
                        </div>
                        <div class="edz-card__body space-y-3 text-sm">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ __('products.product_name_label') }}</p>
                                    <p class="mt-0.5 text-ink">{{ $name ?: '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ __('products.slug_label') }}</p>
                                    <p class="mt-0.5 text-ink">{{ $slug ?: '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ __('products.brand_label') }}</p>
                                    <p class="mt-0.5 text-ink">{{ $this->brands[$brand_id] ?? __('products.no_brand') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ __('products.categories_label') }}</p>
                                    <p class="mt-0.5 text-ink">{{ count($categories) ? count($categories).' selected' : '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ __('products.price_label') }}</p>
                                    <p class="mt-0.5 text-ink">{{ $price !== null ? number_format((float) $price, 2) : '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ __('products.status_label') }}</p>
                                    <p class="mt-0.5 text-ink">{{ $is_active ? __('products.active_label') : '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ __('products.options_label') }}</p>
                                    <p class="mt-0.5 text-ink">{{ $has_variants ? count($variants_preview).' variants' : __('products.simple_product') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ __('products.images_label') }}</p>
                                    <p class="mt-0.5 text-ink">{{ count($images) + count($newImages) }} images</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="edz-card">
                        <div class="edz-card__header">
                            <h2 class="edz-card__title">{{ __('products.search_engine') }}</h2>
                        </div>
                        <div class="edz-card__body grid grid-cols-1 gap-4">
                            <div class="edz-field">
                                <label class="edz-field__label" for="product-meta-title">{{ __('products.meta_title') }}</label>
                                <input id="product-meta-title" type="text" class="edz-input" wire:model="meta_title">
                            </div>
                            <div class="edz-field">
                                <label class="edz-field__label" for="product-meta-description">{{ __('products.meta_description') }}</label>
                                <textarea id="product-meta-description" class="edz-textarea" wire:model="meta_description"
                                          rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <div class="mt-6 flex items-center justify-between">
            <button type="button"
                    x-show="step > 1"
                    @click="$wire.prevStep()"
                    class="edz-btn edz-btn--ghost">
                &larr;                 {{ __('buttons.previous') }}
            </button>
            <div x-show="step <= 1"></div>

            <div class="flex items-center gap-2">
                <a href="{{ route('merchant.products.index', currentStore()) }}" wire:navigate
                   class="edz-btn edz-btn--ghost">{{ __('products.cancel') }}</a>

                <button type="button"
                        x-show="step < 5"
                        @click="$wire.nextStep()"
                        class="edz-btn edz-btn--primary">
                    {{ __('buttons.next') }} &rarr;
                </button>

                <button type="submit"
                        x-show="step === 5"
                        class="edz-btn edz-btn--primary">
                    {{ __('products.save_product') }}
                </button>
            </div>
        </div>
    </form>
</div>
