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

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    #[\Livewire\Attributes\Computed()]
    public function alerts()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('alerts'))->execute(...$arguments);
    }

    public function statusBadge(\App\Models\Products\ProductVariant $variant): array
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('statusBadge'))->execute(...$arguments);
    }

};