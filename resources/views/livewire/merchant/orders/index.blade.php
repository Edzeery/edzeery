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
use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\updated;

layout('components.layouts.store');

state([
    'search' => '',
    'showAdvancedFilters' => false,
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
        'product' => '',
        'source' => null,
    ],
    'page' => 1,
    'visibleColumns' => [],
    'perPage' => 50,
    'allStatuses' => [],
    'allMembers' => [],
    'allStates' => [],
    'allCities' => [],
    'allProviders' => [],

    // Bulk operations
    'selectedOrders' => [],
    'selectAll' => false,
    'showBulkBar' => false,
    'bulkAction' => '',

    // Trash view
    'showTrash' => false,

    // Create/Edit modal
    'showCreateModal' => false,
    'showEditModal' => false,
    'editingOrderId' => null,
    'form' => [
        'customer_name' => '',
        'customer_phone' => '',
        'phone_secondary' => '',
        'address' => '',
        'state_id' => '',
        'city_id' => '',
        'delivery_type' => 'home',
        'shipment_type' => 'delivery',
        'payment_method' => 'cod',
        'notes' => '',
        'weight_kg' => '',
        'items' => [],
    ],
    'formProductSearch' => '',
    'formProductResults' => [],

    // Confirmation-time shipping assignment (carrier + desk)
    'editProviders' => [],
    'editDesks' => [],

    // Status change
    'statusChangeOrderId' => null,
    'statusChangeValue' => '',

    // Order detail expand
    'expandedOrderId' => null,

    // Reassign modal
    'showReassignModal' => false,
    'reassignOrderId' => null,
    'reassignMembershipId' => '',
]);

updated(['formProductSearch'], function (): void {
    $this->searchProducts();
});

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

$orders = computed(function (): array {
    $storeId = currentStoreId();
    $f = $this->filters;

    $query = Order::where('store_id', $storeId)->with(['customer', 'status', 'items.product', 'items.variant', 'assignedMembership.user', 'createdByMembership.user', 'state', 'city']);

    if (!empty($this->search)) {
        $s = $this->search;
        $query->where(function ($q) use ($s) {
            $q->where('number', 'like', "%{$s}%")
                ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$s}%"))
                ->orWhereHas('customer', fn($cq) => $cq->where('phone', 'like', "%{$s}%"));
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
    if (!empty($f['product'])) {
        $query->whereHas('items.variant', fn($vq) => $vq->whereHas('product', fn($pq) => $pq->where('name', 'like', "%{$f['product']}%")));
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
    $result = $paginated->toArray();
    $result['data'] = $paginated
        ->getCollection()
        ->map(function ($order) use ($service) {
            $arr = $order->toArray();
            $arr['transitions'] = $service->availableTransitions($order);
            $arr['items_summary'] = $order->items
                ->map(fn($i) => [
                    'name' => $i->product?->name ?? ($i->variant?->name ?? '—'),
                    'qty' => $i->quantity,
                    'price' => $i->price,
                ])
                ->toArray();
            return $arr;
        })
        ->toArray();

    $result['filtered_total'] = $paginated->total();
    $result['filtered_amount'] = $query->clone()->sum('total_amount');

    return $result;
});

$loadCities = function (string $stateId): void {
    if (empty($stateId)) {
        $this->allCities = [];
        $this->form['city_id'] = '';
        return;
    }
    $this->allCities = City::where('state_id', $stateId)->orderBy('name')->get()->toArray();
    $this->form['city_id'] = '';
};

$setPage = function (int $page): void {
    $this->page = $page;

};

$setPerPage = function (int $perPage): void {
    $this->perPage = min($perPage, 50);
    $this->page = 1;

};

$setFilter = function (string $key, $value): void {
    $this->filters[$key] = $value;
    $this->page = 1;

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
    Order::where('store_id', currentStoreId())
        ->whereIn('id', $this->selectedOrders)
        ->update(['assigned_to_membership_id' => $membershipId]);
    $this->dispatch('swal', type: 'success', title: __('merchant.orders_assigned'));
    $this->clearSelection();

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
                $order->update(['shipping_provider_id' => $providerId]);
                $service->transition($order, 'shipped', 'Handed to carrier');
                $sent++;
            } catch (\Exception $e) {
                // Skip orders that can't transition to shipped.
            }
        });
    $this->dispatch('swal', type: 'success', title: __('merchant.orders_sent'));
    $this->clearSelection();

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

};

// --- Trash ---
$toggleTrash = function (): void {
    $this->showTrash = !$this->showTrash;
    $this->page = 1;
    $this->clearSelection();

};

$restoreOrder = function (string $orderId): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_DELETE->value), 403);
    Order::where('store_id', currentStoreId())->withTrashed()->findOrFail($orderId)->restore();
    $this->dispatch('swal', type: 'success', title: __('merchant.orders_restored'));

};

$restoreAll = function (): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_DELETE->value), 403);
    Order::where('store_id', currentStoreId())->onlyTrashed()->restore();
    $this->dispatch('swal', type: 'success', title: __('merchant.orders_restored'));

};

$forceDeleteAll = function (): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_DELETE->value), 403);
    Order::where('store_id', currentStoreId())->onlyTrashed()->forceDelete();
    $this->dispatch('swal', type: 'success', title: __('merchant.empty_trash'));

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
    if (!$service->canTransition($order, $statusKey)) {
        $this->dispatch('swal', type: 'error', title: __('Invalid status transition'));
        return;
    }

    $service->transition($order, $statusKey, null, $membership);

    $this->page = 1;

    $this->dispatch('swal', type: 'success', title: __('Order status updated'));
};

// ——— Create Modal ———
$openCreateModal = function (): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);
    $this->form = [
        'customer_name' => '',
        'customer_phone' => '',
        'phone_secondary' => '',
        'address' => '',
        'state_id' => '',
        'city_id' => '',
        'delivery_type' => 'home',
        'shipment_type' => 'delivery',
        'payment_method' => 'cod',
        'notes' => '',
        'weight_kg' => '',
        'items' => [],
    ];
    $this->showCreateModal = true;
};

$searchProducts = function (): void {
    $search = $this->formProductSearch;
    if (strlen($search) < 2) {
        $this->formProductResults = [];
        return;
    }
    $storeId = currentStoreId();
    $this->formProductResults = ProductVariant::with('product')
        ->whereHas('product', fn($q) => $q->where('store_id', $storeId))
        ->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")->orWhereHas('product', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
        })
        ->limit(10)
        ->get()
        ->toArray();
};

$addFormItem = function (string $variantId): void {
    $variant = ProductVariant::with('product')->findOrFail($variantId);
    $found = false;
    foreach ($this->form['items'] as $idx => &$item) {
        if ($item['product_variant_id'] === $variantId) {
            $this->form['items'][$idx]['quantity']++;
            $found = true;
            break;
        }
    }
    unset($item);
    if (!$found) {
        $this->form['items'][] = [
            'product_variant_id' => $variant->id,
            'product_id' => $variant->product_id,
            'name' => ($variant->product?->name ?? '') . ' — ' . $variant->name,
            'price' => $variant->price ?? ($variant->product?->price ?? 0),
            'quantity' => 1,
        ];
    }
    $this->formProductSearch = '';
    $this->formProductResults = [];
};

$removeFormItem = function (int $index): void {
    unset($this->form['items'][$index]);
    $this->form['items'] = array_values($this->form['items']);
};

$updateFormItemQty = function (int $index, int $qty): void {
    if (isset($this->form['items'][$index])) {
        $this->form['items'][$index]['quantity'] = max(1, $qty);
    }
};

$submitCreate = function (): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);

    $storeId = currentStoreId();

    \Illuminate\Support\Facades\Validator::make($this->form, [
        'customer_phone' => 'required|string|max:20',
        'customer_name' => 'required|string|max:255',
        'items' => 'required|array|min:1',
        'items.*.product_variant_id' => 'required|string',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.price' => 'required|numeric|min:0',
        'delivery_type' => 'required|in:home,stopdesk',
        'shipment_type' => 'required|in:delivery,exchange,pickup',
        'payment_method' => 'required|in:cod',
        'state_id' => 'nullable|exists:states,id',
        'city_id' => 'nullable|exists:cities,id',
        'weight_kg' => 'nullable|numeric|min:0',
        'notes' => 'nullable|string|max:500',
    ])->validate();

    $customer = Customer::firstOrCreate(
        ['store_id' => $storeId, 'phone' => $this->form['customer_phone']],
        [
            'name' => $this->form['customer_name'],
            'phone' => $this->form['customer_phone'],
            'address' => $this->form['address'],
            'state_id' => $this->form['state_id'] ?: null,
            'city_id' => $this->form['city_id'] ?: null,
            'status' => true,
        ],
    );

    $total = collect($this->form['items'])->sum(fn($i) => $i['price'] * $i['quantity']);

    $service = app(OrderService::class);
    $membership = $this->getCurrentMembership();

    $order = $service->createManual(
        [
            'customer_id' => $customer->id,
            'total_amount' => $total,
            'state_id' => $this->form['state_id'] ?: null,
            'city_id' => $this->form['city_id'] ?: null,
            'address' => $this->form['address'],
            'delivery_type' => $this->form['delivery_type'],
            'shipment_type' => $this->form['shipment_type'],
            'payment_method' => $this->form['payment_method'],
            'notes' => $this->form['notes'],
            'phone_secondary' => $this->form['phone_secondary'],
            'weight_kg' => $this->form['weight_kg'] ?: null,
            'items' => $this->form['items'],
        ],
        $membership,
    );

    // Auto-assign
    $assignmentService = app(OrderAssignmentService::class);
    $assignmentService->assign($order);

    $this->showCreateModal = false;
    $this->page = 1;

    $this->dispatch('swal', type: 'success', title: __('Order created'));
};

// ——— Edit Modal ———
$openEditModal = function (string $orderId): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);

    $order = Order::with(['customer', 'items.product', 'items.variant'])
        ->where('store_id', currentStoreId())
        ->findOrFail($orderId);

    $this->editingOrderId = $order->id;
    $this->form = [
        'customer_name' => $order->customer?->name ?? '',
        'customer_phone' => $order->customer?->phone ?? '',
        'phone_secondary' => $order->phone_secondary ?? '',
        'address' => $order->address ?? '',
        'state_id' => $order->state_id ?? '',
        'city_id' => $order->city_id ?? '',
        'delivery_type' => $order->delivery_type,
        'shipment_type' => $order->shipment_type ?? 'delivery',
        'payment_method' => $order->payment_method,
        'notes' => $order->notes ?? '',
        'weight_kg' => $order->weight_kg ?? '',
        'shipping_provider_id' => $order->shipping_provider_id ?? '',
        'stopdesk_point_id' => $order->stopdesk_point_id ?? '',
        'items' => $order->items
            ->map(
                fn($i) => [
                    'product_variant_id' => $i->product_variant_id,
                    'product_id' => $i->product_id,
                    'name' => ($i->product?->name ?? '') . ' — ' . ($i->variant?->name ?? ''),
                    'price' => $i->price,
                    'quantity' => $i->quantity,
                ],
            )
            ->toArray(),
    ];
    $this->formProductSearch = '';
    $this->formProductResults = [];
    $this->showEditModal = true;

    if ($order->state_id) {
        $this->allCities = City::where('state_id', $order->state_id)->orderBy('name')->get()->toArray();
    }

    // Confirmation-time shipping assignment data (carrier + desk).
    $this->editProviders = \App\Domains\Shipping\Models\ShippingProvider::where('store_id', currentStoreId())
        ->where('is_active', true)
        ->orderBy('name')
        ->get(['id', 'name'])
        ->toArray();
    $this->editDesks = \App\Domains\Shipping\Models\StopdeskPoint::where('store_id', currentStoreId())
        ->where('is_active', true)
        ->orderBy('name')
        ->get(['id', 'name', 'address', 'state_id', 'city_id', 'shipping_provider_id'])
        ->toArray();
};

$submitEdit = function (): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);

    $order = Order::where('store_id', currentStoreId())->findOrFail($this->editingOrderId);

    // Block edit if shipped or later
    $blocked = ['shipped', 'in_transit', 'out_for_delivery', 'delivered', 'completed', 'returned', 'refunded', 'cancelled', 'canceled'];
    if (in_array($order->status?->key, $blocked)) {
        $this->dispatch('swal', type: 'error', title: __('Cannot edit shipped/closed orders'));
        return;
    }

    \Illuminate\Support\Facades\Validator::make($this->form, [
        'customer_phone' => 'required|string|max:20',
        'customer_name' => 'required|string|max:255',
        'items' => 'required|array|min:1',
        'items.*.product_variant_id' => 'required|string',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.price' => 'required|numeric|min:0',
        'delivery_type' => 'required|in:home,stopdesk',
        'shipment_type' => 'required|in:delivery,exchange,pickup',
        'payment_method' => 'required|in:cod',
        'shipping_provider_id' => 'nullable|string|exists:shipping_providers,id',
        'stopdesk_point_id' => 'nullable|string|exists:stopdesk_points,id',
    ])->validate();

    $storeId = currentStoreId();

    // Both assignments must belong to this store.
    foreach (['shipping_provider_id', 'stopdesk_point_id'] as $shipField) {
        if (filled($this->form[$shipField] ?? null)) {
            $model = $shipField === 'stopdesk_point_id' ? \App\Domains\Shipping\Models\StopdeskPoint::class : \App\Domains\Shipping\Models\ShippingProvider::class;
            $model::where('store_id', $storeId)->findOrFail($this->form[$shipField]);
        }
    }

    $customer = Customer::firstOrCreate(
        ['store_id' => $storeId, 'phone' => $this->form['customer_phone']],
        [
            'name' => $this->form['customer_name'],
            'phone' => $this->form['customer_phone'],
            'address' => $this->form['address'],
            'state_id' => $this->form['state_id'] ?: null,
            'city_id' => $this->form['city_id'] ?: null,
            'status' => true,
        ],
    );

    $total = collect($this->form['items'])->sum(fn($i) => $i['price'] * $i['quantity']);

    $order->update([
        'customer_id' => $customer->id,
        'total_amount' => $total,
        'state_id' => $this->form['state_id'] ?: null,
        'city_id' => $this->form['city_id'] ?: null,
        'address' => $this->form['address'],
        'delivery_type' => $this->form['delivery_type'],
        'shipment_type' => $this->form['shipment_type'],
        'payment_method' => $this->form['payment_method'],
        'notes' => $this->form['notes'],
        'phone_secondary' => $this->form['phone_secondary'],
        'weight_kg' => $this->form['weight_kg'] ?: null,
        'shipping_provider_id' => $this->form['shipping_provider_id'] ?: null,
        // Desk only applies to stopdesk deliveries; clear it on home.
        'stopdesk_point_id' => $this->form['delivery_type'] === 'stopdesk' ? ($this->form['stopdesk_point_id'] ?: null) : null,
    ]);

    // Sync order items
    $incomingVariantIds = collect($this->form['items'])->pluck('product_variant_id')->filter()->toArray();
    $existingItems = $order->items()->get()->keyBy('product_variant_id');

    // Remove items no longer in form
    foreach ($existingItems as $variantId => $item) {
        if (!in_array($variantId, $incomingVariantIds)) {
            $item->delete();
        }
    }

    // Add or update items
    foreach ($this->form['items'] as $itemData) {
        $existingItem = $order
            ->items()
            ->where('product_variant_id', $itemData['product_variant_id'] ?? null)
            ->first();

        if ($existingItem) {
            $existingItem->update([
                'quantity' => $itemData['quantity'],
                'price' => $itemData['price'],
                'subtotal' => $itemData['quantity'] * $itemData['price'],
            ]);
        } else {
            $order->items()->create([
                'store_id' => $storeId,
                'product_variant_id' => $itemData['product_variant_id'],
                'product_id' => $itemData['product_id'] ?? null,
                'quantity' => $itemData['quantity'],
                'price' => $itemData['price'],
                'subtotal' => $itemData['quantity'] * $itemData['price'],
            ]);
        }
    }

    $this->showEditModal = false;
    $this->editingOrderId = null;

    $this->dispatch('swal', type: 'success', title: __('Order updated'));
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

    $this->dispatch('swal', type: 'success', title: __('Order reassigned'));
};

// ——— Delete ———
$deleteOrder = function (string $orderId): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_DELETE->value), 403);

    $order = Order::where('store_id', currentStoreId())->findOrFail($orderId);
    $order->delete();


    $this->dispatch('swal', type: 'success', title: __('Order deleted'));
};
?>

<div x-data="{ openFilter: null, openColToggle: false }">
    {{-- Page Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <x-edz.page-header title="{{ __('merchant_panel.orders') }}" description="{{ __('merchant_panel.manage_customer_orders') }}">
        </x-edz.page-header>
        {{-- Aggregated summary --}}
        @if (!empty($orders['filtered_total']))
            <div class="flex items-center gap-6 px-4 py-2 mb-4 text-sm text-ink-muted">
                <span>{{ $orders['filtered_total'] }} {{ __('merchant.orders_count') }}</span>
                <span class="font-semibold text-ink">{{ currency($orders['filtered_amount'] ?? 0) }}</span>
            </div>
        @endif
        <div class="flex items-center gap-2">
            @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
                <button wire:click="openCreateModal" class="edz-btn edz-btn--primary edz-btn--sm">
                    <x-edz.icon name="plus" class="w-4 h-4" />
                    {{ __('merchant_panel.new_order') }}
                </button>
            @endif
            <button wire:click="$refresh" class="edz-btn edz-btn--ghost edz-btn--sm">
                <x-edz.icon name="arrow-path" class="w-4 h-4" />
            </button>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="edz-card edz-card--padded mb-4">
        {{-- Main row: search + column toggle + filters toggle --}}
        <div class="flex flex-wrap items-center gap-3">
            {{-- Unified Search --}}
            <div class="relative flex-1 min-w-[200px]">
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('merchant.search_orders') }}" class="edz-input text-sm ps-9">
                <x-edz.icon name="search"
                    class="absolute start-3 top-1/2 -translate-y-1/2 w-4 h-4 text-ink-muted pointer-events-none" />
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

            {{-- Advanced Filters Toggle --}}
            <button x-on:click="$wire.set('showAdvancedFilters', !$wire.get('showAdvancedFilters'))"
                class="edz-btn edz-btn--ghost edz-btn--sm">
                <x-edz.icon name="adjustments" class="w-4 h-4" />
                {{ __('merchant_panel.filters') }}
                @if (array_filter($this->filters))
                    <span
                        class="ml-1 px-1.5 py-0.5 text-[10px] rounded-full bg-accent-100 text-accent-700">{{ count(array_filter($this->filters)) }}</span>
                @endif
            </button>

            {{-- Trash Toggle --}}
            <button wire:click="toggleTrash"
                class="edz-btn edz-btn--ghost edz-btn--sm {{ $this->showTrash ? 'text-danger-600' : '' }}">
                <x-edz.icon name="trash" class="w-4 h-4" />
                {{ __('merchant.trash_bin') }}
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

        {{-- Advanced Filters (collapsible) --}}
        <div x-show="$wire.showAdvancedFilters" x-transition class="mt-3 pt-3 border-t border-surface-border">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">

                {{-- Source --}}
                <div x-data="{ open: false }" @click.away="open = false" class="relative">
                    <label
                        class="text-[10px] uppercase font-semibold text-ink-muted mb-1 block">{{ __('merchant_panel.source') }}</label>
                    <button @click="open = !open"
                        class="edz-btn edz-btn--ghost edz-btn--sm w-full justify-between text-xs">
                        {{ $this->filters['source'] === 'manual' ? __('merchant.delivery_man') : ($this->filters['source'] === 'store' ? __('merchant_panel.store') : '—') }}
                        <x-edz.icon name="chevron-down" class="w-3 h-3" />
                    </button>
                    <div x-show="open" x-transition
                        class="absolute z-40 mt-1 w-full bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5">
                        <button wire:click="setFilter('source', null)"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">—</button>
                        <button wire:click="setFilter('source', 'store')"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">{{ __('merchant_panel.store') }}</button>
                        <button wire:click="setFilter('source', 'manual')"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">{{ __('merchant.delivery_man') }}</button>
                    </div>
                </div>

                {{-- Wilaya --}}
                <div x-data="{ open: false, q: '' }" @click.away="open = false" class="relative">
                    <label
                        class="text-[10px] uppercase font-semibold text-ink-muted mb-1 block">{{ __('merchant_panel.wilaya') }}</label>
                    <button @click="open = !open"
                        class="edz-btn edz-btn--ghost edz-btn--sm w-full justify-between text-xs truncate">
                        {{ collect($this->allStates)->firstWhere('id', $this->filters['wilaya'])['name'] ?? '—' }}
                        <x-edz.icon name="chevron-down" class="w-3 h-3 shrink-0" />
                    </button>
                    <div x-show="open" x-transition
                        class="absolute z-40 mt-1 w-full bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5 max-h-60 overflow-y-auto">
                        <input type="text" x-model="q" placeholder="Search..."
                            class="w-full text-xs px-2 py-1 mb-1 rounded border border-surface-border bg-surface dark:bg-ink-700 focus:outline-none">
                        <button wire:click="setFilter('wilaya', null); $wire.setFilter('city', null)"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">—</button>
                        @foreach ($this->allStates as $st)
                            <button
                                wire:click="setFilter('wilaya', '{{ $st['id'] }}'); $wire.loadFilterCities('{{ $st['id'] }}')"
                                class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary"
                                x-show="!q || '{{ $st['name'] }}'.toLowerCase().includes(q.toLowerCase())">
                                {{ $st['name'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Commune --}}
                <div x-data="{ open: false, q: '' }" @click.away="open = false" class="relative">
                    <label
                        class="text-[10px] uppercase font-semibold text-ink-muted mb-1 block">{{ __('merchant_panel.commune') }}</label>
                    <button @click="open = !open"
                        class="edz-btn edz-btn--ghost edz-btn--sm w-full justify-between text-xs truncate">
                        {{ collect($this->allCities)->firstWhere('id', $this->filters['city'])['name'] ?? '—' }}
                        <x-edz.icon name="chevron-down" class="w-3 h-3 shrink-0" />
                    </button>
                    <div x-show="open" x-transition
                        class="absolute z-40 mt-1 w-full bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5 max-h-60 overflow-y-auto">
                        <input type="text" x-model="q" placeholder="Search..."
                            class="w-full text-xs px-2 py-1 mb-1 rounded border border-surface-border bg-surface dark:bg-ink-700 focus:outline-none">
                        <button wire:click="setFilter('city', null)"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">—</button>
                        @foreach ($this->allCities as $ci)
                            <button wire:click="setFilter('city', '{{ $ci['id'] }}')"
                                class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary"
                                x-show="!q || '{{ $ci['name'] }}'.toLowerCase().includes(q.toLowerCase())">
                                {{ $ci['name'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Status Multi-select --}}
                <div x-data="{ open: false, q: '' }" @click.away="open = false" class="relative">
                    <label
                        class="text-[10px] uppercase font-semibold text-ink-muted mb-1 block">{{ __('merchant_panel.status') }}</label>
                    <button @click="open = !open"
                        class="edz-btn edz-btn--ghost edz-btn--sm w-full justify-between text-xs">
                        {{ count($this->filters['status'] ?? []) ? count($this->filters['status']) . ' ' . __('merchant.selected') : '—' }}
                        <x-edz.icon name="chevron-down" class="w-3 h-3 shrink-0" />
                    </button>
                    <div x-show="open" x-transition
                        class="absolute z-40 mt-1 w-full bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5 max-h-60 overflow-y-auto">
                        <input type="text" x-model="q" placeholder="Search..."
                            class="w-full text-xs px-2 py-1 mb-1 rounded border border-surface-border bg-surface dark:bg-ink-700 focus:outline-none">
                        @foreach ($this->allStatuses as $s)
                            <label
                                class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-surface-secondary cursor-pointer text-xs"
                                x-show="!q || '{{ $s['label'] }}'.toLowerCase().includes(q.toLowerCase())">
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
                </div>

                {{-- Product --}}
                <div>
                    <label
                        class="text-[10px] uppercase font-semibold text-ink-muted mb-1 block">{{ __('merchant_panel.products') }}</label>
                    <input type="text" wire:model.live.debounce.500ms="filters.product" placeholder="..."
                        class="edz-input text-xs w-full">
                </div>

                {{-- Delivery Type --}}
                <div x-data="{ open: false }" @click.away="open = false" class="relative">
                    <label
                        class="text-[10px] uppercase font-semibold text-ink-muted mb-1 block">{{ __('storefront.delivery_type') }}</label>
                    <button @click="open = !open"
                        class="edz-btn edz-btn--ghost edz-btn--sm w-full justify-between text-xs">
                        {{ $this->filters['delivery_type'] === 'stopdesk' ? __('storefront.stop_desk') : ($this->filters['delivery_type'] === 'home' ? __('storefront.home_delivery') : '—') }}
                        <x-edz.icon name="chevron-down" class="w-3 h-3 shrink-0" />
                    </button>
                    <div x-show="open" x-transition
                        class="absolute z-40 mt-1 w-full bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5">
                        <button wire:click="setFilter('delivery_type', null)"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">—</button>
                        <button wire:click="setFilter('delivery_type', 'home')"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">{{ __('storefront.home_delivery') }}</button>
                        <button wire:click="setFilter('delivery_type', 'stopdesk')"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">{{ __('storefront.stop_desk') }}</button>
                    </div>
                </div>

                {{-- Shipping Provider --}}
                <div x-data="{ open: false, q: '' }" @click.away="open = false" class="relative">
                    <label
                        class="text-[10px] uppercase font-semibold text-ink-muted mb-1 block">{{ __('merchant.assign_delivery_man') }}</label>
                    <button @click="open = !open"
                        class="edz-btn edz-btn--ghost edz-btn--sm w-full justify-between text-xs truncate">
                        {{ collect($this->allProviders)->firstWhere('id', $this->filters['shipping_provider'])['name'] ?? '—' }}
                        <x-edz.icon name="chevron-down" class="w-3 h-3 shrink-0" />
                    </button>
                    <div x-show="open" x-transition
                        class="absolute z-40 mt-1 w-full bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5 max-h-60 overflow-y-auto">
                        <input type="text" x-model="q" placeholder="Search..."
                            class="w-full text-xs px-2 py-1 mb-1 rounded border border-surface-border bg-surface dark:bg-ink-700 focus:outline-none">
                        <button wire:click="setFilter('shipping_provider', null)"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">—</button>
                        @foreach ($this->allProviders as $pr)
                            <button wire:click="setFilter('shipping_provider', '{{ $pr['id'] }}')"
                                class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary"
                                x-show="!q || '{{ $pr['name'] }}'.toLowerCase().includes(q.toLowerCase())">
                                {{ $pr['name'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Agent --}}
                <div x-data="{ open: false, q: '' }" @click.away="open = false" class="relative">
                    <label
                        class="text-[10px] uppercase font-semibold text-ink-muted mb-1 block">{{ __('merchant_panel.assigned_to') }}</label>
                    <button @click="open = !open"
                        class="edz-btn edz-btn--ghost edz-btn--sm w-full justify-between text-xs truncate">
                        {{ collect($this->allMembers)->firstWhere('id', $this->filters['assigned_to'])['user']['name'] ?? '—' }}
                        <x-edz.icon name="chevron-down" class="w-3 h-3 shrink-0" />
                    </button>
                    <div x-show="open" x-transition
                        class="absolute z-40 mt-1 w-full bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5 max-h-60 overflow-y-auto">
                        <input type="text" x-model="q" placeholder="Search..."
                            class="w-full text-xs px-2 py-1 mb-1 rounded border border-surface-border bg-surface dark:bg-ink-700 focus:outline-none">
                        <button wire:click="setFilter('assigned_to', null)"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">—</button>
                        @foreach ($this->allMembers as $m)
                            <button wire:click="setFilter('assigned_to', '{{ $m['id'] }}')"
                                class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary"
                                x-show="!q || '{{ $m['user']['name'] }}'.toLowerCase().includes(q.toLowerCase())">
                                {{ $m['user']['name'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Amount --}}
                <div class="flex gap-1">
                    <div class="flex-1">
                        <label
                            class="text-[10px] uppercase font-semibold text-ink-muted mb-1 block">{{ __('merchant_panel.amount') }}</label>
                        <input type="number" wire:model.live.debounce.500ms="filters.amount_min" placeholder="Min"
                            class="edz-input text-xs w-full">
                    </div>
                    <div class="flex-1">
                        <label class="text-[10px] uppercase font-semibold text-ink-muted mb-1 block">&nbsp;</label>
                        <input type="number" wire:model.live.debounce.500ms="filters.amount_max" placeholder="Max"
                            class="edz-input text-xs w-full">
                    </div>
                </div>

                {{-- Date Range --}}
                <div class="flex gap-1">
                    <div class="flex-1">
                        <label
                            class="text-[10px] uppercase font-semibold text-ink-muted mb-1 block">{{ __('merchant_panel.created_at') }}</label>
                        <input type="text" wire:model="filters.date_from"
                            class="edz-input text-xs w-full flatpickr-input"
                            placeholder="YYYY-MM-DD" autocomplete="off">
                    </div>
                    <div class="flex-1">
                        <label class="text-[10px] uppercase font-semibold text-ink-muted mb-1 block">&nbsp;</label>
                        <input type="text" wire:model="filters.date_to"
                            class="edz-input text-xs w-full flatpickr-input"
                            placeholder="YYYY-MM-DD" autocomplete="off">
                    </div>
                </div>

            </div>

            {{-- Clear All --}}
            @if (!empty($this->search) || array_filter($this->filters))
                <div class="mt-3 flex justify-end">
                    <button x-on:click="$wire.set('search', ''); $wire.clearFilters()"
                        class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 hover:text-danger-700">
                        <x-edz.icon name="x-circle" class="w-4 h-4" />
                        {{ __('merchant_panel.clear_filters') }}
                    </button>
                </div>
            @endif
        </div>
    </div>



    {{-- Bulk action bar (sticky when items selected) --}}
    @if ($this->showTrash)
        <div class="mb-4 p-3 bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-700 rounded-xl flex items-center justify-between">
            <span class="text-sm text-warning-700 dark:text-warning-400 font-medium">
                {{ __('merchant.trash_bin') }} — {{ $orders['total'] ?? 0 }}
            </span>
            <div class="flex gap-2">
                <button wire:click="restoreAll" class="edz-btn edz-btn--ghost edz-btn--sm">{{ __('merchant.restore_all') }}</button>
                <button wire:click="forceDeleteAll" x-data x-on:click.prevent="EdzSwal.confirmDelete(() => { $wire.forceDeleteAll() })" class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600">{{ __('merchant.empty_trash') }}</button>
            </div>
        </div>
    @elseif (count($this->selectedOrders) > 0)
        <div class="mb-4 p-3 bg-accent-50 dark:bg-accent-900/20 border border-accent-200 dark:border-accent-700 rounded-xl flex items-center justify-between sticky top-0 z-30">
            <span class="text-sm text-accent-700 dark:text-accent-400 font-medium">
                {{ count($this->selectedOrders) }} {{ __('merchant.orders_count') }}
            </span>
            <div class="flex gap-2 flex-wrap">
                {{-- Assign agent --}}
                <div x-data="{ open: false }" @click.away="open = false" class="relative">
                    <button @click="open = !open" class="edz-btn edz-btn--ghost edz-btn--sm">
                        <x-edz.icon name="user-plus" class="w-4 h-4" />
                        {{ __('merchant.bulk_assign_agent') }}
                    </button>
                    <div x-show="open" x-transition class="absolute z-50 right-0 mt-1 w-56 bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5 max-h-60 overflow-y-auto">
                        @foreach ($this->allMembers as $m)
                            <button wire:click="bulkAssignAgent('{{ $m['id'] }}')"
                                class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">
                                {{ $m['user']['name'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Send to carrier --}}
                @if (count($this->allProviders) > 0)
                    <div x-data="{ open: false }" @click.away="open = false" class="relative">
                        <button @click="open = !open" class="edz-btn edz-btn--ghost edz-btn--sm">
                            <x-edz.icon name="truck" class="w-4 h-4" />
                            {{ __('merchant.bulk_send_carrier') }}
                        </button>
                        <div x-show="open" x-transition class="absolute z-50 right-0 mt-1 w-56 bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5">
                            @foreach ($this->allProviders as $pr)
                                <button wire:click="bulkSendToCarrier('{{ $pr['id'] }}')"
                                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">
                                    {{ $pr['name'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Delete --}}
                <button wire:click="bulkDelete" x-data x-on:click.prevent="EdzSwal.confirmDelete(() => { $wire.bulkDelete() })" class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600">
                    <x-edz.icon name="trash" class="w-4 h-4" />
                    {{ __('merchant.bulk_delete') }}
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
                wire:target="filters,loadOrders">
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

            <div wire:loading.class="opacity-40 pointer-events-none" wire:target="filters,loadOrders">
                @if (!empty($orders['data']))
                    <div class="overflow-x-auto max-h-[calc(100vh-320px)] overflow-y-auto">
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
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            {{ __('merchant_panel.state') }}</th>
                                    @endif
                                    @if (in_array('products', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            {{ __('merchant_panel.products') }}</th>
                                    @endif
                                    @if (in_array('amount', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            {{ __('merchant_panel.amount') }}</th>
                                    @endif
                                    @if (in_array('status', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            {{ __('merchant_panel.status') }}</th>
                                    @endif
                                    @if (in_array('assigned_agent', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            {{ __('merchant_panel.agent') }}</th>
                                    @endif
                                    @if (in_array('created_at', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            {{ __('merchant_panel.date') }}</th>
                                    @endif
                                    @if (in_array('confirmation_attempts', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            {{ __('merchant_panel.attempts') }}</th>
                                    @endif
                                    @if (in_array('last_contact', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            {{ __('merchant_panel.last_contact') }}</th>
                                    @endif
                                    @if (in_array('weight', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            {{ __('merchant_panel.weight') }}</th>
                                    @endif
                                    @if (in_array('shipment_type', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            {{ __('merchant_panel.shipment') }}</th>
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
                                    <tr class="hover:bg-surface-50 dark:hover:bg-ink-800/50 {{ in_array($orderId, $this->selectedOrders) ? 'bg-accent-50 dark:bg-accent-900/10' : '' }}">
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
                                                        class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-full cursor-pointer hover:opacity-80"
                                                        style="background: {{ match ($order['status']['color'] ?? 'gray') {'success' => '#dcfce7','info' => '#dbeafe','warning' => '#fef3c7','danger' => '#fecaca',default => '#f3f4f6'} }}; color: {{ match ($order['status']['color'] ?? 'gray') {'success' => '#166534','info' => '#1e40af','warning' => '#92400e','danger' => '#991b1b',default => '#374151'} }};">
                                                        {{ $order['status']['label'] ?? '—' }}
                                                        <x-edz.icon name="chevron-down" class="w-3 h-3" />
                                                    </button>
                                                    <div x-show="open" x-transition x-cloak
                                                        class="fixed z-[200] w-56 bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5 max-h-64 overflow-y-auto"
                                                        :style="'top:' + top + 'px; left:' + Math.min(left, window.innerWidth -
                                                            240) + 'px'">
                                                        @foreach ($this->allStatuses as $s)
                                                            @if (in_array($s['key'], $transitions) || $s['id'] == $order['status_id'])
                                                                <button
                                                                    wire:click="transitionOrder('{{ $orderId }}', '{{ $s['key'] }}')"
                                                                    @click="open = false"
                                                                    class="w-full text-left flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary dark:hover:bg-ink-700 {{ $s['id'] == $order['status_id'] ? 'font-bold' : '' }}">
                                                                    <span class="w-2 h-2 rounded-full shrink-0"
                                                                        style="background: {{ match ($s['color'] ?? 'gray') {'success' => '#22c55e','info' => '#3b82f6','warning' => '#f59e0b','danger' => '#ef4444',default => '#6b7280'} }}"></span>
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
                                                    <button wire:click="openEditModal('{{ $orderId }}')"
                                                        class="edz-btn edz-btn--ghost edz-btn--xs shrink-0"
                                                        title="{{ __('merchant_panel.edit') }}">
                                                        <x-edz.icon name="edit" class="w-4 h-4 shrink-0" />

                                                    </button>
                                                    <button wire:click="openReassignModal('{{ $orderId }}')"
                                                        class="edz-btn edz-btn--ghost edz-btn--xs shrink-0"
                                                        title="{{ __('merchant_panel.reassign') }}">
                                                        <x-edz.icon name="arrows-right-left"
                                                            class="w-4 h-4 shrink-0" />
                                                    </button>
                                                @endif
                                                @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_DELETE->value))
                                                    @if ($this->showTrash)
                                                        <button wire:click="restoreOrder('{{ $orderId }}')"
                                                            class="edz-btn edz-btn--ghost edz-btn--xs shrink-0 text-success-600"
                                                            title="{{ __('merchant.restore_order') }}">
                                                            <x-edz.icon name="arrow-uturn-left" class="w-4 h-4 shrink-0" />
                                                        </button>
                                                    @else
                                                        <button
                                                            class="edz-btn edz-btn--ghost edz-btn--xs text-danger-600 hover:text-danger-700 shrink-0"
                                                            x-data
                                                            x-on:click.prevent="EdzSwal.confirmDelete(() => { $wire.deleteOrder('{{ $orderId }}') })"
                                                            title="{{ __('merchant.delete_permanently') }}">
                                                            <x-edz.icon name="trash" class="w-4 h-4 shrink-0" />
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
                                                        <h4 class="font-semibold text-ink mb-2">{{ __('merchant_panel.items') }}</h4>
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
                                                        <h4 class="font-semibold text-ink mb-2">{{ __('merchant_panel.details') }}</h4>
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
                                                        <h4 class="font-semibold text-ink mb-2">{{ __('merchant_panel.assignment') }}</h4>
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
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="p-4 border-t border-surface-border">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-ink-muted">
                                    {{ $orders['from'] ?? 0 }}–{{ $orders['to'] ?? 0 }} / {{ $orders['total'] }}
                                </span>

                            </div>
                            @if ($orders['last_page'] > 1)
                                <div class="flex gap-1">
                                    @if ($orders['current_page'] > 1)
                                        <button wire:click="setPage({{ $orders['current_page'] - 1 }})"
                                            class="edz-btn edz-btn--ghost edz-btn--xs">&laquo;</button>
                                    @endif
                                    @foreach (range(max(1, $orders['current_page'] - 2), min($orders['last_page'], $orders['current_page'] + 2)) as $pg)
                                        <button wire:click="setPage({{ $pg }})"
                                            class="edz-btn edz-btn--xs {{ $pg === $orders['current_page'] ? 'edz-btn--primary' : 'edz-btn--ghost' }}">
                                            {{ $pg }}
                                        </button>
                                    @endforeach
                                    @if ($orders['current_page'] < $orders['last_page'])
                                        <button wire:click="setPage({{ $orders['current_page'] + 1 }})"
                                            class="edz-btn edz-btn--ghost edz-btn--xs">&raquo;</button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="p-8 text-center text-ink-muted">
                        <x-edz.icon name="cart" class="w-12 h-12 mx-auto mb-3 text-ink-muted opacity-40" />
                        <p>{{ __('merchant_panel.no_orders_found') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Create / Edit Modal --}}
    @if ($showCreateModal || $showEditModal)
        <x-edz.modal :isOpen="true" size="lg"
            wire:key="order-create-edit-{{ $showCreateModal ? 'create' : 'edit' }}-{{ $showEditModal ? $editingOrderId : 'new' }}">
            <form wire:submit="{{ $showEditModal ? 'submitEdit' : 'submitCreate' }}">
                <div class="p-6 space-y-5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-ink">
                            {{ $showEditModal ? __('Edit Order') : __('New Order') }}</h3>
                        <div class="flex items-center gap-2">
                            <button type="submit" class="edz-btn edz-btn--primary edz-btn--sm">
                                {{ $showEditModal ? __('Update') : __('Create') }}
                            </button>
                            <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                wire:click="{{ $showEditModal ? 'set(\'showEditModal\', false)' : 'set(\'showCreateModal\', false)' }}">
                                <x-edz.icon name="x-mark" class="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    {{-- Customer --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="edz-label">Name *</label>
                            <input type="text" wire:model="form.customer_name" class="edz-input text-sm" required>
                            @error('form.customer_name')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="edz-label">Phone *</label>
                            <input type="tel" wire:model="form.customer_phone" class="edz-input text-sm"
                                required>
                            @error('form.customer_phone')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="edz-label">Phone 2</label>
                            <input type="tel" wire:model="form.phone_secondary" class="edz-input text-sm">
                        </div>
                        <div>
                            <label class="edz-label">Wilaya</label>
                            <select wire:model="form.state_id" wire:change="loadCities($event.target.value)"
                                class="edz-input text-sm">
                                <option value="">—</option>
                                @foreach ($this->allStates as $st)
                                    <option value="{{ $st['id'] }}">{{ $st['name'] }}</option>
                                @endforeach
                            </select>
                            @error('form.state_id')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="edz-label">City</label>
                            <select wire:model="form.city_id" class="edz-input text-sm">
                                <option value="">—</option>
                                @foreach ($this->allCities as $city)
                                    <option value="{{ $city['id'] }}">{{ $city['name'] }}</option>
                                @endforeach
                            </select>
                            @error('form.city_id')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="edz-label">Address</label>
                            <input type="text" wire:model="form.address" class="edz-input text-sm">
                        </div>
                    </div>

                    {{-- Order Info --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="edz-label">Delivery</label>
                            <select wire:model="form.delivery_type" class="edz-input text-sm">
                                <option value="home">Home</option>
                                <option value="stopdesk">Stop Desk</option>
                            </select>
                        </div>
                        <div>
                            <label class="edz-label">Shipment</label>
                            <select wire:model="form.shipment_type" class="edz-input text-sm">
                                <option value="delivery">Delivery</option>
                                <option value="exchange">Exchange</option>
                                <option value="pickup">Pickup</option>
                            </select>
                        </div>
                        <div>
                            <label class="edz-label">Payment</label>
                            <select wire:model="form.payment_method" class="edz-input text-sm">
                                <option value="cod">COD</option>
                            </select>
                        </div>
                        <div>
                            <label class="edz-label">Weight (kg)</label>
                            <input type="number" wire:model="form.weight_kg" step="0.01"
                                class="edz-input text-sm">
                        </div>
                    </div>

                    {{-- Shipping assignment (done at confirmation time):
                         carrier first, then desks of the same wilaya with
                         matching commune sorted first. --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-data="{
                        get desks() {
                            const all = {{ \Illuminate\Support\Js::from($editDesks) }};
                            const pid = $wire.form.shipping_provider_id || '';
                            const sid = $wire.form.state_id || '';
                            const sel = $wire.form.stopdesk_point_id || '';
                            return all
                                .filter(d =>
                                    d.id === sel ||
                                    (!pid || d.shipping_provider_id === pid) &&
                                    (!sid || d.state_id === sid))
                                .sort((a, b) =>
                                    (b.city_id === ($wire.form.city_id || '')) -
                                    (a.city_id === ($wire.form.city_id || '')));
                        }
                    }">
                        <div>
                            <label class="edz-label">Shipping Company</label>
                            <select wire:model="form.shipping_provider_id" class="edz-input text-sm">
                                <option value="">—</option>
                                @foreach ($editProviders as $provider)
                                    <option value="{{ $provider['id'] }}">{{ $provider['name'] }}</option>
                                @endforeach
                            </select>
                            <span class="text-xs text-gray-400 mt-1 block">Assigned at confirmation</span>
                        </div>
                        <div>
                            <label class="edz-label">Pickup Desk</label>
                            <select wire:model="form.stopdesk_point_id" class="edz-input text-sm">
                                <option value="">—</option>
                                <template x-for="desk in desks" :key="desk.id">
                                    <option :value="desk.id" x-text="desk.name + ' - ' + (desk.address || '')">
                                    </option>
                                </template>
                            </select>
                            <span class="text-xs text-gray-400 mt-1 block">Stopdesk orders only</span>
                        </div>
                    </div>

                    {{-- Items --}}
                    <div>
                        <label class="edz-label">Products</label>
                        <input type="text" wire:model.live.debounce.300ms="formProductSearch"
                            placeholder="Search products..." class="edz-input text-sm mb-2">
                        @if (!empty($formProductResults))
                            <div class="border border-surface-border rounded-lg max-h-40 overflow-y-auto mb-2">
                                @foreach ($formProductResults as $pv)
                                    <button wire:click="addFormItem('{{ $pv['id'] }}')"
                                        class="w-full text-left px-3 py-2 text-sm hover:bg-surface-secondary dark:hover:bg-ink-700 flex justify-between">
                                        <span>{{ $pv['product']['name'] ?? '' }} — {{ $pv['name'] }}</span>
                                        <span class="text-ink-muted">{{ currency($pv['price'] ?? 0) }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        @if (!empty($form['items']))
                            <div class="space-y-2 mt-2">
                                @foreach ($form['items'] as $idx => $item)
                                    <div
                                        class="flex items-center gap-3 p-2 bg-surface-secondary dark:bg-ink-800 rounded-lg text-sm">
                                        <span class="flex-1 truncate text-ink">{{ $item['name'] }}</span>
                                        <input type="number" value="{{ $item['quantity'] }}"
                                            wire:change="updateFormItemQty({{ $idx }}, parseInt($event.target.value))"
                                            min="1" class="edz-input text-xs w-16 text-center">
                                        <span
                                            class="text-ink-muted w-20 text-right">{{ currency($item['price'] * $item['quantity']) }}</span>
                                        <button wire:click="removeFormItem({{ $idx }})"
                                            class="text-danger-400 hover:text-danger-600">
                                            <x-edz.icon name="x-mark" class="w-4 h-4" />
                                        </button>
                                    </div>
                                @endforeach
                                <div class="text-right font-bold text-ink pt-2 border-t border-surface-border">
                                    Total:
                                    {{ currency(collect($form['items'])->sum(fn($i) => $i['price'] * $i['quantity'])) }}
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="edz-label">Notes</label>
                        <textarea wire:model="form.notes" rows="2" class="edz-input text-sm"></textarea>
                    </div>
                </div>
            </form>
        </x-edz.modal>
    @endif

    {{-- Reassign Modal --}}
    @if ($showReassignModal)
        <x-edz.modal :isOpen="true" wire:key="order-reassign-modal">
            <div class="p-6 space-y-4">
                <h3 class="text-lg font-bold text-ink">{{ __('Reassign Order') }}</h3>
                <div>
                    <label class="edz-label">{{ __('Assign to') }} *</label>
                    <select wire:model="reassignMembershipId" class="edz-input text-sm">
                        <option value="">— {{ __('Select Agent') }} —</option>
                        @foreach ($allMembers as $m)
                            <option value="{{ $m['id'] }}">{{ $m['user']['name'] ?? $m['id'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                        wire:click="set('showReassignModal', false)">{{ __('Cancel') }}</button>
                    <button wire:click="submitReassign"
                        class="edz-btn edz-btn--primary edz-btn--sm">{{ __('Reassign') }}</button>
                </div>
            </div>
        </x-edz.modal>
    @endif
</div>
