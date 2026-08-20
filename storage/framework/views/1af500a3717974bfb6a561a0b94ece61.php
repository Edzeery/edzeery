<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public $search;

    public $category_id;

    public $sortBy;

    public $sections;

    public $section_content;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function addToCart(string $variantId)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('addToCart'))->execute(...$arguments);
    }

};