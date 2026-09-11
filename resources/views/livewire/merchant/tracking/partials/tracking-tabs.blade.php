{{-- Carrier / rider tabs (Phase A) — mirror the returns tab pattern (edz-btn primary/ghost).
    The active tab is remembered in browser storage (edz-tracking-active-tab) so a page
    refresh restores the last opened tab without any server-side state.

    Each tab owns its own trash bin on the OPPOSITE side of the tab buttons: while the
    active list is showing the button reads "Trash (+count)", and while the tab's trash
    is showing it swaps to "Back" so the merchant returns to the active list. --}}
<div
    class="mb-4 flex flex-wrap items-center gap-2"
    x-data="{
        persistTab(name) {
            localStorage.setItem('edz-tracking-active-tab', name);
        },
        restoreTab() {
            const saved = localStorage.getItem('edz-tracking-active-tab');
            if (saved && saved !== @js($this->trackingTab)) {
                $wire.set('trackingTab', saved);
            }
        },
    }"
    x-init="restoreTab()"
>
    <button
        wire:click="$set('trackingTab', 'carrier')"
        @click="persistTab('carrier')"
        class="edz-btn {{ $this->trackingTab === 'carrier' ? 'edz-btn--primary' : 'edz-btn--ghost' }}"
    >
        {{ __('order_flow.tracking_tab_carrier') }}
    </button>
    <button
        wire:click="$set('trackingTab', 'rider')"
        @click="persistTab('rider')"
        class="edz-btn {{ $this->trackingTab === 'rider' ? 'edz-btn--primary' : 'edz-btn--ghost' }}"
    >
        {{ __('order_flow.tracking_tab_rider') }}
    </button>

    @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_DELETE->value))
        <x-edz.tooltip class="ms-auto"
            label="{{ $this->showTrash ? __('order_flow.back_from_trash') : __('merchant.trash_bin') }}">
            <button
                wire:click="toggleTrash"
                class="edz-btn edz-btn--ghost edz-btn--sm {{ $this->showTrash ? 'text-accent-600' : '' }}"
                wire:loading.attr="disabled"
            >
                @if ($this->showTrash)
                    <x-edz.icon name="arrow-left" class="w-4 h-4" />
                    <span class="hidden sm:inline">{{ __('order_flow.back_from_trash') }}</span>
                @else
                    <x-edz.icon name="trash" class="w-4 h-4" />
                    <span class="hidden sm:inline">{{ __('merchant.trash_bin') }}</span>
                    @if ($this->trashCount > 0)
                        <span
                            class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-[10px] font-semibold bg-danger-500 text-white leading-none">
                            {{ $this->trashCount }}
                        </span>
                    @endif
                @endif
            </button>
        </x-edz.tooltip>
    @endif
</div>