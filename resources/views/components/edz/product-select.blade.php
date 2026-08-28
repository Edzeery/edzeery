@props([
    'options' => [],
    'placeholder' => __('merchant_panel.filter_by_product'),
    'size' => 'md',
    'disabled' => false,
    'class' => '',
])

@php
    $uid = 'edz-product-select-' . \Illuminate\Support\Str::random(8);

    $jsOptions = collect($options)->map(function ($item, $key) {
        if (is_array($item)) {
            return [
                'id' => (string) ($item['id'] ?? $item['value'] ?? $key),
                'name' => $item['name'] ?? $item['label'] ?? '',
                'sku' => $item['sku'] ?? null,
                'image_url' => $item['image_url'] ?? null,
            ];
        }
        return [
            'id' => (string) $key,
            'name' => $item,
            'sku' => null,
            'image_url' => null,
        ];
    })->values()->all();

    $sizeClass = match ($size) {
        'sm' => 'edz-select--sm',
        'lg' => 'edz-select--lg',
        default => '',
    };

    // The selected product model path (e.g. filters.product_id).
    $modelName = $attributes->whereStartsWith('wire:model.')->first()
        ?: $attributes->whereStartsWith('wire:model')->first();
    // The optional free-text full-name model path (e.g. filters.product).
    $fullModelName = $attributes->whereStartsWith('wire:fullmodel')->first();

    // Remove wire:model / wire:fullmodel from the rendered element (binding is done via $wire.set()).
    $renderAttributes = $attributes
        ->filter(fn ($value, $key) => ! str_starts_with($key, 'wire:model') && ! str_starts_with($key, 'wire:fullmodel'));
@endphp

<div {{ $renderAttributes->merge(['class' => "edz-select $sizeClass $class"]) }}
    x-data="productSelect({
        options: @js($jsOptions),
        placeholder: @js($placeholder),
        modelName: @js($modelName),
        fullModelName: @js($fullModelName)
    })" @click.outside="close()">

    {{-- Trigger --}}
    <button type="button" x-ref="trigger" class="edz-select__trigger"
        :class="{ 'edz-select__trigger--disabled': @js($disabled), 'edz-select__trigger--open': open }"
        @if ($disabled) disabled @endif role="combobox" aria-haspopup="listbox"
        :aria-expanded="open.toString()" aria-controls="{{ $uid }}-listbox"
        @click.prevent="toggle()"
        @keydown.escape="close()">
        <x-edz.icon name="cube" class="edz-select__icon w-4 h-4" />

        <span class="edz-select__text">
            <template x-if="currentLabel !== null">
                <span>
                    <span x-text="currentLabel"></span>
                    <template x-if="currentSku">
                        <span class="edz-select__hint" x-text="' \u2014 ' + currentSku"></span>
                    </template>
                </span>
            </template>
            <template x-if="currentLabel === null">
                <span class="edz-select__placeholder" x-text="placeholder"></span>
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
        class="edz-select__panel">

        {{-- Search --}}
        <div class="edz-select__search">
            <x-edz.icon name="magnifying-glass" class="edz-select__search-icon w-4 h-4" />
            <input type="text" x-model="query" @input="highlighted = -1" @click.stop
                class="edz-select__search-input" x-ref="searchInput"
                placeholder="{{ __('merchant_panel.search_products') }}"
                @keydown.arrow-down.prevent="moveHighlight(1)"
                @keydown.arrow-up.prevent="moveHighlight(-1)"
                @keydown.enter.prevent="selectHighlighted()"
                @keydown.escape="close()">
            <button type="button" x-show="query !== ''" x-cloak @click="query = ''" class="edz-select__search-clear"
                aria-label="Clear" @click.stop>
                <x-edz.icon name="x-mark" class="w-4 h-4" />
            </button>
        </div>

        {{-- List --}}
        <ul id="{{ $uid }}-listbox" role="listbox" class="edz-select__list">
            <template x-for="(opt, idx) in filteredOptions" :key="opt.id">
                <li role="option" class="edz-select__option"
                    :class="{
                        'edz-select__option--highlighted': highlighted === idx,
                        'edz-select__option--selected': String(opt.id) === String(selected)
                    }"
                    :aria-selected="(String(opt.id) === String(selected)).toString()"
                    @click="select(opt.id)"
                    @mouseenter="highlighted = idx">
                    <span class="edz-product-select__thumb" x-show="opt.image_url">
                        <img :src="opt.image_url" alt="" class="w-full h-full object-cover" loading="lazy">
                    </span>
                    <span class="edz-select__option-content">
                        <span class="edz-select__option-label" x-text="opt.name"></span>
                        <template x-if="opt.sku">
                            <span class="edz-select__option-hint" x-text="opt.sku"></span>
                        </template>
                    </span>
                    <span class="edz-select__option-check" x-show="String(opt.id) === String(selected)">
                        <x-edz.icon name="check" class="w-3.5 h-3.5" />
                    </span>
                </li>
            </template>

            <li x-show="filteredOptions.length === 0" class="edz-select__empty">
                {{ __('merchant_panel.no_options_found') }}
            </li>
        </ul>

        {{-- Actions --}}
        <div class="edz-product-select__actions" x-show="hasNameFilter">
            <button type="button" @click="applyNameFilter()"
                class="edz-product-select__action">
                <x-edz.icon name="funnel" class="w-4 h-4" />
                <span>{{ __('merchant_panel.filter_by_full_name') }}: "<span x-text="query"></span>"</span>
            </button>
        </div>

        <div class="edz-product-select__actions" x-show="selected !== null || query !== ''">
            <button type="button" @click="clear()" class="edz-product-select__action edz-product-select__action--clear">
                <x-edz.icon name="x-mark" class="w-4 h-4" />
                <span>{{ __('merchant_panel.clear_filter') }}</span>
            </button>
        </div>
    </div>
</div>
