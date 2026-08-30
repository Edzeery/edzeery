@props(['role', 'dark' => false])

@php
    $resolved = $role instanceof \BackedEnum
        ? $role
        : (\App\Enums\Store\StoreRoleEnum::tryFrom((string) $role)
            ?? \App\Enums\Platform\UserRoleEnum::tryFrom((string) $role));
@endphp

@if ($resolved)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-sm '.trim($resolved->color($dark))]) }}>
        {!! $resolved->icon('heroicon', 'w-4 h-4') !!}
        <span>{{ $resolved->label() }}</span>
    </span>
@else
    <span {{ $attributes }}>{{ $role }}</span>
@endif