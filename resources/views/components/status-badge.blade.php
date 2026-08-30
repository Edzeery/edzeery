@props(['status', 'dark' => true, 'domain' => null, 'set' => null, 'iconOnly' => false, 'storeId' => null])

@php
    $resolved = $status instanceof \App\Domains\Status\Support\ResolvedStatus
        ? $status
        : ($status instanceof \BackedEnum
            ? $status->resolved($storeId)
            : \App\Domains\Status\StatusResolver::resolve($domain, (string) $status, $storeId));
@endphp

<span {{ $attributes->merge([
    'class' => 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-sm '.trim($resolved->classes($dark)),
    'title' => $iconOnly ? $resolved->label : null,
]) }}>
    @if ($resolved->icon)
        {!! $resolved->renderIcon($set, 'w-4 h-4') !!}
    @endif

    @if (! $iconOnly)
        <span>{{ $resolved->label }}</span>
    @endif
</span>