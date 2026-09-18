{{-- Tabs --}}
    <div class="flex gap-1 mb-6 border-b border-surface-border">
        <button wire:click="setTab('shifts')"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px {{ $tab === 'shifts' ? 'border-brand-500 text-brand-fg' : 'border-transparent text-ink-muted hover:text-ink' }}">
            <x-edz.icon name="adjustments" class="w-4 h-4" />
            {{ __('merchant_panel.tab_shifts') }}
        </button>
        <button wire:click="setTab('products')"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px {{ $tab === 'products' ? 'border-brand-500 text-brand-fg' : 'border-transparent text-ink-muted hover:text-ink' }}">
            <x-edz.icon name="package" class="w-4 h-4" />
            {{ __('merchant_panel.tab_product_assignments') }}
        </button>
        <button wire:click="setTab('overflow')"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px {{ $tab === 'overflow' ? 'border-brand-500 text-brand-fg' : 'border-transparent text-ink-muted hover:text-ink' }}">
            <x-edz.icon name="trending-up" class="w-4 h-4" />
            {{ __('merchant_panel.distribution_overflow_group') }}
        </button>
    </div>