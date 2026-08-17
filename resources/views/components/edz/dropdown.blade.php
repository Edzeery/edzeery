@props([
    'align' => 'right',
    'width' => '260px',
    'trigger' => null,
])

@php
    $alignClass = $align === 'left' ? 'left-0' : 'right-0';
@endphp

<div class="edz-dropdown" x-data="{
    open: false,
    toggle() { this.open = !this.open; },
    close() { this.open = false; }
}" @click.away="close()">
    <button @click="toggle()" type="button" class="edz-dropdown__trigger">
        {{ $trigger }}
    </button>

    <div
        x-show="open"
        x-transition:enter="edz-dropdown-enter"
        x-transition:enter-start="edz-dropdown-enter-start"
        x-transition:enter-end="edz-dropdown-enter-end"
        x-transition:leave="edz-dropdown-leave"
        x-transition:leave-start="edz-dropdown-leave-start"
        x-transition:leave-end="edz-dropdown-leave-end"
        class="edz-dropdown__panel {{ $alignClass }}"
        style="width: {{ $width }}; display: none;"
    >
        {{ $slot }}
    </div>
</div>
