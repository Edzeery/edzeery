<?php

use App\Enums\Store\StorePermissionEnum;
use App\Models\InventoryMovement;
use App\Models\Products\ProductVariant;
use App\Services\InventoryService;
use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\usesPagination;

usesPagination();

layout('components.layouts.merchant');

state([
    'search' => '',
    'adjustingId' => null,
    'adjust_quantity' => '',
    'adjust_reason' => '',
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::INVENTORY_VIEW->value), 403);
});

$stats = computed(function (): array {
    $base = ProductVariant::query()->where('store_id', currentStoreId());

    return [
        'total' => (clone $base)->sum('stock'),
        'low' => (clone $base)
            ->where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->count(),
        'out' => (clone $base)->where('stock', '<=', 0)->count(),
        'movements7' => InventoryMovement::query()
            ->where('store_id', currentStoreId())
            ->where('created_at', '>=', now()->subDays(7))
            ->count(),
    ];
});

$inventories = computed(function () {
    return ProductVariant::query()
        ->where('store_id', currentStoreId())
        ->with('product')
        ->when($this->search !== '', function ($query) {
            $query->where(function ($q) {
                $q->where('sku', 'like', '%'.$this->search.'%')
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', '%'.$this->search.'%'));
            });
        })
        ->orderBy('stock', 'asc')
        ->paginate(15);
});

$canAdjust = fn () => canStore(StorePermissionEnum::INVENTORY_UPDATE->value);

$stockBadge = function (ProductVariant $variant): array {
    return match ($variant->stockStatus()) {
        'out' => ['text' => __('stock_alerts.out_of_stock'), 'class' => 'text-danger-700 bg-danger-100 dark:text-danger-300 dark:bg-danger-900/40'],
        'low' => ['text' => __('stock_alerts.low_stock'), 'class' => 'text-warning-700 bg-warning-100 dark:text-warning-300 dark:bg-warning-900/40'],
        default => ['text' => 'In stock', 'class' => 'text-success-700 bg-success-100 dark:text-success-300 dark:bg-success-900/40'],
    };
};

$toggleAdjust = function (ProductVariant $variant): void {
    $this->adjustingId = $this->adjustingId === $variant->id ? null : $variant->id;
    $this->adjust_quantity = '';
    $this->adjust_reason = '';
};

$movementsUrl = function (ProductVariant $variant): string {
    return route('merchant.inventory-movements.index', [currentStore(), 'variant_id' => $variant->id]);
};

$adjust = function (ProductVariant $variant): void {
    abort_unless($this->canAdjust(), 403);

    $validated = $this->validate([
        'adjust_quantity' => ['required', 'integer', 'min:1'],
        'adjust_reason' => ['nullable', 'string', 'max:255'],
    ]);

    try {
        InventoryService::adjust(
            $variant,
            (int) $validated['adjust_quantity'],
            $validated['adjust_reason'] ?: null
        );

        session()->flash('merchant.saved', __('inventories.adjust_stock'));
    } catch (\Illuminate\Validation\ValidationException $e) {
        $this->addError('adjust_quantity', $e->getMessage());
    }

    $this->reset('adjustingId', 'adjust_quantity', 'adjust_reason');
};
?>

<div>
    <div class="edz-page-head">
        <div>
            <h1 class="edz-page-head__title">{{ __('inventories.title') }}</h1>
            <p class="edz-page-head__subtitle">{{ __('inventories.subtitle', ['store' => currentStore()?->name]) }}</p>
        </div>
    </div>

    @if (session('merchant.saved'))
        <div class="mb-6 rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-800 dark:bg-success-950 dark:text-success-300">
            {{ session('merchant.saved') }}
        </div>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="edz-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-ink-soft">{{ __('inventories.total_stock') }}</p>
                    <p class="mt-1 text-2xl font-bold text-ink">{{ number_format($this->stats['total']) }}</p>
                </div>
                <span class="grid h-10 w-10 place-items-center rounded-lg bg-success-100 text-success-700 dark:bg-success-900/40 dark:text-success-300">Σ</span>
            </div>
        </div>
        <div class="edz-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-ink-soft">{{ __('inventories.low_stock') }}</p>
                    <p class="mt-1 text-2xl font-bold text-ink">{{ number_format($this->stats['low']) }}</p>
                </div>
                <span class="grid h-10 w-10 place-items-center rounded-lg bg-warning-100 text-warning-700 dark:bg-warning-900/40 dark:text-warning-300">!</span>
            </div>
        </div>
        <div class="edz-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-ink-soft">{{ __('inventories.out_of_stock') }}</p>
                    <p class="mt-1 text-2xl font-bold text-ink">{{ number_format($this->stats['out']) }}</p>
                </div>
                <span class="grid h-10 w-10 place-items-center rounded-lg bg-danger-100 text-danger-700 dark:bg-danger-900/40 dark:text-danger-300">✕</span>
            </div>
        </div>
        <div class="edz-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-ink-soft">{{ __('inventories.movements_7d') }}</p>
                    <p class="mt-1 text-2xl font-bold text-ink">{{ number_format($this->stats['movements7']) }}</p>
                </div>
                <span class="grid h-10 w-10 place-items-center rounded-lg bg-brand-100 text-brand-700 dark:bg-brand-900/40 dark:text-brand-300">⇄</span>
            </div>
        </div>
    </div>

    <div class="edz-card">
        <div class="edz-card__header">
            <div>
                <h2 class="edz-card__title">{{ __('inventories.list_title') }}</h2>
                <p class="text-sm text-ink-400">{{ __('inventories.list_subtitle') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 border-b border-surface-border p-4 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <input type="search" class="edz-input" placeholder="{{ __('inventories.search_placeholder') }}"
                       wire:model.live.debounce.300ms="search">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-surface-border text-start text-xs uppercase tracking-wider text-ink-muted">
                        <th class="px-4 py-3 text-start font-semibold">{{ __('table.product') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('table.sku') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('table.stock') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('general.status') }}</th>
                        <th class="px-4 py-3 text-end font-semibold">{{ __('general.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->inventories as $variant)
                        <tr class="border-b border-surface-border last:border-0 hover:bg-surface-secondary/50">
                            <td class="px-4 py-3 font-medium text-ink">{{ $variant->product?->name ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-ink-soft">{{ $variant->sku }}</td>
                            <td class="px-4 py-3 font-semibold {{ $variant->stock <= 0 ? 'text-danger-600' : 'text-success-600' }}">{{ $variant->stock }}</td>
                            <td class="px-4 py-3">
                                @php $badge = $this->stockBadge($variant); @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge['class'] }}"
                                      title="Threshold: {{ $variant->low_stock_threshold }}">
                                    {{ $badge['text'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    @if ($this->canAdjust())
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                                wire:click="toggleAdjust('{{ $variant->id }}')">
                                             {{ $adjustingId === $variant->id ? __('inventories.cancel') : __('inventories.adjust_stock') }}
                                        </button>
                                    @endif
                                    <a href="{{ $this->movementsUrl($variant) }}" wire:navigate class="edz-btn edz-btn--ghost edz-btn--sm">{{ __('inventories.movements') }}</a>
                                </div>
                            </td>
                        </tr>

                        @if ($adjustingId === $variant->id)
                            <tr class="bg-surface-secondary/40">
                                <td colspan="5" class="px-4 py-4">
                                    <form wire:submit="adjust('{{ $variant->id }}')" class="flex flex-wrap items-end gap-3">
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-ink-soft" for="adjust-qty">{{ __('inventories.new_quantity') }}</label>
                                            <input id="adjust-qty" type="number" min="0" class="edz-input edz-input--sm"
                                                   wire:model="adjust_quantity" placeholder="{{ $variant->stock }}">
                                            @error('adjust_quantity')
                                                <p class="mt-1 text-xs text-danger-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="min-w-48">
                                            <label class="mb-1 block text-xs font-medium text-ink-soft" for="adjust-reason">{{ __('inventories.reason') }}</label>
                                            <input id="adjust-reason" type="text" maxlength="255" class="edz-input edz-input--sm"
                                                   wire:model="adjust_reason" placeholder="{{ __('inventories.reason_placeholder') }}">
                                        </div>
                                         <button type="submit" class="edz-btn edz-btn--primary edz-btn--sm">{{ __('inventories.adjust_btn') }}</button>
                                        <span class="text-xs text-ink-muted">{{ __('inventories.current_stock', ['count' => $variant->stock]) }}</span>
                                    </form>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-16 text-center">
                                <p class="text-sm font-medium text-ink-soft">{{ __('inventories.no_inventory') }}</p>
                                <p class="mt-1 text-sm text-ink-muted">{{ __('inventories.try_adjusting') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->inventories->hasPages())
            <div class="border-t border-surface-border px-4 py-3">
                {{ $this->inventories->links() }}
            </div>
        @endif
    </div>
</div>
