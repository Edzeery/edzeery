<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public $product;

    public function mount(\App\Models\Products\Product $product): void
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
    public function images()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('images'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function singleVariant()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('singleVariant'))->execute(...$arguments);
    }

    public function imageUrl(string $path): string
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('imageUrl'))->execute(...$arguments);
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

    public function delete(\App\Models\Products\Product $product): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('delete'))->execute(...$arguments);
    }

};