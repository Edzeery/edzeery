<?php

use App\Domains\Cart\Support\OrderRules;
use App\Models\Products\Product;
use Illuminate\Support\Collection;
use function Livewire\Volt\computed;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

state([
    'productId' => null,
    'quantities' => [],
    'direct' => false,
]);

mount(function (string $productId, bool $direct = false): void {
    $this->direct = $direct;

    // Graceful degradation: an unknown/foreign/deactivated product simply
    // renders an empty ordering unit list instead of hard-failing the page.
    $storeId = currentStoreId();

    $product = $storeId
        ? Product::query()
            ->whereKey($productId)
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->first()
        : null;

    $this->productId = $product?->id;
});

$product = computed(function (): ?Product {
    if (! $this->productId) {
        return null;
    }

    return Product::query()
        ->whereKey($this->productId)
        ->where('is_active', true)
        ->with('variants.optionValues.option')
        ->first();
});

$tracksInventory = computed(fn (): bool => OrderRules::tracksInventory(currentStore()));

$allowsBackorder = computed(fn (): bool => OrderRules::allowsBackorder(currentStore()));

$limits = computed(function (): array {
    $product = $this->product;

    return $product ? OrderRules::limits($product, currentStore()) : ['min' => 1, 'max' => null];
});

/**
 * Ordering units grouped for the storefront UI.
 *
 * Single-dimension products (one variant-defining option) are rendered as
 * option VALUES whose behaviour follows the merchant's option Type:
 * radio/select -> exclusive single pick, checkbox -> multiple picks.
 * Multi-dimension combinations fall back to variant rows.
 */
$orderGroups = computed(function (): Collection {
    $product = $this->product;

    if (! $product) {
        return collect();
    }

    $variants = $product->variants->filter(fn ($v) => $v->is_active)->values();

    if ($variants->isEmpty()) {
        return collect();
    }

    $usedOptions = $variants
        ->flatMap(fn ($v) => $v->optionValues)
        ->map(fn ($val) => $val->option?->id)
        ->filter()
        ->unique();

    $isSingleDimension = $usedOptions->count() === 1
        && $variants->every(fn ($v) => $v->optionValues->count() === 1);

    $makeUnit = function ($variant, string $label) {
        $out = $variant->isOutOfStock();

        return [
            'variant' => $variant,
            'label' => $label,
            'out' => $out,
            // Beyond-stock ordering is allowed only when the merchant turned
            // on backorder; with tracking disabled stock is simply irrelevant.
            'preorder' => $out && $this->allowsBackorder && $this->tracksInventory,
            'cap' => OrderRules::lineCap($variant),
        ];
    };

    if ($isSingleDimension) {
        $option = $variants->first()->optionValues->first()->option;

        $units = $variants
            ->sortBy(fn ($v) => $v->optionValues->first()->sort_order ?? 0)
            ->values()
            ->map(fn ($v) => $makeUnit($v, (string) $v->optionValues->first()->value));

        return collect([[
            'key' => (string) $option->id,
            'label' => $option->name,
            'exclusive' => in_array($option->type->value, ['radio', 'select'], true),
            'units' => $units,
        ]]);
    }

    return collect([[
        'key' => 'combo',
        'label' => null,
        'exclusive' => false,
        'units' => $variants->map(fn ($v) => $makeUnit($v, $v->name)),
    ]]);
});

/**
 * Validate + normalize the posted quantities against the shared order rules.
 * Returns [ok, error, lines].
 */
$resolveLines = function (): array {
    $limits = $this->limits;
    $lines = [];
    $hasError = null;

    foreach ($this->orderGroups as $group) {
        $pickedInGroup = 0;

        foreach ($group['units'] as $unit) {
            $qty = (int) ($this->quantities[$unit['variant']->id] ?? 0);

            if ($qty <= 0) {
                continue;
            }

            if ($unit['out'] && ! $unit['preorder']) {
                continue;
            }

            // Exclusive option types accept a single picked value.
            if ($group['exclusive']) {
                $pickedInGroup++;

                if ($pickedInGroup > 1) {
                    $hasError = __('storefront.exclusive_option');
                }
            }

            $qty = min($qty, $unit['cap'] ?? $qty);

            if ($qty < $limits['min']) {
                $hasError = __('storefront.min_order_error', ['min' => $limits['min']]);
            }

            if ($qty > 0) {
                $lines[] = ['variant' => $unit['variant'], 'quantity' => $qty];
            }
        }
    }

    return [$hasError === null, $hasError, $lines];
};

$clearQuantities = function (): void {
    $this->quantities = [];
};

$addAllToCart = function (): void {
    $storeId = currentStoreId();
    if (! $storeId) {
        return;
    }

    [$ok, $error, $lines] = $this->resolveLines();

    if (! $ok) {
        $this->dispatch('swal', type: 'error', title: $error);
        return;
    }

    if ($lines === []) {
        $this->dispatch('swal', type: 'warning', title: __('storefront.nothing_selected'));
        return;
    }

    $cartService = app(\App\Domains\Cart\Services\CartService::class);

    foreach ($lines as $line) {
        $cartService->addItem($storeId, $line['variant']->id, $line['quantity']);
    }

    $this->clearQuantities();
    $this->dispatch('cart-updated');
    $this->dispatch('swal', type: 'success', title: __('storefront.added_to_cart'));
};

// Direct-order mode: single-product stores skip the cart UI entirely.
$buyNowFromMatrix = function () {
    $storeId = currentStoreId();
    if (! $storeId) {
        return null;
    }

    [$ok, $error, $lines] = $this->resolveLines();

    if (! $ok) {
        $this->dispatch('swal', type: 'error', title: $error);
        return null;
    }

    if ($lines === []) {
        $this->dispatch('swal', type: 'warning', title: __('storefront.nothing_selected'));
        return null;
    }

    $cartService = app(\App\Domains\Cart\Services\CartService::class);

    // A fresh direct order starts from a clean slate: drop anything already
    // in this visitor's cart so checkout contains exactly what was ordered.
    $cartService->clear($storeId);

    foreach ($lines as $line) {
        $cartService->addItem($storeId, $line['variant']->id, $line['quantity']);
    }

    return redirect()->route('storefront.checkout', ['store' => currentStore()?->slug]);
};
?>

@php
    // Explicit component reads keep the template independent of Volt's
    // variable-injection behaviour.
    $matrixLimits = $this->limits;
    $matrixGroups = $this->orderGroups;
    $matrixTracksInventory = $this->tracksInventory;
@endphp

<div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5"
    data-matrix-root
    data-min="{{ $matrixLimits['min'] }}"
    x-data="{
        pick(box) {
            const root = box.closest('[data-matrix-root]');
            const row = box.closest('li');
            const input = row ? row.querySelector('input[type=number]') : null;
            if (!input || box.disabled) { return; }
            if (box.checked) {
                const group = box.getAttribute('data-exclusive-group');
                if (group) {
                    root.querySelectorAll('input[data-exclusive-group=\'' + group + '\']').forEach((other) => {
                        if (other !== box) {
                            other.checked = false;
                            const otherInput = other.closest('li').querySelector('input[type=number]');
                            otherInput.value = 0;
                            otherInput.dispatchEvent(new Event('input'));
                        }
                    });
                }
                const min = parseInt(root.dataset.min) || 1;
                if ((parseInt(input.value) || 0) < min) { input.value = String(min); }
                input.dispatchEvent(new Event('input'));
            } else {
                input.value = '0';
                input.dispatchEvent(new Event('input'));
            }
        },
        dec(input) {
            input.value = String(Math.max(0, (parseInt(input.value) || 0) - 1));
            input.dispatchEvent(new Event('input'));
        },
        inc(input, cap) {
            const next = (parseInt(input.value) || 0) + 1;
            const limit = parseInt(cap);
            input.value = Number.isFinite(limit) && limit > 0 ? String(Math.min(next, limit)) : String(next);
            input.dispatchEvent(new Event('input'));
        }
    }">
    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
        <x-edz.icon name="adjustments-horizontal" class="w-4 h-4 store-text-primary" />
        {{ __('storefront.variants_matrix_title') }}
    </h3>

    @if ($matrixLimits['min'] > 1 || $matrixLimits['max'])
        <p class="text-xs text-ink-muted mb-4">
            @if ($matrixLimits['min'] > 1)
                {{ __('storefront.min_order_hint', ['min' => $matrixLimits['min']]) }}
            @endif
            @if ($matrixLimits['max'])
                {{ __('storefront.max_order_hint', ['max' => $matrixLimits['max']]) }}
            @endif
        </p>
    @endif

    @if ($matrixGroups->isEmpty())
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('storefront.no_products_found') }}</p>
    @else
        @foreach ($matrixGroups as $group)
            @if ($group['label'])
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted mb-2">{{ $group['label'] }}</p>
            @endif

            <ul class="divide-y divide-gray-100 dark:divide-gray-800 @if ($loop->last) mb-4 @endif">
                @foreach ($group['units'] as $unit)
                    @php
                        $matrixVariant = $unit['variant'];
                        $matrixBlocked = $unit['out'] && ! $unit['preorder'];
                    @endphp
                    <li wire:key="vm-row-{{ $matrixVariant->id }}"
                        data-cap="{{ $unit['cap'] ?? '' }}"
                        class="py-3 first:pt-0 last:pb-0"
                        x-data="{ on: false, full: false, sync() { const q = this.$refs.q; const v = (q && parseInt(q.value)) || 0; this.on = v > 0; const c = parseInt(this.$el.dataset.cap); this.full = !isNaN(c) && c > 0 && v >= c; if (this.$refs.box) { this.$refs.box.checked = this.on; } } }"
                        x-init="sync()">
                        <div class="flex items-center justify-between gap-3">
                            {{-- Selection gate: ticking reveals the quantity stepper. --}}
                            <label class="flex items-start gap-3 cursor-pointer select-none min-w-0 flex-1">
                                <input type="checkbox" x-ref="box" x-on:change="pick($el)" @if ($matrixBlocked) disabled @endif
                                    @if ($group['exclusive']) data-exclusive-group="{{ $group['key'] }}" @endif
                                    class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 dark:border-gray-600 store-bg-primary focus:ring-0 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                                <span class="min-w-0">
                                    <span class="block text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $unit['label'] }}
                                    </span>
                                    <span class="block text-xs mt-0.5">
                                        <span class="store-text-primary font-semibold">{{ currency($matrixVariant->price) }}</span>
                                        @if ($matrixTracksInventory)
                                            @if ($unit['preorder'])
                                                <span class="ms-1 inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">{{ __('storefront.pre_order') }}</span>
                                            @elseif ($unit['out'])
                                                <span class="ms-1 text-red-500 dark:text-red-400">{{ __('storefront.out_of_stock') }}</span>
                                            @else
                                                <span class="ms-1 text-emerald-600 dark:text-emerald-400">{{ __('storefront.in_stock') }}</span>
                                            @endif
                                        @endif
                                    </span>
                                </span>
                            </label>

                            {{-- Quantity stepper, mirroring the mini-cart design. --}}
                            <div class="shrink-0 flex items-center rounded-xl border border-gray-300 dark:border-gray-600 overflow-hidden transition-opacity duration-150"
                                x-show="on"
                                x-cloak>
                                <button type="button" tabindex="-1"
                                    x-on:click="dec($el.parentElement.querySelector('input'))"
                                    @if ($matrixBlocked) disabled @endif
                                    class="w-9 h-9 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition disabled:opacity-40"
                                    aria-label="-">
                                    <x-edz.icon name="minus" class="w-3.5 h-3.5" />
                                </button>
                                <input type="number" min="0" value="0" x-ref="q"
                                    wire:model="quantities.{{ $matrixVariant->id }}"
                                    x-on:input="sync()"
                                    @if ($matrixBlocked) disabled @endif
                                    class="w-12 h-9 text-center text-sm font-semibold bg-transparent border-x border-gray-300 dark:border-gray-600 focus:outline-none disabled:opacity-50 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                <button type="button" tabindex="-1"
                                    x-on:click="inc($el.parentElement.querySelector('input'), $el.closest('li').dataset.cap)"
                                    @if ($matrixBlocked) disabled @endif
                                    :disabled="full"
                                    class="w-9 h-9 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition disabled:opacity-40 disabled:cursor-not-allowed"
                                    aria-label="+">
                                    <x-edz.icon name="plus" class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endforeach

        <div class="mt-2">
            @if ($direct)
                <button type="button" wire:click="buyNowFromMatrix"
                    class="store-btn-primary w-full h-12 rounded-xl font-semibold flex items-center justify-center gap-2">
                    <x-edz.icon name="shopping-bag" class="w-5 h-5" />
                    {{ __('storefront.order_now') }}
                </button>
            @else
                <button type="button" wire:click="addAllToCart"
                    class="store-btn-primary w-full h-12 rounded-xl font-semibold flex items-center justify-center gap-2">
                    <x-edz.icon name="shopping-cart" class="w-5 h-5" />
                    {{ __('storefront.add_all_to_cart') }}
                </button>
            @endif
        </div>
    @endif
</div>