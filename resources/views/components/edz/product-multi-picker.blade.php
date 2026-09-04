@props([
    'options' => [],
    'selected' => [],
    'selectedNames' => [],
    'toggle' => '',
    'model' => 'listProductSearch',
    'placeholder' => '',
    'emptyMessage' => null,
    'size' => 'md',
    'class' => '',
])

@php
    $uid = 'edz-product-multi-' . \Illuminate\Support\Str::random(8);
    $sizeClass = match ($size) {
        'sm' => 'edz-select--sm',
        'lg' => 'edz-select--lg',
        default => '',
    };
    $count = count($selected);
@endphp

<div {{ $attributes->only(['class']) }} x-data="{ open: false }" @click.outside="open = false"
    class="edz-select {{ $sizeClass }} {{ $class }}">

    {{-- Trigger --}}
    <button type="button" x-ref="trigger" class="edz-select__trigger" role="combobox"
        aria-haspopup="listbox" :aria-expanded="open.toString()" aria-controls="{{ $uid }}-listbox"
        @click.prevent="open = !open" @keydown.escape="open = false">
        <x-edz.icon name="cube" class="edz-select__icon w-4 h-4" />
        <span class="edz-select__text">
            @if ($count > 0)
                <span>{{ $count }} {{ __('merchant_panel.products_selected') }}</span>
            @else
                <span class="edz-select__placeholder">{{ $placeholder }}</span>
            @endif
        </span>
        <span class="edz-select__chevron-wrap" :class="{ 'rotate-180': open }">
            <x-edz.icon name="chevron-down" class="edz-select__chevron w-4 h-4" />
        </span>
    </button>

    {{-- Panel --}}
    <div x-show="open" x-cloak
        x-transition:enter="edz-select-enter" x-transition:enter-start="edz-select-enter-start"
        x-transition:enter-end="edz-select-enter-end" x-transition:leave="edz-select-leave"
        x-transition:leave-start="edz-select-leave-start" x-transition:leave-end="edz-select-leave-end"
        class="edz-select__panel">

        {{-- Search --}}
        <div class="edz-select__search">
            <x-edz.icon name="magnifying-glass" class="edz-select__search-icon w-4 h-4" />
            <input type="text" wire:model.live.debounce.300ms="{{ $model }}"
                class="edz-select__search-input" x-ref="searchInput"
                placeholder="{{ $placeholder ?: __('merchant_panel.search_products') }}">
            <button type="button" x-show="!open" x-cloak class="edz-select__search-clear" aria-label="Clear">
                <x-edz.icon name="x-mark" class="w-4 h-4" />
            </button>
        </div>

        {{-- Options --}}
        <ul id="{{ $uid }}-listbox" role="listbox" class="edz-select__list">
            @forelse ($options as $opt)
                <li role="option" class="edz-select__option" :aria-selected="false">
                    <button type="button" wire:click="{{ $toggle }}('{{ $opt['id'] }}')"
                        class="w-full text-start flex items-center gap-2.5 min-w-0">
                        @if (! empty($opt['image_url']))
                            <span class="edz-product-select__thumb">
                                <img src="{{ $opt['image_url'] }}" alt="" class="w-full h-full object-cover" loading="lazy">
                            </span>
                        @else
                            <span class="edz-product-select__thumb">
                                <x-edz.icon name="cube" class="w-4 h-4 text-ink-muted" />
                            </span>
                        @endif
                        <span class="edz-select__option-content">
                            <span class="edz-select__option-label">{{ $opt['name'] }}</span>
                            @if (! empty($opt['sku']))
                                <span class="edz-select__option-hint">{{ $opt['sku'] }}</span>
                            @endif
                        </span>
                        <span class="w-4 h-4 rounded border flex items-center justify-center shrink-0
                            {{ in_array($opt['id'], $selected, true) ? 'bg-brand-500 border-brand-500' : 'border-neutral-border dark:border-dark-border' }}">
                            @if (in_array($opt['id'], $selected, true))
                                <x-edz.icon name="check" class="w-3 h-3 text-white" />
                            @endif
                        </span>
                    </button>
                </li>
            @empty
                <li class="edz-select__empty">
                    {{ $emptyMessage ?: __('merchant_panel.no_options_found') }}
                </li>
            @endforelse
        </ul>

        {{-- Selected chips --}}
        @if ($count > 0)
            <div class="edz-product-multi__chips">
                @foreach ($selectedNames as $pid => $pname)
                    <button type="button" wire:click="{{ $toggle }}('{{ $pid }}')"
                        class="edz-product-multi__chip" title="{{ $pname }}">
                        <span class="truncate max-w-[10rem]">{{ $pname }}</span>
                        <x-edz.icon name="x-mark" class="w-3 h-3 shrink-0" />
                    </button>
                @endforeach
            </div>
        @endif
    </div>
</div>