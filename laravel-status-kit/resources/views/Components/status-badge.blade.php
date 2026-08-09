@props(['status', 'dark' => false])

@php
    $color = $status->color($dark);
@endphp

<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-sm {{ $color }}">
    @if($status->icon())
        {!! \Edzeery\LaravelStatusKit\Support\IconManager::get($status->icon(), 'hero', 'w-4 h-4') !!}
    @endif
    {{ $status->label() }}
</span>
