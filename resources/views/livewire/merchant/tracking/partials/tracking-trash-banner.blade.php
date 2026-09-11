{{-- Trash-mode banner (shared by the carrier & rider tabs): explains that the grid
    below shows that tab's soft-deleted orders, with restore-all / empty-trash actions,
    both gated to ORDER_DELETE. Uses the semantic danger tokens so it adapts to dark mode. --}}
<div class="edz-card edz-card--padded mb-4 border-danger-border bg-danger-surface">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-sm text-danger-fg">
            <x-edz.icon name="trash" class="w-5 h-5" />
            <span>{{ __('order_flow.trash_mode_hint') }}</span>
            @if ($this->trashCount > 0)
                <span
                    class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-[10px] font-semibold bg-danger-500 text-white leading-none">{{ $this->trashCount }}</span>
            @endif
        </div>
        @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_DELETE->value) && $this->trashCount > 0)
            <div class="flex items-center gap-2">
                <button wire:click="restoreAll" class="edz-btn edz-btn--ghost edz-btn--sm"
                    wire:loading.attr="disabled">
                    <x-edz.icon name="arrow-uturn-left" class="w-4 h-4" />
                    {{ __('merchant.restore_all') }}
                </button>
                <button
                    x-on:click="EdzSwal.confirmAction('{{ __('order_flow.empty_trash_title') }}', '{{ __('order_flow.empty_trash_confirm', ['count' => $this->trashCount]) }}', { confirmText: '{{ __('merchant.empty_trash') }}', confirmColor: '#ef4444' }).then((ok) => { if (ok) $wire.forceDeleteAll(); })"
                    class="edz-btn edz-btn--sm edz-btn--danger">
                    <x-edz.icon name="trash" class="w-4 h-4" />
                    {{ __('merchant.empty_trash') }}
                </button>
            </div>
        @endif
    </div>
</div>