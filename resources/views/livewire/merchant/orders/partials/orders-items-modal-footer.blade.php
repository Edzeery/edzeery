{{-- 31.9 — Shared items-edit modal footer (cancel / save + inline error). --}}
<div class="flex items-center gap-2 pt-1">
    <div class="flex-1 min-w-0">
        @if ($this->editingError)
            <p class="text-xs text-danger-600">{{ $this->editingError }}</p>
        @endif
    </div>
    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm" wire:click="closeItemsModal">
        {{ __('buttons.cancel') }}
    </button>
    <button type="button" class="edz-btn edz-btn--primary edz-btn--sm" wire:click="saveOrderItems"
        wire:loading.attr="disabled" wire:loading.class="opacity-50 pointer-events-none">
        <span wire:loading.remove wire:target="saveOrderItems">{{ __('buttons.save') }}</span>
        <span wire:loading wire:target="saveOrderItems" class="inline-flex items-center gap-1.5">
            <x-edz.spinner class="w-3.5 h-3.5" />
            {{ __('buttons.processing') }}
        </span>
    </button>
</div>