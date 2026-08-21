<?php

use App\Domains\Order\Models\ConfirmationProductAssignment;
use App\Domains\Order\Models\ConfirmationShift;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Products\Product;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Facades\Validator;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.store');

state([
    'tab' => 'shifts',
    'members' => [],
    'allProducts' => [],
    'shifts' => [],
    'assignments' => [],

    'showShiftModal' => false,
    'editingShiftId' => null,
    'shiftForm' => [
        'membership_id' => '',
        'shift_type' => 'morning',
        'start_time' => '08:00',
        'end_time' => '17:00',
        'days_of_week' => [1, 2, 3, 4, 5],
        'is_active' => true,
    ],

    'showAssignModal' => false,
    'assignForm' => [
        'membership_id' => '',
        'product_ids' => [],
    ],
    'productSearch' => '',
    'productResults' => [],
]);

$DAYS_OF_WEEK = [
    1 => __('merchant_panel.monday'),
    2 => __('merchant_panel.tuesday'),
    3 => __('merchant_panel.wednesday'),
    4 => __('merchant_panel.thursday'),
    5 => __('merchant_panel.friday'),
    6 => __('merchant_panel.saturday'),
    7 => __('merchant_panel.sunday'),
];

$SHIFT_TYPES = [
    'morning'   => __('merchant_panel.shift_morning'),
    'afternoon' => __('merchant_panel.shift_afternoon'),
    'evening'   => __('merchant_panel.shift_evening'),
    'full_day'  => __('merchant_panel.shift_full_day'),
    'custom'    => __('merchant_panel.shift_custom'),
];

$SHIFT_TIMES = [
    'morning'   => ['start_time' => '08:00', 'end_time' => '12:00'],
    'afternoon' => ['start_time' => '12:00', 'end_time' => '17:00'],
    'evening'   => ['start_time' => '17:00', 'end_time' => '22:00'],
    'full_day'  => ['start_time' => '08:00', 'end_time' => '22:00'],
    'custom'    => ['start_time' => '08:00', 'end_time' => '17:00'],
];

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);
    $this->loadData();
});

$loadData = function (): void {
    $storeId = currentStoreId();

    $this->members = StoreMembership::where('store_id', $storeId)
        ->where('is_active', true)
        ->with('user')
        ->get()
        ->toArray();

    $this->shifts = ConfirmationShift::where('store_id', $storeId)
        ->with('membership.user')
        ->orderBy('created_at')
        ->get()
        ->toArray();

    $this->assignments = ConfirmationProductAssignment::where('store_id', $storeId)
        ->with('membership.user', 'product')
        ->get()
        ->toArray();

    $this->allProducts = Product::where('store_id', $storeId)
        ->select('id', 'name', 'price')
        ->orderBy('name')
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
            'days_of_week' => $shift->days_of_week ?? [1, 2, 3, 4, 5, 6, 7],
            'is_active' => $shift->is_active,
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
        'shift_type' => 'required|string',
        'start_time' => 'required|string',
        'end_time' => 'required|string|after:start_time',
        'days_of_week' => 'required|array|min:1',
        'is_active' => 'boolean',
    ])->validate();

    $storeId = currentStoreId();
    $data = $validated;
    $data['store_id'] = $storeId;

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

// ——— Product Assignments ———

$openAssignModal = function (?string $membershipId = null): void {
    $this->assignForm = [
        'membership_id' => $membershipId ?? '',
        'product_ids' => [],
    ];

    if ($membershipId) {
        $this->assignForm['product_ids'] = ConfirmationProductAssignment::where('store_id', currentStoreId())
            ->where('membership_id', $membershipId)
            ->pluck('product_id')
            ->toArray();
    }

    $this->productSearch = '';
    $this->productResults = [];
    $this->showAssignModal = true;
};

$searchAssignProducts = function (): void {
    $search = $this->productSearch;
    if (strlen($search) < 2) {
        $this->productResults = [];
        return;
    }
    $storeId = currentStoreId();
    $this->productResults = Product::where('store_id', $storeId)
        ->where('name', 'like', "%{$search}%")
        ->limit(15)
        ->get()
        ->toArray();
};

$toggleAssignProduct = function (string $productId): void {
    $current = $this->assignForm['product_ids'];
    if (in_array($productId, $current)) {
        $this->assignForm['product_ids'] = array_values(array_diff($current, [$productId]));
    } else {
        $this->assignForm['product_ids'][] = $productId;
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

    ConfirmationProductAssignment::where('store_id', $storeId)
        ->where('membership_id', $membershipId)
        ->delete();

    foreach ($this->assignForm['product_ids'] as $productId) {
        ConfirmationProductAssignment::create([
            'store_id' => $storeId,
            'membership_id' => $membershipId,
            'product_id' => $productId,
        ]);
    }

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

<div x-data="{ shiftTypeChanging: false }">
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
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <x-edz.page-header
            title="{{ __('merchant_panel.order_settings') }}"
            description="{{ __('merchant_panel.order_settings_desc') }}">
        </x-edz.page-header>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="edz-card edz-card--padded">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center">
                    <ion-icon name="time-outline" class="text-lg text-primary-500"></ion-icon>
                </div>
                <div>
                    <p class="text-2xl font-bold text-ink">{{ count($shifts) }}</p>
                    <p class="text-xs text-ink-muted">{{ __('merchant_panel.total_shifts') }}</p>
                </div>
            </div>
        </div>
        <div class="edz-card edz-card--padded">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-success-50 dark:bg-success-900/20 flex items-center justify-center">
                    <ion-icon name="checkmark-circle-outline" class="text-lg text-success-500"></ion-icon>
                </div>
                <div>
                    <p class="text-2xl font-bold text-ink">{{ collect($shifts)->where('is_active', true)->count() }}</p>
                    <p class="text-xs text-ink-muted">{{ __('merchant_panel.active_shifts') }}</p>
                </div>
            </div>
        </div>
        <div class="edz-card edz-card--padded">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-warning-50 dark:bg-warning-900/20 flex items-center justify-center">
                    <ion-icon name="cube-outline" class="text-lg text-warning-500"></ion-icon>
                </div>
                <div>
                    <p class="text-2xl font-bold text-ink">{{ count($assignments) }}</p>
                    <p class="text-xs text-ink-muted">{{ __('merchant_panel.product_rules') }}</p>
                </div>
            </div>
        </div>
        <div class="edz-card edz-card--padded">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-info-50 dark:bg-info-900/20 flex items-center justify-center">
                    <ion-icon name="people-outline" class="text-lg text-info-500"></ion-icon>
                </div>
                <div>
                    <p class="text-2xl font-bold text-ink">{{ collect($shifts)->pluck('membership_id')->unique()->count() }}</p>
                    <p class="text-xs text-ink-muted">{{ __('merchant_panel.agents_with_shifts') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 mb-6 border-b border-surface-200 dark:border-ink-700">
        <button wire:click="setTab('shifts')"
                class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px {{ $tab === 'shifts' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-ink-muted hover:text-ink' }}">
            <ion-icon name="time-outline" class="inline mr-1"></ion-icon>
            {{ __('merchant_panel.tab_shifts') }}
        </button>
        <button wire:click="setTab('products')"
                class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px {{ $tab === 'products' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-ink-muted hover:text-ink' }}">
            <ion-icon name="cube-outline" class="inline mr-1"></ion-icon>
            {{ __('merchant_panel.tab_product_assignments') }}
        </button>
    </div>

    {{-- Shifts Tab --}}
    @if($tab === 'shifts')
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm text-ink-muted">{{ __('merchant_panel.tab_shifts_desc') }}</p>
            <button wire:click="openShiftModal" class="edz-btn edz-btn--primary edz-btn--sm">
                <ion-icon name="add-outline" class="text-base"></ion-icon>
                {{ __('merchant_panel.new_shift') }}
            </button>
        </div>

        @if(!empty($shifts))
            <div class="edz-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-secondary">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-muted uppercase">{{ __('merchant_panel.agent') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-muted uppercase">{{ __('merchant_panel.type') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-muted uppercase">{{ __('merchant_panel.hours') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-muted uppercase">{{ __('merchant_panel.days') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-muted uppercase">{{ __('merchant_panel.status') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-ink-muted uppercase">{{ __('merchant_panel.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-100 dark:divide-ink-800">
                            @foreach($shifts as $shift)
                                <tr class="hover:bg-surface-50 dark:hover:bg-ink-800/50">
                                    <td class="px-4 py-3 font-medium text-ink">
                                        {{ $shift['membership']['user']['name'] ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-ink-muted capitalize">
                                        {{ $SHIFT_TYPES[$shift['shift_type']] ?? $shift['shift_type'] }}
                                    </td>
                                    <td class="px-4 py-3 text-ink-muted text-xs font-mono">
                                        {{ $shift['start_time'] }} — {{ $shift['end_time'] }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-ink-muted">
                                        @if(!empty($shift['days_of_week']))
                                            @php
                                                $dayLabels = [1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun'];
                                            @endphp
                                            @foreach($shift['days_of_week'] as $day)
                                                <span class="inline-block px-1.5 py-0.5 rounded bg-surface-100 dark:bg-ink-700 mr-1 mb-0.5">{{ $dayLabels[$day] ?? $day }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-ink-muted">{{ __('merchant_panel.all_days') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <button wire:click="toggleShiftActive('{{ $shift['id'] }}')"
                                                class="inline-flex items-center gap-1 text-xs font-medium px-2 py-1 rounded-full {{ $shift['is_active'] ? 'bg-success-50 text-success-700 dark:bg-success-900/30 dark:text-success-300' : 'bg-surface-100 text-ink-muted dark:bg-ink-700' }}">
                                            {{ $shift['is_active'] ? __('merchant_panel.active') : __('merchant_panel.inactive') }}
                                        </button>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <button wire:click="openShiftModal('{{ $shift['id'] }}')" class="edz-btn edz-btn--ghost edz-btn--xs">
                                                <ion-icon name="create-outline" class="text-sm"></ion-icon>
                                            </button>
                                            <button class="edz-btn edz-btn--ghost edz-btn--xs text-red-500"
                                                    x-data
                                                    x-on:click="if (await EdzSwal.confirmAction(@js(__('merchant_panel.delete_shift')), @js(__('merchant_panel.confirm_delete_shift')))) $wire.deleteShift('{{ $shift['id'] }}')">
                                                <ion-icon name="trash-outline" class="text-sm"></ion-icon>
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
                <div class="w-16 h-16 rounded-full bg-surface-100 dark:bg-ink-700 flex items-center justify-center mx-auto mb-4">
                    <ion-icon name="time-outline" class="text-3xl text-ink-muted opacity-40"></ion-icon>
                </div>
                <p class="text-ink-muted mb-4">{{ __('merchant_panel.no_shifts_yet') }}</p>
                <button wire:click="openShiftModal" class="edz-btn edz-btn--primary edz-btn--sm">
                    <ion-icon name="add-outline" class="text-base"></ion-icon>
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
                <ion-icon name="add-outline" class="text-base"></ion-icon>
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
                    <div class="edz-card overflow-hidden">
                        <div class="px-4 py-3 bg-secondary flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                    <ion-icon name="person-outline" class="text-sm text-primary-600 dark:text-primary-400"></ion-icon>
                                </div>
                                <span class="font-semibold text-sm text-ink">{{ $agentName }}</span>
                                <span class="text-xs text-ink-muted bg-surface-100 dark:bg-ink-700 px-2 py-0.5 rounded-full">{{ $items->count() }} {{ __('merchant_panel.products') }}</span>
                            </div>
                            <button wire:click="openAssignModal('{{ $memberId }}')" class="edz-btn edz-btn--ghost edz-btn--xs">
                                <ion-icon name="create-outline" class="text-sm"></ion-icon>
                                {{ __('merchant_panel.edit') }}
                            </button>
                        </div>
                        <div class="divide-y divide-surface-100 dark:divide-ink-800">
                            @foreach($items as $a)
                                <div class="px-4 py-3 flex items-center justify-between hover:bg-surface-50 dark:hover:bg-ink-800/50">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-surface-100 dark:bg-ink-700 flex items-center justify-center">
                                            <ion-icon name="cube-outline" class="text-sm text-ink-muted"></ion-icon>
                                        </div>
                                        <span class="text-sm text-ink">{{ $a['product']['name'] ?? '—' }}</span>
                                    </div>
                                    <button class="edz-btn edz-btn--ghost edz-btn--xs text-red-500"
                                            x-data
                                            x-on:click="if (await EdzSwal.confirmAction(@js(__('merchant_panel.remove_assignment')), @js(__('merchant_panel.confirm_delete_assignment')))) $wire.removeAssignment('{{ $a['id'] }}')">
                                        <ion-icon name="trash-outline" class="text-sm"></ion-icon>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="edz-card p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-surface-100 dark:bg-ink-700 flex items-center justify-center mx-auto mb-4">
                    <ion-icon name="cube-outline" class="text-3xl text-ink-muted opacity-40"></ion-icon>
                </div>
                <p class="text-ink-muted mb-4">{{ __('merchant_panel.no_assignments_yet') }}</p>
                <button wire:click="openAssignModal" class="edz-btn edz-btn--primary edz-btn--sm">
                    <ion-icon name="add-outline" class="text-base"></ion-icon>
                    {{ __('merchant_panel.assign_products') }}
                </button>
            </div>
        @endif
    @endif

    {{-- Shift Modal --}}
    @if($showShiftModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data
             x-on:keydown.escape.window="$wire.set('showShiftModal', false)">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" x-on:click="$wire.set('showShiftModal', false)"></div>
            <div class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between z-10">
                    <h3 class="text-lg font-bold text-ink">{{ $editingShiftId ? __('merchant_panel.edit_shift') : __('merchant_panel.new_shift') }}</h3>
                    <div class="flex items-center gap-2">
                        <button wire:click="saveShift" class="edz-btn edz-btn--primary edz-btn--sm">{{ __('merchant_panel.save') }}</button>
                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                x-on:click="$wire.set('showShiftModal', false)">
                            <ion-icon name="close-outline" class="text-lg"></ion-icon>
                        </button>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="edz-label">{{ __('merchant_panel.agent') }} *</label>
                        <select wire:model="shiftForm.membership_id" class="edz-input text-sm">
                            <option value="">— {{ __('merchant_panel.select_agent') }} —</option>
                            @foreach($members as $m)
                                <option value="{{ $m['id'] }}">{{ $m['user']['name'] ?? $m['id'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="edz-label">{{ __('merchant_panel.shift_type') }}</label>
                        <select wire:model="shiftForm.shift_type"
                                x-on:change="$wire.call('onShiftTypeChange')"
                                class="edz-input text-sm">
                            @foreach($SHIFT_TYPES as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.start_time') }}</label>
                            <input type="time" wire:model="shiftForm.start_time" class="edz-input text-sm">
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.end_time') }}</label>
                            <input type="time" wire:model="shiftForm.end_time" class="edz-input text-sm">
                            @error('shiftForm.end_time')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div>
                        <label class="edz-label">{{ __('merchant_panel.days_of_week') }}</label>
                        <div class="flex flex-wrap gap-2 mt-1">
                            @foreach([1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun'] as $dayNum => $dayLabel)
                                <button type="button" wire:click="toggleShiftDay({{ $dayNum }})"
                                        class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-colors {{ in_array($dayNum, $shiftForm['days_of_week'] ?? []) ? 'bg-primary-500 text-white border-primary-500' : 'bg-white dark:bg-gray-700 text-ink-muted border-gray-200 dark:border-gray-600 hover:border-primary-400' }}">
                                    {{ $dayLabel }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex items-center gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                        <input type="checkbox" wire:model="shiftForm.is_active" id="shift_active"
                               class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <label for="shift_active" class="text-sm text-ink">{{ __('merchant_panel.active') }}</label>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Assign Products Modal --}}
    @if($showAssignModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data
             x-on:keydown.escape.window="$wire.set('showAssignModal', false)">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" x-on:click="$wire.set('showAssignModal', false)"></div>
            <div class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between z-10">
                    <h3 class="text-lg font-bold text-ink">{{ __('merchant_panel.assign_products') }}</h3>
                    <div class="flex items-center gap-2">
                        <button wire:click="saveAssignments" class="edz-btn edz-btn--primary edz-btn--sm">{{ __('merchant_panel.save') }}</button>
                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                x-on:click="$wire.set('showAssignModal', false)">
                            <ion-icon name="close-outline" class="text-lg"></ion-icon>
                        </button>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="edz-label">{{ __('merchant_panel.agent') }} *</label>
                        <select wire:model="assignForm.membership_id" class="edz-input text-sm">
                            <option value="">— {{ __('merchant_panel.select_agent') }} —</option>
                            @foreach($members as $m)
                                <option value="{{ $m['id'] }}">{{ $m['user']['name'] ?? $m['id'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="edz-label">{{ __('merchant_panel.search_products') }}</label>
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="productSearch"
                                   wire:keyup.debounce.500ms="searchAssignProducts"
                                   placeholder="{{ __('merchant_panel.type_product_name') }}"
                                   class="edz-input text-sm ps-9">
                            <ion-icon name="search-outline" class="absolute start-3 top-1/2 -translate-y-1/2 text-ink-muted pointer-events-none"></ion-icon>
                        </div>
                        @if(!empty($productResults))
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg max-h-40 overflow-y-auto mt-2">
                                @foreach($productResults as $p)
                                    <label class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                        <input type="checkbox"
                                               {{ in_array($p['id'], $assignForm['product_ids'] ?? []) ? 'checked' : '' }}
                                               wire:click="toggleAssignProduct('{{ $p['id'] }}')"
                                               class="rounded border-gray-300 text-primary-600">
                                        <span class="flex-1">{{ $p['name'] }}</span>
                                        <span class="text-xs text-ink-muted">{{ currency($p['price'] ?? 0) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    @if(!empty($assignForm['product_ids']))
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.selected_count') }} ({{ count($assignForm['product_ids']) }})</label>
                            <div class="flex flex-wrap gap-2 mt-1">
                                @foreach($assignForm['product_ids'] as $pid)
                                    @php $pname = collect($allProducts)->firstWhere('id', $pid)['name'] ?? $pid @endphp
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300">
                                        {{ $pname }}
                                        <button wire:click="toggleAssignProduct('{{ $pid }}')" class="hover:text-primary-900 dark:hover:text-white">
                                            <ion-icon name="close-outline" class="text-sm"></ion-icon>
                                        </button>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
