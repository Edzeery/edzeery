<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public $stores;

    public $user;

    public $subscription;

    public $maxStores;

    public $storeCount;

    public $canCreate;

    public $canViewBilling;

    public $effectiveUsage;

    public $isUnlimited;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function selectStore(string $slug): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('selectStore'))->execute(...$arguments);
    }

    public function getMembershipRole($user, \App\Models\Stores\Store $store): \App\Enums\Store\StoreRoleEnum
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('getMembershipRole'))->execute(...$arguments);
    }

};