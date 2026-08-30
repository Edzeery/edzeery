@props([
    'domain',
    'status',
    'storeId' => null,
    'set'   => null,
    'class' => '',
])

@php
    $statusKey = $status instanceof \BackedEnum ? $status->value : (string) $status;
    $result = \App\Domains\Status\StatusResolver::resolve($domain, $statusKey, $storeId ?? currentStoreId());
@endphp

<span role="img" aria-label="{{ $result->label }}">{!! $result->renderIcon($set, $class ? $class : null) !!}</span>
