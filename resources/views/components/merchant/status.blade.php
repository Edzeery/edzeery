@props([
    'domain',
    'status',
])

@php
    $result = \Edzeery\MyStatusKit\Facades\Status::for($domain, $status);
    $classes = 'status-badge inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium '
        . $result->color(true, 'tailwind');
@endphp

<span role="status" aria-label="{{ $result->label() }}"
      {{ $attributes->merge(['class' => $classes]) }}>
    <span>{{ $result->label() }}</span>
</span>
