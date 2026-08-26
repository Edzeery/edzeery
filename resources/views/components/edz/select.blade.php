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
                    'value' => (string) data_get($item, $optionValue, $item),
                    'label' => data_get($item, $optionLabel, $item),
                    'hint' => $optionHint ? data_get($item, $optionHint, null) : null,
                ];
            }
            return [
                'value' => (string) $key,
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
            <template x-for="(opt, idx) in filteredOptions" :key="opt.value">
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
                {{ __('merchant_panel.no_options_found') }}
            </li>
        </ul>
    </div>

    @if ($error)
        <span class="edz-select__error">{{ $error }}</span>
    @endif
</div>


