@props([
    'domain',
    'status',
    'storeId' => null,
    'icon' => false,
])

@php
    $statusKey = $status instanceof \BackedEnum ? $status->value : (string) $status;
    $result = \App\Domains\Status\StatusResolver::resolve($domain, $statusKey, $storeId ?? currentStoreId());
    $classes = 'status-badge inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold '
        . $result->classes(true);
@endphp

<span role="status" aria-label="{{ $result->label }}"
      {{ $attributes->merge(['class' => $classes]) }}>
    @if (filter_var($icon, FILTER_VALIDATE_BOOLEAN))
        {!! $result->renderIcon(null, 'w-3 h-3 shrink-0') !!}
    @endif
    <span>{{ $result->label }}</span>
</span>
