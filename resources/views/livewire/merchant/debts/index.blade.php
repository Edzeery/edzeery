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
    $this->dispatch('swal', type: 'success', title: __('messages.deleted_successfully'));
    $this->redirect(route('merchant.debts.index', currentStore()), navigate: true);
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
        @if ($this->canCreate())
            <a href="{{ route('merchant.debts.create', currentStore()) }}" wire:navigate
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
                <p class="text-2xl font-bold text-success-600">{{ $this->formatAmount($this->totalOwed) }}</p>
            </div>
        </div>
        <div class="edz-card">
            <div class="edz-card__body">
                <p class="text-sm text-ink-muted">{{ __('finance.total_payable') }}</p>
                <p class="text-2xl font-bold text-danger-600">{{ $this->formatAmount($this->totalOwing) }}</p>
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
                    <x-edz.select
                        wire:model.live="type"
                        :options="collect(DebtTypeEnum::cases())->map(fn ($t) => ['value' => $t->value, 'label' => $t->label()])->values()->all()"
                        placeholder="{{ __('finance.all_types') }}"
                    />
                </div>
                <div>
                    <x-edz.select
                        wire:model.live="status"
                        :options="collect(DebtStatusEnum::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()])->values()->all()"
                        placeholder="{{ __('finance.all_statuses') }}"
                    />
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
                        <th class="px-4 py-3 text-end font-semibold text-xs uppercase text-ink-muted">{{ __('buttons.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->debts as $debt)
                        <tr class="border-b border-surface-border last:border-0 hover:bg-surface-secondary/50">
                            <td class="px-4 py-3 font-medium text-ink">{{ $debt->counterparty_name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <x-merchant.status domain="debt_type" :status="$debt->type->value" />
                            </td>
                            <td class="px-4 py-3 text-ink-soft">{{ $this->formatAmount($debt->total_amount) }}</td>
                            <td class="px-4 py-3 text-ink-soft">{{ $this->formatAmount($debt->paid_amount) }}</td>
                            <td class="px-4 py-3 text-ink-soft">{{ $this->formatAmount($debt->remaining_amount) }}</td>
                            <td class="px-4 py-3 text-ink-soft">{{ $debt->due_date?->format('Y-m-d') ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <x-merchant.status domain="debt" :status="$debt->status->value" />
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('merchant.debts.show', [currentStore(), $debt]) }}"
                                       wire:navigate class="edz-btn edz-btn--ghost edz-btn--sm">
                                        {{ __('finance.details') }}
                                    </a>
                                    @if ($this->canUpdate())
                                        <a href="{{ route('merchant.debts.edit', [currentStore(), $debt]) }}"
                                           wire:navigate class="edz-btn edz-btn--ghost edz-btn--sm">
                                            {{ __('finance.edit') }}
                                        </a>
                                    @endif
                                    @if ($this->canDelete())
                                        <button x-data
                                                data-confirm-title="{{ __('finance.delete') }}"
                                                data-confirm-text="{{ __('finance.confirm_delete') }}"
                                                @click.prevent="(async () => { if (await EdzSwal.confirmAction($el.dataset.confirmTitle, $el.dataset.confirmText)) await $wire.delete(Number($el.dataset.deleteId)) })()"
                                                data-delete-id="{{ $debt->id }}"
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
        @if ($this->debts->hasPages())

            <div class="border-t border-surface-border px-4 py-3">
                {{ $this->debts->links() }}
            </div>
        @endif
    </div>
</div>
