@props([
    'title' => null,
    'description' => null,
])

<x-layouts.panel :title="$title" :description="$description" context="account" sidebar="account">
    {{ $slot }}
</x-layouts.panel>
