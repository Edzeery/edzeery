@props([
    'options' => [],
    'optionValue' => 'value',
    'optionLabel' => 'label',
    'optionHint' => null,
    'optionCode' => null,
    'optionExtra' => null,
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
    'lazy' => false,
    'source' => null,
    'scope' => null,
])

@php
    $uid = 'edz-select-' . \Illuminate\Support\Str::random(8);

    $jsOptions = collect($options)
        ->map(function ($item, $key) use ($optionValue, $optionLabel, $optionHint, $optionCode, $optionExtra) {
            if (is_array($item) || is_object($item)) {
                $value = data_get($item, $optionValue);
                $label = data_get($item, $optionLabel);
                $hint = $optionHint ? data_get($item, $optionHint) : null;
                $code = $optionCode ? data_get($item, $optionCode) : null;
                $extra = $optionExtra
                    ? collect(data_get($item, $optionExtra, []))
                        ->filter(fn($v) => filled($v))
                        ->values()
                    : collect();
                return [
                    'value' => (string) ($value ?? $key),
                    'label' => (string) ($label ?? $value ?? $key),
                    'hint' => $hint !== null ? (string) $hint : null,
                    'code' => $code !== null && $code !== '' ? (string) $code : null,
                    'extra' => $extra->map(fn($v) => (string) $v)->all(),
                ];
            }
            return [
                'value' => (string) $key,
                'label' => (string) $item,
                'hint' => null,
                'code' => null,
                'extra' => [],
            ];
        })
        ->values()
        ->all();

    // Livewire morphs the trigger/opens in place; Alpine reads this attribute
    // whenever it changes so the option list stays in sync with the server.
    $optionsAttr = htmlspecialchars(json_encode($jsOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');

    $sizeClass = match ($size) {
        'sm' => 'edz-select--sm',
        'lg' => 'edz-select--lg',
        default => '',
    };

    $errorClass = $error ? 'edz-select--error' : '';
    $searchable = $search;
    $hasBackendSearch = $attributes->has('wire:search');

    // A round-trip happens for every pick when the select is lazy-loaded or
    // bound with a live/change modifier: gate + spinner until the server
    // acknowledges, so a rapid second tap never fires a racing request.
    $relativeAttrs = $attributes->getAttributes();
    $isLiveModel = collect(array_keys($relativeAttrs))
        ->map(fn ($k) => is_string($k) ? $k : '')
        ->first(fn ($k) => str_starts_with($k, 'wire:model') && str_contains($k, '.live')) !== null;
    $roundtrip = (bool) $lazy || $isLiveModel || $attributes->has('wire:change');

    // Bound Livewire model path (form.shipping_provider_id, product_id, ...).
    // wire:model.* variants (.live/.defer/...) must be matched before the
    // plain wire:model so a modifier twin never shadows the base binding.
    $modelName = $attributes->whereStartsWith('wire:model.')->first()
        ?: $attributes->whereStartsWith('wire:model')->first();
@endphp

<div {{ $attributes->merge(['class' => "edz-select $sizeClass $errorClass $class"])->whereDoesntStartWith('wire:model')->whereDoesntStartWith('x-model')->whereDoesntStartWith('wire:search') }}
    data-options="{{ $optionsAttr }}"
    @if ($lazy) data-lazy="1" @endif
    data-source="{{ $source }}" data-scope="{{ $scope }}"
    @if ($roundtrip) data-roundtrip="1" @endif
    x-data="edzSelect({
        options: @js($jsOptions),
        searchable: @js($searchable),
        hasBackendSearch: @js($hasBackendSearch),
        searchMinChars: @js($searchMinChars),
        wireMethodName: @js($attributes->get('wire:search')),
        modelName: @js($modelName),
        lazy: @js((bool) $lazy),
        roundtrip: @js($roundtrip),
        remoteSource: @js($source),
        remoteScope: @js($scope),
        loadingLabel: @js(__('messages.loading'))
    })" x-init="init()">

    <input type="hidden" @if ($name) name="{{ $name }}" @endif
        x-model="selected" x-ref="hiddenInput" {{ $attributes->whereStartsWith('wire:model') }}>

    <button type="button" x-ref="trigger" class="edz-select__trigger"
        :class="{ 'edz-select__trigger--disabled': loading || @js($disabled), 'edz-select__trigger--open': open }"
        :disabled="loading || @js($disabled)"
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
                <span class="edz-select__text-inner">
                    <template x-if="currentCode !== null">
                        <span class="edz-code-badge" x-text="currentCode"></span>
                    </template>
                    <span class="edz-select__text-label">
                        <span x-text="currentLabel"></span>
                        <template x-if="currentHint !== null">
                            <span class="edz-select__hint" x-text="' \u2014 ' + currentHint"></span>
                        </template>
                    </span>
                </span>
            </template>
            <template x-if="currentLabel === null">
                <span class="edz-select__placeholder">{{ $placeholder }}</span>
            </template>
        </span>

        <span class="edz-select__chevron-wrap" :class="{ 'rotate-180': open }">
            <template x-if="loading">
                <svg class="edz-spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                </svg>
            </template>
            <template x-if="!loading">
                <x-edz.icon name="chevron-down" class="edz-select__chevron w-4 h-4" />
            </template>
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
                        <span class="edz-select__option-line">
                            <template x-if="opt.code">
                                <span class="edz-code-badge" x-text="opt.code"></span>
                            </template>
                            <span class="edz-select__option-label" x-text="opt.label"></span>
                        </span>
                        <template x-if="opt.hint">
                            <span class="edz-select__option-hint" x-text="opt.hint"></span>
                        </template>
                        <template x-for="(ln, i) in opt.extra" :key="i">
                            <span class="edz-select__option-hint" x-text="ln"></span>
                        </template>
                    </span>
                </li>
            </template>

            <li x-show="loading" class="edz-select__loading">
                <svg class="edz-spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                </svg>
                <span x-text="labelForType"></span>
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


