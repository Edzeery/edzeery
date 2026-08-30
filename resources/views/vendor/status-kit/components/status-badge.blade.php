@props([
    'domain',
    'status',
    'storeId' => null,
    'set'   => null,
    'icon'  => true,
    'class' => '',
])

@php
    $statusKey = $status instanceof \BackedEnum ? $status->value : (string) $status;
    $result = \App\Domains\Status\StatusResolver::resolve($domain, $statusKey, $storeId ?? currentStoreId());
@endphp

<span role="status" aria-label="{{ $result->label }}" {{ $attributes->merge(['class' => $result->badgeClasses($class)]) }}>
    @if($icon)
        {!! $result->renderIcon($set) !!}
    @endif
    <span>{{ $result->label }}</span>
</span>
