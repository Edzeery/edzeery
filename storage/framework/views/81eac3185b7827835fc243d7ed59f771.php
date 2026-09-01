<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public $returnTab;

    public $trackings;

    public $scanCode;

    public $processTrackingId;

    public $processResult;

    public $processNotes;

    public $showProcessModal;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function loadTrackings(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('loadTrackings'))->execute(...$arguments);
    }

    public function filteredTrackings(): array
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('filteredTrackings'))->execute(...$arguments);
    }

    public function verifyScan(string $code): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('verifyScan'))->execute(...$arguments);
    }

    public function openProcessModal(string $trackingId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('openProcessModal'))->execute(...$arguments);
    }

    public function submitProcess(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('submitProcess'))->execute(...$arguments);
    }

    public function requeue(string $trackingId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('requeue'))->execute(...$arguments);
    }

};