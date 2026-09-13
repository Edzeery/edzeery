{{-- Products index toolbar — always-visible search + a single "Filters" trigger that opens
    a two-level drill-down portal (root groups → options), mirroring the tracking filter-portal
    / orders dropdown idiom (dropdownPosition + x-show sections). --}}

<div class="flex flex-wrap items-center gap-3 border-b border-surface-border p-4">
    {{-- Search (always visible) --}}
    <div class="relative w-full sm:w-auto sm:flex-1 sm:max-w-[280px]">
        <input type="search" wire:model.live.debounce.300ms="search"
            placeholder="{{ __('products.search_placeholder') }}"
            class="edz-input text-sm ps-9 pe-9">
        <x-edz.icon name="search"
            class="absolute start-3 top-1/2 -translate-y-1/2 w-4 h-4 text-ink-muted pointer-events-none" />
        @if ($this->search !== '')
            <button wire:click="$set('search', '')" type="button"
                class="absolute end-2 top-1/2 -translate-y-1/2 text-ink-muted hover:text-accent-500 transition"
                aria-label="Clear search">
                <x-edz.icon name="x-mark" class="w-4 h-4" />
            </button>
        @endif
    </div>

    {{-- Filters trigger --}}
    <button type="button" data-filter-btn
        @click.stop="$dispatch('edz-filter-open', { key: 'root', el: $event.currentTarget })"
        class="edz-btn edz-btn--ghost edz-btn--sm {{ $this->activeFilterCount() > 0 ? 'text-accent-600' : '' }}">
        <x-edz.icon name="funnel" class="w-4 h-4 {{ $this->activeFilterCount() > 0 ? 'text-accent-600' : '' }}" />
        <span>{{ __('merchant_panel.filters') }}</span>
        @if ($this->activeFilterCount() > 0)
            <span
                class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-[10px] font-semibold bg-accent-600 text-white leading-none">
                {{ $this->activeFilterCount() }}
            </span>
        @endif
        <x-edz.icon name="chevron-down" class="w-3 h-3" />
    </button>
</div>

{{-- Drill-down filter portal (mirrors tracking/partials/tracking-filter-portal.blade.php) --}}
@php
    $forwardChevron = app()->getLocale() === 'ar' ? 'chevron-left' : 'chevron-right';
    $backChevron = app()->getLocale() === 'ar' ? 'chevron-right' : 'chevron-left';
@endphp

<div x-data="dropdownPosition()" x-show="open" @click.away="close()"
    @edz-filter-open.window="$event.detail && toggle($event, $event.detail)">
    {{-- Mobile scrim --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-[205] bg-black/40 backdrop-blur-sm sm:hidden"
        @click="close()"></div>

    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-3"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-3"
        :style="menuStyle"
        class="fixed inset-x-0 bottom-0 z-[210] w-full rounded-t-2xl border border-b-0 border-surface-border bg-surface
               p-3 pb-[calc(1rem+env(safe-area-inset-bottom))] shadow-[0_-16px_48px_-12px_rgba(15,23,42,.25)]
               max-h-[75vh] overflow-y-auto edz-scroll
               sm:inset-x-auto sm:bottom-auto sm:z-50 sm:w-64 sm:rounded-xl sm:border-b sm:p-2 sm:shadow-lg">
        <span class="pointer-events-none mx-auto mb-2 block h-1 w-10 rounded-full bg-surface-border sm:hidden"></span>
        <div class="flex items-center justify-between gap-2 px-1 mb-1.5 sm:hidden">
            <p class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink uppercase tracking-wide">
                <x-edz.icon name="funnel" class="w-3.5 h-3.5 text-ink-muted" />
                <span>{{ __('merchant_panel.filters') }}</span>
            </p>
            <button @click="close()" type="button"
                class="-m-1 p-1 rounded-lg text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                title="{{ __('general.close') }}">
                <x-edz.icon name="x-mark" class="w-4 h-4" />
            </button>
        </div>

        {{-- ROOT — choose a filter group --}}
        <div x-show="open === 'root'" x-cloak class="edz-dropdown__section">
            <p class="edz-dropdown__section-title">{{ __('merchant_panel.filters') }}</p>
            <button type="button" @click="open = 'brand'" class="edz-dropdown__item justify-between">
                <span class="truncate">{{ __('products.brand') }}</span>
                <x-edz.icon :name="$forwardChevron" class="w-3.5 h-3.5 shrink-0 text-ink-muted" />
            </button>
            <button type="button" @click="open = 'category'" class="edz-dropdown__item justify-between">
                <span class="truncate">{{ __('products.category') }}</span>
                <x-edz.icon :name="$forwardChevron" class="w-3.5 h-3.5 shrink-0 text-ink-muted" />
            </button>
            <button type="button" @click="open = 'status'" class="edz-dropdown__item justify-between">
                <span class="truncate">{{ __('products.status_label') }}</span>
                <x-edz.icon :name="$forwardChevron" class="w-3.5 h-3.5 shrink-0 text-ink-muted" />
            </button>
            <button type="button" @click="open = 'featured'" class="edz-dropdown__item justify-between">
                <span class="truncate">{{ __('products.featured') }}</span>
                <x-edz.icon :name="$forwardChevron" class="w-3.5 h-3.5 shrink-0 text-ink-muted" />
            </button>
            <button type="button" @click="open = 'period'" class="edz-dropdown__item justify-between">
                <span class="truncate">{{ __('table.created') }}</span>
                <x-edz.icon :name="$forwardChevron" class="w-3.5 h-3.5 shrink-0 text-ink-muted" />
            </button>
            @if ($this->activeFilterCount() > 0)
                <button type="button" wire:click="clearFilters" @click="close()"
                    class="edz-dropdown__item justify-between text-danger-600">
                    <span class="truncate">{{ __('merchant_panel.clear_filters') }}</span>
                    <x-edz.icon name="trash" class="w-3.5 h-3.5 shrink-0" />
                </button>
            @endif
        </div>

        {{-- BRAND --}}
        <div x-show="open === 'brand'" x-cloak class="edz-dropdown__section">
            <div class="mb-1 flex items-center gap-0.5 px-0.5">
                <button type="button" @click="open = 'root'" class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                    title="{{ __('products.back') }}" aria-label="{{ __('products.back') }}">
                    <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                </button>
                <p class="edz-dropdown__section-title m-0">{{ __('products.brand') }}</p>
            </div>
            <button type="button" @click="$wire.setFilter('brand_id', ''); close()"
                aria-pressed="{{ $this->brand_id === '' ? 'true' : 'false' }}"
                class="edz-dropdown__item justify-between {{ $this->brand_id === '' ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                <span class="truncate">{{ __('products.all_brands') }}</span>
                <x-edz.icon name="check"
                    class="w-3.5 h-3.5 shrink-0 {{ $this->brand_id === '' ? 'opacity-100' : 'opacity-0' }}" />
            </button>
            @foreach ($this->brands as $id => $name)
                <button type="button" @click="$wire.setFilter('brand_id', '{{ $id }}'); close()"
                    aria-pressed="{{ $this->brand_id === $id ? 'true' : 'false' }}"
                    class="edz-dropdown__item justify-between {{ $this->brand_id === $id ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                    <span class="truncate">{{ $name }}</span>
                    <x-edz.icon name="check"
                        class="w-3.5 h-3.5 shrink-0 {{ $this->brand_id === $id ? 'opacity-100' : 'opacity-0' }}" />
                </button>
            @endforeach
        </div>

        {{-- CATEGORY --}}
        <div x-show="open === 'category'" x-cloak class="edz-dropdown__section">
            <div class="mb-1 flex items-center gap-0.5 px-0.5">
                <button type="button" @click="open = 'root'" class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                    title="{{ __('products.back') }}" aria-label="{{ __('products.back') }}">
                    <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                </button>
                <p class="edz-dropdown__section-title m-0">{{ __('products.category') }}</p>
            </div>
            <button type="button" @click="$wire.setFilter('category_id', ''); close()"
                aria-pressed="{{ $this->category_id === '' ? 'true' : 'false' }}"
                class="edz-dropdown__item justify-between {{ $this->category_id === '' ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                <span class="truncate">{{ __('products.all_categories') }}</span>
                <x-edz.icon name="check"
                    class="w-3.5 h-3.5 shrink-0 {{ $this->category_id === '' ? 'opacity-100' : 'opacity-0' }}" />
            </button>
            @foreach ($this->categories as $category)
                <button type="button" @click="$wire.setFilter('category_id', '{{ $category->id }}'); close()"
                    aria-pressed="{{ $this->category_id === $category->id ? 'true' : 'false' }}"
                    class="edz-dropdown__item justify-between {{ $this->category_id === $category->id ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                    <span class="truncate">{{ $category->full_name }}</span>
                    <x-edz.icon name="check"
                        class="w-3.5 h-3.5 shrink-0 {{ $this->category_id === $category->id ? 'opacity-100' : 'opacity-0' }}" />
                </button>
            @endforeach
        </div>

        {{-- STATUS --}}
        <div x-show="open === 'status'" x-cloak class="edz-dropdown__section">
            <div class="mb-1 flex items-center gap-0.5 px-0.5">
                <button type="button" @click="open = 'root'" class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                    title="{{ __('products.back') }}" aria-label="{{ __('products.back') }}">
                    <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                </button>
                <p class="edz-dropdown__section-title m-0">{{ __('products.status_label') }}</p>
            </div>
            <button type="button" @click="$wire.setFilter('is_active', ''); close()"
                aria-pressed="{{ $this->is_active === '' ? 'true' : 'false' }}"
                class="edz-dropdown__item justify-between {{ $this->is_active === '' ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                <span class="truncate">{{ __('products.all_statuses') }}</span>
                <x-edz.icon name="check"
                    class="w-3.5 h-3.5 shrink-0 {{ $this->is_active === '' ? 'opacity-100' : 'opacity-0' }}" />
            </button>
            <button type="button" @click="$wire.setFilter('is_active', '1'); close()"
                aria-pressed="{{ $this->is_active === '1' ? 'true' : 'false' }}"
                class="edz-dropdown__item justify-between {{ $this->is_active === '1' ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                <span class="truncate">{{ __('products.active') }}</span>
                <x-edz.icon name="check"
                    class="w-3.5 h-3.5 shrink-0 {{ $this->is_active === '1' ? 'opacity-100' : 'opacity-0' }}" />
            </button>
            <button type="button" @click="$wire.setFilter('is_active', '0'); close()"
                aria-pressed="{{ $this->is_active === '0' ? 'true' : 'false' }}"
                class="edz-dropdown__item justify-between {{ $this->is_active === '0' ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                <span class="truncate">{{ __('products.inactive') }}</span>
                <x-edz.icon name="check"
                    class="w-3.5 h-3.5 shrink-0 {{ $this->is_active === '0' ? 'opacity-100' : 'opacity-0' }}" />
            </button>
        </div>

        {{-- FEATURED --}}
        <div x-show="open === 'featured'" x-cloak class="edz-dropdown__section">
            <div class="mb-1 flex items-center gap-0.5 px-0.5">
                <button type="button" @click="open = 'root'" class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                    title="{{ __('products.back') }}" aria-label="{{ __('products.back') }}">
                    <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                </button>
                <p class="edz-dropdown__section-title m-0">{{ __('products.featured') }}</p>
            </div>
            <button type="button" @click="$wire.setFilter('is_featured', ''); close()"
                aria-pressed="{{ $this->is_featured === '' ? 'true' : 'false' }}"
                class="edz-dropdown__item justify-between {{ $this->is_featured === '' ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                <span class="truncate">{{ __('products.all_featured') }}</span>
                <x-edz.icon name="check"
                    class="w-3.5 h-3.5 shrink-0 {{ $this->is_featured === '' ? 'opacity-100' : 'opacity-0' }}" />
            </button>
            <button type="button" @click="$wire.setFilter('is_featured', '1'); close()"
                aria-pressed="{{ $this->is_featured === '1' ? 'true' : 'false' }}"
                class="edz-dropdown__item justify-between {{ $this->is_featured === '1' ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                <span class="truncate">{{ __('products.featured') }}</span>
                <x-edz.icon name="check"
                    class="w-3.5 h-3.5 shrink-0 {{ $this->is_featured === '1' ? 'opacity-100' : 'opacity-0' }}" />
            </button>
            <button type="button" @click="$wire.setFilter('is_featured', '0'); close()"
                aria-pressed="{{ $this->is_featured === '0' ? 'true' : 'false' }}"
                class="edz-dropdown__item justify-between {{ $this->is_featured === '0' ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                <span class="truncate">{{ __('products.not_featured') }}</span>
                <x-edz.icon name="check"
                    class="w-3.5 h-3.5 shrink-0 {{ $this->is_featured === '0' ? 'opacity-100' : 'opacity-0' }}" />
            </button>
        </div>

        {{-- PERIOD (created from/to via flatpickr) --}}
        <div x-show="open === 'period'" x-cloak class="edz-dropdown__section">
            <div class="mb-1 flex items-center gap-0.5 px-0.5">
                <button type="button" @click="open = 'root'" class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                    title="{{ __('products.back') }}" aria-label="{{ __('products.back') }}">
                    <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                </button>
                <p class="edz-dropdown__section-title m-0">{{ __('table.created') }}</p>
            </div>
            <div class="px-1 flex items-center gap-2">
                <input type="text" wire:model.blur="created_from" class="edz-input text-sm flatpickr-input"
                    placeholder="{{ __('table.from') }} —" autocomplete="off">
                <span class="text-ink-muted text-sm">—</span>
                <input type="text" wire:model.blur="created_to" class="edz-input text-sm flatpickr-input"
                    placeholder="— {{ __('table.to') }}" autocomplete="off">
            </div>
        </div>
    </div>
</div>