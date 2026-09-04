<?php
use App\Domains\Order\Models\UserColumnPreference;
use App\Domains\Order\Services\OrderAssignmentService;
use App\Domains\Order\Services\OrderService;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Customer;
use App\Models\Locations\City;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Status;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Facades\Storage;
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
        'product_id' => null,
        'product' => '',
        'source' => null,
        'address' => '',
        'notes' => '',
        'weight_min' => null,
        'weight_max' => null,
        'shipment_type' => null,
        'stopdesk_point' => null,
        'send_from_carrier_warehouse' => null,
        'confirmed_by' => null,
        'phone_secondary' => '',
    ],
    'orders' => [],
    'page' => 1,
    'visibleColumns' => [],
    'tableStyle' => 'default',
    'draftColumns' => [],
    'draftStyle' => 'default',
    'showTableSettings' => false,
    'filterProducts' => [],
    'perPage' => 15,
    'allStatuses' => [],
    'allMembers' => [],
    'allStates' => [],
    'allCities' => [],
    'allStopdeskPoints' => [],
    'allProviders' => [],

    // Bulk operations
    'selectedOrders' => [],
    'selectAll' => false,
    'showBulkBar' => false,

    // Trash view
    'showTrash' => false,

    // Order detail modal
    'detailsOrderId' => null,

    // Reassign modal
    'showReassignModal' => false,
    'reassignOrderId' => null,
    'reassignMembershipId' => '',

    // Order form modal (Phase 9 @include partial — state kept here on the parent instance)
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
    'filters.address' => function (): void {
        $this->page = 1;
        $this->loadOrders();
    },
    'filters.notes' => function (): void {
        $this->page = 1;
        $this->loadOrders();
    },
    'filters.phone_secondary' => function (): void {
        $this->page = 1;
        $this->loadOrders();
    },
    'filters.weight_min' => function (): void {
        $this->page = 1;
        $this->loadOrders();
    },
    'filters.weight_max' => function (): void {
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

$orderColumns = function (): array {
    return [
        // identity
        ['key' => 'number', 'label_key' => 'number', 'group' => 'identity', 'default' => true],
        ['key' => 'customer', 'label_key' => 'customer', 'group' => 'identity', 'default' => true],
        ['key' => 'phone', 'label_key' => 'phone', 'group' => 'identity', 'default' => true],
        ['key' => 'phone_secondary', 'label_key' => 'phone_secondary', 'group' => 'identity', 'default' => false],
        ['key' => 'notes', 'label_key' => 'notes', 'group' => 'identity', 'default' => false],
        ['key' => 'meta', 'label_key' => 'meta', 'group' => 'identity', 'default' => false],
        // geography
        ['key' => 'wilaya', 'label_key' => 'state', 'group' => 'geography', 'default' => true],
        ['key' => 'city', 'label_key' => 'city', 'group' => 'geography', 'default' => false],
        ['key' => 'address', 'label_key' => 'address', 'group' => 'geography', 'default' => false],
        ['key' => 'delivery_type', 'label_key' => 'delivery_type', 'group' => 'geography', 'default' => false],
        ['key' => 'shipping_provider', 'label_key' => 'shipping_provider', 'group' => 'geography', 'default' => false],
        ['key' => 'stopdesk_point', 'label_key' => 'stopdesk_point', 'group' => 'geography', 'default' => false],
        ['key' => 'send_from_carrier_warehouse', 'label_key' => 'send_from_carrier_warehouse', 'group' => 'geography', 'default' => false],
        // products_financial
        ['key' => 'products', 'label_key' => 'products', 'group' => 'products_financial', 'default' => true],
        ['key' => 'amount', 'label_key' => 'amount', 'group' => 'products_financial', 'default' => true],
        ['key' => 'weight', 'label_key' => 'weight', 'group' => 'products_financial', 'default' => true],
        ['key' => 'shipment_type', 'label_key' => 'shipment', 'group' => 'products_financial', 'default' => true],
        // workflow
        ['key' => 'status', 'label_key' => 'status', 'group' => 'workflow', 'default' => true],
        ['key' => 'assigned_agent', 'label_key' => 'assigned_agent', 'group' => 'workflow', 'default' => true],
        ['key' => 'confirmed_by', 'label_key' => 'confirmed_by', 'group' => 'workflow', 'default' => false],
        ['key' => 'created_at', 'label_key' => 'date', 'group' => 'workflow', 'default' => true],
        ['key' => 'confirmation_attempts', 'label_key' => 'attempts', 'group' => 'workflow', 'default' => true],
        ['key' => 'last_contact', 'label_key' => 'last_contact', 'group' => 'workflow', 'default' => true],
    ];
};

$loadColumnPreferences = function (): void {
    $registry = $this->orderColumns();
    $validKeys = collect($registry)->pluck('key')->all();
    $primaryKeys = collect($registry)->where('default', true)->pluck('key')->all();
    $defaults = $primaryKeys;

    $membership = $this->getCurrentMembership();
    if (!$membership) {
        $this->visibleColumns = $defaults;
        $this->tableStyle = 'default';
        return;
    }

    $pref = UserColumnPreference::where('membership_id', $membership->id)->where('view_key', 'orders_index')->first();

    // Primary columns are always forced; only secondary columns are configurable.
    $stored = $pref->visible_columns ?? [];
    if (!is_array($stored)) {
        $stored = [];
    }
    $stored = array_values(array_intersect($stored, $validKeys));
    $secondaries = array_values(array_diff($stored, $primaryKeys));
    $this->visibleColumns = array_unique(array_merge($primaryKeys, $secondaries));

    $this->tableStyle = ($pref->table_style === 'status') ? 'status' : 'default';
};

$saveColumnPreferences = function (): void {
    $membership = $this->getCurrentMembership();
    if (!$membership) {
        return;
    }

    $registry = $this->orderColumns();
    $validKeys = collect($registry)->pluck('key')->all();
    $primaryKeys = collect($registry)->where('default', true)->pluck('key')->all();

    $secondaries = array_values(array_diff(array_values(array_intersect($this->visibleColumns, $validKeys)), $primaryKeys));
    $this->visibleColumns = array_unique(array_merge($primaryKeys, $secondaries));

    UserColumnPreference::updateOrCreate(
        ['membership_id' => $membership->id, 'view_key' => 'orders_index'],
        ['visible_columns' => $secondaries, 'table_style' => $this->tableStyle]
    );
};

$getCurrentMembership = function (): ?\App\Models\Stores\Team\StoreMembership {
    return \App\Models\Stores\Team\StoreMembership::where('store_id', currentStoreId())
        ->where('user_id', auth()->id())
        ->first();
};

$loadOrders = function (): void {
    $storeId = currentStoreId();
    $f = $this->filters;

    $with = ['customer', 'status', 'items.product', 'items.variant', 'assignedMembership.user', 'createdByMembership.user', 'state', 'city', 'latestTracking.shippingProvider'];
    if (in_array('confirmed_by', $this->visibleColumns, true)) {
        $with[] = 'confirmedByHistory';
        $with[] = 'confirmedByHistory.status';
        $with[] = 'confirmedByHistory.changedBy.user';
    }
    if (in_array('stopdesk_point', $this->visibleColumns, true)) {
        $with[] = 'stopdeskPoint';
        $with[] = 'stopdeskPoint.city';
    }

    $query = Order::where('store_id', $storeId)->with($with);

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
    if (!empty($f['address'])) {
        $query->where('address', 'like', "%{$f['address']}%");
    }
    if (!empty($f['notes'])) {
        $query->where('notes', 'like', "%{$f['notes']}%");
    }
    if (!empty($f['phone_secondary'])) {
        $query->where('phone_secondary', 'like', "%{$f['phone_secondary']}%");
    }
    if (!empty($f['weight_min'])) {
        $query->where('weight_kg', '>=', $f['weight_min']);
    }
    if (!empty($f['weight_max'])) {
        $query->where('weight_kg', '<=', $f['weight_max']);
    }
    if (!empty($f['shipment_type'])) {
        $query->where('shipment_type', $f['shipment_type']);
    }
    if (!empty($f['stopdesk_point'])) {
        $query->where('stopdesk_point_id', $f['stopdesk_point']);
    }
    if ($f['send_from_carrier_warehouse'] !== null) {
        $query->where('send_from_carrier_warehouse', (bool) $f['send_from_carrier_warehouse']);
    }
    if (!empty($f['confirmed_by'])) {
        $query->whereHas('confirmedByHistory.changedBy', fn($q) => $q->where('id', $f['confirmed_by']));
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
    $floatFilters = ['amount_min', 'amount_max', 'weight_min', 'weight_max'];
    $arrFilters = ['status'];

    if (in_array($key, $intFilters, true)) {
        $value = (int) $value;
    } elseif (in_array($key, $floatFilters, true)) {
        $value = (float) $value;
    } elseif (in_array($key, $arrFilters, true)) {
        $value = is_array($value) ? array_map('intval', $value) : [];
    } elseif (in_array($key, ['source', 'delivery_type', 'shipment_type'], true)) {
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
        'address' => '',
        'notes' => '',
        'weight_min' => null,
        'weight_max' => null,
        'shipment_type' => null,
        'stopdesk_point' => null,
        'send_from_carrier_warehouse' => null,
        'confirmed_by' => null,
        'phone_secondary' => '',
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
    abort_unless(canStore(StorePermissionEnum::ORDER_ASSIGN->value), 403);
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

// --- Stopdesk points loader for filter (cascades from shipping_provider) ---
$loadFilterStopdeskPoints = function (?string $providerId): void {
    $this->allStopdeskPoints = $providerId
        ? \App\Domains\Shipping\Models\StopdeskPoint::where('shipping_provider_id', $providerId)
            ->orderBy('name')
            ->get()
            ->toArray()
        : [];
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

$openTableSettings = function (): void {
    $this->draftColumns = $this->visibleColumns;
    $this->draftStyle = $this->tableStyle;
    $this->showTableSettings = true;
};

$discardTableSettings = function (): void {
    $this->showTableSettings = false;
    $this->draftColumns = [];
    $this->draftStyle = 'default';
};

$saveTableSettings = function (): void {
    $registry = $this->orderColumns();
    $validKeys = collect($registry)->pluck('key')->all();
    $primaryKeys = collect($registry)->where('default', true)->pluck('key')->all();

    $draft = array_values(array_intersect($this->draftColumns, $validKeys));
    $secondaries = array_values(array_diff($draft, $primaryKeys));
    $this->visibleColumns = array_unique(array_merge($primaryKeys, $secondaries));
    $this->tableStyle = in_array($this->draftStyle, ['default', 'status'], true) ? $this->draftStyle : 'default';

    $this->saveColumnPreferences();

    $this->showTableSettings = false;
    $this->draftColumns = [];
    $this->draftStyle = 'default';

    $this->loadColumnPreferences();
    $this->loadOrders();

    $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('merchant_panel.settings_saved')]);
};

$toggleDraftColumn = function (string $column): void {
    if (in_array($column, $this->draftColumns, true)) {
        $this->draftColumns = array_values(array_diff($this->draftColumns, [$column]));
    } else {
        $this->draftColumns[] = $column;
    }
};

$resetColumns = function (): void {
    $this->draftColumns = collect($this->orderColumns())->where('default', true)->pluck('key')->all();
    $this->draftStyle = 'default';
};

// ——— Status Transition ———

// Authorization (Phase P1): gate each transition by the fine-grained permission
// for the target status (see App\Support\StoreOrderPermissions::forStatus).
// Confirmation → order.confirm, cancellation → order.cancel, everything else
// (ship / deliver / prepare / return-followup…) → order.manage. This closes the
// gap where $transitionOrder was only checked against the state machine.
$transitionOrder = function (string $orderId, string $statusKey): void {
    $order = Order::where('store_id', currentStoreId())->findOrFail($orderId);
    $membership = $this->getCurrentMembership();

    abort_unless(canStore(\App\Support\StoreOrderPermissions::forStatus($statusKey)), 403);

    $service = app(OrderService::class);
    $statusKey_translation = status_label('order', $statusKey) ?: __('status.' . $statusKey);
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

// ——— Detail Modal ———
$openOrderDetails = function (string $orderId): void {
    $this->detailsOrderId = $orderId;
};

$closeOrderDetails = function (): void {
    $this->detailsOrderId = null;
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

// ——— Order form modal (Phase 9 @include partial — logic lives here on the parent instance) ———

$syncFormSelectedItems = function (): void {
    $this->formSelectedItems = collect($this->form['items'])->pluck('quantity', 'product_variant_id')->toArray();
    $this->dispatch('selected-items-updated', items: $this->formSelectedItems);
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
                'price_range' => $minPrice != $maxPrice ? currency($minPrice) . ' — ' . currency($maxPrice) : currency($minPrice),
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
                        'stock_text' => $firstVariant->stock <= 0 ? __('merchant_panel.out_of_stock') : $firstVariant->stock . ' ' . __('merchant_panel.left'),
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

    app(OrderService::class)->updateOrder($order, [
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
    $this->page = 1;
    $this->loadOrders();

    $this->dispatch('swal', type: 'success', title: __('Order updated'));
};
?>

<div>
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
                <button @click="$wire.openCreateModal()" class="edz-btn edz-btn--primary edz-btn--sm">
                    <x-edz.icon name="plus" class="w-4 h-4" />
                    <span>{{ __('merchant_panel.new_order') }}</span>
                </button>
            @endif
            <button wire:click="refreshOrders" class="edz-btn edz-btn--ghost edz-btn--sm" wire:loading.attr="disabled"
                wire:loading.class="opacity-50 pointer-events-none" wire:target="refreshOrders">
                <x-edz.icon name="arrow-path" wire:loading.remove wire:target="refreshOrders" class="w-4 h-4" />
                <x-edz.spinner wire:target="refreshOrders" class="w-4 h-4" />
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
                    <x-edz.spinner wire:target="loadOrders" class="w-4 h-4" />
                    <x-edz.icon name="arrow-right" wire:loading.remove wire:target="loadOrders" class="w-4 h-4" />
                </button>
            </div>

            {{-- Table Settings --}}
            <button wire:click="openTableSettings" class="edz-btn edz-btn--ghost edz-btn--sm">
                <x-edz.icon name="view-columns" class="w-4 h-4" />
                {{ __('merchant_panel.columns') }}
            </button>

            {{-- Source --}}
            <div x-data="{ open: false }" @click.away="open = false" class="relative">
                <button @click="open = !open"
                    class="edz-btn edz-btn--ghost edz-btn--sm {{ $this->filters['source'] ? 'text-accent-600' : '' }}"
                    wire:loading.attr="disabled" wire:target="setFilter">
                    <x-edz.spinner wire:target="setFilter" class="w-4 h-4" />
                    <x-edz.icon name="user" wire:loading.remove wire:target="setFilter" class="w-4 h-4" />
                    <span wire:loading.remove
                        wire:target="setFilter">{{ $this->filters['source'] === 'manual' ? __('merchant.delivery_man') : ($this->filters['source'] === 'store' ? __('merchant_panel.store') : __('merchant_panel.source')) }}</span>
                    <x-edz.icon name="chevron-down" wire:loading.remove wire:target="setFilter" class="w-3 h-3" />
                </button>
                <div x-show="open" x-transition
                    class="absolute z-40 mt-1 w-40 bg-surface border border-surface-border rounded-xl shadow-lg p-1.5">
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
                    <x-edz.spinner wire:target="setFilter" class="w-4 h-4" />
                    <x-edz.icon name="home" wire:loading.remove wire:target="setFilter" class="w-4 h-4" />
                    <span wire:loading.remove
                        wire:target="setFilter">{{ $this->filters['delivery_type'] === 'stopdesk' ? __('storefront.stop_desk') : ($this->filters['delivery_type'] === 'home' ? __('storefront.home_delivery') : __('storefront.delivery_type')) }}</span>
                    <x-edz.icon name="chevron-down" wire:loading.remove wire:target="setFilter" class="w-3 h-3" />
                </button>
                <div x-show="open" x-transition
                    class="absolute z-40 mt-1 w-44 bg-surface border border-surface-border rounded-xl shadow-lg p-1.5">
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
                    <x-edz.spinner wire:target="setFilter" class="w-4 h-4" />
                    <x-edz.icon name="truck" wire:loading.remove wire:target="setFilter" class="w-4 h-4" />
                    <span wire:loading.remove
                        wire:target="setFilter">{{ collect($this->allProviders)->firstWhere('id', $this->filters['shipping_provider'])['name'] ?? __('merchant.assign_delivery_man') }}</span>
                    <x-edz.icon name="chevron-down" wire:loading.remove wire:target="setFilter" class="w-3 h-3" />
                </button>
                <div x-show="open" x-transition
                    class="absolute z-40 mt-1 w-48 bg-surface border border-surface-border rounded-xl shadow-lg p-1.5 max-h-60 overflow-y-auto edz-scroll">
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
                <x-edz.spinner wire:target="toggleTrash" class="w-4 h-4" />
                <x-edz.icon name="trash" wire:loading.remove wire:target="toggleTrash" class="w-4 h-4" />
                <span wire:loading.remove
                    wire:target="toggleTrash">{{ $this->showTrash ? __('buttons.close') . ' ' . __('merchant.trash_bin') : __('merchant.trash_bin') }}</span>
            </button>

            <div class="flex items-center gap-1 text-xs text-ink-muted" x-data="{ pp: {{ $this->perPage }} }">
                <span>{{ __('merchant.per_page') }}</span>
                <select x-model="pp" x-on:change="$wire.setPerPage(parseInt($event.target.value))"
                    class="text-xs border border-surface-border rounded-lg px-2 py-1 bg-surface text-ink focus:outline-none focus:ring-1 focus:ring-[var(--store-primary)]">
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
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    {{ collect($this->allStates)->firstWhere('id', $this->filters['wilaya'])['name'] ?? '' }}
                    <button wire:click="setFilter('wilaya', null)" wire:loading.attr="disabled"
                        class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
                </span>
            @endif
            @if (!empty($this->filters['city']))
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    {{ collect($this->allCities)->firstWhere('id', $this->filters['city'])['name'] ?? '' }}
                    <button wire:click="setFilter('city', null)" wire:loading.attr="disabled"
                        class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
                </span>
            @endif
            @if (!empty($this->filters['status']))
                @foreach ($this->allStatuses as $s)
                    @if (in_array($s['id'], $this->filters['status']))
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                            {{ \Edzeery\MyStatusKit\Facades\Status::for('order', $s['key'] ?? 'default')->label() }}
                            <button wire:click="toggleStatusFilter('{{ $s['id'] }}')"
                                wire:loading.attr="disabled" class="hover:text-accent-900"><x-edz.icon name="x-mark"
                                    class="w-3 h-3" /></button>
                        </span>
                    @endif
                @endforeach
            @endif
            @if (!empty($this->filters['assigned_to']))
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    {{ collect($this->allMembers)->firstWhere('id', $this->filters['assigned_to'])['user']['name'] ?? '' }}
                    <button wire:click="setFilter('assigned_to', null)" wire:loading.attr="disabled"
                        class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
                </span>
            @endif
            @if (!empty($this->filters['date_from']) || !empty($this->filters['date_to']))
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    {{ $this->filters['date_from'] ?? '...' }} — {{ $this->filters['date_to'] ?? '...' }}
                    <button @click="$wire.setFilter('date_from', null); $wire.setFilter('date_to', null)"
                        wire:loading.attr="disabled" class="hover:text-accent-900"><x-edz.icon name="x-mark"
                            class="w-3 h-3" /></button>
                </span>
            @endif
            @if (!empty($this->filters['delivery_type']))
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    {{ $this->filters['delivery_type'] === 'stopdesk' ? __('merchant_panel.stop_desk_label') : __('merchant_panel.home_delivery_label') }}
                    <button wire:click="setFilter('delivery_type', null)" wire:loading.attr="disabled"
                        class="hover:text-accent-900"><x-edz.icon name="x-mark"
                            class="w-3 h-3" /></button>
                </span>
            @endif
            @if (!empty($this->filters['shipping_provider']))
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    {{ collect($this->allProviders)->firstWhere('id', $this->filters['shipping_provider'])['name'] ?? '' }}
                    <button @click="$wire.setFilter('shipping_provider', null); $wire.setFilter('stopdesk_point', null)"
                        wire:loading.attr="disabled" class="hover:text-accent-900"><x-edz.icon name="x-mark"
                            class="w-3 h-3" /></button>
                </span>
            @endif
            @if (!empty($this->filters['shipment_type']))
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    {{ match ($this->filters['shipment_type']) {
                        'delivery' => __('merchant_panel.delivery'),
                        'exchange' => __('merchant_panel.exchange_label'),
                        'pickup' => __('merchant_panel.pickup_label'),
                        default => $this->filters['shipment_type'],
                    } }}
                    <button wire:click="setFilter('shipment_type', null)" wire:loading.attr="disabled"
                        class="hover:text-accent-900"><x-edz.icon name="x-mark"
                            class="w-3 h-3" /></button>
                </span>
            @endif
            @if (!empty($this->filters['stopdesk_point']))
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    {{ collect($this->allStopdeskPoints)->firstWhere('id', $this->filters['stopdesk_point'])['name'] ?? '' }}
                    <button wire:click="setFilter('stopdesk_point', null)" wire:loading.attr="disabled"
                        class="hover:text-accent-900"><x-edz.icon name="x-mark"
                            class="w-3 h-3" /></button>
                </span>
            @endif
            @if (!empty($this->filters['confirmed_by']))
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    {{ collect($this->allMembers)->firstWhere('id', $this->filters['confirmed_by'])['user']['name'] ?? '' }}
                    <button wire:click="setFilter('confirmed_by', null)" wire:loading.attr="disabled"
                        class="hover:text-accent-900"><x-edz.icon name="x-mark"
                            class="w-3 h-3" /></button>
                </span>
            @endif
            @if ($this->filters['send_from_carrier_warehouse'] !== null)
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    {{ $this->filters['send_from_carrier_warehouse'] ? __('buttons.yes') : __('buttons.no') }}
                    <button wire:click="setFilter('send_from_carrier_warehouse', null)" wire:loading.attr="disabled"
                        class="hover:text-accent-900"><x-edz.icon name="x-mark"
                            class="w-3 h-3" /></button>
                </span>
            @endif
            @if (filled($this->filters['weight_min']) || filled($this->filters['weight_max']))
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    {{ $this->filters['weight_min'] ?? '0' }} — {{ $this->filters['weight_max'] ?? '∞' }}
                    @if (filled($this->filters['weight_min']))
                        <button wire:click="$set('filters.weight_min', '')" wire:loading.attr="disabled"
                            class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
                    @endif
                    @if (filled($this->filters['weight_max']))
                        <button wire:click="$set('filters.weight_max', '')" wire:loading.attr="disabled"
                            class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
                    @endif
                </span>
            @endif
            @if ($this->filters['address'] !== '')
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    {{ $this->filters['address'] }}
                    <button wire:click="setFilter('address', '')" wire:loading.attr="disabled"
                        class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
                </span>
            @endif
            @if ($this->filters['notes'] !== '')
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    {{ $this->filters['notes'] }}
                    <button wire:click="setFilter('notes', '')" wire:loading.attr="disabled"
                        class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
                </span>
            @endif
            @if ($this->filters['phone_secondary'] !== '')
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    {{ $this->filters['phone_secondary'] }}
                    <button wire:click="setFilter('phone_secondary', '')" wire:loading.attr="disabled"
                        class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
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
            class="mb-4 p-3 bg-warning-surface border border-warning-border rounded-xl flex items-center justify-between">
            <span class="text-sm text-warning-fg font-medium">
                {{ __('merchant.trash_bin') }} — {{ $orders['total'] ?? 0 }}
            </span>
            <div class="flex gap-2">
                <button wire:click="restoreAll" wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 pointer-events-none" class="edz-btn edz-btn--ghost edz-btn--sm">
                    <x-edz.spinner wire:target="restoreAll" class="w-3.5 h-3.5" />
                    <span wire:loading.remove wire:target="restoreAll">{{ __('merchant.restore_all') }}</span>
                </button>
                <button x-data="{ isLoading: false }"
                    x-on:click.prevent="(async () => { if (!isLoading && await EdzSwal.confirmDelete()) { isLoading = true; await $wire.forceDeleteAll(); isLoading = false; } })()"
                    :disabled="isLoading"
                    class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 disabled:opacity-50">
                    <x-edz.spinner show="isLoading" class="w-3.5 h-3.5" />
                    <span x-show="!isLoading">{{ __('merchant.empty_trash') }}</span>
                </button>
            </div>
        </div>
    @elseif (count($this->selectedOrders) > 0)
        @include('livewire.merchant.orders.partials.bulk-actions-bar')
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
                    <div class="hidden lg:block overflow-x-auto max-h-[calc(100vh-475px)] overflow-y-auto edz-scroll">
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
                                    @if (in_array('phone_secondary', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase relative group">
                                            <div class="flex items-center gap-1">
                                                {{ __('merchant_panel.phone_secondary') }}
                                                <button data-filter-btn
                                                    @click.stop="$dispatch('edz-filter-open', { key: 'phone_secondary', el: $event.currentTarget })"
                                                    class="shrink-0 {{ $this->filters['phone_secondary'] !== '' ? 'text-accent-500' : 'text-ink-muted/40 group-hover:text-ink-muted' }} transition">
                                                    <x-edz.icon name="filter" class="w-3 h-3" />
                                                </button>
                                                @if ($this->filters['phone_secondary'] !== '')
                                                    <span class="w-1.5 h-1.5 rounded-full bg-accent-500 shrink-0"></span>
                                                @endif
                                            </div>
                                        </th>
                                    @endif
                                    @if (in_array('notes', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase relative group">
                                            <div class="flex items-center gap-1">
                                                {{ __('merchant_panel.notes') }}
                                                <button data-filter-btn
                                                    @click.stop="$dispatch('edz-filter-open', { key: 'notes', el: $event.currentTarget })"
                                                    class="shrink-0 {{ $this->filters['notes'] !== '' ? 'text-accent-500' : 'text-ink-muted/40 group-hover:text-ink-muted' }} transition">
                                                    <x-edz.icon name="filter" class="w-3 h-3" />
                                                </button>
                                                @if ($this->filters['notes'] !== '')
                                                    <span class="w-1.5 h-1.5 rounded-full bg-accent-500 shrink-0"></span>
                                                @endif
                                            </div>
                                        </th>
                                    @endif
                                    @if (in_array('meta', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            {{ __('merchant_panel.meta') }}</th>
                                    @endif
                                    @if (in_array('wilaya', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase relative group">
                                            <div class="flex items-center gap-1">
                                                {{ __('merchant_panel.state') }}
                                                <button data-filter-btn
                                                    @click.stop="$dispatch('edz-filter-open', { key: 'wilaya', el: $event.currentTarget })"
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
                                    @if (in_array('city', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase relative group">
                                            <div class="flex items-center gap-1">
                                                {{ __('merchant_panel.city') }}
                                                <button data-filter-btn
                                                    @click.stop="$dispatch('edz-filter-open', { key: 'city', el: $event.currentTarget })"
                                                    class="shrink-0 {{ filled($this->filters['wilaya']) ? '' : 'opacity-40 pointer-events-none' }} {{ filled($this->filters['city']) ? 'text-accent-500' : 'text-ink-muted/40 group-hover:text-ink-muted' }} transition">
                                                    <x-edz.icon name="filter" class="w-3 h-3" />
                                                </button>
                                                @if (filled($this->filters['city']))
                                                    <span class="w-1.5 h-1.5 rounded-full bg-accent-500 shrink-0"></span>
                                                @endif
                                            </div>
                                        </th>
                                    @endif
                                    @if (in_array('address', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase relative group">
                                            <div class="flex items-center gap-1">
                                                {{ __('merchant_panel.address') }}
                                                <button data-filter-btn
                                                    @click.stop="$dispatch('edz-filter-open', { key: 'address', el: $event.currentTarget })"
                                                    class="shrink-0 {{ $this->filters['address'] !== '' ? 'text-accent-500' : 'text-ink-muted/40 group-hover:text-ink-muted' }} transition">
                                                    <x-edz.icon name="filter" class="w-3 h-3" />
                                                </button>
                                                @if ($this->filters['address'] !== '')
                                                    <span class="w-1.5 h-1.5 rounded-full bg-accent-500 shrink-0"></span>
                                                @endif
                                            </div>
                                        </th>
                                    @endif
                                    @if (in_array('delivery_type', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase relative group">
                                            <div class="flex items-center gap-1">
                                                {{ __('merchant_panel.delivery_type') }}
                                                <button data-filter-btn
                                                    @click.stop="$dispatch('edz-filter-open', { key: 'delivery_type', el: $event.currentTarget })"
                                                    class="shrink-0 {{ filled($this->filters['delivery_type']) ? 'text-accent-500' : 'text-ink-muted/40 group-hover:text-ink-muted' }} transition">
                                                    <x-edz.icon name="filter" class="w-3 h-3" />
                                                </button>
                                                @if (filled($this->filters['delivery_type']))
                                                    <span class="w-1.5 h-1.5 rounded-full bg-accent-500 shrink-0"></span>
                                                @endif
                                            </div>
                                        </th>
                                    @endif
                                    @if (in_array('shipping_provider', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase relative group">
                                            <div class="flex items-center gap-1">
                                                {{ __('merchant_panel.shipping_provider') }}
                                                <button data-filter-btn
                                                    @click.stop="$dispatch('edz-filter-open', { key: 'shipping_provider', el: $event.currentTarget })"
                                                    class="shrink-0 {{ filled($this->filters['shipping_provider']) ? 'text-accent-500' : 'text-ink-muted/40 group-hover:text-ink-muted' }} transition">
                                                    <x-edz.icon name="filter" class="w-3 h-3" />
                                                </button>
                                                @if (filled($this->filters['shipping_provider']))
                                                    <span class="w-1.5 h-1.5 rounded-full bg-accent-500 shrink-0"></span>
                                                @endif
                                            </div>
                                        </th>
                                    @endif
                                    @if (in_array('stopdesk_point', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase relative group">
                                            <div class="flex items-center gap-1">
                                                {{ __('merchant_panel.stopdesk_point') }}
                                                <button data-filter-btn
                                                    @click.stop="$dispatch('edz-filter-open', { key: 'stopdesk_point', el: $event.currentTarget })"
                                                    class="shrink-0 {{ filled($this->filters['shipping_provider']) ? '' : 'opacity-40 pointer-events-none' }} {{ filled($this->filters['stopdesk_point']) ? 'text-accent-500' : 'text-ink-muted/40 group-hover:text-ink-muted' }} transition">
                                                    <x-edz.icon name="filter" class="w-3 h-3" />
                                                </button>
                                                @if (filled($this->filters['stopdesk_point']))
                                                    <span class="w-1.5 h-1.5 rounded-full bg-accent-500 shrink-0"></span>
                                                @endif
                                            </div>
                                        </th>
                                    @endif
                                    @if (in_array('send_from_carrier_warehouse', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase relative group">
                                            <div class="flex items-center gap-1">
                                                {{ __('merchant_panel.send_from_carrier_warehouse') }}
                                                <button data-filter-btn
                                                    @click.stop="$dispatch('edz-filter-open', { key: 'send_from_carrier_warehouse', el: $event.currentTarget })"
                                                    class="shrink-0 {{ $this->filters['send_from_carrier_warehouse'] !== null ? 'text-accent-500' : 'text-ink-muted/40 group-hover:text-ink-muted' }} transition">
                                                    <x-edz.icon name="filter" class="w-3 h-3" />
                                                </button>
                                                @if ($this->filters['send_from_carrier_warehouse'] !== null)
                                                    <span class="w-1.5 h-1.5 rounded-full bg-accent-500 shrink-0"></span>
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
                                                    @click.stop="$dispatch('edz-filter-open', { key: 'product', el: $event.currentTarget })"
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
                                                    @click.stop="$dispatch('edz-filter-open', { key: 'amount', el: $event.currentTarget })"
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
                                                    @click.stop="$dispatch('edz-filter-open', { key: 'status', el: $event.currentTarget })"
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
                                                {{ __('merchant_panel.assigned_agent') }}
                                                <button data-filter-btn
                                                    @click.stop="$dispatch('edz-filter-open', { key: 'assigned_to', el: $event.currentTarget })"
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
                                    @if (in_array('confirmed_by', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase relative group">
                                            <div class="flex items-center gap-1">
                                                {{ __('merchant_panel.confirmed_by') }}
                                                <button data-filter-btn
                                                    @click.stop="$dispatch('edz-filter-open', { key: 'confirmed_by', el: $event.currentTarget })"
                                                    class="shrink-0 {{ filled($this->filters['confirmed_by']) ? 'text-accent-500' : 'text-ink-muted/40 group-hover:text-ink-muted' }} transition">
                                                    <x-edz.icon name="filter" class="w-3 h-3" />
                                                </button>
                                                @if (filled($this->filters['confirmed_by']))
                                                    <span class="w-1.5 h-1.5 rounded-full bg-accent-500 shrink-0"></span>
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
                                                    @click.stop="$dispatch('edz-filter-open', { key: 'date', el: $event.currentTarget })"
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
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase relative group">
                                            <div class="flex items-center gap-1">
                                                {{ __('merchant_panel.weight') }}
                                                <button data-filter-btn
                                                    @click.stop="$dispatch('edz-filter-open', { key: 'weight', el: $event.currentTarget })"
                                                    class="shrink-0 {{ filled($this->filters['weight_min']) || filled($this->filters['weight_max']) ? 'text-accent-500' : 'text-ink-muted/40 group-hover:text-ink-muted' }} transition">
                                                    <x-edz.icon name="filter" class="w-3 h-3" />
                                                </button>
                                                @if (filled($this->filters['weight_min']) || filled($this->filters['weight_max']))
                                                    <span class="w-1.5 h-1.5 rounded-full bg-accent-500 shrink-0"></span>
                                                @endif
                                            </div>
                                        </th>
                                    @endif
                                    @if (in_array('shipment_type', $this->visibleColumns))
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase relative group">
                                            <div class="flex items-center gap-1">
                                                {{ __('merchant_panel.shipment') }}
                                                <button data-filter-btn
                                                    @click.stop="$dispatch('edz-filter-open', { key: 'shipment_type', el: $event.currentTarget })"
                                                    class="shrink-0 {{ filled($this->filters['shipment_type']) ? 'text-accent-500' : 'text-ink-muted/40 group-hover:text-ink-muted' }} transition">
                                                    <x-edz.icon name="filter" class="w-3 h-3" />
                                                </button>
                                                @if (filled($this->filters['shipment_type']))
                                                    <span class="w-1.5 h-1.5 rounded-full bg-accent-500 shrink-0"></span>
                                                @endif
                                            </div>
                                        </th>
                                    @endif
                                    <th class="px-4 py-3 text-end text-xs font-semibold text-ink-muted uppercase">
                                        {{ __('merchant_panel.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-border">
                                @foreach ($orders['data'] as $order)
                                    @php
                                        $transitions = $order['transitions'] ?? [];
                                        $orderId = $order['id'] ?? '';
                                        $orderSelected = in_array($orderId, $this->selectedOrders);
                                        $orderStatusTone = $this->tableStyle === 'status'
                                            ? 'edz-table-row--' . ($order['status']['color'] ?? 'gray') . ($orderSelected ? ' edz-row-selected' : '')
                                            : '';
                                    @endphp
                                    <tr data-order-id="{{ $orderId }}" data-order-number="{{ $order['number'] ?? '' }}"
                                        x-data="orderRowActions($el)"
                                        class="{{ $this->tableStyle === 'status' ? '' : 'hover:bg-surface-tertiary/50 ' }}{{ $this->tableStyle !== 'status' && $orderSelected ? 'bg-accent-surface-subtle ' : '' }}{{ $orderStatusTone }}">
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
                                        @if (in_array('phone_secondary', $this->visibleColumns))
                                            <td class="px-4 py-3 text-ink-muted text-xs" dir="ltr">
                                                {{ $order['phone_secondary'] ?? '-' }}
                                            </td>
                                        @endif
                                        @if (in_array('notes', $this->visibleColumns))
                                            <td class="px-4 py-3 text-xs text-ink-muted max-w-[200px] truncate"
                                                title="{{ $order['notes'] ?? '' }}">
                                                {{ $order['notes'] ? \Illuminate\Support\Str::limit($order['notes'], 30) : '-' }}
                                            </td>
                                        @endif
                                        @if (in_array('meta', $this->visibleColumns))
                                            @php
                                                $metaEntries = collect($order['meta'] ?? [])
                                                    ->map(fn($v, $k) => "{$k}: {$v}")
                                                    ->implode(', ');
                                            @endphp
                                            <td class="px-4 py-3 text-xs text-ink-muted max-w-[200px] truncate"
                                                title="{{ $metaEntries }}">
                                                {{ $metaEntries ?: '-' }}
                                            </td>
                                        @endif
                                        @if (in_array('wilaya', $this->visibleColumns))
                                            <td class="px-4 py-3 text-ink-muted text-xs">
                                                {{ $order['state']['name'] ?? '-' }}
                                            </td>
                                        @endif
                                        @if (in_array('city', $this->visibleColumns))
                                            <td class="px-4 py-3 text-ink-muted text-xs">
                                                {{ $order['city']['name'] ?? '-' }}
                                            </td>
                                        @endif
                                        @if (in_array('address', $this->visibleColumns))
                                            <td class="px-4 py-3 text-xs text-ink-muted max-w-[200px] truncate"
                                                title="{{ $order['address'] ?? '' }}">
                                                {{ $order['address'] ? \Illuminate\Support\Str::limit($order['address'], 40) : '-' }}
                                            </td>
                                        @endif
                                        @if (in_array('delivery_type', $this->visibleColumns))
                                            <td class="px-4 py-3 text-ink-muted text-xs">
                                                {{ $order['delivery_type'] === 'stopdesk' ? __('merchant_panel.stop_desk_label') : ($order['delivery_type'] === 'home' ? __('merchant_panel.home_delivery_label') : ($order['delivery_type'] ?? '-')) }}
                                            </td>
                                        @endif
                                        @if (in_array('shipping_provider', $this->visibleColumns))
                                            <td class="px-4 py-3 text-ink-muted text-xs">
                                                {{ $order['tracking']['shipping_provider'] ?? '-' }}
                                            </td>
                                        @endif
                                        @if (in_array('stopdesk_point', $this->visibleColumns))
                                            <td class="px-4 py-3 text-xs text-ink-muted">
                                                {{ $order['stopdesk_point']['name'] ?? '-' }}@if (!empty($order['stopdesk_point']['city']['name']))
                                                    ({{ $order['stopdesk_point']['city']['name'] }})
                                                @endif
                                            </td>
                                        @endif
                                        @if (in_array('send_from_carrier_warehouse', $this->visibleColumns))
                                            <td class="px-4 py-3">
                                                @if ($order['send_from_carrier_warehouse'] ?? false)
                                                    <x-edz.badge tone="success" sm>
                                                        <x-edz.icon name="check" class="w-3 h-3" />
                                                    </x-edz.badge>
                                                @else
                                                    <x-edz.badge tone="neutral" sm>
                                                        <x-edz.icon name="x-mark" class="w-3 h-3" />
                                                    </x-edz.badge>
                                                @endif
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
                                                <div class="relative"
                                                    @click.away="open = false">
                                                    <button
                                                        @click="openStatusMenu()"
                                                        x-ref="trigger"
                                                        class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-full cursor-pointer hover:opacity-80 {{ \Edzeery\MyStatusKit\Facades\Status::for('general', $order['status']['color'] ?? 'gray')->color() }}">
                                                        {!! \Edzeery\MyStatusKit\Facades\Status::for('order', $order['status']['key'] ?? 'default')->icon(null, 'w-3 h-3 shrink-0') !!}
                                                        {{ \Edzeery\MyStatusKit\Facades\Status::for('order', $order['status']['key'] ?? 'default')->label() }}
                                                        <x-edz.icon name="chevron-down" class="w-3 h-3" />
                                                    </button>
                                                    <div x-show="open" x-transition x-cloak
                                                        class="fixed z-[200] w-56 bg-surface border border-surface-border rounded-xl shadow-lg p-1.5 max-h-64 overflow-y-auto edz-scroll"
                                                        :style="'top:' + top + 'px; left:' + left + 'px'">
                                                        @foreach ($this->allStatuses as $s)
                                                            @if (in_array($s['key'], $transitions) || $s['id'] == $order['status_id'])
                                                                <button
                                                                    wire:click="transitionOrder('{{ $orderId }}', '{{ $s['key'] }}')"
                                                                    wire:loading.attr="disabled" @click="open = false"
                                                                    class="w-full text-left flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-tertiary disabled:opacity-50 {{ $s['id'] == $order['status_id'] ? 'font-bold' : '' }}">
                                                                    <x-edz.spinner
                                                                        wire:target="transitionOrder('{{ $orderId }}', '{{ $s['key'] }}')"
                                                                        class="w-3 h-3" />
                                                                    {!! \Edzeery\MyStatusKit\Facades\Status::for('order', $s['key'] ?? 'default')->icon(null, 'w-3 h-3 shrink-0') !!}
                                                                    <span class="w-2 h-2 rounded-full shrink-0"
                                                                        style="background: {{ \Edzeery\MyStatusKit\Facades\Status::for('general', $s['color'] ?? 'gray')->hex() }}"></span>
                                                                    {{ \Edzeery\MyStatusKit\Facades\Status::for('order', $s['key'] ?? 'default')->label() }}
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
                                        @if (in_array('confirmed_by', $this->visibleColumns))
                                            <td class="px-4 py-3 text-xs text-ink-muted">
                                                {{ $order['confirmed_by_history']['changed_by']['user']['name'] ?? '-' }}
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
                                                <button wire:click="openOrderDetails('{{ $orderId }}')"
                                                    class="edz-btn edz-btn--ghost edz-btn--xs shrink-0"
                                                    title="{{ __('merchant.order_details') }}">
                                                    <x-edz.icon name="info-circle"
                                                        class="w-4 h-4 shrink-0" />
                                                </button>
                                                @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
                                                    <button
                                                        @click="$wire.openEditModal('{{ $orderId }}')"
                                                        class="edz-btn edz-btn--ghost edz-btn--xs shrink-0"
                                                        title="{{ __('merchant_panel.edit') }}">
                                                        <x-edz.icon name="edit" class="w-4 h-4 shrink-0" />
                                                    </button>
                                                    <button wire:click="openReassignModal('{{ $orderId }}')"
                                                        wire:loading.attr="disabled" wire:loading.class="opacity-50"
                                                        wire:target="openReassignModal('{{ $orderId }}')"
                                                        class="edz-btn edz-btn--ghost edz-btn--xs shrink-0"
                                                        title="{{ __('merchant_panel.reassign') }}">
                                                        <x-edz.spinner
                                                            wire:target="openReassignModal('{{ $orderId }}')"
                                                            class="w-3.5 h-3.5" />
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
                                                            <x-edz.spinner
                                                                wire:target="restoreOrder('{{ $orderId }}')"
                                                                class="w-3.5 h-3.5" />
                                                            <x-edz.icon name="arrow-uturn-left" wire:loading.remove
                                                                wire:target="restoreOrder('{{ $orderId }}')"
                                                                class="w-4 h-4 shrink-0" />
                                                        </button>
                                                    @else
                                                        <button
                                                            class="edz-btn edz-btn--ghost edz-btn--xs text-danger-600 hover:text-danger-700 shrink-0"
                                                            x-on:click.prevent="confirmDelete()"
                                                            :disabled="deleteLoading"
                                                            :class="deleteLoading ? 'opacity-50' : ''"
                                                            title="{{ __('merchant.delete_permanently') }}">
                                                            <x-edz.spinner show="deleteLoading" class="w-3.5 h-3.5" />
                                                            <x-edz.icon name="trash" x-show="!deleteLoading"
                                                                class="w-4 h-4 shrink-0" />
                                                        </button>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile cards (Apple Adaptive Layout): below lg only --}}
                    <div class="lg:hidden divide-y divide-surface-border">
                        @foreach ($orders['data'] as $order)
                            @php
                                $orderId = $order['id'] ?? '';
                                $orderSelected = in_array($orderId, $this->selectedOrders);
                                $orderStatusTone = $this->tableStyle === 'status'
                                    ? 'edz-table-row--' . ($order['status']['color'] ?? 'gray') . ($orderSelected ? ' edz-row-selected' : '')
                                    : '';
                            @endphp
                            <div data-order-id="{{ $orderId }}" data-order-number="{{ $order['number'] ?? '' }}"
                                x-data="orderRowActions($el)"
                                class="px-4 py-4 {{ $this->tableStyle !== 'status' && $orderSelected ? 'bg-accent-surface-subtle' : '' }} {{ $orderStatusTone }}">
                                <div class="flex items-start gap-3">
                                    <input type="checkbox" value="{{ $orderId }}"
                                        wire:click="toggleSelectOrder('{{ $orderId }}')"
                                        {{ in_array($orderId, $this->selectedOrders) ? 'checked' : '' }}
                                        class="mt-1 rounded border-gray-300 text-accent-600 focus:ring-accent-500 shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="font-mono font-semibold text-ink">#{{ $order['number'] }}</span>
                                            <span class="text-xs text-ink-muted shrink-0">{{ \Carbon\Carbon::parse($order['created_at'])->format('M d, Y') }}</span>
                                        </div>
                                        <div class="mt-1 text-sm font-medium text-ink truncate">{{ $order['customer']['name'] ?? '-' }}</div>
                                        <div class="text-xs text-ink-muted" dir="ltr">{{ $order['customer']['phone'] ?? '-' }}</div>
                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full {{ \Edzeery\MyStatusKit\Facades\Status::for('general', $order['status']['color'] ?? 'gray')->color() }}">
                                                {!! \Edzeery\MyStatusKit\Facades\Status::for('order', $order['status']['key'] ?? 'default')->icon(null, 'w-3.5 h-3.5 shrink-0') !!}
                                                {{ \Edzeery\MyStatusKit\Facades\Status::for('order', $order['status']['key'] ?? 'default')->label() }}
                                            </span>
                                            @if (in_array('wilaya', $this->visibleColumns))
                                                <span class="text-xs text-ink-muted">{{ $order['state']['name'] ?? '-' }}</span>
                                            @endif
                                            @if (in_array('amount', $this->visibleColumns))
                                                <span class="text-sm font-semibold text-ink ms-auto">{{ currency($order['total_amount']) }}</span>
                                            @endif
                                        </div>
                                        <div class="mt-3 flex items-center gap-2 flex-wrap">
                                            <x-edz.spinner wire:target="transitionOrder('{{ $orderId }}')"
                                                class="w-3.5 h-3.5 text-ink-muted" />
                                            <button wire:click="openOrderDetails('{{ $orderId }}')"
                                                class="edz-btn edz-btn--ghost edz-btn--xs" title="{{ __('merchant.order_details') }}">
                                                <x-edz.icon name="info-circle" class="w-4 h-4" />
                                            </button>
                                            @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
                                                <button @click="$wire.openEditModal('{{ $orderId }}')"
                                                    class="edz-btn edz-btn--ghost edz-btn--xs" title="{{ __('merchant_panel.edit') }}">
                                                    <x-edz.icon name="edit" class="w-4 h-4" />
                                                </button>
                                                <button wire:click="openReassignModal('{{ $orderId }}')"
                                                    class="edz-btn edz-btn--ghost edz-btn--xs" title="{{ __('merchant_panel.reassign') }}">
                                                    <x-edz.icon name="arrows-right-left" class="w-4 h-4" />
                                                </button>
                                            @endif
                                            @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_DELETE->value))
                                                <button x-on:click.prevent="confirmDelete()" :disabled="deleteLoading"
                                                    :class="deleteLoading ? 'opacity-50' : ''"
                                                    class="edz-btn edz-btn--ghost edz-btn--xs text-danger-600" title="{{ __('merchant.delete_permanently') }}">
                                                    <x-edz.spinner show="deleteLoading" class="w-3.5 h-3.5" />
                                                    <x-edz.icon name="trash" x-show="!deleteLoading" class="w-4 h-4" />
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                            </div>
                        @endforeach
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
                            <x-edz.spinner wire:target="submitReassign" class="w-3.5 h-3.5" />
                            <span wire:loading.remove wire:target="submitReassign">{{ __('merchant_panel.reassign') }}</span>
                        </button>
                    </div>
                </div>
            </x-edz.modal>
        @endif
    </div>

    {{-- Table Settings Modal --}}
    @if ($showTableSettings)
        <div @edz-modal-closed.window="$wire.discardTableSettings()">
            <x-edz.modal :isOpen="true" size="lg" wire:key="order-table-settings">
                <div x-data="{ tab: 'columns' }" class="p-6">
                    {{-- Header --}}
                    <div class="mb-5">
                        <h3 class="text-lg font-bold text-ink">{{ __('merchant_panel.table_settings') }}</h3>
                    </div>

                    {{-- Tabs --}}
                    <div class="inline-flex w-full sm:w-auto items-center gap-1 p-1 bg-surface-secondary rounded-xl mb-5">
                        <button @click="tab = 'columns'" type="button"
                            class="flex-1 sm:flex-none px-4 py-2 text-sm font-semibold rounded-lg transition"
                            :class="tab === 'columns' ? 'bg-surface text-ink shadow-sm' : 'text-ink-muted hover:text-ink'">
                            <span class="inline-flex items-center gap-1.5">
                                <x-edz.icon name="view-columns" class="w-4 h-4" />
                                {{ __('merchant_panel.tab_columns') }}
                            </span>
                        </button>
                        <button @click="tab = 'style'" type="button"
                            class="flex-1 sm:flex-none px-4 py-2 text-sm font-semibold rounded-lg transition"
                            :class="tab === 'style' ? 'bg-surface text-ink shadow-sm' : 'text-ink-muted hover:text-ink'">
                            <span class="inline-flex items-center gap-1.5">
                                <x-edz.icon name="color-palette" class="w-4 h-4" />
                                {{ __('merchant_panel.tab_style') }}
                            </span>
                        </button>
                    </div>

                    {{-- Tab: Columns --}}
                    <div x-show="tab === 'columns'" x-cloak class="space-y-5">
                        @php
                            $settingsColumns = $this->orderColumns();
                            $settingsPrimaries = collect($settingsColumns)->where('default', true)->all();
                            $settingsSecondaries = collect($settingsColumns)->where('default', false)->all();
                        @endphp

                        {{-- Primary (pinned) columns --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-xs font-semibold text-ink-muted uppercase tracking-wide">
                                    {{ __('merchant_panel.primary_columns') }}</p>
                                <span class="text-[10px] font-medium text-ink-muted">{{ __('merchant_panel.always_visible') }}</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                                @foreach ($settingsPrimaries as $col)
                                    <div
                                        class="flex items-center gap-2 px-2.5 py-2 rounded-lg bg-surface-secondary/60 border border-surface-border text-sm text-ink">
                                        <x-edz.icon name="lock-closed" class="w-3.5 h-3.5 text-ink-muted shrink-0" />
                                        {{ __("merchant_panel.{$col['label_key']}") }}
                                    </div>
                                @endforeach
                            </div>
                            <p class="mt-1.5 text-xs text-ink-muted">{{ __('merchant_panel.primary_columns_hint') }}</p>
                        </div>

                        {{-- Secondary (configurable) columns --}}
                        <div>
                            <p class="text-xs font-semibold text-ink-muted uppercase tracking-wide mb-2">
                                {{ __('merchant_panel.secondary_columns') }}</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                                @foreach ($settingsSecondaries as $col)
                                    <label
                                        class="flex items-center gap-2 px-2.5 py-2 rounded-lg border border-surface-border hover:bg-surface-secondary cursor-pointer text-sm">
                                        <input type="checkbox" wire:click="toggleDraftColumn('{{ $col['key'] }}')"
                                            {{ in_array($col['key'], $this->draftColumns) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-accent-600 focus:ring-accent-500">
                                        {{ __("merchant_panel.{$col['label_key']}") }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Tab: Style --}}
                    <div x-show="tab === 'style'" x-cloak>
                        <p class="text-xs font-semibold text-ink-muted uppercase tracking-wide mb-2">
                            {{ __('merchant_panel.tab_style') }}</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <button type="button" wire:click="$set('draftStyle', 'default')"
                                class="flex items-start gap-3 text-start p-4 rounded-xl border transition {{ $this->draftStyle === 'default' ? 'border-accent-500 ring-1 ring-accent-500 bg-accent-50/40' : 'border-surface-border hover:bg-surface-secondary' }}">
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-ink">{{ __('merchant_panel.style_default') }}</p>
                                    <div class="mt-2 flex items-center gap-1.5 text-[10px]">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full font-semibold bg-surface-secondary text-ink-muted">#1001</span>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full font-semibold bg-surface-secondary text-ink-muted">#1002</span>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full font-semibold bg-surface-secondary text-ink-muted">#1003</span>
                                    </div>
                                </div>
                                <x-edz.icon name="check"
                                    class="w-4 h-4 mt-0.5 shrink-0 {{ $this->draftStyle === 'default' ? 'text-accent-600' : 'text-surface-border' }}" />
                            </button>

                            <button type="button" wire:click="$set('draftStyle', 'status')"
                                class="flex items-start gap-3 text-start p-4 rounded-xl border transition {{ $this->draftStyle === 'status' ? 'border-accent-500 ring-1 ring-accent-500 bg-accent-50/40' : 'border-surface-border hover:bg-surface-secondary' }}">
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-ink">{{ __('merchant_panel.style_status') }}</p>
                                    <p class="mt-0.5 text-xs text-ink-muted">{{ __('merchant_panel.style_status_hint') }}</p>
                                    <div class="mt-2 rounded-lg overflow-hidden border border-surface-border text-[10px]">
                                        <table class="w-full">
                                            <tbody>
                                                <tr class="edz-table-row--success">
                                                    <td class="px-2.5 py-1.5 font-semibold">#1001</td>
                                                    <td class="px-2.5 py-1.5">{{ __('merchant_panel.style_status') }}</td>
                                                </tr>
                                                <tr class="edz-table-row--warning">
                                                    <td class="px-2.5 py-1.5 font-semibold">#1002</td>
                                                    <td class="px-2.5 py-1.5">{{ __('merchant_panel.style_status') }}</td>
                                                </tr>
                                                <tr class="edz-table-row--danger">
                                                    <td class="px-2.5 py-1.5 font-semibold">#1003</td>
                                                    <td class="px-2.5 py-1.5">{{ __('merchant_panel.style_status') }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <x-edz.icon name="check"
                                    class="w-4 h-4 mt-0.5 shrink-0 {{ $this->draftStyle === 'status' ? 'text-accent-600' : 'text-surface-border' }}" />
                            </button>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-5 mt-6 border-t border-surface-border">
                        <button type="button" wire:click="resetColumns"
                            class="edz-btn edz-btn--ghost edz-btn--sm">{{ __('merchant_panel.reset_columns') }}</button>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="discardTableSettings"
                                class="edz-btn edz-btn--ghost edz-btn--sm">{{ __('merchant_panel.cancel') }}</button>
                            <button wire:click="saveTableSettings" class="edz-btn edz-btn--primary edz-btn--sm"
                                wire:loading.attr="disabled" wire:loading.class="opacity-50 pointer-events-none"
                                wire:target="saveTableSettings">
                                <x-edz.spinner wire:target="saveTableSettings" class="w-3.5 h-3.5" />
                                <span wire:loading.remove
                                    wire:target="saveTableSettings">{{ __('merchant_panel.save_settings') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </x-edz.modal>
        </div>
    @endif

    {{-- Order Details Modal (Phase 23): responsive popup with de-duplicated fields --}}
    @if ($this->detailsOrderId)
        @php
            $detailsOrder = collect($this->orders['data'] ?? [])->firstWhere('id', $this->detailsOrderId);
        @endphp
        @if ($detailsOrder)
            @php
                $detailsStatus = \Edzeery\MyStatusKit\Facades\Status::for('order', $detailsOrder['status']['key'] ?? 'default');
                $detailsTracking = $detailsOrder['tracking'] ?? null;
            @endphp
            <div @edz-modal-closed.window="$wire.closeOrderDetails()">
                <x-edz.modal :isOpen="true" size="md" wire:key="order-details-modal">
                    <div class="p-6">
                        {{-- Header --}}
                        <div class="flex items-start gap-3">
                            <div
                                class="flex items-center justify-center w-10 h-10 rounded-full bg-accent-surface text-accent-fg-strong shrink-0">
                                <x-edz.icon name="info-circle" class="w-5 h-5" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <h3 class="text-base sm:text-lg font-bold text-ink">
                                        {{ $detailsOrder['number'] ? '#' . $detailsOrder['number'] : __('merchant_panel.order_details') }}
                                    </h3>
                                    <span
                                        class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-0.5 rounded-full {{ $detailsStatus->color() }}">
                                        {!! $detailsStatus->icon(null, 'w-3.5 h-3.5 shrink-0') !!}
                                        <span>{{ $detailsStatus->label() }}</span>
                                    </span>
                                </div>
                                <p class="mt-0.5 text-sm font-medium text-ink truncate">
                                    {{ $detailsOrder['customer']['name'] ?? '—' }}</p>
                                <p class="text-xs text-ink-muted truncate" dir="ltr">
                                    {{ $detailsOrder['customer']['phone'] ?? '—' }}</p>
                            </div>
                        </div>
                        <div
                            class="mt-3 pt-3 flex flex-wrap items-center gap-x-3 gap-y-1 border-t border-surface-border text-xs text-ink-muted">
                            <span>{{ \Carbon\Carbon::parse($detailsOrder['created_at'])->format('M d, Y') }}</span>
                            @if (!empty($detailsOrder['state']['name']))
                                <span class="inline-flex items-center gap-1">
                                    <x-edz.icon name="map-pin" class="w-3.5 h-3.5" />
                                    {{ $detailsOrder['state']['name'] }}
                                </span>
                            @endif
                        </div>

                        @php
                            $showItems = !in_array('products', $this->visibleColumns) || !in_array('amount', $this->visibleColumns);
                            $showShipping = !in_array('delivery_type', $this->visibleColumns) || !in_array('shipment_type', $this->visibleColumns) || !in_array('weight', $this->visibleColumns) || !in_array('stopdesk_point', $this->visibleColumns) || !in_array('send_from_carrier_warehouse', $this->visibleColumns);
                            $showContact = !in_array('phone_secondary', $this->visibleColumns) || !in_array('address', $this->visibleColumns) || !in_array('city', $this->visibleColumns) || !in_array('meta', $this->visibleColumns);
                            $showAssignment = !in_array('assigned_agent', $this->visibleColumns) || !in_array('confirmation_attempts', $this->visibleColumns) || !in_array('last_contact', $this->visibleColumns) || !in_array('notes', $this->visibleColumns) || !in_array('confirmed_by', $this->visibleColumns);
                        @endphp

                        {{-- Items --}}
                        @if ($showItems)
                            <section class="mt-5">
                                <h4
                                    class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-2">
                                    <x-edz.icon name="bag" class="w-4 h-4" />
                                    {{ __('merchant_panel.products') }}
                                </h4>
                                <div
                                    class="rounded-xl border border-surface-border divide-y divide-surface-border overflow-hidden bg-surface-tertiary/30 text-sm">
                                    @if (!in_array('products', $this->visibleColumns))
                                        @forelse ($detailsOrder['items_summary'] ?? [] as $item)
                                            <div class="flex items-center justify-between gap-3 px-3 py-2">
                                                <span class="min-w-0 flex-1 truncate text-ink">{{ $item['name'] }}
                                                    <span class="text-ink-muted">×{{ $item['qty'] }}</span></span>
                                                <span
                                                    class="font-medium text-ink shrink-0">{{ currency($item['price'] * $item['qty']) }}</span>
                                            </div>
                                        @empty
                                            <div class="px-3 py-2 text-ink-muted text-xs">
                                                {{ __('merchant_panel.no_orders_found') }}</div>
                                        @endforelse
                                    @endif
                                    @if (!in_array('amount', $this->visibleColumns))
                                        <div
                                            class="flex items-center justify-between gap-3 px-3 py-2.5 bg-surface font-bold text-ink">
                                            <span>{{ __('merchant_panel.total') }}</span>
                                            <span>{{ currency($detailsOrder['total_amount']) }}</span>
                                        </div>
                                    @endif
                                </div>
                            </section>
                        @endif

                        {{-- Shipping & Payment --}}
                        @if ($showShipping)
                            <section class="mt-5">
                                <h4
                                    class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-2">
                                    <x-edz.icon name="credit-card" class="w-4 h-4" />
                                    {{ __('merchant_panel.details_shipping') }}
                                </h4>
                                <dl
                                    class="rounded-xl border border-surface-border divide-y divide-surface-border overflow-hidden bg-surface-tertiary/30 text-sm">
                                    @if (!in_array('delivery_type', $this->visibleColumns))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.delivery') }}</dt>
                                            <dd class="text-ink text-end">{{ $detailsOrder['delivery_type'] === 'stopdesk' ? __('merchant_panel.stop_desk_label') : ($detailsOrder['delivery_type'] === 'home' ? __('merchant_panel.home_delivery_label') : ($detailsOrder['delivery_type'] ?? '—')) }}</dd>
                                        </div>
                                    @endif
                                    @if (!in_array('shipment_type', $this->visibleColumns))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.shipment') }}</dt>
                                            <dd class="text-ink text-end capitalize">{{ $detailsOrder['shipment_type'] ?? '—' }}</dd>
                                        </div>
                                    @endif
                                    <div class="flex items-start justify-between gap-3 px-3 py-2">
                                        <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.payment_method') }}</dt>
                                        <dd class="text-ink text-end uppercase">{{ $detailsOrder['payment_method'] ?? '—' }}</dd>
                                    </div>
                                    @if (!in_array('weight', $this->visibleColumns))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.weight') }}</dt>
                                            <dd class="text-ink text-end">{{ $detailsOrder['weight_kg'] ? $detailsOrder['weight_kg'] . ' kg' : '—' }}</dd>
                                        </div>
                                    @endif
                                    @if (!in_array('stopdesk_point', $this->visibleColumns) && !empty($detailsOrder['stopdesk_point']))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.stopdesk_point') }}</dt>
                                            <dd class="text-ink text-end">{{ $detailsOrder['stopdesk_point']['name'] ?? '—' }}@if (!empty($detailsOrder['stopdesk_point']['city']['name'])) ({{ $detailsOrder['stopdesk_point']['city']['name'] }})@endif</dd>
                                        </div>
                                    @endif
                                    @if (!in_array('send_from_carrier_warehouse', $this->visibleColumns))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.send_from_carrier_warehouse') }}</dt>
                                            <dd class="text-ink text-end">
                                                @if ($detailsOrder['send_from_carrier_warehouse'] ?? false)
                                                    <x-edz.badge tone="success" sm>
                                                        <x-edz.icon name="check" class="w-3 h-3" />
                                                    </x-edz.badge>
                                                @else
                                                    <x-edz.badge tone="neutral" sm>
                                                        <x-edz.icon name="x-mark" class="w-3 h-3" />
                                                    </x-edz.badge>
                                                @endif
                                            </dd>
                                        </div>
                                    @endif
                                </dl>
                            </section>
                        @endif

                        {{-- Contact & Location --}}
                        @if ($showContact)
                            <section class="mt-5">
                                <h4
                                    class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-2">
                                    <x-edz.icon name="map-pin" class="w-4 h-4" />
                                    {{ __('merchant_panel.details_contact') }}
                                </h4>
                                <dl
                                    class="rounded-xl border border-surface-border divide-y divide-surface-border overflow-hidden bg-surface-tertiary/30 text-sm">
                                    @if (!in_array('phone_secondary', $this->visibleColumns))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.phone_secondary') }}</dt>
                                            <dd class="text-ink text-end" dir="ltr">{{ $detailsOrder['phone_secondary'] ?? '—' }}</dd>
                                        </div>
                                    @endif
                                    @if (!in_array('city', $this->visibleColumns))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.city') }}</dt>
                                            <dd class="text-ink text-end">{{ $detailsOrder['city']['name'] ?? '—' }}</dd>
                                        </div>
                                    @endif
                                    @if (!in_array('address', $this->visibleColumns))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.address') }}</dt>
                                            <dd class="text-ink text-end min-w-0">{{ $detailsOrder['address'] ? \Illuminate\Support\Str::limit($detailsOrder['address'], 60) : '—' }}</dd>
                                        </div>
                                    @endif
                                    @if (!in_array('meta', $this->visibleColumns) && !empty($detailsOrder['meta']))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.meta') }}</dt>
                                            <dd class="text-ink text-end min-w-0">{{ collect($detailsOrder['meta'])->map(fn($v, $k) => "{$k}: {$v}")->implode(', ') }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </section>
                        @endif

                        {{-- Assignment --}}
                        @if ($showAssignment)
                            <section class="mt-5">
                                <h4
                                    class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-2">
                                    <x-edz.icon name="users" class="w-4 h-4" />
                                    {{ __('merchant_panel.assignment') }}
                                </h4>
                                <dl
                                    class="rounded-xl border border-surface-border divide-y divide-surface-border overflow-hidden bg-surface-tertiary/30 text-sm">
                                    @if (!in_array('assigned_agent', $this->visibleColumns))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.agent') }}</dt>
                                            <dd class="text-ink text-end">{{ $detailsOrder['assigned_membership']['user']['name'] ?? '—' }}</dd>
                                        </div>
                                    @endif
                                    <div class="flex items-start justify-between gap-3 px-3 py-2">
                                        <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.method') }}</dt>
                                        <dd class="text-ink text-end">{{ $detailsOrder['assignment_method'] ? ucfirst($detailsOrder['assignment_method']) : '—' }}</dd>
                                    </div>
                                    <div class="flex items-start justify-between gap-3 px-3 py-2">
                                        <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.created_by') }}</dt>
                                        <dd class="text-ink text-end">{{ $detailsOrder['created_by_membership_id'] ? ($detailsOrder['created_by_membership']['user']['name'] ?? '—') : '—' }}</dd>
                                    </div>
                                    @if (!in_array('confirmed_by', $this->visibleColumns))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.confirmed_by') }}</dt>
                                            <dd class="text-ink text-end">{{ $detailsOrder['confirmed_by_history']['changed_by']['user']['name'] ?? '—' }}</dd>
                                        </div>
                                    @endif
                                    @if (!in_array('confirmation_attempts', $this->visibleColumns))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.attempts') }}</dt>
                                            <dd class="text-ink text-end">{{ $detailsOrder['confirmation_attempts'] ?? 0 }}</dd>
                                        </div>
                                    @endif
                                    @if (!in_array('last_contact', $this->visibleColumns))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.last_contact') }}</dt>
                                            <dd class="text-ink text-end">{{ $detailsOrder['last_contact_at'] ? \Carbon\Carbon::parse($detailsOrder['last_contact_at'])->diffForHumans() : '—' }}</dd>
                                        </div>
                                    @endif
                                </dl>
                                @if (!in_array('notes', $this->visibleColumns) && !empty($detailsOrder['notes']))
                                    <div class="mt-2 p-2.5 rounded-lg bg-surface-tertiary text-sm text-ink-muted italic">
                                        "{{ $detailsOrder['notes'] }}"
                                    </div>
                                @endif
                            </section>
                        @endif

                        {{-- Tracking --}}
                        @if ($detailsTracking && (!in_array('shipping_provider', $this->visibleColumns) || !empty($detailsTracking['tracking_number']) || !empty($detailsTracking['shipped_at']) || !empty($detailsTracking['delivered_at'])))
                            <section class="mt-5">
                                <h4
                                    class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-2">
                                    <x-edz.icon name="truck" class="w-4 h-4" />
                                    {{ __('merchant_panel.tracking') }}
                                </h4>
                                <dl
                                    class="rounded-xl border border-surface-border divide-y divide-surface-border overflow-hidden bg-surface-tertiary/30 text-sm">
                                    @if (!in_array('shipping_provider', $this->visibleColumns) && !empty($detailsTracking['shipping_provider']))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.carrier') }}</dt>
                                            <dd class="text-ink text-end">{{ $detailsTracking['shipping_provider'] }}</dd>
                                        </div>
                                    @endif
                                    @if (!empty($detailsTracking['tracking_number']))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.tracking_number') }}</dt>
                                            <dd class="text-ink text-end font-mono">{{ $detailsTracking['tracking_number'] }}</dd>
                                        </div>
                                    @endif
                                    @if (!empty($detailsTracking['shipped_at']))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.shipped_at') }}</dt>
                                            <dd class="text-ink text-end">{{ $detailsTracking['shipped_at'] }}</dd>
                                        </div>
                                    @endif
                                    @if (!empty($detailsTracking['delivered_at']))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.delivered_at') }}</dt>
                                            <dd class="text-ink text-end">{{ $detailsTracking['delivered_at'] }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </section>
                        @endif
                    </div>
                </x-edz.modal>
            </div>
        @endif
    @endif

    @include('livewire.merchant.orders.partials.order-form-modal')

    {{-- Filter Portal — single container, fixed-positioned --}}
    <div x-data="dropdownPosition()" x-show="open" x-transition @click.away="close()"
        @edz-filter-open.window="$event.detail && toggle($event, $event.detail)"
        :style="`top: ${top}px; left: ${left}px`"
        class="fixed z-50 p-2 bg-surface border border-surface-border rounded-xl shadow-lg"
        :class="{
            'max-h-64 overflow-y-auto edz-scroll': open === 'wilaya' || open === 'status' ||
                open === 'assigned_to' || open === 'city' || open === 'delivery_type' ||
                open === 'shipping_provider' || open === 'stopdesk_point' ||
                open === 'shipment_type' || open === 'confirmed_by',
            'w-48': open === 'product' || open === 'amount' || open === 'address' ||
                open === 'notes' || open === 'phone_secondary' || open === 'weight' ||
                open === 'send_from_carrier_warehouse',
            'w-52': open === 'wilaya' || open === 'status' || open === 'assigned_to' ||
                open === 'date'
        }">

        {{-- Wilaya --}}
        @if (in_array('wilaya', $this->visibleColumns))
            <div x-show="open === 'wilaya'" x-cloak>
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
            <div x-show="open === 'product'" x-cloak>
                <x-edz.product-select :options="$filterProducts" wire:model="filters.product_id"
                    wire:fullmodel="filters.product" size="sm"
                    placeholder="{{ __('merchant_panel.filter_by_product') }}" />
            </div>
        @endif

        {{-- Amount --}}
        @if (in_array('amount', $this->visibleColumns))
            <div x-show="open === 'amount'" x-cloak>
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
            <div x-show="open === 'status'" x-cloak>
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
                        {{ \Edzeery\MyStatusKit\Facades\Status::for('order', $s['key'] ?? 'default')->label() }}
                    </label>
                @endforeach
            </div>
        @endif

        {{-- Assigned Agent --}}
        @if (in_array('assigned_agent', $this->visibleColumns))
            <div x-show="open === 'assigned_to'" x-cloak>
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
            <div x-show="open === 'date'" x-cloak>
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

        {{-- Delivery Type --}}
        @if (in_array('delivery_type', $this->visibleColumns))
            <div x-show="open === 'delivery_type'" x-cloak>
                <button @click="$wire.setFilter('delivery_type', null)"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ !$this->filters['delivery_type'] ? 'bg-surface-secondary font-medium' : '' }}">
                    —
                </button>
                <button @click="$wire.setFilter('delivery_type', 'home')"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['delivery_type'] === 'home' ? 'bg-surface-secondary font-medium' : '' }}">
                    {{ __('merchant_panel.home_delivery_label') }}
                </button>
                <button @click="$wire.setFilter('delivery_type', 'stopdesk')"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['delivery_type'] === 'stopdesk' ? 'bg-surface-secondary font-medium' : '' }}">
                    {{ __('merchant_panel.stop_desk_label') }}
                </button>
            </div>
        @endif

        {{-- Shipment Type --}}
        @if (in_array('shipment_type', $this->visibleColumns))
            <div x-show="open === 'shipment_type'" x-cloak>
                <button @click="$wire.setFilter('shipment_type', null)"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ !$this->filters['shipment_type'] ? 'bg-surface-secondary font-medium' : '' }}">
                    —
                </button>
                <button @click="$wire.setFilter('shipment_type', 'delivery')"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['shipment_type'] === 'delivery' ? 'bg-surface-secondary font-medium' : '' }}">
                    {{ __('merchant_panel.delivery') }}
                </button>
                <button @click="$wire.setFilter('shipment_type', 'exchange')"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['shipment_type'] === 'exchange' ? 'bg-surface-secondary font-medium' : '' }}">
                    {{ __('merchant_panel.exchange_label') }}
                </button>
                <button @click="$wire.setFilter('shipment_type', 'pickup')"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['shipment_type'] === 'pickup' ? 'bg-surface-secondary font-medium' : '' }}">
                    {{ __('merchant_panel.pickup_label') }}
                </button>
            </div>
        @endif

        {{-- Shipping Provider --}}
        @if (in_array('shipping_provider', $this->visibleColumns))
            <div x-show="open === 'shipping_provider'" x-cloak>
                <button @click="$wire.setFilter('shipping_provider', null); $wire.setFilter('stopdesk_point', null)"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ !$this->filters['shipping_provider'] ? 'bg-surface-secondary font-medium' : '' }}">
                    —
                </button>
                @foreach ($this->allProviders as $pr)
                    <button @click="$wire.setFilter('shipping_provider', '{{ $pr['id'] }}'); $wire.setFilter('stopdesk_point', null); $wire.loadFilterStopdeskPoints('{{ $pr['id'] }}')"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['shipping_provider'] == $pr['id'] ? 'bg-surface-secondary font-medium' : '' }}"
                        data-name="{{ $pr['name'] }}">
                        {{ $pr['name'] }}
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Stopdesk Point (cascades from shipping_provider) --}}
        @if (in_array('stopdesk_point', $this->visibleColumns))
            <div x-show="open === 'stopdesk_point'" x-cloak>
                @if (!filled($this->filters['shipping_provider']))
                    <div
                        class="px-2.5 py-1.5 rounded-lg text-xs text-ink-muted">{{ __('merchant_panel.select_provider_first') }}</div>
                @else
                    <button @click="$wire.setFilter('stopdesk_point', null)"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ !$this->filters['stopdesk_point'] ? 'bg-surface-secondary font-medium' : '' }}">
                        —
                    </button>
                    @foreach ($this->allStopdeskPoints as $dp)
                        <button @click="$wire.setFilter('stopdesk_point', '{{ $dp['id'] }}')"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['stopdesk_point'] == $dp['id'] ? 'bg-surface-secondary font-medium' : '' }}"
                            data-name="{{ $dp['name'] }}">
                            {{ $dp['name'] }}
                        </button>
                    @endforeach
                @endif
            </div>
        @endif

        {{-- City (cascades from wilaya) --}}
        @if (in_array('city', $this->visibleColumns))
            <div x-show="open === 'city'" x-cloak>
                @if (!filled($this->filters['wilaya']))
                    <div
                        class="px-2.5 py-1.5 rounded-lg text-xs text-ink-muted">{{ __('merchant_panel.select_state_first') }}</div>
                @else
                    <button @click="$wire.setFilter('city', null)"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ !$this->filters['city'] ? 'bg-surface-secondary font-medium' : '' }}">
                        —
                    </button>
                    @foreach ($this->allCities as $ct)
                        <button @click="$wire.setFilter('city', '{{ $ct['id'] }}')"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['city'] == $ct['id'] ? 'bg-surface-secondary font-medium' : '' }}"
                            data-name="{{ $ct['name'] }}">
                            {{ $ct['name'] }}
                        </button>
                    @endforeach
                @endif
            </div>
        @endif

        {{-- Confirmed By --}}
        @if (in_array('confirmed_by', $this->visibleColumns))
            <div x-show="open === 'confirmed_by'" x-cloak>
                <button @click="$wire.setFilter('confirmed_by', null)"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ !$this->filters['confirmed_by'] ? 'bg-surface-secondary font-medium' : '' }}">
                    —
                </button>
                @foreach ($this->allMembers as $m)
                    <button @click="$wire.setFilter('confirmed_by', '{{ $m['id'] }}')"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['confirmed_by'] == $m['id'] ? 'bg-surface-secondary font-medium' : '' }}"
                        data-name="{{ $m['user']['name'] }}">
                        {{ $m['user']['name'] }}
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Address --}}
        @if (in_array('address', $this->visibleColumns))
            <div x-show="open === 'address'" x-cloak>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.600ms="filters.address"
                        class="edz-input text-xs w-full pe-6" placeholder="{{ __('merchant_panel.address') }}"
                        autocomplete="off">
                    @if ($this->filters['address'] !== '')
                        <button wire:click="$set('filters.address', '')" type="button"
                            class="absolute end-1 top-1/2 -translate-y-1/2 text-ink-muted hover:text-accent-500 transition"
                            aria-label="Clear address">
                            <x-edz.icon name="x-mark" class="w-3.5 h-3.5" />
                        </button>
                    @endif
                </div>
            </div>
        @endif

        {{-- Notes --}}
        @if (in_array('notes', $this->visibleColumns))
            <div x-show="open === 'notes'" x-cloak>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.600ms="filters.notes"
                        class="edz-input text-xs w-full pe-6" placeholder="{{ __('merchant_panel.notes') }}"
                        autocomplete="off">
                    @if ($this->filters['notes'] !== '')
                        <button wire:click="$set('filters.notes', '')" type="button"
                            class="absolute end-1 top-1/2 -translate-y-1/2 text-ink-muted hover:text-accent-500 transition"
                            aria-label="Clear notes">
                            <x-edz.icon name="x-mark" class="w-3.5 h-3.5" />
                        </button>
                    @endif
                </div>
            </div>
        @endif

        {{-- Phone Secondary --}}
        @if (in_array('phone_secondary', $this->visibleColumns))
            <div x-show="open === 'phone_secondary'" x-cloak>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.600ms="filters.phone_secondary"
                        class="edz-input text-xs w-full pe-6" placeholder="{{ __('merchant_panel.phone_secondary') }}"
                        autocomplete="off">
                    @if ($this->filters['phone_secondary'] !== '')
                        <button wire:click="$set('filters.phone_secondary', '')" type="button"
                            class="absolute end-1 top-1/2 -translate-y-1/2 text-ink-muted hover:text-accent-500 transition"
                            aria-label="Clear phone 2">
                            <x-edz.icon name="x-mark" class="w-3.5 h-3.5" />
                        </button>
                    @endif
                </div>
            </div>
        @endif

        {{-- Weight (min/max range) --}}
        @if (in_array('weight', $this->visibleColumns))
            <div x-show="open === 'weight'" x-cloak>
                <div class="flex items-center gap-1">
                    <div class="relative flex-1">
                        <input type="number" wire:model.live.debounce.600ms="filters.weight_min" placeholder="Min"
                            class="edz-input text-xs w-full pe-6" step="0.01">
                        @if ($this->filters['weight_min'] !== null && $this->filters['weight_min'] !== '')
                            <button wire:click="$set('filters.weight_min', '')" type="button"
                                class="absolute end-1 top-1/2 -translate-y-1/2 text-ink-muted hover:text-accent-500 transition"
                                aria-label="Clear min weight">
                                <x-edz.icon name="x-mark" class="w-3.5 h-3.5" />
                            </button>
                        @endif
                    </div>
                    <div class="relative flex-1">
                        <input type="number" wire:model.live.debounce.600ms="filters.weight_max" placeholder="Max"
                            class="edz-input text-xs w-full pe-6" step="0.01">
                        @if ($this->filters['weight_max'] !== null && $this->filters['weight_max'] !== '')
                            <button wire:click="$set('filters.weight_max', '')" type="button"
                                class="absolute end-1 top-1/2 -translate-y-1/2 text-ink-muted hover:text-accent-500 transition"
                                aria-label="Clear max weight">
                                <x-edz.icon name="x-mark" class="w-3.5 h-3.5" />
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Send from carrier warehouse (tri-state) --}}
        @if (in_array('send_from_carrier_warehouse', $this->visibleColumns))
            <div x-show="open === 'send_from_carrier_warehouse'" x-cloak>
                <button @click="$wire.setFilter('send_from_carrier_warehouse', null)"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['send_from_carrier_warehouse'] === null ? 'bg-surface-secondary font-medium' : '' }}">
                    {{ __('general.all') }}
                </button>
                <button @click="$wire.setFilter('send_from_carrier_warehouse', true)"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['send_from_carrier_warehouse'] === true ? 'bg-surface-secondary font-medium' : '' }}">
                    {{ __('buttons.yes') }}
                </button>
                <button @click="$wire.setFilter('send_from_carrier_warehouse', false)"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['send_from_carrier_warehouse'] === false ? 'bg-surface-secondary font-medium' : '' }}">
                    {{ __('buttons.no') }}
                </button>
            </div>
        @endif
    </div>
</div>
