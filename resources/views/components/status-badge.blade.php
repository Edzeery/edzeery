@props(['status', 'dark' => false, 'iconOnly' => false])

@php

    $color = $status->color($dark);
    $icon = $status->value;

@endphp
<style>
    .fi-icon {
        width: calc(var(--spacing) * 4);
        height: calc(var(--spacing) * 4);
    }
</style>


<span class="inline-flex items-center gap-1 px-2 py-0.5
rounded-full text-sm {{ $color }}">

    @if ($status->icon())
        @if (Str::contains($status->icon(), 'heroicon'))
            <x-filament::badge :icon="$status->icon()" class="inline-flex items-center gap-1 ">
                @if (!$iconOnly)
                    {{ $status->label() }}
                @endif
            </x-filament::badge>
        @else
            {!! App\Support\Status\IconManager::get($icon) !!}
        @endif
    @endif
    @if (!$iconOnly)
        @if (!Str::contains($status->icon(), 'heroicon'))
            {{ $status->label() }}
        @endif
    @endif

</span>
