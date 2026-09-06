{{-- Bulk Actions Bar — shared with the orders index Volt component via @include.

     Runs in the SAME component instance as index.blade.php (Blade partial, not a
     separate Livewire component), so $this / $wire / parent-defined methods are
     all directly available from here.

     M10: the whole bar is disabled while any bulk action is in flight and shows a
     visible <x-edz.spinner /> so the action buttons cannot be double-clicked. --}}
{{-- Opts the heavy bulk-delete into the global loading overlay (edzLoader). --}}
<x-edz.loading-target action="bulkDelete" :label="__('merchant.bulk_processing')" />
<div wire:key="bulk-actions-bar"
    class="relative mb-4 p-3 bg-accent-surface border border-accent-border rounded-xl flex items-center justify-between sticky top-0 z-30"
    wire:loading.attr="disabled"
    wire:loading.class="opacity-60 pointer-events-none cursor-not-allowed"
    wire:target="bulkAssignAgent,openBulkSendModal,confirmBulkSend,bulkDelete,submitBulkStatus">

    {{-- Visible execution indicator + spinner (M10) --}}
    <div wire:loading wire:target="bulkAssignAgent,openBulkSendModal,confirmBulkSend,bulkDelete,submitBulkStatus"
        x-cloak
        class="absolute inset-0 z-20 flex items-center justify-center gap-2 bg-accent-surface-strong rounded-xl">
        <x-edz.spinner class="w-5 h-5 text-accent-fg" />
        <span class="text-xs font-semibold text-accent-fg-strong">{{ __('merchant.bulk_processing') }}</span>
    </div>

    <span class="text-sm text-accent-fg font-medium">
        {{ count($this->selectedOrders) }} {{ __('merchant.orders_count') }}
    </span>
    <div class="flex gap-2 flex-wrap">
        {{-- Assign agent --}}
        <div x-data="{ open: false }" @click.away="open = false" class="relative">
            <button @click="open = !open" class="edz-btn edz-btn--ghost edz-btn--sm"
                wire:loading.attr="disabled" wire:target="bulkAssignAgent">
                <x-edz.spinner wire:target="bulkAssignAgent" class="w-4 h-4" />
                <x-edz.icon name="user-plus" wire:loading.remove wire:target="bulkAssignAgent" class="w-4 h-4" />
                <span wire:loading.remove wire:target="bulkAssignAgent">{{ __('merchant.bulk_assign_agent') }}</span>
            </button>
            <div x-show="open" x-cloak
                class="fixed inset-0 z-[205] bg-black/40 backdrop-blur-sm sm:hidden" @click="open = false"></div>
            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-3"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-3"
                class="fixed inset-x-0 bottom-0 z-[210] w-full rounded-t-2xl border border-b-0 border-surface-border bg-surface
                       p-3 pb-[calc(1rem+env(safe-area-inset-bottom))] shadow-[0_-16px_48px_-12px_rgba(15,23,42,.25)]
                       max-h-[70vh] overflow-y-auto edz-scroll
                       sm:absolute sm:inset-x-auto sm:bottom-auto sm:right-0 sm:top-full sm:mt-1 sm:z-50 sm:w-56
                       sm:rounded-xl sm:border-b sm:p-1.5 sm:shadow-lg sm:max-h-60">
                <span class="pointer-events-none mx-auto mb-2 block h-1 w-10 rounded-full bg-surface-border sm:hidden"></span>
                <div class="flex items-center justify-between gap-2 px-1 mb-1.5 sm:hidden">
                    <p class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink uppercase tracking-wide">
                        <x-edz.icon name="user" class="w-3.5 h-3.5 text-ink-muted" />
                        <span>{{ __('merchant.bulk_assign_agent') }}</span>
                    </p>
                    <button @click="open = false" type="button"
                        class="-m-1 p-1 rounded-lg text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                        title="{{ __('general.close') }}">
                        <x-edz.icon name="x-mark" class="w-4 h-4" />
                    </button>
                </div>
                @foreach ($this->allMembers as $m)
                    <button wire:click="bulkAssignAgent('{{ $m['id'] }}')"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary disabled:opacity-50"
                        wire:loading.attr="disabled" wire:target="bulkAssignAgent">
                        {{ $m['user']['name'] }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Send to carrier (P29.3): opens a confirmation modal grouped by
             each selected order's own carrier (provider, fallback rider). --}}
        @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
            <button wire:click="openBulkSendModal" class="edz-btn edz-btn--ghost edz-btn--sm"
                wire:loading.attr="disabled" wire:target="openBulkSendModal,confirmBulkSend">
                <x-edz.spinner wire:target="confirmBulkSend" class="w-4 h-4" />
                <x-edz.icon name="truck" wire:loading.remove wire:target="confirmBulkSend" class="w-4 h-4" />
                <span wire:loading.remove wire:target="confirmBulkSend">{{ __('merchant.bulk_send_carrier') }}</span>
            </button>
        @endif

        {{-- Change status (P29) --}}
        @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
            <button wire:click="openBulkStatusModal" class="edz-btn edz-btn--ghost edz-btn--sm"
                wire:loading.attr="disabled" wire:target="submitBulkStatus,openBulkStatusModal">
                <x-edz.spinner wire:target="submitBulkStatus" class="w-4 h-4" />
                <x-edz.icon name="adjustments-horizontal" wire:loading.remove
                    wire:target="submitBulkStatus" class="w-4 h-4" />
                <span wire:loading.remove wire:target="submitBulkStatus">{{ __('order_flow.bulk_status_title') }}</span>
            </button>
        @endif

        {{-- Delete (client-side confirm via EdzSwal, then calls $wire.bulkDelete) --}}
        <button x-data="{ isLoading: false }"
            x-on:click.prevent="(async () => { if (!isLoading && await EdzSwal.confirmDelete()) { isLoading = true; await $wire.bulkDelete(); isLoading = false; } })()"
            :disabled="isLoading"
            class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 disabled:opacity-50">
            <x-edz.spinner show="isLoading" class="w-4 h-4" />
            <x-edz.icon name="trash" class="w-4 h-4" x-show="!isLoading" />
            <span x-show="!isLoading">{{ __('merchant.bulk_delete') }}</span>
        </button>

        <button wire:click="clearSelection" class="edz-btn edz-btn--ghost edz-btn--sm">
            <x-edz.icon name="x-mark" class="w-4 h-4" />
        </button>
    </div>
</div>
