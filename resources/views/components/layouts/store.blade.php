@props([
    'title' => null,
    'description' => null,
])

<x-layouts.panel :title="$title" :description="$description" context="store" sidebar="store">
    {{ $slot }}
</x-layouts.panel>
