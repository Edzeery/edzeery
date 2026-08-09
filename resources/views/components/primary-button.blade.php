<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => 'inline-flex items-center px-4 py-2 bg-brand text-white font-semibold rounded-lg hover:bg-brand-strong transition'
]) }}>
    {{ $slot }}
</button>
