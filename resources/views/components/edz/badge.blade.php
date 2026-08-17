@props([
    'tone' => 'neutral',
    'dot' => false,
    'sm' => false,
    'lg' => false,
])

@php
    $classes = ['edz-badge', "edz-badge--{$tone}"];
    if ($dot) $classes[] = 'edz-badge--dot';
    if ($sm) $classes[] = 'edz-badge--sm';
    if ($lg) $classes[] = 'edz-badge--lg';
@endphp

<span {{ $attributes->merge(['class' => implode(' ', $classes)]) }}>
    {{ $slot }}
</span>
