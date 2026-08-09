@props(['active' => false, 'icon' => null])

<a
    {{ $attributes->merge([
        'class' =>
            'flex items-center px-4 py-2
                            rounded-md text-sm font-medium transition ' .
            ($active
                ? 'bg-brand text-white '
                : 'text-neutral-text
                            dark:text-dark-text
                            hover:bg-neutral-secondary
                            dark:hover:bg-dark-secondary'),
    ]) }}>

    {{ $slot }}
</a>
