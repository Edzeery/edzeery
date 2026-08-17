@props([
    'type' => 'info',
    'dismissible' => false,
])

@php
    $types = [
        'success' => 'edz-alert--success',
        'warning' => 'edz-alert--warning',
        'danger'  => 'edz-alert--danger',
        'info'    => 'edz-alert--info',
    ];
@endphp

<div class="edz-alert {{ $types[$type] ?? $types['info'] }}" role="alert">
    <div class="edz-alert__content">
        {{ $slot }}
    </div>
    @if ($dismissible)
        <button type="button" class="edz-alert__close" wire:click="$dispatch('close-alert')" aria-label="Close">
            <x-edz.icon name="x-mark" class="w-4 h-4" />
        </button>
    @endif
</div>
