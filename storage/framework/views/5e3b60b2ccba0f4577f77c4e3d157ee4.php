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

    public $name;

    public $type;

    public $activeOptionId;

    public $newValue;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    #[\Livewire\Attributes\Computed()]
    public function options()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('options'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function typeOptions()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('typeOptions'))->execute(...$arguments);
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

    public function openCreate(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('openCreate'))->execute(...$arguments);
    }

    public function beginEdit(\App\Models\Products\ProductOption $option): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('beginEdit'))->execute(...$arguments);
    }

    public function toggleActive(\App\Models\Products\ProductOption $option): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('toggleActive'))->execute(...$arguments);
    }

    public function save(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('save'))->execute(...$arguments);
    }

    public function cancelForm(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('cancelForm'))->execute(...$arguments);
    }

    public function optionValues(\App\Models\Products\ProductOption $option): \Illuminate\Database\Eloquent\Collection
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('optionValues'))->execute(...$arguments);
    }

    public function addValue(\App\Models\Products\ProductOption $option): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('addValue'))->execute(...$arguments);
    }

    public function generateSizes(\App\Models\Products\ProductOption $option): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('generateSizes'))->execute(...$arguments);
    }

    public function deleteValue(\App\Models\Products\ProductOptionValue $value): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('deleteValue'))->execute(...$arguments);
    }

    public function delete(\App\Models\Products\ProductOption $option): void
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