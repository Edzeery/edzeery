@props([
    'tabs',
    'variant' => 'mobile', // mobile: horizontal pills | desktop: vertical nav
])

@if ($variant === 'mobile')
    <div class="flex gap-1 p-1 bg-gray-100 dark:bg-gray-800 rounded-xl overflow-x-auto" role="tablist"
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
                    ? 'bg-white dark:bg-gray-700 text-accent-600 dark:text-accent-400 shadow-sm'
                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'">
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
                    ? 'bg-accent-50 dark:bg-accent-900/15 text-accent-700 dark:text-accent-300'
                    : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-gray-200'">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 transition-colors duration-200"
                     :class="activeTab === '{{ $tabKey }}'
                         ? 'bg-accent-100 dark:bg-accent-800/40'
                         : 'bg-gray-100 dark:bg-gray-800 group-hover:bg-gray-200 dark:group-hover:bg-gray-700'">
                    <x-edz.icon :name="$tab['icon']" class="w-5 h-5" />
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold leading-tight"
                       :class="activeTab === '{{ $tabKey }}' ? 'text-accent-800 dark:text-accent-200' : ''">
                        {{ $tab['label'] }}
                    </p>
                    <p class="text-[11px] leading-tight mt-0.5 truncate"
                       :class="activeTab === '{{ $tabKey }}' ? 'text-accent-500/70 dark:text-accent-400/60' : 'text-gray-400 dark:text-gray-500'">
                        {{ $tab['desc'] }}
                    </p>
                </div>
            </button>
        @endforeach
    </nav>
@endif
