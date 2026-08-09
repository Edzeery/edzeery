@props(['role', 'dark' => false])

@php 
    $color = $role->color($dark) ;
    $icon = $role->value;

@endphp
<style>
    .fi-icon {
        width: calc(var(--spacing) * 4);
        height: calc(var(--spacing) * 4);
    }

</style>


<span class="inline-flex items-center gap-1 px-2 py-0.5
rounded-full text-sm {{ $color }}">

    @if ($role->icon())
        @if (Str::contains($role->icon(), 'heroicon'))
            <x-filament::badge :icon="$role->icon()" class="inline-flex items-center gap-1 ">
                {{ $role->label() }}
            </x-filament::badge>
        @else
            {!! App\Support\Status\IconManager::get($icon) !!}
        @endif
    @endif
    @if (!Str::contains($role->icon(), 'heroicon'))
        {{ $role->label() }}
    @endif

</span>
