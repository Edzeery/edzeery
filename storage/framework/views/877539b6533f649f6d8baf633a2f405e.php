<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    use App\Livewire\Concerns\DistributionQueueConcern;

    public $tab;

    public $confirmationQueue;

    public $trackingQueue;

    public $confirmationCount;

    public $trackingCount;

    public $reassignOpen;

    public $reassignKind;

    public $reassignId;

    public $reassignMembershipId;

    public $reassignCandidates;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function setTab(string $tab): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('setTab'))->execute(...$arguments);
    }

    public function refresh(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('refresh'))->execute(...$arguments);
    }

    public function openReassignModal(string $id): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('openReassignModal'))->execute(...$arguments);
    }

    public function submitReassign(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('submitReassign'))->execute(...$arguments);
    }

};