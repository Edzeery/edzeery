<?php

use App\Domains\Order\Models\ConfirmationProductAssignment;
use App\Models\Products\Product;
use App\Models\Stores\Team\StoreMembership;
use App\Services\Stores\StoreProductScopeService;
use function Livewire\Volt\computed;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

state([
    'membership' => null,
    'search' => '',
]);

mount(function (string $membershipId): void {
    abort_unless(canManageTeam(), 403);

    $membership = StoreMembership::find($membershipId);

    if (! $membership || $membership->store_id !== currentStoreId() || ! $membership->isManager()) {
        return;
    }

    $this->membership = $membership;
});

$assigned = computed(function (): array {
    $membership = $this->membership;

    if (! $membership) {
        return [];
    }

    return ConfirmationProductAssignment::query()
        ->where('store_id', currentStoreId())
        ->where('membership_id', $membership->id)
        ->with('product:id,name,sku')
        ->get()
        ->map(fn (ConfirmationProductAssignment $assignment) => [
            'id' => $assignment->product_id,
            'name' => $assignment->product?->name,
            'sku' => $assignment->product?->sku,
        ])
        ->values()
        ->all();
});

$assignedIds = computed(fn (): array => array_column($this->assigned, 'id'));

$availableProducts = computed(function (): array {
    $membership = $this->membership;

    if (! $membership) {
        return [];
    }

    $search = trim($this->search);

    return Product::query()
        ->where('store_id', currentStoreId())
        ->where('is_active', true)
        ->whereNotIn('id', $this->assignedIds)
        ->when($search !== '', function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%');
            });
        })
        ->orderBy('name')
        ->limit(15)
        ->get(['id', 'name', 'sku'])
        ->map(fn (Product $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
        ])
        ->all();
});

$addProductScope = function (string $productId): void {
    $membership = $this->membership;

    if (! $membership) {
        return;
    }

    try {
        app(StoreProductScopeService::class)->assign(currentStore(), $membership, Product::findOrFail($productId));
        $this->search = '';
    } catch (\Exception $e) {
        $this->dispatch('swal', type: 'error', title: $e->getMessage());
    }
};

$removeProductScope = function (string $productId): void {
    $membership = $this->membership;

    if (! $membership) {
        return;
    }

    try {
        app(StoreProductScopeService::class)->revoke(currentStore(), $membership, Product::findOrFail($productId));
    } catch (\Exception $e) {
        $this->dispatch('swal', type: 'error', title: $e->getMessage());
    }
};

$close = function (): void {
    $this->search = '';
    $this->dispatch('product-scope-closed');
};
?>

<div @edz-modal-closed.window="$wire.close()">
    @if ($this->membership)
        @php $count = count($this->assignedIds); @endphp
        <x-edz.modal :is-open="true" size="md" show-close-button wire:key="product-scope-modal-{{ $this->membership->id }}">
            <div class="p-5">
                <div class="flex items-start justify-between gap-3 pe-10">
                    <div class="min-w-0">
                        <h3 class="text-base font-semibold text-ink">{{ __('teams.product_scope_title') }}</h3>
                        <p class="mt-0.5 truncate text-sm text-ink-muted">{{ $this->membership->user?->name }}</p>
                    </div>
                    <span class="edz-badge edz-badge--neutral edz-badge--sm shrink-0">
                        {{ trans_choice('teams.product_scope_assigned_count', $count, ['count' => $count]) }}
                    </span>
                </div>

                @if ($count === 0)
                    <div class="mt-4 rounded-lg border border-surface-border bg-surface-secondary/40 p-4">
                        <p class="text-sm leading-relaxed text-ink">{{ __('teams.product_scope_unrestricted') }}</p>
                    </div>
                @else
                    <ul class="mt-4 divide-y divide-surface-border rounded-lg border border-surface-border">
                        @foreach ($this->assigned as $item)
                            <li class="flex items-center justify-between gap-3 px-3 py-2.5">
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-medium text-ink">{{ $item['name'] }}</span>
                                    @if (! empty($item['sku']))
                                        <span class="mt-0.5 block text-xs text-ink-muted">{{ $item['sku'] }}</span>
                                    @endif
                                </span>
                                <button type="button"
                                    class="edz-btn edz-btn--ghost edz-btn--sm shrink-0 text-danger-600 hover:text-danger-700"
                                    x-data
                                    data-confirm-title="{{ __('teams.product_scope_remove') }}"
                                    data-confirm-text="{{ __('teams.product_scope_remove_confirm') }}"
                                    data-scope-product-id="{{ $item['id'] }}"
                                    @click.prevent="(async () => { if (await EdzSwal.confirmAction($el.dataset.confirmTitle, $el.dataset.confirmText)) await $wire.removeProductScope($el.dataset.scopeProductId) })()">
                                    {{ __('buttons.remove') }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="mt-5">
                    <label class="edz-label">{{ __('teams.product_scope_add_label') }}</label>
                    <div class="relative">
                        <x-edz.icon name="magnifying-glass" class="absolute start-3 top-1/2 w-4 h-4 -translate-y-1/2 text-ink-muted" />
                        <input type="search" wire:model.live.debounce.300ms="search"
                            class="edz-input w-full ps-9"
                            placeholder="{{ __('teams.product_scope_search_placeholder') }}">
                    </div>

                    @if ($this->availableProducts)
                        <ul class="mt-3 max-h-56 divide-y divide-surface-border overflow-y-auto rounded-lg border border-surface-border">
                            @foreach ($this->availableProducts as $product)
                                <li class="flex items-center justify-between gap-3 px-3 py-2.5">
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-medium text-ink">{{ $product['name'] }}</span>
                                        @if (! empty($product['sku']))
                                            <span class="mt-0.5 block text-xs text-ink-muted">{{ $product['sku'] }}</span>
                                        @endif
                                    </span>
                                    <button type="button" wire:click="addProductScope('{{ $product['id'] }}')"
                                        class="edz-btn edz-btn--primary edz-btn--sm shrink-0">
                                        {{ __('buttons.add') }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-3 text-sm text-ink-muted">{{ __('teams.product_scope_no_products') }}</p>
                    @endif
                </div>

                <div class="mt-5 flex justify-end border-t border-surface-border pt-4">
                    <button type="button" class="edz-btn edz-btn--primary edz-btn--sm" wire:click="close">
                        {{ __('buttons.done') }}
                    </button>
                </div>
            </div>
        </x-edz.modal>
    @endif
</div>