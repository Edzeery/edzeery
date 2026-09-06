@props([
    'width' => '260px',
    'triggerClass' => '',
])

<div class="edz-dropdown" style="--edz-dropdown-width: {{ $width }};" x-data="edzDropdown()" @click.away="close()">
    <div x-show="open" x-cloak class="edz-dropdown__scrim" @click="close()"></div>

    <button x-ref="trigger" @click="toggle()" type="button"
        class="edz-dropdown__trigger {{ $triggerClass }}"
        :aria-expanded="open ? 'true' : 'false'" aria-haspopup="true">
        {{ $trigger }}
    </button>

    <div
        x-ref="panel"
        x-show="open"
        x-cloak
        x-transition:enter="edz-dropdown-enter"
        x-transition:enter-start="edz-dropdown-enter-start"
        x-transition:enter-end="edz-dropdown-enter-end"
        x-transition:leave="edz-dropdown-leave"
        x-transition:leave-start="edz-dropdown-leave-start"
        x-transition:leave-end="edz-dropdown-leave-end"
        :class="{ 'edz-dropdown__positioning': positioning }"
        class="edz-dropdown__panel"
        :style="menuStyle"
    >
        {{ $slot }}
    </div>
</div>