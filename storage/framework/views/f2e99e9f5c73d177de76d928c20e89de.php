<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public $statusFilter;

    public $search;

    public $orders;

    public $page;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function loadOrders()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('loadOrders'))->execute(...$arguments);
    }

    public function setPage(int $page)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('setPage'))->execute(...$arguments);
    }

    public function confirm(string $orderId)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('confirm'))->execute(...$arguments);
    }

    public function prepare(string $orderId)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('prepare'))->execute(...$arguments);
    }

    public function ship(string $orderId)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('ship'))->execute(...$arguments);
    }

    public function deliver(string $orderId)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('deliver'))->execute(...$arguments);
    }

    public function cancel(string $orderId)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('cancel'))->execute(...$arguments);
    }

    public function refreshOrders()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('refreshOrders'))->execute(...$arguments);
    }

};