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
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);
    $this->loadData();
});

$loadData = function (): void {
    $storeId = currentStoreId();

    $store = \App\Models\Stores\Store::where('id', $storeId)->with('settings')->first();
    $this->storeTimezone = $store?->settings?->timezone ?? config('app.timezone');

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

    {{-- Page Header --}}
    <div class="mb-6">
        <x-edz.page-header
            title="{{ __('merchant_panel.order_settings') }}"
            description="{{ __('merchant_panel.order_settings_desc') }}">
        </x-edz.page-header>
    </div>

    @php
        $now = \Carbon\Carbon::now($storeTimezone ?? config('app.timezone'));
    @endphp

    {{-- Store-time info bar --}}
    <div class="edz-card edz-card--padded mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-surface flex items-center justify-center">
                    <x-edz.icon name="clock" class="w-5 h-5 text-brand-500" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-ink">
                        {{ $now->translatedFormat('l, d M Y — H:i') }}
                    </p>
                    <p class="text-xs text-ink-muted">{{ __('merchant_panel.store_timezone') }}: {{ $storeTimezone }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-success-500"></span>
                <span class="text-sm text-ink">
                    {{ __('merchant_panel.agents_on_shift_now', ['count' => $onShiftNow]) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="edz-card edz-card--padded">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-surface flex items-center justify-center">
                    <x-edz.icon name="adjustments" class="w-5 h-5 text-brand-500" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-ink">{{ count($shifts) }}</p>
                    <p class="text-xs text-ink-muted">{{ __('merchant_panel.total_shifts') }}</p>
                </div>
            </div>
        </div>
        <div class="edz-card edz-card--padded">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-success-surface flex items-center justify-center">
                    <x-edz.icon name="check-circle" class="w-5 h-5 text-success-500" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-ink">{{ collect($shifts)->where('is_active', true)->count() }}</p>
                    <p class="text-xs text-ink-muted">{{ __('merchant_panel.active_shifts') }}</p>
                </div>
            </div>
        </div>
        <div class="edz-card edz-card--padded">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-warning-surface flex items-center justify-center">
                    <x-edz.icon name="package" class="w-5 h-5 text-warning-500" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-ink">{{ count($assignments) }}</p>
                    <p class="text-xs text-ink-muted">{{ __('merchant_panel.product_rules') }}</p>
                </div>
            </div>
        </div>
        <div class="edz-card edz-card--padded">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-info-surface flex items-center justify-center">
                    <x-edz.icon name="user" class="w-5 h-5 text-info-500" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-ink">{{ collect($shifts)->pluck('membership_id')->unique()->count() }}</p>
                    <p class="text-xs text-ink-muted">{{ __('merchant_panel.agents_with_shifts') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 mb-6 border-b border-surface-border">
        <button wire:click="setTab('shifts')"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px {{ $tab === 'shifts' ? 'border-brand-500 text-brand-fg' : 'border-transparent text-ink-muted hover:text-ink' }}">
            <x-edz.icon name="adjustments" class="w-4 h-4" />
            {{ __('merchant_panel.tab_shifts') }}
        </button>
        <button wire:click="setTab('products')"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px {{ $tab === 'products' ? 'border-brand-500 text-brand-fg' : 'border-transparent text-ink-muted hover:text-ink' }}">
            <x-edz.icon name="package" class="w-4 h-4" />
            {{ __('merchant_panel.tab_product_assignments') }}
        </button>
    </div>

    {{-- Shifts Tab --}}
    @if($tab === 'shifts')
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm text-ink-muted">{{ __('merchant_panel.tab_shifts_desc') }}</p>
            <button wire:click="openShiftModal" class="edz-btn edz-btn--primary edz-btn--sm">
                <x-edz.icon name="check-circle" class="w-4 h-4" />
                {{ __('merchant_panel.new_shift') }}
            </button>
        </div>

        @if(!empty($shifts))
            <div class="edz-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="edz-table">
                        <thead>
                            <tr>
                                <th>{{ __('merchant_panel.agent') }}</th>
                                <th>{{ __('merchant_panel.type') }}</th>
                                <th>{{ __('merchant_panel.hours') }}</th>
                                <th>{{ __('merchant_panel.days') }}</th>
                                <th>{{ __('merchant_panel.max_orders_cap') }}</th>
                                <th>{{ __('merchant_panel.status') }}</th>
                                <th class="text-end">{{ __('merchant_panel.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shifts as $shift)
                                <tr wire:key="shift-{{ $shift['id'] }}">
                                    <td class="font-medium text-ink">
                                        <div class="flex items-center gap-2">
                                            {{ $shift['membership']['user']['name'] ?? '—' }}
                                            @if(empty($shift['membership']['is_active']))
                                                <span class="edz-badge edz-badge--neutral">{{ __('merchant_panel.member_inactive') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="capitalize">
                                        {{ $SHIFT_TYPES[$shift['shift_type']] ?? $shift['shift_type'] }}
                                    </td>
                                    <td class="font-mono text-xs">
                                        @php $isOvernight = $shift['start_time'] !== $shift['end_time'] && $shift['start_time'] > $shift['end_time']; @endphp
                                        {{ $shift['start_time'] }} — {{ $shift['end_time'] }}
                                        @if($isOvernight)
                                            <span class="edz-badge edz-badge--brand ms-1">{{ __('merchant_panel.shift_overnight') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-xs">
                                        @if(!empty($shift['days_of_week']))
                                            <span class="inline-flex flex-wrap gap-1">
                                                @foreach($shift['days_of_week'] as $day)
                                                    <span class="edz-badge edz-badge--neutral">{{ $DAYS_OF_WEEK[$day] ?? $day }}</span>
                                                @endforeach
                                            </span>
                                        @else
                                            <span class="text-ink-muted">{{ __('merchant_panel.all_days') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-xs">
                                        @if(!empty($shift['max_concurrent_orders']))
                                            <span class="edz-badge edz-badge--brand">{{ $shift['max_concurrent_orders'] }}</span>
                                        @else
                                            <span class="text-ink-muted">∞</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" wire:click="toggleShiftActive('{{ $shift['id'] }}')"
                                                class="cursor-pointer {{ $shift['is_active'] ? 'edz-badge edz-badge--success' : 'edz-badge edz-badge--neutral' }}">
                                            {{ $shift['is_active'] ? __('merchant_panel.active') : __('merchant_panel.inactive') }}
                                        </button>
                                    </td>
                                    <td class="text-end">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button" aria-label="{{ __('merchant_panel.edit_shift') }}"
                                                    wire:click="openShiftModal('{{ $shift['id'] }}')"
                                                    class="edz-btn edz-btn--ghost edz-btn--sm">
                                                <x-edz.icon name="edit" class="w-4 h-4" />
                                            </button>
<button type="button" aria-label="{{ __('merchant_panel.delete_shift') }}"
                                                    class="edz-btn edz-btn--ghost edz-btn--sm text-danger-500"
                                                    x-data
                                                    x-on:click.prevent="(async () => { if (await EdzSwal.confirmDelete()) await $wire.deleteShift('{{ $shift['id'] }}') })()">
                                                    <x-edz.icon name="x-mark" class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="edz-card p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-surface-secondary flex items-center justify-center mx-auto mb-4">
                    <x-edz.icon name="adjustments" class="w-8 h-8 text-ink-muted opacity-40" />
                </div>
                <p class="text-ink-muted mb-4">{{ __('merchant_panel.no_shifts_yet') }}</p>
                <button wire:click="openShiftModal" class="edz-btn edz-btn--primary edz-btn--sm">
                    <x-edz.icon name="check-circle" class="w-4 h-4" />
                    {{ __('merchant_panel.new_shift') }}
                </button>
            </div>
        @endif
    @endif

    {{-- Product Assignments Tab --}}
    @if($tab === 'products')
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm text-ink-muted">{{ __('merchant_panel.tab_product_assignments_desc') }}</p>
            <button wire:click="openAssignModal" class="edz-btn edz-btn--primary edz-btn--sm">
                <x-edz.icon name="check-circle" class="w-4 h-4" />
                {{ __('merchant_panel.assign_products') }}
            </button>
        </div>

        @if(!empty($assignments))
            @php
                $grouped = collect($assignments)->groupBy(fn($a) => $a['membership_id']);
            @endphp
            <div class="space-y-4">
                @foreach($grouped as $memberId => $items)
                    @php
                        $agentName = $items->first()['membership']['user']['name'] ?? '—';
                    @endphp
                    <div class="edz-card overflow-hidden" wire:key="group-{{ $memberId }}">
                        <div class="bg-surface-secondary border-b border-surface-border px-4 py-3 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-brand-surface flex items-center justify-center">
                                    <x-edz.icon name="user" class="w-4 h-4 text-brand-fg" />
                                </div>
                                <span class="font-semibold text-sm text-ink">{{ $agentName }}</span>
                                <span class="edz-badge edz-badge--neutral">{{ $items->count() }} {{ __('merchant_panel.products') }}</span>
                            </div>
                            <button wire:click="openAssignModal('{{ $memberId }}')" class="edz-btn edz-btn--ghost edz-btn--sm">
                                <x-edz.icon name="edit" class="w-4 h-4" />
                                {{ __('merchant_panel.edit') }}
                            </button>
                        </div>
                        <div class="divide-y divide-surface-border">
                            @foreach($items as $a)
                                <div class="px-4 py-3 flex items-center justify-between hover:bg-surface-secondary" wire:key="assign-{{ $a['id'] }}">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-surface-secondary flex items-center justify-center">
                                            <x-edz.icon name="package" class="w-4 h-4 text-ink-muted" />
                                        </div>
                                        <span class="text-sm text-ink">{{ $a['product']['name'] ?? '—' }}</span>
                                    </div>
                                    <button type="button"
                                            class="edz-btn edz-btn--ghost edz-btn--sm text-danger-500"
                                            x-data
                                            x-on:click.prevent="(async () => { if (await EdzSwal.confirmDelete()) await $wire.removeAssignment('{{ $a['id'] }}') })()">
                                        <x-edz.icon name="x-mark" class="w-4 h-4" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="edz-card p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-surface-secondary flex items-center justify-center mx-auto mb-4">
                    <x-edz.icon name="package" class="w-8 h-8 text-ink-muted opacity-40" />
                </div>
                <p class="text-ink-muted mb-4">{{ __('merchant_panel.no_assignments_yet') }}</p>
                <button wire:click="openAssignModal" class="edz-btn edz-btn--primary edz-btn--sm">
                    <x-edz.icon name="check-circle" class="w-4 h-4" />
                    {{ __('merchant_panel.assign_products') }}
                </button>
            </div>
        @endif
    @endif

    {{-- Shift Modal --}}
    @if($showShiftModal)
    <x-edz.modal :isOpen="true" wire:key="shift-modal-{{ $showShiftModal ? 'open' : 'closed' }}">
        <form wire:submit="saveShift">
            <div class="p-6 space-y-4">
                <h3 class="text-lg font-semibold text-ink">
                    {{ $editingShiftId ? __('merchant_panel.edit_shift') : __('merchant_panel.new_shift') }}
                </h3>

                <div class="space-y-4">
                    <div class="edz-field">
                        <label class="edz-field__label" for="shift-agent">{{ __('merchant_panel.agent') }} *</label>
                        <x-edz.select
                            wire:model="shiftForm.membership_id"
                            :options="$members"
                            option-value="id"
                            option-label="user.name"
                            placeholder="{{ __('merchant_panel.select_agent') }}"
                            :error="$errors->first('shiftForm.membership_id')"
                        />
                    </div>

                    <div class="edz-field">
                        <label class="edz-field__label" for="shift-type">{{ __('merchant_panel.shift_type') }}</label>
                        <x-edz.select
                            wire:model="shiftForm.shift_type"
                            wire:change="onShiftTypeChange"
                            :options="$SHIFT_TYPES"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="edz-field">
                            <label class="edz-field__label" for="shift-start">{{ __('merchant_panel.start_time') }}</label>
                            <input type="time" id="shift-start" wire:model="shiftForm.start_time"
                                   class="edz-input @error('shiftForm.start_time') edz-input--error @enderror">
                            @error('shiftForm.start_time')
                                <span class="edz-field__error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="edz-field">
                            <label class="edz-field__label" for="shift-end">{{ __('merchant_panel.end_time') }}</label>
                            <input type="time" id="shift-end" wire:model="shiftForm.end_time"
                                   class="edz-input @error('shiftForm.end_time') edz-input--error @enderror">
                            @error('shiftForm.end_time')
                                <span class="edz-field__error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="edz-field">
                        <span class="edz-field__label">{{ __('merchant_panel.days_of_week') }}</span>
                        <div class="flex flex-wrap gap-2">
                            @foreach($DAYS_OF_WEEK as $dayNum => $dayLabel)
                                <button type="button" wire:click="toggleShiftDay({{ $dayNum }})"
                                        class="cursor-pointer {{ in_array($dayNum, $shiftForm['days_of_week'] ?? []) ? 'edz-badge edz-badge--brand' : 'edz-badge edz-badge--neutral' }}">
                                    {{ $dayLabel }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="edz-field">
                        <label class="edz-field__label" for="shift-max">{{ __('merchant_panel.max_concurrent_orders') }}</label>
                        <input type="number" id="shift-max" wire:model.blur="shiftForm.max_concurrent_orders"
                               min="1" max="9999" inputmode="numeric"
                               placeholder="{{ __('merchant_panel.max_concurrent_orders_placeholder') }}"
                               class="edz-input @error('shiftForm.max_concurrent_orders') edz-input--error @enderror">
                        <p class="edz-field__hint">{{ __('merchant_panel.max_concurrent_orders_hint') }}</p>
                        @error('shiftForm.max_concurrent_orders')
                            <span class="edz-field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex items-center gap-2 pt-2 border-t border-surface-border">
                        <input type="checkbox" wire:model="shiftForm.is_active" id="shift_active"
                               class="h-4 w-4 rounded border-surface-border text-brand-600">
                        <label for="shift_active" class="text-sm text-ink">{{ __('merchant_panel.active') }}</label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-surface-border">
                        <button type="button" @click="$wire.set('showShiftModal', false)" class="edz-btn edz-btn--ghost">
                            {{ __('buttons.cancel') }}
                        </button>
                        <button type="submit" class="edz-btn edz-btn--primary">
                            <x-edz.icon name="check-circle" class="w-4 h-4" />
                            {{ __('merchant_panel.save') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </x-edz.modal>
    @endif

    {{-- Assign Products Modal --}}
    @if($showAssignModal)
    <x-edz.modal :isOpen="true" wire:key="assign-modal-{{ $showAssignModal ? 'open' : 'closed' }}">
        <form wire:submit="saveAssignments">
            <div class="p-6 space-y-4">
                <h3 class="text-lg font-semibold text-ink">{{ __('merchant_panel.assign_products') }}</h3>

                <div class="space-y-4">
                    <div class="edz-field">
                        <label class="edz-field__label" for="assign-agent">{{ __('merchant_panel.agent') }} *</label>
                        <x-edz.select
                            wire:model="assignForm.membership_id"
                            :options="$members"
                            option-value="id"
                            option-label="user.name"
                            placeholder="{{ __('merchant_panel.select_agent') }}"
                            :error="$errors->first('assignForm.membership_id')"
                        />
                    </div>

                    <div class="edz-field">
                        <label class="edz-field__label" for="assign-search">{{ __('merchant_panel.search_products') }}</label>
                        <x-edz.product-multi-picker
                            :options="$this->searchAssignProducts"
                            :selected="$assignForm['product_ids'] ?? []"
                            :selected-names="$assignProductNames"
                            toggle="toggleAssignProduct"
                            model="productSearch"
                            :placeholder="__('merchant_panel.search_products_to_add')"
                            :empty-message="__('merchant_panel.list_no_products_found')" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-surface-border">
                        <button type="button" @click="$wire.set('showAssignModal', false)" class="edz-btn edz-btn--ghost">
                            {{ __('buttons.cancel') }}
                        </button>
                        <button type="submit" class="edz-btn edz-btn--primary">
                            <x-edz.icon name="check-circle" class="w-4 h-4" />
                            {{ __('merchant_panel.save') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </x-edz.modal>
    @endif
</div>
