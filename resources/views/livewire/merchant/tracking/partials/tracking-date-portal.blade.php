{{-- Date-range portal (Phase 4) — first-class toolbar trigger. Two flatpickr
    fields drive filters.date_from / filters.date_to; the quick-clear reset
    both in a single round-trip. --}}
<div x-data="dropdownPosition()" x-show="open" @click.away="close()"
    @edz-date-filter-open.window="$event.detail && toggle($event, $event.detail)">

    <div x-show="open" x-cloak class="fixed inset-0 z-[205] bg-black/40 backdrop-blur-sm sm:hidden"
        @click="close()"></div>

    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-3"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-3"
        :style="menuStyle"
        class="fixed inset-x-0 bottom-0 z-[210] w-full rounded-t-2xl border border-b-0 border-surface-border bg-surface
               p-3 pb-[calc(1rem+env(safe-area-inset-bottom))] shadow-[0_-16px_48px_-12px_rgba(15,23,42,.25)]
               sm:inset-x-auto sm:bottom-auto sm:z-50 sm:w-64 sm:rounded-xl sm:border-b sm:p-2 sm:shadow-lg">

        <span class="pointer-events-none mx-auto mb-2 block h-1 w-10 rounded-full bg-surface-border sm:hidden"></span>
        <div class="flex items-center justify-between gap-2 px-1 mb-1.5 sm:hidden">
            <p class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink uppercase tracking-wide">
                <x-edz.icon name="calendar" class="w-3.5 h-3.5 text-ink-muted" />
                <span>{{ __('order_flow.filter_date') }}</span>
            </p>
            <button @click="close()" type="button"
                class="-m-1 p-1 rounded-lg text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                title="{{ __('general.close') }}">
                <x-edz.icon name="x-mark" class="w-4 h-4" />
            </button>
        </div>

        <div class="edz-dropdown__section">
            <p class="edz-dropdown__section-title">{{ __('order_flow.filter_date') }}</p>

            @if (filled($this->filters['date_from']) || filled($this->filters['date_to']))
                <button type="button" @click="$wire.clearDateFilter(); close()"
                    class="edz-dropdown__item justify-between text-danger-600">
                    <span class="truncate">{{ __('merchant_panel.clear_filters') }}</span>
                    <x-edz.icon name="x-mark" class="w-3.5 h-3.5 shrink-0" />
                </button>
            @endif

            <div class="px-1 flex items-center gap-2">
                <input type="text" wire:model.blur="filters.date_from"
                    class="edz-input text-sm flatpickr-input"
                    placeholder="{{ __('order_flow.filter_date') }} —" autocomplete="off">
                <span class="text-ink-muted text-sm">—</span>
                <input type="text" wire:model.blur="filters.date_to"
                    class="edz-input text-sm flatpickr-input"
                    placeholder="— {{ __('order_flow.filter_date') }}" autocomplete="off">
            </div>
        </div>
    </div>
</div>