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

    public $riderView;

    public $storeId;

    public $statusList;

    public $labels;

    public $colors;

    public $riderStatusList;

    public $riderLabels;

    public $riderColors;

    public $carrier;

    public $showAddConfirmation;

    public $showAddRider;

    public $newConfirmationLabel;

    public $newConfirmationColor;

    public $newConfirmationLinkedTo;

    public $newRiderLabel;

    public $newRiderColor;

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

    public function loadRider(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('loadRider'))->execute(...$arguments);
    }

    public function resolve(string $key)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('resolve'))->execute(...$arguments);
    }

    public function resolveTracking(string $key)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('resolveTracking'))->execute(...$arguments);
    }

    public function confirmationOptions(): array
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('confirmationOptions'))->execute(...$arguments);
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

    public function setRiderView(string $view): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('setRiderView'))->execute(...$arguments);
    }

    public function saveChanges(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('saveChanges'))->execute(...$arguments);
    }

    public function saveRiderChanges(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('saveRiderChanges'))->execute(...$arguments);
    }

    public function moveStatus(string $key, int $direction): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('moveStatus'))->execute(...$arguments);
    }

    public function moveRiderStatus(string $key, int $direction): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('moveRiderStatus'))->execute(...$arguments);
    }

    public function addConfirmationStatus(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('addConfirmationStatus'))->execute(...$arguments);
    }

    public function addRiderStatus(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('addRiderStatus'))->execute(...$arguments);
    }

    public function deleteConfirmationStatus(string $key): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('deleteConfirmationStatus'))->execute(...$arguments);
    }

    public function deleteRiderStatus(string $key): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('deleteRiderStatus'))->execute(...$arguments);
    }

};