@props([
    'id' => null,
    'label' => null,
    'hint' => null,
    'size' => 'md',
    'checked' => null,
    'value' => null,
    'disabled' => false,
    'labelClass' => '',
])

@php
    $uid = $id ?? 'edz-checkbox-' . \Illuminate\Support\Str::random(8);
    $sizeClass = $size === 'sm' ? 'w-4 h-4' : 'w-[18px] h-[18px]';
    $extraAttrs = $attributes->merge(['class' => "edz-checkbox {$sizeClass}"]);
@endphp

@if ($label || $hint)
    <label for="{{ $uid }}"
        class="inline-flex items-center gap-3 cursor-pointer select-none {{ $disabled ? 'opacity-60 cursor-not-allowed' : '' }} {{ $labelClass }}">
        <input type="checkbox"
            id="{{ $uid }}"
            class="{{ $extraAttrs->get('class') }}"
            {{ $checked === true ? 'checked' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            @if ($value !== null) value="{{ $value }}" @endif
            {{ $extraAttrs->whereDoesntStartWith('wire:')->except(['class', 'id']) }}
            {{ $attributes->wire('model') }}
            {{ $attributes->wire('click') }} />
        <span class="min-w-0">
            @if ($label)
                <span class="block text-sm font-medium text-ink">{{ $label }}</span>
            @endif
            @if ($hint)
                <span class="block text-xs text-ink-muted mt-0.5">{{ $hint }}</span>
            @endif
        </span>
    </label>
@else
    <input type="checkbox"
        id="{{ $uid }}"
        class="{{ $extraAttrs->get('class') }}"
        {{ $checked === true ? 'checked' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        @if ($value !== null) value="{{ $value }}" @endif
        {{ $extraAttrs->whereDoesntStartWith('wire:')->except(['class', 'id']) }}
        {{ $attributes->wire('model') }}
        {{ $attributes->wire('click') }} />
@endif
