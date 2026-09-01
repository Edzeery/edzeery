<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    use Livewire\WithPagination;

    public $search;

    public $selected;

    public $select_all;

    public $creating;

    public $editingId;

    public $adjustingId;

    public $historyId;

    public $product_id;

    public $sku;

    public $price;

    public $compare_price;

    public $cost_price;

    public $adjust_quantity;

    public $adjust_type;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    #[\Livewire\Attributes\Computed()]
    public function variants()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('variants'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function products()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('products'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function manualTypes()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('manualTypes'))->execute(...$arguments);
    }

    public function canCreate()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('canCreate'))->execute(...$arguments);
    }

    public function canUpdate()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('canUpdate'))->execute(...$arguments);
    }

    public function canDelete()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('canDelete'))->execute(...$arguments);
    }

    public function stockBadge(\App\Models\Products\ProductVariant $variant): array
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('stockBadge'))->execute(...$arguments);
    }

    public function openCreate(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('openCreate'))->execute(...$arguments);
    }

    public function beginEdit(\App\Models\Products\ProductVariant $variant): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('beginEdit'))->execute(...$arguments);
    }

    public function toggleAdjust(\App\Models\Products\ProductVariant $variant): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('toggleAdjust'))->execute(...$arguments);
    }

    public function toggleHistory(\App\Models\Products\ProductVariant $variant): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('toggleHistory'))->execute(...$arguments);
    }

    public function movements(\App\Models\Products\ProductVariant $variant): \Illuminate\Database\Eloquent\Collection
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('movements'))->execute(...$arguments);
    }

    public function save(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('save'))->execute(...$arguments);
    }

    public function generateSku(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('generateSku'))->execute(...$arguments);
    }

    public function cancelForm(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('cancelForm'))->execute(...$arguments);
    }

    public function applyStock(\App\Models\Products\ProductVariant $variant): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('applyStock'))->execute(...$arguments);
    }

    public function delete(\App\Models\Products\ProductVariant $variant): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('delete'))->execute(...$arguments);
    }

    public function deleteSelected(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('deleteSelected'))->execute(...$arguments);
    }

};