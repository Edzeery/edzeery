@props([
    'domain',
    'status',
    'storeId' => null,
    'size' => 'md', // sm | md | lg
    'class' => '',
])

@php
    $statusKey = $status instanceof \BackedEnum ? $status->value : (string) $status;
    $result = \App\Domains\Status\StatusResolver::resolve($domain, $statusKey, $storeId ?? currentStoreId());
    $sizeClass = match($size) {
        'sm' => 'w-2 h-2',
        'lg' => 'w-4 h-4',
        default => 'w-3 h-3',
    };
@endphp

<span role="img" aria-label="{{ $result->label }}" {{ $attributes->merge(['class' => "$sizeClass rounded-full inline-block"]) }} style="background-color: {{ $result->hex }};"></span>
