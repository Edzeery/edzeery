<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public $productId;

    public $quantities;

    public $direct;

    public function mount(string $productId, bool $direct = false): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    #[\Livewire\Attributes\Computed()]
    public function product(): ?\App\Models\Products\Product
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('product'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function tracksInventory(): bool
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('tracksInventory'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function allowsBackorder(): bool
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('allowsBackorder'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function limits(): array
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('limits'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function orderGroups(): \Illuminate\Support\Collection
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('orderGroups'))->execute(...$arguments);
    }

    public function resolveLines(): array
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('resolveLines'))->execute(...$arguments);
    }

    public function clearQuantities(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('clearQuantities'))->execute(...$arguments);
    }

    public function addAllToCart(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('addAllToCart'))->execute(...$arguments);
    }

    public function buyNowFromMatrix()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('buyNowFromMatrix'))->execute(...$arguments);
    }

};