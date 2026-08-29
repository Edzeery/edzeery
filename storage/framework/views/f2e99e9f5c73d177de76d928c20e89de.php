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

    public $filterProducts;

    public $perPage;

    public $allStatuses;

    public $allMembers;

    public $allStates;

    public $allCities;

    public $allProviders;

    public $selectedOrders;

    public $selectAll;

    public $showBulkBar;

    public $showTrash;

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

    public function setPage(int $page): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('setPage'))->execute(...$arguments);
    }

    public function setPerPage(int $perPage): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('setPerPage'))->execute(...$arguments);
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

    public function toggleSelectAll(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('toggleSelectAll'))->execute(...$arguments);
    }

    public function toggleSelectOrder(string $orderId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('toggleSelectOrder'))->execute(...$arguments);
    }

    public function clearSelection(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('clearSelection'))->execute(...$arguments);
    }

    public function bulkAssignAgent(?string $membershipId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('bulkAssignAgent'))->execute(...$arguments);
    }

    public function bulkSendToCarrier(?string $providerId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('bulkSendToCarrier'))->execute(...$arguments);
    }

    public function bulkDelete(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('bulkDelete'))->execute(...$arguments);
    }

    public function toggleTrash(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('toggleTrash'))->execute(...$arguments);
    }

    public function restoreOrder(string $orderId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('restoreOrder'))->execute(...$arguments);
    }

    public function restoreAll(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('restoreAll'))->execute(...$arguments);
    }

    public function forceDeleteAll(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('forceDeleteAll'))->execute(...$arguments);
    }

    public function loadFilterCities(string $stateId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('loadFilterCities'))->execute(...$arguments);
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

    public function loadFilterProducts(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('loadFilterProducts'))->execute(...$arguments);
    }

    public function applyProductNameFilter(string $query): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('applyProductNameFilter'))->execute(...$arguments);
    }

    public function clearProductFilter(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('clearProductFilter'))->execute(...$arguments);
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

    public function refreshOrders()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('refreshOrders'))->execute(...$arguments);
    }

    public function updated($name)
    {
        $arguments = [static::$__context, $this, array_slice(func_get_args(), 1)];

        return (new Actions\CallPropertyHook('updated', $name))->execute(...$arguments);
    }

    public function getListeners()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\ResolveListeners)->execute(...$arguments);
    }

    public function ordersRefreshedHandler()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallListener('orders-refreshed'))->execute(...$arguments);
    }

};