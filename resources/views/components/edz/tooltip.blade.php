@props([
    'label' => '',
    'side' => 'top',
    'maxWidth' => '288px',
])

@if ($label === '')
    {{ $slot }}
@else
    <div
        {{ $attributes->class('edz-tooltip') }}
        style="--edz-tooltip-max-w: {{ $maxWidth }};"
        x-data="edzTooltip(@js($side))"
        @mouseenter="enter()"
        @mouseleave="leave()"
        @focusin="enter()"
        @focusout="leave()"
        @click.capture="hide()"
        @scroll.window.passive="hide()"
    >
        <span x-ref="trigger" class="edz-tooltip__trigger">
            {{ $slot }}
        </span>

        <span
            x-ref="bubble"
            x-show="visible"
            x-cloak
            role="tooltip"
            x-transition:enter="edz-tooltip-enter"
            x-transition:enter-start="edz-tooltip-enter-start"
            x-transition:enter-end="edz-tooltip-enter-end"
            x-transition:leave="edz-tooltip-leave"
            x-transition:leave-start="edz-tooltip-leave-start"
            x-transition:leave-end="edz-tooltip-leave-end"
            :class="{ 'edz-tooltip__bubble--positioning': positioning }"
            :style="bubbleStyle"
            class="edz-tooltip__bubble"
        >
            {{ $label }}
        </span>
    </div>
@endif