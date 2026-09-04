{{-- Pure CSS mockup thumbnails per landing template --}}
@if ($key === 'single_product')
    <div class="absolute inset-0 p-4 flex gap-3">
        <div class="flex-1 space-y-2.5">
            <div class="h-2.5 w-14 rounded-full bg-accent-400/70"></div>
            <div class="h-4 w-4/5 rounded-lg bg-gray-300 bg-surface-tertiary"></div>
            <div class="h-3 w-full rounded bg-gray-200 bg-surface-tertiary"></div>
            <div class="h-3 w-3/4 rounded bg-gray-200 bg-surface-tertiary"></div>
            <div class="mt-4 space-y-2">
                <div class="h-2 w-16 rounded bg-gray-200 bg-surface-tertiary"></div>
                <div class="h-3 w-24 rounded bg-gray-300 bg-surface-tertiary"></div>
            </div>
            <div class="h-9 w-28 rounded-xl bg-accent-500/90 mt-2"></div>
        </div>
        <div class="w-28 h-full rounded-xl bg-gray-200 bg-surface-tertiary flex items-center justify-center">
            <x-edz.icon name="image" class="w-8 h-8 text-gray-400 text-ink-muted" />
        </div>
    </div>
@elseif ($key === 'catalog')
    <div class="absolute inset-0 p-3 flex flex-col gap-2.5">
        <div class="flex gap-1.5">
            <div class="h-6 flex-1 rounded-full bg-accent-400/40 border border-accent-300/50"></div>
            <div class="h-6 flex-1 rounded-full bg-gray-200 bg-surface-tertiary"></div>
            <div class="h-6 flex-1 rounded-full bg-gray-200 bg-surface-tertiary"></div>
        </div>
        <div class="h-6 rounded-lg bg-gray-200 bg-surface-tertiary w-1/2"></div>
        <div class="flex-1 grid grid-cols-3 gap-1.5">
            @for ($i = 0; $i < 6; $i++)
                <div class="rounded-lg bg-gray-200 bg-surface-tertiary flex items-center justify-center">
                    <x-edz.icon name="bag" class="w-full text-gray-400 text-ink-muted" />
                </div>
            @endfor
        </div>
        <div class="h-5 w-16 rounded-full bg-accent-500/60 mx-auto"></div>
    </div>
@else
    <div class="absolute inset-0 p-3 flex flex-col items-center gap-2">
        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-accent-400 to-accent-600 mx-auto"></div>
        <div class="h-2 w-24 rounded bg-gray-300 bg-surface-tertiary mx-auto"></div>
        <div class="h-2 w-16 rounded bg-gray-200 bg-surface-tertiary mx-auto"></div>
        <div class="flex-1 w-full grid grid-cols-2 gap-1.5 mt-2">
            @for ($i = 0; $i < 4; $i++)
                <div class="rounded-lg bg-gray-200 bg-surface-tertiary flex flex-col items-center justify-center gap-1 p-1">
                    <div class="w-6 h-6 rounded bg-gray-300 bg-surface-tertiary"></div>
                    <div class="h-1 w-8 rounded bg-gray-300 bg-surface-tertiary"></div>
                </div>
            @endfor
        </div>
    </div>
@endif
