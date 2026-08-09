@props(['active' => false])

<a {{ $attributes->merge([
    'class' => 'block px-3 py-2 rounded-md text-base font-medium transition ' . ($active ? 'bg-brand text-white' : 'text-neutral-text dark:text-dark-text hover:bg-neutral-secondary dark:hover:bg-dark-secondary')
]) }}>
    {{ $slot }}
</a>
