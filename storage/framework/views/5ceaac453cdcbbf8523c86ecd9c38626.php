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

    public $typeFilter;

    public $viewingId;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    #[\Livewire\Attributes\Computed()]
    public function typeOptions()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('typeOptions'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function movements()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('movements'))->execute(...$arguments);
    }

    public function toggleView(\App\Models\InventoryMovement $movement): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('toggleView'))->execute(...$arguments);
    }

    public function clearVariantFilter(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('clearVariantFilter'))->execute(...$arguments);
    }

};