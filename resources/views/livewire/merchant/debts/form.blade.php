<?php

use App\Enums\Finance\DebtStatusEnum;
use App\Enums\Finance\DebtTypeEnum;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Finance\Debt;
use Illuminate\Support\Facades\Validator;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use Carbon\Carbon;

layout('components.layouts.store');

state([
    'debtId' => null,
    'type' => 'owed',
    'counterparty_name' => '',
    'total_amount' => '',
    'due_date' => '',
    'reminder_date' => '',
    'description' => '',
    'notes' => '',
    'status' => 'active',
]);

mount(function (?Debt $debt = null): void {
    if ($debt?->exists) {
        abort_unless($debt->store_id === currentStoreId(), 404);
        abort_unless(canStore(StorePermissionEnum::FINANCE_DEBT_UPDATE->value), 403);

        $this->debtId = $debt->id;
        $this->type = $debt->type->value;
        $this->counterparty_name = $debt->counterparty_name;
        $this->total_amount = $debt->total_amount;
        $this->due_date = $debt->due_date?->format('Y-m-d');
        $this->reminder_date = $debt->reminder_date?->format('Y-m-d');
        $this->description = $debt->description;
        $this->notes = $debt->notes;
        $this->status = $debt->status->value;
    } else {
        abort_unless(canStore(StorePermissionEnum::FINANCE_DEBT_CREATE->value), 403);
        $this->due_date = Carbon::now()->addDays(30)->format('Y-m-d');
    }
});

$save = function (): void {
    $rules = [
        'type' => 'required|string|in:owed,owing',
        'counterparty_name' => 'nullable|string|max:255',
        'total_amount' => 'required|numeric|min:0.01',
        'due_date' => 'nullable|date',
        'reminder_date' => 'nullable|date',
        'description' => 'nullable|string|max:1000',
        'notes' => 'nullable|string|max:2000',
        'status' => 'required|string|in:active,partial,paid,overdue',
    ];

    Validator::make($this->only(array_keys($rules)), $rules)->validate();

    $data = [
        'user_id' => auth()->id(),
        'store_id' => currentStoreId(),
        'type' => $this->type,
        'counterparty_name' => $this->counterparty_name,
        'total_amount' => $this->total_amount,
        'due_date' => $this->due_date ?: null,
        'reminder_date' => $this->reminder_date ?: null,
        'description' => $this->description,
        'notes' => $this->notes,
        'status' => $this->status,
    ];

    if ($this->debtId) {
        $debt = Debt::where('store_id', currentStoreId())->findOrFail($this->debtId);
        $debt->update($data);
    } else {
        Debt::create($data);
    }

    $this->redirect(route('merchant.debts.index', request()->route('store')), navigate: true);
};
?>

<div>
    <div class="edz-page-head">
        <div>
            <h1 class="edz-page-head__title">
                {{ $debtId ? __('finance.edit_debt') : __('finance.add_debt') }}
            </h1>
        </div>
        <a href="{{ route('merchant.debts.index', request()->route('store')) }}"
           wire:navigate class="edz-btn edz-btn--ghost edz-btn--sm">
            {{ __('finance.back') }}
        </a>
    </div>

    <div class="edz-card">
        <div class="edz-card__body">
            <form wire:submit="save">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-ink mb-1">{{ __('finance.type') }}</label>
                        <select wire:model="type" class="edz-select" required>
                            @foreach(DebtTypeEnum::cases() as $t)
                                <option value="{{ $t->value }}">{{ $t->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-ink mb-1">{{ __('finance.status') }}</label>
                        <select wire:model="status" class="edz-select" required>
                            @foreach(DebtStatusEnum::cases() as $s)
                                <option value="{{ $s->value }}">{{ $s->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-ink mb-1">{{ __('finance.counterparty') }}</label>
                        <input type="text" wire:model="counterparty_name"
                               class="edz-input" placeholder="{{ __('finance.counterparty_placeholder') }}" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-ink mb-1">{{ __('finance.total_amount') }}</label>
                        <input type="number" step="0.01" wire:model="total_amount"
                               class="edz-input" required />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-ink mb-1">{{ __('finance.due_date') }}</label>
                        <input type="date" wire:model="due_date" class="edz-input" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-ink mb-1">{{ __('finance.reminder_date') }}</label>
                        <input type="date" wire:model="reminder_date" class="edz-input" />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-ink mb-1">{{ __('finance.description') }}</label>
                        <textarea wire:model="description" class="edz-input" rows="2"></textarea>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-ink mb-1">{{ __('finance.notes') }}</label>
                        <textarea wire:model="notes" class="edz-input" rows="3"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit" class="edz-btn edz-btn--primary">
                        {{ $debtId ? __('finance.update') : __('finance.create') }}
                    </button>
                    <a href="{{ route('merchant.debts.index', request()->route('store')) }}"
                       wire:navigate class="edz-btn edz-btn--ghost">
                        {{ __('finance.back') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
