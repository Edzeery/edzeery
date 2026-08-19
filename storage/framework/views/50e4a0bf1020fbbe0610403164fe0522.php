<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    use Livewire\WithFileUploads;

    public $step;

    public $name;

    public $slug;

    public $description;

    public $logo;

    public $cover;

    public $currency;

    public $currency_symbol;

    public $language;

    public $inventory_tracking;

    public $guest_checkout;

    public $meta_title;

    public $meta_description;

    public $meta_keywords;

    public $primary_color;

    public $secondary_color;

    public $font_family;

    public $landing_template;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function updatedName(string $value): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('updatedName'))->execute(...$arguments);
    }

    public function nextStep(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('nextStep'))->execute(...$arguments);
    }

    public function prevStep(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('prevStep'))->execute(...$arguments);
    }

    public function createStore(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('createStore'))->execute(...$arguments);
    }

};