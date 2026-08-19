@props(['active' => false])

<a {{ $attributes->merge([
    'class' => 'px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 ' .
        ($active
            ? 'bg-brand-600 text-white shadow-sm'
            : 'text-ink hover:bg-neutral-secondary dark:hover:bg-dark-secondary hover:text-ink')
]) }}>
    {{ $slot }}
</a>

