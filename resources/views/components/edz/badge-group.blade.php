@props([])

<div {{ $attributes->merge(['class' => 'edz-badge-group']) }}>
    {{ $slot }}
</div>
