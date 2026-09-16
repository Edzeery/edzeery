<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public $tab;

    public $confirmationView;

    public $storeId;

    public $statusList;

    public $labels;

    public $colors;

    public $carrier;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function loadConfirmation(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('loadConfirmation'))->execute(...$arguments);
    }

    public function resolve(string $key)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('resolve'))->execute(...$arguments);
    }

    public function colorOptions(): array
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('colorOptions'))->execute(...$arguments);
    }

    public function setTab(string $tab): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('setTab'))->execute(...$arguments);
    }

    public function carrierRows(): array
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('carrierRows'))->execute(...$arguments);
    }

    public function setConfirmationView(string $view): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('setConfirmationView'))->execute(...$arguments);
    }

    public function saveChanges(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('saveChanges'))->execute(...$arguments);
    }

    public function moveStatus(string $key, int $direction): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('moveStatus'))->execute(...$arguments);
    }

};