@props([
    'title' => '',
    'subtitle' => null,
])

<div class="w-full max-w-lg
            border border-neutral-border dark:border-dark-border
            rounded-2xl
            bg-neutral-surface dark:bg-dark-surface
            shadow-card
            p-6 sm:p-8
            animate-scale-in mx-auto">

    {{-- Title --}}
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-ink tracking-tight">
            {{ $title }}
        </h2>

        @isset($subtitle)
            <p class="text-sm text-neutral-soft dark:text-dark-soft mt-1.5">
                {{ $subtitle }}
            </p>
        @endisset
    </div>

    {{-- Slot Content --}}
    {{ $slot }}
</div>
