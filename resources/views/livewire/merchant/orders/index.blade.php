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
use function Livewire\Volt\uses;

layout('components.layouts.store');

uses([\App\Livewire\Concerns\HasInlineEdit::class]);

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

    // Inline-edit options for the row currently being edited (31.3 searchable selects)
    'editCityOptions' => [],
    'editProviderOptions' => [],
    'editStopdeskOptions' => [],
    'editDeliveryTypeOptions' => [],
    'editShipmentTypeOptions' => [],
    'editAgentOptions' => [],

    // 31.4 — inline discount editing (type/value/reason drive order.discount_*)
    'discountEditType' => '',
    'discountEditValue' => '',
    'discountEditReason' => '',

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
        'shipping_provider_id' => '',
        'stopdesk_point_id' => '',
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

    // Carrier-first office picker options (create + edit) for x-edz.select
    'formOffices' => [],
    'loadingOffices' => false,

    // Row delivery quick-edit modal (30.2): full cascade reuses $this->form
    // delivery fields + allStates/allCities/formOffices + the cascade methods.
    'showDeliveryModal' => false,
    'deliveryOrderId' => null,
    'deliverySaving' => false,

    // Duplicate-detection warnings inside the create/edit form (P28 extended)
    'formDuplicateWarnings' => [],

    // Confirmation drawer (P26)
    'showConfirmModal' => false,
    'confirmOrderId' => null,
    'confirmProviderId' => '',
    'confirmContacted' => false,
    'confirmSummary' => null,
    'duplicateWarnings' => [],

    // Duplicate scan popup (P29.5): lazy, only computed when the row badge is clicked
    'showDuplicateScanModal' => false,
    'duplicateScanNumber' => null,
    'duplicateScanResults' => [],
    'duplicateScanPhoneCount' => 0,
    'duplicateScanLevel' => 'none',
    'duplicateScanRepeatCount' => 0,

    // Order event log (P29.4) — row action dropdown + full-history popup
    'eventsPreviewOrderId' => null,
    'eventsPreview' => [],
    'eventsFullOrderId' => null,
    'eventsFull' => [],
    'eventsFullLabel' => null,

    // Bulk status change modal (P29)
    'showBulkStatusModal' => false,
    'bulkStatusTarget' => '',
    'bulkStatusReason' => '',

    // Bulk send-to-carrier modal (P29.3): grouped by each order's own carrier.
    // bulkSendAnalysis = per-order eligibility (ready, carrier, explicit reasons).
    'showBulkSendModal' => false,
    'bulkSendSummary' => [],
    'bulkSendAnalysis' => [],
    'bulkSendReadyCount' => 0,
    'bulkSendSkipCount' => 0,

    // Inline phone edit (customer phone + order secondary stacked in one cell)
    'phoneEditPhone' => '',
    'phoneEditSecondary' => '',

    // Inline customer name edit (30.7)
    'nameEditName' => '',
]);

updated([
    'search' => function (): void {
        $this->page = 1;
        $this->loadOrders();
    },
]);

// Refresh duplicate warnings inside the create/edit form when the customer
// phone or the picked items change (P28 extended — debounced on discrete actions).
updated([
    'form.customer_phone' => function (): void {
        $this->refreshFormDuplicateWarnings();
    },
    'form.items' => function (): void {
        $this->refreshFormDuplicateWarnings();
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

    // 31.3 — static option sets shared by the inline searchable selects.
    $this->editProviderOptions = collect($this->allProviders)
        ->map(fn($p) => ['value' => (string) $p['id'], 'label' => $p['name']])
        ->values()
        ->all();
    $this->editDeliveryTypeOptions = [
        ['value' => 'home', 'label' => __('merchant_panel.home_delivery_label')],
        ['value' => 'stopdesk', 'label' => __('merchant_panel.stop_desk_label')],
    ];
    $this->editShipmentTypeOptions = [
        ['value' => 'delivery', 'label' => __('merchant_panel.delivery')],
        ['value' => 'exchange', 'label' => __('merchant_panel.exchange_label')],
        ['value' => 'pickup', 'label' => __('merchant_panel.pickup_label')],
    ];
    $this->editAgentOptions = collect($this->allMembers)
        ->map(fn($m) => ['value' => (string) $m['id'], 'label' => $m['user']['name'] ?? '—'])
        ->prepend(['value' => '', 'label' => __('merchant_panel.unassigned')])
        ->values()
        ->all();

    $this->loadColumnPreferences();
    $this->loadFilterProducts();
    $this->loadOrders();
});

$orderColumns = function (): array {
    // Metadata per column (31.1):
    //   default  – part of the freshly-reset visible set
    //   required – always visible, may not be hidden (reorder remains free)
    //   editable – editable directly from the table (inline editing)
    //   roles    – non-empty ⇒ only members holding one of these roles may even see it
    //   info     – design-only column (placeholder until its data source ships)
    $sensitiveRoles = [
        \App\Enums\Store\StoreRoleEnum::OWNER->value,
        \App\Enums\Store\StoreRoleEnum::ADMIN->value,
        \App\Enums\Store\StoreRoleEnum::MANAGER->value,
    ];

    return [
        // identity
        ['key' => 'number', 'label_key' => 'number', 'group' => 'identity', 'default' => false, 'required' => false, 'editable' => false],
        ['key' => 'source', 'label_key' => 'source', 'group' => 'identity', 'default' => false, 'required' => false, 'editable' => false],
        ['key' => 'customer', 'label_key' => 'customer', 'group' => 'identity', 'default' => true, 'required' => true, 'editable' => true],
        ['key' => 'phone', 'label_key' => 'phone', 'group' => 'identity', 'default' => true, 'required' => true, 'editable' => true],
        ['key' => 'verification', 'label_key' => 'verification', 'group' => 'identity', 'default' => true, 'required' => false, 'editable' => false, 'info' => true],
        ['key' => 'notes', 'label_key' => 'notes', 'group' => 'identity', 'default' => false, 'required' => false, 'editable' => true],
        ['key' => 'meta', 'label_key' => 'meta', 'group' => 'identity', 'default' => false, 'required' => false, 'editable' => false],

        // products_financial
        ['key' => 'products', 'label_key' => 'products', 'group' => 'products_financial', 'default' => true, 'required' => true, 'editable' => true],
        ['key' => 'quantity', 'label_key' => 'quantity', 'group' => 'products_financial', 'default' => true, 'required' => true, 'editable' => true],
        ['key' => 'price', 'label_key' => 'price', 'group' => 'products_financial', 'default' => true, 'required' => true, 'editable' => true],
        ['key' => 'total', 'label_key' => 'total', 'group' => 'products_financial', 'default' => true, 'required' => true, 'editable' => false],
        ['key' => 'shipping_cost', 'label_key' => 'shipping_cost', 'group' => 'products_financial', 'default' => false, 'required' => false, 'editable' => false],
        ['key' => 'weight', 'label_key' => 'weight', 'group' => 'products_financial', 'default' => false, 'required' => false, 'editable' => true],
        ['key' => 'shipment_type', 'label_key' => 'shipment', 'group' => 'products_financial', 'default' => false, 'required' => false, 'editable' => true],
        ['key' => 'discount', 'label_key' => 'discount', 'group' => 'products_financial', 'default' => false, 'required' => false, 'editable' => true],

        // geography
        ['key' => 'wilaya', 'label_key' => 'state', 'group' => 'geography', 'default' => true, 'required' => true, 'editable' => true],
        ['key' => 'city', 'label_key' => 'city', 'group' => 'geography', 'default' => true, 'required' => true, 'editable' => true],
        ['key' => 'address', 'label_key' => 'address', 'group' => 'geography', 'default' => true, 'required' => true, 'editable' => true],
        ['key' => 'delivery_type', 'label_key' => 'delivery_type', 'group' => 'geography', 'default' => true, 'required' => true, 'editable' => true],
        ['key' => 'shipping_provider', 'label_key' => 'shipping_provider', 'group' => 'geography', 'default' => true, 'required' => true, 'editable' => true],
        ['key' => 'stopdesk_point', 'label_key' => 'stopdesk_point', 'group' => 'geography', 'default' => true, 'required' => true, 'editable' => true],
        ['key' => 'send_from_carrier_warehouse', 'label_key' => 'send_from_carrier_warehouse', 'group' => 'geography', 'default' => false, 'required' => false, 'editable' => true],

        // workflow
        ['key' => 'status', 'label_key' => 'status', 'group' => 'workflow', 'default' => true, 'required' => true, 'editable' => true],
        ['key' => 'assigned_agent', 'label_key' => 'assigned_agent', 'group' => 'workflow', 'default' => false, 'required' => false, 'editable' => true],
        ['key' => 'created_at', 'label_key' => 'date', 'group' => 'workflow', 'default' => false, 'required' => false, 'editable' => false],
        ['key' => 'confirmation_attempts', 'label_key' => 'attempts', 'group' => 'workflow', 'default' => true, 'required' => false, 'editable' => false, 'roles' => $sensitiveRoles],
        ['key' => 'last_contact', 'label_key' => 'last_contact', 'group' => 'workflow', 'default' => true, 'required' => false, 'editable' => false, 'roles' => $sensitiveRoles],
    ];
};

// Memoized registry lookup + role gate (used by prefs, header, cells and settings modal).
$orderColumn = function (string $key): ?array {
    static $cache = null;
    if ($cache === null) {
        $cache = collect($this->orderColumns())->keyBy('key')->all();
    }

    return $cache[$key] ?? null;
};

$columnAllowedForUser = function (array $col): bool {
    $roles = $col['roles'] ?? null;
    if (empty($roles)) {
        return true;
    }
    foreach ($roles as $role) {
        if (hasStoreRole($role)) {
            return true;
        }
    }

    return false;
};

$loadColumnPreferences = function (): void {
    $registry = $this->orderColumns();
    $validKeys = collect($registry)->pluck('key')->all();
    $required = collect($registry)->where('required', true)->pluck('key')->all();
    $allowed = collect($registry)->filter(fn($col) => $this->columnAllowedForUser($col))->pluck('key')->all();
    $prefsVersion = 2;

    // Canonical default order (31.1): matches the required/default layout.
    $defaults = array_values(array_intersect([
        'customer',
        'phone',
        'verification',
        'status',
        'shipping_provider',
        'delivery_type',
        'wilaya',
        'city',
        'stopdesk_point',
        'address',
        'products',
        'quantity',
        'price',
        'total',
        'confirmation_attempts',
        'last_contact',
    ], $validKeys, $allowed));

    $membership = $this->getCurrentMembership();
    if (!$membership) {
        $this->visibleColumns = $defaults;
        $this->tableStyle = 'default';
        return;
    }

    $pref = UserColumnPreference::where('membership_id', $membership->id)->where('view_key', 'orders_index')->first();

    $isLegacy = ! $pref
        || (int) ($pref->prefs_version ?? 0) !== $prefsVersion;

    // The stored preference is the FULL ordered column list (columns + order).
    $stored = $pref->visible_columns ?? [];
    if (!is_array($stored)) {
        $stored = [];
    }
    // Drop anything unknown OR not visible for this member's roles.
    $stored = array_values(array_intersect($stored, $validKeys, $allowed));

    if ($isLegacy || empty($stored)) {
        $stored = $defaults;
        $pref?->update(['visible_columns' => $stored, 'prefs_version' => $prefsVersion]);
    }

    // Preserve the member's own order; insert any required column (that is not
    // yet present, e.g. added by a future release) at its canonical default spot.
    $ordered = $stored;
    foreach ($defaults as $pos => $key) {
        if (in_array($key, $ordered, true) || !in_array($key, $required, true)) {
            continue;
        }
        $insertAt = min($pos, count($ordered));
        array_splice($ordered, $insertAt, 0, [$key]);
    }
    foreach ($required as $key) {
        if (!in_array($key, $ordered, true) && in_array($key, $allowed, true)) {
            $ordered[] = $key;
        }
    }

    $this->visibleColumns = $ordered;

    $this->tableStyle = $pref?->table_style === 'status' ? 'status' : 'default';
};

$saveColumnPreferences = function (): void {
    $membership = $this->getCurrentMembership();
    if (!$membership) {
        return;
    }

    $registry = $this->orderColumns();
    $validKeys = collect($registry)->pluck('key')->all();
    $required = collect($registry)->where('required', true)->pluck('key')->all();
    $allowed = collect($registry)->filter(fn($col) => $this->columnAllowedForUser($col))->pluck('key')->all();

    // Persist the FULL ordered list so column order is preserved across sessions.
    $ordered = array_values(array_intersect($this->visibleColumns, $validKeys, $allowed));

    // Required columns can never be hidden — force them back in on save,
    // inserted at their canonical default spot rather than just appended.
    $canonical = ['customer', 'phone', 'verification', 'status', 'shipping_provider', 'delivery_type', 'wilaya', 'city', 'stopdesk_point', 'address', 'products', 'quantity', 'price', 'total'];
    foreach ($canonical as $pos => $key) {
        if (in_array($key, $ordered, true) || !in_array($key, $required, true)) {
            continue;
        }
        $insertAt = min($pos, count($ordered));
        array_splice($ordered, $insertAt, 0, [$key]);
    }
    foreach ($required as $key) {
        if (!in_array($key, $ordered, true)) {
            $ordered[] = $key;
        }
    }
    $this->visibleColumns = $ordered;

    UserColumnPreference::updateOrCreate(['membership_id' => $membership->id, 'view_key' => 'orders_index'], [
        'visible_columns' => $ordered,
        'table_style' => $this->tableStyle,
        'prefs_version' => 2,
    ]);
};

$getCurrentMembership = function (): ?\App\Models\Stores\Team\StoreMembership {
    return \App\Models\Stores\Team\StoreMembership::where('store_id', currentStoreId())
        ->where('user_id', auth()->id())
        ->first();
};

// Single source of truth for the row eager-loads (shared by loadOrders and
// refreshSingleOrder). Extended column-by-column as inline editing grows.
$orderEagerLoads = function (): array {
    $with = ['customer', 'status', 'items.product', 'items.variant', 'assignedMembership.user', 'createdByMembership.user', 'state', 'city', 'latestTracking.shippingProvider', 'shippingProvider'];
    if (in_array('confirmed_by', $this->visibleColumns, true)) {
        $with[] = 'confirmedByHistory';
        $with[] = 'confirmedByHistory.status';
        $with[] = 'confirmedByHistory.changedBy.user';
    }
    if (in_array('stopdesk_point', $this->visibleColumns, true)) {
        $with[] = 'stopdeskPoint';
        $with[] = 'stopdeskPoint.city';
    }

    return $with;
};

$loadOrders = function (): void {
    $storeId = currentStoreId();
    $f = $this->filters;

    $with = $this->orderEagerLoads();

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

        // ط§ظپطھط±ط§ط¶ظٹظ‹ط§: ط§ظ„ط·ظ„ط¨ظٹط§طھ ط§ظ„ط¬ط§ط±ظٹط© ظپظٹ طµظپط­ط© ط§ظ„طھطھط¨ط¹ ظ„ط§ طھط¸ظ‡ط± ظ‡ظ†ط§ ((IOException â‰  confirmed)
        if (empty($f['status'])) {
            $carrierStatusIds = Status::system()->forType('order')
                ->whereIn('key', \App\Domains\Order\Support\OrderWorkflow::carrier())
                ->pluck('id')->all();
            if (! empty($carrierStatusIds)) {
                $query->whereNotIn('status_id', $carrierStatusIds);
            }
        }
    }

    $paginated = $query->orderByDesc('created_at')->paginate(min((int) ($this->perPage ?? 50), 50), ['*'], 'page', $this->page);

    // Duplicate marker (P29.5a): siblings with the SAME phone AND at least one shared product
    // inside the 30-day window (same scope as findSimilar, self excluded). Phone is unique
    // per store, so matching by customer_id == matching by phone.
    $duplicateCounts = app(\App\Domains\Order\Services\OrderDuplicateService::class)
        ->countsBySiblings($paginated->getCollection()->pluck('id')->all(), $storeId);

    $membership = $this->getCurrentMembership();

    $service = app(OrderService::class);

    // P29.6 "ordered before": any prior order of the same phone (== customer) that reached
    // the carrier — any age, per the user's rule (the period does not matter for this signal).
    $priorCarrierCounts = app(\App\Domains\Order\Services\OrderDuplicateService::class)
        ->countsPriorCarrierOrders($paginated->getCollection()->pluck('customer_id')->all());

    $carrierKeys = \App\Domains\Order\Support\OrderWorkflow::carrier();

$this->orders = $paginated->toArray();
    $this->orders['data'] = $paginated
        ->getCollection()
        ->map(fn($order) => $this->decorateOrder($order, $service, $duplicateCounts, $priorCarrierCounts, $carrierKeys, $membership))
        ->toArray();

    $this->orders['filtered_total'] = $paginated->total();
};

$decorateOrder = function (Order $order, OrderService $service, array $duplicateCounts, array $priorCarrierCounts, array $carrierKeys, ?StoreMembership $membership): array {
    $arr = $order->toArray();
    // Status key is resolved through the statuses relation (orders.status_id FK);
    // blades must read this explicit key instead of nesting $arr['status']['key'].
    $arr['status_key'] = $order->status?->key;
    // P29.4 — the row actions column shows the event-log dropdown only to members
    // allowed to read the audit log (OWNER/ADMIN always, MANAGER when assigned).
    $arr['can_view_events'] = $membership
        ? \App\Support\StoreOrderPermissions::canViewOrderEventLog($order, $membership)
        : false;
    $arr['duplicate_count'] = (int) ($duplicateCounts[$order->id]['same_product'] ?? 0);
    $arr['duplicate_phone_count'] = (int) ($duplicateCounts[$order->id]['same_phone'] ?? 0);

    // "سبق أن طلب" — the displayed order itself is excluded so a sent order does not
    // flag itself; only a sibling that reached the carrier counts.
    $selfReachedCarrier = $order->shipping_provider_id
        || $order->delivery_rider_id
        || in_array($order->status?->key, $carrierKeys, true);

    $arr['repeat_count'] = max(0, (int) ($priorCarrierCounts[(string) $order->customer_id] ?? 0) - ($selfReachedCarrier ? 1 : 0));

    // One signal per row, precedence: duplicate > probable > repeat.
    $arr['dup_level'] = $arr['duplicate_count'] >= 1
        ? 'duplicate'
        : ($arr['duplicate_phone_count'] >= 1
            ? 'probable'
            : ($arr['repeat_count'] >= 1
                ? 'repeat'
                : null));

    // 31.4 — row-level missing-fields signal: confirm-gate for open (not-yet-confirmed)
    // orders, send-gate (adds destination + carrier) for confirmed/preparing ones.
    $statusKey = $order->status?->key;
    $arr['missing'] = null;
    $arr['missing_keys'] = [];

    if (in_array($statusKey, \App\Domains\Order\Support\OrderWorkflow::backOffice(), true)) {
        $forSend = in_array($statusKey, ['confirmed', 'preparing'], true);
        $missingEntries = app(\App\Domains\Order\Services\OrderCompleteness::class)->missing($order, $forSend);

        if ($missingEntries !== []) {
            $arr['missing'] = array_column($missingEntries, 'label');
            $arr['missing_keys'] = array_column($missingEntries, 'key');
        }
    }

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

    // 31.1 — the "total" column always shows price×qty + shipping − discount
    // (created orders still store the raw item subtotal; edits persist the
    // full formula via the inline/modal flows).
    $arr['items_subtotal'] = round((float) $order->items->sum(fn($i) => (float) $i->price * (int) $i->quantity), 2);
    $arr['discount_amount'] = (float) $order->discount_amount;
    $arr['display_total'] = round($arr['items_subtotal'] + (float) ($order->shipping_cost ?? 0) - $arr['discount_amount'], 2);
    $arr['tracking'] = $order->latestTracking
        ? [
            'tracking_number' => $order->latestTracking->tracking_number,
            'tracking_status' => $order->latestTracking->tracking_status,
            'carrier_status' => $order->latestTracking->carrier_status,
            'carrier_label' => $order->latestTracking->carrier_label,
            'shipped_at' => $order->latestTracking->shipped_at?->format('Y-m-d H:i'),
            'delivered_at' => $order->latestTracking->delivered_at?->format('Y-m-d H:i'),
            'shipping_provider' => $order->latestTracking->shippingProvider?->name,
        ]
        : null;
    return $arr;
};

$refreshSingleOrder = function (string $orderId): void {
    $storeId = currentStoreId();

    $with = $this->orderEagerLoads();

    $query = Order::where('store_id', $storeId)->with($with);
    if ($this->showTrash) {
        $query->onlyTrashed();
    } else {
        $query->withoutTrashed();
    }
    $order = $query->find($orderId);

    if (!$order) {
        return;
    }

    $duplicateCounts = app(\App\Domains\Order\Services\OrderDuplicateService::class)
        ->countsBySiblings([$order->id], $storeId);
    $priorCarrierCounts = app(\App\Domains\Order\Services\OrderDuplicateService::class)
        ->countsPriorCarrierOrders([$order->customer_id]);
    $carrierKeys = \App\Domains\Order\Support\OrderWorkflow::carrier();
    $membership = $this->getCurrentMembership();
    $service = app(OrderService::class);

    $decorated = $this->decorateOrder($order, $service, $duplicateCounts, $priorCarrierCounts, $carrierKeys, $membership);

    foreach (($this->orders['data'] ?? []) as $i => $row) {
        if (($row['id'] ?? null) === $order->id) {
            $this->orders['data'][$i] = $decorated;
            break;
        }
    }
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
    if (! canStore(StorePermissionEnum::ORDER_ASSIGN->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }
    if (empty($this->selectedOrders)) {
        $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('merchant.no_orders_selected')]);
        return;
    }
    if ($membershipId && !StoreMembership::where('id', $membershipId)->where('store_id', currentStoreId())->exists()) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.invalid_agent')]);
        return;
    }
    Order::where('store_id', currentStoreId())
        ->whereIn('id', $this->selectedOrders)
        ->update(['assigned_to_membership_id' => $membershipId]);
    $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('merchant.orders_assigned')]);
    $this->clearSelection();
    $this->loadOrders();
};

// P29.3 — Bulk "send to carrier" grouped by each order's OWN carrier
// (shipping_provider_id, falling back to delivery_rider_id). Orders that are
// NOT ready (status, missing fields, no carrier) are surfaced explicitly in
// the confirmation modal BEFORE anything is sent, and are named with their
// reasons in the result toast. No auto-confirm is ever performed.
$openBulkSendModal = function (): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    if (empty($this->selectedOrders)) {
        $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('merchant.no_orders_selected')]);
        return;
    }

    $orders = Order::with(['status', 'customer', 'shippingProvider', 'deliveryRider', 'items'])
        ->where('store_id', currentStoreId())
        ->whereIn('id', $this->selectedOrders)
        ->get();

    $this->bulkSendAnalysis = $orders
        ->map(fn (Order $order) => $this->resolveBulkOrderState($order))
        ->values()
        ->all();

    $ready = collect($this->bulkSendAnalysis)->where('ready', true);
    $this->bulkSendReadyCount = $ready->count();
    $this->bulkSendSkipCount = count($this->bulkSendAnalysis) - $ready->count();

    $groups = [];
    foreach ($ready as $entry) {
        if (! isset($groups[$entry['carrierKey']])) {
            $groups[$entry['carrierKey']] = ['key' => $entry['carrierKey'], 'name' => $entry['carrierName'], 'count' => 0];
        }
        $groups[$entry['carrierKey']]['count']++;
    }

    $this->bulkSendSummary = array_values($groups);
    $this->showBulkSendModal = true;
};

$closeBulkSendModal = function (): void {
    $this->showBulkSendModal = false;
    $this->bulkSendSummary = [];
    $this->bulkSendAnalysis = [];
    $this->bulkSendReadyCount = 0;
    $this->bulkSendSkipCount = 0;
};

// Per-order readiness + missing-field list — single source is the
// OrderCompleteness domain service (confirm = no carrier, send = + carrier).
$collectMissingFields = function (Order $order, bool $forSend = true): array {
    return app(\App\Domains\Order\Services\OrderCompleteness::class)->missingLabels($order, $forSend);
};

// Single source of truth for bulk eligibility: resolves each order's carrier
// (provider name, fallback rider name) plus explicit "why not ready" reasons.
$resolveBulkOrderState = function (Order $order): array {
    $carrierKey = $order->shipping_provider_id ?: ($order->delivery_rider_id ?: 'unassigned');
    $carrierName = $order->shippingProvider?->name
        ?? ($order->deliveryRider?->name ?? __('order_flow.bulk_send_unassigned'));

    $reasons = [];

    if (! in_array($order->status?->key, ['confirmed', 'preparing'], true)) {
        $reasons[] = __('order_flow.bulk_send_reason_status', ['status' => status_label('order', $order->status?->key)]);
    }

    $missing = $this->collectMissingFields($order);

    if (blank($order->shipping_provider_id) && blank($order->delivery_rider_id)) {
        $carrierLabel = __('order_flow.confirm_partner');
        $missing = array_values(array_filter($missing, fn (string $field) => $field !== $carrierLabel));
        $reasons[] = __('order_flow.bulk_send_reason_no_carrier');
    }

    if (! empty($missing)) {
        $reasons[] = __('order_flow.bulk_send_reason_missing', ['fields' => implode('طŒ ', $missing)]);
    }

    $reasons = array_values(array_unique($reasons));

    return [
        'id' => $order->id,
        'number' => $order->number,
        'carrierKey' => $carrierKey,
        'carrierName' => $carrierName,
        'ready' => empty($reasons),
        'reasons' => $reasons,
    ];
};

$confirmBulkSend = function (): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $this->closeBulkSendModal();

    if (empty($this->selectedOrders)) {
        $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('merchant.no_orders_selected')]);
        return;
    }

    $membership = $this->getCurrentMembership();
    if (! $membership) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $orders = Order::with(['status', 'customer', 'shippingProvider', 'deliveryRider', 'items'])
        ->where('store_id', currentStoreId())
        ->whereIn('id', $this->selectedOrders)
        ->get();

    $gateway = app(\App\Domains\Shipping\Services\OrderShippingGateway::class);

    $perCarrier = [];
    $sent = 0;
    $skipped = [];

    foreach ($orders as $order) {
        $state = $this->resolveBulkOrderState($order);

        if (! $state['ready']) {
            $skipped[] = $order->number . ' (' . implode('ط› ', $state['reasons']) . ')';
            continue;
        }

        try {
            $gateway->send(
                order: $order,
                providerId: $order->shipping_provider_id ?: null,
                changedBy: $membership,
            );

            if (! isset($perCarrier[$state['carrierKey']])) {
                $perCarrier[$state['carrierKey']] = ['name' => $state['carrierName'], 'count' => 0];
            }
            $perCarrier[$state['carrierKey']]['count']++;
            $sent++;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("confirmedBulkSend failed for order [{$order->number}]: " . $e->getMessage());
            $skipped[] = $order->number . ' (' . $e->getMessage() . ')';
        }
    }

    $this->clearSelection();
    $this->loadOrders();

    $summaryLines = [];
    foreach ($perCarrier as $carrier) {
        $summaryLines[] = __('order_flow.bulk_send_summary_line', ['carrier' => $carrier['name'], 'count' => $carrier['count']]);
    }

    if (! empty($skipped)) {
        $summaryLines[] = __('order_flow.bulk_send_skipped', ['count' => count($skipped)]);
        foreach ($skipped as $line) {
            $summaryLines[] = $line;
        }
    }

    if ($sent > 0 || ! empty($skipped)) {
        $this->dispatch('swal:toast', [
            'icon' => ! empty($skipped) ? 'warning' : 'success',
            'title' => implode(' • ', $summaryLines),
        ]);
    }
};

$bulkDelete = function (): void {
    if (! canStore(StorePermissionEnum::ORDER_DELETE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }
    if (empty($this->selectedOrders)) {
        $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('merchant.no_orders_selected')]);
        return;
    }
    Order::where('store_id', currentStoreId())->whereIn('id', $this->selectedOrders)->delete();
    $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('merchant.orders_deleted')]);
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
    if (! canStore(StorePermissionEnum::ORDER_DELETE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }
    Order::where('store_id', currentStoreId())->withTrashed()->findOrFail($orderId)->restore();
    $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('merchant.orders_restored')]);
    $this->loadOrders();
};

$restoreAll = function (): void {
    if (! canStore(StorePermissionEnum::ORDER_DELETE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }
    Order::where('store_id', currentStoreId())->onlyTrashed()->restore();
    $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('merchant.orders_restored')]);
    $this->loadOrders();
};

$forceDeleteAll = function (): void {
    if (! canStore(StorePermissionEnum::ORDER_DELETE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }
    Order::where('store_id', currentStoreId())->onlyTrashed()->forceDelete();
    $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('merchant.empty_trash')]);
    $this->loadOrders();
};

// --- Cities loader for filter ---
$loadFilterCities = function (string $stateId): void {
    $this->allCities = $stateId ? \App\Models\Locations\City::where('state_id', $stateId)->orderBy('name')->get()->toArray() : [];
};

// --- Stopdesk points loader for filter (cascades from shipping_provider) ---
$loadFilterStopdeskPoints = function (?string $providerId): void {
    $this->allStopdeskPoints = $providerId ? \App\Domains\Shipping\Models\StopdeskPoint::where('shipping_provider_id', $providerId)->orderBy('name')->get()->toArray() : [];
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

    $draft = array_values(array_intersect($this->draftColumns, $validKeys));
    $this->visibleColumns = $draft;
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
    $col = $this->orderColumn($column);

    // Required columns are always visible; role-gated columns are toggleable
    // only by members allowed to see them.
    if (! $col || ($col['required'] ?? false) || ! $this->columnAllowedForUser($col)) {
        return;
    }

    if (in_array($column, $this->draftColumns, true)) {
        $this->draftColumns = array_values(array_diff($this->draftColumns, [$column]));
    } else {
        $this->draftColumns[] = $column;
    }
};

$moveDraftColumn = function (string $column, string $direction): void {
    $position = array_search($column, $this->draftColumns, true);
    if ($position === false) {
        return;
    }

    $target = $direction === 'up' ? $position - 1 : $position + 1;
    if ($target < 0 || $target >= count($this->draftColumns)) {
        return;
    }

    [$this->draftColumns[$position], $this->draftColumns[$target]] = [$this->draftColumns[$target], $this->draftColumns[$position]];
};

$reorderDraftColumns = function (array $keys): void {
    $validKeys = collect($this->orderColumns())->pluck('key')->all();
    $this->draftColumns = array_values(
        array_intersect(
            collect($keys)->unique()->values()->all(),
            $validKeys,
        ),
    );
};

$resetColumns = function (): void {
    $visible = collect($this->orderColumns())
        ->filter(fn($col) => $this->columnAllowedForUser($col))
        ->pluck('key')
        ->all();

    $defaults = ['customer', 'phone', 'verification', 'status', 'shipping_provider', 'delivery_type', 'wilaya', 'city', 'stopdesk_point', 'address', 'products', 'quantity', 'price', 'total', 'confirmation_attempts', 'last_contact'];

    $this->draftColumns = array_values(array_intersect($defaults, $visible));
    $this->draftStyle = 'default';
};

// ——— Status Transition ———

// Authorization (Phase P1): gate each transition by the fine-grained permission
// for the target status (see App\Support\StoreOrderPermissions::forStatus).
// Confirmation â†’ order.confirm, cancellation â†’ order.cancel, everything else
// (ship / deliver / prepare / return-followup…) â†’ order.manage. This closes the
// gap where $transitionOrder was only checked against the state machine.
$transitionOrder = function (string $orderId, string $statusKey): void {
    $order = Order::where('store_id', currentStoreId())->findOrFail($orderId);
    $membership = $this->getCurrentMembership();

    if (! canStore(\App\Support\StoreOrderPermissions::forStatus($statusKey))) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $service = app(OrderService::class);
    $statusKey_translation = status_label('order', $statusKey) ?: __('status.' . $statusKey);
    if (!$service->canTransition($order, $statusKey)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('status_transition.invalid_transition', ['to' => $statusKey_translation ?? '—'])]);
        return;
    }

    $service->transition($order, $statusKey, null, $membership);

    $this->page = 1;
    $this->loadOrders();

    $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('status_transition.order_status_updated', ['new_status' => $statusKey_translation ?? '—'])]);
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

    $order = Order::where('store_id', currentStoreId())
        ->with([
            'customer', 'status', 'items.product', 'items.variant', 'assignedMembership.user',
            'createdByMembership.user', 'state', 'city', 'latestTracking.shippingProvider',
            'confirmedByHistory.status', 'confirmedByHistory.changedBy.user', 'stopdeskPoint.city',
        ])
        ->find($orderId);

    if (! $order) {
        $this->canViewOrderDetailsEvents = false;
        $this->detailsEvents = [];

        return;
    }

    // The details drawer resolves the order from the current page payload.
    // When it's absent (e.g. a duplicate order opened from the form/reference),
    // hydrate a single order with the same mapping so the drawer renders.
    if (! collect($this->orders['data'] ?? [])->contains('id', $orderId)) {
        $service = app(OrderService::class);

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
                'tracking_status' => $order->latestTracking->tracking_status,
                'carrier_status' => $order->latestTracking->carrier_status,
                'carrier_label' => $order->latestTracking->carrier_label,
                'shipped_at' => $order->latestTracking->shipped_at?->format('Y-m-d H:i'),
                'delivered_at' => $order->latestTracking->delivered_at?->format('Y-m-d H:i'),
                'shipping_provider' => $order->latestTracking->shippingProvider?->name,
            ]
            : null;

        // Prepend so firstWhere in the drawer finds it even on an empty page.
        $this->orders['data'] = array_merge([$arr], $this->orders['data'] ?? []);
    }
};

$closeOrderDetails = function (): void {
    $this->detailsOrderId = null;
};

// ——— Order event log (P29.4) — row dropdown preview + "show more" full popup. --—
$loadOrderEvents = function (string $orderId): void {
    $order = \App\Models\Orders\Order::where('store_id', currentStoreId())->find($orderId);
    $membership = $this->getCurrentMembership();

    if (! $order || ! $membership
        || ! \App\Support\StoreOrderPermissions::canViewOrderEventLog($order, $membership)) {
        $this->eventsPreviewOrderId = null;
        $this->eventsPreview = [];
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    if ($this->eventsPreviewOrderId !== $orderId) {
        $this->eventsPreviewOrderId = $orderId;
        $this->eventsPreview = \App\Models\Orders\OrderEvent::where('store_id', currentStoreId())
            ->where('order_id', $orderId)
            ->with('actor.user')
            ->orderByDesc('occurred_at')
            ->limit(15)
            ->get()
            ->toArray();
    }
};

$openOrderEventsModal = function (string $orderId): void {
    $order = \App\Models\Orders\Order::where('store_id', currentStoreId())->find($orderId);
    $membership = $this->getCurrentMembership();

    if (! $order || ! $membership
        || ! \App\Support\StoreOrderPermissions::canViewOrderEventLog($order, $membership)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $this->eventsFullOrderId = $orderId;
    $this->eventsFullLabel = $order->number;
    $this->eventsFull = ($this->eventsPreviewOrderId === $orderId)
        ? $this->eventsPreview
        : \App\Models\Orders\OrderEvent::where('store_id', currentStoreId())
            ->where('order_id', $orderId)
            ->with('actor.user')
            ->orderByDesc('occurred_at')
            ->get()
            ->toArray();
};

$closeOrderEventsModal = function (): void {
    $this->eventsFullOrderId = null;
    $this->eventsFull = [];
    $this->eventsFullLabel = null;
};

// ——— Confirmation drawer (P26) ———

$openConfirmModal = function (string $orderId): void {
    if (! canStore(StorePermissionEnum::ORDER_CONFIRM->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $order = Order::where('store_id', currentStoreId())
        ->with(['status', 'shippingProvider', 'deliveryRider', 'customer'])
        ->find($orderId);

    if (! $order) {
        return;
    }

    $this->confirmOrderId = $orderId;
    $this->confirmProviderId = $order->shipping_provider_id ?? '';
    $this->confirmContacted = false;
    $this->confirmSummary = [
        'number' => $order->number,
        'total' => currency($order->total_amount),
        'customer' => $order->customer->name ?? $order->phone ?? '—',
        'status' => status_label('order', $order->status?->key),
        'partner' => $order->shippingProvider?->name ?? ($order->deliveryRider?->name ?? '—'),
    ];

    $this->refreshDuplicateWarnings($order);

    $this->showConfirmModal = true;
};

$closeConfirmModal = function (): void {
    $this->showConfirmModal = false;
    $this->confirmOrderId = null;
    $this->duplicateWarnings = [];
};

$bumpConfirmationAttempt = function (): void {
    if (! canStore(StorePermissionEnum::ORDER_CONFIRM->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $order = Order::where('store_id', currentStoreId())->find($this->confirmOrderId);

    if (! $order) {
        return;
    }

    $attempts = ($order->confirmation_attempts ?? 0) + 1;

    $payload = ['confirmation_attempts' => $attempts];

    if ($this->confirmContacted) {
        $payload['last_contact_at'] = now();
    }

    $order->update($payload);

    $actor = $this->getCurrentMembership();

    app(\App\Domains\Order\Services\OrderAuditService::class)->contactAttempt(
        $order,
        $this->confirmContacted ? 'contacted' : 'not_reached',
        $actor,
    );

    $this->confirmSummary['status'] = status_label('order', $order->status?->key);
};

$submitConfirmOnly = function (): void {
    if (! canStore(StorePermissionEnum::ORDER_CONFIRM->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $order = Order::where('store_id', currentStoreId())->find($this->confirmOrderId);

    if (! $order) {
        return;
    }

    if ($order->status?->key !== 'pending') {
        $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('order_flow.confirmed_only')]);
        $this->showConfirmModal = false;
        return;
    }

    $missing = $this->collectMissingFields($order, false);

    if ($missing !== []) {
        $this->dispatch('swal:toast', [
            'icon' => 'warning',
            'title' => __('order_flow.confirm_missing_fields', ['fields' => implode(', ', $missing)]),
        ]);
        return;
    }

    $membership = $this->getCurrentMembership();

    app(OrderService::class)->confirm($order, $membership);

    if ($membership && $this->confirmContacted) {
        $order->update(['last_contact_at' => now()]);
    }

    $this->showConfirmModal = false;
    $this->loadOrders();
    $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('order_flow.confirmed_only')]);
};

$submitConfirmAndSend = function (): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $order = Order::where('store_id', currentStoreId())->find($this->confirmOrderId);

    if (! $order) {
        return;
    }

    $missing = $this->collectMissingFields($order, true);

    if ($missing !== []) {
        $this->dispatch('swal:toast', [
            'icon' => 'warning',
            'title' => __('order_flow.confirm_missing_fields', ['fields' => implode(', ', $missing)]),
        ]);
        return;
    }

    $providerId = ! empty($this->confirmProviderId)
        ? $this->confirmProviderId
        : ($order->shipping_provider_id ?? null);

    $isRider = ! empty($order->delivery_rider_id) && empty($providerId);

    if (! $providerId && ! $isRider) {
        $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('order_flow.confirm_requires_partner')]);
        return;
    }

    $membership = $this->getCurrentMembership();

    if (! $membership) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    try {
        $result = app(\App\Domains\Shipping\Services\OrderShippingGateway::class)->send(
            order: $order,
            providerId: $providerId ?: null,
            changedBy: $membership,
            confirmFirst: true,
        );

        $this->showConfirmModal = false;
        $this->loadOrders();
        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => __('order_flow.confirmed_and_sent'),
        ]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::warning("confirm+send failed for order [{$order->number}]: " . $e->getMessage());
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('order_flow.send_failed')]);
    }
};

// 29.2 — Direct "hand to carrier" for an ALREADY confirmed order (no drawer).
// Sends via the same gateway with confirmFirst: false so it NEVER auto-confirms.
// Readiness is validated before any transition; incomplete orders get a
// field-by-field warning and keep their current status.
$sendConfirmedOrder = function (string $orderId): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $order = Order::with('customer')
        ->where('store_id', currentStoreId())
        ->find($orderId);

    if (! $order) {
        return;
    }

    if (! in_array($order->status?->key, ['confirmed', 'preparing'], true)) {
        $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('order_flow.send_requires_confirmation')]);
        return;
    }

    $missing = $this->collectMissingFields($order);

    if (! empty($missing)) {
        $this->dispatch('swal:toast', [
            'icon' => 'warning',
            'title' => __('order_flow.send_missing_fields', ['fields' => implode('طŒ ', $missing)]),
        ]);
        return;
    }

    $membership = $this->getCurrentMembership();

    if (! $membership) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    try {
        app(\App\Domains\Shipping\Services\OrderShippingGateway::class)->send(
            order: $order,
            providerId: $order->shipping_provider_id ?: null,
            changedBy: $membership,
        );

        $this->loadOrders();
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('merchant.orders_sent')]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::warning("sendConfirmedOrder failed for order [{$order->number}]: " . $e->getMessage());
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('order_flow.send_failed')]);
    }
};

$refreshDuplicateWarnings = function (?Order $order = null): void {
    $order ??= $this->confirmOrderId
        ? Order::where('store_id', currentStoreId())->with('items.variant')->find($this->confirmOrderId)
        : null;

    if (! $order) {
        $this->duplicateWarnings = [];
        return;
    }

    $service = app(\App\Domains\Order\Services\OrderDuplicateService::class);
    $this->duplicateWarnings = $service->findSimilar($order);
};

// Lazy duplicate scan (P29.6): runs findSimilar() + sibling counts only for the clicked order.
// Classifies the row: duplicate (same phone + shared product in window), probable (same phone,
// different product in window) or repeat (a prior order of this phone reached the carrier).
$openDuplicateScan = function (string $orderId): void {
    $order = Order::where('store_id', currentStoreId())->with(['items.variant', 'status'])->find($orderId);

    if (! $order) {
        return;
    }

    $service = app(\App\Domains\Order\Services\OrderDuplicateService::class);

    $counts = $service->countsBySiblings([$orderId], currentStoreId());
    $samePhone = (int) ($counts[$orderId]['same_phone'] ?? 0);
    $sameProduct = (int) ($counts[$orderId]['same_product'] ?? 0);

    $selfReachedCarrier = $order->shipping_provider_id
        || $order->delivery_rider_id
        || in_array($order->status?->key, \App\Domains\Order\Support\OrderWorkflow::carrier(), true);

    $priorCarrier = $service->countsPriorCarrierOrders([(string) $order->customer_id]);
    $repeatCount = max(0, (int) ($priorCarrier[(string) $order->customer_id] ?? 0) - ($selfReachedCarrier ? 1 : 0));

    $this->duplicateScanPhoneCount = $samePhone;
    $this->duplicateScanRepeatCount = $repeatCount;
    $this->duplicateScanLevel = $sameProduct >= 1
        ? 'duplicate'
        : ($samePhone >= 1
            ? 'probable'
            : ($repeatCount >= 1
                ? 'repeat'
                : 'none'));
    $this->duplicateScanNumber = $order->number;
    $this->duplicateScanResults = $service->findSimilar($order);
    $this->showDuplicateScanModal = true;
};

$closeDuplicateScanModal = function (): void {
    $this->showDuplicateScanModal = false;
    $this->duplicateScanNumber = null;
    $this->duplicateScanResults = [];
    $this->duplicateScanPhoneCount = 0;
    $this->duplicateScanLevel = 'none';
    $this->duplicateScanRepeatCount = 0;
};

// Duplicate warnings inside the create/edit form (P28 extended):
// builds an array candidate from the unsaved form and reuses the same service.
$refreshFormDuplicateWarnings = function (): void {
    $items = collect($this->form['items'] ?? []);

    $variantIds = $items->pluck('product_variant_id')->filter()->values()->all();
    $productIds = $items->pluck('product_id')->filter()->values()->all();

    if (empty($variantIds) && empty($productIds) && empty($this->form['customer_phone'] ?? '')) {
        $this->formDuplicateWarnings = [];
        return;
    }

    $candidate = [
        'store_id' => currentStoreId(),
        'exclude_id' => $this->editingOrderId ?? null,
        'customer_phone' => $this->form['customer_phone'] ?? null,
        'items' => $items
            ->map(fn($item) => [
                'product_variant_id' => $item['product_variant_id'] ?? null,
                'product_id' => $item['product_id'] ?? null,
                'quantity' => $item['quantity'] ?? 1,
            ])
            ->values()
            ->all(),
    ];

    $service = app(\App\Domains\Order\Services\OrderDuplicateService::class);
    $this->formDuplicateWarnings = $service->findSimilar($candidate);
};

$markOrderDuplicate = function (string $orderId): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $order = Order::where('store_id', currentStoreId())->findOrFail($orderId);

    $status = \App\Models\Status::system()
        ->forType('order')
        ->where('key', 'duplicate')
        ->first();

    if ($status && app(OrderService::class)->canTransition($order, 'duplicate')) {
        $membership = $this->getCurrentMembership();
        app(OrderService::class)->transition($order, 'duplicate', 'Marked as duplicate', $membership);
    }

    $this->closeConfirmModal();
    $this->loadOrders();
    $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('order_flow.duplicate_marked')]);
};

// ——— Bulk status change (P29) ———

$openBulkStatusModal = function (): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    if (empty($this->selectedOrders)) {
        $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('merchant.no_orders_selected')]);
        return;
    }

    $excluded = ['cancelled', 'canceled', 'confirmed'];
    $service = app(OrderService::class);

    $allowedTargets = collect($this->allStatuses)
        ->pluck('key')
        ->filter(fn($key) => ! in_array($key, $excluded, true))
        ->values()
        ->all();

    $this->bulkStatusTarget = '';
    $this->bulkStatusReason = '';
    $this->showBulkStatusModal = true;
};

$closeBulkStatusModal = function (): void {
    $this->showBulkStatusModal = false;
    $this->bulkStatusTarget = '';
    $this->bulkStatusReason = '';
};

$submitBulkStatus = function (): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $statusKey = $this->bulkStatusTarget;

    if (empty($statusKey)) {
        $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('order_flow.bulk_status_no_allowed')]);
        return;
    }

    $excluded = ['cancelled', 'canceled', 'confirmed'];

    if (in_array($statusKey, $excluded, true)) {
        $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('order_flow.bulk_status_no_allowed')]);
        return;
    }

    $service = app(OrderService::class);
    $membership = $this->getCurrentMembership();
    $done = 0;
    $skipped = 0;

    Order::where('store_id', currentStoreId())
        ->whereIn('id', $this->selectedOrders)
        ->each(function ($order) use ($service, $statusKey, $membership, &$done, &$skipped) {
            try {
                if (! $service->canTransition($order, $statusKey)) {
                    $skipped++;
                    return;
                }

                $service->transition($order, $statusKey, $this->bulkStatusReason, $membership);
                $done++;
            } catch (\Exception $e) {
                $skipped++;
            }
        });

    $this->clearSelection();
    $this->showBulkStatusModal = false;
    $this->loadOrders();

    $this->dispatch('swal:toast', [
        'icon' => $skipped > 0 ? 'warning' : 'success',
        'title' => __('order_flow.bulk_status_done', ['done' => $done, 'skipped' => $skipped]),
    ]);
};

// ——— Reassign ———
$openReassignModal = function (string $orderId): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }
    $this->reassignOrderId = $orderId;
    $this->reassignMembershipId = '';
    $this->showReassignModal = true;
};

$submitReassign = function (): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    if (empty($this->reassignMembershipId)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.select_agent')]);
        return;
    }

    $order = Order::where('store_id', currentStoreId())->findOrFail($this->reassignOrderId);
    $targetMembership = StoreMembership::where('store_id', currentStoreId())->findOrFail($this->reassignMembershipId);
    $byMembership = $this->getCurrentMembership();

    if (!$byMembership) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $service = app(OrderAssignmentService::class);
    $service->reassign($order, $targetMembership, $byMembership);

    $this->showReassignModal = false;
    $this->loadOrders();

    $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('merchant.order_reassigned')]);
};

// ——— Delete ———
$deleteOrder = function (string $orderId): void {
    if (! canStore(StorePermissionEnum::ORDER_DELETE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $order = Order::where('store_id', currentStoreId())->findOrFail($orderId);
    $order->delete();

    $this->loadOrders();
    $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('merchant.order_deleted')]);
};

$refreshOrders = function () {
    $this->loadOrders();
};

// ——— Order form modal (Phase 9 @include partial — logic lives here on the parent instance) ———

$syncFormSelectedItems = function (): void {
    $this->formSelectedItems = collect($this->form['items'])->pluck('quantity', 'product_variant_id')->toArray();
    $this->dispatch('selected-items-updated', items: $this->formSelectedItems);
};

// 30.3: weight_kg is auto-calculated from variant weights أ— quantities on every item
// mutation. It stays a manual field otherwise, so a typed override is only overwritten
// when the items actually change — never on a mere price edit.
$recalcFormWeight = function (): void {
    $weight = collect($this->form['items'] ?? [])->sum(
        fn($i) => (float) ($i['weight'] ?? 0) * (int) ($i['quantity'] ?? 1)
    );
    $this->form['weight_kg'] = $weight > 0 ? round($weight, 3) : '';
};

$loadCities = function (string $stateId): void {
    if (empty($stateId)) {
        $this->allCities = [];
        $this->form['city_id'] = '';
        $this->rebuildFormOffices();
        return;
    }
    $this->allCities = City::where('state_id', $stateId)->orderBy('name')->get()->toArray();
    $this->form['city_id'] = '';
    $this->rebuildFormOffices();
};

$rebuildFormOffices = function (): void {
    $wasSelected = (string) ($this->form['stopdesk_point_id'] ?? '');

    if (empty($this->form['shipping_provider_id'])) {
        $this->formOffices = [];
        $this->form['stopdesk_point_id'] = '';
        return;
    }

    $query = \App\Domains\Shipping\Models\StopdeskPoint::query()->where('store_id', currentStoreId())->where('shipping_provider_id', $this->form['shipping_provider_id'])->where('is_active', true)->with('city:id,name');

    $stateId = $this->form['state_id'] ?? null;
    if (!empty($stateId)) {
        $query->where(function ($q) use ($stateId) {
            $q->where('state_id', $stateId)->orWhereNull('state_id');
        });
    }

    $cityId = $this->form['city_id'] ?? null;
    // Chain: company â†’ type â†’ wilaya â†’ city â†’ office. When a commune is chosen
    // the office list is scoped to it (regional null-city offices still show).
    if (!empty($cityId)) {
        $query->where(function ($q) use ($cityId) {
            $q->where('city_id', $cityId)->orWhereNull('city_id');
        });
    }

    $offices = $query
        ->orderBy('name')
        ->get()
        ->sortByDesc(fn($office) => !empty($cityId) && $office->city_id === $cityId ? 1 : 0)
        ->values();

    $this->formOffices = $offices
        ->map(function ($office) use ($cityId) {
            $hint = trim(($office->city?->name ?? '') . ($office->address ? ' — ' . $office->address : ''), ' —');

            return [
                'value' => $office->id,
                'label' => $office->name,
                'hint' => $hint !== '' ? $hint : null,
            ];
        })
        ->all();

    $cityOffices = empty($cityId) ? collect() : $offices->filter(fn($office) => $office->city_id === $cityId);

    // Chain: company â†’ type â†’ wilaya â†’ city â†’ office. When the chosen
    // municipality has a single office, auto-select it.
    if (($this->form['delivery_type'] ?? null) === 'stopdesk' && $cityOffices->count() === 1) {
        $this->form['stopdesk_point_id'] = (string) $cityOffices->first()->id;
    } elseif (!collect($this->formOffices)->contains(fn($o) => $o['value'] === ($this->form['stopdesk_point_id'] ?? null))) {
        // 30.2: never revert a previously valid office silently. When the
        // selected destination (wilaya/commune) no longer offers the chosen
        // office, clear it AND surface a visible note so the user knows why.
        if ($wasSelected !== '' && $this->form['stopdesk_point_id'] === $wasSelected) {
            $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('order_flow.office_reset_for_destination')]);
        }
        $this->form['stopdesk_point_id'] = '';
    }
};

// Carrier-first: fetch + reconcile offices for the chosen provider, then
// rebuild the picker options. Also updates form.shipping_provider_id when
// invoked from the wire:change event. Keeps an existing office selection
// when $preserveOffice is true (edit modal).
$loadFormOffices = function (?string $providerId = null, bool $preserveOffice = false): void {
    if ($providerId !== null) {
        $this->form['shipping_provider_id'] = $providerId;
    }

    // Home delivery has no office concept: picking a carrier must not trigger
    // an office sync/rebuild. Drop any stale selection and options only.
    if (($this->form['delivery_type'] ?? null) !== 'stopdesk') {
        $this->form['stopdesk_point_id'] = '';
        $this->formOffices = [];

        return;
    }

    if (!$preserveOffice) {
        $this->form['stopdesk_point_id'] = '';
    }

    if (empty($this->form['shipping_provider_id'])) {
        $this->formOffices = [];
        return;
    }

    $storeId = currentStoreId();
    $provider = \App\Domains\Shipping\Models\ShippingProvider::where('store_id', $storeId)->where('is_active', true)->find($this->form['shipping_provider_id']);

    if (!$provider) {
        $this->formOffices = [];
        return;
    }

    $this->loadingOffices = true;

    try {
        $state = !empty($this->form['state_id']) ? \App\Models\Locations\State::find($this->form['state_id']) : null;
        $city = !empty($this->form['city_id']) ? \App\Models\Locations\City::find($this->form['city_id']) : null;

        app(\App\Domains\Shipping\Services\StopdeskOfficeSync::class)->sync($provider, $state, $city);

        $this->rebuildFormOffices();
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::warning("office sync failed for provider [{$provider->id}]: " . $e->getMessage());
        $this->rebuildFormOffices();
    } finally {
        $this->loadingOffices = false;
    }
};

// Delivery type switch: rebuild office options (which auto-selects the single
// office of the chosen municipality on stopdesk) without requiring a provider change.
$changeDeliveryType = function (string $type): void {
    $this->form['delivery_type'] = $type;

    if ($type === 'stopdesk') {
        // Full sync path when a carrier is already chosen so the office list is
        // present and scoped the moment the stopdesk fields appear.
        if (!empty($this->form['shipping_provider_id'])) {
            $this->loadFormOffices(preserveOffice: true);
        } else {
            $this->rebuildFormOffices();
        }
    } else {
        $this->form['stopdesk_point_id'] = '';
        $this->formOffices = [];
    }
};

$refreshFormOffices = function (): void {
    if (empty($this->form['shipping_provider_id'])) {
        return;
    }

    $storeId = currentStoreId();
    $provider = \App\Domains\Shipping\Models\ShippingProvider::where('store_id', $storeId)->where('is_active', true)->find($this->form['shipping_provider_id']);

    if (!$provider) {
        return;
    }

    $this->loadingOffices = true;

    try {
        $state = !empty($this->form['state_id']) ? \App\Models\Locations\State::find($this->form['state_id']) : null;
        $city = !empty($this->form['city_id']) ? \App\Models\Locations\City::find($this->form['city_id']) : null;

        app(\App\Domains\Shipping\Services\StopdeskOfficeSync::class)->sync($provider, $state, $city, refresh: true);

        $this->rebuildFormOffices();
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('merchant_panel.offices_updated')]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::warning("office refresh failed for provider [{$provider->id}]: " . $e->getMessage());
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.office_sync_failed')]);
    } finally {
        $this->loadingOffices = false;
    }
};

// ——— Delivery quick-edit modal (30.2): full cascade reusing $this->form's
//      delivery fields + allStates/allCities/formOffices + the cascade helpers.

$openDeliveryModal = function (string $orderId): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $order = Order::with('customer')->where('store_id', currentStoreId())->findOrFail($orderId);

    $shippedSortOrder = \App\Models\Status::where('type', 'order')->where('key', 'shipped')->value('sort_order');
    if ($order->status && $shippedSortOrder !== null && $order->status->sort_order >= $shippedSortOrder) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.cannot_edit_shipped')]);
        return;
    }

    $this->deliveryOrderId = $order->id;
    $this->form['delivery_type'] = $order->delivery_type;
    $this->form['shipping_provider_id'] = $order->shipping_provider_id ?? '';
    $this->form['state_id'] = $order->state_id ?? '';
    $this->form['city_id'] = $order->city_id ?? '';
    $this->form['stopdesk_point_id'] = $order->stopdesk_point_id ?? '';
    $this->allCities = !empty($this->form['state_id'])
        ? City::where('state_id', $this->form['state_id'])->orderBy('name')->get()->toArray()
        : [];

    $this->formOffices = [];
    $this->loadFormOffices($this->form['shipping_provider_id'], preserveOffice: true);

    $this->showDeliveryModal = true;
    $this->deliverySaving = false;
};

$closeDeliveryModal = function (): void {
    $this->showDeliveryModal = false;
    $this->deliveryOrderId = null;
    $this->deliverySaving = false;
};

$saveDeliveryModal = function (): void {
    if (! $this->deliveryOrderId) {
        return;
    }

    $order = Order::with('store')->where('store_id', currentStoreId())->findOrFail($this->deliveryOrderId);

    $shippedSortOrder = \App\Models\Status::where('type', 'order')->where('key', 'shipped')->value('sort_order');
    if ($order->status && $shippedSortOrder !== null && $order->status->sort_order >= $shippedSortOrder) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.cannot_edit_shipped')]);
        return;
    }

    $this->deliverySaving = true;

    try {
        $data = $this->form;
        $validator = Validator::make($data, [
            'delivery_type' => ['required', 'in:home,stopdesk'],
            'shipping_provider_id' => ['nullable', 'string', 'exists:shipping_providers,id'],
            'state_id' => ['nullable', 'string', 'exists:states,id'],
            'city_id' => ['nullable', 'string', 'exists:cities,id'],
            'stopdesk_point_id' => ['nullable', 'string', 'exists:stopdesk_points,id'],
        ]);

        if ($validator->fails()) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => $validator->errors()->first()]);
            return;
        }

        if (!empty($data['city_id']) && empty($data['state_id'])) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.city_without_state')]);
            return;
        }

        if (filled($data['city_id'])) {
            $city = City::find($data['city_id']);
            if ($city && $city->state_id !== ($data['state_id'] ?? null)) {
                $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.invalid_city_for_state')]);
                return;
            }
        }

        if (filled($data['stopdesk_point_id'])) {
            $stopdesk = \App\Domains\Shipping\Models\StopdeskPoint::where('store_id', currentStoreId())->find($data['stopdesk_point_id']);
            if (!$stopdesk) {
                $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.invalid_office')]);
                return;
            }

            if ($data['delivery_type'] !== 'stopdesk') {
                $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.office_home_invalid')]);
                return;
            }

            if (filled($data['shipping_provider_id']) && $stopdesk->shipping_provider_id !== $data['shipping_provider_id']) {
                $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.office_provider_mismatch')]);
                return;
            }
        }

        // Write-collect before applying so we can compare for the audit event.
        $prev = [
            'delivery_type' => $order->delivery_type,
            'shipping_provider_id' => $order->shipping_provider_id,
            'state_id' => $order->state_id,
            'city_id' => $order->city_id,
            'stopdesk_point_id' => $order->stopdesk_point_id,
        ];

        $order->update([
            'delivery_type' => $data['delivery_type'],
            'shipping_provider_id' => filled($data['shipping_provider_id']) ? $data['shipping_provider_id'] : null,
            'state_id' => filled($data['state_id']) ? $data['state_id'] : null,
            'city_id' => filled($data['city_id']) ? $data['city_id'] : null,
            'stopdesk_point_id' => $data['delivery_type'] === 'stopdesk' && filled($data['stopdesk_point_id']) ? $data['stopdesk_point_id'] : null,
        ]);

        $this->recalculateOrderShipping($order->fresh());

        $dirty = array_keys(array_diff_assoc($prev, [
            'delivery_type' => $order->delivery_type,
            'shipping_provider_id' => $order->shipping_provider_id,
            'state_id' => $order->state_id,
            'city_id' => $order->city_id,
            'stopdesk_point_id' => $order->stopdesk_point_id,
        ]));

        activity(config('activitylog.default_log_name', 'default'))
            ->event('order_delivery_updated')
            ->withProperties([
                'field' => 'order.delivery',
                'record_id' => $order->id,
                'before' => $prev,
                'after' => $order->only(['delivery_type', 'shipping_provider_id', 'state_id', 'city_id', 'stopdesk_point_id']),
                'changed' => $dirty,
            ])
            ->on($order)
            ->log('Updated order delivery');

        $this->showDeliveryModal = false;
        $this->deliveryOrderId = null;
        $this->deliverySaving = false;

        $this->loadOrders();
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('merchant_panel.delivery_updated')]);
    } finally {
        $this->deliverySaving = false;
    }
};

// ——— Inline phone edit (customer phone + order secondary, stacked) ———

$startOrderPhoneEdit = function (string $orderId): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $order = Order::where('store_id', currentStoreId())->with('customer')->find($orderId);

    if (!$order) {
        return;
    }

    $this->phoneEditPhone = (string) ($order->customer?->phone ?? '');
    $this->phoneEditSecondary = (string) ($order->phone_secondary ?? '');

    $this->startEdit('order.phone', $orderId, $this->phoneEditPhone);
};

$cancelOrderPhoneEdit = function (): void {
    $this->phoneEditPhone = '';
    $this->phoneEditSecondary = '';
    $this->cancelEdit();
};

$saveOrderPhone = function (): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $this->editingError = null;

    \Illuminate\Support\Facades\Validator::make(
        [
            'phone' => $this->phoneEditPhone,
            'phone_secondary' => $this->phoneEditSecondary,
        ],
        [
            'phone' => 'required|string|max:20|regex:/^0[5-7]\d{8}$/',
            'phone_secondary' => 'nullable|string|max:20|regex:/^0[5-7]\d{8}$/',
        ],
    )->validate();

    $order = Order::where('store_id', currentStoreId())->findOrFail($this->editingId);

    $customer = $order->customer;
    if ($customer) {
        $customer->update(['phone' => $this->phoneEditPhone]);
    }

    $order->update([
        'phone_secondary' => $this->phoneEditSecondary !== '' ? $this->phoneEditSecondary : null,
    ]);

    $this->writeInlineAudit(
        [
            'field' => 'order.phone',
            'label' => 'order phone',
            'audit_event' => 'order_phone_updated',
            'subject' => fn(mixed $id) => Order::where('store_id', currentStoreId())->with('customer')->find($id),
        ],
        ['phone' => $this->phoneEditPhone, 'phone_secondary' => $this->phoneEditSecondary],
        'applied',
    );

    $orderId = $this->editingId;

    $this->resetEditingState();
    $this->phoneEditPhone = '';
    $this->phoneEditSecondary = '';

    $this->refreshSingleOrder($orderId);
    $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('merchant_panel.phone_updated')]);
};

// ——— Inline customer name edit (30.7) ———

$startOrderNameEdit = function (string $orderId): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $order = Order::where('store_id', currentStoreId())->with('customer')->find($orderId);

    if (!$order) {
        return;
    }

    $this->nameEditName = (string) ($order->customer?->name ?? '');

    $this->startEdit('order.customer_name', $orderId, $this->nameEditName);
};

$cancelOrderNameEdit = function (): void {
    $this->nameEditName = '';
    $this->cancelEdit();
};

$saveOrderName = function (): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $this->editingError = null;

    \Illuminate\Support\Facades\Validator::make(
        [
            'name' => $this->nameEditName,
        ],
        [
            'name' => 'required|string|max:255',
        ],
    )->validate();

    $order = Order::where('store_id', currentStoreId())->findOrFail($this->editingId);

    $customer = $order->customer;
    if ($customer) {
        $customer->update(['name' => $this->nameEditName]);
    }

    $this->writeInlineAudit(
        [
            'field' => 'order.customer_name',
            'label' => 'order customer name',
            'audit_event' => 'order_customer_name_updated',
            'subject' => fn(mixed $id) => Order::where('store_id', currentStoreId())->with('customer')->find($id),
        ],
        ['name' => $this->nameEditName],
        'applied',
    );
    $orderId = $this->editingId;

    $this->resetEditingState();
    $this->nameEditName = '';

    $this->refreshSingleOrder($orderId);
    $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('merchant_panel.name_updated')]);
};

// ——— Inline edits (wilaya / commune / shipping cost override) ———

$recalculateOrderShipping = function (Order $order): void {
    if (!$order->store || $order->delivery_type !== 'home') {
        return;
    }

    $items = collect($order->items);
    $productIds = $items->pluck('product_id')->filter()->values()->toArray();
    $subtotal = (float) $items->sum('subtotal');

    $result = app(\App\Domains\Shipping\Services\ShippingCostCalculator::class)->calculate($order->store, $order->state_id, $order->city_id, $subtotal, $productIds);

    // shipping_cost is always sourced from published/configured rates only.
    $order->update(['shipping_cost' => (float) ($result['cost'] ?? 0)]);
};

$guardOrderEditable = function (): bool {
    if (!$this->editingId) {
        return false;
    }

    $order = Order::where('store_id', currentStoreId())->findOrFail($this->editingId);

    // Dynamic guard: block geography edits for shipped or later (mirrors the edit modal).
    $shippedSortOrder = \App\Models\Status::where('type', 'order')->where('key', 'shipped')->value('sort_order');

    if ($order->status && $shippedSortOrder !== null && $order->status->sort_order >= $shippedSortOrder) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.cannot_edit_shipped')]);
        return false;
    }

    return true;
};

$startOrderWilayaEdit = function (string $orderId): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $this->startEdit('order.wilaya', $orderId, Order::where('store_id', currentStoreId())->whereKey($orderId)->value('state_id'));
};

$startOrderCityEdit = function (string $orderId): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $order = Order::where('store_id', currentStoreId())->find($orderId);

    if (!$order || !$order->state_id) {
        return;
    }

    $this->editCityOptions = City::where('state_id', $order->state_id)->orderBy('name')->get()->toArray();
    $this->startEdit('order.city', $orderId, $order->city_id);
};

$cancelOrderEdit = function (): void {
    $this->cancelEdit();
};

$saveOrderWilaya = function (?string $stateId = null): void {
    if (!$this->guardOrderEditable()) {
        return;
    }

    if ($stateId !== null) {
        $this->editingValue = $stateId;
    }

    $orderId = $this->editingId;

    $this->saveEdit([
        'field' => 'order.wilaya',
        'permission' => StorePermissionEnum::ORDER_MANAGE->value,
        'rules' => ['value' => ['required', 'string', 'exists:states,id']],
        'subject' => fn(mixed $id) => Order::where('store_id', currentStoreId())->findOrFail($id),
        'apply' => function (Order $order, $value): void {
            $cityId = $order->city_id;
            if ($cityId) {
                $city = City::find($cityId);
                if (!$city || $city->state_id !== $value) {
                    $cityId = null;
                }
            }

            $order->update(['state_id' => $value, 'city_id' => $cityId]);
            $this->recalculateOrderShipping($order->fresh());
        },
        'label' => 'order wilaya',
        'audit_event' => 'order_wilaya_updated',
    ]);

    $this->refreshSingleOrder($orderId);
};

$saveOrderCity = function (?string $cityId = null): void {
    if (!$this->guardOrderEditable()) {
        return;
    }

    if ($cityId !== null) {
        $this->editingValue = $cityId;
    }

    $order = Order::where('store_id', currentStoreId())->find($this->editingId);
    $orderId = $this->editingId;

    $this->saveEdit([
        'field' => 'order.city',
        'permission' => StorePermissionEnum::ORDER_MANAGE->value,
        'rules' => function (string $field, $value, $id) use ($order): array {
            return [
                'value' => [
                    'required',
                    'string',
                    'exists:cities,id',
                    function (string $attribute, $candidate, $fail) use ($order): void {
                        if (!$candidate) {
                            return;
                        }

                        $city = City::find($candidate);

                        if (!$order || !$order->state_id || !$city || $city->state_id !== $order->state_id) {
                            $fail(__('Selected commune does not belong to this wilaya'));
                        }
                    },
                ],
            ];
        },
        'subject' => fn(mixed $id) => Order::where('store_id', currentStoreId())->findOrFail($id),
        'apply' => function (Order $order, $value): void {
            $order->update(['city_id' => $value]);
            $this->recalculateOrderShipping($order->fresh());
        },
        'label' => 'order city',
        'audit_event' => 'order_city_updated',
    ]);

    $this->refreshSingleOrder($orderId);
};

// ——— 31.3 ——— Inline searchable selects (provider / delivery type / shipment type / stopdesk point / agent) ———

$inlineStopdeskOptions = function (Order $order): array {
    $query = \App\Domains\Shipping\Models\StopdeskPoint::query()
        ->where('store_id', currentStoreId())
        ->where('is_active', true)
        ->with('city:id,name');

    // Office selection is scoped to the order's carrier (a company may only serve
    // its own points; shared points remain available to every carrier).
    if ($order->shipping_provider_id) {
        $query->where(function ($q) use ($order) {
            $q->where('shipping_provider_id', $order->shipping_provider_id)
                ->orWhereNull('shipping_provider_id');
        });
    }

    return $query->orderBy('name')
        ->get()
        ->map(fn($p) => [
            'value' => (string) $p->id,
            'label' => $p->name . ($p->city?->name ? " ({$p->city->name})" : ''),
        ])
        ->all();
};

$startOrderProviderEdit = function (string $orderId): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $order = Order::where('store_id', currentStoreId())->find($orderId);

    if (!$order) {
        return;
    }

    $this->editStopdeskOptions = $this->inlineStopdeskOptions($order);
    $this->startEdit('order.shipping_provider', $orderId, $order->shipping_provider_id);
};

$saveOrderProvider = function (?string $providerId = null): void {
    if (!$this->guardOrderEditable()) {
        return;
    }

    if ($providerId !== null) {
        $this->editingValue = $providerId;
    }

    $order = Order::where('store_id', currentStoreId())->find($this->editingId);
    $orderId = $this->editingId;

    $this->saveEdit([
        'field' => 'order.shipping_provider',
        'permission' => StorePermissionEnum::ORDER_MANAGE->value,
        'rules' => function (string $field, $value, $id) use ($order): array {
            return ['value' => ['nullable', function (string $attribute, $candidate, $fail) use ($order): void {
                if (blank($candidate)) {
                    return;
                }

                $providerExists = \App\Domains\Shipping\Models\ShippingProvider::where('store_id', currentStoreId())
                    ->whereKey($candidate)
                    ->exists();

                if (!$providerExists) {
                    $fail(__('Selected shipping company is invalid'));
                }
            }]];
        },
        'subject' => fn(mixed $id) => Order::where('store_id', currentStoreId())->findOrFail($id),
        'apply' => function (Order $order, $value): void {
            $providerId = blank($value) ? null : $value;
            $data = ['shipping_provider_id' => $providerId];

            // Switching carrier: drop an office no longer served by the new carrier.
            if ($providerId && $order->delivery_type === Order::DELIVERY_STOPDESK && $order->stopdesk_point_id) {
                $stillValid = \App\Domains\Shipping\Models\StopdeskPoint::where('store_id', currentStoreId())
                    ->whereKey($order->stopdesk_point_id)
                    ->where(fn($q) => $q->where('shipping_provider_id', $providerId)->orWhereNull('shipping_provider_id'))
                    ->exists();

                if (!$stillValid) {
                    $data['stopdesk_point_id'] = null;
                }
            }

            $order->update($data);
            $this->recalculateOrderShipping($order->fresh());
        },
        'label' => 'order shipping provider',
        'audit_event' => 'order_shipping_provider_updated',
    ]);

    $this->refreshSingleOrder($orderId);
};

$startOrderDeliveryTypeEdit = function (string $orderId): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $this->startEdit(
        'order.delivery_type',
        $orderId,
        Order::where('store_id', currentStoreId())->whereKey($orderId)->value('delivery_type'),
    );
};

$saveOrderDeliveryType = function (?string $deliveryType = null): void {
    if (!$this->guardOrderEditable()) {
        return;
    }

    if ($deliveryType !== null) {
        $this->editingValue = $deliveryType;
    }

    $orderId = $this->editingId;

    $this->saveEdit([
        'field' => 'order.delivery_type',
        'permission' => StorePermissionEnum::ORDER_MANAGE->value,
        'rules' => ['value' => ['required', 'in:home,stopdesk']],
        'subject' => fn(mixed $id) => Order::where('store_id', currentStoreId())->findOrFail($id),
        'apply' => function (Order $order, $value): void {
            $data = ['delivery_type' => $value];

            // Home delivery has no office; only custom-created stopdesk orders
            // keep one, and the completeness gate re-checks it on send.
            if ($value === Order::DELIVERY_HOME) {
                $data['stopdesk_point_id'] = null;
            }

            $order->update($data);
            $this->recalculateOrderShipping($order->fresh());
        },
        'label' => 'order delivery type',
        'audit_event' => 'order_delivery_type_updated',
    ]);

    $this->refreshSingleOrder($orderId);
};

$startOrderShipmentTypeEdit = function (string $orderId): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $this->startEdit(
        'order.shipment_type',
        $orderId,
        Order::where('store_id', currentStoreId())->whereKey($orderId)->value('shipment_type'),
    );
};

$saveOrderShipmentType = function (?string $shipmentType = null): void {
    if (!$this->guardOrderEditable()) {
        return;
    }

    if ($shipmentType !== null) {
        $this->editingValue = $shipmentType;
    }

    $orderId = $this->editingId;

    $this->saveEdit([
        'field' => 'order.shipment_type',
        'permission' => StorePermissionEnum::ORDER_MANAGE->value,
        'rules' => ['value' => ['required', 'in:delivery,exchange,pickup']],
        'subject' => fn(mixed $id) => Order::where('store_id', currentStoreId())->findOrFail($id),
        'apply' => fn(Order $order, $value) => $order->update(['shipment_type' => $value]),
        'label' => 'order shipment type',
        'audit_event' => 'order_shipment_type_updated',
    ]);

    $this->refreshSingleOrder($orderId);
};

$startOrderStopdeskEdit = function (string $orderId): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $order = Order::where('store_id', currentStoreId())->find($orderId);

    if (!$order) {
        return;
    }

    $this->editStopdeskOptions = $this->inlineStopdeskOptions($order);
    $this->startEdit('order.stopdesk_point', $orderId, $order->stopdesk_point_id);
};

$saveOrderStopdesk = function (?string $pointId = null): void {
    if (!$this->guardOrderEditable()) {
        return;
    }

    if ($pointId !== null) {
        $this->editingValue = $pointId;
    }

    $order = Order::where('store_id', currentStoreId())->find($this->editingId);
    $orderId = $this->editingId;

    $this->saveEdit([
        'field' => 'order.stopdesk_point',
        'permission' => StorePermissionEnum::ORDER_MANAGE->value,
        'rules' => function (string $field, $value, $id) use ($order): array {
            return ['value' => ['nullable', function (string $attribute, $candidate, $fail) use ($order): void {
                if (blank($candidate)) {
                    return;
                }

                $point = \App\Domains\Shipping\Models\StopdeskPoint::where('store_id', currentStoreId())
                    ->whereKey($candidate)
                    ->first();

                if (!$point) {
                    $fail(__('Selected point is invalid'));
                    return;
                }

                if ($order?->shipping_provider_id
                    && $point->shipping_provider_id
                    && $point->shipping_provider_id !== $order->shipping_provider_id) {
                    $fail(__('Selected point does not belong to this company'));
                }
            }]];
        },
        'subject' => fn(mixed $id) => Order::where('store_id', currentStoreId())->findOrFail($id),
        'apply' => fn(Order $order, $value) => $order->update(['stopdesk_point_id' => blank($value) ? null : $value]),
        'label' => 'order stopdesk point',
        'audit_event' => 'order_stopdesk_point_updated',
    ]);

    $this->refreshSingleOrder($orderId);
};

$startOrderAgentEdit = function (string $orderId): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $this->startEdit(
        'order.assigned_agent',
        $orderId,
        Order::where('store_id', currentStoreId())->whereKey($orderId)->value('assigned_to_membership_id'),
    );
};

$saveOrderAgent = function (?string $membershipId = null): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    if (!$this->editingId) {
        return;
    }

    if ($membershipId !== null) {
        $this->editingValue = $membershipId;
    }

    $order = Order::where('store_id', currentStoreId())->find($this->editingId);
    $orderId = $this->editingId;

    if (!$order) {
        $this->cancelEdit();
        return;
    }

    $this->saveEdit([
        'field' => 'order.assigned_agent',
        'permission' => StorePermissionEnum::ORDER_MANAGE->value,
        'rules' => function (string $field, $value, $id) use ($order): array {
            return ['value' => ['nullable', function (string $attribute, $candidate, $fail) use ($order): void {
                if (blank($candidate)) {
                    return;
                }

                $member = \App\Models\Stores\Team\StoreMembership::where('store_id', currentStoreId())
                    ->whereKey($candidate)
                    ->where('is_active', true)
                    ->exists();

                if (!$member) {
                    $fail(__('Selected agent is invalid'));
                }
            }]];
        },
        'subject' => fn(mixed $id) => Order::where('store_id', currentStoreId())->findOrFail($id),
        'apply' => function (Order $order, $value): void {
            if (blank($value)) {
                $order->update([
                    'assigned_to_membership_id' => null,
                    'assigned_at' => null,
                    'assigned_by_membership_id' => null,
                    'assignment_method' => null,
                ]);
                return;
            }

            // Reuse the manual-reassignment path (no eligibility checks, no shift cap).
            $target = \App\Models\Stores\Team\StoreMembership::where('store_id', currentStoreId())->find($value);

            if (!$target) {
                return;
            }

            $by = $this->getCurrentMembership();

            if (!$by) {
                return;
            }

            app(\App\Domains\Order\Services\OrderAssignmentService::class)
                ->reassign($order, $target, $by);
        },
        'label' => 'order assigned agent',
        'audit_event' => 'order_assigned_agent_updated',
    ]);

    $this->refreshSingleOrder($orderId);
};

// ——— 31.4 ——— Inline field edits (address / weight / discount / send-from-warehouse) ———

$startOrderAddressEdit = function (string $orderId): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $this->startEdit('order.address', $orderId, Order::where('store_id', currentStoreId())->whereKey($orderId)->value('address'));
};

$saveOrderAddress = function (?string $address = null): void {
    if (!$this->guardOrderEditable()) {
        return;
    }

    if ($address !== null) {
        $this->editingValue = $address;
    }

    $orderId = $this->editingId;

    $this->saveEdit([
        'field' => 'order.address',
        'permission' => StorePermissionEnum::ORDER_MANAGE->value,
        'rules' => ['value' => ['nullable', 'string', 'max:5000']],
        'subject' => fn(mixed $id) => Order::where('store_id', currentStoreId())->findOrFail($id),
        'apply' => function (Order $order, $value): void {
            $data = ['address' => blank($value) ? null : $value];

            // Address keep-alive mirrors the customer side: editing it when the
            // order already moved towards the carrier is blocked by the guard.
            $order->update($data);
            $this->recalculateOrderShipping($order->fresh());
        },
        'label' => 'order address',
        'audit_event' => 'order_address_updated',
    ]);

    $this->refreshSingleOrder($orderId);
};

$startOrderWeightEdit = function (string $orderId): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $this->startEdit(
        'order.weight',
        $orderId,
        Order::where('store_id', currentStoreId())->whereKey($orderId)->value('weight_kg'),
    );
};

$saveOrderWeight = function (?string $weight = null): void {
    if (!$this->guardOrderEditable()) {
        return;
    }

    if ($weight !== null) {
        $this->editingValue = $weight;
    }

    $orderId = $this->editingId;

    $this->saveEdit([
        'field' => 'order.weight',
        'permission' => StorePermissionEnum::ORDER_MANAGE->value,
        'rules' => ['value' => ['nullable', 'numeric', 'min:0', 'max:9999']],
        'subject' => fn(mixed $id) => Order::where('store_id', currentStoreId())->findOrFail($id),
        'apply' => fn(Order $order, $value) => $order->update(['weight_kg' => blank($value) ? 1.00 : $value]),
        'label' => 'order weight',
        'audit_event' => 'order_weight_updated',
    ]);

    $this->refreshSingleOrder($orderId);
};

$startOrderNotesEdit = function (string $orderId): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $this->startEdit(
        'order.notes',
        $orderId,
        Order::where('store_id', currentStoreId())->whereKey($orderId)->value('notes'),
    );
};

$saveOrderNotes = function (?string $notes = null): void {
    if (!$this->guardOrderEditable()) {
        return;
    }

    if ($notes !== null) {
        $this->editingValue = $notes;
    }

    $orderId = $this->editingId;

    $this->saveEdit([
        'field' => 'order.notes',
        'permission' => StorePermissionEnum::ORDER_MANAGE->value,
        'rules' => ['value' => ['nullable', 'string', 'max:500']],
        'subject' => fn(mixed $id) => Order::where('store_id', currentStoreId())->findOrFail($id),
        'apply' => fn(Order $order, $value) => $order->update(['notes' => blank($value) ? null : $value]),
        'label' => 'order notes',
        'audit_event' => 'order_notes_updated',
    ]);

    $this->refreshSingleOrder($orderId);
};

$startOrderDiscountEdit = function (string $orderId): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $order = Order::where('store_id', currentStoreId())->find($orderId);

    if (!$order) {
        return;
    }

    $this->discountEditType = (string) ($order->discount_type ?? '');
    $this->discountEditValue = $order->discount_value ?? '';
    $this->discountEditReason = (string) ($order->discount_reason ?? '');
    $this->startEdit('order.discount', $orderId, $order->discount_value);
};

$saveOrderDiscount = function (): void {
    if (!$this->guardOrderEditable()) {
        return;
    }

    $orderId = $this->editingId;
    $type = $this->discountEditType;
    $value = $this->discountEditValue;

    // Type must be one of the select options; value drives the audit payload.
    $this->editingValue = $value;

    $this->saveEdit([
        'field' => 'order.discount',
        'permission' => StorePermissionEnum::ORDER_MANAGE->value,
        'rules' => [
            'value' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999',
                function (string $attribute, $candidate, $fail): void {
                    if ($this->discountEditType === 'percent' && (float) $candidate > 100) {
                        $fail(__('merchant_panel.discount_percent_max'));
                    }
                },
            ],
        ],
        'subject' => fn(mixed $id) => Order::where('store_id', currentStoreId())->findOrFail($id),
        'apply' => function (Order $order, $value): void {
            $type = $this->discountEditType;

            // No type selected (or a leaked value with no type): clear the discount.
            if (! in_array($type, ['amount', 'percent'], true) || blank($value)) {
                $order->update(['discount_type' => null, 'discount_value' => null, 'discount_reason' => null]);
                return;
            }

            $order->update([
                'discount_type' => $type,
                'discount_value' => $value,
                'discount_reason' => blank($this->discountEditReason) ? null : $this->discountEditReason,
            ]);
        },
        'label' => 'order discount',
        'audit_event' => 'order_discount_updated',
    ]);

    $this->refreshSingleOrder($orderId);
};

$toggleSendFromWarehouse = function (string $orderId): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $order = Order::where('store_id', currentStoreId())->find($orderId);

    if (!$order) {
        return;
    }

    $next = ! (bool) $order->send_from_carrier_warehouse;
    $order->update(['send_from_carrier_warehouse' => $next]);

    activity(config('activitylog.default_log_name', 'default'))
        ->event('order_send_from_warehouse_updated')
        ->withProperties(['value' => $next, 'record_id' => $order->id])
        ->by(auth()->user())
        ->on($order)
        ->log('Applied order send from carrier warehouse');

    $this->refreshSingleOrder($orderId);
};

// Clicking the missing-fields row badge jumps straight to the first missing
// field that has an inline editor (customer/address/carrier/geo). "items" has
// no inline editor yet (Phase 31.5) so it opens the full edit modal instead.
$startMissingFieldEdit = function (string $orderId): void {
    $order = Order::where('store_id', currentStoreId())->with($this->orderEagerLoads())->find($orderId);

    if (!$order) {
        return;
    }

    // A shipped order's only actionable missing field is the carrier (send-level),
    // but stopdesk/address already carry the shipped-blocking guard inside their
    // save path, so start the first editable one directly.
    $forSend = $order->status?->key === 'confirmed' || $order->status?->key === 'preparing';
    $first = collect(app(\App\Domains\Order\Services\OrderCompleteness::class)->missing($order, $forSend))->first();

    if (!$first) {
        return;
    }

    $action = match ($first['key']) {
        'customer_name' => 'startOrderNameEdit',
        'customer_phone' => 'startOrderPhoneEdit',
        'state' => 'startOrderWilayaEdit',
        'city' => 'startOrderCityEdit',
        'stopdesk_point' => 'startOrderStopdeskEdit',
        'delivery_address' => 'startOrderAddressEdit',
        'carrier' => 'startOrderProviderEdit',
        default => null,
    };

    if ($action === null) {
        $this->openEditModal($orderId);
        return;
    }

    $this->{$action}($orderId);
};

// ——— Create Modal ———
$openCreateModal = function (): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }
    $this->form = [
        'customer_name' => '',
        'customer_phone' => '',
        'phone_secondary' => '',
        'address' => '',
        'state_id' => '',
        'city_id' => '',
        'delivery_type' => 'home',
        'shipping_provider_id' => '',
        'stopdesk_point_id' => '',
        'shipment_type' => 'delivery',
        'payment_method' => 'cod',
        'discount_type' => null,
        'discount_value' => null,
        'discount_reason' => '',
        'notes' => '',
        'weight_kg' => '',
        'items' => [],
    ];
    $this->formOffices = [];
    $this->formProductView = 'list';
    $this->formSelectedProduct = null;
    $this->formDuplicateWarnings = [];
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

    $store = $variant->product?->store;
    $cap = \App\Domains\Cart\Support\OrderRules::lineCap($variant, $store);
    $available = (int) $variant->stock;
    $tracks = \App\Domains\Cart\Support\OrderRules::tracksInventory($store);
    $backorder = \App\Domains\Cart\Support\OrderRules::allowsBackorder($store);

    if ($tracks && !$backorder && $available <= 0) {
        $this->syncFormSelectedItems();
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.out_of_stock', ['variant' => $variant->name])]);
        return;
    }

    $found = false;
    foreach ($this->form['items'] as $idx => &$item) {
        if ($item['product_variant_id'] === $variantId) {
            if ($cap !== null && ($item['quantity'] ?? 0) >= $cap) {
                $this->syncFormSelectedItems();
                $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.max_qty_reached', ['cap' => $cap])]);
                return;
            }
            $this->form['items'][$idx]['quantity']++;
            $this->form['items'][$idx]['preorder'] = $backorder && $available < $this->form['items'][$idx]['quantity'];
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
            'cap' => $cap,
            'preorder' => $backorder && $available < 1,
            'image_url' => $variant->product?->primaryImage?->path ? Storage::disk('public')->url($variant->product->primaryImage->path) : asset('img/icons/noimg.png'),
        ];
    }
    $this->syncFormSelectedItems();
    $this->recalcFormWeight();
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

    $store = $variant->product?->store;
    $cap = \App\Domains\Cart\Support\OrderRules::lineCap($variant, $store);
    $available = (int) $variant->stock;
    $tracks = \App\Domains\Cart\Support\OrderRules::tracksInventory($store);
    $backorder = \App\Domains\Cart\Support\OrderRules::allowsBackorder($store);

    if ($tracks && !$backorder && $available <= 0) {
        $this->syncFormSelectedItems();
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.out_of_stock', ['variant' => $variant->name])]);
        return;
    }

    // Check if already in items
    foreach ($this->form['items'] as $idx => &$item) {
        if ($item['product_variant_id'] === $variant->id) {
            if ($cap !== null && ($item['quantity'] ?? 0) >= $cap) {
                $this->syncFormSelectedItems();
                $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.max_qty_reached', ['cap' => $cap])]);
                return;
            }
            $this->form['items'][$idx]['quantity']++;
            $this->form['items'][$idx]['preorder'] = $backorder && $available < $this->form['items'][$idx]['quantity'];
            $this->syncFormSelectedItems();
            $this->recalcFormWeight();
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
        'cap' => $cap,
        'preorder' => $backorder && $available < 1,
        'image_url' => $variant->product?->primaryImage?->path ? Storage::disk('public')->url($variant->product->primaryImage->path) : asset('img/icons/noimg.png'),
    ];
    $this->syncFormSelectedItems();
    $this->recalcFormWeight();
};

$removeFormItem = function (int $index): void {
    unset($this->form['items'][$index]);
    $this->form['items'] = array_values($this->form['items']);
    $this->syncFormSelectedItems();
    $this->recalcFormWeight();
};

$updateFormItemQty = function (int $index, int $qty): void {
    if (!isset($this->form['items'][$index])) {
        return;
    }

    $cap = $this->form['items'][$index]['cap'] ?? null;
    $this->form['items'][$index]['quantity'] = min(max(1, $qty), $cap ?? PHP_INT_MAX);

    $variant = ProductVariant::find($this->form['items'][$index]['product_variant_id']);
    if ($variant) {
        $available = (int) $variant->stock;
        $this->form['items'][$index]['preorder'] = \App\Domains\Cart\Support\OrderRules::allowsBackorder($variant->product?->store) && $available < $this->form['items'][$index]['quantity'];
    }

    $this->recalcFormWeight();
};

$updateFormItemPrice = function (int $index, $price): void {
    if (isset($this->form['items'][$index])) {
        $this->form['items'][$index]['price'] = max(0, (float) $price);
    }
};

$submitCreate = function (): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

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
        'shipping_provider_id' => 'required_if:delivery_type,stopdesk|nullable|exists:shipping_providers,id',
        'stopdesk_point_id' => 'required_if:delivery_type,stopdesk|nullable|exists:stopdesk_points,id',
        'phone_secondary' => 'nullable|string|max:20|regex:/^0[5-7]\d{8}$/',
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

        // C4: Check stock unless backorders are allowed
        if ($tracksInventory && !\App\Domains\Cart\Support\OrderRules::allowsBackorder($store)) {
            $available = (int) $variant->stock;
            if ($available < $itemData['quantity']) {
                $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.insufficient_stock', ['variant' => $variant->name, 'available' => max(0, $available)])]);
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
            'weight_kg' => $this->form['weight_kg'] ?: 1.00,
            // Carrier applies to both delivery types (dispatch needs it for home too);
            // the office only applies to stopdesk deliveries.
            'shipping_provider_id' => $this->form['shipping_provider_id'] ?: null,
            'stopdesk_point_id' => $this->form['delivery_type'] === 'stopdesk' ? ($this->form['stopdesk_point_id'] ?: null) : null,
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

    $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('merchant.order_created')]);
};

// ——— Edit Modal ———
$openEditModal = function (string $orderId): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

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
    $this->formDuplicateWarnings = [];
    $this->loadProducts();
    $this->refreshFormDuplicateWarnings();
    $this->showEditModal = true;

    if ($order->state_id) {
        $this->allCities = City::where('state_id', $order->state_id)->orderBy('name')->get()->toArray();
    }

    // Carrier-first office options for the edit modal (no network round-trip:
    // offices are already persisted in stopdesk_points).
    $this->formOffices = [];
    $this->loadFormOffices($order->shipping_provider_id, preserveOffice: true);
};

$submitEdit = function (): void {
    if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
        return;
    }

    $order = Order::where('store_id', currentStoreId())->findOrFail($this->editingOrderId);

    // Block edit if shipped or later (dynamic: compare sort_order against 'shipped')
    $shippedSortOrder = \App\Models\Status::where('type', 'order')->where('key', 'shipped')->value('sort_order');
    if ($order->status && $shippedSortOrder !== null && $order->status->sort_order >= $shippedSortOrder) {
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.cannot_edit_shipped')]);
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
        'weight_kg' => $this->form['weight_kg'] ?: 1.00,
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
        if ($delta > 0 && \App\Domains\Cart\Support\OrderRules::tracksInventory($order->store) && !\App\Domains\Cart\Support\OrderRules::allowsBackorder($order->store)) {
            $available = (int) $variant->stock;
            if ($available < $delta) {
                $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.insufficient_stock', ['variant' => $variant->name, 'available' => max(0, $available)])]);
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

    // Recompute the structured shipping cost after item/destination changes.
    $order->refresh();
    $this->recalculateOrderShipping($order);

    $this->showEditModal = false;
    $this->editingOrderId = null;
    $this->page = 1;
    $this->loadOrders();

    $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('merchant.order_updated')]);
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
                <button @click="$wire.openCreateModal()" class="edz-btn edz-btn--primary edz-btn--sm"
                    wire:loading.attr="disabled" wire:target="openCreateModal">
                    <x-edz.icon name="plus" class="w-4 h-4" />
                    <span>{{ __('merchant_panel.new_order') }}</span>
                </button>
            @endif
            <button wire:click="refreshOrders" class="edz-btn edz-btn--ghost edz-btn--sm" wire:loading.attr="disabled"
                wire:loading.class="opacity-50 pointer-events-none" wire:target="refreshOrders">
                <x-edz.icon name="arrow-path" class="w-4 h-4" />
            </button>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="edz-card edz-card--padded mb-4">
        {{-- Main row: search + column toggle + filters toggle --}}
        <div class="flex flex-wrap items-center gap-3">
            {{-- Unified Search --}}
            <div class="relative flex-1 max-w-[230px] min-w-[150px]">
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
                    <x-edz.icon name="arrow-right" class="w-4 h-4" />
                </button>
            </div>

            {{-- Table Settings --}}
            <button wire:click="openTableSettings" class="edz-btn edz-btn--ghost edz-btn--sm"
                    wire:loading.attr="disabled" wire:target="openTableSettings">
                    <x-edz.icon name="view-columns" class="w-4 h-4" />
                    <span>{{ __('merchant_panel.columns') }}</span>
                </button>

            {{-- Quick Filters (grouped popup) --}}
            @php
                $quickActiveCount = collect(['source', 'delivery_type', 'shipping_provider'])
                    ->filter(fn ($k) => filled($this->filters[$k] ?? null))
                    ->count();
            @endphp
            <x-edz.dropdown align="right" width="340px"
                trigger-class="edz-btn edz-btn--ghost edz-btn--sm {{ $quickActiveCount > 0 ? 'text-accent-600' : '' }}">
                <x-slot name="trigger">
                    <x-edz.icon name="funnel"
                        class="w-4 h-4 {{ $quickActiveCount > 0 ? 'text-accent-600' : '' }}" />
                    <span>{{ __('merchant_panel.filters') }}</span>
                    @if ($quickActiveCount > 0)
                        <span
                            class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-[10px] font-semibold bg-accent-600 text-white leading-none">
                            {{ $quickActiveCount }}
                        </span>
                    @endif
                    <x-edz.icon name="chevron-down" class="w-3 h-3" />
                </x-slot>

                <span class="pointer-events-none mx-auto mb-2 block h-1 w-10 rounded-full bg-surface-border sm:hidden"></span>
                <div class="flex items-center justify-between gap-2 px-1 mb-1.5 sm:hidden">
                    <p class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink uppercase tracking-wide">
                        <x-edz.icon name="funnel" class="w-3.5 h-3.5 text-ink-muted" />
                        <span>{{ __('merchant_panel.filters') }}</span>
                    </p>
                    <button @click="close()" type="button"
                        class="-m-1 p-1 rounded-lg text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                        title="{{ __('general.close') }}">
                        <x-edz.icon name="x-mark" class="w-4 h-4" />
                    </button>
                </div>

                <div class="edz-dropdown__section">
                    <p class="edz-dropdown__section-title">{{ __('merchant_panel.source') }}</p>
                    <button type="button" wire:click="setFilter('source', null)" @click="close()"
                        aria-pressed="{{ empty($this->filters['source']) ? 'true' : 'false' }}"
                        class="edz-dropdown__item justify-between {{ empty($this->filters['source']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                        {{ __('general.all') }}
                        <x-edz.icon name="check" class="w-3.5 h-3.5 {{ empty($this->filters['source']) ? 'opacity-100' : 'opacity-0' }}" />
                    </button>
                    <button type="button" wire:click="setFilter('source', 'store')" @click="close()"
                        aria-pressed="{{ ($this->filters['source'] ?? null) === 'store' ? 'true' : 'false' }}"
                        class="edz-dropdown__item justify-between {{ ($this->filters['source'] ?? null) === 'store' ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                        <span>{{ __('merchant_panel.store') }}</span>
                        <x-edz.icon name="check" class="w-3.5 h-3.5 {{ ($this->filters['source'] ?? null) === 'store' ? 'opacity-100' : 'opacity-0' }}" />
                    </button>
                    <button type="button" wire:click="setFilter('source', 'manual')" @click="close()"
                        aria-pressed="{{ ($this->filters['source'] ?? null) === 'manual' ? 'true' : 'false' }}"
                        class="edz-dropdown__item justify-between {{ ($this->filters['source'] ?? null) === 'manual' ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                        <span>{{ __('merchant.delivery_man') }}</span>
                        <x-edz.icon name="check" class="w-3.5 h-3.5 {{ ($this->filters['source'] ?? null) === 'manual' ? 'opacity-100' : 'opacity-0' }}" />
                    </button>
                </div>

                <div class="edz-dropdown__section">
                    <p class="edz-dropdown__section-title">{{ __('storefront.delivery_type') }}</p>
                    <button type="button" wire:click="setFilter('delivery_type', null)" @click="close()"
                        aria-pressed="{{ empty($this->filters['delivery_type']) ? 'true' : 'false' }}"
                        class="edz-dropdown__item justify-between {{ empty($this->filters['delivery_type']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                        {{ __('general.all') }}
                        <x-edz.icon name="check" class="w-3.5 h-3.5 {{ empty($this->filters['delivery_type']) ? 'opacity-100' : 'opacity-0' }}" />
                    </button>
                    <button type="button" wire:click="setFilter('delivery_type', 'home')" @click="close()"
                        aria-pressed="{{ ($this->filters['delivery_type'] ?? null) === 'home' ? 'true' : 'false' }}"
                        class="edz-dropdown__item justify-between {{ ($this->filters['delivery_type'] ?? null) === 'home' ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                        <span>{{ __('storefront.home_delivery') }}</span>
                        <x-edz.icon name="check" class="w-3.5 h-3.5 {{ ($this->filters['delivery_type'] ?? null) === 'home' ? 'opacity-100' : 'opacity-0' }}" />
                    </button>
                    <button type="button" wire:click="setFilter('delivery_type', 'stopdesk')" @click="close()"
                        aria-pressed="{{ ($this->filters['delivery_type'] ?? null) === 'stopdesk' ? 'true' : 'false' }}"
                        class="edz-dropdown__item justify-between {{ ($this->filters['delivery_type'] ?? null) === 'stopdesk' ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                        <span>{{ __('storefront.stop_desk') }}</span>
                        <x-edz.icon name="check" class="w-3.5 h-3.5 {{ ($this->filters['delivery_type'] ?? null) === 'stopdesk' ? 'opacity-100' : 'opacity-0' }}" />
                    </button>
                </div>

                <div class="edz-dropdown__section">
                    <p class="edz-dropdown__section-title">{{ __('order_flow.filter_provider') }}</p>
                    <button type="button" wire:click="setFilter('shipping_provider', null)" @click="close()"
                        aria-pressed="{{ empty($this->filters['shipping_provider']) ? 'true' : 'false' }}"
                        class="edz-dropdown__item justify-between {{ empty($this->filters['shipping_provider']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                        {{ __('general.all') }}
                        <x-edz.icon name="check" class="w-3.5 h-3.5 {{ empty($this->filters['shipping_provider']) ? 'opacity-100' : 'opacity-0' }}" />
                    </button>
                    @foreach ($this->allProviders as $pr)
                        <button type="button" wire:click="setFilter('shipping_provider', '{{ $pr['id'] }}')" @click="close()"
                            aria-pressed="{{ ($this->filters['shipping_provider'] ?? null) == $pr['id'] ? 'true' : 'false' }}"
                            class="edz-dropdown__item justify-between {{ ($this->filters['shipping_provider'] ?? null) == $pr['id'] ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                            <span>{{ $pr['name'] }}</span>
                            <x-edz.icon name="check" class="w-3.5 h-3.5 {{ ($this->filters['shipping_provider'] ?? null) == $pr['id'] ? 'opacity-100' : 'opacity-0' }}" />
                        </button>
                    @endforeach
                </div>
            </x-edz.dropdown>

            {{-- Trash Toggle --}}
            <button wire:click="toggleTrash"
                class="edz-btn edz-btn--ghost edz-btn--sm {{ $this->showTrash ? 'text-danger-600' : '' }}"
                wire:loading.attr="disabled" wire:loading.class="opacity-50 pointer-events-none">
                <x-edz.icon name="trash" class="w-4 h-4" />
                <span>{{ $this->showTrash ? __('buttons.close') . ' ' . __('merchant.trash_bin') : __('merchant.trash_bin') }}</span>
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
    @php
        $hasActiveFilters = collect($this->filters)->filter(function ($v, $k) {
            if ($k === 'send_from_carrier_warehouse') {
                return $v !== null;
            }

            return filled($v);
        })->isNotEmpty();
    @endphp
    @if ($hasActiveFilters)
        <div class="mb-3 flex items-center gap-2 flex-wrap">
            @if (!empty($this->filters['status']))
                @foreach ($this->allStatuses as $s)
                    @if (in_array($s['id'], $this->filters['status']))
                        <span
                            class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                            <span class="font-semibold opacity-75">{{ __('merchant_panel.status') }}:</span>
                            <span>{{ \Edzeery\MyStatusKit\Facades\Status::for('order', $s['key'] ?? 'default')->label() }}</span>
                            <button wire:click="toggleStatusFilter('{{ $s['id'] }}')"
                                wire:loading.attr="disabled" class="hover:text-accent-900"><x-edz.icon name="x-mark"
                                    class="w-3 h-3" /></button>
                        </span>
                    @endif
                @endforeach
            @endif
            @if (!empty($this->filters['wilaya']))
                <span
                    class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    <span class="font-semibold opacity-75">{{ __('merchant_panel.state') }}:</span>
                    <span>{{ collect($this->allStates)->firstWhere('id', $this->filters['wilaya'])['name'] ?? $this->filters['wilaya'] }}</span>
                    <button wire:click="setFilter('wilaya', null)" wire:loading.attr="disabled"
                        class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
                </span>
            @endif
            @if (!empty($this->filters['city']))
                <span
                    class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    <span class="font-semibold opacity-75">{{ __('merchant_panel.city') }}:</span>
                    <span>{{ collect($this->allCities)->firstWhere('id', $this->filters['city'])['name'] ?? $this->filters['city'] }}</span>
                    <button wire:click="setFilter('city', null)" wire:loading.attr="disabled"
                        class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
                </span>
            @endif
            @if (!empty($this->filters['shipping_provider']))
                <span
                    class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    <span class="font-semibold opacity-75">{{ __('order_flow.filter_provider') }}:</span>
                    <span>{{ collect($this->allProviders)->firstWhere('id', $this->filters['shipping_provider'])['name'] ?? $this->filters['shipping_provider'] }}</span>
                    <button
                        @click="$wire.setFilter('shipping_provider', null); $wire.setFilter('stopdesk_point', null)"
                        wire:loading.attr="disabled" class="hover:text-accent-900"><x-edz.icon name="x-mark"
                            class="w-3 h-3" /></button>
                </span>
            @endif
            @if (!empty($this->filters['stopdesk_point']))
                <span
                    class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    <span class="font-semibold opacity-75">{{ __('merchant_panel.office') }}:</span>
                    <span>{{ collect($this->allStopdeskPoints)->firstWhere('id', $this->filters['stopdesk_point'])['name'] ?? $this->filters['stopdesk_point'] }}</span>
                    <button wire:click="setFilter('stopdesk_point', null)" wire:loading.attr="disabled"
                        class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
                </span>
            @endif
            @if (!empty($this->filters['delivery_type']))
                <span
                    class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    <span class="font-semibold opacity-75">{{ __('storefront.delivery_type') }}:</span>
                    <span>{{ $this->filters['delivery_type'] === 'stopdesk' ? __('storefront.stop_desk') : __('storefront.home_delivery') }}</span>
                    <button wire:click="setFilter('delivery_type', null)" wire:loading.attr="disabled"
                        class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
                </span>
            @endif
            @if (!empty($this->filters['shipment_type']))
                <span
                    class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    <span class="font-semibold opacity-75">{{ __('merchant_panel.shipment_type') }}:</span>
                    <span>{{ match ($this->filters['shipment_type']) {
                        'delivery' => __('merchant_panel.delivery'),
                        'exchange' => __('merchant_panel.exchange_label'),
                        'pickup' => __('merchant_panel.pickup_label'),
                        default => $this->filters['shipment_type'],
                    } }}</span>
                    <button wire:click="setFilter('shipment_type', null)" wire:loading.attr="disabled"
                        class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
                </span>
            @endif
            @if (!empty($this->filters['source']))
                <span
                    class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    <span class="font-semibold opacity-75">{{ __('merchant_panel.source') }}:</span>
                    <span>{{ $this->filters['source'] === 'store' ? __('merchant_panel.store') : __('merchant.delivery_man') }}</span>
                    <button wire:click="setFilter('source', null)" wire:loading.attr="disabled"
                        class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
                </span>
            @endif
            @if (!empty($this->filters['assigned_to']))
                <span
                    class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    <span class="font-semibold opacity-75">{{ __('merchant_panel.assigned_agent') }}:</span>
                    <span>{{ collect($this->allMembers)->firstWhere('id', $this->filters['assigned_to'])['user']['name'] ?? $this->filters['assigned_to'] }}</span>
                    <button wire:click="setFilter('assigned_to', null)" wire:loading.attr="disabled"
                        class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
                </span>
            @endif
            @if (!empty($this->filters['confirmed_by']))
                <span
                    class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    <span class="font-semibold opacity-75">{{ __('merchant_panel.confirmed_by') }}:</span>
                    <span>{{ collect($this->allMembers)->firstWhere('id', $this->filters['confirmed_by'])['user']['name'] ?? $this->filters['confirmed_by'] }}</span>
                    <button wire:click="setFilter('confirmed_by', null)" wire:loading.attr="disabled"
                        class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
                </span>
            @endif
            @if ($this->filters['send_from_carrier_warehouse'] !== null)
                <span
                    class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    <span class="font-semibold opacity-75">{{ __('merchant_panel.send_from_carrier_warehouse') }}:</span>
                    <span>{{ $this->filters['send_from_carrier_warehouse'] ? __('buttons.yes') : __('buttons.no') }}</span>
                    <button wire:click="setFilter('send_from_carrier_warehouse', null)" wire:loading.attr="disabled"
                        class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
                </span>
            @endif
            @if (!empty($this->filters['date_from']) || !empty($this->filters['date_to']))
                <span
                    class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    <span class="font-semibold opacity-75">{{ __('merchant_panel.date') }}:</span>
                    <span>{{ $this->filters['date_from'] ?? '...' }} — {{ $this->filters['date_to'] ?? '...' }}</span>
                    <button @click="$wire.setFilter('date_from', null); $wire.setFilter('date_to', null)"
                        wire:loading.attr="disabled" class="hover:text-accent-900"><x-edz.icon name="x-mark"
                            class="w-3 h-3" /></button>
                </span>
            @endif
            @if (filled($this->filters['amount_min']) || filled($this->filters['amount_max']))
                <span
                    class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    <span class="font-semibold opacity-75">{{ __('merchant_panel.amount') }}:</span>
                    <span>{{ $this->filters['amount_min'] ?? '0' }} — {{ $this->filters['amount_max'] ?? 'âˆ‍' }}</span>
                    @if (filled($this->filters['amount_min']))
                        <button wire:click="$set('filters.amount_min', '')" wire:loading.attr="disabled"
                            class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
                    @endif
                    @if (filled($this->filters['amount_max']))
                        <button wire:click="$set('filters.amount_max', '')" wire:loading.attr="disabled"
                            class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
                    @endif
                </span>
            @endif
            @if (filled($this->filters['weight_min']) || filled($this->filters['weight_max']))
                <span
                    class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    <span class="font-semibold opacity-75">{{ __('merchant_panel.weight') }}:</span>
                    <span>{{ $this->filters['weight_min'] ?? '0' }} — {{ $this->filters['weight_max'] ?? 'âˆ‍' }}</span>
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
            @if (filled($this->filters['product_id']) || filled($this->filters['product']))
                <span
                    class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    <span class="font-semibold opacity-75">{{ __('merchant_panel.product') }}:</span>
                    <span>{{ filled($this->filters['product']) ? $this->filters['product'] : (collect($this->filterProducts)->firstWhere('id', $this->filters['product_id'])['name'] ?? $this->filters['product_id']) }}</span>
                    <button @click="$wire.set('filters.product', ''); $wire.set('filters.product_id', null)"
                        wire:loading.attr="disabled" class="hover:text-accent-900"><x-edz.icon name="x-mark"
                            class="w-3 h-3" /></button>
                </span>
            @endif
            @if ($this->filters['address'] !== '')
                <span
                    class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    <span class="font-semibold opacity-75">{{ __('merchant_panel.address') }}:</span>
                    <span class="max-w-[14rem] truncate">{{ $this->filters['address'] }}</span>
                    <button wire:click="setFilter('address', '')" wire:loading.attr="disabled"
                        class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
                </span>
            @endif
            @if ($this->filters['notes'] !== '')
                <span
                    class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                    <span class="font-semibold opacity-75">{{ __('merchant_panel.notes') }}:</span>
                    <span class="max-w-[14rem] truncate">{{ $this->filters['notes'] }}</span>
                    <button wire:click="setFilter('notes', '')" wire:loading.attr="disabled"
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
                    <span>{{ __('merchant.restore_all') }}</span>
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
                                    @foreach ($this->visibleColumns as $colKey)
                                        @include('livewire.merchant.orders.partials.orders-table-header', [
                                            'colKey' => $colKey,
                                        ])
                                    @endforeach
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
                                        $orderStatusTone =
                                            $this->tableStyle === 'status'
                                                ? 'edz-table-row--' .
                                                    ($order['status']['color'] ?? 'gray') .
                                                    ($orderSelected ? ' edz-row-selected' : '')
                                                : '';
                                    @endphp
                                    <tr data-order-id="{{ $orderId }}"
                                        wire:key="order-row-{{ $orderId }}"
                                        data-order-number="{{ $order['number'] ?? '' }}" x-data="orderRowActions($el)"
                                        class="{{ $this->tableStyle === 'status' ? '' : 'hover:bg-surface-tertiary/50 ' }}{{ $this->tableStyle !== 'status' && $orderSelected ? 'bg-accent-surface-subtle ' : '' }}{{ $orderStatusTone }}">
                                        <td class="px-3 py-3 w-10">
                                            <input type="checkbox" value="{{ $orderId }}"
                                                wire:click="toggleSelectOrder('{{ $orderId }}')"
                                                {{ in_array($orderId, $this->selectedOrders) ? 'checked' : '' }}
                                                class="rounded border-gray-300 text-accent-600 focus:ring-accent-500">
                                        </td>
                                        @foreach ($this->visibleColumns as $colKey)
                                            @include('livewire.merchant.orders.partials.orders-table-cell', [
                                                'order' => $order,
                                                'colKey' => $colKey,
                                                'orderId' => $orderId,
                                                'transitions' => $transitions,
                                            ])
                                        @endforeach
                                        <td class="px-4 py-3 text-right">
                                                @include('livewire.merchant.orders.partials.orders-table-actions-column', [
                                                    'orderId' => $orderId,
                                                    'order' => $order,
                                                    'transitions' => $transitions,
                                                    'showTrash' => $this->showTrash,
                                                    'layout' => 'compact',
                                                    'events' => true,
                                                ])
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
                                $orderStatusTone =
                                    $this->tableStyle === 'status'
                                        ? 'edz-table-row--' .
                                            ($order['status']['color'] ?? 'gray') .
                                            ($orderSelected ? ' edz-row-selected' : '')
                                        : '';
                            @endphp
                            <div data-order-id="{{ $orderId }}"
                                wire:key="order-card-{{ $orderId }}"
                                data-order-number="{{ $order['number'] ?? '' }}" x-data="orderRowActions($el)"
                                class="px-4 py-4 {{ $this->tableStyle !== 'status' && $orderSelected ? 'bg-accent-surface-subtle' : '' }} {{ $orderStatusTone }}">
                                <div class="flex items-start gap-3">
                                    <input type="checkbox" value="{{ $orderId }}"
                                        wire:click="toggleSelectOrder('{{ $orderId }}')"
                                        {{ in_array($orderId, $this->selectedOrders) ? 'checked' : '' }}
                                        class="mt-1 rounded border-gray-300 text-accent-600 focus:ring-accent-500 shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2">
                                            <span
                                                class="font-mono font-semibold text-ink">#{{ $order['number'] }}</span>
                                            <span
                                                class="text-xs text-ink-muted shrink-0">{{ \Carbon\Carbon::parse($order['created_at'])->format('M d, Y') }}</span>
                                        </div>
                                        @php
                                            $dupToneM = match ($order['dup_level'] ?? null) {
                                                'duplicate' => 'danger',
                                                'probable' => 'warning',
                                                'repeat' => 'neutral',
                                                default => null,
                                            };
                                            $dupLabelM = match ($order['dup_level'] ?? null) {
                                                'duplicate' => __('order_flow.dup_badge_duplicate'),
                                                'probable' => __('order_flow.dup_badge_probable'),
                                                'repeat' => __('order_flow.dup_badge_repeat'),
                                                default => null,
                                            };
                                            $dupCountM = (int) ($order['duplicate_count'] ?? $order['repeat_count'] ?? 0);
                                        @endphp
                                        <div class="mt-1 flex items-center gap-1.5 min-w-0">
                                            @if ($this->editingField === 'order.customer_name' && $this->editingId === $orderId)
                                                <div class="edz-inline-edit__edit w-full"
                                                    wire:key="name-inline-card-{{ $orderId }}">
                                                    <input type="text" wire:model="nameEditName"
                                                        wire:keydown.enter="saveOrderName"
                                                        placeholder="{{ __('merchant_panel.name') }}"
                                                        class="edz-inline-edit__input @if ($this->editingError) edz-inline-edit__input--error @endif">
                                                    <div class="edz-inline-edit__actions">
                                                        <button type="button" class="edz-inline-edit__save"
                                                            wire:click="saveOrderName" wire:loading.attr="disabled">
                                                            <span>Save</span>
                                                        </button>
                                                        <button type="button" class="edz-inline-edit__cancel"
                                                            wire:click="cancelOrderNameEdit">Cancel</button>
                                                    </div>
                                                    @if ($this->editingError)
                                                        <p class="edz-inline-edit__error">{{ $this->editingError }}</p>
                                                    @endif
                                                </div>
                                            @elseif (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
                                                <button type="button" class="edz-inline-edit__display min-w-0"
                                                    wire:click="startOrderNameEdit('{{ $orderId }}')"
                                                    title="{{ $order['customer']['name'] ?? '-' }}">
                                                    <span
                                                        class="edz-inline-edit__value text-sm">{{ $order['customer']['name'] ?? '-' }}</span>
                                                </button>
                                            @else
                                                <div class="text-sm font-medium text-ink truncate">
                                                    {{ $order['customer']['name'] ?? '-' }}</div>
                                            @endif
                                            @if (!$this->showTrash && ($order['status_key'] ?? null) !== 'duplicate' && $dupToneM)
                                                <button type="button"
                                                    wire:click="openDuplicateScan('{{ $orderId }}')"
                                                    title="{{ __('order_flow.duplicate_warnings_title') }}"
                                                    class="edz-badge edz-badge--{{ $dupToneM }} edz-badge--sm shrink-0 cursor-pointer transition hover:brightness-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-warning/40">
                                                    <x-edz.icon name="copy" class="w-3 h-3" />
                                                    {{ $dupLabelM }}@if (($order['dup_level'] ?? null) !== 'repeat')
                                                        أ—{{ min($dupCountM, 9) }}{{ $dupCountM > 9 ? '+' : '' }}
                                                    @endif
                                                </button>
                                            @endif
                                        </div>
                                        @if ($this->editingField === 'order.phone' && $this->editingId === $orderId)
                                            <div class="edz-inline-edit__edit mt-1"
                                                wire:key="phone-inline-card-{{ $orderId }}">
                                                <input type="tel" wire:model="phoneEditPhone"
                                                    wire:keydown.enter="saveOrderPhone"
                                                    placeholder="{{ __('merchant_panel.phone') }}"
                                                    class="edz-inline-edit__input @if ($this->editingError) edz-inline-edit__input--error @endif">
                                                <input type="tel" wire:model="phoneEditSecondary"
                                                    wire:keydown.enter="saveOrderPhone"
                                                    placeholder="{{ __('merchant_panel.phone_secondary') }}"
                                                    class="edz-inline-edit__input @if ($this->editingError) edz-inline-edit__input--error @endif">
                                                <div class="edz-inline-edit__actions">
                                                    <button type="button" class="edz-inline-edit__save"
                                                        wire:click="saveOrderPhone" wire:loading.attr="disabled">
                                                        <span>Save</span>
                                                    </button>
                                                    <button type="button" class="edz-inline-edit__cancel"
                                                        wire:click="cancelOrderPhoneEdit">Cancel</button>
                                                </div>
                                                @if ($this->editingError)
                                                    <p class="edz-inline-edit__error">
                                                        {{ $this->editingError }}</p>
                                                @endif
                                            </div>
                                        @elseif (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
                                            <button type="button" class="edz-inline-edit__display mt-1"
                                                wire:click="startOrderPhoneEdit('{{ $orderId }}')">
                                                <span class="edz-inline-edit__value" dir="ltr">
                                                    {{ $order['customer']['phone'] ?? '—' }}
                                                    @if (!empty($order['phone_secondary']))
                                                        <span class="text-ink-muted/60"> آ·
                                                            {{ $order['phone_secondary'] }}</span>
                                                    @endif
                                                </span>
                                            </button>
                                        @else
                                            <div class="text-xs text-ink-muted" dir="ltr">
                                                {{ $order['customer']['phone'] ?? '-' }}
                                                @if (!empty($order['phone_secondary']))
                                                    آ· {{ $order['phone_secondary'] }}
                                                @endif
                                            </div>
                                        @endif
                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            <div class="relative" @click.away="open = false">
                                                <button @click="openStatusMenu()" x-ref="trigger"
                                                    class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full cursor-pointer hover:opacity-80 {{ \Edzeery\MyStatusKit\Facades\Status::for('general', $order['status']['color'] ?? 'gray')->color() }}">
                                                    {!! \Edzeery\MyStatusKit\Facades\Status::for('order', $order['status']['key'] ?? 'default')->icon(
                                                        null,
                                                        'w-3.5 h-3.5 shrink-0',
                                                    ) !!}
                                                    {{ \Edzeery\MyStatusKit\Facades\Status::for('order', $order['status']['key'] ?? 'default')->label() }}
                                                    <x-edz.icon name="chevron-down" class="w-3 h-3" />
                                                </button>
                                                <div x-show="open" x-cloak
                                                    class="fixed inset-0 z-[205] bg-black/40 backdrop-blur-sm sm:hidden"
                                                    @click="open = false"></div>
                                                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                                                    x-transition:enter-start="opacity-0 translate-y-3"
                                                    x-transition:enter-end="opacity-100 translate-y-0"
                                                    x-transition:leave="transition ease-in duration-150"
                                                    x-transition:leave-start="opacity-100 translate-y-0"
                                                    x-transition:leave-end="opacity-0 translate-y-3"
                                                    :style="menuStyle"
                                                    class="fixed inset-x-0 bottom-0 z-[210] w-full rounded-t-2xl border border-b-0 border-surface-border bg-surface
                                                           p-3 pb-[calc(1rem+env(safe-area-inset-bottom))]
                                                           sm:inset-x-auto sm:bottom-auto sm:z-[200] sm:w-56 sm:rounded-xl sm:border-b sm:p-1.5 sm:pb-1.5
                                                           sm:shadow-lg shadow-[0_-16px_48px_-12px_rgba(15,23,42,.25)] max-h-[70vh] overflow-y-auto edz-scroll sm:max-h-64">
                                                    <span class="pointer-events-none mx-auto mb-2 block h-1 w-10 rounded-full bg-surface-border sm:hidden"></span>
                                                    <div class="flex items-center justify-between gap-2 px-1 mb-1.5 sm:hidden">
                                                        <p class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink uppercase tracking-wide">
                                                            <x-edz.icon name="chevron-down" class="w-3.5 h-3.5 text-ink-muted" />
                                                            <span>{{ __('merchant_panel.status') }}</span>
                                                        </p>
                                                        <button @click="open = false" type="button"
                                                            class="-m-1 p-1 rounded-lg text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                                                            title="{{ __('general.close') }}">
                                                            <x-edz.icon name="x-mark" class="w-4 h-4" />
                                                        </button>
                                                    </div>
                                                    @foreach ($this->allStatuses as $s)
                                                        @if (in_array($s['key'], $order['transitions'] ?? []) || $s['id'] == $order['status_id'])
                                                            <button
                                                                wire:click="transitionOrder('{{ $orderId }}', '{{ $s['key'] }}')"
                                                                wire:loading.attr="disabled" @click="open = false"
                                                                class="w-full text-left flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-tertiary disabled:opacity-50 {{ $s['id'] == $order['status_id'] ? 'font-bold' : '' }}">
                                                                {!! \Edzeery\MyStatusKit\Facades\Status::for('order', $s['key'] ?? 'default')->icon(null, 'w-3 h-3 shrink-0') !!}
                                                                <span class="w-2 h-2 rounded-full shrink-0"
                                                                    style="background: {{ \Edzeery\MyStatusKit\Facades\Status::for('general', $s['color'] ?? 'gray')->hex() }}"></span>
                                                                {{ \Edzeery\MyStatusKit\Facades\Status::for('order', $s['key'] ?? 'default')->label() }}
                                                            </button>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                            @if (in_array('notes', $this->visibleColumns))
                                                <div class="mt-2 w-full">
                                                    @if ($this->editingField === 'order.notes' && $this->editingId === $orderId)
                                                        <div class="edz-inline-edit__edit"
                                                            wire:key="notes-inline-card-{{ $orderId }}">
                                                            <textarea wire:model="editingValue"
                                                                wire:keydown.enter="saveOrderNotes"
                                                                rows="2" placeholder="{{ __('merchant_panel.notes') }}"
                                                                class="edz-inline-edit__input @if ($this->editingError) edz-inline-edit__input--error @endif"></textarea>
                                                            <div class="edz-inline-edit__actions">
                                                                <button type="button" class="edz-inline-edit__save"
                                                                    wire:click="saveOrderNotes"
                                                                    wire:loading.attr="disabled">
                                                                    <span>{{ __('buttons.save') }}</span>
                                                                </button>
                                                                <button type="button" class="edz-inline-edit__cancel"
                                                                    wire:click="cancelOrderEdit">{{ __('buttons.cancel') }}</button>
                                                            </div>
                                                            @if ($this->editingError)
                                                                <p class="edz-inline-edit__error">
                                                                    {{ $this->editingError }}</p>
                                                            @endif
                                                        </div>
                                                    @elseif (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
                                                        <button type="button"
                                                            class="edz-inline-edit__display w-full text-left"
                                                            wire:click="startOrderNotesEdit('{{ $orderId }}')"
                                                            title="{{ $order['notes'] ?? '' }}">
                                                            <x-edz.icon name="pencil-square" class="w-3 h-3 shrink-0" />
                                                            <span
                                                                class="edz-inline-edit__value break-words">{{ $order['notes'] ? $order['notes'] : '—' }}</span>
                                                        </button>
                                                    @else
                                                        <div class="text-xs text-ink-muted break-words">
                                                            {{ $order['notes'] ?? '-' }}</div>
                                                    @endif
                                                </div>
                                            @endif
                                            @if (in_array('source', $this->visibleColumns))
                                                @if (filled($order['created_by_membership_id']))
                                                    <x-edz.badge tone="neutral" sm>
                                                        <x-edz.icon name="user" class="w-3 h-3" />
                                                        {{ __('merchant.delivery_man') }}
                                                    </x-edz.badge>
                                                @else
                                                    <x-edz.badge tone="accent" sm>
                                                        <x-edz.icon name="shopping-bag" class="w-3 h-3" />
                                                        {{ __('merchant_panel.store') }}
                                                    </x-edz.badge>
                                                @endif
                                            @endif
                                            @if (in_array('wilaya', $this->visibleColumns))
                                                @if ($this->editingField === 'order.wilaya' && $this->editingId === $orderId)
                                                    <div class="edz-inline-edit__edit w-full min-w-[180px]"
                                                        wire:key="wilaya-inline-card-{{ $orderId }}">
                                                        <select wire:change="saveOrderWilaya($event.target.value)"
                                                            class="edz-inline-edit__input @if ($this->editingError) edz-inline-edit__input--error @endif">
                                                            @foreach ($this->allStates as $st)
                                                                <option value="{{ $st['id'] }}"
                                                                    @if ((string) $this->editingValue === (string) $st['id']) selected @endif>
                                                                    {{ $st['name'] }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <div class="edz-inline-edit__actions">
                                                            <button type="button" class="edz-inline-edit__cancel"
                                                                @click="$wire.cancelOrderEdit()">Cancel</button>
                                                        </div>
                                                        @if ($this->editingError)
                                                            <p class="edz-inline-edit__error">
                                                                {{ $this->editingError }}</p>
                                                        @endif
                                                    </div>
                                                @elseif (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
                                                    <button type="button" class="edz-inline-edit__display"
                                                        @click="$wire.startOrderWilayaEdit('{{ $orderId }}')">
                                                        <span
                                                            class="edz-inline-edit__value">{{ $order['state']['name'] ?? '—' }}</span>
                                                    </button>
                                                @else
                                                    <span
                                                        class="text-xs text-ink-muted">{{ $order['state']['name'] ?? '-' }}</span>
                                                @endif
                                            @endif
                                            @if (in_array('total', $this->visibleColumns))
                                                <span
                                                    class="text-sm font-semibold text-ink ms-auto tabular-nums">{{ currency($order['display_total'] ?? $order['total_amount'] ?? 0) }}</span>
                                            @endif
                                            @if (in_array('shipping_cost', $this->visibleColumns) && !in_array('total', $this->visibleColumns))
                                                <span
                                                    class="text-xs text-ink-muted ms-auto inline-flex items-center gap-1">{{ __('merchant_panel.shipping_cost') }}:
                                                    @if ((float) ($order['shipping_cost'] ?? 0) <= 0)
                                                        <x-edz.badge tone="neutral" sm>
                                                            <x-edz.icon name="truck" class="w-3 h-3" />
                                                            {{ __('merchant_panel.shipping_free') }}</x-edz.badge>
                                                    @else
                                                        <span
                                                            class="tabular-nums">{{ currency((float) ($order['shipping_cost'] ?? 0)) }}</span>
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                        <div class="mt-3 flex items-center gap-2 flex-wrap">
                                            <button wire:click="openOrderDetails('{{ $orderId }}')"
                                                class="edz-btn edz-btn--ghost edz-btn--xs"
                                                title="{{ __('merchant.order_details') }}"
                                                wire:loading.attr="disabled"
                                                wire:target="openOrderDetails('{{ $orderId }}')">
                                                <x-edz.icon name="info-circle" class="w-4 h-4" />
                                            </button>
                                            @include('livewire.merchant.orders.partials.order-events-menu', [
                                                'orderId' => $orderId,
                                                'order' => $order,
                                                'canViewEvents' => $order['can_view_events'] ?? false,
                                            ])

                                            {{-- Overflow actions popover (P29.7): keeps the card tidy with 44px touch rows --}}
                                            <div class="relative shrink-0" x-data="orderMoreMenu($el)"
                                                @click.away="close()">
                                                <button @click="toggle()" x-ref="moreTrigger"
                                                    class="edz-btn edz-btn--ghost edz-btn--xs shrink-0"
                                                    title="{{ __('general.more') }}">
                                                    <x-edz.icon name="ellipsis-horizontal" class="w-4 h-4" />
                                                </button>
                                                <div x-show="open" x-cloak
                                                    class="fixed inset-0 z-[205] bg-black/40 backdrop-blur-sm sm:hidden"
                                                    @click="close()"></div>
                                                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                                                    x-transition:enter-start="opacity-0 translate-y-3"
                                                    x-transition:enter-end="opacity-100 translate-y-0"
                                                    x-transition:leave="transition ease-in duration-150"
                                                    x-transition:leave-start="opacity-100 translate-y-0"
                                                    x-transition:leave-end="opacity-0 translate-y-3"
                                                    :style="menuStyle"
                                                    class="fixed inset-x-0 bottom-0 z-[210] w-full rounded-t-2xl border border-b-0 border-surface-border bg-surface
                                                           p-3 pb-[calc(1rem+env(safe-area-inset-bottom))]
                                                           sm:inset-x-auto sm:bottom-auto sm:w-60 sm:rounded-xl sm:border-b sm:p-1.5 sm:pb-1.5
                                                           sm:shadow-lg shadow-[0_-16px_48px_-12px_rgba(15,23,42,.25)] max-h-[70vh] overflow-y-auto edz-scroll">
                                                    <span class="pointer-events-none mx-auto mb-2 block h-1 w-10 rounded-full bg-surface-border sm:hidden"></span>
                                                    <div class="flex items-center justify-between gap-2 px-1 mb-1.5 sm:hidden">
                                                        <p class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink uppercase tracking-wide">
                                                            <x-edz.icon name="ellipsis-horizontal" class="w-3.5 h-3.5 text-ink-muted" />
                                                            <span>{{ __('merchant_panel.actions') }}</span>
                                                        </p>
                                                        <button @click="close()" type="button"
                                                            class="-m-1 p-1 rounded-lg text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                                                            title="{{ __('general.close') }}">
                                                            <x-edz.icon name="x-mark" class="w-4 h-4" />
                                                        </button>
                                                    </div>
                                                    @include('livewire.merchant.orders.partials.orders-table-actions-column', [
                                                        'orderId' => $orderId,
                                                        'order' => $order,
                                                        'transitions' => $order['transitions'] ?? [],
                                                        'showTrash' => $this->showTrash,
                                                        'layout' => 'list',
                                                    ])
                                                </div>
                                            </div>
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
                            wire:loading.attr="disabled" wire:loading.class="opacity-50 pointer-events-none">
                            <span>{{ __('merchant_panel.reassign') }}</span>
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
                    <div
                        class="inline-flex w-full sm:w-auto items-center gap-1 p-1 bg-surface-secondary rounded-xl mb-5">
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
                            $settingsColumns = collect($this->orderColumns())
                                ->filter(fn($col) => $this->columnAllowedForUser($col))
                                ->values()
                                ->all();
                            $settingsAllKeys = collect($settingsColumns)->pluck('key')->all();
                            $settingsRequiredKeys = collect($settingsColumns)->where('required', true)->pluck('key')->all();
                            $settingsVisibleDraft = array_values(array_intersect($this->draftColumns, $settingsAllKeys));
                            $settingsSortedAll = array_merge(
                                $settingsVisibleDraft,
                                collect($settingsColumns)->pluck('key')->reject(fn($k) => in_array($k, $settingsVisibleDraft, true))->values()->all(),
                            );
                        @endphp

                        <div>
                            <p class="text-xs font-semibold text-ink-muted uppercase tracking-wide mb-2">
                                {{ __('merchant_panel.columns') }}</p>
                            <p class="text-xs text-ink-muted mb-2">{{ __('merchant_panel.primary_columns_hint') }}</p>
                            <p class="text-xs text-ink-muted mb-2">{{ __('merchant_panel.column_order_hint') }}</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5"
                                x-data="orderColumnReorderDraft()"
                                @dragstart="onDragStart($event)"
                                @dragover="onDragOver($event)"
                                @drop="onDrop($event)"
                                @dragleave="onDragLeave($event)"
                                @dragend="onDragEnd()">
                                @foreach ($settingsSortedAll as $settingsKey)
                                    @php
                                        $settingsCol = collect($settingsColumns)->firstWhere('key', $settingsKey);
                                        $settingsIsChecked = in_array($settingsKey, $settingsVisibleDraft, true);
                                        $settingsIndex = array_search($settingsKey, $settingsVisibleDraft, true);
                                        $settingsPos = $settingsIndex !== false ? $settingsIndex + 1 : null;
                                        $settingsCanUp = $settingsIndex !== false && $settingsIndex > 0;
                                        $settingsCanDown = $settingsIndex !== false && $settingsIndex < count($settingsVisibleDraft) - 1;
                                        $settingsIsRequired = in_array($settingsKey, $settingsRequiredKeys, true);
                                    @endphp
                                    <label
                                        class="flex items-center gap-2 px-2.5 py-2 rounded-lg border border-surface-border hover:bg-surface-secondary cursor-pointer text-sm {{ $settingsIsRequired ? 'bg-surface-secondary/60' : '' }}"
                                        data-col-key="{{ $settingsKey }}"
                                        data-col-row="true">
                                        <span class="cursor-grab text-ink-muted hover:text-ink shrink-0 opacity-70"
                                            draggable="true"
                                            title="{{ __('merchant_panel.drag_to_reorder') }}">
                                            <x-edz.icon name="bars-2" class="w-4 h-4" />
                                        </span>
                                        <input type="checkbox" wire:click="toggleDraftColumn('{{ $settingsKey }}')"
                                            {{ $settingsIsChecked ? 'checked' : '' }}
                                            @disabled($settingsIsRequired)
                                            class="rounded border-gray-300 text-accent-600 focus:ring-accent-500 {{ $settingsIsRequired ? 'opacity-70' : '' }}">
                                        @if ($settingsIsRequired)
                                            <x-edz.icon name="lock-closed" class="w-3.5 h-3.5 shrink-0 text-ink-muted"
                                                title="{{ __('merchant_panel.primary_columns') }}" />
                                        @endif
                                        <span class="flex-1 min-w-0 truncate">
                                            {{ __("merchant_panel.{$settingsCol['label_key']}") }}
                                            @if ($settingsIsRequired)
                                                <span class="text-[10px] text-ink-muted font-normal">
                                                    ({{ __('merchant_panel.always_visible') }})</span>
                                            @endif
                                        </span>
                                        @if ($settingsIsChecked)
                                            <span class="text-[10px] text-ink-muted tabular-nums shrink-0" wire:key="col-order-{{ $settingsKey }}">
                                                {{ $settingsPos }}
                                            </span>
                                            <span class="flex items-center gap-0.5 shrink-0">
                                                <button type="button" title="Up"
                                                    wire:click="moveDraftColumn('{{ $settingsKey }}', 'up')"
                                                    @disabled(!$settingsCanUp)
                                                    class="p-1 rounded text-ink-muted hover:text-ink hover:bg-surface-tertiary disabled:opacity-30 disabled:cursor-not-allowed">
                                                    <x-edz.icon name="arrow-up" class="w-3.5 h-3.5" />
                                                </button>
                                                <button type="button" title="Down"
                                                    wire:click="moveDraftColumn('{{ $settingsKey }}', 'down')"
                                                    @disabled(!$settingsCanDown)
                                                    class="p-1 rounded text-ink-muted hover:text-ink hover:bg-surface-tertiary disabled:opacity-30 disabled:cursor-not-allowed">
                                                    <x-edz.icon name="arrow-down" class="w-3.5 h-3.5" />
                                                </button>
                                            </span>
                                        @endif
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
                                    <p class="text-sm font-semibold text-ink">
                                        {{ __('merchant_panel.style_default') }}</p>
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
                                    <p class="text-sm font-semibold text-ink">{{ __('merchant_panel.style_status') }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-ink-muted">
                                        {{ __('merchant_panel.style_status_hint') }}</p>
                                    <div
                                        class="mt-2 rounded-lg overflow-hidden border border-surface-border text-[10px]">
                                        <table class="w-full">
                                            <tbody>
                                                <tr class="edz-table-row--success">
                                                    <td class="px-2.5 py-1.5 font-semibold">#1001</td>
                                                    <td class="px-2.5 py-1.5">{{ __('merchant_panel.style_status') }}
                                                    </td>
                                                </tr>
                                                <tr class="edz-table-row--warning">
                                                    <td class="px-2.5 py-1.5 font-semibold">#1002</td>
                                                    <td class="px-2.5 py-1.5">{{ __('merchant_panel.style_status') }}
                                                    </td>
                                                </tr>
                                                <tr class="edz-table-row--danger">
                                                    <td class="px-2.5 py-1.5 font-semibold">#1003</td>
                                                    <td class="px-2.5 py-1.5">{{ __('merchant_panel.style_status') }}
                                                    </td>
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
                    <div
                        class="flex flex-wrap items-center justify-between gap-3 pt-5 mt-6 border-t border-surface-border">
                        <button type="button" wire:click="resetColumns"
                            class="edz-btn edz-btn--ghost edz-btn--sm">{{ __('merchant_panel.reset_columns') }}</button>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="discardTableSettings"
                                class="edz-btn edz-btn--ghost edz-btn--sm">{{ __('merchant_panel.cancel') }}</button>
                            <button wire:click="saveTableSettings" class="edz-btn edz-btn--primary edz-btn--sm"
                                wire:loading.attr="disabled" wire:loading.class="opacity-50 pointer-events-none">
                                <span>{{ __('merchant_panel.save_settings') }}</span>
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
                $detailsStatus = \Edzeery\MyStatusKit\Facades\Status::for(
                    'order',
                    $detailsOrder['status']['key'] ?? 'default',
                );
                $detailsTracking = $detailsOrder['tracking'] ?? null;
            @endphp
            <div @edz-modal-closed.window="$wire.closeOrderDetails()">
                <x-edz.modal  :isOpen="true" size="md" wire:key="order-details-modal">
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
                            $showItems =
                                !in_array('products', $this->visibleColumns) ||
                                !in_array('total', $this->visibleColumns);
                            $showShipping =
                                !in_array('delivery_type', $this->visibleColumns) ||
                                !in_array('shipment_type', $this->visibleColumns) ||
                                !in_array('weight', $this->visibleColumns) ||
                                !in_array('stopdesk_point', $this->visibleColumns) ||
                                !in_array('send_from_carrier_warehouse', $this->visibleColumns);
                            $showContact =
                                !in_array('address', $this->visibleColumns) ||
                                !in_array('city', $this->visibleColumns) ||
                                !in_array('meta', $this->visibleColumns);
                            $showAssignment =
                                !in_array('assigned_agent', $this->visibleColumns) ||
                                !in_array('confirmation_attempts', $this->visibleColumns) ||
                                !in_array('last_contact', $this->visibleColumns) ||
                                !in_array('notes', $this->visibleColumns) ||
                                !in_array('confirmed_by', $this->visibleColumns);
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
                                                    <span class="text-ink-muted">أ—{{ $item['qty'] }}</span></span>
                                                <span
                                                    class="font-medium text-ink shrink-0">{{ currency($item['price'] * $item['qty']) }}</span>
                                            </div>
                                        @empty
                                            <div class="px-3 py-2 text-ink-muted text-xs">
                                                {{ __('merchant_panel.no_orders_found') }}</div>
                                        @endforelse
                                    @endif
                                    @if (!in_array('total', $this->visibleColumns))
                                        <div
                                            class="flex items-center justify-between gap-3 px-3 py-2.5 bg-surface font-bold text-ink">
                                            <span>{{ __('merchant_panel.total') }}</span>
                                            <span class="tabular-nums">{{ currency($detailsOrder['display_total'] ?? $detailsOrder['total_amount'] ?? 0) }}</span>
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
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.delivery') }}
                                            </dt>
                                            <dd class="text-ink text-end">
                                                {{ $detailsOrder['delivery_type'] === 'stopdesk' ? __('merchant_panel.stop_desk_label') : ($detailsOrder['delivery_type'] === 'home' ? __('merchant_panel.home_delivery_label') : $detailsOrder['delivery_type'] ?? '—') }}
                                            </dd>
                                        </div>
                                    @endif
                                    @if (!in_array('shipment_type', $this->visibleColumns))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.shipment') }}
                                            </dt>
                                            <dd class="text-ink text-end capitalize">
                                                {{ $detailsOrder['shipment_type'] ?? '—' }}</dd>
                                        </div>
                                    @endif
                                    <div class="flex items-start justify-between gap-3 px-3 py-2">
                                        <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.payment_method') }}
                                        </dt>
                                        <dd class="text-ink text-end uppercase">
                                            {{ $detailsOrder['payment_method'] ?? '—' }}</dd>
                                    </div>
                                    @if (!in_array('weight', $this->visibleColumns))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.weight') }}
                                            </dt>
                                            <dd class="text-ink text-end">
                                                {{ $detailsOrder['weight_kg'] ? $detailsOrder['weight_kg'] . ' kg' : '—' }}
                                            </dd>
                                        </div>
                                    @endif
                                    @if (!in_array('stopdesk_point', $this->visibleColumns) && !empty($detailsOrder['stopdesk_point']))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">
                                                {{ __('merchant_panel.stopdesk_point') }}</dt>
                                            <dd class="text-ink text-end">
                                                {{ $detailsOrder['stopdesk_point']['name'] ?? '—' }}@if (!empty($detailsOrder['stopdesk_point']['city']['name']))
                                                    ({{ $detailsOrder['stopdesk_point']['city']['name'] }})
                                                @endif
                                            </dd>
                                        </div>
                                    @endif
                                    @if (!in_array('send_from_carrier_warehouse', $this->visibleColumns))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">
                                                {{ __('merchant_panel.send_from_carrier_warehouse') }}</dt>
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
                                            <dt class="text-ink-muted shrink-0">
                                                {{ __('merchant_panel.phone_secondary') }}</dt>
                                            <dd class="text-ink text-end" dir="ltr">
                                                {{ $detailsOrder['phone_secondary'] ?? '—' }}</dd>
                                        </div>
                                    @endif
                                    @if (!in_array('city', $this->visibleColumns))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.city') }}</dt>
                                            <dd class="text-ink text-end">{{ $detailsOrder['city']['name'] ?? '—' }}
                                            </dd>
                                        </div>
                                    @endif
                                    @if (!in_array('address', $this->visibleColumns))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.address') }}
                                            </dt>
                                            <dd class="text-ink text-end min-w-0">
                                                {{ $detailsOrder['address'] ? \Illuminate\Support\Str::limit($detailsOrder['address'], 60) : '—' }}
                                            </dd>
                                        </div>
                                    @endif
                                    @if (!in_array('meta', $this->visibleColumns) && !empty($detailsOrder['meta']))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.meta') }}</dt>
                                            <dd class="text-ink text-end min-w-0">
                                                {{ collect($detailsOrder['meta'])->map(fn($v, $k) => "{$k}: {$v}")->implode(', ') }}
                                            </dd>
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
                                            <dd class="text-ink text-end">
                                                {{ $detailsOrder['assigned_membership']['user']['name'] ?? '—' }}</dd>
                                        </div>
                                    @endif
                                    <div class="flex items-start justify-between gap-3 px-3 py-2">
                                        <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.method') }}</dt>
                                        <dd class="text-ink text-end">
                                            {{ $detailsOrder['assignment_method'] ? ucfirst($detailsOrder['assignment_method']) : '—' }}
                                        </dd>
                                    </div>
                                    <div class="flex items-start justify-between gap-3 px-3 py-2">
                                        <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.created_by') }}
                                        </dt>
                                        <dd class="text-ink text-end">
                                            {{ $detailsOrder['created_by_membership_id'] ? $detailsOrder['created_by_membership']['user']['name'] ?? '—' : '—' }}
                                        </dd>
                                    </div>
                                    @if (!in_array('confirmed_by', $this->visibleColumns))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">
                                                {{ __('merchant_panel.confirmed_by') }}</dt>
                                            <dd class="text-ink text-end">
                                                {{ $detailsOrder['confirmed_by_history']['changed_by']['user']['name'] ?? '—' }}
                                            </dd>
                                        </div>
                                    @endif
                                    @if (!in_array('confirmation_attempts', $this->visibleColumns))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.attempts') }}
                                            </dt>
                                            <dd class="text-ink text-end">
                                                {{ $detailsOrder['confirmation_attempts'] ?? 0 }}</dd>
                                        </div>
                                    @endif
                                    @if (!in_array('last_contact', $this->visibleColumns))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">
                                                {{ __('merchant_panel.last_contact') }}</dt>
                                            <dd class="text-ink text-end">
                                                {{ $detailsOrder['last_contact_at'] ? \Carbon\Carbon::parse($detailsOrder['last_contact_at'])->diffForHumans() : '—' }}
                                            </dd>
                                        </div>
                                    @endif
                                </dl>
                                @if (!in_array('notes', $this->visibleColumns) && !empty($detailsOrder['notes']))
                                    <div
                                        class="mt-2 p-2.5 rounded-lg bg-surface-tertiary text-sm text-ink-muted italic">
                                        "{{ $detailsOrder['notes'] }}"
                                    </div>
                                @endif
                            </section>
                        @endif

                        {{-- Tracking --}}
                        @if (
                            $detailsTracking &&
                                (!in_array('shipping_provider', $this->visibleColumns) ||
                                    !empty($detailsTracking['tracking_number']) ||
                                    !empty($detailsTracking['shipped_at']) ||
                                    !empty($detailsTracking['delivered_at'])))
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
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.carrier') }}
                                            </dt>
                                            <dd class="text-ink text-end">{{ $detailsTracking['shipping_provider'] }}
                                            </dd>
                                        </div>
                                    @endif
                                    @if (!empty($detailsTracking['tracking_number']))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">
                                                {{ __('merchant_panel.tracking_number') }}</dt>
                                            <dd class="text-ink text-end font-mono">
                                                {{ $detailsTracking['tracking_number'] }}</dd>
                                        </div>
                                    @endif
                                    @if (!empty($detailsTracking['shipped_at']))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.shipped_at') }}
                                            </dt>
                                            <dd class="text-ink text-end">{{ $detailsTracking['shipped_at'] }}</dd>
                                        </div>
                                    @endif
                                    @if (!empty($detailsTracking['delivered_at']))
                                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                                            <dt class="text-ink-muted shrink-0">
                                                {{ __('merchant_panel.delivered_at') }}</dt>
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

    @include('livewire.merchant.orders.partials.delivery-edit-modal')

    {{-- Confirmation Drawer (P26) --}}
    @if (canStore(StorePermissionEnum::ORDER_CONFIRM->value) || canStore(StorePermissionEnum::ORDER_MANAGE->value))
        <x-edz.modal :is-open="$showConfirmModal" @close="$wire.closeConfirmModal()" size="lg"
            show-close-button>
            <div class="p-5">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-lg font-semibold text-ink">
                            {{ __('order_flow.confirm_title') }}
                            @if ($this->confirmSummary)
                                <span class="text-ink-muted font-normal">#{{ $this->confirmSummary['number'] }}</span>
                            @endif
                        </h3>
                        <p class="text-sm text-ink-muted mt-0.5">{{ __('order_flow.confirm_summary') }}</p>
                    </div>
                </div>

                @if ($this->confirmSummary)
                    <dl
                        class="rounded-xl border border-surface-border divide-y divide-surface-border overflow-hidden bg-surface-tertiary/30 text-sm">
                        <div class="flex items-center justify-between gap-3 px-3 py-2">
                            <dt class="text-ink-muted">{{ __('merchant_panel.customer') }}</dt>
                            <dd class="text-ink text-end font-medium">{{ $this->confirmSummary['customer'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 px-3 py-2">
                            <dt class="text-ink-muted">{{ __('merchant_panel.total') }}</dt>
                            <dd class="text-ink text-end font-bold">{{ $this->confirmSummary['total'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 px-3 py-2">
                            <dt class="text-ink-muted">{{ __('merchant_panel.status') }}</dt>
                            <dd class="text-ink text-end">{{ $this->confirmSummary['status'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 px-3 py-2">
                            <dt class="text-ink-muted">{{ __('order_flow.confirm_partner') }}</dt>
                            <dd class="text-ink text-end">{{ $this->confirmSummary['partner'] }}</dd>
                        </div>
                    </dl>
                @endif

                @if (!empty($this->duplicateWarnings))
                    <div class="mt-4 rounded-xl border border-warning/40 bg-warning/5 p-3">
                        <div class="flex items-center gap-2 text-warning mb-2">
                            <x-edz.icon name="exclamation-triangle" class="w-4 h-4" />
                            <span class="text-sm font-medium">
                                {{ __('order_flow.duplicate_detected', ['count' => count($this->duplicateWarnings)]) }}
                            </span>
                        </div>
                        <ul class="space-y-1.5 text-sm">
                            @foreach ($this->duplicateWarnings as $dup)
                                <li class="flex items-center justify-between gap-2">
                                    <span class="text-ink truncate">
                                        #{{ $dup['number'] }}
                                        <span class="text-ink-muted">• {{ \Carbon\Carbon::parse($dup['created_at'])->diffForHumans() }}</span>
                                    </span>
                                    <span class="shrink-0 text-xs text-ink-muted">
                                        أ—{{ $dup['total_overlap_qty'] }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                        @if ($this->confirmOrderId)
                            <button wire:click="markOrderDuplicate('{{ $this->confirmOrderId }}')" type="button"
                                class="mt-3 edz-btn edz-btn--ghost edz-btn--sm">
                                <x-edz.icon name="copy" class="w-3.5 h-3.5" />
                                {{ __('order_flow.mark_as_duplicate') }}
                            </button>
                        @endif
                    </div>
                @elseif ($this->confirmOrderId)
                    <div class="mt-4 flex items-center gap-2 text-xs text-ink-muted">
                        <x-edz.icon name="check-circle" class="w-4 h-4 text-success" />
                        {{ __('order_flow.no_duplicates') }}
                    </div>
                @endif

                <div class="mt-5">
                    <h4 class="text-xs font-semibold text-ink-muted uppercase tracking-wide mb-2">
                        {{ __('order_flow.confirm_partner') }}
                    </h4>
                    <x-edz.select wire:model="confirmProviderId"
                        :options="$allProviders"
                        option-value="id" option-label="name" searchable
                        placeholder="{{ __('order_flow.confirm_provider_placeholder') }}" />
                </div>

                <div class="mt-5 flex items-center justify-between gap-4 rounded-xl border border-surface-border p-3">
                    <div class="flex items-center gap-2 text-sm text-ink">
                        <x-edz.icon name="phone" class="w-4 h-4 text-ink-muted" />
                        {{ __('order_flow.confirm_contacted') }}
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="confirmContacted" class="sr-only peer">
                        <div
                            class="w-10 h-6 bg-surface-tertiary rounded-full peer-checked:bg-accent-600 transition"></div>
                        <div
                            class="absolute left-1 top-0.5 w-5 h-5 bg-white rounded-full shadow transition peer-checked:translate-x-4">
                        </div>
                    </label>
                </div>

                <div class="mt-6 flex flex-col-reverse sm:flex-row gap-2 justify-end">
                    <button wire:click="closeConfirmModal" type="button"
                        class="edz-btn edz-btn--ghost">
                        {{ __('buttons.cancel') }}
                    </button>
                    @if (canStore(StorePermissionEnum::ORDER_CONFIRM->value))
                        <button wire:click="submitConfirmOnly" type="button"
                            class="edz-btn edz-btn--ghost"
                            wire:loading.attr="disabled">
                            <span>{{ __('order_flow.confirm_only') }}</span>
                        </button>
                    @endif
                    @if (canStore(StorePermissionEnum::ORDER_MANAGE->value))
                        <button wire:click="submitConfirmAndSend" type="button"
                            class="edz-btn edz-btn--primary"
                            wire:loading.attr="disabled">
                            <x-edz.icon name="truck" class="w-4 h-4" />
                            <span>{{ __('order_flow.confirm_and_send') }}</span>
                        </button>
                    @endif
                </div>
            </div>
        </x-edz.modal>
    @endif

    {{-- Bulk Status Change (P29) --}}
    @if (canStore(StorePermissionEnum::ORDER_MANAGE->value))
        <x-edz.modal :is-open="$showBulkStatusModal" @close="$wire.closeBulkStatusModal()" size="md"
            show-close-button>
            <div class="p-5">
                <h3 class="text-lg font-semibold text-ink mb-4">{{ __('order_flow.bulk_status_title') }}</h3>

                <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">
                    {{ __('order_flow.bulk_status_target') }}
                </label>
                <select wire:model="bulkStatusTarget"
                    class="edz-input w-full">
                    <option value="">—</option>
                    @foreach ($this->allStatuses as $s)
                        @if (!in_array($s['key'] ?? '', ['cancelled', 'canceled', 'confirmed'], true))
                            <option value="{{ $s['key'] }}">
                                {{ \Edzeery\MyStatusKit\Facades\Status::for('order', $s['key'] ?? 'default')->label() }}
                            </option>
                        @endif
                    @endforeach
                </select>

                <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mt-4 mb-1.5">
                    {{ __('order_flow.bulk_status_reason') }}
                </label>
                <textarea wire:model="bulkStatusReason" rows="2"
                    class="edz-input w-full"
                    placeholder="{{ __('order_flow.bulk_status_reason_placeholder') }}"></textarea>

                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="closeBulkStatusModal" type="button"
                        class="edz-btn edz-btn--ghost">
                        {{ __('buttons.cancel') }}
                    </button>
                    <button wire:click="submitBulkStatus" type="button"
                        class="edz-btn edz-btn--primary"
                        wire:loading.attr="disabled">
                        <span>{{ __('buttons.save') }}</span>
                    </button>
                </div>
            </div>
        </x-edz.modal>
    @endif

    {{-- Bulk Send to Carrier (P29.3) --}}
    @if (canStore(StorePermissionEnum::ORDER_MANAGE->value))
        <x-edz.modal :is-open="$showBulkSendModal" @close="$wire.closeBulkSendModal()" size="md"
            show-close-button>
            <div class="p-5">
                <h3 class="text-lg font-semibold text-ink mb-1">{{ __('order_flow.bulk_send_summary_title') }}</h3>
                <p class="text-xs text-ink-muted mb-4">{{ __('order_flow.bulk_send_summary_subtitle') }}</p>

                @if (empty($this->bulkSendSummary) && $this->bulkSendSkipCount === 0)
                    <p class="text-sm text-ink-muted">{{ __('order_flow.bulk_send_no_groups') }}</p>
                @else
                    @if (! empty($this->bulkSendSummary))
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-ink-muted mb-2">
                            {{ __('order_flow.bulk_send_ready_title') }}
                        </h4>
                        <ul class="space-y-2 mb-4">
                            @foreach ($this->bulkSendSummary as $g)
                                <li class="flex items-center justify-between gap-2 rounded-lg border border-surface-border bg-surface-secondary px-3 py-2">
                                    <span class="text-sm font-medium text-ink">{{ $g['name'] }}</span>
                                    <span class="text-xs font-semibold text-ink-muted">{{ __('order_flow.bulk_send_group_count', ['count' => $g['count']]) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($this->bulkSendSkipCount > 0)
                        <x-edz.alert type="warning">
                            <p class="font-semibold mb-1">{{ __('order_flow.bulk_send_skipped_title', ['count' => $this->bulkSendSkipCount]) }}</p>
                            <ul class="space-y-1 max-h-40 overflow-y-auto edz-scroll">
                                @foreach (collect($this->bulkSendAnalysis)->where('ready', false) as $entry)
                                    <li class="leading-relaxed break-words">
                                        #{{ $entry['number'] }} — {{ implode('ط› ', $entry['reasons']) }}
                                    </li>
                                @endforeach
                            </ul>
                        </x-edz.alert>
                    @endif
                @endif

                <div class="mt-6 flex flex-col sm:flex-row sm:justify-end gap-2">
                    <button wire:click="closeBulkSendModal" type="button"
                        class="edz-btn edz-btn--ghost">
                        {{ __('buttons.cancel') }}
                    </button>
                    @if ($this->bulkSendSkipCount === 0)
                        <button wire:click="confirmBulkSend" type="button"
                            class="edz-btn edz-btn--primary"
                            wire:loading.attr="disabled">
                            <span>{{ __('order_flow.bulk_send_confirm') }}</span>
                        </button>
                    @elseif ($this->bulkSendReadyCount > 0)
                        <button wire:click="confirmBulkSend" type="button"
                            class="edz-btn edz-btn--primary"
                            wire:loading.attr="disabled">
                            <span>{{ __('order_flow.bulk_send_confirm_some', ['count' => $this->bulkSendReadyCount]) }}</span>
                        </button>
                    @else
                        <button type="button" disabled
                            class="edz-btn edz-btn--primary opacity-50 cursor-not-allowed">
                            {{ __('order_flow.bulk_send_confirm_none') }}
                        </button>
                    @endif
                </div>
            </div>
        </x-edz.modal>
    @endif

    {{-- Duplicate scan popup (P29.6): lazy — computed on click via openDuplicateScan() --}}
    @if ($this->showDuplicateScanModal)
        <div @edz-modal-closed.window="$wire.closeDuplicateScanModal()">
            <x-edz.modal :isOpen="true" size="md" wire:key="duplicate-scan-modal">
                <div class="p-6">
                    @php
                        $scanTone = match ($this->duplicateScanLevel) {
                            'duplicate' => 'danger',
                            'probable' => 'warning',
                            'repeat' => 'neutral',
                            default => null,
                        };
                        $scanLabel = match ($this->duplicateScanLevel) {
                            'duplicate' => __('order_flow.dup_badge_duplicate'),
                            'probable' => __('order_flow.dup_badge_probable'),
                            'repeat' => __('order_flow.dup_badge_repeat'),
                            default => null,
                        };
                    @endphp
                    <div class="flex items-start gap-3">
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-warning/10 text-warning shrink-0">
                            <x-edz.icon name="copy" class="w-5 h-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base sm:text-lg font-bold text-ink">
                                {{ __('order_flow.duplicate_warnings_title') }}
                            </h3>
                            @if ($this->duplicateScanNumber)
                                <p class="mt-0.5 text-xs text-ink-muted dark:text-ink-muted" dir="ltr">
                                    #{{ $this->duplicateScanNumber }}</p>
                            @endif
                            @if ($scanTone)
                                <span
                                    class="mt-2 inline-flex items-center gap-1 edz-badge edz-badge--{{ $scanTone }} edz-badge--sm">
                                    <x-edz.icon name="copy" class="w-3 h-3" />
                                    {{ $scanLabel }}
                                </span>
                            @endif
                        </div>
                    </div>

                    @if (!empty($this->duplicateScanResults))
                        <p class="mt-4 text-sm text-ink">
                            {{ __('order_flow.duplicate_detected', ['count' => count($this->duplicateScanResults)]) }}
                        </p>
                        <ul class="mt-2 space-y-1.5 text-sm">
                            @foreach ($this->duplicateScanResults as $dup)
                                <li class="flex items-center justify-between gap-2 rounded-lg border border-surface-border bg-surface-tertiary/40 px-3 py-2">
                                    <button type="button"
                                        wire:click="openOrderDetails('{{ $dup['order_id'] }}')"
                                        class="flex items-center gap-2 text-ink hover:text-brand-600 truncate text-start">
                                        <span class="truncate">
                                            #{{ $dup['number'] }}
                                            <span class="text-ink-muted">• {{ \Carbon\Carbon::parse($dup['created_at'])->diffForHumans() }}</span>
                                        </span>
                                    </button>
                                    <span class="shrink-0 text-xs text-ink-muted">
                                        أ—{{ $dup['total_overlap_qty'] }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @elseif (($this->duplicateScanPhoneCount ?? 0) > 0)
                        <div
                            class="mt-4 flex items-start gap-2 rounded-xl border border-surface-border bg-surface-tertiary/40 p-3 text-xs text-ink-muted">
                            <x-edz.icon name="info-circle" class="w-4 h-4 shrink-0 text-brand" />
                            <span>
                                {{ __('order_flow.duplicate_phone_only_orders', ['count' => $this->duplicateScanPhoneCount]) }}
                            </span>
                        </div>
                    @elseif (($this->duplicateScanRepeatCount ?? 0) > 0)
                        <div
                            class="mt-4 flex items-start gap-2 rounded-xl border border-surface-border bg-surface-tertiary/40 p-3 text-xs text-ink-muted">
                            <x-edz.icon name="info-circle" class="w-4 h-4 shrink-0 text-brand" />
                            <span>
                                {{ __('order_flow.dup_repeat_carrier_sent', ['count' => $this->duplicateScanRepeatCount]) }}
                            </span>
                        </div>
                    @else
                        <div class="mt-4 flex items-center gap-2 text-xs text-ink-muted">
                            <x-edz.icon name="check-circle" class="w-4 h-4 text-success" />
                            {{ __('order_flow.no_duplicates') }}
                        </div>
                    @endif

                    @if (($this->duplicateScanRepeatCount ?? 0) > 0 && $this->duplicateScanLevel !== 'repeat')
                        <div
                            class="mt-3 flex items-start gap-2 rounded-xl border border-surface-border bg-surface-tertiary/40 p-3 text-xs text-ink-muted">
                            <x-edz.icon name="info-circle" class="w-4 h-4 shrink-0 text-brand" />
                            <span>
                                {{ __('order_flow.dup_repeat_carrier_sent', ['count' => $this->duplicateScanRepeatCount]) }}
                            </span>
                        </div>
                    @endif

                    <div class="mt-5 flex justify-end">
                        <button type="button" wire:click="closeDuplicateScanModal()"
                            class="edz-btn edz-btn--ghost edz-btn--sm">
                            {{ __('general.close') }}
                        </button>
                    </div>
                </div>
            </x-edz.modal>
        </div>
    @endif

    {{-- Order event-log popup (P29.4) — extracted partial --}}
    @include('livewire.merchant.orders.partials.order-events-modal')

    {{-- Filter Portal — single container, fixed-positioned --}}
    <div x-data="dropdownPosition()" x-show="open" @click.away="close()"
        @edz-filter-open.window="$event.detail && toggle($event, $event.detail)">
        <div x-show="open" x-cloak
            class="fixed inset-0 z-[205] bg-black/40 backdrop-blur-sm sm:hidden" @click="close()"></div>
        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-3"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-3"
            :style="menuStyle"
            class="fixed inset-x-0 bottom-0 z-[210] w-full rounded-t-2xl border border-b-0 border-surface-border bg-surface
                   p-3 pb-[calc(1rem+env(safe-area-inset-bottom))] shadow-[0_-16px_48px_-12px_rgba(15,23,42,.25)]
                   max-h-[75vh] overflow-y-auto edz-scroll
                   sm:inset-x-auto sm:bottom-auto sm:z-50 sm:w-auto sm:rounded-xl sm:border-b sm:p-2 sm:shadow-lg"
            :class="{
                'sm:max-h-64': open === 'wilaya' || open === 'status' ||
                    open === 'assigned_to' || open === 'city' || open === 'delivery_type' ||
                    open === 'shipping_provider' || open === 'stopdesk_point' ||
                    open === 'shipment_type' || open === 'confirmed_by',
                'sm:w-48': open === 'product' || open === 'amount' || open === 'address' ||
                    open === 'notes' || open === 'weight' ||
                    open === 'send_from_carrier_warehouse',
                'sm:w-52': open === 'wilaya' || open === 'status' || open === 'assigned_to' ||
                    open === 'date'
            }">
            <span class="pointer-events-none mx-auto mb-2 block h-1 w-10 rounded-full bg-surface-border sm:hidden"></span>
            <div class="flex items-center justify-between gap-2 px-1 mb-1.5 sm:hidden">
                <p class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink uppercase tracking-wide">
                    <x-edz.icon name="filter" class="w-3.5 h-3.5 text-ink-muted" />
                    <span>{{ __('buttons.filter') }}</span>
                </p>
                <button @click="close()" type="button"
                    class="-m-1 p-1 rounded-lg text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                    title="{{ __('general.close') }}">
                    <x-edz.icon name="x-mark" class="w-4 h-4" />
                </button>
            </div>

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
        @if (in_array('total', $this->visibleColumns))
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
                    <button
                        @click="$wire.setFilter('shipping_provider', '{{ $pr['id'] }}'); $wire.setFilter('stopdesk_point', null); $wire.loadFilterStopdeskPoints('{{ $pr['id'] }}')"
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
                    <div class="px-2.5 py-1.5 rounded-lg text-xs text-ink-muted">
                        {{ __('merchant_panel.select_provider_first') }}</div>
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
                    <div class="px-2.5 py-1.5 rounded-lg text-xs text-ink-muted">
                        {{ __('merchant_panel.select_state_first') }}</div>
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

        {{-- Weight (min/max range) --}}
        @if (in_array('weight', $this->visibleColumns))
            <div x-show="open === 'weight'" x-cloak>
                <div class="flex items-center gap-1">
                    <div class="relative flex-1">
                        <input type="number" wire:model.live.debounce.600ms="filters.weight_min"
                            placeholder="Min" class="edz-input text-xs w-full pe-6" step="0.01">
                        @if ($this->filters['weight_min'] !== null && $this->filters['weight_min'] !== '')
                            <button wire:click="$set('filters.weight_min', '')" type="button"
                                class="absolute end-1 top-1/2 -translate-y-1/2 text-ink-muted hover:text-accent-500 transition"
                                aria-label="Clear min weight">
                                <x-edz.icon name="x-mark" class="w-3.5 h-3.5" />
                            </button>
                        @endif
                    </div>
                    <div class="relative flex-1">
                        <input type="number" wire:model.live.debounce.600ms="filters.weight_max"
                            placeholder="Max" class="edz-input text-xs w-full pe-6" step="0.01">
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
