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

    public $showCreateModal;

    public $showEditModal;

    public $showProductPickerModal;

    public $showVariantPickerModal;

    public $editingOrderId;

    public $form;

    public $formProductResults;

    public $formProductView;

    public $formSelectedProduct;

    public $formSelectedItems;

    public $editProviders;

    public $editDesks;

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

    public function syncFormSelectedItems(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('syncFormSelectedItems'))->execute(...$arguments);
    }

    public function loadCities(string $stateId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('loadCities'))->execute(...$arguments);
    }

    public function openCreateModal(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('openCreateModal'))->execute(...$arguments);
    }

    public function loadProducts(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('loadProducts'))->execute(...$arguments);
    }

    public function selectProduct(string $productId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('selectProduct'))->execute(...$arguments);
    }

    public function backToProducts(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('backToProducts'))->execute(...$arguments);
    }

    public function addFormItem(string $variantId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('addFormItem'))->execute(...$arguments);
    }

    public function addFormItemByBarcode(string $code): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('addFormItemByBarcode'))->execute(...$arguments);
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

    public function updateFormItemPrice(int $index, $price): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('updateFormItemPrice'))->execute(...$arguments);
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

    public function updated($name)
    {
        $arguments = [static::$__context, $this, array_slice(func_get_args(), 1)];

        return (new Actions\CallPropertyHook('updated', $name))->execute(...$arguments);
    }

};