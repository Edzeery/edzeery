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

    public $filters;

    public $orders;

    public $page;

    public $visibleColumns;

    public $allStatuses;

    public $allMembers;

    public $allStates;

    public $allCities;

    public $showCreateModal;

    public $showEditModal;

    public $editingOrderId;

    public $form;

    public $formProductSearch;

    public $formProductResults;

    public $statusChangeOrderId;

    public $statusChangeValue;

    public $expandedOrderId;

    public $showReassignModal;

    public $reassignOrderId;

    public $reassignMembershipId;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function loadColumnPreferences(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('loadColumnPreferences'))->execute(...$arguments);
    }

    public function saveColumnPreferences(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('saveColumnPreferences'))->execute(...$arguments);
    }

    public function getCurrentMembership(): ?\App\Models\Stores\Team\StoreMembership
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('getCurrentMembership'))->execute(...$arguments);
    }

    public function loadOrders(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('loadOrders'))->execute(...$arguments);
    }

    public function loadCities(string $stateId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('loadCities'))->execute(...$arguments);
    }

    public function setPage(int $page): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('setPage'))->execute(...$arguments);
    }

    public function setFilter(string $key, $value): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('setFilter'))->execute(...$arguments);
    }

    public function clearFilters(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('clearFilters'))->execute(...$arguments);
    }

    public function getExpandIcon(string $orderId): string
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('getExpandIcon'))->execute(...$arguments);
    }

    public function toggleStatusFilter(string $statusId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('toggleStatusFilter'))->execute(...$arguments);
    }

    public function toggleColumn(string $column): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('toggleColumn'))->execute(...$arguments);
    }

    public function transitionOrder(string $orderId, string $statusKey): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('transitionOrder'))->execute(...$arguments);
    }

    public function openCreateModal(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('openCreateModal'))->execute(...$arguments);
    }

    public function searchProducts(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('searchProducts'))->execute(...$arguments);
    }

    public function addFormItem(string $variantId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('addFormItem'))->execute(...$arguments);
    }

    public function removeFormItem(int $index): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('removeFormItem'))->execute(...$arguments);
    }

    public function updateFormItemQty(int $index, int $qty): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('updateFormItemQty'))->execute(...$arguments);
    }

    public function submitCreate(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('submitCreate'))->execute(...$arguments);
    }

    public function openEditModal(string $orderId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('openEditModal'))->execute(...$arguments);
    }

    public function submitEdit(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('submitEdit'))->execute(...$arguments);
    }

    public function refreshOrders(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('refreshOrders'))->execute(...$arguments);
    }

    public function toggleDetail(string $orderId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('toggleDetail'))->execute(...$arguments);
    }

    public function openReassignModal(string $orderId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('openReassignModal'))->execute(...$arguments);
    }

    public function submitReassign(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('submitReassign'))->execute(...$arguments);
    }

    public function deleteOrder(string $orderId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('deleteOrder'))->execute(...$arguments);
    }

    public function closeAllModals(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('closeAllModals'))->execute(...$arguments);
    }

    public function updated($name)
    {
        $arguments = [static::$__context, $this, array_slice(func_get_args(), 1)];

        return (new Actions\CallPropertyHook('updated', $name))->execute(...$arguments);
    }

};