<button {{ $attributes->merge([
    'type' => 'button',
    'class' => 'inline-flex items-center px-4
    py-2 bg-neutral-secondary
    dark:bg-dark-secondary
    text-neutral-text
    dark:text-dark-text
    font-semibold hover:bg-neutral-tertiary
    dark:hover:bg-dark-tertiary transition'
]) }}>
    {{ $slot }}
</button>
