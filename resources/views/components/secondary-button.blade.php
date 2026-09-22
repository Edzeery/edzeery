<button {{ $attributes->merge([
    'type' => 'button',
    'class' => 'inline-flex items-center px-4
    py-2 bg-surface-secondary
    text-ink
    font-semibold hover:bg-surface-tertiary transition'
]) }}>
    {{ $slot }}
</button>
