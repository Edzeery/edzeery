@props([
    'title' => null,
    'description' => null,
])

<x-layouts.panel :title="$title" :description="$description" context="account" sidebar="account">
    @include('livewire.account.partials.tabs')
    {{ $slot }}
</x-layouts.panel>
