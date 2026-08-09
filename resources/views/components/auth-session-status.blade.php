@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'mb-4 font-medium text-sm text-success']) }}>
        {{ $status }}
    </div>
@endif
