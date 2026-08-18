@props([
    'title' => null,
    'description' => null,
])

<x-layouts.panel :title="$title" :description="$description" context="merchant" sidebar="merchant">
    {{ $slot }}
</x-layouts.panel>
