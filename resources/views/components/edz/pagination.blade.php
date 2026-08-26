{{-- resources/views/components/edz/pagination.blade.php --}}

@props([
    'paginator'   => [],          // ['from','to','total','current_page','last_page']
    'method'      => 'setPage',   // اسم الميثود فـ Livewire component
    'maxVisible'  => 5,           // عدد أزرار الصفحات الظاهرة فـ النص
    'size'        => 'md',        // 'sm' | 'md'
    'showInfo'    => true,
    'showJump'    => false,
    'showPerPage' => false,
    'perPageOptions' => [10, 25, 50, 100],
    'perPageMethod'  => 'setPerPage',
    'perPage'        => 5,
])

@php
    $current = (int) ($paginator['current_page'] ?? 1);
    $last    = (int) ($paginator['last_page'] ?? 1);
    $total   = (int) ($paginator['total'] ?? 0);
    $from    = $paginator['from'] ?? 0;
    $to      = $paginator['to'] ?? 0;

    $sizeClasses = $size === 'sm'
        ? ['btn' => 'h-7 min-w-[1.75rem] text-xs', 'gap' => 'gap-0.5']
        : ['btn' => 'h-8 min-w-[2rem] text-sm', 'gap' => 'gap-1'];

    // بناء لائحة الصفحات مع ellipsis
    $pages = [];
    if ($last <= 1) {
        $pages = [1];
    } else {
        $delta = (int) floor(($maxVisible - 1) / 2);
        $rangeStart = max(2, $current - $delta);
        $rangeEnd   = min($last - 1, $current + $delta);

        if ($current - $delta <= 2) {
            $rangeEnd = min($last - 1, $rangeEnd + (2 - ($current - $delta)));
        }
        if ($current + $delta >= $last - 1) {
            $rangeStart = max(2, $rangeStart - (($current + $delta) - ($last - 1)));
        }

        $pages = [1];
        if ($rangeStart > 2) $pages[] = '...';
        for ($i = $rangeStart; $i <= $rangeEnd; $i++) $pages[] = $i;
        if ($rangeEnd < $last - 1) $pages[] = '...';
        if ($last > 1) $pages[] = $last;
    }
@endphp

@if ($total > 0)
<nav
    role="navigation"
    aria-label="{{ __('تصفح الصفحات') }}"
    {{ $attributes->class(['edz-pagination flex flex-col sm:flex-row items-center justify-between gap-3 p-4 border-t border-surface-border']) }}
    wire:loading.class="opacity-60 pointer-events-none"
    wire:target="{{ $method }}"
>
    {{-- معلومات النتائج + اختيار عدد العناصر --}}
    <div class="flex items-center gap-3 order-2 sm:order-1">
        @if ($showInfo)
            <span class="text-sm text-ink-muted tabular-nums whitespace-nowrap">
                {{ __('عرض') }}
                <span class="font-medium text-ink">{{ $from }}–{{ $to }}</span>
                {{ __('من') }}
                <span class="font-medium text-ink">{{ $total }}</span>
            </span>
        @endif

        @if ($showPerPage)
            <select
                wire:change="{{ $perPageMethod }}($event.target.value)"
                class="edz-select edz-select--xs h-7 rounded-lg border-surface-border text-xs text-ink-muted
                       focus:ring-2 focus:ring-[--edz-accent] focus:border-transparent transition"
                aria-label="{{ __('عدد العناصر فـ الصفحة') }}"
            >
                @foreach ($perPageOptions as $opt)
                    <option value="{{ $opt }}" @selected((int) $perPage === (int) $opt)>
                        {{ $opt }} / {{ __('صفحة') }}
                    </option>
                @endforeach
            </select>
        @endif
    </div>

    {{-- أزرار الصفحات --}}
    @if ($last > 1)
        <div class="flex items-center {{ $sizeClasses['gap'] }} order-1 sm:order-2">

            {{-- السابق --}}
            <button
                type="button"
                wire:click="{{ $method }}({{ $current - 1 }})"
                wire:loading.attr="disabled"
                @disabled($current <= 1)
                aria-label="{{ __('الصفحة السابقة') }}"
                class="edz-pagination__btn edz-pagination__btn--nav {{ $sizeClasses['btn'] }}
                       flex items-center justify-center rounded-lg text-ink-muted
                       hover:bg-surface-hover disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-transparent
                       focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[--edz-accent]
                       transition-all duration-150"
            >
                <svg class="w-4 h-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            {{-- أرقام الصفحات --}}
            @foreach ($pages as $pg)
                @if ($pg === '...')
                    <span class="{{ $sizeClasses['btn'] }} flex items-center justify-center text-ink-muted select-none">
                        &hellip;
                    </span>
                @else
                    <button
                        type="button"
                        wire:click="{{ $method }}({{ $pg }})"
                        wire:loading.attr="disabled"
                        wire:key="edz-page-{{ $pg }}"
                        aria-current="{{ $pg === $current ? 'page' : 'false' }}"
                        aria-label="{{ __('الصفحة') }} {{ $pg }}"
                        class="edz-pagination__btn {{ $sizeClasses['btn'] }} px-2 rounded-lg font-medium tabular-nums
                               transition-all duration-150 ease-out
                               focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[--edz-accent]
                               {{ $pg === $current
                                   ? 'edz-btn edz-btn--primary edz-btn--sm text-white shadow-sm'
                                   : 'text-ink-muted hover:bg-surface-hover' }}"
                    >
                        {{ $pg }}
                    </button>
                @endif
            @endforeach

            {{-- التالي --}}
            <button
                type="button"
                wire:click="{{ $method }}({{ $current + 1 }})"
                wire:loading.attr="disabled"
                @disabled($current >= $last)
                aria-label="{{ __('الصفحة التالية') }}"
                class="edz-pagination__btn edz-pagination__btn--nav {{ $sizeClasses['btn'] }}
                       flex items-center justify-center rounded-lg text-ink-muted
                       hover:bg-surface-hover disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-transparent
                       focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[--edz-accent]
                       transition-all duration-150"
            >
                <svg class="w-4 h-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            {{-- الانتقال المباشر لصفحة --}}
            @if ($showJump && $last > 10)
                <div class="flex items-center gap-1 ms-2 ps-2 border-s border-surface-border">
                    <span class="text-xs text-ink-muted whitespace-nowrap">{{ __('الذهاب إلى') }}</span>
                    <input
                        type="number"
                        min="1"
                        max="{{ $last }}"
                        x-data
                        @keydown.enter="
                            const val = Math.min({{ $last }}, Math.max(1, parseInt($event.target.value) || 1));
                            $wire.{{ $method }}(val);
                            $event.target.value = '';
                        "
                        class="w-14 h-7 rounded-lg border-surface-border text-xs text-center tabular-nums
                               focus:ring-2 focus:ring-[--edz-accent] focus:border-transparent transition"
                        placeholder="{{ $current }}"
                        aria-label="{{ __('رقم الصفحة') }}"
                    />
                </div>
            @endif
        </div>
    @endif
</nav>
@endif

{{--
<x-edz.pagination
    :paginator="$leads"
    method="goToPage"
    size="sm"
    :max-visible="7"
    :show-jump="true"
    :show-per-page="true"
    :per-page="$perPage"
    per-page-method="updatePerPage"
/> --}}
{{-- <x-edz.pagination :paginator="$orders" method="setPage" /> --}}
