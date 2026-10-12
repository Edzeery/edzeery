{{-- M4 product filter modal (confirmation view only). A server-built pool of
   active products (DashboardProductFilterConcern) with instant client-side
   search via data-product-filter-search; toggling a row appends/removes its
   product_id in $productIds, which the confirmation stats query resolves as an
   EXISTS clause. Product-level selection covers every variant of a product
   (D10's first half); a variant-level pick is a later refinement. --}}
<div x-data="{
    searchTerm: '',
    visibleCount: 0,
    onSearchInput(event) {
        this.searchTerm = event.target.value;
        this.recount();
    },
    recount() {
        this.$nextTick(() => {
            let count = 0;
            document.querySelectorAll('[data-product-filter-search]').forEach(el => {
                if (el.style.display !== 'none') count++;
            });
            this.visibleCount = count;
        });
    },
    loadMore() {
        if ($wire.filterProductChunkLoading || !$wire.filterProductHasMore) return;
        $wire.loadFilterProductChunk().then(() => this.recount());
    }
}">
    @if ($showProductFilterModal)
        <x-edz.modal :isOpen="true" :showCloseButton="false" :preventClose="true" size="md">
            <div class="flex flex-col max-h-[85vh]">
                <div class="edz-modal__handle sm:hidden"></div>

                {{-- Header --}}
                <div class="flex items-center justify-between px-5 pt-5 pb-3">
                    <h3 class="text-lg font-bold text-ink">{{ __('dashboard.product_filter_title') }}</h3>
                    <button type="button" wire:click="closeProductFilter" class="edz-modal__close" style="position:static;">
                        <x-edz.icon name="x-mark" class="w-5 h-5" />
                    </button>
                </div>

                {{-- Search --}}
                <div class="px-5 pb-3">
                    <div class="relative">
                        <input type="text" @input="onSearchInput($event)" data-product-filter-search-input
                            placeholder="{{ __('dashboard.search_products') }}"
                            class="edz-input text-sm ps-10">
                        <x-edz.icon name="magnifying-glass"
                            class="w-4 h-4 absolute start-3 top-1/2 -translate-y-1/2 text-ink-muted pointer-events-none" />
                    </div>
                </div>

                {{-- Counter --}}
                <div class="px-5 pb-2">
                    <span class="text-xs text-ink-muted"
                        data-total-count="{{ count($filterProductResults) }}"
                        data-selected-label="{{ __('dashboard.products_selected') }}"
                        x-text="(searchTerm && searchTerm.length >= 2 ? visibleCount : Number($el.dataset.totalCount)) + ' ' + $el.dataset.selectedLabel"></span>
                </div>

                {{-- Selected chip strip --}}
                @if (count($productIds) > 0)
                    <div class="px-5 pb-2">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-[11px] font-medium text-ink-muted">{{ __('dashboard.products_selected') }} ({{ count($productIds) }})</span>
                            <button type="button" wire:click="clearProductFilter"
                                class="text-[11px] font-medium text-danger-600 hover:text-danger-500 transition-colors">
                                {{ __('dashboard.clear_products') }}
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Product list --}}
                <div class="min-h-0 flex-1 max-h-[calc(100vh-475px)] overflow-y-auto edz-scroll px-5 pb-5">
                    <div wire:loading wire:target="loadFilterProducts, loadFilterProductChunk" class="space-y-3 py-2">
                        @foreach (range(1, 4) as $i)
                            <div class="flex items-center gap-3 py-2">
                                <div class="w-11 h-11 rounded-xl edz-skeleton shrink-0"></div>
                                <div class="flex-1 space-y-2">
                                    <x-edz.skeleton width="{{ 40 + $i * 10 }}%" height="0.875rem" />
                                    <x-edz.skeleton width="6rem" height="0.75rem" />
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div wire:loading.remove wire:target="loadFilterProducts, loadFilterProductChunk"
                        class="divide-y divide-surface-border">
                        @forelse ($filterProductResults as $pv)
                            @php
                                $productId = (string) $pv['product_id'];
                                $selected = in_array($productId, array_map('strval', $productIds), true);
                                $searchText = mb_strtolower($pv['product_name'] . ' ' . ($pv['first_variant']['sku'] ?? ''));
                            @endphp
                            <div data-product-filter-search="{{ $searchText }}" wire:key="pf-{{ $productId }}"
                                x-show="!searchTerm || searchTerm.length < 2 || $el.dataset.productFilterSearch.includes(searchTerm.toLowerCase())">
                                <button type="button" wire:click="toggleFilterProduct('{{ $productId }}')"
                                    @class([
                                        'w-full text-left py-3 flex items-center gap-3 text-sm transition-colors rounded-lg px-2 -mx-2',
                                        'bg-success-surface-subtle border border-success-border' => $selected,
                                        'hover:bg-surface-secondary' => ! $selected,
                                    ])>
                                    <img src="{{ $pv['image_url'] ?? asset('img/icons/noimg.png') }}" alt=""
                                        loading="lazy"
                                        class="w-11 h-11 rounded-xl object-cover bg-surface-secondary shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-ink truncate">{{ $pv['product_name'] }}</div>
                                        <div class="text-xs mt-0.5 flex items-center gap-1.5">
                                            @if ($pv['has_variants'] && ($pv['variant_count'] ?? 0) > 1)
                                                <span class="text-ink-muted">
                                                    {{ $pv['variant_count'] }} @js(__('merchant_panel.variants')) ·
                                                    {{ __('dashboard.product_includes_variants') }}
                                                </span>
                                            @elseif ($pv['first_variant'])
                                                <span class="text-ink-muted">SKU: {{ $pv['first_variant']['sku'] ?? '—' }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <span @class([
                                        'w-8 h-8 flex items-center justify-center rounded-lg shrink-0',
                                        'bg-success-surface text-success-fg' => $selected,
                                        'bg-surface-secondary text-ink-muted' => ! $selected,
                                    ])>
                                        @if ($selected)
                                            <x-edz.icon name="check" class="w-4 h-4" />
                                        @else
                                            <span class="w-4 h-4 rounded border border-surface-border bg-surface"></span>
                                        @endif
                                    </span>
                                </button>
                            </div>
                        @empty
                            <div class="px-4 py-10 text-center">
                                <x-edz.icon name="cube" class="w-10 h-10 text-ink-muted/40 mx-auto mb-3" />
                                <p class="text-sm text-ink-muted">{{ __('dashboard.no_products_found') }}</p>
                            </div>
                        @endforelse

                        <div x-show="searchTerm && searchTerm.length >= 2 && visibleCount === 0"
                            class="px-4 py-10 text-center">
                            <x-edz.icon name="magnifying-glass" class="w-10 h-10 text-ink-muted/40 mx-auto mb-3" />
                            <p class="text-sm text-ink-muted">{{ __('dashboard.no_products_found') }}</p>
                        </div>
                    </div>

                    <div x-show="$wire.filterProductHasMore" class="py-4 text-center">
                        <button type="button" @click="loadMore()" :disabled="$wire.filterProductChunkLoading"
                            class="edz-btn edz-btn--ghost edz-btn--sm disabled:opacity-50">
                            <x-edz.spinner :show="'$wire.filterProductChunkLoading'" class="w-4 h-4" />
                            <span>{{ __('dashboard.load_more') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </x-edz.modal>
    @endif
</div>