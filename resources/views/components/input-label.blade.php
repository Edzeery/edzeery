@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-ink mb-1.5 tracking-tight']) }}>
    {{ $value ?? $slot }}
</label> 
