<?php

use App\Enums\Finance\DebtStatusEnum;
use App\Enums\Finance\DebtTypeEnum;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Finance\Debt;
use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\usesPagination;

usesPagination();

layout('components.layouts.store');

state([
    'search' => '',
    'type' => '',
    'status' => '',
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::FINANCE_DEBT_VIEW->value), 403);
});

$debts = computed(function () {
    return Debt::query()
        ->where('store_id', currentStoreId())
        ->when($this->search !== '', function ($query) {
            $query->where(function ($q) {
                $q->where('counterparty_name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        })
        ->when($this->type !== '', fn ($q) => $q->where('type', $this->type))
        ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
        ->latest()
        ->paginate(15);
});

$totalOwed = computed(function () {
    return Debt::query()->where('store_id', currentStoreId())->owed()->active()->sum('total_amount')
         - Debt::query()->where('store_id', currentStoreId())->owed()->active()->sum('paid_amount');
});

$totalOwing = computed(function () {
    return Debt::query()->where('store_id', currentStoreId())->owing()->active()->sum('total_amount')
         - Debt::query()->where('store_id', currentStoreId())->owing()->active()->sum('paid_amount');
});

$canCreate = fn () => canStore(StorePermissionEnum::FINANCE_DEBT_CREATE->value);
$canUpdate = fn () => canStore(StorePermissionEnum::FINANCE_DEBT_UPDATE->value);
$canDelete = fn () => canStore(StorePermissionEnum::FINANCE_DEBT_DELETE->value);

$delete = function (Debt $debt): void {
    abort_unless(canStore(StorePermissionEnum::FINANCE_DEBT_DELETE->value), 403);
    $debt->delete();
    $this->redirect(route('merchant.debts.index', request()->route('store')), navigate: true);
};

$formatAmount = function (float $amount): string {
    return number_format($amount, 2);
};
?>

<div>
    <div class="edz-page-head">
        <div>
            <h1 class="edz-page-head__title">{{ __('finance.debts') }}</h1>
            <p class="edz-page-head__subtitle">{{ __('finance.manage_debts_subtitle') }}</p>
        </div>
        @if ($canCreate())
            <a href="{{ route('merchant.debts.create', request()->route('store')) }}" wire:navigate
               class="edz-btn edz-btn--primary edz-btn--sm">
                <x-edz.icon name="plus" class="edz-btn__icon" />
                {{ __('finance.add_debt') }}
            </a>
        @endif
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2">
        <div class="edz-card">
            <div class="edz-card__body">
                <p class="text-sm text-ink-muted">{{ __('finance.total_receivable') }}</p>
                <p class="text-2xl font-bold text-success-600">{{ $formatAmount($totalOwed) }}</p>
            </div>
        </div>
        <div class="edz-card">
            <div class="edz-card__body">
                <p class="text-sm text-ink-muted">{{ __('finance.total_payable') }}</p>
                <p class="text-2xl font-bold text-danger-600">{{ $formatAmount($totalOwing) }}</p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="edz-card mb-6">
        <div class="edz-card__body">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div>
                    <input type="text" wire:model.live.debounce.300ms="search"
                           placeholder="{{ __('finance.search_placeholder') }}"
                           class="edz-input" />
                </div>
                <div>
                    <select wire:model.live="type" class="edz-select">
                        <option value="">{{ __('finance.all_types') }}</option>
                        @foreach(DebtTypeEnum::cases() as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select wire:model.live="status" class="edz-select">
                        <option value="">{{ __('finance.all_statuses') }}</option>
                        @foreach(DebtStatusEnum::cases() as $st)
                            <option value="{{ $st->value }}">{{ $st->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="edz-card">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-surface-border text-start text-xs uppercase tracking-wider text-ink-muted">
                        <th class="px-4 py-3 text-start font-semibold">{{ __('finance.counterparty') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('finance.type') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('finance.total_amount') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('finance.paid_amount') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('finance.remaining') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('finance.due_date') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('finance.status') }}</th>
                        <th class="px-4 py-3 text-end font-semibold"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($debts as $debt)
                        <tr class="border-b border-surface-border last:border-0 hover:bg-surface-secondary/50">
                            <td class="px-4 py-3 font-medium text-ink">{{ $debt->counterparty_name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <x-merchant.status domain="debt_type" :status="$debt->type->value" />
                            </td>
                            <td class="px-4 py-3 text-ink-soft">{{ $formatAmount($debt->total_amount) }}</td>
                            <td class="px-4 py-3 text-ink-soft">{{ $formatAmount($debt->paid_amount) }}</td>
                            <td class="px-4 py-3 text-ink-soft">{{ $formatAmount($debt->remaining_amount) }}</td>
                            <td class="px-4 py-3 text-ink-soft">{{ $debt->due_date?->format('Y-m-d') ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <x-merchant.status domain="debt" :status="$debt->status->value" />
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('merchant.debts.show', [request()->route('store'), $debt]) }}"
                                       wire:navigate class="edz-btn edz-btn--ghost edz-btn--sm">
                                        {{ __('finance.details') }}
                                    </a>
                                    @if ($canUpdate())
                                        <a href="{{ route('merchant.debts.edit', [request()->route('store'), $debt]) }}"
                                           wire:navigate class="edz-btn edz-btn--ghost edz-btn--sm">
                                            {{ __('finance.edit') }}
                                        </a>
                                    @endif
                                    @if ($canDelete())
                                        <button x-data
                                                @click.prevent="if (await EdzSwal.confirmAction('{{ __('finance.delete') }}', '{{ __('finance.confirm_delete') }}')) $wire.delete('{{ $debt->id }}')"
                                                class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 hover:text-danger-700">
                                            <x-edz.icon name="trash" class="edz-btn__icon" />
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-16 text-center">
                                <p class="text-sm font-medium text-ink-soft">{{ __('finance.no_debts_found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($debts->hasPages())
            <div class="border-t border-surface-border px-4 py-3">
                {{ $debts->links() }}
            </div>
        @endif
    </div>
</div>
