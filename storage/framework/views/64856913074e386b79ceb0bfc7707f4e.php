<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    use Livewire\Features\SupportFileUploads\WithFileUploads;

    public $product;

    public $name;

    public $slug;

    public $sku;

    public $barcode;

    public $brand_id;

    public $categories;

    public $unit;

    public $short_description;

    public $description;

    public $meta_title;

    public $meta_description;

    public $is_active;

    public $is_featured;

    public $has_variants;

    public $auto_generate_sku;

    public $auto_generate_barcode;

    public $price;

    public $compare_price;

    public $cost_price;

    public $min_order_qty;

    public $max_order_qty;

    public $stock;

    public $low_stock_threshold;

    public $options;

    public $options_changed;

    public $variants_preview;

    public $images;

    public $newImages;

    public $apply_all_price;

    public $apply_all_cost_price;

    public $apply_all_stock;

    public $apply_all_low_stock;

    public $currentStep;

    public function mount(?\App\Models\Products\Product $product = NULL): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    #[\Livewire\Attributes\Computed()]
    public function brands()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('brands'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function categoryOptions()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('categoryOptions'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function productOptions()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('productOptions'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function optionValuesByOption()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('optionValuesByOption'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function hasActiveSubscription()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('hasActiveSubscription'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function subscriptionStatus()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('subscriptionStatus'))->execute(...$arguments);
    }

    public function optionChanged(int $index, string $optionId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('optionChanged'))->execute(...$arguments);
    }

    public function valuesChanged(int $index): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('valuesChanged'))->execute(...$arguments);
    }

    public function addOption(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('addOption'))->execute(...$arguments);
    }

    public function removeOption(int $index): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('removeOption'))->execute(...$arguments);
    }

    public function removeImage(int $index): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('removeImage'))->execute(...$arguments);
    }

    public function removeNewImage(int $index): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('removeNewImage'))->execute(...$arguments);
    }

    public function applyAll(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('applyAll'))->execute(...$arguments);
    }

    public function nextStep(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('nextStep'))->execute(...$arguments);
    }

    public function prevStep(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('prevStep'))->execute(...$arguments);
    }

    public function goToStep(int $step): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('goToStep'))->execute(...$arguments);
    }

    public function save(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('save'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function wizardSteps()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('wizardSteps'))->execute(...$arguments);
    }

    public function updated($name)
    {
        $arguments = [static::$__context, $this, array_slice(func_get_args(), 1)];

        return (new Actions\CallPropertyHook('updated', $name))->execute(...$arguments);
    }

    
    protected function normalizeOptions(array $options): array
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('normalizeOptions'))->execute(...$arguments);
    }

    
    protected function rebuildPreview(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('rebuildPreview'))->execute(...$arguments);
    }

    
    protected function optionsChanged(): bool
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('optionsChanged'))->execute(...$arguments);
    }

    
    protected function syncExistingVariants(array $preview): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('syncExistingVariants'))->execute(...$arguments);
    }

    
    protected function fillPreviewFromExisting(array $preview): array
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('fillPreviewFromExisting'))->execute(...$arguments);
    }

    
    protected function stepRules(int $step): array
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('stepRules'))->execute(...$arguments);
    }

};