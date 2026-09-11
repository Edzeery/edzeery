{{-- Bulk Tasks dropdown â€” shared with the orders index Volt component via @include.

     Runs in the SAME component instance as index.blade.php (Blade partial, not a
     separate Livewire component), so $this / $wire / parent-defined methods are
     all directly available from here.

     M10: the trigger is disabled (with a visible spinner) while any bulk action
     is in flight so the actions cannot be double-clicked. --}}
{{-- Opts the heavy bulk-delete into the global loading overlay (edzLoader). --}}
<x-edz.loading-target action="bulkDelete" :label="__('merchant.bulk_processing')" />
<div wire:key="bulk-actions-bar" x-data="{ open: false }" @click.away="open = false"
    class="relative shrink-0">

    <button @click="open = !open" type="button"
        class="edz-btn edz-btn--primary edz-btn--sm inline-flex items-center gap-1.5"
        wire:loading.attr="disabled"
        wire:target="bulkAssignAgent,openBulkSendModal,confirmBulkSend,bulkDelete,submitBulkStatus,openBulkValidateModal,confirmBulkValidate">
        <x-edz.spinner class="w-4 h-4" wire:loading wire:target="bulkAssignAgent,openBulkSendModal,confirmBulkSend,bulkDelete,submitBulkStatus,openBulkValidateModal,confirmBulkValidate" wire:key="bulk-spinner" />
        <x-edz.icon name="bars-2" class="w-4 h-4" wire:loading.remove wire:target="bulkAssignAgent,openBulkSendModal,confirmBulkSend,bulkDelete,submitBulkStatus,openBulkValidateModal,confirmBulkValidate" />
        <span>{{ __('merchant.bulk_tasks') }}</span>
        <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-accent-fg text-accent text-[10px] font-bold tabular-nums">
            {{ count($this->selectedOrders) }}
        </span>
        <x-edz.icon name="chevron-down" class="w-3 h-3 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
    </button>

    {{-- Mobile backdrop --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-[205] bg-black/40 backdrop-blur-sm sm:hidden"
        @click="open = false"></div>

    {{-- Bottom sheet on mobile, anchored dropdown on sm+ --}}
    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-3"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-3"
        class="fixed inset-x-0 bottom-0 z-[210] w-full rounded-t-2xl border border-b-0 border-surface-border bg-surface
               p-3 pb-[calc(1rem+env(safe-area-inset-bottom))] shadow-[0_-16px_48px_-12px_rgba(15,23,42,.25)]
               max-h-[70vh] overflow-y-auto edz-scroll
               sm:absolute sm:inset-x-auto sm:bottom-auto sm:left-0 sm:top-full sm:mt-1 sm:z-50 sm:w-72
               sm:rounded-xl sm:border-b sm:p-2 sm:shadow-lg sm:max-h-96"
        wire:loading.attr="disabled"
        wire:target="bulkAssignAgent,openBulkSendModal,confirmBulkSend,bulkDelete,submitBulkStatus,openBulkValidateModal,confirmBulkValidate">
        <span class="pointer-events-none mx-auto mb-2 block h-1 w-10 rounded-full bg-surface-border sm:hidden"></span>
        <div class="flex items-center justify-between gap-2 px-1 mb-1.5 sm:hidden">
            <p class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink uppercase tracking-wide">
                <x-edz.icon name="list-bullet" class="w-3.5 h-3.5 text-ink-muted" />
                <span>{{ __('merchant.bulk_tasks') }}</span>
            </p>
            <button @click="open = false" type="button"
                class="-m-1 p-1 rounded-lg text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                title="{{ __('general.close') }}">
                <x-edz.icon name="x-mark" class="w-4 h-4" />
            </button>
        </div>

        <p class="px-2.5 pb-1.5 text-xs text-ink-muted">
            {{ count($this->selectedOrders) }} {{ __('merchant.orders_count') }}
        </p>

        {{-- Assign agent --}}
        <p class="px-2.5 pt-1 pb-0.5 text-[11px] font-semibold uppercase tracking-wide text-ink-muted">
            {{ __('merchant.bulk_assign_agent') }}
        </p>
        <div class="px-2.5 pb-1.5">
            <x-edz.select wire:model="bulkAssignMembershipId"
                :options="$this->allMembers" option-value="id" option-label="user.name" search
                placeholder="{{ __('merchant_panel.select_agent') }}" size="sm" />
            <button wire:click="bulkAssignAgent($this->bulkAssignMembershipId)" type="button"
                class="mt-1.5 w-full edz-btn edz-btn--accent edz-btn--sm justify-center"
                wire:loading.attr="disabled" wire:target="bulkAssignAgent">
                <x-edz.icon name="user" class="w-3.5 h-3.5" />
                <span>{{ __('buttons.apply') }}</span>
            </button>
        </div>

        {{-- Send to carrier (P29.3) / change status (P29) --}}
        @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
            <p class="px-2.5 pt-2 pb-0.5 text-[11px] font-semibold uppercase tracking-wide text-ink-muted">
                {{ __('order_flow.bulk_status_title') }}
            </p>
            <button wire:click="openBulkSendModal" @click="open = false" type="button"
                class="w-full flex items-center gap-2 px-2.5 min-h-[44px] rounded-lg text-sm hover:bg-surface-secondary disabled:opacity-50"
                wire:loading.attr="disabled" wire:target="openBulkSendModal,confirmBulkSend">
                <x-edz.icon name="truck" class="w-4 h-4 shrink-0 text-ink-muted" />
                <span>{{ __('merchant.bulk_send_carrier') }}</span>
            </button>
            <button wire:click="openBulkStatusModal" @click="open = false" type="button"
                class="w-full flex items-center gap-2 px-2.5 min-h-[44px] rounded-lg text-sm hover:bg-surface-secondary disabled:opacity-50"
                wire:loading.attr="disabled" wire:target="openBulkStatusModal,submitBulkStatus">
                <x-edz.icon name="adjustments-horizontal" class="w-4 h-4 shrink-0 text-ink-muted" />
                <span>{{ __('order_flow.bulk_status_title') }}</span>
            </button>
        @endif

        {{-- Validate at carrier (Phase 36) â€” dispatch handover, own permission --}}
        @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_DISPATCH_VALIDATE->value))
            <button wire:click="openBulkValidateModal" @click="open = false" type="button"
                class="w-full flex items-center gap-2 px-2.5 min-h-[44px] rounded-lg text-sm hover:bg-surface-secondary disabled:opacity-50"
                wire:loading.attr="disabled" wire:target="openBulkValidateModal,confirmBulkValidate">
                <x-edz.icon name="checkmark-circle" class="w-4 h-4 shrink-0 text-ink-muted" />
                <span>{{ __('order_flow.bulk_validate_btn') }}</span>
            </button>
        @endif

        <div class="my-1.5 border-t border-surface-border"></div>

        {{-- Delete (client-side confirm via EdzSwal, then calls $wire.bulkDelete) --}}
        <button x-data="{ isLoading: false }" type="button"
            x-on:click.prevent="(async () => { if (!isLoading && await EdzSwal.confirmDelete()) { isLoading = true; open = false; await $wire.bulkDelete(); isLoading = false; } })()"
            :disabled="isLoading"
            class="w-full flex items-center gap-2 px-2.5 min-h-[44px] rounded-lg text-sm text-danger-600 disabled:opacity-50">
            <x-edz.spinner show="isLoading" class="w-4 h-4 text-danger-600" />
            <x-edz.icon name="trash" class="w-4 h-4 shrink-0" x-show="!isLoading" />
            <span x-show="!isLoading">{{ __('merchant.bulk_delete') }}</span>
        </button>

        <button wire:click="clearSelection" @click="open = false" type="button"
            class="w-full flex items-center gap-2 px-2.5 min-h-[44px] rounded-lg text-sm text-ink-muted hover:bg-surface-secondary">
            <x-edz.icon name="x-mark" class="w-4 h-4 shrink-0" />
            <span>{{ __('merchant.bulk_clear') }}</span>
        </button>
    </div>
</div>