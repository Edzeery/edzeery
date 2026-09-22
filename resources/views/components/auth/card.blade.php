@props([
    'title' => '',
    'subtitle' => null,
])

<div class="w-full max-w-lg
            border border-surface-border
            rounded-2xl
            bg-surface
            shadow-card
            p-6 sm:p-8
            animate-scale-in mx-auto">

    {{-- Title --}}
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-ink tracking-tight">
            {{ $title }}
        </h2>

        @isset($subtitle)
            <p class="text-sm text-ink-soft mt-1.5">
                {{ $subtitle }}
            </p>
        @endisset
    </div>

    {{-- Slot Content --}}
    {{ $slot }}
</div>
