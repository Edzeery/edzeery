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
        'product' => '',
        'source' => null,
    ],
    'orders' => [],
    'page' => 1,
    'visibleColumns' => [],
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

    // Create/Edit modal
    'showCreateModal' => false,
    'showEditModal' => false,
    'showProductPickerModal' => false,
    'showVariantPickerModal' => false,
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
        'discount_type' => null,
        'discount_value' => null,
        'discount_reason' => '',
        'notes' => '',
        'weight_kg' => '',
        'items' => [],
    ],
    'formProductResults' => [],
    'formProductView' => 'list', // 'list' | 'variants'
    'formSelectedProduct' => null,
    'formSelectedItems' => [],

    // Confirmation-time shipping assignment (carrier + desk)
    'editProviders' => [],
    'editDesks' => [],

    // Order detail expand
    'expandedOrderId' => null,

    // Reassign modal
    'showReassignModal' => false,
    'reassignOrderId' => null,
    'reassignMembershipId' => '',
]);

updated(['search'], function (): void {
    $this->page = 1;
    $this->loadOrders();
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
    $this->loadOrders();
});

$syncFormSelectedItems = function (): void {
    $this->formSelectedItems = collect($this->form['items'])
        ->pluck('quantity', 'product_variant_id')
        ->toArray();
    $this->dispatch('selected-items-updated', items: $this->formSelectedItems);
};

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
    if (!$service->canTransition($order, $statusKey)) {
        $this->dispatch('swal', type: 'error', title:
         __('status_transition.invalid_transition', ['from' => $order->status?->name ?? '—',
          'to' => $statusKey ?? '—']));
        return;
    }

    $service->transition($order, $statusKey, null, $membership);

    $this->page = 1;
    $this->loadOrders();

    $this->dispatch('swal', type: 'success', title: __('status_transition.order_status_updated',
     ['order_number' => $order->number, 'new_status' => $order->status?->name ?? '—']));
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
        'discount_type' => null,
        'discount_value' => null,
        'discount_reason' => '',
        'notes' => '',
        'weight_kg' => '',
        'items' => [],
    ];
    $this->formProductView = 'list';
    $this->formSelectedProduct = null;
    $this->loadProducts();
    $this->showCreateModal = true;
};

$loadProducts = function (): void {
    $storeId = currentStoreId();
    $this->formProductResults = Product::with(['primaryImage', 'variants:id,product_id,name,sku,price,stock'])
        ->select('id', 'name', 'price', 'type')
        ->where('store_id', $storeId)
        ->where('is_active', true)
        ->orderByDesc('sort_order')
        ->orderByDesc('created_at')
        ->limit(100)
        ->get()
        ->map(function ($product) use ($storeId) {
            $variants = $product->variants;
            $prices = $variants->pluck('price')->filter();
            $imageUrl = $product->primaryImage?->path ? Storage::disk('public')->url($product->primaryImage->path) : asset('img/icons/noimg.png');
            $minPrice = $prices->min() ?? ($product->price ?? 0);
            $maxPrice = $prices->max() ?? ($product->price ?? 0);
            $firstVariant = $variants->count() === 1 ? $variants->first() : null;
            return [
                'id' => $firstVariant?->id ?? null,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'image_url' => $imageUrl,
                'variant_count' => $variants->count(),
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'price_range' => $minPrice != $maxPrice
                    ? currency($minPrice) . ' — ' . currency($maxPrice)
                    : currency($minPrice),
                'has_variants' => $product->hasVariants(),
                'first_variant' => $firstVariant
                    ? [
                        'id' => $firstVariant->id,
                        'name' => $firstVariant->name,
                        'sku' => $firstVariant->sku,
                        'price' => $firstVariant->price,
                        'price_formatted' => currency($firstVariant->price),
                        'stock' => $firstVariant->stock,
                        'stock_status' => $firstVariant->stock <= 0 ? 'out' : ($firstVariant->stock <= 5 ? 'low' : 'ok'),
                        'stock_text' => $firstVariant->stock <= 0
                            ? __('merchant_panel.out_of_stock')
                            : $firstVariant->stock . ' ' . __('merchant_panel.left'),
                    ]
                    : null,
            ];
        })
        ->toArray();
};

$selectProduct = function (string $productId): void {
    $product = Product::with(['primaryImage', 'variants.optionValues.option'])
        ->where('id', $productId)
        ->where('store_id', currentStoreId())
        ->first();

    if (!$product) {
        return;
    }

    $imageUrl = $product->primaryImage?->path ? Storage::disk('public')->url($product->primaryImage->path) : asset('img/icons/noimg.png');

    $this->formSelectedProduct = [
        'id' => $product->id,
        'name' => $product->name,
        'image_url' => $imageUrl,
        'variants' => $product->variants
            ->map(function ($v) {
                $optionLabels = $v->optionValues->map(fn($ov) => ($ov->option?->name ?? '') . ': ' . $ov->value)->implode(', ');
                return [
                    'id' => $v->id,
                    'name' => $v->name,
                    'option_labels' => $optionLabels,
                    'sku' => $v->sku,
                    'price' => $v->price,
                    'stock' => $v->stock,
                    'is_active' => $v->is_active,
                ];
            })
            ->toArray(),
    ];
    $this->formProductView = 'variants';
};

$backToProducts = function (): void {
    $this->formProductView = 'list';
    $this->formSelectedProduct = null;
};

$addFormItem = function (string $variantId): void {
    $variant = ProductVariant::with(['product', 'product.primaryImage'])
        ->where('store_id', currentStoreId())
        ->findOrFail($variantId);
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
            'sku' => $variant->sku ?? '',
            'price' => $variant->price ?? ($variant->product?->price ?? 0),
            'quantity' => 1,
            'stock' => $variant->stock ?? 0,
            'weight' => $variant->weight ?? 0,
            'image_url' => $variant->product?->primaryImage?->path ? Storage::disk('public')->url($variant->product->primaryImage->path) : asset('img/icons/noimg.png'),
        ];
    }
    $this->syncFormSelectedItems();
};

$addFormItemByBarcode = function (string $code): void {
    if (strlen($code) < 2) {
        return;
    }
    $storeId = currentStoreId();
    $variant = ProductVariant::with(['product', 'product.primaryImage'])
        ->whereHas('product', fn($q) => $q->where('store_id', $storeId))
        ->where(function ($q) use ($code) {
            $q->where('barcode', $code)->orWhere('sku', $code);
        })
        ->first();

    if (!$variant) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.product_not_found')]);
        return;
    }

    // Check if already in items
    foreach ($this->form['items'] as $idx => &$item) {
        if ($item['product_variant_id'] === $variant->id) {
            $this->form['items'][$idx]['quantity']++;
            $this->syncFormSelectedItems();
            return;
        }
    }
    unset($item);

    $this->form['items'][] = [
        'product_variant_id' => $variant->id,
        'product_id' => $variant->product_id,
        'name' => ($variant->product?->name ?? '') . ' — ' . $variant->name,
        'sku' => $variant->sku ?? '',
        'price' => $variant->price ?? ($variant->product?->price ?? 0),
        'quantity' => 1,
        'stock' => $variant->stock ?? 0,
        'weight' => $variant->weight ?? 0,
        'image_url' => $variant->product?->primaryImage?->path ? Storage::disk('public')->url($variant->product->primaryImage->path) : asset('img/icons/noimg.png'),
    ];
    $this->syncFormSelectedItems();
};

$removeFormItem = function (int $index): void {
    unset($this->form['items'][$index]);
    $this->form['items'] = array_values($this->form['items']);
    $this->syncFormSelectedItems();
};

$updateFormItemQty = function (int $index, int $qty): void {
    if (isset($this->form['items'][$index])) {
        $this->form['items'][$index]['quantity'] = max(1, $qty);
    }
};

$updateFormItemPrice = function (int $index, $price): void {
    if (isset($this->form['items'][$index])) {
        $this->form['items'][$index]['price'] = max(0, (float) $price);
    }
};

$submitCreate = function (): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);

    $storeId = currentStoreId();

    \Illuminate\Support\Facades\Validator::make($this->form, [
        'customer_phone' => 'required|string|max:20|regex:/^0[5-7]\d{8}$/',
        'customer_name' => 'required|string|max:255',
        'items' => 'required|array|min:1',
        'items.*.product_variant_id' => 'required|string',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.price' => 'required|numeric|min:0',
        'delivery_type' => 'required|in:home,stopdesk',
        'shipment_type' => 'required|in:delivery,exchange,pickup',
        'payment_method' => 'required|in:cod',
        'address' => 'required_if:delivery_type,home|nullable|string|max:1000',
        'state_id' => 'required_if:delivery_type,home|nullable|exists:states,id',
        'city_id' => 'required_if:delivery_type,home|nullable|exists:cities,id',
        'discount_type' => 'nullable|in:amount,percent',
        'discount_value' => 'nullable|numeric|min:0',
        'discount_reason' => 'nullable|string|max:255',
        'weight_kg' => 'nullable|numeric|min:0',
        'notes' => 'nullable|string|max:500',
    ])->validate();

    // C3+C4: Validate prices from DB + check stock
    $variantIds = collect($this->form['items'])->pluck('product_variant_id')->filter()->toArray();
    $variantMap = ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id');
    $store = \App\Models\Stores\Store::find($storeId);
    $tracksInventory = \App\Domains\Cart\Support\OrderRules::tracksInventory($store);

    foreach ($this->form['items'] as $idx => $itemData) {
        $vid = $itemData['product_variant_id'] ?? null;
        if (!$vid || !$variantMap->has($vid)) {
            continue;
        }
        $variant = $variantMap[$vid];

        // C3: Always use DB price
        $this->form['items'][$idx]['price'] = $variant->price ?? $itemData['price'];
        $this->form['items'][$idx]['product_id'] = $variant->product_id;

        // C4: Check stock
        if ($tracksInventory) {
            $available = $variant->quantity - $variant->reserved;
            if ($available < $itemData['quantity']) {
                $this->dispatch('swal', type: 'error', title: __('merchant_panel.insufficient_stock', ['variant' => $variant->name, 'available' => max(0, $available)]));
                return;
            }
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
            'discount_type' => $this->form['discount_type'],
            'discount_value' => $this->form['discount_value'] ?: null,
            'discount_reason' => $this->form['discount_reason'] ?: null,
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
    $this->loadOrders();

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
        'discount_type' => $order->discount_type,
        'discount_value' => $order->discount_value,
        'discount_reason' => $order->discount_reason ?? '',
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
                    'sku' => $i->variant?->sku ?? '',
                    'price' => $i->price,
                    'quantity' => $i->quantity,
                    'stock' => $i->variant?->stock ?? 0,
                    'weight' => $i->variant?->weight ?? 0,
                    'image_url' => $i->product?->primaryImage?->path ? Storage::disk('public')->url($i->product->primaryImage->path) : asset('img/icons/noimg.png'),
                ],
            )
            ->toArray(),
    ];
    $this->formProductResults = [];
    $this->formProductView = 'list';
    $this->formSelectedProduct = null;
    $this->loadProducts();
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

    // Block edit if shipped or later (dynamic: compare sort_order against 'shipped')
    $shippedSortOrder = \App\Models\Status::where('type', 'order')->where('key', 'shipped')->value('sort_order');
    if ($order->status && $shippedSortOrder !== null && $order->status->sort_order >= $shippedSortOrder) {
        $this->dispatch('swal', type: 'error', title: __('Cannot edit shipped/closed orders'));
        return;
    }

    \Illuminate\Support\Facades\Validator::make($this->form, [
        'customer_phone' => 'required|string|max:20|regex:/^0[5-7]\d{8}$/',
        'customer_name' => 'required|string|max:255',
        'items' => 'required|array|min:1',
        'items.*.product_variant_id' => 'required|string',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.price' => 'required|numeric|min:0',
        'delivery_type' => 'required|in:home,stopdesk',
        'shipment_type' => 'required|in:delivery,exchange,pickup',
        'payment_method' => 'required|in:cod',
        'address' => 'required_if:delivery_type,home|nullable|string|max:1000',
        'state_id' => 'required_if:delivery_type,home|nullable|exists:states,id',
        'city_id' => 'required_if:delivery_type,home|nullable|exists:cities,id',
        'discount_type' => 'nullable|in:amount,percent',
        'discount_value' => 'nullable|numeric|min:0',
        'discount_reason' => 'nullable|string|max:255',
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
        'discount_type' => $this->form['discount_type'],
        'discount_value' => $this->form['discount_value'] ?: null,
        'discount_reason' => $this->form['discount_reason'] ?: null,
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

    // C3+C4: Validate prices from DB + check stock for new/changed items
    $variantMap = ProductVariant::whereIn('id', $incomingVariantIds)->get()->keyBy('id');

    foreach ($this->form['items'] as $idx => $itemData) {
        $vid = $itemData['product_variant_id'] ?? null;
        if (!$vid || !$variantMap->has($vid)) {
            continue;
        }
        $variant = $variantMap[$vid];

        // C3: Always use DB price — never trust client-submitted price
        $this->form['items'][$idx]['price'] = $variant->price ?? $itemData['price'];
        $this->form['items'][$idx]['product_id'] = $variant->product_id;

        // C4: Check stock for new items or increased quantities
        $prevQty = 0;
        if (isset($existingItems[$vid])) {
            $prevQty = $existingItems[$vid]->quantity;
        }
        $delta = $itemData['quantity'] - $prevQty;
        if ($delta > 0 && \App\Domains\Cart\Support\OrderRules::tracksInventory($order->store)) {
            $available = $variant->quantity - $variant->reserved;
            if ($available < $delta) {
                $this->dispatch('swal', type: 'error', title: __('merchant_panel.insufficient_stock', ['variant' => $variant->name, 'available' => max(0, $available)]));
                return;
            }
        }
    }

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
    $this->loadOrders();

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

$refreshOrders = function (): void {
    $this->loadOrders();
};
?>

<div x-data="{ openFilter: null, openColToggle: false, filterPos: { top: 0, left: 0 }, positionFilter(e) { let r = e.currentTarget.getBoundingClientRect();
        this.filterPos = { top: r.bottom + 4, left: Math.max(8, r.left) }; } }">
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
                <button wire:click="openCreateModal" class="edz-btn edz-btn--primary edz-btn--sm">
                    <x-edz.icon name="plus" class="w-4 h-4" />
                    {{ __('merchant_panel.new_order') }}
                </button>
            @endif
            <button wire:click="refreshOrders" class="edz-btn edz-btn--ghost edz-btn--sm">
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
                    placeholder="{{ __('merchant.search_orders') }} — {{ __('merchant_panel.products') }}, SKU, barcode..."
                    class="edz-input text-sm ps-9 pe-9">
                <x-edz.icon name="search"
                    class="absolute start-3 top-1/2 -translate-y-1/2 w-4 h-4 text-ink-muted pointer-events-none" />
                <button wire:click="loadOrders" type="button"
                    class="absolute end-2 top-1/2 -translate-y-1/2 text-ink-muted hover:text-accent-500 transition">
                    <x-edz.icon name="arrow-right" class="w-4 h-4" />
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
                    class="edz-btn edz-btn--ghost edz-btn--sm {{ $this->filters['source'] ? 'text-accent-600' : '' }}">
                    <x-edz.icon name="user" class="w-4 h-4" />
                    {{ $this->filters['source'] === 'manual' ? __('merchant.delivery_man') : ($this->filters['source'] === 'store' ? __('merchant_panel.store') : __('merchant_panel.source')) }}
                    <x-edz.icon name="chevron-down" class="w-3 h-3" />
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
                    class="edz-btn edz-btn--ghost edz-btn--sm {{ $this->filters['delivery_type'] ? 'text-accent-600' : '' }}">
                    <x-edz.icon name="home" class="w-4 h-4" />
                    {{ $this->filters['delivery_type'] === 'stopdesk' ? __('storefront.stop_desk') : ($this->filters['delivery_type'] === 'home' ? __('storefront.home_delivery') : __('storefront.delivery_type')) }}
                    <x-edz.icon name="chevron-down" class="w-3 h-3" />
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
                    class="edz-btn edz-btn--ghost edz-btn--sm {{ $this->filters['shipping_provider'] ? 'text-accent-600' : '' }}">
                    <x-edz.icon name="truck" class="w-4 h-4" />
                    {{ collect($this->allProviders)->firstWhere('id', $this->filters['shipping_provider'])['name'] ?? __('merchant.assign_delivery_man') }}
                    <x-edz.icon name="chevron-down" class="w-3 h-3" />
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

    </div>

    {{-- Active filter summary + Clear --}}
    @if (array_filter($this->filters))
        <div class="mb-3 flex items-center gap-2 flex-wrap">
            @if (!empty($this->filters['wilaya']))
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-accent-50 text-accent-700 dark:bg-accent-900/30 dark:text-accent-400">
                    {{ collect($this->allStates)->firstWhere('id', $this->filters['wilaya'])['name'] ?? '' }}
                    <button wire:click="setFilter('wilaya', null)" class="hover:text-accent-900"><x-edz.icon
                            name="x-mark" class="w-3 h-3" /></button>
                </span>
            @endif
            @if (!empty($this->filters['city']))
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-accent-50 text-accent-700 dark:bg-accent-900/30 dark:text-accent-400">
                    {{ collect($this->allCities)->firstWhere('id', $this->filters['city'])['name'] ?? '' }}
                    <button wire:click="setFilter('city', null)" class="hover:text-accent-900"><x-edz.icon
                            name="x-mark" class="w-3 h-3" /></button>
                </span>
            @endif
            @if (!empty($this->filters['status']))
                @foreach ($this->allStatuses as $s)
                    @if (in_array($s['id'], $this->filters['status']))
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-accent-50 text-accent-700 dark:bg-accent-900/30 dark:text-accent-400">
                            {{ $s['label'] }}
                            <button wire:click="toggleStatusFilter('{{ $s['id'] }}')"
                                class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
                        </span>
                    @endif
                @endforeach
            @endif
            @if (!empty($this->filters['assigned_to']))
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-accent-50 text-accent-700 dark:bg-accent-900/30 dark:text-accent-400">
                    {{ collect($this->allMembers)->firstWhere('id', $this->filters['assigned_to'])['user']['name'] ?? '' }}
                    <button wire:click="setFilter('assigned_to', null)" class="hover:text-accent-900"><x-edz.icon
                            name="x-mark" class="w-3 h-3" /></button>
                </span>
            @endif
            @if (!empty($this->filters['date_from']) || !empty($this->filters['date_to']))
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-accent-50 text-accent-700 dark:bg-accent-900/30 dark:text-accent-400">
                    {{ $this->filters['date_from'] ?? '...' }} — {{ $this->filters['date_to'] ?? '...' }}
                    <button @click="$wire.setFilter('date_from', null); $wire.setFilter('date_to', null)"
                        class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
                </span>
            @endif
            <button wire:click="clearFilters" class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 text-xs">
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
                <button wire:click="restoreAll"
                    class="edz-btn edz-btn--ghost edz-btn--sm">{{ __('merchant.restore_all') }}</button>
                <button x-data
                    x-on:click.prevent="(async () => { if (await EdzSwal.confirmDelete()) await $wire.forceDeleteAll() })()"
                    class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600">{{ __('merchant.empty_trash') }}</button>
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
                    <button @click="open = !open" class="edz-btn edz-btn--ghost edz-btn--sm">
                        <x-edz.icon name="user-plus" class="w-4 h-4" />
                        {{ __('merchant.bulk_assign_agent') }}
                    </button>
                    <div x-show="open" x-transition
                        class="absolute z-50 right-0 mt-1 w-56 bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5 max-h-60 overflow-y-auto edz-scroll">
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
                        <div x-show="open" x-transition
                            class="absolute z-50 right-0 mt-1 w-56 bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5">
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
                <button x-data
                    x-on:click.prevent="(async () => { if (await EdzSwal.confirmDelete()) await $wire.bulkDelete() })()"
                    class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600">
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
                                                                    @click="open = false"
                                                                    class="w-full text-left flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary dark:hover:bg-ink-700 {{ $s['id'] == $order['status_id'] ? 'font-bold' : '' }}">
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
                                                            <x-edz.icon name="arrow-uturn-left"
                                                                class="w-4 h-4 shrink-0" />
                                                        </button>
                                                    @else
                                                        <button
                                                            class="edz-btn edz-btn--ghost edz-btn--xs text-danger-600 hover:text-danger-700 shrink-0"
                                                            x-data
                                                            x-on:click.prevent="(async () => { if (await EdzSwal.confirmDelete('{{ $order['number'] ?? '' }}')) await $wire.deleteOrder('{{ $orderId }}') })()"
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
    @if ($showCreateModal || $showEditModal)
        <x-edz.modal :isOpen="true" :showCloseButton="false" :preventClose="true" size="lg" class="  edz-scroll"
            wire:key="order-create-edit-{{ $showCreateModal ? 'create' : 'edit' }}-{{ $showEditModal ? $editingOrderId : 'new' }}">
            <form wire:submit="{{ $showEditModal ? 'submitEdit' : 'submitCreate' }}">
                <div class="p-6 space-y-5">
                    {{-- Header --}}
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-ink">
                            {{ $showEditModal ? __('merchant_panel.edit_order') : __('merchant_panel.new_order') }}
                        </h3>
                        <div class="flex items-center gap-2">
                            <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                wire:click="{{ $showEditModal ? 'set(\'showEditModal\', false)' : 'set(\'showCreateModal\', false)' }}">
                                <x-edz.icon name="x-mark" class="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    {{-- Customer --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.name') }} *</label>
                            <input type="text" wire:model="form.customer_name" class="edz-input text-sm" required>
                            @error('form.customer_name')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.phone') }} *</label>
                            <input type="tel" wire:model="form.customer_phone" class="edz-input text-sm"
                                required>
                            @error('form.customer_phone')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.phone_secondary') }}</label>
                            <input type="tel" wire:model="form.phone_secondary" class="edz-input text-sm">
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.state') }}</label>
                            <x-edz.select wire:model="form.state_id" wire:change="loadCities($event.target.value)"
                                :options="$this->allStates" option-value="id" option-label="name" placeholder="—"
                                size="sm" />
                            @error('form.state_id')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.city') }}</label>
                            <x-edz.select wire:model="form.city_id" :options="$this->allCities" option-value="id"
                                option-label="name" placeholder="—" size="sm" />
                            @error('form.city_id')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="edz-label">{{ __('merchant_panel.address') }}</label>
                            <input type="text" wire:model="form.address" class="edz-input text-sm">
                        </div>
                    </div>

                    {{-- Delivery Type Toggle --}}
                    <div x-data="{ delivery: $wire.form.delivery_type }" x-init="$watch('delivery', v => $wire.set('form.delivery_type', v))"
                        x-effect="delivery = $wire.form.delivery_type">
                        <label class="edz-label">{{ __('merchant_panel.delivery') }}</label>
                        <div class="inline-flex rounded-lg border border-surface-border overflow-hidden">
                            <button type="button"
                                :class="delivery === 'home' ? 'bg-primary-500 text-white' : 'bg-surface text-ink'"
                                @click="delivery = 'home'" class="px-4 py-2 text-sm font-medium transition-colors">
                                <x-edz.icon name="home" class="w-4 h-4 inline mr-1" />
                                {{ __('merchant_panel.home_delivery_label') }}
                            </button>
                            <button type="button"
                                :class="delivery === 'stopdesk' ? 'bg-primary-500 text-white' : 'bg-surface text-ink'"
                                @click="delivery = 'stopdesk'"
                                class="px-4 py-2 text-sm font-medium transition-colors">
                                <x-edz.icon name="building-storefront" class="w-4 h-4 inline mr-1" />
                                {{ __('merchant_panel.stop_desk_label') }}
                            </button>
                        </div>
                    </div>

                    {{-- Order Info --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.shipment') }}</label>
                            <x-edz.select wire:model="form.shipment_type" :options="[
                                ['value' => 'delivery', 'label' => __('merchant_panel.delivery')],
                                ['value' => 'exchange', 'label' => __('merchant_panel.exchange_label')],
                                ['value' => 'pickup', 'label' => __('merchant_panel.pickup_label')],
                            ]" size="sm" />
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.payment_method') }}</label>
                            <x-edz.select wire:model="form.payment_method" :options="[['value' => 'cod', 'label' => __('merchant_panel.cod')]]" size="sm" />
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.weight_kg') }}</label>
                            <input type="number" wire:model="form.weight_kg" step="0.01"
                                class="edz-input text-sm">
                        </div>
                    </div>

                    {{-- Shipping assignment (edit only) --}}
                    @if ($showEditModal)
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
                                <label class="edz-label">{{ __('merchant_panel.shipping_company') }}</label>
                                <x-edz.select wire:model="form.shipping_provider_id" :options="$editProviders"
                                    option-value="id" option-label="name" placeholder="—" size="sm" />
                            </div>
                            <div>
                                <label class="edz-label">{{ __('merchant_panel.pickup_desk') }}</label>
                                <select wire:model="form.stopdesk_point_id" class="edz-input text-sm">
                                    <option value="">—</option>
                                    <template x-for="desk in desks" :key="desk.id">
                                        <option :value="desk.id"
                                            x-text="desk.name + ' - ' + (desk.address || '')">
                                        </option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    @endif

                    {{-- Products --}}
                    <div>
                        <label class="edz-label">{{ __('merchant_panel.products') }}</label>

                        {{-- Trigger to open product picker modal --}}
                        <button type="button" @click="openProductPicker()"
                            class="w-full flex items-center gap-3 px-4 py-3 bg-surface-secondary dark:bg-ink-800
                                   border border-dashed border-surface-border dark:border-ink-600 rounded-xl
                                   hover:border-primary-400 dark:hover:border-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/10
                                   transition-colors text-sm text-ink-muted group">
                            <x-edz.icon name="qr-code" class="w-5 h-5 text-ink-muted group-hover:text-primary-500 transition-colors" />
                            <span class="flex-1 text-start">{{ __('merchant_panel.search_products_barcode') }}</span>
                            <x-edz.icon name="plus" class="w-4 h-4 text-ink-muted group-hover:text-primary-500 transition-colors" />
                        </button>

                        {{-- Items list --}}
                        @if (!empty($form['items']))
                            <div class="mt-3 space-y-2  overflow-y-auto max-h-[calc(80vh-475px)]  edz-scroll">
                                @foreach ($form['items'] as $idx => $item)
                                    <div
                                        class="flex items-center gap-3 p-3 bg-surface-secondary dark:bg-ink-800 rounded-lg">
                                        {{-- Image --}}
                                        <img src="{{ $item['image_url'] ?? asset('img/icons/noimg.png') }}"
                                            alt=""
                                            class="w-12 h-12 rounded-lg object-cover bg-surface shrink-0">

                                        {{-- Name + SKU --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-ink truncate">{{ $item['name'] }}
                                            </div>
                                            <div class="text-xs text-ink-muted mt-0.5">
                                                SKU: {{ $item['sku'] ?? '—' }}
                                                @if (($item['stock'] ?? 0) <= 0)
                                                    <span
                                                        class="text-danger-500 ml-2">{{ __('merchant_panel.out_of_stock') }}</span>
                                                @elseif (($item['stock'] ?? 0) <= 5)
                                                    <span class="text-warning-500 ml-2">{{ $item['stock'] }}
                                                        {{ __('merchant_panel.left') }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Quantity stepper --}}
                                        <div
                                            class="flex items-center rounded-lg border border-surface-border dark:border-ink-600 overflow-hidden shrink-0">
                                            <button type="button"
                                                wire:click="updateFormItemQty({{ $idx }}, {{ max(1, $item['quantity'] - 1) }})"
                                                :disabled="{{ $item['quantity'] <= 1 ? 'true' : 'false' }}"
                                                class="w-8 h-8 flex items-center justify-center bg-surface dark:bg-ink-700
                                                       text-ink-muted hover:bg-surface-secondary dark:hover:bg-ink-600
                                                       transition-colors disabled:opacity-30 disabled:cursor-not-allowed
                                                       text-sm font-medium select-none">
                                                &minus;
                                            </button>
                                            <input type="number" value="{{ $item['quantity'] }}"
                                                wire:change="updateFormItemQty({{ $idx }}, parseInt($event.target.value))"
                                                min="1"
                                                class="w-10 h-8 text-center border-x border-surface-border dark:border-ink-600
                                                       bg-transparent text-sm font-semibold text-ink
                                                       focus:outline-none focus:ring-0
                                                       [appearance:textfield]
                                                       [&::-webkit-outer-spin-button]:appearance-none
                                                       [&::-webkit-inner-spin-button]:appearance-none">
                                            <button type="button"
                                                wire:click="updateFormItemQty({{ $idx }}, {{ $item['quantity'] + 1 }})"
                                                class="w-8 h-8 flex items-center justify-center bg-surface dark:bg-ink-700
                                                       text-ink-muted hover:bg-surface-secondary dark:hover:bg-ink-600
                                                       transition-colors disabled:opacity-30 disabled:cursor-not-allowed
                                                       text-sm font-medium select-none">
                                                &plus;
                                            </button>
                                        </div>

                                        {{-- Unit price (editable) --}}
                                        <div class="shrink-0 hidden sm:block">
                                            <input type="number" value="{{ $item['price'] }}"
                                                wire:change="updateFormItemPrice({{ $idx }}, parseFloat($event.target.value))"
                                                step="10" min="0"
                                                class="edz-input text-xs w-20 text-center py-1"
                                                placeholder="{{ __('merchant_panel.price') }}">
                                        </div>

                                        {{-- Line total --}}
                                        <div class="text-right shrink-0 w-24">
                                            <div class="text-sm font-bold text-ink tabular-nums">
                                                {{ currency($item['price'] * $item['quantity']) }}</div>
                                            <div class="text-xs text-ink-muted">{{ $item['quantity'] }} ×
                                                {{ currency($item['price']) }}</div>
                                        </div>

                                        {{-- Delete --}}
                                        <button type="button" wire:click="removeFormItem({{ $idx }})"
                                            class="text-danger-400 hover:text-danger-600 shrink-0 p-1 rounded hover:bg-danger-50 dark:hover:bg-danger-900/20 transition-colors">
                                            <x-edz.icon name="x-mark" class="w-4 h-4" />
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Order Summary — Horizontal --}}
                    @if (!empty($form['items']))
                        @php
                            $subtotal = collect($form['items'])->sum(fn($i) => $i['price'] * $i['quantity']);
                            $totalWeight = collect($form['items'])->sum(fn($i) => ($i['weight'] ?? 0) * $i['quantity']);
                            $discount = 0;
                            if ($form['discount_type'] && $form['discount_value']) {
                                $discount =
                                    $form['discount_type'] === 'amount'
                                        ? (float) $form['discount_value']
                                        : round(($subtotal * (float) $form['discount_value']) / 100, 2);
                            }
                            $grandTotal = max(0, $subtotal - $discount);
                        @endphp
                        <div class="bg-surface-secondary dark:bg-ink-800 rounded-lg p-4">
                            {{-- Top row: main stats --}}
                            <div class="flex items-center justify-between gap-4 flex-wrap">
                                <div class="flex items-center gap-4 text-sm">
                                    <span class="text-ink-muted">{{ __('merchant_panel.items') }}:</span>
                                    <span
                                        class="font-semibold text-ink">{{ collect($form['items'])->sum('quantity') }}</span>
                                </div>
                                <div class="flex items-center gap-4 text-sm">
                                    <span class="text-ink-muted">{{ __('merchant_panel.subtotal') }}:</span>
                                    <span
                                        class="font-semibold text-ink tabular-nums">{{ currency($subtotal) }}</span>
                                </div>
                                @if ($totalWeight > 0)
                                    <div class="flex items-center gap-4 text-sm">
                                        <span class="text-ink-muted">{{ __('merchant_panel.total_weight') }}:</span>
                                        <span
                                            class="font-medium text-ink tabular-nums">{{ number_format($totalWeight, 2) }}
                                            kg</span>
                                    </div>
                                @endif
                                <div class="flex items-center gap-4 text-sm">
                                    <span class="text-ink-muted">{{ __('merchant_panel.delivery_cost') }}:</span>
                                    <span class="text-success-500 font-medium">{{ __('merchant_panel.free') }}</span>
                                </div>
                            </div>

                            {{-- Discount row --}}
                            <div
                                class="flex items-center justify-between gap-4 mt-3 pt-3 border-t border-surface-border dark:border-ink-700">
                                <div class="flex items-center gap-2">
                                    <x-edz.select wire:model="form.discount_type" :options="[
                                        ['value' => '', 'label' => __('merchant_panel.discount')],
                                        ['value' => 'amount', 'label' => __('merchant_panel.fixed_amount')],
                                        ['value' => 'percent', 'label' => __('merchant_panel.percentage')],
                                    ]" size="sm"
                                        class="w-28" />
                                    @if ($form['discount_type'])
                                        <input type="number" wire:model="form.discount_value"
                                            class="edz-input text-xs py-1 w-20" min="0"
                                            placeholder="{{ $form['discount_type'] === 'percent' ? '%' : 'DZD' }}">
                                    @endif
                                    @if ($form['discount_type'] && $form['discount_value'])
                                        <input type="text" wire:model="form.discount_reason"
                                            class="edz-input text-xs py-1 flex-1 max-w-xs"
                                            placeholder="{{ __('merchant_panel.discount_reason') }}">
                                    @endif
                                </div>
                                <span
                                    class="text-sm font-medium tabular-nums {{ $discount > 0 ? 'text-danger-500' : 'text-ink-muted' }}">
                                    {{ $discount > 0 ? '-' . currency($discount) : '—' }}
                                </span>
                            </div>

                            {{-- Grand total row --}}
                            <div
                                class="flex items-center justify-between mt-3 pt-3 border-t border-surface-border dark:border-ink-700">
                                <span class="text-base font-bold text-ink">{{ __('merchant_panel.total') }}</span>
                                <span
                                    class="text-lg font-bold text-ink tabular-nums">{{ currency($grandTotal) }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Notes --}}
                    <div>
                        <label class="edz-label">{{ __('merchant_panel.notes') }}</label>
                        <textarea wire:model="form.notes" rows="2" class="edz-input text-sm"></textarea>
                    </div>

                    {{-- Submit --}}
                    <div class="flex justify-end gap-2 pt-2 border-t border-surface-border">
                        <button type="button" class="edz-btn edz-btn--ghost"
                            wire:click="{{ $showEditModal ? 'set(\'showEditModal\', false)' : 'set(\'showCreateModal\', false)' }}">
                            {{ __('buttons.cancel') }}
                        </button>
                        <button type="submit" class="edz-btn edz-btn--primary" wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 pointer-events-none">
                            {{ $showEditModal ? __('merchant_panel.update') : __('merchant_panel.create') }}
                        </button>
                    </div>
                </div>
            </form>
        </x-edz.modal>
    @endif

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
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 pointer-events-none">{{ __('merchant_panel.reassign') }}</button>
                </div>
            </div>
        </x-edz.modal>
    @endif

    {{-- Product Picker Modal --}}
    @if ($showProductPickerModal)
    <x-edz.modal :isOpen="true" :showCloseButton="false" :preventClose="true" size="md">
        <div class="flex flex-col max-h-[85vh]">
            {{-- Drag handle (mobile) --}}
            <div class="edz-modal__handle sm:hidden"></div>

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 pt-5 pb-3">
                <h3 class="text-lg font-bold text-ink">{{ __('merchant_panel.products') }}</h3>
                <button type="button" @click="open = false; closeProductPicker()"
                    class="edz-modal__close" style="position:static;">
                    <x-edz.icon name="x-mark" class="w-5 h-5" />
                </button>
            </div>

            {{-- Search --}}
            <div class="px-5 pb-3">
                <div class="relative">
                    <input type="text"
                        @input="onSearchInput($event)"
                        data-product-search-input
                        placeholder="{{ __('merchant_panel.search_products_barcode') }}"
                        class="edz-input text-sm ps-10 pe-10"
                        @keydown.enter.prevent="selectProductByBarcode($event)">
                    <x-edz.icon name="magnifying-glass"
                        class="w-4 h-4 absolute start-3 top-1/2 -translate-y-1/2 text-ink-muted pointer-events-none" />
                    <x-edz.icon name="qr-code"
                        class="w-4 h-4 absolute end-3 top-1/2 -translate-y-1/2 text-ink-muted pointer-events-none" />
                </div>
            </div>

            {{-- Counter --}}
            <div class="px-5 pb-2">
                <span class="text-xs text-ink-muted"
                      x-text="(searchTerm && searchTerm.length >= 2 ? visibleCount : {{ count($formProductResults) }}) + ' {{ __('merchant_panel.products') }}'"></span>
            </div>

            {{-- Product list --}}
            <div class="min-h-0 flex-1  max-h-[calc(100vh-475px)] overflow-y-auto edz-scroll px-5 pb-5">
                {{-- Skeleton loading (while loadProducts is executing) --}}
                <div wire:loading wire:target="loadProducts" class="space-y-3 py-2">
                    @foreach (range(1, 5) as $i)
                        <div class="flex items-center gap-3 py-2">
                            <div class="w-11 h-11 rounded-xl edz-skeleton shrink-0"></div>
                            <div class="flex-1 space-y-2">
                                <x-edz.skeleton width="{{ 40 + $i * 10 }}%" height="0.875rem" />
                                <x-edz.skeleton width="6rem" height="0.75rem" />
                            </div>
                            <x-edz.skeleton width="3.5rem" height="1rem" />
                        </div>
                    @endforeach
                </div>

                {{-- Product items (server-rendered) --}}
                <div wire:loading.remove wire:target="loadProducts" class="divide-y divide-surface-border dark:divide-ink-700">
                    @forelse ($formProductResults as $pv)
                        @php
                            $searchText = mb_strtolower($pv['product_name'] . ' ' . ($pv['first_variant']['sku'] ?? ''));
                        @endphp
                        <div data-search="{{ $searchText }}"
                             x-show="!searchTerm || searchTerm.length < 2 || $el.dataset.search.includes(searchTerm.toLowerCase())"
                             class="transition-opacity">

                            {{-- Multi-variant product --}}
                            @if ($pv['has_variants'] && ($pv['variant_count'] ?? 0) > 1)
                                <button type="button" @click="openVariants('{{ $pv['product_id'] }}')"
                                    class="w-full text-left py-3 hover:bg-surface-secondary dark:hover:bg-ink-700 flex items-center gap-3 text-sm transition-colors rounded-lg px-2 -mx-2">
                                    <img src="{{ $pv['image_url'] ?? asset('img/icons/noimg.png') }}"
                                        alt="" loading="lazy"
                                        class="w-11 h-11 rounded-xl object-cover bg-surface-secondary shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-ink truncate">{{ $pv['product_name'] }}</div>
                                        <div class="text-xs text-ink-muted mt-0.5">
                                            {{ $pv['variant_count'] }} {{ __('merchant_panel.variants') }}
                                        </div>
                                    </div>
                                    <span class="text-xs text-ink-muted shrink-0 tabular-nums">{{ $pv['price_range'] }}</span>
                                    <x-edz.icon name="chevron-left" class="w-4 h-4 text-ink-muted shrink-0 rtl:rotate-180" />
                                </button>

                            {{-- Single variant product --}}
                            @elseif ($pv['first_variant'])
                                @php
                                    $isProductSelected = isset($formSelectedItems[$pv['first_variant']['id']]);
                                @endphp
                                <button type="button"
                                    @if (!$isProductSelected) @click="selectProduct('{{ $pv['first_variant']['id'] }}')" @endif
                                    :disabled="isAddingProduct"
                                    class="w-full text-left py-3 flex items-center gap-3 text-sm transition-colors rounded-lg px-2 -mx-2
                                        {{ $isProductSelected
                                            ? 'bg-success-50/50 dark:bg-success-900/10 border border-success-200/50 dark:border-success-800/30'
                                            : 'hover:bg-surface-secondary dark:hover:bg-ink-700' }}
                                        disabled:opacity-50">
                                    <img src="{{ $pv['image_url'] ?? asset('img/icons/noimg.png') }}"
                                        alt="" loading="lazy"
                                        class="w-11 h-11 rounded-xl object-cover bg-surface-secondary shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-ink truncate">{{ $pv['product_name'] }}</div>
                                        <div class="text-xs text-ink-muted mt-0.5 flex items-center gap-1.5">
                                            <span>SKU: {{ $pv['first_variant']['sku'] ?? '—' }}</span>
                                            @if (($pv['first_variant']['stock_status'] ?? '') === 'out')
                                                <span class="text-danger-500 font-medium">{{ __('merchant_panel.out_of_stock') }}</span>
                                            @elseif (($pv['first_variant']['stock_status'] ?? '') === 'low')
                                                <span class="text-warning-500 font-medium">{{ $pv['first_variant']['stock_text'] }}</span>
                                            @else
                                                <span class="text-success-500 font-medium">{{ $pv['first_variant']['stock_text'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="text-ink font-semibold shrink-0 tabular-nums">{{ $pv['first_variant']['price_formatted'] }}</span>
                                    @if ($isProductSelected)
                                        <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-success-100 dark:bg-success-900/20 text-success-600 dark:text-success-400 shrink-0">
                                            <x-edz.icon name="check" class="w-4 h-4" />
                                        </span>
                                    @else
                                        <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 shrink-0">
                                            <svg x-show="!isAddingProduct" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                            <svg x-show="isAddingProduct" x-cloak class="edz-spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" /></svg>
                                        </span>
                                    @endif
                                </button>
                            @endif
                        </div>
                    @empty
                        <div class="px-4 py-10 text-center">
                            <x-edz.icon name="magnifying-glass" class="w-10 h-10 text-ink-muted/40 mx-auto mb-3" />
                            <p class="text-sm text-ink-muted">{{ __('merchant_panel.no_products_found') }}</p>
                        </div>
                    @endforelse

                    {{-- No search results (all items hidden by x-show) --}}
                    <div x-show="searchTerm && searchTerm.length >= 2 && visibleCount === 0"
                         class="px-4 py-10 text-center">
                        <x-edz.icon name="magnifying-glass" class="w-10 h-10 text-ink-muted/40 mx-auto mb-3" />
                        <p class="text-sm text-ink-muted">{{ __('merchant_panel.no_products_found') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </x-edz.modal>
    @endif

    {{-- Variant Picker Modal --}}
    @if ($showVariantPickerModal)
    <x-edz.modal :isOpen="true" :showCloseButton="false" :preventClose="true" size="sm">
        <div class="flex flex-col max-h-[85vh]">
            {{-- Drag handle (mobile) --}}
            <div class="edz-modal__handle sm:hidden"></div>

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 pt-5 pb-3">
                <div class="flex items-center gap-3 min-w-0">
                    <button type="button" @click="open = false; closeVariantPicker(); $wire.set('showProductPickerModal', true);"
                        class="edz-btn edz-btn--ghost edz-btn--sm shrink-0">
                        <x-edz.icon name="arrow-right" class="w-4 h-4 rtl:rotate-180" />
                        <span class="hidden sm:inline">{{ __('buttons.back') }}</span>
                    </button>
                    @if ($formSelectedProduct)
                        <div class="flex items-center gap-2 min-w-0">
                            <img src="{{ $formSelectedProduct['image_url'] ?? asset('img/icons/noimg.png') }}"
                                alt="" class="w-8 h-8 rounded-lg object-cover bg-surface shrink-0">
                            <span class="text-sm font-bold text-ink truncate">{{ $formSelectedProduct['name'] }}</span>
                        </div>
                    @endif
                </div>
                <button type="button" @click="open = false; closeVariantPicker()"
                    class="edz-modal__close" style="position:static;">
                    <x-edz.icon name="x-mark" class="w-5 h-5" />
                </button>
            </div>

            {{-- Variant list --}}
            <div class="flex-1 overflow-y-auto px-5 pb-5 max-h-[calc(100vh-475px)]  edz-scroll">
                @if ($formSelectedProduct && count($formSelectedProduct['variants']) > 0)
                    <div class="divide-y divide-surface-border dark:divide-ink-700">
                        @foreach ($formSelectedProduct['variants'] as $variant)
                            @php
                                $isVariantSelected = isset($formSelectedItems[$variant['id']]);
                                $variantQty = $formSelectedItems[$variant['id']] ?? 0;
                                $isDisabled = !$variant['is_active'] || $variant['stock'] <= 0;
                            @endphp
                            <div class="py-3 flex items-center gap-3 text-sm rounded-lg px-2 -mx-2 transition-colors
                                {{ $isVariantSelected ? 'bg-success-50/50 dark:bg-success-900/10' : '' }}
                                {{ $isDisabled && !$isVariantSelected ? 'opacity-40' : '' }}">
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-ink text-xs truncate">{{ $variant['name'] }}</div>
                                    <div class="text-[11px] text-ink-muted mt-0.5 flex items-center gap-1.5 flex-wrap">
                                        @if ($variant['option_labels'])
                                            <span>{{ $variant['option_labels'] }}</span>
                                            <span class="text-surface-border">·</span>
                                        @endif
                                        <span>SKU: {{ $variant['sku'] ?? '—' }}</span>
                                        @if ($isVariantSelected)
                                            <span class="text-success-600 dark:text-success-400 font-medium">· {{ $variantQty }} {{ __('merchant_panel.in_cart') }}</span>
                                        @elseif ($variant['stock'] <= 0)
                                            <span class="text-danger-500 font-medium">{{ __('merchant_panel.out_of_stock') }}</span>
                                        @elseif ($variant['stock'] <= 5)
                                            <span class="text-warning-500 font-medium">{{ $variant['stock'] }} {{ __('merchant_panel.left') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="text-ink font-semibold text-xs shrink-0 tabular-nums">{{ currency($variant['price']) }}</span>
                                @if ($isVariantSelected)
                                    <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-success-100 dark:bg-success-900/20
                                           text-success-600 dark:text-success-400 shrink-0">
                                        <x-edz.icon name="check" class="w-4 h-4" />
                                    </span>
                                @elseif ($variant['is_active'] && $variant['stock'] > 0)
                                    <button type="button" @click="selectVariant('{{ $variant['id'] }}')"
                                        :disabled="isAddingProduct"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-900/20
                                               text-primary-600 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-900/30
                                               transition-colors shrink-0 disabled:opacity-50">
                                        <svg x-show="!isAddingProduct" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                        <svg x-show="isAddingProduct" x-cloak class="edz-spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" /></svg>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-4 py-10 text-center">
                        <x-edz.icon name="cube" class="w-10 h-10 text-ink-muted/40 mx-auto mb-3" />
                        <p class="text-sm text-ink-muted">{{ __('merchant_panel.no_products_found') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </x-edz.modal>
    @endif
    </div>

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
                <input type="text" wire:model.live.debounce.300ms="filters.product"
                    placeholder="{{ __('merchant_panel.products') }}..." class="edz-input text-xs w-full">
            </div>
        @endif

        {{-- Amount --}}
        @if (in_array('amount', $this->visibleColumns))
            <div x-show="openFilter === 'amount'" x-cloak>
                <div class="flex gap-1">
                    <input type="number" wire:model.live.debounce.500ms="filters.amount_min" placeholder="Min"
                        class="edz-input text-xs flex-1">
                    <input type="number" wire:model.live.debounce.500ms="filters.amount_max" placeholder="Max"
                        class="edz-input text-xs flex-1">
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
                    <input type="text" wire:model.blur="filters.date_from"
                        class="edz-input text-xs w-full flatpickr-input" placeholder="From" autocomplete="off">
                    <input type="text" wire:model.blur="filters.date_to"
                        class="edz-input text-xs w-full flatpickr-input" placeholder="To" autocomplete="off">
                </div>
            </div>
        @endif
    </div>
</div>
