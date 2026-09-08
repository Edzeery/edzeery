@props([
    'title' => '',
    'icon' => 'ellipsis-horizontal',
    'closeExpr' => 'close()',
    'smWidth' => 'sm:w-56',
    'smMaxHeight' => 'sm:max-h-64',
    'smPad' => 'sm:p-1.5 sm:pb-1.5',
    'smZ' => 'sm:z-[200]',
    'keepHeaderSm' => false,
    'scrimFade' => false,
])

{{-- Shared order bottom-sheet (P29.8). Backdrop fills the viewport on phones and
    the panel slides up as a bottom sheet; on sm+ it becomes an anchored dropdown.
    Positioning / open state / close handlers live in the caller's Alpine scope
    ($open, $menuStyle, $close): parameterised via $closeExpr ($close() or raw
    Alpine like "open = false"), $smWidth/$smMaxHeight/$smPad/$smZ for the
    per-sheet anchored frame, $keepHeaderSm for sheets that keep their header on
    sm+, and $scrimFade for sheets whose backdrop fades in. Body goes in $slot. --}}

{{-- Mobile scrim — a dimmed backdrop that closes the sheet --}}
<div x-show="open" x-cloak @if ($scrimFade) x-transition.opacity @endif
    @click="{{ $closeExpr }}"
    class="fixed inset-0 z-[205] bg-black/40 backdrop-blur-sm sm:hidden"></div>

{{-- Panel: anchored dropdown on sm+, bottom sheet on phones --}}
<div x-show="open" x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-y-3"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-3"
    :style="menuStyle"
    @class([
        'fixed inset-x-0 bottom-0 sm:inset-x-auto sm:bottom-auto z-[210] w-full rounded-t-2xl sm:rounded-xl',
        'border border-b-0 sm:border-b border-surface-border bg-surface',
        'p-3 pb-[calc(1rem+env(safe-area-inset-bottom))]',
        'shadow-[0_-16px_48px_-12px_rgba(15,23,42,.25)] sm:shadow-lg',
        'max-h-[70vh] overflow-y-auto edz-scroll',
        $smZ,
        $smWidth,
        $smPad,
        $smMaxHeight,
    ])>
    <span
        class="pointer-events-none mx-auto mb-2 block h-1 w-10 rounded-full bg-surface-border sm:hidden"></span>
    <div @class([
        'flex items-center justify-between gap-2 px-1 mb-1.5',
        $keepHeaderSm ? '' : 'sm:hidden',
    ])>
        <p class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink uppercase tracking-wide">
            <x-edz.icon :name="$icon" class="w-3.5 h-3.5 text-ink-muted" />
            <span>{{ $title }}</span>
        </p>
        <button @click="{{ $closeExpr }}" type="button"
            @class([
                '-m-1 p-1 rounded-lg text-ink-muted hover:text-ink hover:bg-surface-tertiary',
                $keepHeaderSm ? 'sm:hidden' : '',
            ])
            title="{{ __('general.close') }}">
            <x-edz.icon name="x-mark" class="w-4 h-4" />
        </button>
    </div>
    {{ $slot }}
</div>