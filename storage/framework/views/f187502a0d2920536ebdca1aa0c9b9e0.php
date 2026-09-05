<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public $filters;

    public $shipments;

    public $page;

    public $perPage;

    public $allProviders;

    public $stats;

    public $drawerOrderId;

    public $drawerTracking;

    public $drawerStatusHistories;

    public $drawerEvents;

    public $canViewDrawerEvents;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function loadShipments(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('loadShipments'))->execute(...$arguments);
    }

    public function refresh(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('refresh'))->execute(...$arguments);
    }

    public function resetFilters(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('resetFilters'))->execute(...$arguments);
    }

    public function openDrawer(string $orderId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('openDrawer'))->execute(...$arguments);
    }

    public function closeDrawer(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('closeDrawer'))->execute(...$arguments);
    }

    public function membership()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('membership'))->execute(...$arguments);
    }

    public function trackingTransition(string $action): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('trackingTransition'))->execute(...$arguments);
    }

    public function trackingAction(string $orderId, string $action): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('trackingAction'))->execute(...$arguments);
    }

};