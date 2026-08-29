<?php
use App\Domains\Order\Models\UserColumnPreference;
use App\Domains\Order\Services\OrderAssignmentService;
use App\Domains\Order\Services\OrderService;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Customers\Customer;
use App\Models\Locations\City;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Status;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Facades\Validator;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\on;
use function Livewire\Volt\state;
use function Livewire\Volt\updated;

layout('components.layouts.store');

state([
    'search' => '',
    'filters' => [
        'number' => '',
        'customer' => '',
        'phone' => '',
        'status' => [],
        'wilaya' => null,
        'city' => null,
        'assigned_to' => null,
        'amount_min' => null,
        'amount_max' => null,
        'date_from' => null,
        'date_to' => null,
        'delivery_type' => null,
        'shipping_provider' => null,
        'product_id' => null,
        'product' => '',
        'source' => null,
    ],
    'orders' => [],
    'page' => 1,
    'visibleColumns' => [],
    'filterProducts' => [],
    'perPage' => 15,
    'allStatuses' => [],
    'allMembers' => [],
    'allStates' => [],
    'allCities' => [],
    'allProviders' => [],

    // Bulk operations
    'selectedOrders' => [],
    'selectAll' => false,
    'showBulkBar' => false,

    // Trash view
    'showTrash' => false,

    // Order detail expand
    'expandedOrderId' => null,

    // Reassign modal
    'showReassignModal' => false,
    'reassignOrderId' => null,
    'reassignMembershipId' => '',
]);

updated([
    'search' => function (): void {
        $this->page = 1;
        $this->loadOrders();
    },
]);

// Reload orders when the product filter (specific product or full-name text) changes.
updated([
    'filters.product_id' => function (): void {
        $this->page = 1;
        $this->loadOrders();
    },
    'filters.product' => function (): void {
        $this->page = 1;
        $this->loadOrders();
    },
    'filters.amount_min' => function (): void {
        $this->page = 1;
        $this->loadOrders();
    },
    'filters.amount_max' => function (): void {
        $this->page = 1;
        $this->loadOrders();
    },
    'filters.date_from' => function (): void {
        $this->page = 1;
        $this->loadOrders();
    },
    'filters.date_to' => function (): void {
        $this->page = 1;
        $this->loadOrders();
    },
]);

// Reload the list when the create/edit form child component saves.
on([
    'orders-refreshed' => function () {
        $this->page = 1;
        $this->loadOrders();
    },
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_VIEW->value), 403);

    $storeId = currentStoreId();

    $this->allStatuses = Status::where('type', 'order')->where(fn($q) => $q->whereNull('store_id')->orWhere('store_id', $storeId))->orderBy('sort_order')->get()->toArray();

    $this->allMembers = StoreMembership::where('store_id', $storeId)->where('is_active', true)->with('user')->get()->toArray();

    $this->allStates = State::active()->orderBy('name')->get()->toArray();

    // Shipping providers for filter dropdown.
    $this->allProviders = \App\Domains\Shipping\Models\ShippingProvider::where('store_id', $storeId)
        ->where('is_active', true)
        ->orderBy('name')
        ->get(['id', 'name'])
        ->toArray();

    $this->loadColumnPreferences();
    $this->loadFilterProducts();
    $this->loadOrders();
});

$loadColumnPreferences = function (): void {
    $defaults = ['number', 'customer', 'phone', 'wilaya', 'products', 'amount', 'status', 'assigned_agent', 'created_at'];

    $membership = $this->getCurrentMembership();
    if (!$membership) {
        $this->visibleColumns = $defaults;
        return;
    }

    $pref = UserColumnPreference::where('membership_id', $membership->id)->where('view_key', 'orders_index')->first();

    $this->visibleColumns = $pref->visible_columns ?? $defaults;
};

$saveColumnPreferences = function (): void {
    $membership = $this->getCurrentMembership();
    if (!$membership) {
        return;
    }

    UserColumnPreference::updateOrCreate(['membership_id' => $membership->id, 'view_key' => 'orders_index'], ['visible_columns' => $this->visibleColumns]);
};

$getCurrentMembership = function (): ?\App\Models\Stores\Team\StoreMembership {
    return \App\Models\Stores\Team\StoreMembership::where('store_id', currentStoreId())
        ->where('user_id', auth()->id())
        ->first();
};

$loadOrders = function (): void {
    $storeId = currentStoreId();
    $f = $this->filters;

    $query = Order::where('store_id', $storeId)->with(['customer', 'status', 'items.product', 'items.variant', 'assignedMembership.user', 'createdByMembership.user', 'state', 'city', 'latestTracking.shippingProvider']);

    if (!empty($this->search)) {
        $s = $this->search;
        $query->where(function ($q) use ($s) {
            $q->where('number', 'like', "%{$s}%")
                ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$s}%"))
                ->orWhereHas('customer', fn($cq) => $cq->where('phone', 'like', "%{$s}%"))
                ->orWhere('phone_secondary', 'like', "%{$s}%")
                ->orWhereHas('items.variant', fn($vq) => $vq->where('name', 'like', "%{$s}%"))
                ->orWhereHas('items.variant.product', fn($pq) => $pq->where('name', 'like', "%{$s}%"))
                ->orWhereHas('items.variant', fn($vq) => $vq->where('sku', 'like', "%{$s}%"))
                ->orWhereHas('items.variant', fn($vq) => $vq->where('barcode', 'like', "%{$s}%"))
                ->orWhereHas('items.variant.product', fn($pq) => $pq->where('sku', 'like', "%{$s}%"))
                ->orWhereHas('items.variant.product', fn($pq) => $pq->where('barcode', 'like', "%{$s}%"));
        });
    }

    if (!empty($f['number'])) {
        $query->where('number', 'like', "%{$f['number']}%");
    }
    if (!empty($f['customer'])) {
        $query->whereHas('customer', fn($q) => $q->where('name', 'like', "%{$f['customer']}%"));
    }
    if (!empty($f['phone'])) {
        $query->where(function ($q) use ($f) {
            $q->whereHas('customer', fn($q2) => $q2->where('phone', 'like', "%{$f['phone']}%"))->orWhere('phone_secondary', 'like', "%{$f['phone']}%");
        });
    }
    if (!empty($f['status']) && is_array($f['status'])) {
        $query->whereIn('status_id', $f['status']);
    }
    if (!empty($f['wilaya'])) {
        $query->where('state_id', $f['wilaya']);
    }
    if (!empty($f['city'])) {
        $query->where('city_id', $f['city']);
    }
    if (!empty($f['delivery_type'])) {
        $query->where('delivery_type', $f['delivery_type']);
    }
    if (!empty($f['shipping_provider'])) {
        $query->where('shipping_provider_id', $f['shipping_provider']);
    }
    if (!empty($f['product_id'])) {
        $query->whereHas('items', function ($iq) use ($f) {
            $iq->where('product_id', (int) $f['product_id'])->orWhereHas('variant', fn($vq) => $vq->where('product_id', (int) $f['product_id']));
        });
    } elseif (!empty($f['product'])) {
        $query->whereHas('items', function ($iq) use ($f) {
            $iq->whereHas('product', fn($pq) => $pq->where('name', 'like', "%{$f['product']}%"))->orWhereHas('variant.product', fn($pq) => $pq->where('name', 'like', "%{$f['product']}%"));
        });
    }
    if ($f['source'] === 'manual') {
        $query->whereNotNull('created_by_membership_id');
    } elseif ($f['source'] === 'store') {
        $query->whereNull('created_by_membership_id');
    }
    if (!empty($f['assigned_to'])) {
        $query->where('assigned_to_membership_id', $f['assigned_to']);
    }
    if (!empty($f['amount_min'])) {
        $query->where('total_amount', '>=', $f['amount_min']);
    }
    if (!empty($f['amount_max'])) {
        $query->where('total_amount', '<=', $f['amount_max']);
    }
    if (!empty($f['date_from'])) {
        $query->where('created_at', '>=', $f['date_from']);
    }
    if (!empty($f['date_to'])) {
        $query->where('created_at', '<=', $f['date_to'] . ' 23:59:59');
    }

    if ($this->showTrash) {
        $query->onlyTrashed();
    } else {
        $query->withoutTrashed();
    }

    $paginated = $query->orderByDesc('created_at')->paginate(min((int) ($this->perPage ?? 50), 50), ['*'], 'page', $this->page);

    $service = app(OrderService::class);
    $this->orders = $paginated->toArray();
    $this->orders['data'] = $paginated
        ->getCollection()
        ->map(function ($order) use ($service) {
            $arr = $order->toArray();
            $arr['transitions'] = $service->availableTransitions($order);
            $arr['items_summary'] = $order->items
                ->map(
                    fn($i) => [
                        'name' => $i->product?->name ?? ($i->variant?->name ?? '—'),
                        'qty' => $i->quantity,
                        'price' => $i->price,
                    ],
                )
                ->toArray();
            $arr['tracking'] = $order->latestTracking
                ? [
                    'tracking_number' => $order->latestTracking->tracking_number,
                    'carrier_status' => $order->latestTracking->carrier_status,
                    'carrier_label' => $order->latestTracking->carrier_label,
                    'shipped_at' => $order->latestTracking->shipped_at?->format('Y-m-d H:i'),
                    'delivered_at' => $order->latestTracking->delivered_at?->format('Y-m-d H:i'),
                    'shipping_provider' => $order->latestTracking->shippingProvider?->name,
                ]
                : null;
            return $arr;
        })
        ->toArray();

    $this->orders['filtered_total'] = $paginated->total();
};

$setPage = function (int $page): void {
    $maxPage = $this->orders['last_page'] ?? 1;
    $this->page = max(1, min($page, $maxPage));
    $this->loadOrders();
};

$setPerPage = function (int $perPage): void {
    $this->perPage = min($perPage, 50);
    $this->page = 1;
    $this->loadOrders();
};

$setFilter = function (string $key, $value): void {
    $intFilters = ['wilaya', 'city', 'assigned_to', 'shipping_provider'];
    $floatFilters = ['amount_min', 'amount_max'];
    $arrFilters = ['status'];

    if (in_array($key, $intFilters, true)) {
        $value = (int) $value;
    } elseif (in_array($key, $floatFilters, true)) {
        $value = (float) $value;
    } elseif (in_array($key, $arrFilters, true)) {
        $value = is_array($value) ? array_map('intval', $value) : [];
    } elseif (in_array($key, ['source', 'delivery_type'], true)) {
        $value = (string) $value;
    }

    $this->filters[$key] = $value;
    $this->page = 1;
    $this->loadOrders();
};

$clearFilters = function (): void {
    $this->filters = [
        'number' => '',
        'customer' => '',
        'phone' => '',
        'status' => [],
        'wilaya' => null,
        'city' => null,
        'assigned_to' => null,
        'amount_min' => null,
        'amount_max' => null,
        'date_from' => null,
        'date_to' => null,
        'delivery_type' => null,
        'shipping_provider' => null,
        'product' => '',
        'source' => null,
    ];
    $this->page = 1;
    $this->selectedOrders = [];
    $this->loadOrders();
};

// --- Bulk selection ---
$toggleSelectAll = function (): void {
    if ($this->selectAll) {
        $this->selectedOrders = collect($this->orders['data'] ?? [])
            ->pluck('id')
            ->toArray();
    } else {
        $this->selectedOrders = [];
    }
    $this->showBulkBar = count($this->selectedOrders) > 0;
};

$toggleSelectOrder = function (string $orderId): void {
    if (in_array($orderId, $this->selectedOrders)) {
        $this->selectedOrders = array_values(array_diff($this->selectedOrders, [$orderId]));
    } else {
        $this->selectedOrders[] = $orderId;
    }
    $pageIds = collect($this->orders['data'] ?? [])
        ->pluck('id')
        ->toArray();
    $this->selectAll = count($this->selectedOrders) > 0 && count(array_intersect($this->selectedOrders, $pageIds)) === count($pageIds);
    $this->showBulkBar = count($this->selectedOrders) > 0;
};

$clearSelection = function (): void {
    $this->selectedOrders = [];
    $this->selectAll = false;
    $this->showBulkBar = false;
};

// --- Bulk actions ---
$bulkAssignAgent = function (?string $membershipId): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);
    if (empty($this->selectedOrders)) {
        $this->dispatch('swal', type: 'warning', title: __('merchant.no_orders_selected'));
        return;
    }
    if ($membershipId && !StoreMembership::where('id', $membershipId)->where('store_id', currentStoreId())->exists()) {
        $this->dispatch('swal', type: 'error', title: __('merchant_panel.invalid_agent'));
        return;
    }
    Order::where('store_id', currentStoreId())
        ->whereIn('id', $this->selectedOrders)
        ->update(['assigned_to_membership_id' => $membershipId]);
    $this->dispatch('swal', type: 'success', title: __('merchant.orders_assigned'));
    $this->clearSelection();
    $this->loadOrders();
};

$bulkSendToCarrier = function (?string $providerId): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);
    if (empty($this->selectedOrders)) {
        $this->dispatch('swal', type: 'warning', title: __('merchant.no_orders_selected'));
        return;
    }
    $service = app(OrderService::class);
    $sent = 0;
    Order::where('store_id', currentStoreId())
        ->whereIn('id', $this->selectedOrders)
        ->each(function ($order) use ($service, $providerId, &$sent) {
            try {
                // Provider must be set BEFORE the transition — the observer's
                // syncTracking() reads order.shipping_provider_id when creating
                // the tracking record.
                $order->update(['shipping_provider_id' => $providerId]);
                $service->transition($order, 'shipped', 'Handed to carrier');
                $sent++;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("bulkSendToCarrier failed for order [{$order->number}]: " . $e->getMessage());
            }
        });
    $failed = count($this->selectedOrders) - $sent;
    $msg = $failed > 0 ? __('merchant.orders_sent') . " ({$sent}/" . count($this->selectedOrders) . " — {$failed} failed)" : __('merchant.orders_sent');
    $this->dispatch('swal', type: $failed > 0 ? 'warning' : 'success', title: $msg);
    $this->clearSelection();
    $this->loadOrders();
};

$bulkDelete = function (): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_DELETE->value), 403);
    if (empty($this->selectedOrders)) {
        $this->dispatch('swal', type: 'warning', title: __('merchant.no_orders_selected'));
        return;
    }
    Order::where('store_id', currentStoreId())->whereIn('id', $this->selectedOrders)->delete();
    $this->dispatch('swal', type: 'success', title: __('merchant.orders_deleted'));
    $this->clearSelection();
    $this->loadOrders();
};

// --- Trash ---
$toggleTrash = function (): void {
    $this->showTrash = !$this->showTrash;
    $this->page = 1;
    $this->clearSelection();
    $this->loadOrders();
};

$restoreOrder = function (string $orderId): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_DELETE->value), 403);
    Order::where('store_id', currentStoreId())->withTrashed()->findOrFail($orderId)->restore();
    $this->dispatch('swal', type: 'success', title: __('merchant.orders_restored'));
    $this->loadOrders();
};

$restoreAll = function (): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_DELETE->value), 403);
    Order::where('store_id', currentStoreId())->onlyTrashed()->restore();
    $this->dispatch('swal', type: 'success', title: __('merchant.orders_restored'));
    $this->loadOrders();
};

$forceDeleteAll = function (): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_DELETE->value), 403);
    Order::where('store_id', currentStoreId())->onlyTrashed()->forceDelete();
    $this->dispatch('swal', type: 'success', title: __('merchant.empty_trash'));
    $this->loadOrders();
};

// --- Cities loader for filter ---
$loadFilterCities = function (string $stateId): void {
    $this->allCities = $stateId ? \App\Models\Locations\City::where('state_id', $stateId)->orderBy('name')->get()->toArray() : [];
};

$toggleStatusFilter = function (string $statusId): void {
    $current = $this->filters['status'] ?? [];
    if (in_array($statusId, $current)) {
        $this->filters['status'] = array_values(array_diff($current, [$statusId]));
    } else {
        $this->filters['status'][] = $statusId;
    }
    $this->page = 1;
    $this->loadOrders();
};

$toggleColumn = function (string $column): void {
    if (in_array($column, $this->visibleColumns)) {
        $this->visibleColumns = array_values(array_diff($this->visibleColumns, [$column]));
    } else {
        $this->visibleColumns[] = $column;
    }
    $this->saveColumnPreferences();
};

// ——— Status Transition ———
$transitionOrder = function (string $orderId, string $statusKey): void {
    $order = Order::where('store_id', currentStoreId())->findOrFail($orderId);
    $membership = $this->getCurrentMembership();

    $service = app(OrderService::class);
    $statusKey_translation = __('status.' . $statusKey);
    if (!$service->canTransition($order, $statusKey)) {
        $this->dispatch('swal', type: 'error', title: __('status_transition.invalid_transition', ['to' => $statusKey_translation ?? '—']));
        return;
    }

    $service->transition($order, $statusKey, null, $membership);

    $this->page = 1;
    $this->loadOrders();

    $this->dispatch('swal', type: 'success', title: __('status_transition.order_status_updated', ['new_status' => $statusKey_translation ?? '—']));
};

$loadFilterProducts = function (): void {
    $storeId = currentStoreId();
    $this->filterProducts = Product::with('primaryImage')
        ->select('id', 'name', 'sku', 'price', 'sort_order', 'created_at')
        ->where('store_id', $storeId)
        ->where('is_active', true)
        ->orderByDesc('sort_order')
        ->orderByDesc('created_at')
        ->limit(100)
        ->get()
        ->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'image_url' => $product->primaryImage?->path ? Storage::disk('public')->url($product->primaryImage->path) : asset('img/icons/noimg.png'),
            ];
        })
        ->toArray();
};

$applyProductNameFilter = function (string $query): void {
    $this->filters['product'] = trim($query);
    $this->filters['product_id'] = null;
    $this->page = 1;
    $this->loadOrders();
};

$clearProductFilter = function (): void {
    $this->filters['product'] = '';
    $this->filters['product_id'] = null;
    $this->page = 1;
    $this->loadOrders();
};

// ——— Detail Expand ———
$toggleDetail = function (string $orderId): void {
    $this->expandedOrderId = $this->expandedOrderId === $orderId ? null : $orderId;
};

// ——— Reassign ———
$openReassignModal = function (string $orderId): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);
    $this->reassignOrderId = $orderId;
    $this->reassignMembershipId = '';
    $this->showReassignModal = true;
};

$submitReassign = function (): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);

    if (empty($this->reassignMembershipId)) {
        $this->dispatch('swal', type: 'error', title: __('Select an agent'));
        return;
    }

    $order = Order::where('store_id', currentStoreId())->findOrFail($this->reassignOrderId);
    $targetMembership = StoreMembership::where('store_id', currentStoreId())->findOrFail($this->reassignMembershipId);
    $byMembership = $this->getCurrentMembership();

    if (!$byMembership) {
        $this->dispatch('swal', type: 'error', title: __('Unauthorized'));
        return;
    }

    $service = app(OrderAssignmentService::class);
    $service->reassign($order, $targetMembership, $byMembership);

    $this->showReassignModal = false;
    $this->loadOrders();

    $this->dispatch('swal', type: 'success', title: __('Order reassigned'));
};

// ——— Delete ———
$deleteOrder = function (string $orderId): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_DELETE->value), 403);

    $order = Order::where('store_id', currentStoreId())->findOrFail($orderId);
    $order->delete();

    $this->loadOrders();
    $this->dispatch('swal', type: 'success', title: __('Order deleted'));
};

$refreshOrders = function ()  {
    $this->loadOrders();
};
?>

<div x-data="{
    openFilter: null,
    openColToggle: false,
    filterPos: { top: 0, left: 0 },
    positionFilter(e) {
        let r = e.currentTarget.getBoundingClientRect();
        this.filterPos = { top: r.bottom + 4, left: Math.max(8, r.left) };
    }
}">
    {{-- Page Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <x-edz.page-header title="{{ __('merchant_panel.orders') }}"
            description="{{ __('merchant_panel.manage_customer_orders') }}">
        </x-edz.page-header>
        {{-- Aggregated summary --}}
        @if (!empty($orders['filtered_total']))
            <div class="flex items-center gap-6 px-4 py-2 mb-4 text-sm text-ink-muted">
                <span>{{ $orders['filtered_total'] }} {{ __('merchant.orders_count') }}</span>
            </div>
        @endif
        <div class="flex items-center gap-2">
            @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
                <button @click="$wire.$dispatch('orders-form-open-create')" class="edz-btn edz-btn--primary edz-btn--sm">
                    <x-edz.icon name="plus" class="w-4 h-4" />
                    <span>{{ __('merchant_panel.new_order') }}</span>
                </button>
            @endif
            <button wire:click="refreshOrders" class="edz-btn edz-btn--ghost edz-btn--sm" wire:loading.attr="disabled"
                wire:loading.class="opacity-50 pointer-events-none" wire:target="refreshOrders">
                <x-edz.icon name="arrow-path" wire:loading.remove wire:target="refreshOrders" class="w-4 h-4" />
                <svg x-cloak wire:loading wire:target="refreshOrders" class="edz-spinner" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2">
                    <path
                        d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="edz-card edz-card--padded mb-4">
        {{-- Main row: search + column toggle + filters toggle --}}
        <div class="flex flex-wrap items-center gap-3">
            {{-- Unified Search --}}
            <div class="relative flex-1 min-w-[200px]">
                <input type="text" wire:model.live.debounce.600ms="search" @keydown.enter="$wire.loadOrders()"
                    placeholder="{{ __('merchant.search_orders') }} — {{ __('merchant_panel.products') }}, SKU, barcode..."
                    class="edz-input text-sm ps-9 pe-9">
                <x-edz.icon name="search"
                    class="absolute start-3 top-1/2 -translate-y-1/2 w-4 h-4 text-ink-muted pointer-events-none" />
                @if ($this->search !== '')
                    <button wire:click="$set('search', '')" type="button"
                        class="absolute end-2 top-1/2 -translate-y-1/2 text-ink-muted hover:text-accent-500 transition me-7"
                        aria-label="Clear search">
                        <x-edz.icon name="x-mark" class="w-4 h-4" />
                    </button>
                @endif
                <button wire:click="loadOrders" type="button"
                    class="absolute end-2 top-1/2 -translate-y-1/2 text-ink-muted hover:text-accent-500 transition"
                    wire:loading.attr="disabled" wire:target="loadOrders">
                    <svg x-cloak wire:loading wire:target="loadOrders" class="edz-spinner w-4 h-4" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                    </svg>
                    <x-edz.icon name="arrow-right" wire:loading.remove wire:target="loadOrders" class="w-4 h-4" />
                </button>
            </div>

            {{-- Column Toggle --}}
            <div class="relative" @click.away="openColToggle = false">
                <button @click="openColToggle = !openColToggle" class="edz-btn edz-btn--ghost edz-btn--sm">
                    <x-edz.icon name="view-columns" class="w-4 h-4" />
                    {{ __('merchant_panel.columns') }}
                </button>
                <div x-show="openColToggle" x-transition
                    class="absolute z-40 mt-1 w-56 bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-2 space-y-1">
                    @foreach (['number' => __('merchant_panel.number'), 'customer' => __('merchant_panel.customer'), 'phone' => __('merchant_panel.phone'), 'wilaya' => __('merchant_panel.state'), 'products' => __('merchant_panel.products'), 'amount' => __('merchant_panel.amount'), 'status' => __('merchant_panel.status'), 'assigned_agent' => __('merchant_panel.agent'), 'created_at' => __('merchant_panel.date'), 'confirmation_attempts' => __('merchant_panel.attempts'), 'last_contact' => __('merchant_panel.last_contact'), 'weight' => __('merchant_panel.weight'), 'shipment_type' => __('merchant_panel.shipment')] as $col => $label)
                        <label
                            class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-surface-secondary dark:hover:bg-ink-700 cursor-pointer text-sm">
                            <input type="checkbox" wire:click="toggleColumn('{{ $col }}')"
                                {{ in_array($col, $this->visibleColumns) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-accent-600 focus:ring-accent-500">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Source --}}
            <div x-data="{ open: false }" @click.away="open = false" class="relative">
                <button @click="open = !open"
                    class="edz-btn edz-btn--ghost edz-btn--sm {{ $this->filters['source'] ? 'text-accent-600' : '' }}"
                    wire:loading.attr="disabled" wire:target="setFilter">
                    <svg x-cloak wire:loading wire:target="setFilter" class="edz-spinner w-4 h-4" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                    </svg>
                    <x-edz.icon name="user" wire:loading.remove wire:target="setFilter" class="w-4 h-4" />
                    <span wire:loading.remove
                        wire:target="setFilter">{{ $this->filters['source'] === 'manual' ? __('merchant.delivery_man') : ($this->filters['source'] === 'store' ? __('merchant_panel.store') : __('merchant_panel.source')) }}</span>
                    <x-edz.icon name="chevron-down" wire:loading.remove wire:target="setFilter" class="w-3 h-3" />
                </button>
                <div x-show="open" x-transition
                    class="absolute z-40 mt-1 w-40 bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5">
                    <button wire:click="setFilter('source', null)"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">—</button>
                    <button wire:click="setFilter('source', 'store')"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">{{ __('merchant_panel.store') }}</button>
                    <button wire:click="setFilter('source', 'manual')"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">{{ __('merchant.delivery_man') }}</button>
                </div>
            </div>

            {{-- Delivery Type --}}
            <div x-data="{ open: false }" @click.away="open = false" class="relative">
                <button @click="open = !open"
                    class="edz-btn edz-btn--ghost edz-btn--sm {{ $this->filters['delivery_type'] ? 'text-accent-600' : '' }}"
                    wire:loading.attr="disabled" wire:target="setFilter">
                    <svg x-cloak wire:loading wire:target="setFilter" class="edz-spinner w-4 h-4" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                    </svg>
                    <x-edz.icon name="home" wire:loading.remove wire:target="setFilter" class="w-4 h-4" />
                    <span wire:loading.remove
                        wire:target="setFilter">{{ $this->filters['delivery_type'] === 'stopdesk' ? __('storefront.stop_desk') : ($this->filters['delivery_type'] === 'home' ? __('storefront.home_delivery') : __('storefront.delivery_type')) }}</span>
                    <x-edz.icon name="chevron-down" wire:loading.remove wire:target="setFilter" class="w-3 h-3" />
                </button>
                <div x-show="open" x-transition
                    class="absolute z-40 mt-1 w-44 bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5">
                    <button wire:click="setFilter('delivery_type', null)"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">—</button>
                    <button wire:click="setFilter('delivery_type', 'home')"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">{{ __('storefront.home_delivery') }}</button>
                    <button wire:click="setFilter('delivery_type', 'stopdesk')"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">{{ __('storefront.stop_desk') }}</button>
                </div>
            </div>

            {{-- Shipping Provider --}}
            <div x-data="{ open: false }" @click.away="open = false" class="relative">
                <button @click="open = !open"
                    class="edz-btn edz-btn--ghost edz-btn--sm {{ $this->filters['shipping_provider'] ? 'text-accent-600' : '' }}"
                    wire:loading.attr="disabled" wire:target="setFilter">
                    <svg x-cloak wire:loading wire:target="setFilter" class="edz-spinner w-4 h-4" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                    </svg>
                    <x-edz.icon name="truck" wire:loading.remove wire:target="setFilter" class="w-4 h-4" />
                    <span wire:loading.remove
                        wire:target="setFilter">{{ collect($this->allProviders)->firstWhere('id', $this->filters['shipping_provider'])['name'] ?? __('merchant.assign_delivery_man') }}</span>
                    <x-edz.icon name="chevron-down" wire:loading.remove wire:target="setFilter" class="w-3 h-3" />
                </button>
                <div x-show="open" x-transition
                    class="absolute z-40 mt-1 w-48 bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5 max-h-60 overflow-y-auto edz-scroll">
                    <button wire:click="setFilter('shipping_provider', null)"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">—</button>
                    @foreach ($this->allProviders as $pr)
                        <button wire:click="setFilter('shipping_provider', '{{ $pr['id'] }}')"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">
                            {{ $pr['name'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Trash Toggle --}}
            <button wire:click="toggleTrash"
                class="edz-btn edz-btn--ghost edz-btn--sm {{ $this->showTrash ? 'text-danger-600' : '' }}"
                wire:loading.attr="disabled" wire:loading.class="opacity-50 pointer-events-none">
                <svg x-cloak wire:loading wire:target="toggleTrash" class="edz-spinner w-4 h-4" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2">
                    <path
                        d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                </svg>
                <x-edz.icon name="trash" wire:loading.remove wire:target="toggleTrash" class="w-4 h-4" />
                <span wire:loading.remove
                    wire:target="toggleTrash">{{ $this->showTrash ? __('buttons.close') . ' ' . __('merchant.trash_bin') : __('merchant.trash_bin') }}</span>
            </button>

            <div class="flex items-center gap-1 text-xs text-ink-muted" x-data="{ pp: {{ $this->perPage }} }">
                <span>{{ __('merchant.per_page') }}</span>
                <select x-model="pp" x-on:change="$wire.setPerPage(parseInt($event.target.value))"
                    class="text-xs border border-surface-border rounded-lg px-2 py-1 bg-surface dark:bg-ink-800 text-ink focus:outline-none focus:ring-1 focus:ring-[var(--store-primary)]">
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

    </div>

    {{-- Active filter summary + Clear --}}
    @if (array_filter($this->filters))
        <div class="mb-3 flex items-center gap-2 flex-wrap">
            @if (!empty($this->filters['wilaya']))
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-accent-50 text-accent-700 dark:bg-accent-900/30 dark:text-accent-400">
                    {{ collect($this->allStates)->firstWhere('id', $this->filters['wilaya'])['name'] ?? '' }}
                    <button wire:click="setFilter('wilaya', null)" wire:loading.attr="disabled"
                        class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
                </span>
            @endif
            @if (!empty($this->filters['city']))
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-accent-50 text-accent-700 dark:bg-accent-900/30 dark:text-accent-400">
                    {{ collect($this->allCities)->firstWhere('id', $this->filters['city'])['name'] ?? '' }}
                    <button wire:click="setFilter('city', null)" wire:loading.attr="disabled"
                        class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
                </span>
            @endif
            @if (!empty($this->filters['status']))
                @foreach ($this->allStatuses as $s)
                    @if (in_array($s['id'], $this->filters['status']))
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-accent-50 text-accent-700 dark:bg-accent-900/30 dark:text-accent-400">
                            {{ $s['label'] }}
                            <button wire:click="toggleStatusFilter('{{ $s['id'] }}')"
                                wire:loading.attr="disabled" class="hover:text-accent-900"><x-edz.icon name="x-mark"
                                    class="w-3 h-3" /></button>
                        </span>
                    @endif
                @endforeach
            @endif
            @if (!empty($this->filters['assigned_to']))
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-accent-50 text-accent-700 dark:bg-accent-900/30 dark:text-accent-400">
                    {{ collect($this->allMembers)->firstWhere('id', $this->filters['assigned_to'])['user']['name'] ?? '' }}
                    <button wire:click="setFilter('assigned_to', null)" wire:loading.attr="disabled"
                        class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
                </span>
            @endif
            @if (!empty($this->filters['date_from']) || !empty($this->filters['date_to']))
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-accent-50 text-accent-700 dark:bg-accent-900/30 dark:text-accent-400">
                    {{ $this->filters['date_from'] ?? '...' }} — {{ $this->filters['date_to'] ?? '...' }}
                    <button @click="$wire.setFilter('date_from', null); $wire.setFilter('date_to', null)"
                        wire:loading.attr="disabled" class="hover:text-accent-900"><x-edz.icon name="x-mark"
                            class="w-3 h-3" /></button>
                </span>
            @endif
            <button wire:click="clearFilters" class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 text-xs"
                wire:loading.attr="disabled" wire:loading.class="opacity-50 pointer-events-none">
                <x-edz.icon name="x-circle" class="w-3 h-3" />
                {{ __('merchant_panel.clear_filters') }}
            </button>
        </div>
    @endif



    {{-- Bulk action bar (sticky when items selected) --}}
    @if ($this->showTrash)
        <div
            class="mb-4 p-3 bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-700 rounded-xl flex items-center justify-between">
            <span class="text-sm text-warning-700 dark:text-warning-400 font-medium">
                {{ __('merchant.trash_bin') }} — {{ $orders['total'] ?? 0 }}
            </span>
            <div class="flex gap-2">
                <button wire:click="restoreAll" wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 pointer-events-none" class="edz-btn edz-btn--ghost edz-btn--sm">
                    <svg x-cloak wire:loading wire:target="restoreAll" class="edz-spinner w-3.5 h-3.5"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                    </svg>
                    <span wire:loading.remove wire:target="restoreAll">{{ __('merchant.restore_all') }}</span>
                </button>
                <button x-data="{ isLoading: false }"
                    x-on:click.prevent="(async () => { if (!isLoading && await EdzSwal.confirmDelete()) { isLoading = true; await $wire.forceDeleteAll(); isLoading = false; } })()"
                    :disabled="isLoading"
                    class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 disabled:opacity-50">
                    <svg x-show="isLoading" x-cloak class="edz-spinner w-3.5 h-3.5" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                    </svg>
                    <span x-show="!isLoading">{{ __('merchant.empty_trash') }}</span>
                </button>
            </div>
        </div>
    @elseif (count($this->selectedOrders) > 0)
        <div
            class="mb-4 p-3 bg-accent-50 dark:bg-accent-900/20 border border-accent-200 dark:border-accent-700 rounded-xl flex items-center justify-between sticky top-0 z-30">
            <span class="text-sm text-accent-700 dark:text-accent-400 font-medium">
                {{ count($this->selectedOrders) }} {{ __('merchant.orders_count') }}
            </span>
            <div class="flex gap-2 flex-wrap">
                {{-- Assign agent --}}
                <div x-data="{ open: false }" @click.away="open = false" class="relative">
                    <button @click="open = !open" class="edz-btn edz-btn--ghost edz-btn--sm"
                        wire:loading.attr="disabled" wire:target="bulkAssignAgent">
                        <svg x-cloak wire:loading wire:target="bulkAssignAgent" class="edz-spinner w-4 h-4"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path
                                d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                        </svg>
                        <x-edz.icon name="user-plus" wire:loading.remove wire:target="bulkAssignAgent"
                            class="w-4 h-4" />
                        <span wire:loading.remove
                            wire:target="bulkAssignAgent">{{ __('merchant.bulk_assign_agent') }}</span>
                    </button>
                    <div x-show="open" x-transition
                        class="absolute z-50 right-0 mt-1 w-56 bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5 max-h-60 overflow-y-auto edz-scroll">
                        @foreach ($this->allMembers as $m)
                            <button wire:click="bulkAssignAgent('{{ $m['id'] }}')"
                                class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary disabled:opacity-50"
                                wire:loading.attr="disabled" wire:target="bulkAssignAgent">
                                {{ $m['user']['name'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Send to carrier --}}
                @if (count($this->allProviders) > 0)
                    <div x-data="{ open: false }" @click.away="open = false" class="relative">
                        <button @click="open = !open" class="edz-btn edz-btn--ghost edz-btn--sm"
                            wire:loading.attr="disabled" wire:target="bulkSendToCarrier">
                            <svg x-cloak wire:loading wire:target="bulkSendToCarrier" class="edz-spinner w-4 h-4"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path
                                    d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                            </svg>
                            <x-edz.icon name="truck" wire:loading.remove wire:target="bulkSendToCarrier"
                                class="w-4 h-4" />
                            <span wire:loading.remove
                                wire:target="bulkSendToCarrier">{{ __('merchant.bulk_send_carrier') }}</span>
                        </button>
                        <div x-show="open" x-transition
                            class="absolute z-50 right-0 mt-1 w-56 bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5">
                            @foreach ($this->allProviders as $pr)
                                <button wire:click="bulkSendToCarrier('{{ $pr['id'] }}')"
                                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary disabled:opacity-50"
                                    wire:loading.attr="disabled" wire:target="bulkSendToCarrier">
                                    {{ $pr['name'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Delete --}}
                <button x-data="{ isLoading: false }"
                    x-on:click.prevent="(async () => { if (!isLoading && await EdzSwal.confirmDelete()) { isLoading = true; await $wire.bulkDelete(); isLoading = false; } })()"
                    :disabled="isLoading"
                    class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 disabled:opacity-50">
                    <svg x-show="isLoading" x-cloak class="edz-spinner w-4 h-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path
                            d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                    </svg>
                    <x-edz.icon name="trash" class="w-4 h-4" x-show="!isLoading" />
                    <span x-show="!isLoading">{{ __('merchant.bulk_delete') }}</span>
                </button>

                <button wire:click="clearSelection" class="edz-btn edz-btn--ghost edz-btn--sm">
                    <x-edz.icon name="x-mark" class="w-4 h-4" />
                </button>
            </div>
        </div>
    @endif

    {{-- Table --}}
    <div class="edz-card overflow-hidden">
        <div class="relative">
            {{-- Loading skeleton --}}
            <div wire:loading
                class="absolute inset-0 z-10 bg-surface/80 backdrop-blur-sm p-4 space-y-3 overflow-hidden"
                wire:target="search,filters">
                @for ($i = 0; $i < 5; $i++)
                    <div class="flex items-center gap-4 py-2">
                        <x-edz.skeleton width="5rem" height="0.875rem" />
                        <div class="flex-1 space-y-1.5">
                            <x-edz.skeleton width="40%" />
                            <x-edz.skeleton width="6rem" height="0.75rem" />
                        </div>
                        <x-edz.skeleton width="5rem" height="1.5rem" rounded="full" />
                        <x-edz.skeleton width="4rem" />
                        <x-edz.skeleton width="6rem" height="0.75rem" />
                    </div>
                @endfor
            </div>

            <div wire:loading.class="opacity-40 pointer-events-none" wire:target="search,filters">
                @if (!empty($orders['data']))
                    <div class="overflow-x-auto max-h-[calc(100vh-475px)] overflow-y-auto edz-scroll">
                        <table class="w-full text-sm">
                            <thead class="bg-secondary">
                                <tr>
                                    <th class="px-3 py-3 w-10">
                                        <input type="checkbox" wire:model="selectAll" wire:click="toggleSelectAll"
                                            class="rounded border-gray-300 text-accent-600 focus:ring-accent-500">
                                    </th>
                                    @if (in_array('number', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            {{ __('merchant_panel.number') }}</th>
                                    @endif
                                    @if (in_array('customer', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            {{ __('merchant_panel.customer') }}</th>
                                    @endif
                                    @if (in_array('phone', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            {{ __('merchant_panel.phone') }}</th>
                                    @endif
                                    @if (in_array('wilaya', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase relative group">
                                            <div class="flex items-center gap-1">
                                                {{ __('merchant_panel.state') }}
                                                <button data-filter-btn
                                                    @click.stop="openFilter = openFilter === 'wilaya' ? null : 'wilaya'; if (openFilter === 'wilaya') positionFilter($event)"
                                                    class="shrink-0 {{ filled($this->filters['wilaya']) ? 'text-accent-500' : 'text-ink-muted/40 group-hover:text-ink-muted' }} transition">
                                                    <x-edz.icon name="filter" class="w-3 h-3" />
                                                </button>
                                                @if (filled($this->filters['wilaya']))
                                                    <span
                                                        class="w-1.5 h-1.5 rounded-full bg-accent-500 shrink-0"></span>
                                                @endif
                                            </div>
                                        </th>
                                    @endif
                                    @if (in_array('products', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase relative group">
                                            <div class="flex items-center gap-1">
                                                {{ __('merchant_panel.products') }}
                                                <button data-filter-btn
                                                    @click.stop="openFilter = openFilter === 'product' ? null : 'product'; if (openFilter === 'product') positionFilter($event)"
                                                    class="shrink-0 {{ filled($this->filters['product']) ? 'text-accent-500' : 'text-ink-muted/40 group-hover:text-ink-muted' }} transition">
                                                    <x-edz.icon name="filter" class="w-3 h-3" />
                                                </button>
                                                @if (filled($this->filters['product']))
                                                    <span
                                                        class="w-1.5 h-1.5 rounded-full bg-accent-500 shrink-0"></span>
                                                @endif
                                            </div>
                                        </th>
                                    @endif
                                    @if (in_array('amount', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase relative group">
                                            <div class="flex items-center gap-1">
                                                {{ __('merchant_panel.amount') }}
                                                <button data-filter-btn
                                                    @click.stop="openFilter = openFilter === 'amount' ? null : 'amount'; if (openFilter === 'amount') positionFilter($event)"
                                                    class="shrink-0 {{ filled($this->filters['amount_min']) || filled($this->filters['amount_max']) ? 'text-accent-500' : 'text-ink-muted/40 group-hover:text-ink-muted' }} transition">
                                                    <x-edz.icon name="filter" class="w-3 h-3" />
                                                </button>
                                                @if (filled($this->filters['amount_min']) || filled($this->filters['amount_max']))
                                                    <span
                                                        class="w-1.5 h-1.5 rounded-full bg-accent-500 shrink-0"></span>
                                                @endif
                                            </div>
                                        </th>
                                    @endif
                                    @if (in_array('status', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase relative group">
                                            <div class="flex items-center gap-1">
                                                {{ __('merchant_panel.status') }}
                                                <button data-filter-btn
                                                    @click.stop="openFilter = openFilter === 'status' ? null : 'status'; if (openFilter === 'status') positionFilter($event)"
                                                    class="shrink-0 {{ !empty($this->filters['status']) ? 'text-accent-500' : 'text-ink-muted/40 group-hover:text-ink-muted' }} transition">
                                                    <x-edz.icon name="filter" class="w-3 h-3" />
                                                </button>
                                                @if (!empty($this->filters['status']))
                                                    <span
                                                        class="w-1.5 h-1.5 rounded-full bg-accent-500 shrink-0"></span>
                                                @endif
                                            </div>
                                        </th>
                                    @endif
                                    @if (in_array('assigned_agent', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase relative group">
                                            <div class="flex items-center gap-1">
                                                {{ __('merchant_panel.agent') }}
                                                <button data-filter-btn
                                                    @click.stop="openFilter = openFilter === 'assigned_to' ? null : 'assigned_to'; if (openFilter === 'assigned_to') positionFilter($event)"
                                                    class="shrink-0 {{ filled($this->filters['assigned_to']) ? 'text-accent-500' : 'text-ink-muted/40 group-hover:text-ink-muted' }} transition">
                                                    <x-edz.icon name="filter" class="w-3 h-3" />
                                                </button>
                                                @if (filled($this->filters['assigned_to']))
                                                    <span
                                                        class="w-1.5 h-1.5 rounded-full bg-accent-500 shrink-0"></span>
                                                @endif
                                            </div>
                                        </th>
                                    @endif
                                    @if (in_array('created_at', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase relative group w-[150px]">
                                            <div class="flex items-center gap-1">
                                                {{ __('merchant_panel.date') }}
                                                <button data-filter-btn
                                                    @click.stop="openFilter = openFilter === 'date' ? null : 'date'; if (openFilter === 'date') positionFilter($event)"
                                                    class="shrink-0 {{ filled($this->filters['date_from']) || filled($this->filters['date_to']) ? 'text-accent-500' : 'text-ink-muted/40 group-hover:text-ink-muted' }} transition">
                                                    <x-edz.icon name="filter" class="w-3 h-3" />
                                                </button>
                                                @if (filled($this->filters['date_from']) || filled($this->filters['date_to']))
                                                    <span
                                                        class="w-1.5 h-1.5 rounded-full bg-accent-500 shrink-0"></span>
                                                @endif
                                            </div>
                                        </th>
                                    @endif
                                    @if (in_array('confirmation_attempts', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            {{ __('merchant_panel.attempts') }}
                                        </th>
                                    @endif
                                    @if (in_array('last_contact', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            {{ __('merchant_panel.last_contact') }}
                                        </th>
                                    @endif
                                    @if (in_array('weight', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            {{ __('merchant_panel.weight') }}
                                        </th>
                                    @endif
                                    @if (in_array('shipment_type', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            {{ __('merchant_panel.shipment') }}
                                        </th>
                                    @endif
                                    <th class="px-4 py-3 text-end text-xs font-semibold text-ink-muted uppercase">
                                        {{ __('merchant_panel.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-100 dark:divide-ink-800">
                                @foreach ($orders['data'] as $order)
                                    @php
                                        $transitions = $order['transitions'] ?? [];
                                        $orderId = $order['id'] ?? '';
                                    @endphp
                                    <tr
                                        class="hover:bg-surface-50 dark:hover:bg-ink-800/50 {{ in_array($orderId, $this->selectedOrders) ? 'bg-accent-50 dark:bg-accent-900/10' : '' }}">
                                        <td class="px-3 py-3 w-10">
                                            <input type="checkbox" value="{{ $orderId }}"
                                                wire:click="toggleSelectOrder('{{ $orderId }}')"
                                                {{ in_array($orderId, $this->selectedOrders) ? 'checked' : '' }}
                                                class="rounded border-gray-300 text-accent-600 focus:ring-accent-500">
                                        </td>
                                        @if (in_array('number', $this->visibleColumns))
                                            <td class="px-4 py-3 font-mono font-semibold text-ink">
                                                #{{ $order['number'] }}
                                            </td>
                                        @endif
                                        @if (in_array('customer', $this->visibleColumns))
                                            <td class="px-4 py-3">
                                                <div class="text-ink font-medium">
                                                    {{ $order['customer']['name'] ?? '-' }}</div>
                                            </td>
                                        @endif
                                        @if (in_array('phone', $this->visibleColumns))
                                            <td class="px-4 py-3 text-ink-muted text-xs">
                                                {{ $order['customer']['phone'] ?? '-' }}
                                            </td>
                                        @endif
                                        @if (in_array('wilaya', $this->visibleColumns))
                                            <td class="px-4 py-3 text-ink-muted text-xs">
                                                {{ $order['state']['name'] ?? '-' }}
                                            </td>
                                        @endif
                                        @if (in_array('products', $this->visibleColumns))
                                            <td class="px-4 py-3 text-xs text-ink-muted max-w-[200px] truncate"
                                                title="{{ collect($order['items_summary'] ?? [])->map(fn($i) => $i['name'] . ' ×' . $i['qty'])->implode(', ') }}">
                                                @foreach ($order['items_summary'] ?? [] as $item)
                                                    {{ $item['name'] }} ×{{ $item['qty'] }}@if (!$loop->last)
                                                        ,
                                                    @endif
                                                @endforeach
                                            </td>
                                        @endif
                                        @if (in_array('amount', $this->visibleColumns))
                                            <td class="px-4 py-3 font-semibold text-ink">
                                                {{ currency($order['total_amount']) }}
                                            </td>
                                        @endif
                                        @if (in_array('status', $this->visibleColumns))
                                            <td class="px-4 py-3">
                                                <div class="relative" x-data="{ open: false, top: 0, left: 0 }"
                                                    @click.away="open = false">
                                                    <button
                                                        @click="
                                                        const r = $refs.trigger.getBoundingClientRect();
                                                        top = r.bottom + 4;
                                                        left = r.left;
                                                        if (top + 260 > window.innerHeight) top = r.top - 260;
                                                        open = !open;
                                                    "
                                                        x-ref="trigger"
                                                        class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-full cursor-pointer hover:opacity-80 {{ \Edzeery\MyStatusKit\Facades\Status::for('general', $order['status']['color'] ?? 'gray')->color() }}">
                                                        {{ $order['status']['label'] ?? '—' }}
                                                        <x-edz.icon name="chevron-down" class="w-3 h-3" />
                                                    </button>
                                                    <div x-show="open" x-transition x-cloak
                                                        class="fixed z-[200] w-56 bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5 max-h-64 overflow-y-auto edz-scroll"
                                                        :style="'top:' + top + 'px; left:' + Math.min(left, window.innerWidth -
                                                            240) + 'px'">
                                                        @foreach ($this->allStatuses as $s)
                                                            @if (in_array($s['key'], $transitions) || $s['id'] == $order['status_id'])
                                                                <button
                                                                    wire:click="transitionOrder('{{ $orderId }}', '{{ $s['key'] }}')"
                                                                    wire:loading.attr="disabled" @click="open = false"
                                                                    class="w-full text-left flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary dark:hover:bg-ink-700 disabled:opacity-50 {{ $s['id'] == $order['status_id'] ? 'font-bold' : '' }}">
                                                                    <svg x-cloak wire:loading
                                                                        wire:target="transitionOrder('{{ $orderId }}', '{{ $s['key'] }}')"
                                                                        class="edz-spinner w-3 h-3"
                                                                        viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="2">
                                                                        <path
                                                                            d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                                                                    </svg>
                                                                    <span class="w-2 h-2 rounded-full shrink-0"
                                                                        style="background: {{ \Edzeery\MyStatusKit\Facades\Status::for('general', $s['color'] ?? 'gray')->hex() }}"></span>
                                                                    {{ $s['label'] }}
                                                                </button>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </td>
                                        @endif
                                        @if (in_array('assigned_agent', $this->visibleColumns))
                                            <td class="px-4 py-3 text-xs text-ink-muted">
                                                {{ $order['assigned_membership']['user']['name'] ?? '—' }}
                                            </td>
                                        @endif
                                        @if (in_array('created_at', $this->visibleColumns))
                                            <td class="px-4 py-3 text-ink-muted text-xs">
                                                {{ \Carbon\Carbon::parse($order['created_at'])->format('M d, Y') }}
                                            </td>
                                        @endif
                                        @if (in_array('confirmation_attempts', $this->visibleColumns))
                                            <td class="px-4 py-3 text-ink-muted text-xs">
                                                {{ $order['confirmation_attempts'] ?? 0 }}
                                            </td>
                                        @endif
                                        @if (in_array('last_contact', $this->visibleColumns))
                                            <td class="px-4 py-3 text-ink-muted text-xs">
                                                {{ $order['last_contact_at'] ? \Carbon\Carbon::parse($order['last_contact_at'])->diffForHumans() : '—' }}
                                            </td>
                                        @endif
                                        @if (in_array('weight', $this->visibleColumns))
                                            <td class="px-4 py-3 text-ink-muted text-xs">
                                                {{ $order['weight_kg'] ? $order['weight_kg'] . ' kg' : '—' }}
                                            </td>
                                        @endif
                                        @if (in_array('shipment_type', $this->visibleColumns))
                                            <td class="px-4 py-3 text-ink-muted text-xs capitalize">
                                                {{ $order['shipment_type'] ?? '—' }}
                                            </td>
                                        @endif
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-1 flex-nowrap">
                                                <button wire:click="toggleDetail('{{ $orderId }}')"
                                                    class="edz-btn edz-btn--ghost edz-btn--xs shrink-0"
                                                    title="{{ __('merchant.order_details') }}">
                                                    <x-edz.icon name="chevron-right"
                                                        class="w-4 h-4 shrink-0 transition-transform duration-200 {{ $this->expandedOrderId === $orderId ? 'rotate-90' : '' }}" />
                                                </button>
                                                @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
                                                    <button
                                                        @click="$wire.$dispatch('orders-form-open-edit', { orderId: '{{ $orderId }}' })"
                                                        class="edz-btn edz-btn--ghost edz-btn--xs shrink-0"
                                                        title="{{ __('merchant_panel.edit') }}">
                                                        <x-edz.icon name="edit" class="w-4 h-4 shrink-0" />
                                                    </button>
                                                    <button wire:click="openReassignModal('{{ $orderId }}')"
                                                        wire:loading.attr="disabled" wire:loading.class="opacity-50"
                                                        wire:target="openReassignModal('{{ $orderId }}')"
                                                        class="edz-btn edz-btn--ghost edz-btn--xs shrink-0"
                                                        title="{{ __('merchant_panel.reassign') }}">
                                                        <svg x-cloak wire:loading
                                                            wire:target="openReassignModal('{{ $orderId }}')"
                                                            class="edz-spinner w-3.5 h-3.5" viewBox="0 0 24 24"
                                                            fill="none" stroke="currentColor" stroke-width="2">
                                                            <path
                                                                d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                                                        </svg>
                                                        <x-edz.icon name="arrows-right-left" wire:loading.remove
                                                            wire:target="openReassignModal('{{ $orderId }}')"
                                                            class="w-4 h-4 shrink-0" />
                                                    </button>
                                                @endif
                                                @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_DELETE->value))
                                                    @if ($this->showTrash)
                                                        <button wire:click="restoreOrder('{{ $orderId }}')"
                                                            wire:loading.attr="disabled"
                                                            wire:loading.class="opacity-50"
                                                            wire:target="restoreOrder('{{ $orderId }}')"
                                                            class="edz-btn edz-btn--ghost edz-btn--xs shrink-0 text-success-600"
                                                            title="{{ __('merchant.restore_order') }}">
                                                            <svg x-cloak wire:loading
                                                                wire:target="restoreOrder('{{ $orderId }}')"
                                                                class="edz-spinner w-3.5 h-3.5" viewBox="0 0 24 24"
                                                                fill="none" stroke="currentColor"
                                                                stroke-width="2">
                                                                <path
                                                                    d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                                                            </svg>
                                                            <x-edz.icon name="arrow-uturn-left" wire:loading.remove
                                                                wire:target="restoreOrder('{{ $orderId }}')"
                                                                class="w-4 h-4 shrink-0" />
                                                        </button>
                                                    @else
                                                        <button
                                                            class="edz-btn edz-btn--ghost edz-btn--xs text-danger-600 hover:text-danger-700 shrink-0"
                                                            x-data="{ isLoading: false }"
                                                            x-on:click.prevent="(async () => { if (!isLoading && await EdzSwal.confirmDelete('{{ $order['number'] ?? '' }}')) { isLoading = true; await $wire.deleteOrder('{{ $orderId }}'); isLoading = false; } })()"
                                                            :disabled="isLoading"
                                                            :class="isLoading ? 'opacity-50' : ''"
                                                            title="{{ __('merchant.delete_permanently') }}">
                                                            <svg x-show="isLoading" x-cloak
                                                                class="edz-spinner w-3.5 h-3.5" viewBox="0 0 24 24"
                                                                fill="none" stroke="currentColor"
                                                                stroke-width="2">
                                                                <path
                                                                    d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                                                            </svg>
                                                            <x-edz.icon name="trash" x-show="!isLoading"
                                                                class="w-4 h-4 shrink-0" />
                                                        </button>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @if ($this->expandedOrderId === $orderId)
                                        <tr>
                                            <td colspan="99" class="px-4 py-4 bg-surface-50 dark:bg-ink-800/30">
                                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                                                    <div>
                                                        <h4 class="font-semibold text-ink mb-2">
                                                            {{ __('merchant_panel.items') }}</h4>
                                                        @foreach ($order['items_summary'] ?? [] as $item)
                                                            <div
                                                                class="flex justify-between py-1 border-b border-surface-200 dark:border-ink-700">
                                                                <span class="text-ink-muted">{{ $item['name'] }}
                                                                    ×{{ $item['qty'] }}</span>
                                                                <span
                                                                    class="font-medium text-ink">{{ currency($item['price'] * $item['qty']) }}</span>
                                                            </div>
                                                        @endforeach
                                                        <div class="flex justify-between pt-2 font-bold text-ink">
                                                            <span>{{ __('merchant_panel.total') }}</span>
                                                            <span>{{ currency($order['total_amount']) }}</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-semibold text-ink mb-2">
                                                            {{ __('merchant_panel.details') }}</h4>
                                                        <dl class="space-y-1 text-ink-muted">
                                                            <div class="flex justify-between">
                                                                <dt>{{ __('merchant_panel.delivery') }}:</dt>
                                                                <dd class="text-ink capitalize">
                                                                    {{ $order['delivery_type'] ?? '—' }}</dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt>{{ __('merchant_panel.shipment') }}:</dt>
                                                                <dd class="text-ink capitalize">
                                                                    {{ $order['shipment_type'] ?? '—' }}</dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt>{{ __('merchant_panel.payment_method') }}:</dt>
                                                                <dd class="text-ink uppercase">
                                                                    {{ $order['payment_method'] ?? '—' }}</dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt>{{ __('merchant_panel.weight') }}:</dt>
                                                                <dd class="text-ink">
                                                                    {{ $order['weight_kg'] ? $order['weight_kg'] . ' kg' : '—' }}
                                                                </dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt>{{ __('merchant_panel.phone_secondary') }}:</dt>
                                                                <dd class="text-ink">
                                                                    {{ $order['phone_secondary'] ?? '—' }}</dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt>{{ __('merchant_panel.address') }}:</dt>
                                                                <dd class="text-ink">{{ $order['address'] ?? '—' }}
                                                                </dd>
                                                            </div>
                                                        </dl>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-semibold text-ink mb-2">
                                                            {{ __('merchant_panel.assignment') }}</h4>
                                                        <dl class="space-y-1 text-ink-muted">
                                                            <div class="flex justify-between">
                                                                <dt>{{ __('merchant_panel.agent') }}:</dt>
                                                                <dd class="text-ink">
                                                                    {{ $order['assigned_membership']['user']['name'] ?? '—' }}
                                                                </dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt>{{ __('merchant_panel.method') }}:</dt>
                                                                <dd class="text-ink capitalize">
                                                                    {{ $order['assignment_method'] ?? '—' }}</dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt>{{ __('merchant_panel.created_by') }}:</dt>
                                                                <dd class="text-ink">
                                                                    {{ $order['created_by_membership_id'] ? $order['created_by_membership']['user']['name'] ?? '—' : '—' }}
                                                                </dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt>{{ __('merchant_panel.attempts') }}:</dt>
                                                                <dd class="text-ink">
                                                                    {{ $order['confirmation_attempts'] ?? 0 }}</dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt>{{ __('merchant_panel.last_contact') }}:</dt>
                                                                <dd class="text-ink">
                                                                    {{ $order['last_contact_at'] ? \Carbon\Carbon::parse($order['last_contact_at'])->diffForHumans() : '—' }}
                                                                </dd>
                                                            </div>
                                                            @if (!empty($order['notes']))
                                                                <div
                                                                    class="mt-2 p-2 bg-surface-100 dark:bg-ink-700 rounded-lg text-ink-muted italic">
                                                                    "{{ $order['notes'] }}"</div>
                                                            @endif
                                                        </dl>
                                                    </div>
                                                    @if ($order['tracking'] ?? null)
                                                        <div>
                                                            <h4 class="font-semibold text-ink mb-2">
                                                                {{ __('merchant_panel.tracking') }}</h4>
                                                            <dl class="space-y-1 text-ink-muted">
                                                                @if ($order['tracking']['shipping_provider'])
                                                                    <div class="flex justify-between">
                                                                        <dt>{{ __('merchant_panel.carrier') }}:</dt>
                                                                        <dd class="text-ink">
                                                                            {{ $order['tracking']['shipping_provider'] }}
                                                                        </dd>
                                                                    </div>
                                                                @endif
                                                                @if ($order['tracking']['tracking_number'])
                                                                    <div class="flex justify-between">
                                                                        <dt>{{ __('merchant_panel.tracking_number') }}:
                                                                        </dt>
                                                                        <dd class="text-ink font-mono">
                                                                            {{ $order['tracking']['tracking_number'] }}
                                                                        </dd>
                                                                    </div>
                                                                @endif
                                                                @if ($order['tracking']['shipped_at'])
                                                                    <div class="flex justify-between">
                                                                        <dt>{{ __('merchant_panel.shipped_at') }}:
                                                                        </dt>
                                                                        <dd class="text-ink">
                                                                            {{ $order['tracking']['shipped_at'] }}
                                                                        </dd>
                                                                    </div>
                                                                @endif
                                                                @if ($order['tracking']['delivered_at'])
                                                                    <div class="flex justify-between">
                                                                        <dt>{{ __('merchant_panel.delivered_at') }}:
                                                                        </dt>
                                                                        <dd class="text-ink">
                                                                            {{ $order['tracking']['delivered_at'] }}
                                                                        </dd>
                                                                    </div>
                                                                @endif
                                                            </dl>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <x-edz.pagination :paginator="$orders" method="setPage" />
                @else
                    <div class="p-8 text-center text-ink-muted">
                        <x-edz.icon name="cart" class="w-12 h-12 mx-auto mb-3 text-ink-muted opacity-40" />
                        <p>{{ __('merchant_panel.no_orders_found') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Create / Edit Modal + Product/Variant Picker Modals --}}
    <div x-data="orderProductPicker()">
        {{-- Reassign Modal --}}
        @if ($showReassignModal)
            <x-edz.modal :isOpen="true" :showCloseButton="false" wire:key="order-reassign-modal">
                <div class="p-6 space-y-4">
                    <h3 class="text-lg font-bold text-ink">{{ __('merchant_panel.reassign_order') }}</h3>
                    <div>
                        <label class="edz-label">{{ __('merchant_panel.assign_to') }} *</label>
                        <x-edz.select wire:model="reassignMembershipId" :options="$allMembers" option-value="id"
                            option-label="user.name" placeholder="{{ __('merchant_panel.select_agent') }}"
                            size="sm" />
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                            wire:click="set('showReassignModal', false)">{{ __('merchant_panel.cancel') }}</button>
                        <button wire:click="submitReassign" class="edz-btn edz-btn--primary edz-btn--sm"
                            wire:loading.attr="disabled" wire:loading.class="opacity-50 pointer-events-none"
                            wire:target="submitReassign">
                            <svg x-cloak wire:loading wire:target="submitReassign" class="edz-spinner w-3.5 h-3.5"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path
                                    d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                            </svg>
                            <span wire:loading.remove
                                wire:target="submitReassign">{{ __('merchant_panel.reassign') }}</span>
                        </button>
                    </div>
                </div>
            </x-edz.modal>
        @endif
    </div>

    <livewire:merchant.orders.order-form-modal />

    {{-- Filter Portal — single container, fixed-positioned --}}
    <div x-show="openFilter !== null" x-transition @click.away="openFilter = null"
        :style="`top: ${filterPos.top}px; left: ${filterPos.left}px`"
        class="fixed z-50 p-2 bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg"
        :class="{
            'max-h-64 overflow-y-auto edz-scroll': openFilter === 'wilaya' || openFilter === 'status' ||
                openFilter === 'assigned_to',
            'w-48': openFilter === 'product' || openFilter === 'amount',
            'w-52': openFilter === 'wilaya' || openFilter === 'status' || openFilter === 'assigned_to' ||
                openFilter === 'date'
        }">

        {{-- Wilaya --}}
        @if (in_array('wilaya', $this->visibleColumns))
            <div x-show="openFilter === 'wilaya'" x-cloak>
                <button @click="$wire.setFilter('wilaya', null); $wire.setFilter('city', null)"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ !$this->filters['wilaya'] ? 'bg-surface-secondary font-medium' : '' }}">
                    —
                </button>
                @foreach ($this->allStates as $st)
                    <button
                        @click="$wire.setFilter('wilaya', '{{ $st['id'] }}'); $wire.loadFilterCities('{{ $st['id'] }}')"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['wilaya'] == $st['id'] ? 'bg-surface-secondary font-medium' : '' }}"
                        data-name="{{ $st['name'] }}">
                        {{ $st['name'] }}
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Product --}}
        @if (in_array('products', $this->visibleColumns))
            <div x-show="openFilter === 'product'" x-cloak>
                <x-edz.product-select :options="$filterProducts" wire:model="filters.product_id"
                    wire:fullmodel="filters.product" size="sm"
                    placeholder="{{ __('merchant_panel.filter_by_product') }}" />
            </div>
        @endif

        {{-- Amount --}}
        @if (in_array('amount', $this->visibleColumns))
            <div x-show="openFilter === 'amount'" x-cloak>
                <div class="flex items-center gap-1">
                    <div class="relative flex-1">
                        <input type="number" wire:model.live.debounce.600ms="filters.amount_min" placeholder="Min"
                            class="edz-input text-xs w-full pe-6">
                        @if ($this->filters['amount_min'] !== null && $this->filters['amount_min'] !== '')
                            <button wire:click="$set('filters.amount_min', '')" type="button"
                                class="absolute end-1 top-1/2 -translate-y-1/2 text-ink-muted hover:text-accent-500 transition"
                                aria-label="Clear min amount">
                                <x-edz.icon name="x-mark" class="w-3.5 h-3.5" />
                            </button>
                        @endif
                    </div>
                    <div class="relative flex-1">
                        <input type="number" wire:model.live.debounce.600ms="filters.amount_max" placeholder="Max"
                            class="edz-input text-xs w-full pe-6">
                        @if ($this->filters['amount_max'] !== null && $this->filters['amount_max'] !== '')
                            <button wire:click="$set('filters.amount_max', '')" type="button"
                                class="absolute end-1 top-1/2 -translate-y-1/2 text-ink-muted hover:text-accent-500 transition"
                                aria-label="Clear max amount">
                                <x-edz.icon name="x-mark" class="w-3.5 h-3.5" />
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Status --}}
        @if (in_array('status', $this->visibleColumns))
            <div x-show="openFilter === 'status'" x-cloak>
                @foreach ($this->allStatuses as $s)
                    <label
                        class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-surface-secondary cursor-pointer text-xs"
                        data-name="{{ $s['label'] }}">
                        <input type="checkbox" value="{{ $s['id'] }}"
                            wire:click="toggleStatusFilter('{{ $s['id'] }}')"
                            {{ in_array($s['id'], $this->filters['status'] ?? []) ? 'checked' : '' }}
                            class="rounded border-gray-300">
                        <span class="w-2 h-2 rounded-full shrink-0"
                            style="background: {{ match ($s['color'] ?? 'gray') {'success' => '#22c55e','info' => '#3b82f6','warning' => '#f59e0b','danger' => '#ef4444',default => '#6b7280'} }}"></span>
                        {{ $s['label'] }}
                    </label>
                @endforeach
            </div>
        @endif

        {{-- Assigned Agent --}}
        @if (in_array('assigned_agent', $this->visibleColumns))
            <div x-show="openFilter === 'assigned_to'" x-cloak>
                <button @click="$wire.setFilter('assigned_to', null)"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ !$this->filters['assigned_to'] ? 'bg-surface-secondary font-medium' : '' }}">
                    —
                </button>
                @foreach ($this->allMembers as $m)
                    <button @click="$wire.setFilter('assigned_to', '{{ $m['id'] }}')"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['assigned_to'] == $m['id'] ? 'bg-surface-secondary font-medium' : '' }}"
                        data-name="{{ $m['user']['name'] }}">
                        {{ $m['user']['name'] }}
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Date --}}
        @if (in_array('created_at', $this->visibleColumns))
            <div x-show="openFilter === 'date'" x-cloak>
                <div class="flex flex-col gap-1">
                    <div class="relative">
                        <input type="text" wire:model.blur="filters.date_from"
                            class="edz-input text-xs w-full flatpickr-input pe-7" placeholder="From"
                            autocomplete="off">
                        @if (!empty($this->filters['date_from']))
                            <button wire:click="$set('filters.date_from', '')" type="button"
                                class="absolute end-1 top-1/2 -translate-y-1/2 text-ink-muted hover:text-accent-500 transition"
                                aria-label="Clear from date">
                                <x-edz.icon name="x-mark" class="w-3.5 h-3.5" />
                            </button>
                        @endif
                    </div>
                    <div class="relative">
                        <input type="text" wire:model.blur="filters.date_to"
                            class="edz-input text-xs w-full flatpickr-input pe-7" placeholder="To"
                            autocomplete="off">
                        @if (!empty($this->filters['date_to']))
                            <button wire:click="$set('filters.date_to', '')" type="button"
                                class="absolute end-1 top-1/2 -translate-y-1/2 text-ink-muted hover:text-accent-500 transition"
                                aria-label="Clear to date">
                                <x-edz.icon name="x-mark" class="w-3.5 h-3.5" />
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
