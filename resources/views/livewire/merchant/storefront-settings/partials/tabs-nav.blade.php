@props([
    'tabs',
    'variant' => 'mobile', // mobile: horizontal pills | desktop: vertical nav
])

@if ($variant === 'mobile')
    <div class="flex gap-1 p-1 bg-surface-secondary rounded-xl overflow-x-auto" role="tablist"
         aria-label="{{ __('merchant_panel.storefront_template') }}">
        @foreach ($tabs as $tabKey => $tab)
            <button type="button"
                id="tab-btn-{{ $tabKey }}"
                role="tab"
                aria-controls="tab-panel-{{ $tabKey }}"
                x-on:click="activeTab = '{{ $tabKey }}'"
                :aria-selected="activeTab === '{{ $tabKey }}' ? 'true' : 'false'"
                class="flex items-center gap-1.5 px-4 py-2.5 rounded-lg text-sm font-medium whitespace-nowrap transition-all duration-200 flex-1 justify-center"
                :class="activeTab === '{{ $tabKey }}'
                    ? 'bg-surface-tertiary text-accent-fg shadow-sm'
                    : 'text-ink-muted hover:text-ink-soft'">
                <x-edz.icon :name="$tab['icon']" class="w-4 h-4 shrink-0" />
                <span>{{ $tab['label'] }}</span>
            </button>
        @endforeach
    </div>
@else
    <nav class="space-y-1" role="tablist" aria-label="{{ __('merchant_panel.storefront_template') }}">
        @foreach ($tabs as $tabKey => $tab)
            <button type="button"
                id="tab-btn-{{ $tabKey }}"
                role="tab"
                aria-controls="tab-panel-{{ $tabKey }}"
                x-on:click="activeTab = '{{ $tabKey }}'"
                :aria-selected="activeTab === '{{ $tabKey }}' ? 'true' : 'false'"
                class="w-full flex items-center gap-3 px-3.5 py-3 rounded-xl text-start transition-all duration-200 group"
                :class="activeTab === '{{ $tabKey }}'
                    ? 'bg-accent-surface-subtle text-accent-fg-strong'
                    : 'text-ink-muted hover:bg-surface-secondary/50 hover:text-ink'">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 transition-colors duration-200"
                     :class="activeTab === '{{ $tabKey }}'
                         ? 'bg-accent-surface-strong'
                         : 'bg-surface-secondary group-hover:bg-surface-tertiary'">
                    <x-edz.icon :name="$tab['icon']" class="w-5 h-5" />
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold leading-tight"
                       :class="activeTab === '{{ $tabKey }}' ? 'text-accent-fg-strong' : ''">
                        {{ $tab['label'] }}
                    </p>
                    <p class="text-[11px] leading-tight mt-0.5 truncate"
                       :class="activeTab === '{{ $tabKey }}' ? 'text-accent-fg/60' : 'text-ink-muted'">
                        {{ $tab['desc'] }}
                    </p>
                </div>
            </button>
        @endforeach
    </nav>
@endif
