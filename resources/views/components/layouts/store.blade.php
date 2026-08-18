@props([
    'title' => null,
    'description' => null,
])

<x-layouts.panel :title="$title" :description="$description" context="store" sidebar="merchant">
    {{ $slot }}
</x-layouts.panel>
