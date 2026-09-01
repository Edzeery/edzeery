<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public $template;

    public $sections;

    public $primary_color;

    public $secondary_color;

    public $font_family;

    public $section_content;

    public $picker_query;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function save(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('save'))->execute(...$arguments);
    }

    public function resetSection(string $key): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('resetSection'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function productsForPicker()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('productsForPicker'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function pickerOptions()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('pickerOptions'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function chosenPickerProduct()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('chosenPickerProduct'))->execute(...$arguments);
    }

};