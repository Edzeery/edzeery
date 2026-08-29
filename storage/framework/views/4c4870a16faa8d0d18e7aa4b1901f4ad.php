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

    public $adjustingId;

    public $adjust_quantity;

    public $adjust_reason;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    #[\Livewire\Attributes\Computed()]
    public function stats(): array
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('stats'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function inventories()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('inventories'))->execute(...$arguments);
    }

    public function canAdjust()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('canAdjust'))->execute(...$arguments);
    }

    public function stockBadge(\App\Models\Products\ProductVariant $variant): array
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('stockBadge'))->execute(...$arguments);
    }

    public function toggleAdjust(\App\Models\Products\ProductVariant $variant): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('toggleAdjust'))->execute(...$arguments);
    }

    public function movementsUrl(\App\Models\Products\ProductVariant $variant): string
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('movementsUrl'))->execute(...$arguments);
    }

    public function adjust(\App\Models\Products\ProductVariant $variant): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('adjust'))->execute(...$arguments);
    }

};