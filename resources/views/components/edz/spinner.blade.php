@props(['size' => 'w-4 h-4', 'show' => null])

@php
    $attrs = $show !== null
        ? $attributes->merge(['class' => 'edz-spinner ' . $size, 'x-show' => $show, 'x-cloak' => ''])
        : $attributes->merge(['class' => 'edz-spinner ' . $size, 'x-cloak' => '', 'wire:loading' => '']);
@endphp

<svg {{ $attrs }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">
    <circle cx="12" cy="12" r="9" stroke-dasharray="42.41 56.55" />
</svg>