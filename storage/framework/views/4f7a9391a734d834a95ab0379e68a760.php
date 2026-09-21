<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public $membership;

    public $search;

    public function mount(string $membershipId): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    #[\Livewire\Attributes\Computed()]
    public function assigned(): array
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('assigned'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function assignedIds(): array
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('assignedIds'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function availableProducts(): array
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('availableProducts'))->execute(...$arguments);
    }

    public function addProductScope(string $productId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('addProductScope'))->execute(...$arguments);
    }

    public function removeProductScope(string $productId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('removeProductScope'))->execute(...$arguments);
    }

    public function close(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('close'))->execute(...$arguments);
    }

};