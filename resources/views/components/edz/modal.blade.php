@props([
    'isOpen' => false,
    'showCloseButton' => true,
    'size' => 'md',
])

@php
    $sizeClass = "edz-modal__panel--{$size}";
@endphp

<div x-data='{
    open: @js($isOpen),
    init() {
        this.$watch("open", value => {
            if (value) {
                document.body.style.overflow = "hidden";
            } else {
                document.body.style.overflow = "unset";
                this.$dispatch("edz-modal-closed");
            }
        });
    }
}' x-show="open" x-cloak @keydown.escape.window="open = false"
    class="edz-modal"
    {{ $attributes->except('class') }}>

    <!-- Backdrop -->
    <div @click="open = false" class="edz-modal__backdrop"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    </div>

    <!-- Panel -->
    <div @click.stop class="edz-modal__panel {{ $sizeClass }} {{ $attributes->get('class') }}"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95">

        <!-- Close Button -->
        @if ($showCloseButton)
            <button @click="open = false" class="edz-modal__close" aria-label="Close">
                <x-edz.icon name="x-mark" class="w-5 h-5" />
            </button>
        @endif

        <!-- Modal Body -->
        <div>
            {{ $slot }}
        </div>
    </div>
</div>
