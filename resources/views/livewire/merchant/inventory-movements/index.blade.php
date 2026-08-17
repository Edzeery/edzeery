<?php

use App\Enums\Store\InventoryMovementType;
use App\Enums\Store\StorePermissionEnum;
use App\Models\InventoryMovement;
use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\usesPagination;

usesPagination();

layout('components.layouts.merchant');

state([
    'search' => '',
    'typeFilter' => '',
    'viewingId' => null,
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::INVENTORY_VIEW->value), 403);

    $this->typeFilter = request()->query('type', '');
});

$typeOptions = computed(fn () => InventoryMovementType::options());

$movements = computed(function () {
    return InventoryMovement::query()
        ->where('store_id', currentStoreId())
        ->with(['variant.product', 'user'])
        ->when($this->search !== '', function ($query) {
            $query->where(function ($q) {
                $q->whereHas('variant', fn ($v) => $v->where('sku', 'like', '%'.$this->search.'%'))
                    ->orWhereHas('variant.product', fn ($p) => $p->where('name', 'like', '%'.$this->search.'%'));
            });
        })
        ->when($this->typeFilter !== '', fn ($query) => $query->where('type', $this->typeFilter))
        ->when(request()->query('variant_id'), function ($query, $variantId) {
            $query->where('product_variant_id', $variantId);
        })
        ->orderBy('created_at', 'desc')
        ->paginate(15);
});

$toggleView = function (InventoryMovement $movement): void {
    $this->viewingId = $this->viewingId === $movement->id ? null : $movement->id;
};

$clearVariantFilter = function (): void {
    $this->redirect(route('merchant.inventory-movements.index', currentStore()), navigate: true);
};
?>

<div>
    <div class="edz-page-head">
        <div>
            <h1 class="edz-page-head__title">{{ __('titles.inventory_movements') }}</h1>
            <p class="edz-page-head__subtitle">{{ __('inventories.subtitle', ['store' => currentStore()?->name]) }}</p>
        </div>
    </div>

    <div class="edz-card">
        <div class="edz-card__header">
            <div>
                <h2 class="edz-card__title">{{ __('inventories.movements') }}</h2>
                <p class="text-sm text-ink-400">{{ __('inventories.list_subtitle') }}</p>
            </div>
            @if (request()->query('variant_id'))
                <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm" wire:click="clearVariantFilter">{{ __('buttons.clear') }}</button>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-3 border-b border-surface-border p-4 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <input type="search" class="edz-input" placeholder="{{ __('inventories.search_placeholder') }}"
                       wire:model.live.debounce.300ms="search">
            </div>
            <div>
                <select class="edz-select" wire:model.live="typeFilter">
                    <option value="">{{ __('general.all') }}</option>
                    @foreach ($this->typeOptions as $value => $label)
                        <option value="{{ $value }}" @selected($typeFilter === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-surface-border text-start text-xs uppercase tracking-wider text-ink-muted">
                        <th class="px-4 py-3 text-start font-semibold">{{ __('table.date') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('table.product') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('table.sku') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('table.type') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('table.qty') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('table.after') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('table.by') }}</th>
                        <th class="px-4 py-3 text-end font-semibold">{{ __('general.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->movements as $movement)
                        <tr class="border-b border-surface-border last:border-0 hover:bg-surface-secondary/50">
                            <td class="px-4 py-3 text-xs text-ink-muted">{{ $movement->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 font-medium text-ink">{{ $movement->variant?->product?->name ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-ink-soft">{{ $movement->variant?->sku ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    @if ($movement->type->isDecrease()) bg-danger-100 text-danger-700 dark:bg-danger-900/40 dark:text-danger-300
                                    @elseif ($movement->type->isIncrease()) bg-success-100 text-success-700 dark:bg-success-900/40 dark:text-success-300
                                    @else bg-warning-100 text-warning-700 dark:bg-warning-900/40 dark:text-warning-300 @endif">
                                    {{ $movement->type->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold {{ $movement->type->isDecrease() ? 'text-danger-600' : 'text-success-600' }}">
                                {{ $movement->type->isDecrease() ? '-' : '+' }}{{ $movement->quantity }}
                            </td>
                            <td class="px-4 py-3 font-bold {{ $movement->balance_after <= 0 ? 'text-danger-600' : 'text-ink' }}">{{ $movement->balance_after }}</td>
                            <td class="px-4 py-3 text-xs text-ink-muted">{{ $movement->user?->name ?? __('general.system') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                            wire:click="toggleView('{{ $movement->id }}')">
                                        {{ $viewingId === $movement->id ? __('buttons.close') : __('buttons.view') }}
                                    </button>
                                </div>
                            </td>
                        </tr>

                        @if ($viewingId === $movement->id)
                            <tr class="bg-surface-secondary/40">
                                <td colspan="8" class="px-4 py-4">
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                        <div>
                                            <p class="text-xs font-medium text-ink-muted">{{ __('table.type') }}</p>
                                            <p class="mt-1 text-sm font-semibold text-ink">{{ $movement->type->label() }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-ink-muted">{{ __('table.date') }}</p>
                                            <p class="mt-1 text-sm text-ink">{{ $movement->created_at?->format('Y-m-d H:i:s') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-ink-muted">{{ __('table.quantity') }}</p>
                                            <p class="mt-1 text-sm font-semibold {{ $movement->type->isDecrease() ? 'text-danger-600' : 'text-success-600' }}">
                                                {{ $movement->type->isDecrease() ? '-' : '+' }}{{ $movement->quantity }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-ink-muted">{{ __('table.after') }}</p>
                                            <p class="mt-1 text-sm font-semibold text-ink">{{ $movement->balance_after }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-ink-muted">{{ __('table.by') }}</p>
                                            <p class="mt-1 text-sm text-ink">{{ $movement->user?->name ?? __('general.system') }}
                                                @if ($movement->user?->email)
                                                    <span class="text-xs text-ink-muted">({{ $movement->user->email }})</span>
                                                @endif
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-ink-muted">{{ __('table.product') }}</p>
                                            <p class="mt-1 text-sm text-ink">{{ $movement->variant?->product?->name ?? '—' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-ink-muted">{{ __('table.sku') }}</p>
                                            <p class="mt-1 font-mono text-sm text-ink">{{ $movement->variant?->sku ?? '—' }}</p>
                                        </div>
                                        @if ($movement->source_type)
                                            <div>
                                                <p class="text-xs font-medium text-ink-muted">{{ __('general.type') }}</p>
                                                <p class="mt-1 text-sm text-ink">{{ class_basename($movement->source_type) }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-16 text-center">
                                <p class="text-sm font-medium text-ink-soft">{{ __('inventories.no_inventory') }}</p>
                                <p class="mt-1 text-sm text-ink-muted">{{ __('inventories.try_adjusting') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->movements->hasPages())
            <div class="border-t border-surface-border px-4 py-3">
                {{ $this->movements->links() }}
            </div>
        @endif
    </div>
</div>
