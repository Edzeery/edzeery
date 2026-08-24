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

    public $selectedVariant;

    public $sections;

    public $section_content;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function selectVariant(string $variantId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('selectVariant'))->execute(...$arguments);
    }

    public function addToCart(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('addToCart'))->execute(...$arguments);
    }

};