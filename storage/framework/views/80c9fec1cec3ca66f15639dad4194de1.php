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

    public $platforms;

    public $standaloneCarriers;

    public $showProviderModal;

    public $editingProviderId;

    public $providerForm;

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

    public function carrierOption(\App\Domains\Shipping\Models\Carrier $c): array
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('carrierOption'))->execute(...$arguments);
    }

    public function providerPlatformOptions(): array
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('providerPlatformOptions'))->execute(...$arguments);
    }

    public function providerCarrierOptions(): array
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('providerCarrierOptions'))->execute(...$arguments);
    }

    public function selectedCarrier(): ?array
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('selectedCarrier'))->execute(...$arguments);
    }

    public function selectProviderPlatform(string $platformId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('selectProviderPlatform'))->execute(...$arguments);
    }

    public function selectProviderCarrier(string $carrierId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('selectProviderCarrier'))->execute(...$arguments);
    }

    public function openProviderModal(?string $providerId = NULL): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('openProviderModal'))->execute(...$arguments);
    }

    public function saveProvider(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('saveProvider'))->execute(...$arguments);
    }

    public function toggleProviderActive(string $id): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('toggleProviderActive'))->execute(...$arguments);
    }

    public function deleteProvider(string $id): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('deleteProvider'))->execute(...$arguments);
    }

};