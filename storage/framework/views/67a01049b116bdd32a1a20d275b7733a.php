<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    use Livewire\Features\SupportFileUploads\WithFileUploads;

    use Livewire\WithPagination;

    public $search;

    public $is_active;

    public $selected;

    public $select_all;

    public $creating;

    public $editingId;

    public $parent_id;

    public $name;

    public $slug;

    public $logo;

    public $isActive;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    #[\Livewire\Attributes\Computed()]
    public function categories()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('categories'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function parentOptions()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('parentOptions'))->execute(...$arguments);
    }

    public function canCreate()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('canCreate'))->execute(...$arguments);
    }

    public function canUpdate()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('canUpdate'))->execute(...$arguments);
    }

    public function canDelete()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('canDelete'))->execute(...$arguments);
    }

    public function logoUrl(\App\Models\Category $category): string
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('logoUrl'))->execute(...$arguments);
    }

    public function openCreate(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('openCreate'))->execute(...$arguments);
    }

    public function beginEdit(\App\Models\Category $category): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('beginEdit'))->execute(...$arguments);
    }

    public function toggleActive(\App\Models\Category $category): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('toggleActive'))->execute(...$arguments);
    }

    public function save(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('save'))->execute(...$arguments);
    }

    public function isDescendant(string $id, string $possibleParentId): bool
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('isDescendant'))->execute(...$arguments);
    }

    public function cancelForm(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('cancelForm'))->execute(...$arguments);
    }

    public function delete(\App\Models\Category $category): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('delete'))->execute(...$arguments);
    }

    public function deleteSelected(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('deleteSelected'))->execute(...$arguments);
    }

    public function updated($name)
    {
        $arguments = [static::$__context, $this, array_slice(func_get_args(), 1)];

        return (new Actions\CallPropertyHook('updated', $name))->execute(...$arguments);
    }

};