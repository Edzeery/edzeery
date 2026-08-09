@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-neutral-text dark:text-dark-text mb-1']) }}>
    {{ $value ?? $slot }}
</label> 
