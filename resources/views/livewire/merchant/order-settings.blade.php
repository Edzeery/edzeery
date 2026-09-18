<?php

use App\Domains\Order\Models\ConfirmationProductAssignment;
use App\Domains\Order\Models\ConfirmationShift;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Products\Product;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.store');

state([
    'tab' => 'shifts',
    'members' => [],
    'shifts' => [],
    'assignments' => [],

    'showShiftModal' => false,
    'editingShiftId' => null,
    'shiftForm' => [
        'membership_id' => '',
        'shift_type' => 'morning',
        'start_time' => '08:00',
        'end_time' => '12:00',
        'days_of_week' => [1, 2, 3, 4, 5],
        'is_active' => true,
        'max_concurrent_orders' => null,
    ],

    'showAssignModal' => false,
    'assignForm' => [
        'membership_id' => '',
        'product_ids' => [],
    ],
    'productSearch' => '',
    'assignProductNames' => [],
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

// ——— Shift Type auto-fill times ———
$onShiftTypeChange = function (): void {
    $shiftTimes = [
        'morning'   => ['start_time' => '08:00', 'end_time' => '12:00'],
        'afternoon' => ['start_time' => '12:00', 'end_time' => '17:00'],
        'evening'   => ['start_time' => '17:00', 'end_time' => '22:00'],
        'full_day'  => ['start_time' => '08:00', 'end_time' => '22:00'],
        'custom'    => ['start_time' => '08:00', 'end_time' => '17:00'],
    ];
    $times = $shiftTimes[$this->shiftForm['shift_type']] ?? ['start_time' => '08:00', 'end_time' => '17:00'];
    $this->shiftForm['start_time'] = $times['start_time'];
    $this->shiftForm['end_time'] = $times['end_time'];

    if ($this->shiftForm['shift_type'] === 'full_day') {
        $this->shiftForm['days_of_week'] = [1, 2, 3, 4, 5, 6, 7];
    }
};

// ——— Shifts CRUD ———

$openShiftModal = function (?string $shiftId = null): void {
    if ($shiftId) {
        $shift = ConfirmationShift::where('store_id', currentStoreId())->findOrFail($shiftId);
        $this->editingShiftId = $shift->id;
        $this->shiftForm = [
            'membership_id' => $shift->membership_id,
            'shift_type' => $shift->shift_type,
            'start_time' => $shift->start_time,
            'end_time' => $shift->end_time,
            'days_of_week' => $shift->days_of_week ?? range(1, 7),
            'is_active' => $shift->is_active,
            'max_concurrent_orders' => $shift->max_concurrent_orders,
        ];
    } else {
        $this->editingShiftId = null;
        $this->shiftForm = [
            'membership_id' => '',
            'shift_type' => 'morning',
            'start_time' => '08:00',
            'end_time' => '12:00',
            'days_of_week' => [1, 2, 3, 4, 5],
            'is_active' => true,
            'max_concurrent_orders' => null,
        ];
    }
    $this->showShiftModal = true;
};

$toggleShiftDay = function (int $day): void {
    $current = $this->shiftForm['days_of_week'] ?? [];
    if (in_array($day, $current)) {
        $this->shiftForm['days_of_week'] = array_values(array_diff($current, [$day]));
    } else {
        $this->shiftForm['days_of_week'][] = $day;
        sort($this->shiftForm['days_of_week']);
    }
};

$saveShift = function (): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);

    $validated = Validator::make($this->shiftForm, [
        'membership_id' => 'required|exists:store_memberships,id',
        'shift_type' => 'required|string|in:morning,afternoon,evening,full_day,custom',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i',
        'days_of_week' => 'required|array|min:1',
        'days_of_week.*' => 'integer|min:1|max:7',
        'is_active' => 'boolean',
        'max_concurrent_orders' => 'nullable|integer|min:1|max:9999',
    ])->validate();

    $storeId = currentStoreId();

    // Overnight shifts are allowed (start > end); empty end means invalid.
    $days = array_values(array_unique($validated['days_of_week']));

    // Prevent overlapping active shifts for the same member.
    $candidate = $validated;
    $candidate['days_of_week'] = $days;
    if (ConfirmationShift::overlapsActiveShift($candidate, $this->editingShiftId)) {
        $this->addError('shiftForm.start_time', __('merchant_panel.shift_overlap'));
        return;
    }

    $data = $validated;
    $data['store_id'] = $storeId;
    $data['days_of_week'] = $days;

    if (! empty($this->editingShiftId)) {
        ConfirmationShift::where('store_id', $storeId)
            ->findOrFail($this->editingShiftId)
            ->update($data);
        $this->dispatch('swal', type: 'success', title: __('merchant_panel.shift_saved'));
    } else {
        ConfirmationShift::create($data);
        $this->dispatch('swal', type: 'success', title: __('merchant_panel.shift_saved'));
    }

    $this->showShiftModal = false;
    $this->loadData();
};

$deleteShift = function (string $shiftId): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);
    ConfirmationShift::where('store_id', currentStoreId())->findOrFail($shiftId)->delete();
    $this->loadData();
    $this->dispatch('swal', type: 'success', title: __('merchant_panel.shift_deleted'));
};

$toggleShiftActive = function (string $shiftId): void {
    $shift = ConfirmationShift::where('store_id', currentStoreId())->findOrFail($shiftId);
    $shift->update(['is_active' => ! $shift->is_active]);
    $this->loadData();
};

$benefitsMembership = function (string $membershipId): bool {
    return StoreMembership::where('store_id', currentStoreId())
        ->where('id', $membershipId)
        ->where('is_active', true)
        ->exists();
};

// ——— Product Assignments ———

$openAssignModal = function (?string $membershipId = null): void {
    $this->assignForm = [
        'membership_id' => $membershipId ?? '',
        'product_ids' => [],
    ];
    $this->assignProductNames = [];

    if ($membershipId) {
        $existing = ConfirmationProductAssignment::where('store_id', currentStoreId())
            ->where('membership_id', $membershipId)
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

    DB::transaction(function () use ($storeId, $membershipId, $productIds) {
        ConfirmationProductAssignment::where('store_id', $storeId)
            ->where('membership_id', $membershipId)
            ->delete();

        foreach ($productIds as $productId) {
            ConfirmationProductAssignment::create([
                'store_id' => $storeId,
                'membership_id' => $membershipId,
                'product_id' => $productId,
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
