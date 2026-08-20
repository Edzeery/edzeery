@props([
    'align' => 'right',
    'width' => '48',
    'contentClasses' => 'py-1 bg-white dark:bg-dark-surface'
])

@php
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'top' => 'origin-top',
    default => 'ltr:origin-top-right rtl:origin-top-left start-0',
};

$width = match ($width) {
    '32' => 'w-32',
    '38' => 'w-38',
    '40' => 'w-40',
    '48' => 'w-48',
    default => $width,
};
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">

    {{-- Trigger --}}
    <div @click="open = ! open" class="inline-flex w-full justify-center items-center gap-1 cursor-pointer">
        {{ $trigger }}
        <svg :class="{ 'rotate-180': open }" class="w-3 h-3 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>

    {{-- Content --}}
    <div x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-2 {{ $width }} rounded-lg shadow-lg {{ $alignmentClasses }}"
        style="display: none;"
    >
        <div class="rounded-lg ring-1 ring-black ring-opacity-5 {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
