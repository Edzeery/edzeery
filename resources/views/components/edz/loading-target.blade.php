@props(['action' => null, 'label' => null])

@php
    if ($action) {
        \App\Support\Loading\LoadingActions::register($action, $label);
    }
@endphp

@if ($action)
    <script>
        window.EdzLoaderActions = Object.assign(
            {},
            (typeof window.EdzLoaderActions === 'object' && window.EdzLoaderActions !== null) ? window.EdzLoaderActions : {},
            @js([$action => $label ?? null])
        );
    </script>
@endif

{{--
    Opts a Livewire action into the global loading overlay.

    Usage (render anywhere on the page that performs the action):
        <x-edz.loading-target action="bulkDelete" :label="__('merchant.bulk_processing')" />

    Renders nothing visually. Two mechanisms keep the registry in sync:
      * an inline script merges this action into `window.EdzLoaderActions`
        every time this markup renders — including Livewire morphs after a
        component update — so client-side gating (e.g. "bulk bar appears once
        orders are selected") registers correctly;
      * the panel layout snapshots the full per-request set on full loads /
        wire:navigate.
    The `edzLoader` Alpine component shows the overlay only while that exact
    method is in flight and hides it on the real network `succeed`/`fail`
    signal, so trivial or unregistered requests never trigger the overlay.
--}}