<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public $tab;

    public $members;

    public $shifts;

    public $assignments;

    public $showShiftModal;

    public $editingShiftId;

    public $shiftForm;

    public $showAssignModal;

    public $assignForm;

    public $productSearch;

    public $assignProductNames;

    public $storeTimezone;

    public $onShiftNow;

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

    public function setTab(string $tab): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('setTab'))->execute(...$arguments);
    }

    public function onShiftTypeChange(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('onShiftTypeChange'))->execute(...$arguments);
    }

    public function openShiftModal(?string $shiftId = NULL): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('openShiftModal'))->execute(...$arguments);
    }

    public function toggleShiftDay(int $day): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('toggleShiftDay'))->execute(...$arguments);
    }

    public function saveShift(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('saveShift'))->execute(...$arguments);
    }

    public function deleteShift(string $shiftId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('deleteShift'))->execute(...$arguments);
    }

    public function toggleShiftActive(string $shiftId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('toggleShiftActive'))->execute(...$arguments);
    }

    public function benefitsMembership(string $membershipId): bool
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('benefitsMembership'))->execute(...$arguments);
    }

    public function openAssignModal(?string $membershipId = NULL): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('openAssignModal'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function searchAssignProducts(): array
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('searchAssignProducts'))->execute(...$arguments);
    }

    public function toggleAssignProduct(string $productId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('toggleAssignProduct'))->execute(...$arguments);
    }

    public function saveAssignments(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('saveAssignments'))->execute(...$arguments);
    }

    public function removeAssignment(string $assignmentId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('removeAssignment'))->execute(...$arguments);
    }

};