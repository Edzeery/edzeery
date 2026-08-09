<button {{ $attributes->merge([
    'type' => 'button',
    'class' => 'inline-flex items-center px-4 py-2 bg-danger text-white font-semibold rounded-lg hover:bg-danger/90 transition'
]) }}>
    {{ $slot }}
</button>
