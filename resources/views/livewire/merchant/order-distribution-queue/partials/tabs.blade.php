{{-- Distribution queue tabs (P34.5) — confirmation / tracking with live counts. --}}
<div class="flex gap-1 mb-6 border-b border-surface-border">
    <button wire:click="setTab('confirmation')"
            class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px {{ $tab === 'confirmation' ? 'border-brand-500 text-brand-fg' : 'border-transparent text-ink-muted hover:text-ink' }}">
        <x-edz.icon name="check" class="w-4 h-4" />
        {{ __('merchant_panel.queue_tab_confirmation') }}
        <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 text-[11px] font-semibold rounded-full tabular-nums {{ $tab === 'confirmation' ? 'bg-brand-500/15 text-brand-700 dark:text-brand-400' : 'bg-surface-tertiary text-ink-muted' }}">
            {{ $confirmationCount }}
        </span>
    </button>

    <button wire:click="setTab('tracking')"
            class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px {{ $tab === 'tracking' ? 'border-brand-500 text-brand-fg' : 'border-transparent text-ink-muted hover:text-ink' }}">
        <x-edz.icon name="truck" class="w-4 h-4" />
        {{ __('merchant_panel.queue_tab_tracking') }}
        <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 text-[11px] font-semibold rounded-full tabular-nums {{ $tab === 'tracking' ? 'bg-brand-500/15 text-brand-700 dark:text-brand-400' : 'bg-surface-tertiary text-ink-muted' }}">
            {{ $trackingCount }}
        </span>
    </button>
</div>