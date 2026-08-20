@props([
    'align' => 'right',
    'width' => '80',
])

@php
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'top' => 'origin-top',
    default => 'ltr:origin-top-right rtl:origin-top-left end-0',
};

$widthClass = match ($width) {
    '72' => 'w-72',
    '80' => 'w-80',
    '96' => 'w-96',
    default => "w-{$width}",
};
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.window="open = false">

    {{-- Trigger --}}
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    {{-- Panel --}}
    <div x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-1"
        class="absolute z-50 mt-2 {{ $widthClass }} {{ $alignmentClasses }} rounded-xl
               bg-white dark:bg-gray-800
               border border-gray-200 dark:border-gray-700
               shadow-xl shadow-black/5
               overflow-hidden flex flex-col"
        style="display: none;"
    >
        {{ $slot }}
    </div>
</div>
