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

    public $type;

    public $status;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    #[\Livewire\Attributes\Computed()]
    public function debts()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('debts'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function totalOwed()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('totalOwed'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function totalOwing()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('totalOwing'))->execute(...$arguments);
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

    public function delete(\App\Models\Finance\Debt $debt): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('delete'))->execute(...$arguments);
    }

    public function formatAmount(float $amount): string
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('formatAmount'))->execute(...$arguments);
    }

};