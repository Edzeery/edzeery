@props([
    'options' => [],
    'optionValue' => 'value',
    'optionLabel' => 'label',
    'optionHint' => null,
    'placeholder' => '—',
    'search' => false,
    'searchPlaceholder' => null,
    'searchMinChars' => 2,
    'size' => 'md',
    'disabled' => false,
    'error' => null,
    'icon' => null,
    'class' => '',
    'name' => null,
])

@php
    $uid = 'edz-select-' . \Illuminate\Support\Str::random(8);

    $jsOptions = collect($options)
        ->map(function ($item, $key) use ($optionValue, $optionLabel, $optionHint) {
            if (is_array($item)) {
                return [
                    'value' => data_get($item, $optionValue, $item),
                    'label' => data_get($item, $optionLabel, $item),
                    'hint' => $optionHint ? data_get($item, $optionHint, null) : null,
                ];
            }
            return [
                'value' => $key,
                'label' => $item,
                'hint' => null,
            ];
        })
        ->values()
        ->all();

    $sizeClass = match ($size) {
        'sm' => 'edz-select--sm',
        'lg' => 'edz-select--lg',
        default => '',
    };

    $errorClass = $error ? 'edz-select--error' : '';
    $searchable = $search;
    $hasBackendSearch = $attributes->has('wire:search');
@endphp

<div {{ $attributes->merge(['class' => "edz-select $sizeClass $errorClass $class"])->whereDoesntStartWith('wire:model')->whereDoesntStartWith('x-model')->whereDoesntStartWith('wire:search') }}
    x-data="edzSelect({
        options: @js($jsOptions),
        searchable: @js($searchable),
        hasBackendSearch: @js($hasBackendSearch),
        searchMinChars: @js($searchMinChars),
        wireMethodName: @js($attributes->get('wire:search')),
        initialValue: @js($attributes->get('wire:model', ''))
    })" x-init="init()">

    <input type="hidden" @if ($name) name="{{ $name }}" @endif
        x-model="selected" x-ref="hiddenInput" {{ $attributes->whereStartsWith('wire:model') }}>

    <button type="button" x-ref="trigger" class="edz-select__trigger"
        :class="{ 'edz-select__trigger--disabled': @js($disabled), 'edz-select__trigger--open': open }"
        @if ($disabled) disabled @endif role="combobox" aria-haspopup="listbox"
        :aria-expanded="open.toString()" aria-controls="{{ $uid }}-listbox"
        @click.prevent="toggle()"
        @keydown.arrow-down.prevent="moveHighlight(1)"
        @keydown.arrow-up.prevent="moveHighlight(-1)"
        @keydown.enter.prevent="open ? selectHighlighted() : toggle()"
        @keydown.escape="close()">
        @if ($icon)
            <x-edz.icon :name="$icon" class="edz-select__icon w-4 h-4" />
        @endif

        <span class="edz-select__text">
            <template x-if="currentLabel !== null">
                <span>
                    <span x-text="currentLabel"></span>
                    <template x-if="currentHint !== null">
                        <span class="edz-select__hint" x-text="' \u2014 ' + currentHint"></span>
                    </template>
                </span>
            </template>
            <template x-if="currentLabel === null">
                <span class="edz-select__placeholder">{{ $placeholder }}</span>
            </template>
        </span>

        <span class="edz-select__chevron-wrap" :class="{ 'rotate-180': open }">
            <x-edz.icon name="chevron-down" class="edz-select__chevron w-4 h-4" />
        </span>
    </button>

    <div x-show="open" x-cloak
        x-transition:enter="edz-select-enter"
        x-transition:enter-start="edz-select-enter-start"
        x-transition:enter-end="edz-select-enter-end"
        x-transition:leave="edz-select-leave"
        x-transition:leave-start="edz-select-leave-start"
        x-transition:leave-end="edz-select-leave-end"
        :style="panelStyle"
        class="edz-select__panel">

        @if ($search)
            <div class="edz-select__search">
                <x-edz.icon name="magnifying-glass" class="edz-select__search-icon w-4 h-4" />
                <input type="text" x-model="query" @input.debounce.200ms="onQueryChange()" @click.stop
                    class="edz-select__search-input"
                    placeholder="{{ $searchPlaceholder ?? __('merchant_panel.search') }}"
                    x-ref="searchInput"
                    @keydown.arrow-down.prevent="moveHighlight(1)"
                    @keydown.arrow-up.prevent="moveHighlight(-1)"
                    @keydown.enter.prevent="selectHighlighted()"
                    @keydown.escape="close()">
            </div>
        @endif

        <ul id="{{ $uid }}-listbox" role="listbox" class="edz-select__list">
            <template x-for="(opt, idx) in filteredOptions" :key="opt.id">
                <li role="option" class="edz-select__option"
                    :class="{
                        'edz-select__option--highlighted': highlighted === idx,
                        'edz-select__option--selected': opt.value === selected
                    }"
                    :aria-selected="(opt.value === selected).toString()"
                    @click="select(opt.value)"
                    @mouseenter="highlighted = idx">
                    <span class="edz-select__option-check" x-show="opt.value === selected">
                        <x-edz.icon name="check" class="w-3.5 h-3.5" />
                    </span>
                    <span class="edz-select__option-content">
                        <span class="edz-select__option-label" x-text="opt.label"></span>
                        <template x-if="opt.hint">
                            <span class="edz-select__option-hint" x-text="opt.hint"></span>
                        </template>
                    </span>
                </li>
            </template>

            <li x-show="loading" class="edz-select__loading">
                <svg class="edz-spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                </svg>
                <span x-text="'Searching...'"></span>
            </li>

            <li x-show="!loading && filteredOptions.length === 0" class="edz-select__empty">
                {{ __('merchant_panel.no_products_found') }}
            </li>
        </ul>
    </div>

    @if ($error)
        <span class="edz-select__error">{{ $error }}</span>
    @endif
</div>

<script>
    function edzSelect(config) {
        return {
            open: false,
            highlighted: -1,
            query: '',
            selected: null,
            _busy: false,
            openUpward: false,
            popupTop: 0,
            popupLeft: 0,
            popupWidth: 0,
            options: config.options || [],
            backendOptions: [],
            loading: false,
            searchTimeout: null,
            searchable: config.searchable || false,
            hasBackendSearch: config.hasBackendSearch || false,
            searchMinChars: config.searchMinChars || 2,
            wireMethodName: config.wireMethodName || null,

            get allOptions() {
                return [...this.options, ...this.backendOptions];
            },

            get filteredOptions() {
                if (!this.searchable || this.query.trim() === '') return this.allOptions;
                const q = this.query.toLowerCase();
                return this.allOptions.filter(o =>
                    o.label.toLowerCase().includes(q) ||
                    (o.hint && o.hint.toLowerCase().includes(q))
                );
            },

            get currentLabel() {
                const opt = this.allOptions.find(o => o.value === this.selected);
                return opt ? opt.label : null;
            },

            get currentHint() {
                const opt = this.allOptions.find(o => o.value === this.selected);
                return opt && opt.hint ? opt.hint : null;
            },

            get panelStyle() {
                if (!this.open) return 'display:none;';
                const isMobile = window.innerWidth < 640;
                if (isMobile) {
                    const w = Math.min(this.$refs.trigger?.offsetWidth || 300, window.innerWidth - 16);
                    return `position:fixed;bottom:0;left:50%;transform:translateX(-50%);width:${w}px;z-index:70;border-radius:var(--edz-radius-2xl) var(--edz-radius-2xl) 0 0;max-height:60vh;`;
                }
                const s = `position:fixed;z-index:70;width:${this.popupWidth}px;`;
                if (this.openUpward) {
                    return s + `bottom:${window.innerHeight - this.popupTop}px;left:${this.popupLeft}px;`;
                }
                return s + `top:${this.popupTop}px;left:${this.popupLeft}px;`;
            },

            init() {
                this.selected = this.$refs.hiddenInput?.value || null;
            },

            toggle() {
                if (this._busy) return;
                if (this.$el.querySelector('.edz-select__trigger')?.disabled) return;
                this._busy = true;
                setTimeout(() => { this._busy = false; }, 150);

                this.open = !this.open;
                if (this.open) {
                    this.query = '';
                    this.backendOptions = [];
                    this.highlighted = this.allOptions.findIndex(o => o.value === this.selected);
                    this.updatePosition();
                    this.$nextTick(() => {
                        if (this.searchable && this.$refs.searchInput) {
                            this.$refs.searchInput.focus();
                        }
                    });
                }
            },

            close() {
                this.open = false;
            },

            updatePosition() {
                const trigger = this.$refs.trigger;
                if (!trigger) return;
                const rect = trigger.getBoundingClientRect();
                const isMobile = window.innerWidth < 640;

                if (isMobile) return;

                this.popupWidth = rect.width;
                this.popupLeft = rect.left;
                const spaceBelow = window.innerHeight - rect.bottom;
                const spaceAbove = rect.top;

                if (spaceBelow < 280 && spaceAbove > spaceBelow) {
                    this.popupTop = rect.top - 4;
                    this.openUpward = true;
                } else {
                    this.popupTop = rect.bottom + 4;
                    this.openUpward = false;
                }

                if (this.popupLeft + this.popupWidth > window.innerWidth - 8) {
                    this.popupLeft = window.innerWidth - this.popupWidth - 8;
                }
                if (this.popupLeft < 8) {
                    this.popupLeft = 8;
                }
            },

            select(value) {
                if (this._busy) return;
                this._busy = true;
                setTimeout(() => { this._busy = false; }, 200);

                this.selected = value;
                this.open = false;
                this.$nextTick(() => {
                    const input = this.$refs.hiddenInput;
                    if (input) {
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                        input.dispatchEvent(new CustomEvent('livewire-change', { bubbles: true }));
                    }
                });
            },

            onQueryChange() {
                this.highlighted = this.filteredOptions.length > 0 ? 0 : -1;
                if (!this.hasBackendSearch || !this.wireMethodName) return;

                clearTimeout(this.searchTimeout);
                const q = this.query.trim();
                if (q.length < this.searchMinChars) {
                    this.backendOptions = [];
                    return;
                }
                const localResults = this.options.filter(o =>
                    o.label.toLowerCase().includes(q) ||
                    (o.hint && o.hint.toLowerCase().includes(q))
                );
                if (localResults.length > 0) {
                    this.backendOptions = [];
                    return;
                }
                this.loading = true;
                this.searchTimeout = setTimeout(() => {
                    this.$wire.call(this.wireMethodName, q)
                        .then(results => {
                            this.backendOptions = (results || []).map(r => ({
                                value: r.value ?? r.id ?? r,
                                label: r.label ?? r.name ?? String(r),
                                hint: r.hint ?? null,
                            }));
                            this.loading = false;
                            this.highlighted = this.filteredOptions.length > 0 ? 0 : -1;
                        })
                        .catch(() => { this.loading = false; });
                }, 300);
            },

            moveHighlight(delta) {
                if (!this.open) { this.toggle(); return; }
                const max = this.filteredOptions.length - 1;
                this.highlighted = Math.min(max, Math.max(0, this.highlighted + delta));
            },

            selectHighlighted() {
                const opt = this.filteredOptions[this.highlighted];
                if (opt) this.select(opt.value);
            }
        };
    }
</script>
