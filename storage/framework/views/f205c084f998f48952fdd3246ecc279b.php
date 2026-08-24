<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public $search;

    public $creating;

    public $editingId;

    public $name;

    public $email;

    public $password;

    public $country_id;

    public $state_id;

    public $city_id;

    public $store_role;

    public $isActive;

    public $permissions;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    #[\Livewire\Attributes\Computed()]
    public function members()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('members'))->execute(...$arguments);
    }

    public function canCreate()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('canCreate'))->execute(...$arguments);
    }

    public function canModify(\App\Models\Stores\Team\StoreMembership $membership)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('canModify'))->execute(...$arguments);
    }

    public function memberRoleName(\App\Models\Stores\Team\StoreMembership $membership): string
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('memberRoleName'))->execute(...$arguments);
    }

    public function openCreate(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('openCreate'))->execute(...$arguments);
    }

    public function closeCreate(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('closeCreate'))->execute(...$arguments);
    }

    public function openEdit(\App\Models\Stores\Team\StoreMembership $membership): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('openEdit'))->execute(...$arguments);
    }

    public function closeEdit(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('closeEdit'))->execute(...$arguments);
    }

    public function saveNew(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('saveNew'))->execute(...$arguments);
    }

    public function saveEdit(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('saveEdit'))->execute(...$arguments);
    }

    public function toggleActive(\App\Models\Stores\Team\StoreMembership $membership): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('toggleActive'))->execute(...$arguments);
    }

    public function remove(\App\Models\Stores\Team\StoreMembership $membership): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('remove'))->execute(...$arguments);
    }

    public function updatedCountryId(?string $value): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('updatedCountryId'))->execute(...$arguments);
    }

    public function updatedStateId(?string $value): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('updatedStateId'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function states()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('states'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function cities()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('cities'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function allPermissions()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('allPermissions'))->execute(...$arguments);
    }

};