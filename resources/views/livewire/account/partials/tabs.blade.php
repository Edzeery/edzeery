{{-- Account sub-navigation: pill segmented control between account sections. --}}
@php
    $tabs = [
        ['key' => 'profile',    'route' => 'account.profile',       'icon' => 'user',        'label' => __('merchant_panel.profile')],
        ['key' => 'security',   'route' => 'account.security',      'icon' => 'lock-closed', 'label' => __('account.security')],
        ['key' => 'preferences','route' => 'account.personal-data','icon' => 'adjustments', 'label' => __('merchant_panel.preferences')],
        ['key' => 'billing',    'route' => 'account.billing',       'icon' => 'credit-card', 'label' => __('merchant_panel.billing')],
    ];
@endphp

<nav class="mb-6" aria-label="{{ __('account.tabs_label') }}">
    <div class="flex gap-1 p-1 rounded-2xl border border-surface-border bg-surface-secondary/70 overflow-x-auto edz-scroll snap-x">
        @foreach ($tabs as $tab)
            @php $active = request()->routeIs($tab['route']); @endphp
            <a href="{{ route($tab['route']) }}" wire:navigate
               @if ($active) aria-current="page" @endif
               class="flex flex-1 sm:flex-none items-center justify-center gap-2 whitespace-nowrap snap-start rounded-xl px-3 py-2 text-xs font-semibold transition-all duration-200 sm:px-5 sm:py-2.5 sm:text-sm
                      {{ $active
                          ? 'bg-surface text-ink shadow-sm'
                          : 'text-ink-muted hover:bg-surface/60 hover:text-ink' }}">
                <x-edz.icon :name="$tab['icon']" class="h-4 w-4 shrink-0 {{ $active ? 'text-accent-600' : 'text-ink-muted' }}" />
                <span>{{ $tab['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
