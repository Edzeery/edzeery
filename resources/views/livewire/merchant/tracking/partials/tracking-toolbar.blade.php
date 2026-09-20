{{-- Toolbar (Phase B) — unified search + single "Filters" drill-down trigger + active-filter chips,
    mirroring the products filter-bar. The drill-down portal lists every filter group; visible
    columns additionally expose the same filters via their header icons. --}}
<div class="edz-card edz-card--padded mb-4">
    <div class="flex flex-wrap items-center gap-3">
        {{-- Unified search --}}
        <div class="relative flex-1 max-w-[280px] min-w-[150px]">
            <input type="text" wire:model.live.debounce.600ms="search" @keydown.enter="$wire.loadShipments()"
                placeholder="{{ __('order_flow.search_tracking_placeholder') }}"
                class="edz-input text-sm ps-9 pe-9">
            <x-edz.icon name="search"
                class="absolute start-3 top-1/2 -translate-y-1/2 w-4 h-4 text-ink-muted pointer-events-none" />
            @if ($this->search !== '')
                <button wire:click="$set('search', '')" type="button"
                    class="absolute end-2 top-1/2 -translate-y-1/2 text-ink-muted hover:text-accent-500 transition me-7"
                    aria-label="Clear search">
                    <x-edz.icon name="x-mark" class="w-4 h-4" />
                </button>
            @endif
            <button wire:click="loadShipments" type="button"
                class="absolute end-2 top-1/2 -translate-y-1/2 text-ink-muted hover:text-accent-500 transition"
                wire:loading.attr="disabled" wire:target="loadShipments">
                <x-edz.icon name="arrow-right" class="w-4 h-4" />
            </button>
        </div>

        @if (! $this->showTrash)
            {{-- Carrier-validate scanner — sits beside the search field and only
                 renders when the current (filtered) view still holds shipments
                 that need validation; it is NOT tied to row selection. --}}
            @if ($this->bulkValidateNeedsCount > 0
                && canStore(\App\Enums\Store\StorePermissionEnum::ORDER_DISPATCH_VALIDATE->value))
                <x-edz.tooltip label="{{ __('order_flow.bulk_validate_btn') }}">
                    <button wire:click="openBulkValidateModal" type="button"
                        class="edz-btn edz-btn--ghost edz-btn--sm text-accent-600 inline-flex items-center gap-1.5">
                        <x-edz.icon name="shield-check" class="w-4 h-4" />
                        <span class="hidden lg:inline">{{ __('order_flow.bulk_validate_btn') }}</span>
                    </button>
                </x-edz.tooltip>
            @endif

            {{-- Opt the selection actions into the global branded loader so every
                 checkbox / select-all / clear round-trip shows "being processed"
                 (the loader's 150 ms flicker guard keeps fast toggles quiet). --}}
            <x-edz.loading-target action="toggleSelectOrder" :label="__('merchant.bulk_processing')" />
            <x-edz.loading-target action="toggleSelectAll" :label="__('merchant.bulk_processing')" />
            <x-edz.loading-target action="clearSelection" :label="__('merchant.bulk_processing')" />

            @if (count($this->selectedShipments) > 0)
                {{-- Bulk tasks — multi-select reassign / validate-at-carrier / soft delete --}}
                @include('livewire.merchant.tracking.partials.tracking-bulk-actions-bar')
            @endif

            {{-- Bulk status sync — refreshes every open adapter-backed tracking row. --}}
            <x-edz.tooltip label="{{ __('order_flow.sync_all_statuses') }}">
                <button wire:click="syncAllTracking" type="button" wire:loading.attr="disabled"
                    wire:loading.class="opacity-60 pointer-events-none"
                    class="edz-btn edz-btn--ghost edz-btn--sm">
                    <x-edz.icon name="arrow-path" wire:loading.remove wire:target="syncAllTracking" class="w-4 h-4" />
                    <x-edz.spinner wire:target="syncAllTracking" class="w-4 h-4" />
                    <span class="hidden lg:inline">{{ __('order_flow.sync_all_statuses') }}</span>
                </button>
            </x-edz.tooltip>

            {{-- Date range — a first-class toolbar trigger with its own portal --}}
            <x-edz.tooltip label="{{ __('order_flow.filter_date') }}">
                <button type="button" data-date-filter-btn
                    @click.stop="$dispatch('edz-date-filter-open', { el: $event.currentTarget })"
                    class="edz-btn edz-btn--ghost edz-btn--sm {{ filled($this->filters['date_from'] ?? null) || filled($this->filters['date_to'] ?? null) ? 'text-accent-600' : '' }}">
                    <x-edz.icon name="calendar"
                        class="w-4 h-4 {{ filled($this->filters['date_from'] ?? null) || filled($this->filters['date_to'] ?? null) ? 'text-accent-600' : '' }}" />
                    <span class="hidden lg:inline">{{ __('order_flow.filter_date') }}</span>
                    @if (filled($this->filters['date_from'] ?? null) || filled($this->filters['date_to'] ?? null))
                        <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-[10px] font-semibold bg-accent-600 text-white leading-none">
                            {{ filled($this->filters['date_from'] ?? null) && filled($this->filters['date_to'] ?? null) ? '2' : '1' }}
                        </span>
                    @endif
                </button>
            </x-edz.tooltip>

            {{-- Filters — one trigger; the drill-down portal lists every filter
                group regardless of column visibility (visible columns additionally
                keep their header filter icons). --}}
            <button type="button" data-filter-btn
                @click.stop="$dispatch('edz-toolbar-filter-open', { key: 'root', el: $event.currentTarget })"
                class="edz-btn edz-btn--ghost edz-btn--sm {{ $this->activeFilterCount() > 0 ? 'text-accent-600' : '' }}">
                <x-edz.icon name="funnel"
                    class="w-4 h-4 {{ $this->activeFilterCount() > 0 ? 'text-accent-600' : '' }}" />
                <span>{{ __('merchant_panel.filters') }}</span>
                @if ($this->activeFilterCount() > 0)
                    <span
                        class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-[10px] font-semibold bg-accent-600 text-white leading-none">
                        {{ $this->activeFilterCount() }}
                    </span>
                @endif
                <x-edz.icon name="chevron-down" class="w-3 h-3" />
            </button>

            {{-- Column settings (advanced grid) --}}
            <x-edz.tooltip label="{{ __('merchant_panel.table_settings') }}">
                <button wire:click="openTableSettings" type="button"
                    class="edz-btn edz-btn--ghost edz-btn--sm">
                    <x-edz.icon name="view-columns" class="w-4 h-4" />
                    <span class="hidden lg:inline">{{ __('merchant_panel.columns') }}</span>
                </button>
            </x-edz.tooltip>
        @endif
    </div>
</div>

{{-- Active-filter summary + Clear --}}
@php
    $hasActiveFilters = filled($this->filters['provider'] ?? null)
        || count($this->filters['tracking_statuses'] ?? []) > 0
        || filled($this->filters['date_from'] ?? null)
        || filled($this->filters['date_to'] ?? null)
        || filled($this->filters['state'] ?? null)
        || filled($this->filters['city'] ?? null)
        || filled($this->filters['assigned_to'] ?? null)
        || filled($this->filters['confirmed_by'] ?? null)
        || filled($this->filters['rider'] ?? null)
        || count($this->filters['products'] ?? []) > 0
        || filled($this->filters['amount_min'] ?? null)
        || filled($this->filters['amount_max'] ?? null);
@endphp
@if ($hasActiveFilters)
    <div class="mb-3 flex flex-wrap items-center gap-2">
        @if (filled($this->filters['state']))
            <span
                class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                <span class="font-semibold opacity-75">{{ __('merchant_panel.state') }}:</span>
                <span
                    class="max-w-[12rem] truncate">{{ collect($this->allStates)->firstWhere('id', $this->filters['state'])['name'] ?? $this->filters['state'] }}</span>
                <button wire:click="setFilter('state', null)" wire:loading.attr="disabled"
                    class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
            </span>
        @endif

        @if (filled($this->filters['city']))
            <span
                class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                <span class="font-semibold opacity-75">{{ __('merchant_panel.city') }}:</span>
                <span
                    class="max-w-[12rem] truncate">{{ collect($this->allCities)->firstWhere('id', $this->filters['city'])['name'] ?? $this->filters['city'] }}</span>
                <button wire:click="setFilter('city', null)" wire:loading.attr="disabled"
                    class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
            </span>
        @endif

        @if (filled($this->filters['provider']))
            <span
                class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                <span class="font-semibold opacity-75">{{ __('order_flow.tracking_provider') }}:</span>
                <span
                    class="max-w-[12rem] truncate">{{ collect($this->allProviders)->firstWhere('id', $this->filters['provider'])['name'] ?? $this->filters['provider'] }}</span>
                <button wire:click="setFilter('provider', null)" wire:loading.attr="disabled"
                    class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
            </span>
        @endif

        @if (count($this->filters['tracking_statuses'] ?? []) > 0)
            <span
                class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                <span class="font-semibold opacity-75">{{ __('order_flow.tracking_status') }}:</span>
                <span class="max-w-[16rem] truncate">{{ collect($this->trackingStatusOptions())->filter(fn ($opt) => in_array($opt['value'], $this->filters['tracking_statuses'] ?? [], true))->map(fn ($opt) => $opt['label'])->join(', ') }}</span>
                <button wire:click="setFilter('tracking_statuses', [])" wire:loading.attr="disabled"
                    class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
            </span>
        @endif

        @if (filled($this->filters['date_from']) || filled($this->filters['date_to']))
            <span
                class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                <span class="font-semibold opacity-75">{{ __('merchant_panel.date') }}:</span>
                <span>{{ $this->filters['date_from'] ?? '...' }} — {{ $this->filters['date_to'] ?? '...' }}</span>
                <button wire:click="clearDateFilter" wire:loading.attr="disabled"
                    class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
            </span>
        @endif

        @if (filled($this->filters['rider']))
            <span
                class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                <span class="font-semibold opacity-75">{{ __('order_flow.rider_tab_title') }}:</span>
                <span class="max-w-[12rem] truncate">{{ collect($this->searchableRiders)->firstWhere('id', $this->filters['rider'])['name'] ?? $this->filters['rider'] }}</span>
                <button wire:click="setFilter('rider', null)" wire:loading.attr="disabled"
                    class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
            </span>
        @endif

        @if (filled($this->filters['assigned_to']))
            <span
                class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                <span class="font-semibold opacity-75">{{ __('merchant_panel.assigned_agent') }}:</span>
                <span class="max-w-[12rem] truncate">{{ collect($this->allMembers)->firstWhere('id', $this->filters['assigned_to'])['name'] ?? $this->filters['assigned_to'] }}</span>
                <button wire:click="setFilter('assigned_to', null)" wire:loading.attr="disabled"
                    class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
            </span>
        @endif

        @if (filled($this->filters['confirmed_by']))
            <span
                class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                <span class="font-semibold opacity-75">{{ __('merchant_panel.confirmed_by') }}:</span>
                <span class="max-w-[12rem] truncate">{{ collect($this->allMembers)->firstWhere('id', $this->filters['confirmed_by'])['name'] ?? $this->filters['confirmed_by'] }}</span>
                <button wire:click="setFilter('confirmed_by', null)" wire:loading.attr="disabled"
                    class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
            </span>
        @endif

        @if (count($this->filters['products'] ?? []) > 0)
            <span
                class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                <span class="font-semibold opacity-75">{{ __('merchant_panel.products') }}:</span>
                <span class="max-w-[16rem] truncate">{{ collect($this->allProducts)->filter(fn ($p) => in_array($p['id'], $this->filters['products'] ?? []))->pluck('name')->join(', ') }}</span>
                <button wire:click="setFilter('products', [])" wire:loading.attr="disabled"
                    class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
            </span>
        @endif

        @if (filled($this->filters['amount_min'] ?? null) || filled($this->filters['amount_max'] ?? null))
            <span
                class="inline-flex items-center gap-1 pe-2 ps-2 py-0.5 rounded-full text-xs bg-accent-surface text-accent-fg">
                <span class="font-semibold opacity-75">{{ __('order_flow.filter_amount') }}:</span>
                <span>{{ $this->filters['amount_min'] ?? '...' }} — {{ $this->filters['amount_max'] ?? '...' }}</span>
                <button wire:click="$set('filters.amount_min', null); $set('filters.amount_max', null); $wire.loadShipments()" wire:loading.attr="disabled"
                    class="hover:text-accent-900"><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
            </span>
        @endif

        <button wire:click="clearFilters" class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 text-xs"
            wire:loading.attr="disabled" wire:loading.class="opacity-50 pointer-events-none">
            <x-edz.icon name="x-circle" class="w-3 h-3" />
            {{ __('merchant_panel.clear_filters') }}
        </button>
    </div>
@endif