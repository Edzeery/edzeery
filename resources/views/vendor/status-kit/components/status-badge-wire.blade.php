@props([
    'domain',
    'status',
    'set'   => null,
    'icon'  => true,
    'class' => '',
])

@php
    $result = \Edzeery\MyStatusKit\Facades\Status::for($domain, $status);
@endphp

<span
    role="status"
    aria-label="{{ $result->label() }}"
    wire:ignore.self
    {{ $attributes->merge(['class' => $result->badgeClasses($class)]) }}
>
    @if($icon)
        {!! $result->icon($set) !!}
    @endif
    <span>{{ $result->label() }}</span>
</span>
