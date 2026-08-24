@props([
    'domain',
    'status',
    'set'   => null,
    'class' => '',
])

@php
    $result = \Edzeery\MyStatusKit\Facades\Status::for($domain, $status);
@endphp

<span role="img" aria-label="{{ $result->label() }}">{!! $result->icon($set, $class ? $class : null) !!}</span>
