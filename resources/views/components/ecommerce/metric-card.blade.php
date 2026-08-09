@props([
    'icon' => null,
    'title',
    'count' => 0,
    'desc' => '',
    'percentageResult' => 0,
    'trend' => 'up',
])

@php
    $isPositive = $trend === 'up';
@endphp

<div
    class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-white/[0.03] md:p-6">

    {{-- Icon --}}
    <div class="flex items-center justify-center w-12 h-12 bg-gray-100 rounded-xl dark:bg-gray-800">
        @if ($icon)
            {!! $icon !!}
        @else
            <svg class="w-6 h-6 fill-gray-800 dark:fill-white/90" viewBox="0 0 24 24">
                <path d="M12 2L2 7l10 5 10-5-10-5Zm0 7L2 4v13l10 5V9Zm2 13 8-4V5l-8 4v13Z" />
            </svg>
        @endif
    </div>

    {{-- Content --}}
    <div class="flex items-end justify-between mt-5">
        <div>
            <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ $title }}
            </span>

            <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                {{ number_format($count) }}
            </h4>

            @if ($desc)
                <p class="mt-1 text-xs text-gray-500">{{ $desc }}</p>
            @endif
        </div>

        @if ($trend && $percentageResult )

            <span @class([
                'flex items-center gap-1 rounded-full py-1 pl-2 pr-2.5 text-sm font-medium',
                'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' => $isPositive,
                'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500' => !$isPositive,
            ])>
                @if ($isPositive)
                    ↑
                @else
                    ↓
                @endif

                {{ $percentageResult }}%
            </span>
        @endif
    </div>
</div>
