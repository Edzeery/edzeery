<a {{ $attributes->merge([
    'class' => 'block w-full text-left px-4 py-2 text-sm text-ink hover:bg-neutral-secondary dark:hover:bg-dark-secondary transition'
]) }}>
    {{ $slot }}
</a>
