<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public $stopdeskPoints;

    public $states;

    public $cities;

    public $showStopdeskModal;

    public $editingStopdeskId;

    public $providers;

    public $stopdeskForm;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function loadData(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('loadData'))->execute(...$arguments);
    }

    public function watchState(string $stateId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('watchState'))->execute(...$arguments);
    }

    public function openStopdeskModal(?string $stopdeskId = NULL): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('openStopdeskModal'))->execute(...$arguments);
    }

    public function saveStopdesk(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('saveStopdesk'))->execute(...$arguments);
    }

    public function deleteStopdesk(string $id): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('deleteStopdesk'))->execute(...$arguments);
    }

};