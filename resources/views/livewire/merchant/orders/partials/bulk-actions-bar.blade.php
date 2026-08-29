{{-- Bulk Actions Bar — shared with the orders index Volt component via @include.

     Runs in the SAME component instance as index.blade.php (Blade partial, not a
     separate Livewire component), so $this / $wire / parent-defined methods are
     all directly available from here.

     M10: the whole bar is disabled while any bulk action is in flight and shows a
     visible <x-edz.spinner /> so the action buttons cannot be double-clicked. --}}
{{-- Opts the heavy bulk-delete into the global loading overlay (edzLoader). --}}
<x-edz.loading-target action="bulkDelete" :label="__('merchant.bulk_processing')" />
<div wire:key="bulk-actions-bar"
    class="relative mb-4 p-3 bg-accent-50 dark:bg-accent-900/20 border border-accent-200 dark:border-accent-700 rounded-xl flex items-center justify-between sticky top-0 z-30"
    wire:loading.attr="disabled"
    wire:loading.class="opacity-60 pointer-events-none cursor-not-allowed"
    wire:target="bulkAssignAgent,bulkSendToCarrier,bulkDelete">

    {{-- Visible execution indicator + spinner (M10) --}}
    <div wire:loading wire:target="bulkAssignAgent,bulkSendToCarrier,bulkDelete"
        x-cloak
        class="absolute inset-0 z-20 flex items-center justify-center gap-2 bg-accent-50/80 dark:bg-accent-900/40 rounded-xl">
        <x-edz.spinner class="w-5 h-5 text-accent-600 dark:text-accent-300" />
        <span class="text-xs font-semibold text-accent-700 dark:text-accent-200">{{ __('merchant.bulk_processing') }}</span>
    </div>

    <span class="text-sm text-accent-700 dark:text-accent-400 font-medium">
        {{ count($this->selectedOrders) }} {{ __('merchant.orders_count') }}
    </span>
    <div class="flex gap-2 flex-wrap">
        {{-- Assign agent --}}
        <div x-data="{ open: false }" @click.away="open = false" class="relative">
            <button @click="open = !open" class="edz-btn edz-btn--ghost edz-btn--sm"
                wire:loading.attr="disabled" wire:target="bulkAssignAgent">
                <svg x-cloak wire:loading wire:target="bulkAssignAgent" class="edz-spinner w-4 h-4"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path
                        d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                </svg>
                <x-edz.icon name="user-plus" wire:loading.remove wire:target="bulkAssignAgent" class="w-4 h-4" />
                <span wire:loading.remove wire:target="bulkAssignAgent">{{ __('merchant.bulk_assign_agent') }}</span>
            </button>
            <div x-show="open" x-transition
                class="absolute z-50 right-0 mt-1 w-56 bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5 max-h-60 overflow-y-auto edz-scroll">
                @foreach ($this->allMembers as $m)
                    <button wire:click="bulkAssignAgent('{{ $m['id'] }}')"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary disabled:opacity-50"
                        wire:loading.attr="disabled" wire:target="bulkAssignAgent">
                        {{ $m['user']['name'] }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Send to carrier --}}
        @if (count($this->allProviders) > 0)
            <div x-data="{ open: false }" @click.away="open = false" class="relative">
                <button @click="open = !open" class="edz-btn edz-btn--ghost edz-btn--sm"
                    wire:loading.attr="disabled" wire:target="bulkSendToCarrier">
                    <svg x-cloak wire:loading wire:target="bulkSendToCarrier" class="edz-spinner w-4 h-4"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                    </svg>
                    <x-edz.icon name="truck" wire:loading.remove wire:target="bulkSendToCarrier" class="w-4 h-4" />
                    <span wire:loading.remove wire:target="bulkSendToCarrier">{{ __('merchant.bulk_send_carrier') }}</span>
                </button>
                <div x-show="open" x-transition
                    class="absolute z-50 right-0 mt-1 w-56 bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5">
                    @foreach ($this->allProviders as $pr)
                        <button wire:click="bulkSendToCarrier('{{ $pr['id'] }}')"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary disabled:opacity-50"
                            wire:loading.attr="disabled" wire:target="bulkSendToCarrier">
                            {{ $pr['name'] }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Delete (client-side confirm via EdzSwal, then calls $wire.bulkDelete) --}}
        <button x-data="{ isLoading: false }"
            x-on:click.prevent="(async () => { if (!isLoading && await EdzSwal.confirmDelete()) { isLoading = true; await $wire.bulkDelete(); isLoading = false; } })()"
            :disabled="isLoading"
            class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 disabled:opacity-50">
            <svg x-show="isLoading" x-cloak class="edz-spinner w-4 h-4" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <path
                    d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
            </svg>
            <x-edz.icon name="trash" class="w-4 h-4" x-show="!isLoading" />
            <span x-show="!isLoading">{{ __('merchant.bulk_delete') }}</span>
        </button>

        <button wire:click="clearSelection" class="edz-btn edz-btn--ghost edz-btn--sm">
            <x-edz.icon name="x-mark" class="w-4 h-4" />
        </button>
    </div>
</div>
