<?php

use App\Enums\Finance\DebtStatusEnum;
use App\Enums\Finance\DebtTypeEnum;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Finance\Debt;
use App\Models\Finance\DebtPayment;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use Carbon\Carbon;

layout('components.layouts.store');

state([
    'payment_amount' => '',
    'payment_date' => '',
    'payment_notes' => '',
]);

$debt = null;

mount(function (Debt $debt): void {
    abort_unless($debt->store_id === currentStoreId(), 404);
    abort_unless(canStore(StorePermissionEnum::FINANCE_DEBT_VIEW->value), 403);
    $this->debt = $debt->load('payments');
    $this->payment_date = Carbon::now()->format('Y-m-d');
});

$addPayment = function (): void {
    abort_unless(canStore(StorePermissionEnum::FINANCE_DEBT_UPDATE->value), 403);

    $this->validate([
        'payment_amount' => 'required|numeric|min:0.01',
        'payment_date' => 'required|date',
        'payment_notes' => 'nullable|string|max:500',
    ]);

    $remaining = $this->debt->total_amount - $this->debt->paid_amount;
    if ($this->payment_amount > $remaining) {
        $this->addError('payment_amount', __('finance.payment_exceeds_remaining'));
        return;
    }

    DebtPayment::create([
        'debt_id' => $this->debt->id,
        'store_id' => currentStoreId(),
        'amount' => $this->payment_amount,
        'payment_date' => $this->payment_date,
        'notes' => $this->payment_notes,
    ]);

    $this->debt->refresh()->load('payments');
    $this->reset(['payment_amount', 'payment_notes']);
    $this->payment_date = Carbon::now()->format('Y-m-d');
    $this->dispatch('payment-added');
};

$formatAmount = function (float $amount): string {
    return number_format($amount, 2);
};
?>

<div>
    @if($debt)
        <div class="edz-page-head">
            <div>
                <h1 class="edz-page-head__title">{{ $debt->counterparty_name ?? __('finance.debt') }}</h1>
                <p class="edz-page-head__subtitle">{{ $debt->description ?? '—' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('merchant.debts.edit', [request()->route('store'), $debt]) }}"
                   wire:navigate class="edz-btn edz-btn--secondary edz-btn--sm">
                    {{ __('finance.edit') }}
                </a>
                <a href="{{ route('merchant.debts.index', request()->route('store')) }}"
                   wire:navigate class="edz-btn edz-btn--ghost edz-btn--sm">
                    {{ __('finance.back') }}
                </a>
            </div>
        </div>

        {{-- Summary --}}
        <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="edz-card">
                <div class="edz-card__body">
                    <p class="text-sm text-ink-muted">{{ __('finance.total_amount') }}</p>
                    <p class="text-xl font-bold text-ink">{{ $formatAmount($debt->total_amount) }}</p>
                </div>
            </div>
            <div class="edz-card">
                <div class="edz-card__body">
                    <p class="text-sm text-ink-muted">{{ __('finance.paid_amount') }}</p>
                    <p class="text-xl font-bold text-success-600">{{ $formatAmount($debt->paid_amount) }}</p>
                </div>
            </div>
            <div class="edz-card">
                <div class="edz-card__body">
                    <p class="text-sm text-ink-muted">{{ __('finance.remaining') }}</p>
                    <p class="text-xl font-bold text-danger-600">{{ $formatAmount($debt->remaining_amount) }}</p>
                </div>
            </div>
            <div class="edz-card">
                <div class="edz-card__body">
                    <p class="text-sm text-ink-muted">{{ __('finance.status') }}</p>
                    <div class="mt-1"><x-merchant.status domain="debt" :status="$debt->status->value" /></div>
                </div>
            </div>
        </div>

        {{-- Info + Add Payment --}}
        <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-2">
            <div class="edz-card">
                <div class="edz-card__header">
                    <h3 class="edz-card__title">{{ __('finance.details') }}</h3>
                </div>
                <div class="edz-card__body">
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-ink-muted">{{ __('finance.type') }}</dt>
                            <dd><x-merchant.status domain="debt_type" :status="$debt->type->value" /></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-ink-muted">{{ __('finance.counterparty') }}</dt>
                            <dd class="text-sm font-medium text-ink">{{ $debt->counterparty_name ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-ink-muted">{{ __('finance.due_date') }}</dt>
                            <dd class="text-sm text-ink">{{ $debt->due_date?->format('Y-m-d') ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-ink-muted">{{ __('finance.reminder_date') }}</dt>
                            <dd class="text-sm text-ink">{{ $debt->reminder_date?->format('Y-m-d') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-ink-muted mb-1">{{ __('finance.progress') }}</dt>
                            <div class="w-full bg-surface-tertiary rounded-full h-2 dark:bg-surface-secondary">
                                <div class="bg-success-500 h-2 rounded-full" style="width: {{ $debt->progress }}%"></div>
                            </div>
                            <span class="text-xs text-ink-muted">{{ $debt->progress }}%</span>
                        </div>
                    </dl>
                </div>
            </div>

            @if(canStore(\App\Enums\Store\StorePermissionEnum::FINANCE_DEBT_UPDATE->value))
                <div class="edz-card">
                    <div class="edz-card__header">
                        <h3 class="edz-card__title">{{ __('finance.add_payment') }}</h3>
                    </div>
                    <div class="edz-card__body">
                        <form wire:submit="addPayment" class="space-y-4" x-data="edzDirty()">
                            <div>
                                <label class="block text-sm font-medium text-ink mb-1">{{ __('finance.amount') }}</label>
                                <input type="number" step="0.01" wire:model="payment_amount"
                                       class="edz-input" required />
                                @error('payment_amount') <p class="mt-1 text-sm text-danger-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-ink mb-1">{{ __('finance.payment_date') }}</label>
                                <input type="date" wire:model="payment_date"
                                       class="edz-input" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-ink mb-1">{{ __('finance.notes') }}</label>
                                <textarea wire:model="payment_notes" class="edz-input" rows="2"></textarea>
                            </div>
                            <button type="submit" class="edz-btn edz-btn--primary edz-btn--sm">
                                {{ __('finance.add_payment') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        {{-- Payments History --}}
        <div class="edz-card">
            <div class="edz-card__header">
                <h3 class="edz-card__title">{{ __('finance.payments_history') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-surface-border text-start text-xs uppercase tracking-wider text-ink-muted">
                            <th class="px-4 py-3 text-start font-semibold">{{ __('finance.date') }}</th>
                            <th class="px-4 py-3 text-start font-semibold">{{ __('finance.amount') }}</th>
                            <th class="px-4 py-3 text-start font-semibold">{{ __('finance.notes') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($debt->payments()->latest('payment_date')->get() as $payment)
                            <tr class="border-b border-surface-border last:border-0">
                                <td class="px-4 py-3 text-ink-soft">{{ $payment->payment_date->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 font-medium text-success-600">{{ $formatAmount($payment->amount) }}</td>
                                <td class="px-4 py-3 text-ink-soft">{{ $payment->notes ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-12 text-center">
                                    <p class="text-sm text-ink-soft">{{ __('finance.no_payments_yet') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
