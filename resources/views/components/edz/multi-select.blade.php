@props([
    'options' => [],
    'optionValue' => 'value',
    'optionLabel' => 'label',
    'optionHint' => null,
    'optionCode' => null,
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
    'selected' => [],
    'maxVisibleChips' => 3,
])

@php
    $uid = 'edz-multi-select-' . \Illuminate\Support\Str::random(8);

    $jsOptions = collect($options)
        ->map(function ($item, $key) use ($optionValue, $optionLabel, $optionHint, $optionCode) {
            if (is_array($item) || is_object($item)) {
                $value = data_get($item, $optionValue);
                $label = data_get($item, $optionLabel);
                $hint = $optionHint ? data_get($item, $optionHint) : null;
                $code = $optionCode ? data_get($item, $optionCode) : null;
                return [
                    'value' => (string) ($value ?? $key),
                    'label' => (string) ($label ?? $value ?? $key),
                    'hint' => $hint !== null && $hint !== '' ? (string) $hint : null,
                    'code' => $code !== null && $code !== '' ? (string) $code : null,
                ];
            }
            return [
                'value' => (string) $key,
                'label' => (string) $item,
                'hint' => null,
                'code' => null,
            ];
        })
        ->values()
        ->all();

    $selectedValues = array_map('strval', array_values($selected));

    $sizeClass = match ($size) {
        'sm' => 'edz-select--sm',
        'lg' => 'edz-select--lg',
        default => '',
    };

    $errorClass = $error ? 'edz-select--error' : '';

    // Bound Livewire model path (options.0.values, ...). wire:model.* variants
    // (.live/.defer/...) must be matched before the plain wire:model so a
    // modifier twin never shadows the base binding.
    $modelName = $attributes->whereStartsWith('wire:model.')->first()
        ?: $attributes->whereStartsWith('wire:model')->first();
    $hasWireChange = (bool) $attributes->get('wire:change');
@endphp

<div {{ $attributes->merge(['class' => "edz-select edz-multi-select $sizeClass $errorClass $class"])->whereDoesntStartWith('wire:model')->whereDoesntStartWith('wire:change')->whereDoesntStartWith('x-model') }}
    x-data="edzMultiSelect({
        searchable: @js((bool) $search),
        searchMinChars: @js($searchMinChars),
        modelName: @js($modelName),
        roundtrip: @js($hasWireChange),
        maxChips: @js($maxVisibleChips)
    })" x-init="init()">

    {{-- Hidden select: the Livewire contract for array bindings --}}
    <select @if ($name) name="{{ $name }}" @endif
        multiple hidden
        x-ref="hiddenInput"
        {{ $attributes->whereStartsWith('wire:model') }}
        {{ $attributes->whereStartsWith('wire:change') }}>
        @foreach ($jsOptions as $opt)
            <option value="{{ $opt['value'] }}"
                @if ($opt['hint'] !== null) data-hint="{{ $opt['hint'] }}" @endif
                @if ($opt['code'] !== null) data-code="{{ $opt['code'] }}" @endif
                @selected(in_array($opt['value'], $selectedValues, true))>
                {{ $opt['label'] }}
            </option>
        @endforeach
    </select>

    {{-- Trigger --}}
    <button type="button" x-ref="trigger" class="edz-select__trigger edz-multi-select__trigger"
        :class="{ 'edz-select__trigger--disabled': @js($disabled), 'edz-select__trigger--open': open }"
        :disabled="@js($disabled)"
        @if ($disabled) disabled @endif
        role="combobox" aria-haspopup="listbox"
        :aria-expanded="open.toString()" aria-controls="{{ $uid }}-listbox"
        @click.prevent="toggle()"
        @keydown.arrow-down.prevent="moveHighlight(1)"
        @keydown.arrow-up.prevent="moveHighlight(-1)"
        @keydown.enter.prevent="open ? selectHighlighted() : toggle()"
        @keydown.escape="close()">

        @if ($icon)
            <x-edz.icon :name="$icon" class="edz-select__icon w-4 h-4" />
        @endif

        <span class="edz-select__text edz-multi-select__text">
            <template x-if="selectedOptions.length > 0">
                <span class="edz-multi-select__chips">
                    <template x-for="opt in visibleChips" :key="opt.value">
                        <span class="edz-multi-select__chip" @click.stop="removeValue(opt.value)"
                            :title="opt.label">
                            <span class="edz-multi-select__chip-label" x-text="opt.label"></span>
                            <span class="edz-multi-select__chip-remove">
                                <x-edz.icon name="x-mark" class="w-3 h-3" />
                            </span>
                        </span>
                    </template>
                    <template x-if="moreCount > 0">
                        <span class="edz-multi-select__chip edz-multi-select__chip--more">
                            <span x-text="'+' + moreCount"></span>
                        </span>
                    </template>
                </span>
            </template>
            <template x-if="selectedOptions.length === 0">
                <span class="edz-select__placeholder">{{ $placeholder }}</span>
            </template>
        </span>

        <span class="edz-select__chevron-wrap" :class="{ 'rotate-180': open }">
            <x-edz.icon name="chevron-down" class="edz-select__chevron w-4 h-4" />
        </span>
    </button>

    {{-- Panel --}}
    <div x-show="open" x-cloak
        x-transition:enter="edz-select-enter"
        x-transition:enter-start="edz-select-enter-start"
        x-transition:enter-end="edz-select-enter-end"
        x-transition:leave="edz-select-leave"
        x-transition:leave-start="edz-select-leave-start"
        x-transition:leave-end="edz-select-leave-end"
        :style="panelStyle"
        class="edz-select__panel edz-multi-select__panel">

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

        <ul id="{{ $uid }}-listbox" role="listbox" aria-multiselectable="true" class="edz-select__list">
            <template x-for="(opt, idx) in filteredOptions" :key="opt.value">
                <li role="option" class="edz-select__option edz-multi-select__option"
                    :class="{ 'edz-select__option--highlighted': highlighted === idx }"
                    :aria-selected="isSelected(opt.value).toString()"
                    @click.prevent="toggleValue(opt.value)"
                    @mouseenter="highlighted = idx">
                    <span class="edz-multi-select__check"
                        :class="{ 'edz-multi-select__check--on': isSelected(opt.value) }">
                        <template x-if="isSelected(opt.value)">
                            <x-edz.icon name="check" class="w-3 h-3" />
                        </template>
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
                    </span>
                </li>
            </template>

            <li x-show="filteredOptions.length === 0" class="edz-select__empty">
                {{ __('merchant_panel.no_options_found') }}
            </li>
        </ul>

        <div class="edz-multi-select__footer">
            <span class="edz-multi-select__count" x-text="selectedOptions.length"></span>
            <button type="button" class="edz-btn edz-btn--primary edz-btn--sm" @click="close()">
                {{ __('buttons.done') }}
            </button>
        </div>
    </div>

    @if ($error)
        <span class="edz-select__error">{{ $error }}</span>
    @endif
</div>