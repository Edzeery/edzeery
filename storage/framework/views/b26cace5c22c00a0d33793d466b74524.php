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

    public $email;

    public $phone;

    public $address;

    public $birthdate;

    public $country;

    public $countries;

    public function mount(\App\Domains\Account\Actions\Profile\GetProfileAction $action): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function updateProfile(\App\Http\Requests\Account\Profile\UpdateProfileRequest $request, \App\Domains\Account\Services\AccountService $service): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('updateProfile'))->execute(...$arguments);
    }

};