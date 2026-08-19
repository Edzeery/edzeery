@props([
    'lines' => 1,
    'height' => null,
    'width' => null,
    'rounded' => 'md',
    'class' => '',
])

@for ($i = 0; $i < $lines; $i++)
    <div
        {{ $attributes->class([
            'animate-shimmer bg-gradient-to-r from-gray-200 via-gray-100 to-gray-200 dark:from-gray-700 dark:via-gray-600 dark:to-gray-700',
            "bg-[length:200%_100%]",
            "rounded-{$rounded}",
            $class,
        ])->style([
            'height' => $height ?: '0.875rem',
            'width' => $width ?: '100%',
            'animation-delay' => $i * 0.1 . 's',
        ]) }}
    ></div>
@endfor
