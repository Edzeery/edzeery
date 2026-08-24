@props([
    'domain',
    'status',
    'value'  => 100,
    'size'   => 'md', // sm | md | lg
    'showLabel' => true,
    'class'  => '',
])

@php
    $result = \Edzeery\MyStatusKit\Facades\Status::for($domain, $status);
    $sizeClass = match($size) {
        'sm' => 'h-1',
        'lg' => 'h-4',
        default => 'h-2',
    };
    $clampedValue = max(0, min(100, $value));
@endphp

<div
    role="progressbar"
    aria-valuenow="{{ $clampedValue }}"
    aria-valuemin="0"
    aria-valuemax="100"
    aria-label="{{ $result->label() }}"
    {{ $attributes->merge(['class' => trim("w-full rounded-full overflow-hidden $sizeClass $class")]) }}
    style="background-color: {{ $result->hex() }}20;"
>
    <div
        class="{{ $sizeClass }} rounded-full transition-all duration-300"
        style="width: {{ $clampedValue }}%; background-color: {{ $result->hex() }};"
    ></div>
    @if($showLabel)
        <span class="sr-only">{{ $result->label() }}: {{ $clampedValue }}%</span>
    @endif
</div>
