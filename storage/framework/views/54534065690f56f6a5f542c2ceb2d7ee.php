<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public $providers;

    public $states;

    public $cities;

    public $selectedProviderId;

    public $pointsByState;

    public $stateRows;

    public $syncCandidates;

    public $selectedSyncProviderId;

    public $syncing;

    public $showOfficesPopup;

    public $popupStateId;

    public $popupStateName;

    public $popupOffices;

    public $showStopdeskModal;

    public $editingStopdeskId;

    public $stopdeskForm;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function providerHasIntegration(\App\Domains\Shipping\Models\ShippingProvider $provider): bool
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('providerHasIntegration'))->execute(...$arguments);
    }

    public function loadData(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('loadData'))->execute(...$arguments);
    }

    public function loadPoints(string $providerId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('loadPoints'))->execute(...$arguments);
    }

    public function selectProvider(string $providerId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('selectProvider'))->execute(...$arguments);
    }

    public function syncStopdesk(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('syncStopdesk'))->execute(...$arguments);
    }

    public function watchState(string $stateId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('watchState'))->execute(...$arguments);
    }

    public function openOfficesPopup(string $stateKey): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('openOfficesPopup'))->execute(...$arguments);
    }

    public function closeOfficesPopup(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('closeOfficesPopup'))->execute(...$arguments);
    }

    public function openStopdeskModal(?string $stopdeskId = NULL, ?string $defaultStateId = NULL): void
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