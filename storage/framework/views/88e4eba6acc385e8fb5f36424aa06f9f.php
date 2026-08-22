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

    public $brand_id;

    public $is_active;

    public $is_featured;

    public $created_at;

    public $selected;

    public $select_all;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    #[\Livewire\Attributes\Computed()]
    public function products()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('products'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function brands()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('brands'))->execute(...$arguments);
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

    public function imageUrl(\App\Models\Products\Product $product): string
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('imageUrl'))->execute(...$arguments);
    }

    public function delete(\App\Models\Products\Product $product): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('delete'))->execute(...$arguments);
    }

    public function deleteSelected(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('deleteSelected'))->execute(...$arguments);
    }

    public function activateSelected(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('activateSelected'))->execute(...$arguments);
    }

    public function deactivateSelected(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('deactivateSelected'))->execute(...$arguments);
    }

    public function updated($name)
    {
        $arguments = [static::$__context, $this, array_slice(func_get_args(), 1)];

        return (new Actions\CallPropertyHook('updated', $name))->execute(...$arguments);
    }

};