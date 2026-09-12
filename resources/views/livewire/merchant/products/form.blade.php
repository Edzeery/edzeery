<?php

use App\Domains\Product\Support\ProductWizardSteps;
use App\Domains\User\Services\SubscriptionGuardService;
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
    'min_order_qty' => null,
    'max_order_qty' => null,
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
    'validated_steps' => [],
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
        $this->min_order_qty = $product->min_order_qty;
        $this->max_order_qty = $product->max_order_qty;

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

        // Existing products already passed every step's validation on save -
        // free navigation stays fully unlocked when editing.
        $this->validated_steps = ProductWizardSteps::ids();
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

$hasActiveSubscription = computed(fn () => app(SubscriptionGuardService::class)->hasActiveSubscription());

$subscriptionStatus = computed(fn () => app(SubscriptionGuardService::class)->statusLabel());

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

    $dbVariants = $this->product->variants()->get()->values();

    $existing = $dbVariants
        ->map(fn ($v) => [
            'id' => $v->id,
            'combo' => implode('-', $v->optionValues()->pluck('product_option_values.id')->map(fn ($id) => (string) $id)->sort()->values()->all()),
        ]);

    $hasDuplicateCombos = $existing->pluck('combo')->duplicates()->isNotEmpty();

    if ($hasDuplicateCombos) {
        $matched = [];

        foreach ($preview as $index => $row) {
            $variant = $dbVariants[$index] ?? null;

            if (! $variant) {
                continue;
            }

            $dirty = collect($fields)
                ->mapWithKeys(fn ($f) => [$f => $row[$f] ?? null])
                ->filter(fn ($value, $key) => $variant->{$key} != $value)
                ->all();

            if (! empty($dirty)) {
                $variant->update($dirty);
            }

            $matched[] = $variant->id;
        }
    } else {
        $existingByCombo = $existing->keyBy('combo');
        $matched = [];

        foreach ($preview as $row) {
            $combo = implode('-', collect($row['value_ids'] ?? [])
                ->map(fn ($id) => (string) $id)
                ->sort()
                ->values()
                ->all());

            $variant = $this->product->variants()->find($existingByCombo->get($combo)['id'] ?? null);

            if (! $variant) {
                continue;
            }

            $dirty = collect($fields)
                ->mapWithKeys(fn ($f) => [$f => $row[$f] ?? null])
                ->filter(fn ($value, $key) => $variant->{$key} != $value)
                ->all();

            if (! empty($dirty)) {
                $variant->update($dirty);
            }

            $matched[] = $variant->id;
        }
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
    $fieldMap = [
        'price' => 'apply_all_price',
        'cost_price' => 'apply_all_cost_price',
        'stock' => 'apply_all_stock',
        'low_stock_threshold' => 'apply_all_low_stock',
    ];

    foreach ($fieldMap as $field => $prop) {
        $value = $this->{$prop};

        if ($value === null || $value === '') {
            continue;
        }

        foreach ($this->variants_preview as $index => $variant) {
            $this->variants_preview[$index][$field] = $value;
        }

        $this->{$prop} = null;
    }
});

$stepRules = protect(function (int $step): array {
    return ProductWizardSteps::rulesFor($step, [
        'store_id' => currentStoreId(),
        'product_id' => $this->product?->id,
        'min_order_qty' => $this->min_order_qty,
    ]);
});

$nextStep = action(function (): void {
    $v = \Illuminate\Support\Facades\Validator::make(
        $this->all(),
        $this->stepRules($this->currentStep)
    );
    $v->validate();
    if (! in_array($this->currentStep, $this->validated_steps, true)) {
        $this->validated_steps[] = $this->currentStep;
    }
    $this->currentStep = min(ProductWizardSteps::count(), $this->currentStep + 1);
});

$prevStep = action(function (): void {
    $this->currentStep = max(1, $this->currentStep - 1);
});

$isStepUnlocked = protect(function (int $step): bool {
    if (! ProductWizardSteps::isExistingStep($step)) {
        return false;
    }

    foreach (ProductWizardSteps::ids() as $id) {
        if ($id >= $step) {
            break;
        }
        if (! in_array($id, $this->validated_steps, true)) {
            return false;
        }
    }

    return true;
});

$goToStep = action(function (int $step): void {
    if (! ProductWizardSteps::isExistingStep($step) || $step === $this->currentStep) {
        return;
    }

    if (! $this->isStepUnlocked($step)) {
        $this->dispatch('swal', type: 'info', title: __('products.step_locked_title'), text: __('products.step_locked_text'));
        return;
    }

    if (! in_array($step, $this->validated_steps, true)) {
        $v = \Illuminate\Support\Facades\Validator::make($this->all(), $this->stepRules($step));
        $v->validate();
        $this->validated_steps[] = $step;
    }

    $this->currentStep = $step;
});

$save = action(function (): void {
    if (! app(SubscriptionGuardService::class)->hasActiveSubscription()) {
        $this->dispatch('swal', type: 'warning', title: __('messages.subscription_required'), text: __('messages.subscription_expired_text'));
        return;
    }

    $allRules = [
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
        'min_order_qty' => ['nullable', 'integer', 'min:1', 'max:100000'],
        'max_order_qty' => ['nullable', 'integer', 'min:1', 'max:100000'],
    ];

    $v = \Illuminate\Support\Facades\Validator::make($this->all(), $allRules);
    $v->validate();
    $data = $v->validated();

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
        $this->dispatch('swal', type: 'error', title: $e->getMessage());

        return;
    }

    $product->categories()->sync($this->categories);

    $this->dispatch('swal', type: 'success', title: $message);
    $this->redirectRoute('merchant.products.edit', [currentStore(), $product]);
});

$lockedSteps = computed(fn () => collect(ProductWizardSteps::ids())
    ->filter(fn (int $id) => ! $this->isStepUnlocked($id))
    ->values()
    ->all());

$wizardSteps = computed(fn () => array_values(ProductWizardSteps::all()));
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

    <x-merchant.wizard-steps :steps="$this->wizardSteps" :currentStep="$currentStep" :lockedSteps="$this->lockedSteps" />

    @if (! $this->hasActiveSubscription)
        <div class="mb-6 rounded-lg border border-warning-border bg-warning-surface px-5 py-4 text-sm text-warning-800">
            <div class="flex items-start gap-3">
                <x-edz.icon name="exclamation-triangle" class="mt-0.5 h-5 w-5 flex-shrink-0 text-warning-500" />
                <div>
                    <p class="font-semibold">{{ __('messages.subscription_required') }}</p>
                    <p class="mt-1">{{ __('messages.subscription_expired_text') }}</p>
                    <a href="{{ route('account.billing') }}" wire:navigate
                       class="mt-2 inline-flex items-center gap-1 text-sm font-medium text-warning-fg underline hover:text-warning-fg-strong">
                        {{ __('messages.go_to_billing') }}
                        <x-edz.icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-danger-border bg-danger-surface px-4 py-3 text-sm text-danger-fg-strong">
            <p class="font-semibold">{{ __('products.fix_errors') }}</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form wire:submit="save" x-data="edzDirty()">
        {{-- Wizard steps render from ProductWizardSteps; each partial owns its own x-show guard --}}
        @foreach ($this->wizardSteps as $wizardStep)
            @include($wizardStep['partial'])
        @endforeach

        {{-- Navigation --}}
        <div class="mt-6 flex items-center justify-between">
            <button type="button"
                    x-show="step > 1"
                    @click="$wire.prevStep()"
                    class="edz-btn edz-btn--ghost">
                <x-edz.icon name="arrow-left" class="h-4 w-4" />
                {{ __('buttons.previous') }}
            </button>
            <div x-show="step <= 1"></div>

            <div class="flex items-center gap-2">
                <a href="{{ route('merchant.products.index', currentStore()) }}" wire:navigate
                   class="edz-btn edz-btn--ghost">{{ __('products.cancel') }}</a>

                <button type="button"
                        x-show="step < {{ ProductWizardSteps::count() }}"
                        @click="$wire.nextStep()"
                        wire:loading.attr="disabled"
                        :disabled="! $wire.hasActiveSubscription"
                        class="edz-btn edz-btn--primary"
                        :class="{ 'opacity-50 cursor-not-allowed': ! $wire.hasActiveSubscription }">
                    <span wire:loading.remove>{{ __('buttons.next') }}</span>
                    <span wire:loading class="inline-flex items-center gap-1">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" class="opacity-75"></path></svg>
                        {{ __('buttons.processing') }}
                    </span>
                    <x-edz.icon name="arrow-right" class="h-4 w-4" />
                </button>

                <button type="submit"
                        x-show="step === {{ ProductWizardSteps::LAST_STEP }}"
                        wire:loading.attr="disabled"
                        :disabled="! $wire.hasActiveSubscription"
                        class="edz-btn edz-btn--primary"
                        :class="{ 'opacity-50 cursor-not-allowed': ! $wire.hasActiveSubscription }">
                    <span wire:loading.remove>{{ __('products.save_product') }}</span>
                    <span wire:loading class="inline-flex items-center gap-1">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" class="opacity-75"></path></svg>
                        {{ __('buttons.processing') }}
                    </span>
                </button>
            </div>
        </div>
    </form>
</div>
