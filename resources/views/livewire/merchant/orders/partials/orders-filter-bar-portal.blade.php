{{-- Orders toolbar drill-down filter portal (products-style). A single "Filters"
     trigger dispatches `edz-toolbar-filter-open` which opens this two-level portal:
     root groups → controls per group. Groups are dynamic — only those whose column
     is NOT visible in the table appear (visible columns are filtered via their
     header filter icons in orders-table-header).

     Control types per group:
       single          – radio-like list with "All" (wilaya/city/provider/status/confirmed_by/assigned_to/delivery_type/source/shipment_type/stopdesk_point)
       text            – plain text input (number/customer/phone/address/notes)
       product         – x-edz.product-select (products)
       min_max         – two number inputs (amount/weight)
       range_flatpickr – two .flatpickr-input fields with icon trigger (date)
       toggle          – tri-state yes/no/all (send_from_carrier_warehouse/refund_request/can_open)
       multi_checkbox  – checkbox list (status) --}}

@php
    $forwardChevron = app()->getLocale() === 'ar' ? 'chevron-left' : 'chevron-right';
    $backChevron    = app()->getLocale() === 'ar' ? 'chevron-right' : 'chevron-left';
    $available      = $this->availableFilterGroups();
    $groupLabels    = [
        'number'               => __('merchant_panel.number'),
        'customer'             => __('merchant_panel.customer'),
        'phone'                => __('merchant_panel.phone'),
        'address'              => __('merchant_panel.address'),
        'notes'                => __('merchant_panel.notes'),
        'products'             => __('merchant_panel.products'),
        'wilaya'               => __('merchant_panel.state'),
        'city'                 => __('merchant_panel.city'),
        'delivery_type'        => __('storefront.delivery_type'),
        'shipping_provider'    => __('merchant_panel.shipping_provider'),
        'stopdesk_point'       => __('merchant_panel.stopdesk_point'),
        'shipment_type'        => __('merchant_panel.shipment_type'),
        'source'               => __('merchant_panel.source'),
        'status'               => __('merchant_panel.status'),
        'assigned_to'          => __('merchant_panel.assigned_agent'),
        'amount'               => __('order_flow.filter_amount'),
        'weight'               => __('merchant_panel.weight'),
        'date'                 => __('order_flow.filter_date'),
        'send_from_carrier_warehouse' => __('merchant_panel.send_from_carrier_warehouse'),
        'refund_request'        => __('merchant_panel.refund_request'),
        'can_open'   => __('merchant_panel.can_open'),
        'confirmed_by'         => __('merchant_panel.confirmed_by'),
    ];
@endphp

<div x-data="dropdownPosition()" x-show="open" @click.away="close()"
    @edz-toolbar-filter-open.window="$event.detail && toggle($event, $event.detail)">

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

        {{-- ==================== ROOT ==================== --}}
        <div x-show="open === 'root'" x-cloak class="edz-dropdown__section">
            <p class="edz-dropdown__section-title">{{ __('merchant_panel.filters') }}</p>

            @foreach ($available as $group)
                <button type="button" @click="open = '{{ $group }}'"
                    class="edz-dropdown__item justify-between">
                    <span class="truncate">{{ $groupLabels[$group] ?? $group }}</span>
                    <x-edz.icon :name="$forwardChevron" class="w-3.5 h-3.5 shrink-0 text-ink-muted" />
                </button>
            @endforeach

            @if ($this->activeFilterCount() > 0)
                <button type="button" wire:click="clearFilters" @click="close()"
                    class="edz-dropdown__item justify-between text-danger-600">
                    <span class="truncate">{{ __('merchant_panel.clear_filters') }}</span>
                    <x-edz.icon name="trash" class="w-3.5 h-3.5 shrink-0" />
                </button>
            @endif
        </div>

        {{-- ==================== TEXT groups ==================== --}}
        @foreach (['number', 'customer', 'phone', 'address', 'notes'] as $textGroup)
            @if (in_array($textGroup, $available))
                <div x-show="open === '{{ $textGroup }}'" x-cloak class="edz-dropdown__section">
                    <div class="mb-1 flex items-center gap-0.5 px-0.5">
                        <button type="button" @click="open = 'root'"
                            class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                            title="{{ __('general.back') }}" aria-label="{{ __('general.back') }}">
                            <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                        </button>
                        <p class="edz-dropdown__section-title m-0">{{ $groupLabels[$textGroup] }}</p>
                    </div>
                    <div class="px-1">
                        <input type="text" wire:model.blur="filters.{{ $textGroup }}" class="edz-input text-sm w-full"
                            placeholder="{{ $groupLabels[$textGroup] }}">
                    </div>
                </div>
            @endif
        @endforeach

        {{-- ==================== PRODUCTS ==================== --}}
        @if (in_array('products', $available))
            <div x-show="open === 'products'" x-cloak class="edz-dropdown__section">
                <div class="mb-1 flex items-center gap-0.5 px-0.5">
                    <button type="button" @click="open = 'root'"
                        class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                        title="{{ __('general.back') }}" aria-label="{{ __('general.back') }}">
                        <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                    </button>
                    <p class="edz-dropdown__section-title m-0">{{ $groupLabels['products'] }}</p>
                </div>
                <div class="px-1">
                    <x-edz.product-select :options="$filterProducts" wire:model="filters.product_id"
                        wire:fullmodel="filters.product" size="sm"
                        placeholder="{{ __('merchant_panel.filter_by_product') }}" />
                </div>
            </div>
        @endif

        {{-- ==================== WILAYA ==================== --}}
        @if (in_array('wilaya', $available))
            <div x-show="open === 'wilaya'" x-cloak class="edz-dropdown__section">
                <div class="mb-1 flex items-center gap-0.5 px-0.5">
                    <button type="button" @click="open = 'root'"
                        class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                        title="{{ __('general.back') }}" aria-label="{{ __('general.back') }}">
                        <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                    </button>
                    <p class="edz-dropdown__section-title m-0">{{ $groupLabels['wilaya'] }}</p>
                </div>
                <button type="button" @click="$wire.setFilter('wilaya', null); close()"
                    class="edz-dropdown__item justify-between {{ empty($this->filters['wilaya']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                    <span class="truncate">{{ __('general.all') }}</span>
                    <x-edz.icon name="check" class="w-3.5 h-3.5 shrink-0 {{ empty($this->filters['wilaya']) ? 'opacity-100' : 'opacity-0' }}" />
                </button>
                <div x-data="edzSearchableList()" data-items="@json($this->allStates)" data-active="@json(array_filter([$this->filters['wilaya'] ?? null]))">
                    <input type="search" x-model="query" placeholder="{{ __('general.search') }}" class="edz-input text-sm mb-1" autocomplete="off">
                    <template x-for="item in filtered" :key="item.id">
                        <button type="button" @click="$wire.setFilter('wilaya', item.id); close()"
                            :aria-pressed="isActive(item.id)"
                            class="edz-dropdown__item justify-between"
                            :class="activeCls(item.id)">
                            <span class="truncate"><span class="edz-code-badge" x-text="item.state_code || ''"></span> <span x-text="item.name"></span></span>
                            <x-edz.icon name="check" class="w-3.5 h-3.5 shrink-0" x-bind:class="checkCls(item.id)" />
                        </button>
                    </template>
                </div>
            </div>
        @endif

        {{-- ==================== CITY ==================== --}}
        @if (in_array('city', $available))
            <div x-show="open === 'city'" x-cloak class="edz-dropdown__section">
                <div class="mb-1 flex items-center gap-0.5 px-0.5">
                    <button type="button" @click="open = 'root'"
                        class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                        title="{{ __('general.back') }}" aria-label="{{ __('general.back') }}">
                        <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                    </button>
                    <p class="edz-dropdown__section-title m-0">{{ $groupLabels['city'] }}</p>
                </div>
                <button type="button" @click="$wire.setFilter('city', null); close()"
                    class="edz-dropdown__item justify-between {{ empty($this->filters['city']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                    <span class="truncate">{{ __('general.all') }}</span>
                    <x-edz.icon name="check" class="w-3.5 h-3.5 shrink-0 {{ empty($this->filters['city']) ? 'opacity-100' : 'opacity-0' }}" />
                </button>
                <div x-data="edzSearchableList()" data-items="@json($this->allCities)" data-active="@json(array_filter([$this->filters['city'] ?? null]))">
                    <input type="search" x-model="query" placeholder="{{ __('general.search') }}" class="edz-input text-sm mb-1" autocomplete="off">
                    <template x-for="item in filtered" :key="item.id">
                        <button type="button" @click="$wire.setFilter('city', item.id); close()"
                            :aria-pressed="isActive(item.id)"
                            class="edz-dropdown__item justify-between"
                            :class="activeCls(item.id)">
                            <span class="truncate" x-text="item.name"></span>
                            <x-edz.icon name="check" class="w-3.5 h-3.5 shrink-0" x-bind:class="checkCls(item.id)" />
                        </button>
                    </template>
                </div>
            </div>
        @endif

        {{-- ==================== DELIVERY TYPE ==================== --}}
        @if (in_array('delivery_type', $available))
            <div x-show="open === 'delivery_type'" x-cloak class="edz-dropdown__section">
                <div class="mb-1 flex items-center gap-0.5 px-0.5">
                    <button type="button" @click="open = 'root'"
                        class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                        title="{{ __('general.back') }}" aria-label="{{ __('general.back') }}">
                        <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                    </button>
                    <p class="edz-dropdown__section-title m-0">{{ $groupLabels['delivery_type'] }}</p>
                </div>
                <button type="button" @click="$wire.setFilter('delivery_type', null); close()"
                    class="edz-dropdown__item justify-between {{ empty($this->filters['delivery_type']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                    <span class="truncate">{{ __('general.all') }}</span>
                    <x-edz.icon name="check" class="w-3.5 h-3.5 shrink-0 {{ empty($this->filters['delivery_type']) ? 'opacity-100' : 'opacity-0' }}" />
                </button>
                @foreach (['home' => __('storefront.home_delivery'), 'stopdesk' => __('storefront.stop_desk')] as $val => $lbl)
                    <button type="button" @click="$wire.setFilter('delivery_type', '{{ $val }}'); close()"
                        aria-pressed="{{ ($this->filters['delivery_type'] ?? null) === $val ? 'true' : 'false' }}"
                        class="edz-dropdown__item justify-between {{ ($this->filters['delivery_type'] ?? null) === $val ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                        <span class="truncate">{{ $lbl }}</span>
                        <x-edz.icon name="check" class="w-3.5 h-3.5 shrink-0 {{ ($this->filters['delivery_type'] ?? null) === $val ? 'opacity-100' : 'opacity-0' }}" />
                    </button>
                @endforeach
            </div>
        @endif

        {{-- ==================== SHIPPING PROVIDER ==================== --}}
        @if (in_array('shipping_provider', $available))
            <div x-show="open === 'shipping_provider'" x-cloak class="edz-dropdown__section">
                <div class="mb-1 flex items-center gap-0.5 px-0.5">
                    <button type="button" @click="open = 'root'"
                        class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                        title="{{ __('general.back') }}" aria-label="{{ __('general.back') }}">
                        <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                    </button>
                    <p class="edz-dropdown__section-title m-0">{{ $groupLabels['shipping_provider'] }}</p>
                </div>
                <button type="button" @click="$wire.setFilter('shipping_provider', null); close()"
                    class="edz-dropdown__item justify-between {{ empty($this->filters['shipping_provider']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                    <span class="truncate">{{ __('general.all') }}</span>
                    <x-edz.icon name="check" class="w-3.5 h-3.5 shrink-0 {{ empty($this->filters['shipping_provider']) ? 'opacity-100' : 'opacity-0' }}" />
                </button>
                <div x-data="edzSearchableList()" data-items="@json($this->allProviders)" data-active="@json(array_filter([$this->filters['shipping_provider'] ?? null]))">
                    <input type="search" x-model="query" placeholder="{{ __('general.search') }}" class="edz-input text-sm mb-1" autocomplete="off">
                    <template x-for="item in filtered" :key="item.id">
                        <button type="button" @click="$wire.setFilter('shipping_provider', item.id); close()"
                            :aria-pressed="isActive(item.id)"
                            class="edz-dropdown__item justify-between"
                            :class="activeCls(item.id)">
                            <span class="truncate" x-text="item.name"></span>
                            <x-edz.icon name="check" class="w-3.5 h-3.5 shrink-0" x-bind:class="checkCls(item.id)" />
                        </button>
                    </template>
                </div>
            </div>
        @endif

        {{-- ==================== STOPDESK POINT ==================== --}}
        @if (in_array('stopdesk_point', $available))
            <div x-show="open === 'stopdesk_point'" x-cloak class="edz-dropdown__section">
                <div class="mb-1 flex items-center gap-0.5 px-0.5">
                    <button type="button" @click="open = 'root'"
                        class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                        title="{{ __('general.back') }}" aria-label="{{ __('general.back') }}">
                        <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                    </button>
                    <p class="edz-dropdown__section-title m-0">{{ $groupLabels['stopdesk_point'] }}</p>
                </div>
                <button type="button" @click="$wire.setFilter('stopdesk_point', null); close()"
                    class="edz-dropdown__item justify-between {{ empty($this->filters['stopdesk_point']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                    <span class="truncate">{{ __('general.all') }}</span>
                    <x-edz.icon name="check" class="w-3.5 h-3.5 shrink-0 {{ empty($this->filters['stopdesk_point']) ? 'opacity-100' : 'opacity-0' }}" />
                </button>
                <div x-data="edzSearchableList()" data-items="@json($this->allStopdeskPoints)" data-active="@json(array_filter([$this->filters['stopdesk_point'] ?? null]))">
                    <input type="search" x-model="query" placeholder="{{ __('general.search') }}" class="edz-input text-sm mb-1" autocomplete="off">
                    <template x-for="item in filtered" :key="item.id">
                        <button type="button" @click="$wire.setFilter('stopdesk_point', item.id); close()"
                            :aria-pressed="isActive(item.id)"
                            class="edz-dropdown__item justify-between"
                            :class="activeCls(item.id)">
                            <span class="truncate" x-text="item.name"></span>
                            <x-edz.icon name="check" class="w-3.5 h-3.5 shrink-0" x-bind:class="checkCls(item.id)" />
                        </button>
                    </template>
                </div>
            </div>
        @endif

        {{-- ==================== SHIPMENT TYPE ==================== --}}
        @if (in_array('shipment_type', $available))
            <div x-show="open === 'shipment_type'" x-cloak class="edz-dropdown__section">
                <div class="mb-1 flex items-center gap-0.5 px-0.5">
                    <button type="button" @click="open = 'root'"
                        class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                        title="{{ __('general.back') }}" aria-label="{{ __('general.back') }}">
                        <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                    </button>
                    <p class="edz-dropdown__section-title m-0">{{ $groupLabels['shipment_type'] }}</p>
                </div>
                <button type="button" @click="$wire.setFilter('shipment_type', null); close()"
                    class="edz-dropdown__item justify-between {{ empty($this->filters['shipment_type']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                    <span class="truncate">{{ __('general.all') }}</span>
                    <x-edz.icon name="check" class="w-3.5 h-3.5 shrink-0 {{ empty($this->filters['shipment_type']) ? 'opacity-100' : 'opacity-0' }}" />
                </button>
                @foreach (['delivery' => __('merchant_panel.delivery'), 'exchange' => __('merchant_panel.exchange_label'), 'pickup' => __('merchant_panel.pickup_label')] as $val => $lbl)
                    <button type="button" @click="$wire.setFilter('shipment_type', '{{ $val }}'); close()"
                        aria-pressed="{{ ($this->filters['shipment_type'] ?? null) === $val ? 'true' : 'false' }}"
                        class="edz-dropdown__item justify-between {{ ($this->filters['shipment_type'] ?? null) === $val ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                        <span class="truncate">{{ $lbl }}</span>
                        <x-edz.icon name="check" class="w-3.5 h-3.5 shrink-0 {{ ($this->filters['shipment_type'] ?? null) === $val ? 'opacity-100' : 'opacity-0' }}" />
                    </button>
                @endforeach
            </div>
        @endif

        {{-- ==================== SOURCE ==================== --}}
        @if (in_array('source', $available))
            <div x-show="open === 'source'" x-cloak class="edz-dropdown__section">
                <div class="mb-1 flex items-center gap-0.5 px-0.5">
                    <button type="button" @click="open = 'root'"
                        class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                        title="{{ __('general.back') }}" aria-label="{{ __('general.back') }}">
                        <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                    </button>
                    <p class="edz-dropdown__section-title m-0">{{ $groupLabels['source'] }}</p>
                </div>
                <button type="button" @click="$wire.setFilter('source', null); close()"
                    class="edz-dropdown__item justify-between {{ empty($this->filters['source']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                    <span class="truncate">{{ __('general.all') }}</span>
                    <x-edz.icon name="check" class="w-3.5 h-3.5 shrink-0 {{ empty($this->filters['source']) ? 'opacity-100' : 'opacity-0' }}" />
                </button>
                @foreach (['store' => __('merchant_panel.store'), 'manual' => __('merchant.delivery_man')] as $val => $lbl)
                    <button type="button" @click="$wire.setFilter('source', '{{ $val }}'); close()"
                        aria-pressed="{{ ($this->filters['source'] ?? null) === $val ? 'true' : 'false' }}"
                        class="edz-dropdown__item justify-between {{ ($this->filters['source'] ?? null) === $val ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                        <span class="truncate">{{ $lbl }}</span>
                        <x-edz.icon name="check" class="w-3.5 h-3.5 shrink-0 {{ ($this->filters['source'] ?? null) === $val ? 'opacity-100' : 'opacity-0' }}" />
                    </button>
                @endforeach
            </div>
        @endif

        {{-- ==================== STATUS (multi-checkbox) ==================== --}}
        @if (in_array('status', $available))
            <div x-show="open === 'status'" x-cloak class="edz-dropdown__section">
                <div class="mb-1 flex items-center gap-0.5 px-0.5">
                    <button type="button" @click="open = 'root'"
                        class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                        title="{{ __('general.back') }}" aria-label="{{ __('general.back') }}">
                        <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                    </button>
                    <p class="edz-dropdown__section-title m-0">{{ $groupLabels['status'] }}</p>
                </div>
                <div x-data="edzSearchableList()"
                    data-items="@json($this->searchableStatuses)"
                    data-active="@json($this->filters['status'] ?? [])">
                    <input type="search" x-model="query" placeholder="{{ __('general.search') }}" class="edz-input text-sm mb-1" autocomplete="off">
                    <template x-for="s in filtered" :key="s.id">
                        <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-surface-secondary cursor-pointer text-xs">
                            <input type="checkbox" :checked="isActive(s.id)"
                                @click="$wire.toggleStatusFilter(s.id); toggleActive(s.id)"
                                class="rounded border-gray-300">
                            <span class="w-2 h-2 rounded-full shrink-0"
                                :style="`background: ${s.color === 'success' ? '#22c55e' : s.color === 'info' ? '#3b82f6' : s.color === 'warning' ? '#f59e0b' : s.color === 'danger' ? '#ef4444' : '#6b7280'}`"></span>
                            <span x-text="s.name" class="truncate"></span>
                        </label>
                    </template>
                </div>
            </div>
        @endif

        {{-- ==================== ASSIGNED-TO (single) ==================== --}}
        @if (in_array('assigned_to', $available))
            <div x-show="open === 'assigned_to'" x-cloak class="edz-dropdown__section">
                <div class="mb-1 flex items-center gap-0.5 px-0.5">
                    <button type="button" @click="open = 'root'"
                        class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                        title="{{ __('general.back') }}" aria-label="{{ __('general.back') }}">
                        <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                    </button>
                    <p class="edz-dropdown__section-title m-0">{{ $groupLabels['assigned_to'] }}</p>
                </div>
                <button type="button" @click="$wire.setFilter('assigned_to', null); close()"
                    class="edz-dropdown__item justify-between {{ empty($this->filters['assigned_to']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                    <span class="truncate">{{ __('general.all') }}</span>
                    <x-edz.icon name="check" class="w-3.5 h-3.5 shrink-0 {{ empty($this->filters['assigned_to']) ? 'opacity-100' : 'opacity-0' }}" />
                </button>
                <div x-data="edzSearchableList()"
                    data-items="@json($this->searchableMembers)"
                    data-active="@json(array_filter([$this->filters['assigned_to'] ?? null]))">
                    <input type="search" x-model="query" placeholder="{{ __('general.search') }}" class="edz-input text-sm mb-1" autocomplete="off">
                    <template x-for="item in filtered" :key="item.id">
                        <button type="button" @click="$wire.setFilter('assigned_to', item.id); close()"
                            :aria-pressed="isActive(item.id)"
                            class="edz-dropdown__item justify-between"
                            :class="activeCls(item.id)">
                            <span class="inline-flex items-center gap-1 truncate">
                                <x-edz.icon name="user" class="w-3 h-3 shrink-0 text-ink-muted" />
                                <span class="truncate" x-text="item.name"></span>
                            </span>
                            <x-edz.icon name="check" class="w-3.5 h-3.5 shrink-0" x-bind:class="checkCls(item.id)" />
                        </button>
                    </template>
                </div>
            </div>
        @endif

        {{-- ==================== AMOUNT (min/max) ==================== --}}
        @if (in_array('amount', $available))
            <div x-show="open === 'amount'" x-cloak class="edz-dropdown__section">
                <div class="mb-1 flex items-center gap-0.5 px-0.5">
                    <button type="button" @click="open = 'root'"
                        class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                        title="{{ __('general.back') }}" aria-label="{{ __('general.back') }}">
                        <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                    </button>
                    <p class="edz-dropdown__section-title m-0">{{ $groupLabels['amount'] }}</p>
                </div>
                <div class="px-1 flex items-center gap-2">
                    <input type="number" min="0" step="0.01" wire:model.blur="filters.amount_min"
                        placeholder="{{ __('order_flow.amount_min_placeholder') }}" class="edz-input text-sm">
                    <span class="text-ink-muted text-sm">—</span>
                    <input type="number" min="0" step="0.01" wire:model.blur="filters.amount_max"
                        placeholder="{{ __('order_flow.amount_max_placeholder') }}" class="edz-input text-sm">
                </div>
            </div>
        @endif

        {{-- ==================== WEIGHT (min/max) ==================== --}}
        @if (in_array('weight', $available))
            <div x-show="open === 'weight'" x-cloak class="edz-dropdown__section">
                <div class="mb-1 flex items-center gap-0.5 px-0.5">
                    <button type="button" @click="open = 'root'"
                        class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                        title="{{ __('general.back') }}" aria-label="{{ __('general.back') }}">
                        <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                    </button>
                    <p class="edz-dropdown__section-title m-0">{{ $groupLabels['weight'] }}</p>
                </div>
                <div class="px-1 flex items-center gap-2">
                    <input type="number" min="0" step="0.01" wire:model.blur="filters.weight_min"
                        placeholder="{{ __('order_flow.amount_min_placeholder') }}" class="edz-input text-sm">
                    <span class="text-ink-muted text-sm">—</span>
                    <input type="number" min="0" step="0.01" wire:model.blur="filters.weight_max"
                        placeholder="{{ __('order_flow.amount_max_placeholder') }}" class="edz-input text-sm">
                </div>
            </div>
        @endif

        {{-- ==================== DATE (range with flatpickr icon) ==================== --}}
        @if (in_array('date', $available))
            <div x-show="open === 'date'" x-cloak class="edz-dropdown__section">
                <div class="mb-1 flex items-center gap-0.5 px-0.5">
                    <button type="button" @click="open = 'root'"
                        class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                        title="{{ __('general.back') }}" aria-label="{{ __('general.back') }}">
                        <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                    </button>
                    <p class="edz-dropdown__section-title m-0">{{ $groupLabels['date'] }}</p>
                </div>
                <div class="px-1 space-y-2">
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 start-0 ps-2 flex items-center text-ink-muted">
                            <x-edz.icon name="calendar" class="w-4 h-4" />
                        </span>
                        <input type="text" wire:model.blur="filters.date_from"
                            class="edz-input text-sm flatpickr-input ps-8"
                            placeholder="{{ __('order_flow.filter_date') }} —" autocomplete="off">
                    </div>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 start-0 ps-2 flex items-center text-ink-muted">
                            <x-edz.icon name="calendar" class="w-4 h-4" />
                        </span>
                        <input type="text" wire:model.blur="filters.date_to"
                            class="edz-input text-sm flatpickr-input ps-8"
                            placeholder="— {{ __('order_flow.filter_date') }}" autocomplete="off">
                    </div>
                </div>
            </div>
        @endif

        {{-- ==================== SEND-FROM-WAREHOUSE (tri-state) ==================== --}}
        @if (in_array('send_from_carrier_warehouse', $available))
            <div x-show="open === 'send_from_carrier_warehouse'" x-cloak class="edz-dropdown__section">
                <div class="mb-1 flex items-center gap-0.5 px-0.5">
                    <button type="button" @click="open = 'root'"
                        class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                        title="{{ __('general.back') }}" aria-label="{{ __('general.back') }}">
                        <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                    </button>
                    <p class="edz-dropdown__section-title m-0">{{ $groupLabels['send_from_carrier_warehouse'] }}</p>
                </div>
                @foreach ([null => __('general.all'), true => __('buttons.yes'), false => __('buttons.no')] as $val => $lbl)
                    <button type="button" @click="$wire.setFilter('send_from_carrier_warehouse', {{ is_null($val) ? 'null' : ($val ? 'true' : 'false') }}); close()"
                        class="edz-dropdown__item justify-between {{ $this->filters['send_from_carrier_warehouse'] === $val ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                        <span class="truncate">{{ $lbl }}</span>
                        <x-edz.icon name="check" class="w-3.5 h-3.5 shrink-0 {{ $this->filters['send_from_carrier_warehouse'] === $val ? 'opacity-100' : 'opacity-0' }}" />
                    </button>
                @endforeach
            </div>
        @endif

        {{-- ==================== IS-COLLECTION (tri-state) ==================== --}}
        @if (in_array('refund_request', $available))
            <div x-show="open === 'refund_request'" x-cloak class="edz-dropdown__section">
                <div class="mb-1 flex items-center gap-0.5 px-0.5">
                    <button type="button" @click="open = 'root'"
                        class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                        title="{{ __('general.back') }}" aria-label="{{ __('general.back') }}">
                        <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                    </button>
                    <p class="edz-dropdown__section-title m-0">{{ $groupLabels['refund_request'] }}</p>
                </div>
                @foreach ([null => __('general.all'), true => __('buttons.yes'), false => __('buttons.no')] as $val => $lbl)
                    <button type="button" @click="$wire.setFilter('refund_request', {{ is_null($val) ? 'null' : ($val ? 'true' : 'false') }}); close()"
                        class="edz-dropdown__item justify-between {{ $this->filters['refund_request'] === $val ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                        <span class="truncate">{{ $lbl }}</span>
                        <x-edz.icon name="check" class="w-3.5 h-3.5 shrink-0 {{ $this->filters['refund_request'] === $val ? 'opacity-100' : 'opacity-0' }}" />
                    </button>
                @endforeach
            </div>
        @endif

        {{-- ==================== AUTHORIZED-TO-OPEN (tri-state) ==================== --}}
        @if (in_array('can_open', $available))
            <div x-show="open === 'can_open'" x-cloak class="edz-dropdown__section">
                <div class="mb-1 flex items-center gap-0.5 px-0.5">
                    <button type="button" @click="open = 'root'"
                        class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                        title="{{ __('general.back') }}" aria-label="{{ __('general.back') }}">
                        <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                    </button>
                    <p class="edz-dropdown__section-title m-0">{{ $groupLabels['can_open'] }}</p>
                </div>
                @foreach ([null => __('general.all'), true => __('buttons.yes'), false => __('buttons.no')] as $val => $lbl)
                    <button type="button" @click="$wire.setFilter('can_open', {{ is_null($val) ? 'null' : ($val ? 'true' : 'false') }}); close()"
                        class="edz-dropdown__item justify-between {{ $this->filters['can_open'] === $val ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                        <span class="truncate">{{ $lbl }}</span>
                        <x-edz.icon name="check" class="w-3.5 h-3.5 shrink-0 {{ $this->filters['can_open'] === $val ? 'opacity-100' : 'opacity-0' }}" />
                    </button>
                @endforeach
            </div>
        @endif

        {{-- ==================== CONFIRMED-BY (single) ==================== --}}
        @if (in_array('confirmed_by', $available))
            <div x-show="open === 'confirmed_by'" x-cloak class="edz-dropdown__section">
                <div class="mb-1 flex items-center gap-0.5 px-0.5">
                    <button type="button" @click="open = 'root'"
                        class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                        title="{{ __('general.back') }}" aria-label="{{ __('general.back') }}">
                        <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                    </button>
                    <p class="edz-dropdown__section-title m-0">{{ $groupLabels['confirmed_by'] }}</p>
                </div>
                <button type="button" @click="$wire.setFilter('confirmed_by', null); close()"
                    class="edz-dropdown__item justify-between {{ empty($this->filters['confirmed_by']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                    <span class="truncate">{{ __('general.all') }}</span>
                    <x-edz.icon name="check" class="w-3.5 h-3.5 shrink-0 {{ empty($this->filters['confirmed_by']) ? 'opacity-100' : 'opacity-0' }}" />
                </button>
                <div x-data="edzSearchableList()"
                    data-items="@json($this->searchableMembers)"
                    data-active="@json(array_filter([$this->filters['confirmed_by'] ?? null]))">
                    <input type="search" x-model="query" placeholder="{{ __('general.search') }}" class="edz-input text-sm mb-1" autocomplete="off">
                    <template x-for="item in filtered" :key="item.id">
                        <button type="button" @click="$wire.setFilter('confirmed_by', item.id); close()"
                            :aria-pressed="isActive(item.id)"
                            class="edz-dropdown__item justify-between"
                            :class="activeCls(item.id)">
                            <span class="inline-flex items-center gap-1 truncate">
                                <x-edz.icon name="user" class="w-3 h-3 shrink-0 text-ink-muted" />
                                <span class="truncate" x-text="item.name"></span>
                            </span>
                            <x-edz.icon name="check" class="w-3.5 h-3.5 shrink-0" x-bind:class="checkCls(item.id)" />
                        </button>
                    </template>
                </div>
            </div>
        @endif
    </div>
</div>