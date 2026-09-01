<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public $language;

    public $theme;

    public $timezone;

    public $date_format;

    public $email_notifications;

    public $order_notifications;

    public $stock_notifications;

    public $marketing_notifications;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function saveSettings(\App\Http\Requests\Account\Settings\UpdateSettingsRequest $request, \App\Domains\Account\Services\AccountService $service): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('saveSettings'))->execute(...$arguments);
    }

};