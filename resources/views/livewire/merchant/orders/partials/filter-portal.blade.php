    {{-- Filter Portal — single container, fixed-positioned --}}
    <div x-data="dropdownPosition()" x-show="open" @click.away="close()"
        @edz-filter-open.window="$event.detail && toggle($event, $event.detail)">
        <div x-show="open" x-cloak
            class="fixed inset-0 z-[205] bg-black/40 backdrop-blur-sm sm:hidden" @click="close()"></div>
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
                'sm:max-h-64': open === 'wilaya' || open === 'status' ||
                    open === 'assigned_to' || open === 'city' || open === 'delivery_type' ||
                    open === 'shipping_provider' || open === 'stopdesk_point' ||
                    open === 'shipment_type' || open === 'confirmed_by',
                'sm:w-48': open === 'product' || open === 'amount' || open === 'address' ||
                    open === 'notes' || open === 'weight' ||
                    open === 'send_from_carrier_warehouse',
                'sm:w-52': open === 'wilaya' || open === 'status' || open === 'assigned_to' ||
                    open === 'date'
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

        {{-- Wilaya --}}
        @if (in_array('wilaya', $this->visibleColumns))
            <div x-show="open === 'wilaya'" x-cloak>
                <button @click="$wire.setFilter('wilaya', null); $wire.setFilter('city', null)"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ !$this->filters['wilaya'] ? 'bg-surface-secondary font-medium' : '' }}">
                    —
                </button>
                @foreach ($this->allStates as $st)
                    <button
                        @click="$wire.setFilter('wilaya', '{{ $st['id'] }}'); $wire.loadFilterCities('{{ $st['id'] }}')"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['wilaya'] == $st['id'] ? 'bg-surface-secondary font-medium' : '' }}"
                        data-name="{{ $st['name'] }}">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="edz-code-badge">{{ $st['state_code'] ?? '' }}</span>
                            {{ $st['name'] }}
                        </span>
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Product --}}
        @if (in_array('products', $this->visibleColumns))
            <div x-show="open === 'product'" x-cloak>
                <x-edz.product-select :options="$filterProducts" wire:model="filters.product_id"
                    wire:fullmodel="filters.product" size="sm"
                    placeholder="{{ __('merchant_panel.filter_by_product') }}" />
            </div>
        @endif

        {{-- Amount --}}
        @if (in_array('total', $this->visibleColumns))
            <div x-show="open === 'amount'" x-cloak>
                <div class="flex items-center gap-1">
                    <div class="relative flex-1">
                        <input type="number" wire:model.live.debounce.600ms="filters.amount_min" placeholder="Min"
                            class="edz-input text-xs w-full pe-6">
                        @if ($this->filters['amount_min'] !== null && $this->filters['amount_min'] !== '')
                            <button wire:click="$set('filters.amount_min', '')" type="button"
                                class="absolute end-1 top-1/2 -translate-y-1/2 text-ink-muted hover:text-accent-500 transition"
                                aria-label="Clear min amount">
                                <x-edz.icon name="x-mark" class="w-3.5 h-3.5" />
                            </button>
                        @endif
                    </div>
                    <div class="relative flex-1">
                        <input type="number" wire:model.live.debounce.600ms="filters.amount_max" placeholder="Max"
                            class="edz-input text-xs w-full pe-6">
                        @if ($this->filters['amount_max'] !== null && $this->filters['amount_max'] !== '')
                            <button wire:click="$set('filters.amount_max', '')" type="button"
                                class="absolute end-1 top-1/2 -translate-y-1/2 text-ink-muted hover:text-accent-500 transition"
                                aria-label="Clear max amount">
                                <x-edz.icon name="x-mark" class="w-3.5 h-3.5" />
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Status --}}
        @if (in_array('status', $this->visibleColumns))
            <div x-show="open === 'status'" x-cloak>
                @foreach ($this->allStatuses as $s)
                    <label
                        class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-surface-secondary cursor-pointer text-xs"
                        data-name="{{ $s['label'] }}">
                        <input type="checkbox" value="{{ $s['id'] }}"
                            wire:click="toggleStatusFilter('{{ $s['id'] }}')"
                            {{ in_array($s['id'], $this->filters['status'] ?? []) ? 'checked' : '' }}
                            class="rounded border-gray-300">
                        <span class="w-2 h-2 rounded-full shrink-0"
                            style="background: {{ match ($s['color'] ?? 'gray') {'success' => '#22c55e','info' => '#3b82f6','warning' => '#f59e0b','danger' => '#ef4444',default => '#6b7280'} }}"></span>
                        {{ \Edzeery\MyStatusKit\Facades\Status::for('order', $s['key'] ?? 'default')->label() }}
                    </label>
                @endforeach
            </div>
        @endif

        {{-- Assigned Agent --}}
        @if (in_array('assigned_agent', $this->visibleColumns))
            <div x-show="open === 'assigned_to'" x-cloak>
                <button @click="$wire.setFilter('assigned_to', null)"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ !$this->filters['assigned_to'] ? 'bg-surface-secondary font-medium' : '' }}">
                    —
                </button>
                @foreach ($this->allMembers as $m)
                    <button @click="$wire.setFilter('assigned_to', '{{ $m['id'] }}')"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['assigned_to'] == $m['id'] ? 'bg-surface-secondary font-medium' : '' }}"
                        data-name="{{ $m['user']['name'] }}">
                        {{ $m['user']['name'] }}
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Date --}}
        @if (in_array('created_at', $this->visibleColumns))
            <div x-show="open === 'date'" x-cloak>
                <div class="flex flex-col gap-1">
                    <div class="relative">
                        <input type="text" wire:model.blur="filters.date_from"
                            class="edz-input text-xs w-full flatpickr-input pe-7" placeholder="From"
                            autocomplete="off">
                        @if (!empty($this->filters['date_from']))
                            <button wire:click="$set('filters.date_from', '')" type="button"
                                class="absolute end-1 top-1/2 -translate-y-1/2 text-ink-muted hover:text-accent-500 transition"
                                aria-label="Clear from date">
                                <x-edz.icon name="x-mark" class="w-3.5 h-3.5" />
                            </button>
                        @endif
                    </div>
                    <div class="relative">
                        <input type="text" wire:model.blur="filters.date_to"
                            class="edz-input text-xs w-full flatpickr-input pe-7" placeholder="To"
                            autocomplete="off">
                        @if (!empty($this->filters['date_to']))
                            <button wire:click="$set('filters.date_to', '')" type="button"
                                class="absolute end-1 top-1/2 -translate-y-1/2 text-ink-muted hover:text-accent-500 transition"
                                aria-label="Clear to date">
                                <x-edz.icon name="x-mark" class="w-3.5 h-3.5" />
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Delivery Type --}}
        @if (in_array('delivery_type', $this->visibleColumns))
            <div x-show="open === 'delivery_type'" x-cloak>
                <button @click="$wire.setFilter('delivery_type', null)"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ !$this->filters['delivery_type'] ? 'bg-surface-secondary font-medium' : '' }}">
                    —
                </button>
                <button @click="$wire.setFilter('delivery_type', 'home')"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['delivery_type'] === 'home' ? 'bg-surface-secondary font-medium' : '' }}">
                    {{ __('merchant_panel.home_delivery_label') }}
                </button>
                <button @click="$wire.setFilter('delivery_type', 'stopdesk')"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['delivery_type'] === 'stopdesk' ? 'bg-surface-secondary font-medium' : '' }}">
                    {{ __('merchant_panel.stop_desk_label') }}
                </button>
            </div>
        @endif

        {{-- Shipment Type --}}
        @if (in_array('shipment_type', $this->visibleColumns))
            <div x-show="open === 'shipment_type'" x-cloak>
                <button @click="$wire.setFilter('shipment_type', null)"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ !$this->filters['shipment_type'] ? 'bg-surface-secondary font-medium' : '' }}">
                    —
                </button>
                <button @click="$wire.setFilter('shipment_type', 'delivery')"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['shipment_type'] === 'delivery' ? 'bg-surface-secondary font-medium' : '' }}">
                    {{ __('merchant_panel.delivery') }}
                </button>
                <button @click="$wire.setFilter('shipment_type', 'exchange')"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['shipment_type'] === 'exchange' ? 'bg-surface-secondary font-medium' : '' }}">
                    {{ __('merchant_panel.exchange_label') }}
                </button>
                <button @click="$wire.setFilter('shipment_type', 'pickup')"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['shipment_type'] === 'pickup' ? 'bg-surface-secondary font-medium' : '' }}">
                    {{ __('merchant_panel.pickup_label') }}
                </button>
            </div>
        @endif

        {{-- Shipping Provider --}}
        @if (in_array('shipping_provider', $this->visibleColumns))
            <div x-show="open === 'shipping_provider'" x-cloak>
                <button @click="$wire.setFilter('shipping_provider', null); $wire.setFilter('stopdesk_point', null)"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ !$this->filters['shipping_provider'] ? 'bg-surface-secondary font-medium' : '' }}">
                    —
                </button>
                @foreach ($this->allProviders as $pr)
                    <button
                        @click="$wire.setFilter('shipping_provider', '{{ $pr['id'] }}'); $wire.setFilter('stopdesk_point', null); $wire.loadFilterStopdeskPoints('{{ $pr['id'] }}')"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['shipping_provider'] == $pr['id'] ? 'bg-surface-secondary font-medium' : '' }}"
                        data-name="{{ $pr['name'] }}">
                        {{ $pr['name'] }}
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Stopdesk Point (cascades from shipping_provider) --}}
        @if (in_array('stopdesk_point', $this->visibleColumns))
            <div x-show="open === 'stopdesk_point'" x-cloak>
                @if (!filled($this->filters['shipping_provider']))
                    <div class="px-2.5 py-1.5 rounded-lg text-xs text-ink-muted">
                        {{ __('merchant_panel.select_provider_first') }}</div>
                @else
                    <button @click="$wire.setFilter('stopdesk_point', null)"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ !$this->filters['stopdesk_point'] ? 'bg-surface-secondary font-medium' : '' }}">
                        —
                    </button>
                    @foreach ($this->allStopdeskPoints as $dp)
                        <button @click="$wire.setFilter('stopdesk_point', '{{ $dp['id'] }}')"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['stopdesk_point'] == $dp['id'] ? 'bg-surface-secondary font-medium' : '' }}"
                            data-name="{{ $dp['name'] }}">
                            {{ $dp['name'] }}
                        </button>
                    @endforeach
                @endif
            </div>
        @endif

        {{-- City (cascades from wilaya) --}}
        @if (in_array('city', $this->visibleColumns))
            <div x-show="open === 'city'" x-cloak>
                @if (!filled($this->filters['wilaya']))
                    <div class="px-2.5 py-1.5 rounded-lg text-xs text-ink-muted">
                        {{ __('merchant_panel.select_state_first') }}</div>
                @else
                    <button @click="$wire.setFilter('city', null)"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ !$this->filters['city'] ? 'bg-surface-secondary font-medium' : '' }}">
                        —
                    </button>
                    @foreach ($this->allCities as $ct)
                        <button @click="$wire.setFilter('city', '{{ $ct['id'] }}')"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['city'] == $ct['id'] ? 'bg-surface-secondary font-medium' : '' }}"
                            data-name="{{ $ct['name'] }}">
                            {{ $ct['name'] }}
                        </button>
                    @endforeach
                @endif
            </div>
        @endif

        {{-- Confirmed By --}}
        @if (in_array('confirmed_by', $this->visibleColumns))
            <div x-show="open === 'confirmed_by'" x-cloak>
                <button @click="$wire.setFilter('confirmed_by', null)"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ !$this->filters['confirmed_by'] ? 'bg-surface-secondary font-medium' : '' }}">
                    —
                </button>
                @foreach ($this->allMembers as $m)
                    <button @click="$wire.setFilter('confirmed_by', '{{ $m['id'] }}')"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['confirmed_by'] == $m['id'] ? 'bg-surface-secondary font-medium' : '' }}"
                        data-name="{{ $m['user']['name'] }}">
                        {{ $m['user']['name'] }}
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Address --}}
        @if (in_array('address', $this->visibleColumns))
            <div x-show="open === 'address'" x-cloak>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.600ms="filters.address"
                        class="edz-input text-xs w-full pe-6" placeholder="{{ __('merchant_panel.address') }}"
                        autocomplete="off">
                    @if ($this->filters['address'] !== '')
                        <button wire:click="$set('filters.address', '')" type="button"
                            class="absolute end-1 top-1/2 -translate-y-1/2 text-ink-muted hover:text-accent-500 transition"
                            aria-label="Clear address">
                            <x-edz.icon name="x-mark" class="w-3.5 h-3.5" />
                        </button>
                    @endif
                </div>
            </div>
        @endif

        {{-- Notes --}}
        @if (in_array('notes', $this->visibleColumns))
            <div x-show="open === 'notes'" x-cloak>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.600ms="filters.notes"
                        class="edz-input text-xs w-full pe-6" placeholder="{{ __('merchant_panel.notes') }}"
                        autocomplete="off">
                    @if ($this->filters['notes'] !== '')
                        <button wire:click="$set('filters.notes', '')" type="button"
                            class="absolute end-1 top-1/2 -translate-y-1/2 text-ink-muted hover:text-accent-500 transition"
                            aria-label="Clear notes">
                            <x-edz.icon name="x-mark" class="w-3.5 h-3.5" />
                        </button>
                    @endif
                </div>
            </div>
        @endif

        {{-- Weight (min/max range) --}}
        @if (in_array('weight', $this->visibleColumns))
            <div x-show="open === 'weight'" x-cloak>
                <div class="flex items-center gap-1">
                    <div class="relative flex-1">
                        <input type="number" wire:model.live.debounce.600ms="filters.weight_min"
                            placeholder="Min" class="edz-input text-xs w-full pe-6" step="0.01">
                        @if ($this->filters['weight_min'] !== null && $this->filters['weight_min'] !== '')
                            <button wire:click="$set('filters.weight_min', '')" type="button"
                                class="absolute end-1 top-1/2 -translate-y-1/2 text-ink-muted hover:text-accent-500 transition"
                                aria-label="Clear min weight">
                                <x-edz.icon name="x-mark" class="w-3.5 h-3.5" />
                            </button>
                        @endif
                    </div>
                    <div class="relative flex-1">
                        <input type="number" wire:model.live.debounce.600ms="filters.weight_max"
                            placeholder="Max" class="edz-input text-xs w-full pe-6" step="0.01">
                        @if ($this->filters['weight_max'] !== null && $this->filters['weight_max'] !== '')
                            <button wire:click="$set('filters.weight_max', '')" type="button"
                                class="absolute end-1 top-1/2 -translate-y-1/2 text-ink-muted hover:text-accent-500 transition"
                                aria-label="Clear max weight">
                                <x-edz.icon name="x-mark" class="w-3.5 h-3.5" />
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Send from carrier warehouse (tri-state) --}}
        @if (in_array('send_from_carrier_warehouse', $this->visibleColumns))
            <div x-show="open === 'send_from_carrier_warehouse'" x-cloak>
                <button @click="$wire.setFilter('send_from_carrier_warehouse', null)"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['send_from_carrier_warehouse'] === null ? 'bg-surface-secondary font-medium' : '' }}">
                    {{ __('general.all') }}
                </button>
                <button @click="$wire.setFilter('send_from_carrier_warehouse', true)"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['send_from_carrier_warehouse'] === true ? 'bg-surface-secondary font-medium' : '' }}">
                    {{ __('buttons.yes') }}
                </button>
                <button @click="$wire.setFilter('send_from_carrier_warehouse', false)"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary {{ $this->filters['send_from_carrier_warehouse'] === false ? 'bg-surface-secondary font-medium' : '' }}">
                    {{ __('buttons.no') }}
                </button>
            </div>
        @endif
    </div>