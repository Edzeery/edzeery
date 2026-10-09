<?php

use App\Domains\Order\Models\ConfirmationProductAssignment;
use App\Domains\Order\Models\ConfirmationShift;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Products\Product;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Facades\DB;
use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\uses;

layout('components.layouts.store');

uses([
    \App\Livewire\Concerns\ManagesOrderShifts::class,
    \App\Livewire\Concerns\ReportsShiftCoverage::class,
]);

state([
    'tab' => 'shifts',
    'members' => [],
    'shifts' => [],
    'assignments' => [],

    'showShiftModal' => false,
    'editingShiftId' => null,
    'shiftForm' => [
        'role_scope' => 'confirm',
        'membership_id' => '',
        'shift_type' => 'morning',
        'start_time' => '08:00',
        'end_time' => '12:00',
        'days_of_week' => [1, 2, 3, 4, 5],
        'is_active' => true,
        'max_concurrent_orders' => null,
    ],
    'shiftRoleFilter' => 'all',
    'shiftSearch' => '',

    'showAssignModal' => false,
    'assignForm' => [
        'role_scope' => 'confirm',
        'membership_id' => '',
        'product_ids' => [],
    ],
    'productSearch' => '',
    'assignProductNames' => [],
    'assignSearch' => '',
    'assignRoleFilter' => 'all',
    'storeTimezone' => null,
    'onShiftNow' => 0,

    'overflowEnabled' => false,
    'overflowPercentage' => 10,
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);
    $this->loadData();
});

$loadData = function (): void {
    $storeId = currentStoreId();

    $store = \App\Models\Stores\Store::where('id', $storeId)->with('settings')->first();
    $this->storeTimezone = $store?->settings?->timezone ?? config('app.timezone');

    $settings = $store?->settings;
    $this->overflowEnabled = (bool) ($settings?->distribution_overflow_enabled ?? false);
    $this->overflowPercentage = (int) ($settings?->distribution_overflow_percentage ?? 10);

    $this->members = StoreMembership::where('store_id', $storeId)
        ->with('user')
        ->get()
        ->toArray();

    $this->shifts = ConfirmationShift::where('store_id', $storeId)
        ->with('membership.user')
        ->orderBy('start_time')
        ->get();

    $now = \Carbon\Carbon::now($this->storeTimezone);
    $this->onShiftNow = $this->shifts
        ->filter(fn (ConfirmationShift $s) => $s->coversDayTime($now->dayOfWeekIso, $now->format('H:i')))
        ->pluck('membership_id')
        ->unique()
        ->count();

    $this->shifts = $this->shifts->toArray();

    $this->assignments = ConfirmationProductAssignment::where('store_id', $storeId)
        ->with('membership.user', 'product:id,name')
        ->get()
        ->toArray();
};

$saveOverflow = function (): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);

    $this->validate([
        'overflowPercentage' => ['required', 'integer', 'min:0', 'max:100'],
    ]);

    currentStore()->settings()->updateOrCreate([], [
        'distribution_overflow_enabled' => $this->overflowEnabled,
        'distribution_overflow_percentage' => $this->overflowPercentage,
    ]);

    $this->dispatch('swal', type: 'success', title: __('merchant_panel.settings_saved'));
};

$setTab = function (string $tab): void {
    $this->tab = $tab;
};

$benefitsMembership = function (string $membershipId): bool {
    return StoreMembership::where('store_id', currentStoreId())
        ->where('id', $membershipId)
        ->where('is_active', true)
        ->exists();
};

// ——— Product Assignments ———

$openAssignModal = function (?string $membershipId = null, string $roleScope = 'confirm'): void {
    $roleScope = in_array($roleScope, ['confirm', 'track'], true) ? $roleScope : 'confirm';

    $this->assignForm = [
        'role_scope' => $roleScope,
        'membership_id' => $membershipId ?? '',
        'product_ids' => [],
    ];
    $this->assignProductNames = [];

    if ($membershipId) {
        $existing = ConfirmationProductAssignment::where('store_id', currentStoreId())
            ->where('membership_id', $membershipId)
            ->where('role_scope', $roleScope)
            ->with('product:id,name')
            ->get();

        $this->assignForm['product_ids'] = $existing->pluck('product_id')->toArray();
        $this->assignProductNames = $existing->pluck('product.name', 'product_id')->toArray();
    }

    $this->productSearch = '';
    $this->showAssignModal = true;
};

$searchAssignProducts = computed(function (): array {
    $search = trim($this->productSearch);
    $storeId = currentStoreId();

    return Product::where('store_id', $storeId)
        ->where('is_active', true)
        ->when($search !== '', function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        })
        ->orderBy('name')
        ->limit(15)
        ->get(['id', 'name', 'sku', 'price'])
        ->map(fn (Product $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'price' => (float) $p->price,
            'image_url' => $p->getPrimaryImagePathAttribute(),
        ])
        ->values()
        ->all();
});

$visibleAssignments = computed(function (): array {
    $items = collect($this->assignments);

    if ($this->assignRoleFilter !== 'all') {
        $items = $items->filter(fn (array $a) => ($a['role_scope'] ?? 'confirm') === $this->assignRoleFilter);
    }

    $search = trim($this->assignSearch);
    if ($search === '') {
        return $items->values()->all();
    }

    $needle = mb_strtolower($search);

    return $items
        ->filter(fn (array $a) => str_contains(mb_strtolower($a['membership']['user']['name'] ?? ''), $needle)
            || str_contains(mb_strtolower($a['product']['name'] ?? ''), $needle))
        ->values()
        ->all();
});

/**
 * Per-role ownership setup gap: eligible members who hold the role permission
 * but own no product of that role. Under strict ownership routing those members
 * form the general pool — unowned-product orders will land on it (or wait when
 * nobody eligible is on shift), so the owner should see the roster at a glance.
 */
$assignSetupGaps = computed(function (): array {
    $storeId = currentStoreId();

    $members = StoreMembership::where('store_id', $storeId)
        ->where('is_active', true)
        ->with('user:id,name')
        ->get();

    $assignedByRole = ConfirmationProductAssignment::where('store_id', $storeId)
        ->get(['membership_id', 'role_scope'])
        ->groupBy('role_scope')
        ->map(fn ($rows) => $rows->pluck('membership_id')->unique()->all());

    $gaps = [];

    foreach (['confirm', 'track'] as $role) {
        $permission = $role === 'track'
            ? StorePermissionEnum::CRM_ORDER_TRACKING->value
            : StorePermissionEnum::ORDER_CONFIRM->value;

        $eligible = $members->filter(fn (StoreMembership $m) => $m->can($permission));

        if ($eligible->isEmpty()) {
            continue;
        }

        $assignedIds = $assignedByRole[$role] ?? [];
        $unowned = $eligible->reject(fn (StoreMembership $m) => in_array($m->id, $assignedIds, true));

        $gaps[$role] = [
            'role' => $role,
            'eligible' => $eligible->count(),
            'unowned' => $unowned->count(),
            'names' => $unowned->map(fn (StoreMembership $m) => $m->user?->name ?: '—')->values()->all(),
        ];
    }

    return $gaps;
});

$changeAssignRole = function (string $roleScope): void {
    $this->openAssignModal($this->assignForm['membership_id'] ?: null, $roleScope);
};

$toggleAssignProduct = function (string $productId): void {
    $product = Product::where('store_id', currentStoreId())
        ->where('id', $productId)
        ->where('is_active', true)
        ->first();

    if (! $product) {
        return;
    }

    $current = $this->assignForm['product_ids'];
    if (in_array($productId, $current)) {
        $this->assignForm['product_ids'] = array_values(array_diff($current, [$productId]));
        unset($this->assignProductNames[$productId]);
    } else {
        $this->assignForm['product_ids'][] = $productId;
        $this->assignProductNames[$productId] = $product->name;
    }
};

$saveAssignments = function (): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);

    $storeId = currentStoreId();
    $membershipId = $this->assignForm['membership_id'];
    $roleScope = in_array($this->assignForm['role_scope'] ?? 'confirm', ['confirm', 'track'], true)
        ? $this->assignForm['role_scope']
        : 'confirm';

    if (! $membershipId) {
        $this->dispatch('swal', type: 'error', title: __('merchant_panel.select_member_first'));
        return;
    }

    $productIds = array_values(array_unique($this->assignForm['product_ids'] ?? []));

    if (! $this->benefitsMembership($membershipId)) {
        $this->dispatch('swal', type: 'error', title: __('merchant_panel.select_member_first'));
        return;
    }

    // Ensure every selected product actually belongs to this store.
    $validProductCount = Product::where('store_id', $storeId)
        ->whereIn('id', $productIds)
        ->count();

    if ($validProductCount !== count($productIds)) {
        $this->dispatch('swal', type: 'error', title: __('merchant_panel.invalid_products'));
        return;
    }

    DB::transaction(function () use ($storeId, $membershipId, $productIds, $roleScope) {
        ConfirmationProductAssignment::where('store_id', $storeId)
            ->where('membership_id', $membershipId)
            ->where('role_scope', $roleScope)
            ->delete();

        foreach ($productIds as $productId) {
            ConfirmationProductAssignment::create([
                'store_id' => $storeId,
                'membership_id' => $membershipId,
                'product_id' => $productId,
                'role_scope' => $roleScope,
            ]);
        }
    });

    $this->showAssignModal = false;
    $this->loadData();
    $this->dispatch('swal', type: 'success', title: __('merchant_panel.assignment_saved'));
};

$removeAssignment = function (string $assignmentId): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);
    ConfirmationProductAssignment::where('store_id', currentStoreId())->findOrFail($assignmentId)->delete();
    $this->loadData();
};
?>

<div>
    @php
        $SHIFT_TYPES = [
            'morning'   => __('merchant_panel.shift_morning'),
            'afternoon' => __('merchant_panel.shift_afternoon'),
            'evening'   => __('merchant_panel.shift_evening'),
            'full_day'  => __('merchant_panel.shift_full_day'),
            'custom'    => __('merchant_panel.shift_custom'),
        ];

        $DAYS_OF_WEEK = [
            1 => __('merchant_panel.monday'),
            2 => __('merchant_panel.tuesday'),
            3 => __('merchant_panel.wednesday'),
            4 => __('merchant_panel.thursday'),
            5 => __('merchant_panel.friday'),
            6 => __('merchant_panel.saturday'),
            7 => __('merchant_panel.sunday'),
        ];
    @endphp

    @include('livewire.merchant.order-settings.partials.overview')

    @include('livewire.merchant.order-settings.partials.tabs')

    @include('livewire.merchant.order-settings.partials.shifts-tab')

    {{-- Distribution Overflow Tab --}}
    @if($tab === 'overflow')
        <div class="max-w-2xl">
            @include('livewire.merchant.order-distribution-settings.partials.overflow-settings')
        </div>
    @endif

    @include('livewire.merchant.order-settings.partials.assignments-tab')

    @include('livewire.merchant.order-settings.partials.shift-modal')

    @include('livewire.merchant.order-settings.partials.assignments-modal')
</div>
