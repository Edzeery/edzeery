<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public $name;

    public $phone;

    public $email;

    public $state_id;

    public $city_id;

    public $address;

    public $delivery_type;

    public $payment_method;

    public $notes;

    public $selectedStopdesk;

    public $selectedProvider;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    #[\Livewire\Attributes\Computed()]
    public function availableProviders(): \Illuminate\Support\Collection
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('availableProviders'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function hasActiveProviders(): bool
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('hasActiveProviders'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function paymentMethods(): array
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('paymentMethods'))->execute(...$arguments);
    }

    public function officesForSelection(): \Illuminate\Support\Collection
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('officesForSelection'))->execute(...$arguments);
    }

    public function formatOfficeOptions(\Illuminate\Support\Collection $stopdesks): \Illuminate\Support\Collection
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('formatOfficeOptions'))->execute(...$arguments);
    }

    public function citiesForSelection(): \Illuminate\Support\Collection
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('citiesForSelection'))->execute(...$arguments);
    }

    public function quoteShipping(float $subtotal, array $shippingProductIds): array
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('quoteShipping'))->execute(...$arguments);
    }

    public function submitOrder()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('submitOrder'))->execute(...$arguments);
    }

    public function updated($name)
    {
        $arguments = [static::$__context, $this, array_slice(func_get_args(), 1)];

        return (new Actions\CallPropertyHook('updated', $name))->execute(...$arguments);
    }

};