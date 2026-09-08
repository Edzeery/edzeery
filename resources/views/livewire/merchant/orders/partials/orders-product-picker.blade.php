{{-- Shared order product picker (create / edit / items-edit all drive these
   two modals through HasOrderProductPicker). The list is a server-built pool
   (loadProducts → 100 rows, loadProductChunk → next 100) so filtering is
   instant client-side via data-search; the picker keeps writing rows into
   $this->form['items'] (the shared draft). Each modal owns its own
   orderProductPicker() Alpine scope so it works from any ancestor. --}}
<div x-data="orderProductPicker()">

    {{-- Product Picker Modal --}}
    @if ($showProductPickerModal)
        <x-edz.modal :isOpen="true" :showCloseButton="false" :preventClose="true" size="md">
            <div class="flex flex-col max-h-[85vh]">
                {{-- Drag handle (mobile) --}}
                <div class="edz-modal__handle sm:hidden"></div>

                {{-- Header --}}
                <div class="flex items-center justify-between px-5 pt-5 pb-3">
                    <h3 class="text-lg font-bold text-ink">{{ __('merchant_panel.products') }}</h3>
                    <button type="button" @click="open = false; closeProductPicker()" class="edz-modal__close"
                        style="position:static;">
                        <x-edz.icon name="x-mark" class="w-5 h-5" />
                    </button>
                </div>

                {{-- Search --}}
                <div class="px-5 pb-3">
                    <div class="relative">
                        <input type="text" @input="onSearchInput($event)"
                            @keydown.enter.prevent="selectProductByBarcode($event)" data-product-search-input
                            placeholder="{{ __('merchant_panel.search_products_barcode') }}"
                            class="edz-input text-sm ps-10 pe-10">
                        <x-edz.icon name="magnifying-glass"
                            class="w-4 h-4 absolute start-3 top-1/2 -translate-y-1/2 text-ink-muted pointer-events-none" />
                        <x-edz.icon name="qr-code"
                            class="w-4 h-4 absolute end-3 top-1/2 -translate-y-1/2 text-ink-muted pointer-events-none" />
                    </div>
                </div>

                {{-- Counter --}}
                <div class="px-5 pb-2">
                    <span class="text-xs text-ink-muted"
                        x-text="(searchTerm && searchTerm.length >= 2 ? visibleCount : {{ count($formProductResults) }}) + ' {{ __('merchant_panel.products') }}'"></span>
                </div>

                {{-- Product list --}}
                <div class="min-h-0 flex-1 max-h-[calc(100vh-475px)] overflow-y-auto edz-scroll px-5 pb-5">
                    {{-- Skeleton loading (while loadProducts/loadProductChunk is executing) --}}
                    <div wire:loading wire:target="loadProducts, loadProductChunk" class="space-y-3 py-2">
                        @foreach (range(1, 5) as $i)
                            <div class="flex items-center gap-3 py-2">
                                <div class="w-11 h-11 rounded-xl edz-skeleton shrink-0"></div>
                                <div class="flex-1 space-y-2">
                                    <x-edz.skeleton width="{{ 40 + $i * 10 }}%" height="0.875rem" />
                                    <x-edz.skeleton width="6rem" height="0.75rem" />
                                </div>
                                <x-edz.skeleton width="3.5rem" height="1rem" />
                            </div>
                        @endforeach
                    </div>

                    {{-- Product items (server-rendered pool) --}}
                    <div wire:loading.remove wire:target="loadProducts, loadProductChunk"
                        class="divide-y divide-surface-border">
                        @forelse ($formProductResults as $pv)
                            @php
                                $searchText = mb_strtolower(
                                    $pv['product_name'] . ' ' . ($pv['first_variant']['sku'] ?? ''),
                                );
                            @endphp
                            <div data-search="{{ $searchText }}"
                                x-show="!searchTerm || searchTerm.length < 2 || $el.dataset.search.includes(searchTerm.toLowerCase())"
                                class="transition-opacity">

                                {{-- Multi-variant product --}}
                                @if ($pv['has_variants'] && ($pv['variant_count'] ?? 0) > 1)
                                    <button type="button" @click="openVariants('{{ $pv['product_id'] }}')"
                                        :disabled="isLoadingVariants"
                                        class="w-full text-left py-3 hover:bg-surface-secondary flex items-center gap-3 text-sm transition-colors rounded-lg px-2 -mx-2 disabled:opacity-50">
                                        <img src="{{ $pv['image_url'] ?? asset('img/icons/noimg.png') }}"
                                            alt="" loading="lazy"
                                            class="w-11 h-11 rounded-xl object-cover bg-surface-secondary shrink-0">
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium text-ink truncate">{{ $pv['product_name'] }}
                                            </div>
                                            <div class="text-xs text-ink-muted mt-0.5">
                                                {{ $pv['variant_count'] }} {{ __('merchant_panel.variants') }}
                                            </div>
                                        </div>
                                        <span
                                            class="text-xs text-ink-muted shrink-0 tabular-nums">{{ $pv['price_range'] }}</span>
                                        <x-edz.spinner :show="'isLoadingVariants'" class="w-4 h-4 text-ink-muted shrink-0" />
                                        <x-edz.icon name="chevron-left" x-show="!isLoadingVariants"
                                            class="w-4 h-4 text-ink-muted shrink-0 rtl:rotate-180" />
                                    </button>

                                    {{-- Single variant product --}}
                                @elseif ($pv['first_variant'])
                                    @php
                                        $isProductSelected = isset($formSelectedItems[$pv['first_variant']['id']]);
                                    @endphp
                                    <button type="button"
                                        @if (!$isProductSelected) @click="selectProduct('{{ $pv['first_variant']['id'] }}')" @endif
                                        :disabled="isAddingProduct"
                                        class="w-full text-left py-3 flex items-center gap-3 text-sm transition-colors rounded-lg px-2 -mx-2
                                    {{ $isProductSelected
                                        ? 'bg-success-surface-subtle border border-success-border'
                                        : 'hover:bg-surface-secondary' }}
                                    disabled:opacity-50">
                                        <img src="{{ $pv['image_url'] ?? asset('img/icons/noimg.png') }}"
                                            alt="" loading="lazy"
                                            class="w-11 h-11 rounded-xl object-cover bg-surface-secondary shrink-0">
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium text-ink truncate">{{ $pv['product_name'] }}
                                            </div>
                                            <div class="text-xs text-ink-muted mt-0.5 flex items-center gap-1.5">
                                                <span>SKU: {{ $pv['first_variant']['sku'] ?? '—' }}</span>
                                                @if (($pv['first_variant']['stock_status'] ?? '') === 'out')
                                                    <span
                                                        class="text-danger-500 font-medium">{{ __('merchant_panel.out_of_stock') }}</span>
                                                @elseif (($pv['first_variant']['stock_status'] ?? '') === 'low')
                                                    <span
                                                        class="text-warning-500 font-medium">{{ $pv['first_variant']['stock_text'] }}</span>
                                                @else
                                                    <span
                                                        class="text-success-500 font-medium">{{ $pv['first_variant']['stock_text'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <span
                                            class="text-ink font-semibold shrink-0 tabular-nums">{{ $pv['first_variant']['price_formatted'] }}</span>
                                        @if ($isProductSelected)
                                            <span
                                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-success-surface text-success-fg shrink-0">
                                                <x-edz.icon name="check" class="w-4 h-4" />
                                            </span>
                                        @else
                                            <span
                                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-brand-surface text-brand-fg shrink-0">
                                                <svg x-show="!isAddingProduct" class="w-4 h-4" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M12 4.5v15m7.5-7.5h-15" />
                                                </svg>
                                                <x-edz.spinner :show="'isAddingProduct'" />
                                            </span>
                                        @endif
                                    </button>
                                @endif
                            </div>
                        @empty
                            <div class="px-4 py-10 text-center">
                                <x-edz.icon name="magnifying-glass"
                                    class="w-10 h-10 text-ink-muted/40 mx-auto mb-3" />
                                <p class="text-sm text-ink-muted">{{ __('merchant_panel.no_products_found') }}
                                </p>
                            </div>
                        @endforelse

                        {{-- No search results (all items hidden by x-show) --}}
                        <div x-show="searchTerm && searchTerm.length >= 2 && visibleCount === 0"
                            class="px-4 py-10 text-center">
                            <x-edz.icon name="magnifying-glass" class="w-10 h-10 text-ink-muted/40 mx-auto mb-3" />
                            <p class="text-sm text-ink-muted">{{ __('merchant_panel.no_products_found') }}</p>
                        </div>
                    </div>

                    {{-- Load more (fetch next chunk of the pool when the current one is exhausted) --}}
                    <div x-show="$wire.productHasMore"
                        class="py-4 text-center">
                        <button type="button" @click="loadMore()" :disabled="$wire.productChunkLoading"
                            class="edz-btn edz-btn--ghost edz-btn--sm disabled:opacity-50">
                            <x-edz.spinner :show="'$wire.productChunkLoading'" class="w-4 h-4" />
                            <span>{{ __('merchant_panel.load_more') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </x-edz.modal>
    @endif

    {{-- Variant Picker Modal --}}
    @if ($showVariantPickerModal)
        <x-edz.modal :isOpen="true" :showCloseButton="false" :preventClose="true" size="sm">
            <div class="flex flex-col max-h-[85vh]">
                {{-- Drag handle (mobile) --}}
                <div class="edz-modal__handle sm:hidden"></div>

                {{-- Header --}}
                <div class="flex items-center justify-between px-5 pt-5 pb-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <button type="button"
                            @click="open = false; closeVariantPicker(); $wire.set('showProductPickerModal', true);"
                            class="edz-btn edz-btn--ghost edz-btn--sm shrink-0">
                            <x-edz.icon name="arrow-right" class="w-4 h-4 rtl:rotate-180" />
                            <span class="hidden sm:inline">{{ __('buttons.back') }}</span>
                        </button>
                        @if ($formSelectedProduct)
                            <div class="flex items-center gap-2 min-w-0">
                                <img src="{{ $formSelectedProduct['image_url'] ?? asset('img/icons/noimg.png') }}"
                                    alt="" class="w-8 h-8 rounded-lg object-cover bg-surface shrink-0">
                                <span
                                    class="text-sm font-bold text-ink truncate">{{ $formSelectedProduct['name'] }}</span>
                            </div>
                        @endif
                    </div>
                    <button type="button" @click="open = false; closeVariantPicker()" class="edz-modal__close"
                        style="position:static;">
                        <x-edz.icon name="x-mark" class="w-5 h-5" />
                    </button>
                </div>

                {{-- Variant search --}}
                <div class="px-5 pb-3">
                    <div class="relative">
                        <input type="text" @input="onVariantSearchInput($event)" data-variant-search-input
                            placeholder="{{ __('merchant_panel.search_variants') }}"
                            class="edz-input text-sm ps-10 pe-10">
                        <x-edz.icon name="magnifying-glass"
                            class="w-4 h-4 absolute start-3 top-1/2 -translate-y-1/2 text-ink-muted pointer-events-none" />
                    </div>
                </div>

                {{-- Variant list --}}
                <div class="flex-1 overflow-y-auto px-5 pb-5 max-h-[calc(100vh-475px)] edz-scroll">
                    @if ($formSelectedProduct && count($formSelectedProduct['variants']) > 0)
                        <div class="divide-y divide-surface-border">
                            @foreach ($formSelectedProduct['variants'] as $variant)
                                @php
                                    $isVariantSelected = isset($formSelectedItems[$variant['id']]);
                                    $variantQty = $formSelectedItems[$variant['id']] ?? 0;
                                    $isDisabled = !$variant['is_active'] || $variant['stock'] <= 0;
                                    $variantSearchText = mb_strtolower(
                                        $variant['name'] .
                                            ' ' .
                                            ($variant['sku'] ?? '') .
                                            ' ' .
                                            ($variant['option_labels'] ?? ''),
                                    );
                                @endphp
                                <div data-variant-search="{{ $variantSearchText }}"
                                    x-show="!variantQuery || variantQuery.length < 2 || $el.dataset.variantSearch.includes(variantQuery.toLowerCase())"
                                    class="py-3 flex items-center gap-3 text-sm rounded-lg px-2 -mx-2 transition-colors
                                {{ $isVariantSelected ? 'bg-success-surface-subtle' : '' }}
                                {{ $isDisabled && !$isVariantSelected ? 'opacity-40' : '' }}">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-ink text-xs truncate">{{ $variant['name'] }}
                                        </div>
                                        <div
                                            class="text-[11px] text-ink-muted mt-0.5 flex items-center gap-1.5 flex-wrap">
                                            @if ($variant['option_labels'])
                                                <span>{{ $variant['option_labels'] }}</span>
                                                <span class="text-surface-border">·</span>
                                            @endif
                                            <span>SKU: {{ $variant['sku'] ?? '—' }}</span>
                                            @if ($isVariantSelected)
                                                <span class="text-success-fg font-medium">·
                                                    {{ $variantQty }}
                                                    {{ __('merchant_panel.in_cart') }}</span>
                                            @elseif ($variant['stock'] <= 0)
                                                <span
                                                    class="text-danger-500 font-medium">{{ __('merchant_panel.out_of_stock') }}</span>
                                            @elseif ($variant['stock'] <= 5)
                                                <span class="text-warning-500 font-medium">{{ $variant['stock'] }}
                                                    {{ __('merchant_panel.left') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <span
                                        class="text-ink font-semibold text-xs shrink-0 tabular-nums">{{ currency($variant['price']) }}</span>
                                    @if ($isVariantSelected)
                                        <span
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-success-surface
                                        text-success-fg shrink-0">
                                            <x-edz.icon name="check" class="w-4 h-4" />
                                        </span>
                                    @elseif ($variant['is_active'] && $variant['stock'] > 0)
                                        <button type="button" @click="selectVariant('{{ $variant['id'] }}')"
                                            :disabled="isAddingProduct"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-brand-surface
                                            text-brand-fg hover:bg-brand-surface
                                            transition-colors shrink-0 disabled:opacity-50">
                                            <svg x-show="!isAddingProduct" class="w-4 h-4" fill="none"
                                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>
                                            <x-edz.spinner :show="'isAddingProduct'" />
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        {{-- No variant search results --}}
                        <div x-show="variantQuery && variantQuery.length >= 2 && variantVisibleCount === 0"
                            class="px-4 py-10 text-center">
                            <x-edz.icon name="magnifying-glass" class="w-10 h-10 text-ink-muted/40 mx-auto mb-3" />
                            <p class="text-sm text-ink-muted">{{ __('merchant_panel.no_products_found') }}</p>
                        </div>
                    @else
                        <div class="px-4 py-10 text-center">
                            <x-edz.icon name="cube" class="w-10 h-10 text-ink-muted/40 mx-auto mb-3" />
                            <p class="text-sm text-ink-muted">{{ __('merchant_panel.no_products_found') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </x-edz.modal>
    @endif
</div>