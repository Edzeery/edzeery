@props([
    'title' => '',
    'subtitle' => null,
])

<div data-aos="zoom-in"
     class="w-full max-w-lg
            border border-neutral-border dark:border-dark-border
            rounded-2xl
            bg-neutral-surface dark:bg-dark-surface
            shadow-card
            p-6">

    {{-- Title --}}
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-neutral-text dark:text-dark-text">
            {{ $title }}
        </h2>

        @isset($subtitle)
            <p class="text-sm text-neutral-soft dark:text-dark-soft mt-1">
                {{ $subtitle }}
            </p>
        @endisset
    </div>

    {{-- Slot Content --}}
    {{ $slot }}
</div>
