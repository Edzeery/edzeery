<?php

use App\Models\Products\Product;
use App\Models\Orders\Order;
use App\Models\Stores\Store;
use Livewire\Volt\Component;

new class extends Component {
    public string $query = '';
    public bool $open = false;
    public array $results = [];

    public function mount(): void
    {
        $this->only(['open', 'query', 'search']);
    }

    public function updatedQuery(): void
    {
        $this->search();
    }

    public function search(): void
    {
        if (strlen($this->query) < 2) {
            $this->results = [];
            return;
        }

        $store = currentStore();
        if (! $store) {
            $this->results = [];
            return;
        }

        $q = $this->query;
        $results = [];

        // Search products
        $products = Product::where('store_id', $store->id)
            ->where('name', 'LIKE', "%{$q}%")
            ->limit(5)
            ->get(['id', 'name', 'slug']);

        foreach ($products as $p) {
            $results[] = [
                'type' => __('merchant_panel.products'),
                'name' => $p->name,
                'url' => route('merchant.products.edit', [$store->slug, $p]),
                'icon' => 'bag-outline',
            ];
        }

        // Search orders
        $orders = Order::where('store_id', $store->id)
            ->where('number', 'LIKE', "%{$q}%")
            ->limit(5)
            ->get(['id', 'number']);

        foreach ($orders as $o) {
            $results[] = [
                'type' => __('merchant_panel.orders'),
                'name' => $o->order_number,
                'url' => route('merchant.orders.show', [$store->slug, $o]),
                'icon' => 'receipt-outline',
            ];
        }

        // Static navigation shortcuts
        $shortcuts = [
            ['type' => __('merchant_panel.dashboard'), 'name' => __('merchant_panel.dashboard'), 'url' => route('merchant.dashboard', $store->slug), 'icon' => 'grid-outline'],
            ['type' => __('merchant_panel.products'), 'name' => __('buttons.create') . ' ' . __('merchant_panel.product'), 'url' => route('merchant.products.create', $store->slug), 'icon' => 'add-circle-outline'],
            ['type' => __('merchant_panel.orders'), 'name' => __('merchant_panel.orders'), 'url' => route('merchant.orders.index', $store->slug), 'icon' => 'receipt-outline'],
            ['type' => __('merchant_panel.settings'), 'name' => __('merchant_panel.settings'), 'url' => route('merchant.settings', $store->slug), 'icon' => 'settings-outline'],
        ];

        foreach ($shortcuts as $s) {
            if (str_contains(strtolower($s['name']), strtolower($q))) {
                $results[] = $s;
            }
        }

        $this->results = array_slice($results, 0, 8);
    }
};
?>

<div x-data="{ get open() { return $wire.open } }"
     x-on:keydown.escape.window="$wire.set('open', false)"
     x-on:keydown.meta.k.window.prevent="$wire.set('open', !open)"
     x-on:keydown.ctrl.k.window.prevent="$wire.set('open', !open)"
     x-on:command-palette-toggle.window="$wire.set('open', !open)"
     x-effect="if (open) { $nextTick(() => $refs.searchInput?.focus()) }">

    {{-- Overlay --}}
    <div x-show="open" x-transition.opacity
         class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm"
         @click="$wire.set('open', false)"
         x-cloak></div>

    {{-- Command Palette Modal --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-x-0 top-[15vh] z-50 mx-auto max-w-xl px-4"
         x-cloak>
        <div class="rounded-2xl bg-surface border border-surface-border shadow-floating overflow-hidden">

            {{-- Search Input --}}
            <div class="flex items-center gap-3 px-4 py-3 border-b border-surface-border">
                <x-edz.icon name="search" class="w-5 h-5 text-ink-muted flex-shrink-0" />
                <input x-ref="searchInput"
                       type="text"
                       wire:model.live.debounce.200ms="query"
                       placeholder="{{ __('buttons.search') }}…"
                       class="flex-1 bg-transparent border-0 outline-none text-sm text-ink placeholder:text-ink-muted" />
                <kbd class="px-1.5 py-0.5 text-[10px] font-mono text-ink-muted bg-surface-secondary border border-surface-border rounded">ESC</kbd>
            </div>

            {{-- Results --}}
            <div class="max-h-80 overflow-y-auto p-2" wire:loading.class="opacity-50">
                @if (empty($results) && strlen($query) >= 2)
                    <div class="px-4 py-8 text-center text-sm text-ink-muted">
                        {{ __('messages.no_results') ?? 'No results found' }}
                    </div>
                @elseif (empty($query))
                    <div class="px-4 py-3 text-xs text-ink-muted">
                        {{ __('messages.search_hint') ?? 'Type to search products, orders, and pages…' }}
                    </div>
                @else
                    @foreach ($results as $i => $result)
                        <a href="{{ $result['url'] }}"
                           wire:navigate
                           @click="$wire.set('open', false)"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-ink
                                  hover:bg-surface-secondary transition-colors duration-150
                                  {{ $i === 0 ? 'bg-surface-secondary' : '' }}">
                            <x-edz.icon :name="$result['icon'] ?? 'search-outline'" class="w-4 h-4 text-ink-muted flex-shrink-0" />
                            <div class="min-w-0 flex-1">
                                <div class="font-medium truncate">{{ $result['name'] }}</div>
                                <div class="text-xs text-ink-muted">{{ $result['type'] }}</div>
                            </div>
                            <x-edz.icon name="arrow-forward-outline" class="w-3.5 h-3.5 text-ink-muted opacity-0 group-hover:opacity-100" />
                        </a>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
