@props([
    'isOpen' => false,
    'showCloseButton' => true,
    'size' => 'md',
    'preventClose' => false,
])

@php
    $sizeClass = "edz-modal__panel--{$size}";
@endphp

<div x-data='{
    open: @js($isOpen),
    preventClose: @js($preventClose),
    depth: 0,
    init() {
        const stack = window.__edzModalStack ||= [];
        if (this.open) {
            if (!stack.includes(this.$el)) stack.push(this.$el);
            this.depth = stack.indexOf(this.$el) + 1;
            document.body.style.overflow = "hidden";
        }
        this.$watch("open", (value) => {
            const stack = window.__edzModalStack ||= [];
            if (value) {
                if (!stack.includes(this.$el)) stack.push(this.$el);
                this.depth = stack.indexOf(this.$el) + 1;
                document.body.style.overflow = "hidden";
            } else {
                const i = stack.indexOf(this.$el);
                if (i !== -1) stack.splice(i, 1);
                this.depth = 0;
                document.body.style.overflow = stack.length ? "hidden" : "unset";
                this.$dispatch("edz-modal-closed");
            }
        });
    },
    // A parent re-render can remove this element while it is still "open"
    // (e.g. a Volt action nulls the bound state directly). $watch never
    // fires on destroy, so restore the body scroll here to keep the page
    // usable.
    destroy() {
        const stack = window.__edzModalStack || [];
        const i = stack.indexOf(this.$el);
        if (i !== -1) stack.splice(i, 1);
        document.body.style.overflow = stack.length ? "hidden" : "unset";
    }
}' x-show="open" x-cloak
    @keydown.escape.window="if (!preventClose) { const s = window.__edzModalStack ||= []; if (s[s.length - 1] === $el) open = false }"
    @edz-modal-closed="if ($event.target !== $event.currentTarget) $event.stopPropagation()"
    :style="'--edz-modal-depth:' + depth"
    class="edz-modal"
    {{ $attributes->except('class') }}>

    <!-- Backdrop -->
    <div @click="if (!preventClose) open = false" class="edz-modal__backdrop"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    </div>

    <!-- Panel -->
    <div @click.stop class="edz-modal__panel {{ $sizeClass }} {{ $attributes->get('class') }} "
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95">

        <!-- Close Button -->
        @if ($showCloseButton)
            <button type="button" @click="open = false" class="edz-modal__close end-4 me-auto" aria-label="Close">
                <x-edz.icon name="x-mark" class="w-5 h-5" />
            </button>
        @endif

        <!-- Modal Body -->
        <div>
            {{ $slot }}
        </div>
    </div>
</div>