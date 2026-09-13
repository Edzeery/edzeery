{{-- Tracking toolbar drill-down filter portal (products-style). A single "Filters"
    trigger dispatches `edz-toolbar-filter-open` which opens this two-level portal:
    root groups → options per group. Groups are dynamic — only those whose column
    is NOT visible in the grid appear (visible columns are filtered via their
    header filter icons in tracking-table-header). --}}

@php
    $forwardChevron = app()->getLocale() === 'ar' ? 'chevron-left' : 'chevron-right';
    $backChevron    = app()->getLocale() === 'ar' ? 'chevron-right' : 'chevron-left';
    $available      = $this->availableFilterGroups();
    $groupLabels    = [
        'provider'        => __('order_flow.tracking_provider'),
        'tracking_statuses' => __('order_flow.tracking_status'),
        'date'            => __('order_flow.filter_date'),
        'amount'          => __('order_flow.filter_amount'),
        'city'            => __('merchant_panel.city'),
        'rider'           => __('order_flow.rider_tab_title'),
        'assigned_to'     => __('merchant_panel.assigned_agent'),
        'confirmed_by'    => __('merchant_panel.confirmed_by'),
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

        {{-- ==================== ROOT — choose a filter group ==================== --}}
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

        {{-- ==================== PROVIDER (single) ==================== --}}
        @if (in_array('provider', $available))
            <div x-show="open === 'provider'" x-cloak class="edz-dropdown__section">
                <div class="mb-1 flex items-center gap-0.5 px-0.5">
                    <button type="button" @click="open = 'root'"
                        class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                        title="{{ __('general.back') }}" aria-label="{{ __('general.back') }}">
                        <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                    </button>
                    <p class="edz-dropdown__section-title m-0">{{ __('order_flow.tracking_provider') }}</p>
                </div>

                <button type="button" @click="$wire.setFilter('provider', null); close()"
                    aria-pressed="{{ empty($this->filters['provider']) ? 'true' : 'false' }}"
                    class="edz-dropdown__item justify-between {{ empty($this->filters['provider']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                    <span class="truncate">{{ __('general.all') }}</span>
                    <x-edz.icon name="check"
                        class="w-3.5 h-3.5 shrink-0 {{ empty($this->filters['provider']) ? 'opacity-100' : 'opacity-0' }}" />
                </button>

                @foreach ($this->allProviders as $pr)
                    <button type="button" @click="$wire.setFilter('provider', '{{ $pr['id'] }}'); close()"
                        aria-pressed="{{ ($this->filters['provider'] ?? null) === $pr['id'] ? 'true' : 'false' }}"
                        class="edz-dropdown__item justify-between {{ ($this->filters['provider'] ?? null) === $pr['id'] ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                        <span class="truncate">{{ $pr['name'] }}</span>
                        <x-edz.icon name="check"
                            class="w-3.5 h-3.5 shrink-0 {{ ($this->filters['provider'] ?? null) === $pr['id'] ? 'opacity-100' : 'opacity-0' }}" />
                    </button>
                @endforeach
            </div>
        @endif

        {{-- ==================== TRACKING STATUS (multi-toggle) ==================== --}}
        @if (in_array('tracking_statuses', $available))
            <div x-show="open === 'tracking_statuses'" x-cloak class="edz-dropdown__section">
                <div class="mb-1 flex items-center gap-0.5 px-0.5">
                    <button type="button" @click="open = 'root'"
                        class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                        title="{{ __('general.back') }}" aria-label="{{ __('general.back') }}">
                        <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                    </button>
                    <p class="edz-dropdown__section-title m-0">{{ __('order_flow.tracking_status') }}</p>
                </div>

                <button type="button" @click="$wire.setFilter('tracking_statuses', []); close()"
                    aria-pressed="{{ empty($this->filters['tracking_statuses']) ? 'true' : 'false' }}"
                    class="edz-dropdown__item justify-between {{ empty($this->filters['tracking_statuses']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                    <span class="truncate">{{ __('general.all') }}</span>
                    <x-edz.icon name="check"
                        class="w-3.5 h-3.5 shrink-0 {{ empty($this->filters['tracking_statuses']) ? 'opacity-100' : 'opacity-0' }}" />
                </button>

                @foreach (\App\Enums\Store\OrderTrackingStatus::cases() as $ts)
                    <button type="button" @click="$wire.toggleTrackingStatus('{{ $ts->value }}'); close()"
                        aria-pressed="{{ in_array($ts->value, $this->filters['tracking_statuses'] ?? [], true) ? 'true' : 'false' }}"
                        class="edz-dropdown__item justify-between {{ in_array($ts->value, $this->filters['tracking_statuses'] ?? [], true) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                        <span class="truncate">{{ $ts->label() }}</span>
                        <x-edz.icon name="check"
                            class="w-3.5 h-3.5 shrink-0 {{ in_array($ts->value, $this->filters['tracking_statuses'] ?? [], true) ? 'opacity-100' : 'opacity-0' }}" />
                    </button>
                @endforeach
            </div>
        @endif

        {{-- ==================== DATE (from/to) ==================== --}}
        @if (in_array('date', $available))
            <div x-show="open === 'date'" x-cloak class="edz-dropdown__section">
                <div class="mb-1 flex items-center gap-0.5 px-0.5">
                    <button type="button" @click="open = 'root'"
                        class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                        title="{{ __('general.back') }}" aria-label="{{ __('general.back') }}">
                        <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                    </button>
                    <p class="edz-dropdown__section-title m-0">{{ __('order_flow.filter_date') }}</p>
                </div>

                <div class="px-1 flex items-center gap-2">
                    <input type="text" wire:model.blur="filters.date_from"
                        class="edz-input text-sm flatpickr-input"
                        placeholder="{{ __('order_flow.filter_date') }} —" autocomplete="off">
                    <span class="text-ink-muted text-sm">—</span>
                    <input type="text" wire:model.blur="filters.date_to"
                        class="edz-input text-sm flatpickr-input"
                        placeholder="— {{ __('order_flow.filter_date') }}" autocomplete="off">
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
                    <p class="edz-dropdown__section-title m-0">{{ __('order_flow.filter_amount') }}</p>
                </div>

                <div class="px-1 flex items-center gap-2">
                    <input type="number" min="0" step="0.01"
                        wire:model.blur="filters.amount_min"
                        placeholder="{{ __('order_flow.amount_min_placeholder') }}"
                        class="edz-input text-sm">
                    <span class="text-ink-muted text-sm">—</span>
                    <input type="number" min="0" step="0.01"
                        wire:model.blur="filters.amount_max"
                        placeholder="{{ __('order_flow.amount_max_placeholder') }}"
                        class="edz-input text-sm">
                </div>
            </div>
        @endif

        {{-- ==================== CITY (single) ==================== --}}
        @if (in_array('city', $available))
            <div x-show="open === 'city'" x-cloak class="edz-dropdown__section">
                <div class="mb-1 flex items-center gap-0.5 px-0.5">
                    <button type="button" @click="open = 'root'"
                        class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                        title="{{ __('general.back') }}" aria-label="{{ __('general.back') }}">
                        <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                    </button>
                    <p class="edz-dropdown__section-title m-0">{{ __('merchant_panel.city') }}</p>
                </div>

                <button type="button" @click="$wire.setFilter('city', null); close()"
                    aria-pressed="{{ empty($this->filters['city']) ? 'true' : 'false' }}"
                    class="edz-dropdown__item justify-between {{ empty($this->filters['city']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                    <span class="truncate">{{ __('general.all') }}</span>
                    <x-edz.icon name="check"
                        class="w-3.5 h-3.5 shrink-0 {{ empty($this->filters['city']) ? 'opacity-100' : 'opacity-0' }}" />
                </button>

                @foreach ($this->allCities as $ct)
                    <button type="button" @click="$wire.setFilter('city', '{{ $ct['id'] }}'); close()"
                        aria-pressed="{{ ($this->filters['city'] ?? null) === $ct['id'] ? 'true' : 'false' }}"
                        class="edz-dropdown__item justify-between {{ ($this->filters['city'] ?? null) === $ct['id'] ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                        <span class="truncate">{{ $ct['name'] }}</span>
                        <x-edz.icon name="check"
                            class="w-3.5 h-3.5 shrink-0 {{ ($this->filters['city'] ?? null) === $ct['id'] ? 'opacity-100' : 'opacity-0' }}" />
                    </button>
                @endforeach
            </div>
        @endif

        {{-- ==================== RIDER (single — rider tab) ==================== --}}
        @if (in_array('rider', $available))
            @php $riderCounts = collect($this->riderRiders)->keyBy('id'); @endphp
            <div x-show="open === 'rider'" x-cloak class="edz-dropdown__section">
                <div class="mb-1 flex items-center gap-0.5 px-0.5">
                    <button type="button" @click="open = 'root'"
                        class="-m-1 p-1 rounded-md text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                        title="{{ __('general.back') }}" aria-label="{{ __('general.back') }}">
                        <x-edz.icon :name="$backChevron" class="w-3.5 h-3.5" />
                    </button>
                    <p class="edz-dropdown__section-title m-0">{{ __('order_flow.rider_tab_title') }}</p>
                </div>

                <button type="button" @click="$wire.setFilter('rider', null); close()"
                    aria-pressed="{{ empty($this->filters['rider']) ? 'true' : 'false' }}"
                    class="edz-dropdown__item justify-between {{ empty($this->filters['rider']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                    <span class="truncate">{{ __('general.all') }}</span>
                    <x-edz.icon name="check"
                        class="w-3.5 h-3.5 shrink-0 {{ empty($this->filters['rider']) ? 'opacity-100' : 'opacity-0' }}" />
                </button>

                @foreach ($this->allRiders as $rRider)
                    @php $riderTotal = (int) ($riderCounts->get($rRider['id'])['total'] ?? 0); @endphp
                    <button type="button" @click="$wire.setFilter('rider', '{{ $rRider['id'] }}'); close()"
                        aria-pressed="{{ ($this->filters['rider'] ?? null) === $rRider['id'] ? 'true' : 'false' }}"
                        class="edz-dropdown__item justify-between {{ ($this->filters['rider'] ?? null) === $rRider['id'] ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                        <span class="inline-flex items-center gap-1 truncate">
                            <x-edz.icon name="user" class="w-3 h-3 shrink-0 text-ink-muted" />
                            <span class="truncate">{{ $rRider['name'] }}</span>
                        </span>
                        @if ($riderTotal > 0)
                            <span class="text-[10px] tabular-nums text-ink-muted">{{ $riderTotal }}</span>
                        @endif
                        <x-edz.icon name="check"
                            class="w-3.5 h-3.5 shrink-0 {{ ($this->filters['rider'] ?? null) === $rRider['id'] ? 'opacity-100' : 'opacity-0' }}" />
                    </button>
                @endforeach
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
                    <p class="edz-dropdown__section-title m-0">{{ __('merchant_panel.assigned_agent') }}</p>
                </div>

                <button type="button" @click="$wire.setFilter('assigned_to', null); close()"
                    aria-pressed="{{ empty($this->filters['assigned_to']) ? 'true' : 'false' }}"
                    class="edz-dropdown__item justify-between {{ empty($this->filters['assigned_to']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                    <span class="truncate">{{ __('general.all') }}</span>
                    <x-edz.icon name="check"
                        class="w-3.5 h-3.5 shrink-0 {{ empty($this->filters['assigned_to']) ? 'opacity-100' : 'opacity-0' }}" />
                </button>

                @foreach ($this->allMembers as $mem)
                    <button type="button" @click="$wire.setFilter('assigned_to', '{{ $mem['id'] }}'); close()"
                        aria-pressed="{{ ($this->filters['assigned_to'] ?? null) === $mem['id'] ? 'true' : 'false' }}"
                        class="edz-dropdown__item justify-between {{ ($this->filters['assigned_to'] ?? null) === $mem['id'] ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                        <span class="inline-flex items-center gap-1 truncate">
                            <x-edz.icon name="user" class="w-3 h-3 shrink-0 text-ink-muted" />
                            <span class="truncate">{{ $mem['name'] }}</span>
                        </span>
                        <x-edz.icon name="check"
                            class="w-3.5 h-3.5 shrink-0 {{ ($this->filters['assigned_to'] ?? null) === $mem['id'] ? 'opacity-100' : 'opacity-0' }}" />
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
                    <p class="edz-dropdown__section-title m-0">{{ __('merchant_panel.confirmed_by') }}</p>
                </div>

                <button type="button" @click="$wire.setFilter('confirmed_by', null); close()"
                    aria-pressed="{{ empty($this->filters['confirmed_by']) ? 'true' : 'false' }}"
                    class="edz-dropdown__item justify-between {{ empty($this->filters['confirmed_by']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                    <span class="truncate">{{ __('general.all') }}</span>
                    <x-edz.icon name="check"
                        class="w-3.5 h-3.5 shrink-0 {{ empty($this->filters['confirmed_by']) ? 'opacity-100' : 'opacity-0' }}" />
                </button>

                @foreach ($this->allMembers as $mem)
                    <button type="button" @click="$wire.setFilter('confirmed_by', '{{ $mem['id'] }}'); close()"
                        aria-pressed="{{ ($this->filters['confirmed_by'] ?? null) === $mem['id'] ? 'true' : 'false' }}"
                        class="edz-dropdown__item justify-between {{ ($this->filters['confirmed_by'] ?? null) === $mem['id'] ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                        <span class="inline-flex items-center gap-1 truncate">
                            <x-edz.icon name="user" class="w-3 h-3 shrink-0 text-ink-muted" />
                            <span class="truncate">{{ $mem['name'] }}</span>
                        </span>
                        <x-edz.icon name="check"
                            class="w-3.5 h-3.5 shrink-0 {{ ($this->filters['confirmed_by'] ?? null) === $mem['id'] ? 'opacity-100' : 'opacity-0' }}" />
                    </button>
                @endforeach
            </div>
        @endif
    </div>
</div>
