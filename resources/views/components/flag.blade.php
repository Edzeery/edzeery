@props([
    'src',
    'alt' => '',
    'circular' => false,
    'switch' => false,
])
<img
    src="{{ $src }}"
    {{ $attributes
        ->class([
            'object-cover object-center',
            'rounded-full w-10 h-10' => $circular,
            'rounded-lg' => ! $circular && ! $switch,
            'rounded-md' => ! $circular && $switch,
        ])
        ->merge(['alt' => $alt])
    }}
/>
