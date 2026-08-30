@props([
    'status',
    'domain' => null,
    'storeId' => null,
    'mode' => null,
    'iconOnly' => false,
    'dark' => true,
    'size' => 'sm',
    'label' => null,
    'set' => null,
    'iconClass' => null,
])

@php
    $resolved = $status instanceof \App\Domains\Status\Support\ResolvedStatus
        ? $status
        : ($status instanceof \BackedEnum
            ? $status->resolved($storeId)
            : \App\Domains\Status\StatusResolver::resolve($domain, (string) $status, $storeId));

    $mode = $mode ?: ($iconOnly ? 'icon' : $resolved->displayMode);

    $sizeClass = match ($size) {
        'xs' => 'px-1.5 py-0.5 text-[10px]',
        'md' => 'px-3 py-1 text-sm',
        default => 'px-2.5 py-0.5 text-xs',
    };

    $iconSize = match ($size) {
        'xs' => 'w-3 h-3',
        'md' => 'w-5 h-5',
        default => 'w-4 h-4',
    };

    $text = $label ?? $resolved->label;
@endphp

@if ($mode === 'icon')
    <span {{ $attributes->merge([
        'class' => 'inline-flex items-center justify-center',
        'title' => $text,
    ]) }}>
        {!! $resolved->renderIcon($set, $iconClass ?? $iconSize) !!}
    </span>
@elseif ($mode === 'dot')
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5']) }} title="{{ $text }}">
        <span class="inline-block rounded-full {{ $iconSize }}" style="background-color: {{ $resolved->hex }};"></span>
        @if (! $iconOnly)
            <span class="text-xs font-medium">{{ $text }}</span>
        @endif
    </span>
@elseif ($mode === 'text')
    <span {{ $attributes->merge(['class' => 'text-xs font-medium '.trim($resolved->classes($dark))]) }}>
        {{ $text }}
    </span>
@else
    <span {{ $attributes->merge([
        'class' => 'status-badge inline-flex items-center gap-1 rounded-full font-medium '.$sizeClass.' '.trim($resolved->classes($dark)),
        'title' => $iconOnly ? $text : null,
    ]) }}>
        @if ($resolved->icon && ! $iconOnly)
            {!! $resolved->renderIcon($set, $iconSize) !!}
        @endif
        @if (! $iconOnly)
            <span>{{ $text }}</span>
        @endif
    </span>
@endif