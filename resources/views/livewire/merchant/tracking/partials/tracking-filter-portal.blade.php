{{-- Tracking grid header-column filter portal — one fixed-positioned container opened by
    $dispatch('edz-filter-open') from tracking-table-header (dropdownPosition pattern from orders). --}}

<div x-data="dropdownPosition()" x-show="open" @click.away="close()"
    @edz-filter-open.window="$event.detail && toggle($event, $event.detail)">
    <div x-show="open" x-cloak class="fixed inset-0 z-[205] bg-black/40 backdrop-blur-sm sm:hidden" @click="close()"></div>
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
               sm:inset-x-auto sm:bottom-auto sm:z-50 sm:w-auto sm:rounded-xl sm:border-b sm:p-2 sm:shadow-lg"
        :class="{
            'sm:max-h-[70vh]': open === 'city' || open === 'state',
            'sm:max-h-[350px]': open === 'status' || open === 'provider' || open === 'rider' || open === 'assigned' || open === 'confirmed' || open === 'products',
            'sm:w-52': open === 'status' || open === 'date' || open === 'products',
            'sm:w-48': open === 'provider' || open === 'rider' || open === 'city' || open === 'assigned' || open === 'confirmed' || open === 'state'
        }">
        <span class="pointer-events-none mx-auto mb-2 block h-1 w-10 rounded-full bg-surface-border sm:hidden"></span>
        <div class="flex items-center justify-between gap-2 px-1 mb-1.5 sm:hidden">
            <p class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink uppercase tracking-wide">
                <x-edz.icon name="filter" class="w-3.5 h-3.5 text-ink-muted" />
                <span>{{ __('buttons.filter') }}</span>
            </p>
            <button @click="close()" type="button"
                class="-m-1 p-1 rounded-lg text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                title="{{ __('general.close') }}">
                <x-edz.icon name="x-mark" class="w-4 h-4" />
            </button>
        </div>

        {{-- Status (multi-select) --}}
        @if (in_array('tracking_status', $this->visibleColumns))
            <div x-show="open === 'status'" x-cloak class="edz-dropdown__section">
                <p class="edz-dropdown__section-title">{{ __('order_flow.tracking_status') }}</p>
                <button @click="$wire.setFilter('tracking_statuses', []); close()"
                    class="edz-dropdown__item justify-between {{ empty($this->filters['tracking_statuses']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                    {{ __('general.all') }}
                    <x-edz.icon name="check" class="w-3.5 h-3.5 {{ empty($this->filters['tracking_statuses']) ? 'opacity-100' : 'opacity-0' }}" />
                </button>
                @foreach ($this->trackingStatusOptions() as $opt)
                    <button @click="$wire.toggleTrackingStatus('{{ $opt['value'] }}'); close()"
                        aria-pressed="{{ in_array($opt['value'], $this->filters['tracking_statuses'] ?? [], true) ? 'true' : 'false' }}"
                        class="edz-dropdown__item justify-between {{ in_array($opt['value'], $this->filters['tracking_statuses'] ?? [], true) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                        <span class="truncate">{{ $opt['label'] }}</span>
                        <x-edz.icon name="check" class="w-3.5 h-3.5 {{ in_array($opt['value'], $this->filters['tracking_statuses'] ?? [], true) ? 'opacity-100' : 'opacity-0' }}" />
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Products (multi-select, searchable) --}}
        @if (in_array('products', $this->visibleColumns) && ! empty($this->allProducts))
            <div x-show="open === 'products'" x-cloak class="edz-dropdown__section">
                <p class="edz-dropdown__section-title">{{ __('merchant_panel.products') }}</p>
                <button @click="$wire.setFilter('products', []); close()"
                    class="edz-dropdown__item justify-between {{ empty($this->filters['products']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                    {{ __('general.all') }}
                    <x-edz.icon name="check" class="w-3.5 h-3.5 {{ empty($this->filters['products']) ? 'opacity-100' : 'opacity-0' }}" />
                </button>
                <div x-data="edzSearchableList()" data-items='@json($this->allProducts)' data-active='@json($this->filters['products'] ?? [])'>
                    <input type="search" x-model="query" placeholder="{{ __('general.search') }}" class="edz-input text-sm mb-1" autocomplete="off">
                    <div class="max-h-[40vh] sm:max-h-[350px] overflow-y-auto edz-scroll">
                        <template x-for="item in filtered" :key="item.id">
                            <button @click="$wire.toggleProductFilter(item.id); close()"
                                :aria-pressed="isActive(item.id)"
                                class="edz-dropdown__item justify-between"
                                :class="activeCls(item.id)">
                                <span class="truncate" x-text="item.name"></span>
                                <x-edz.icon name="check" class="w-3.5 h-3.5" x-bind:class="checkCls(item.id)" />
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        @endif

        {{-- Provider (single, searchable) — carrier tab --}}
        @if (in_array('provider', $this->visibleColumns) && ! empty($this->allProviders))
            <div x-show="open === 'provider'" x-cloak class="edz-dropdown__section">
                <p class="edz-dropdown__section-title">{{ __('order_flow.tracking_provider') }}</p>
                <button @click="$wire.setFilter('provider', null); close()"
                    class="edz-dropdown__item justify-between {{ empty($this->filters['provider']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                    {{ __('general.all') }}
                    <x-edz.icon name="check" class="w-3.5 h-3.5 {{ empty($this->filters['provider']) ? 'opacity-100' : 'opacity-0' }}" />
                </button>
                <div x-data="edzSearchableList()" data-items='@json($this->allProviders)' data-active='@json(array_filter([$this->filters['provider'] ?? null]))'>
                    <input type="search" x-model="query" placeholder="{{ __('general.search') }}" class="edz-input text-sm mb-1" autocomplete="off">
                    <div class="max-h-[40vh] sm:max-h-[350px] overflow-y-auto edz-scroll">
                        <template x-for="item in filtered" :key="item.id">
                            <button @click="$wire.setFilter('provider', item.id); close()"
                                :aria-pressed="isActive(item.id)"
                                class="edz-dropdown__item justify-between"
                                :class="activeCls(item.id)">
                                <span class="truncate" x-text="item.name"></span>
                                <x-edz.icon name="check" class="w-3.5 h-3.5" x-bind:class="checkCls(item.id)" />
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        @endif

        {{-- Rider (single, searchable) — rider tab --}}
        @if ($this->trackingTab === 'rider' && in_array('delivery_rider', $this->visibleColumns) && ! empty($this->allRiders))
            <div x-show="open === 'rider'" x-cloak class="edz-dropdown__section">
                <p class="edz-dropdown__section-title">{{ __('order_flow.rider_tab_title') }}</p>
                <button @click="$wire.setFilter('rider', null); close()"
                    class="edz-dropdown__item justify-between {{ empty($this->filters['rider']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                    {{ __('general.all') }}
                    <x-edz.icon name="check" class="w-3.5 h-3.5 {{ empty($this->filters['rider']) ? 'opacity-100' : 'opacity-0' }}" />
                </button>
                <div x-data="edzSearchableList()" data-items='@json($this->searchableRiders)' data-active='@json(array_filter([$this->filters['rider'] ?? null]))'>
                    <input type="search" x-model="query" placeholder="{{ __('general.search') }}" class="edz-input text-sm mb-1" autocomplete="off">
                    <div class="max-h-[40vh] sm:max-h-[350px] overflow-y-auto edz-scroll">
                        <template x-for="item in filtered" :key="item.id">
                            <button @click="$wire.setFilter('rider', item.id); close()"
                                :aria-pressed="isActive(item.id)"
                                class="edz-dropdown__item justify-between"
                                :class="activeCls(item.id)">
                                <span class="inline-flex items-center gap-1 truncate">
                                    <x-edz.icon name="user" class="w-3 h-3 shrink-0 text-ink-muted" />
                                    <span class="truncate" x-text="item.name"></span>
                                </span>
                                <span class="text-[10px] tabular-nums text-ink-muted" x-text="item.total > 0 ? item.total : ''"></span>
                                <x-edz.icon name="check" class="w-3.5 h-3.5 shrink-0" x-bind:class="checkCls(item.id)" />
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        @endif

        {{-- Assigned-to (single, searchable) --}}
        @if (in_array('assigned_to', $this->visibleColumns) && ! empty($this->allMembers))
            <div x-show="open === 'assigned'" x-cloak class="edz-dropdown__section">
                <p class="edz-dropdown__section-title">{{ __('merchant_panel.assigned_agent') }}</p>
                <button @click="$wire.setFilter('assigned_to', null); close()"
                    class="edz-dropdown__item justify-between {{ empty($this->filters['assigned_to']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                    {{ __('general.all') }}
                    <x-edz.icon name="check" class="w-3.5 h-3.5 {{ empty($this->filters['assigned_to']) ? 'opacity-100' : 'opacity-0' }}" />
                </button>
                <div x-data="edzSearchableList()" data-items='@json($this->allMembers)' data-active='@json(array_filter([$this->filters['assigned_to'] ?? null]))'>
                    <input type="search" x-model="query" placeholder="{{ __('general.search') }}" class="edz-input text-sm mb-1" autocomplete="off">
                    <div class="max-h-[40vh] sm:max-h-[350px] overflow-y-auto edz-scroll">
                        <template x-for="item in filtered" :key="item.id">
                            <button @click="$wire.setFilter('assigned_to', item.id); close()"
                                :aria-pressed="isActive(item.id)"
                                class="edz-dropdown__item justify-between"
                                :class="activeCls(item.id)">
                                <span class="inline-flex items-center gap-1 truncate">
                                    <x-edz.icon name="user" class="w-3 h-3 shrink-0 text-ink-muted" />
                                    <span class="truncate" x-text="item.name"></span>
                                </span>
                                <x-edz.icon name="check" class="w-3.5 h-3.5" x-bind:class="checkCls(item.id)" />
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        @endif

        {{-- Confirmed-by (single, searchable) — reused for the toolbar + header portal. --}}
        @if (in_array('confirmed_by', $this->visibleColumns) && ! empty($this->allMembers))
            <div x-show="open === 'confirmed'" x-cloak class="edz-dropdown__section">
                <p class="edz-dropdown__section-title">{{ __('merchant_panel.confirmed_by') }}</p>
                <button @click="$wire.setFilter('confirmed_by', null); close()"
                    class="edz-dropdown__item justify-between {{ empty($this->filters['confirmed_by']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                    {{ __('general.all') }}
                    <x-edz.icon name="check" class="w-3.5 h-3.5 {{ empty($this->filters['confirmed_by']) ? 'opacity-100' : 'opacity-0' }}" />
                </button>
                <div x-data="edzSearchableList()" data-items='@json($this->allMembers)' data-active='@json(array_filter([$this->filters['confirmed_by'] ?? null]))'>
                    <input type="search" x-model="query" placeholder="{{ __('general.search') }}" class="edz-input text-sm mb-1" autocomplete="off">
                    <div class="max-h-[40vh] sm:max-h-[350px] overflow-y-auto edz-scroll">
                        <template x-for="item in filtered" :key="item.id">
                            <button @click="$wire.setFilter('confirmed_by', item.id); close()"
                                :aria-pressed="isActive(item.id)"
                                class="edz-dropdown__item justify-between"
                                :class="activeCls(item.id)">
                                <span class="inline-flex items-center gap-1 truncate">
                                    <x-edz.icon name="user" class="w-3 h-3 shrink-0 text-ink-muted" />
                                    <span class="truncate" x-text="item.name"></span>
                                </span>
                                <x-edz.icon name="check" class="w-3.5 h-3.5" x-bind:class="checkCls(item.id)" />
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        @endif

        {{-- State (single, searchable) --}}
        @if (in_array('state', $this->visibleColumns) && ! empty($this->allStates))
            <div x-show="open === 'state'" x-cloak class="edz-dropdown__section">
                <p class="edz-dropdown__section-title">{{ __('merchant_panel.state') }}</p>
                <button @click="$wire.setFilter('state', null); close()"
                    class="edz-dropdown__item justify-between {{ empty($this->filters['state']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                    {{ __('general.all') }}
                    <x-edz.icon name="check" class="w-3.5 h-3.5 {{ empty($this->filters['state']) ? 'opacity-100' : 'opacity-0' }}" />
                </button>
                <div x-data="edzSearchableList()" data-items='@json($this->allStates)' data-active='@json(array_filter([$this->filters['state'] ?? null]))'>
                    <input type="search" x-model="query" placeholder="{{ __('general.search') }}" class="edz-input text-sm mb-1" autocomplete="off">
                    <div class="max-h-[40vh] sm:max-h-[350px] overflow-y-auto edz-scroll">
                        <template x-for="item in filtered" :key="item.id">
                            <button @click="$wire.setFilter('state', item.id); close()"
                                :aria-pressed="isActive(item.id)"
                                class="edz-dropdown__item justify-between"
                                :class="activeCls(item.id)">
                                <span class="truncate" x-text="item.name"></span>
                                <x-edz.icon name="check" class="w-3.5 h-3.5" x-bind:class="checkCls(item.id)" />
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        @endif

        {{-- City (single, searchable) --}}
        @if (in_array('city', $this->visibleColumns) && ! empty($this->allCities))
            <div x-show="open === 'city'" x-cloak class="edz-dropdown__section">
                <p class="edz-dropdown__section-title">{{ __('merchant_panel.city') }}</p>
                <button @click="$wire.setFilter('city', null); close()"
                    class="edz-dropdown__item justify-between {{ empty($this->filters['city']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                    {{ __('general.all') }}
                    <x-edz.icon name="check" class="w-3.5 h-3.5 {{ empty($this->filters['city']) ? 'opacity-100' : 'opacity-0' }}" />
                </button>
                <div x-data="edzSearchableList()" data-items='@json($this->allCities)' data-active='@json(array_filter([$this->filters['city'] ?? null]))'>
                    <input type="search" x-model="query" placeholder="{{ __('general.search') }}" class="edz-input text-sm mb-1" autocomplete="off">
                    <div class="max-h-[40vh] sm:max-h-[350px] overflow-y-auto edz-scroll">
                        <template x-for="item in filtered" :key="item.id">
                            <button @click="$wire.setFilter('city', item.id); close()"
                                :aria-pressed="isActive(item.id)"
                                class="edz-dropdown__item justify-between"
                                :class="activeCls(item.id)">
                                <span class="truncate" x-text="item.name"></span>
                                <x-edz.icon name="check" class="w-3.5 h-3.5" x-bind:class="checkCls(item.id)" />
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        @endif

        {{-- Date (from/to) --}}
        @if (in_array('shipping_date', $this->visibleColumns))
            <div x-show="open === 'date'" x-cloak class="edz-dropdown__section">
                <p class="edz-dropdown__section-title">{{ __('order_flow.filter_date') }}</p>
                <div class="px-1 flex items-center gap-2">
                    <input type="text" wire:model.blur="filters.date_from" class="edz-input text-sm flatpickr-input"
                        placeholder="{{ __('order_flow.filter_date') }} —" autocomplete="off">
                    <span class="text-ink-muted text-sm">—</span>
                    <input type="text" wire:model.blur="filters.date_to" class="edz-input text-sm flatpickr-input"
                        placeholder="— {{ __('order_flow.filter_date') }}" autocomplete="off">
                </div>
            </div>
        @endif
    </div>
</div>