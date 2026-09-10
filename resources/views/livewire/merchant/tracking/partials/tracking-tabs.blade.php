{{-- Carrier / rider tabs (Phase A) — mirror the returns tab pattern (edz-btn primary/ghost). --}}
<div class="mb-4 flex gap-2">
    <button
        wire:click="$set('trackingTab', 'carrier')"
        class="edz-btn {{ $this->trackingTab === 'carrier' ? 'edz-btn--primary' : 'edz-btn--ghost' }}"
    >
        {{ __('order_flow.tracking_tab_carrier') }}
    </button>
    <button
        wire:click="$set('trackingTab', 'rider')"
        class="edz-btn {{ $this->trackingTab === 'rider' ? 'edz-btn--primary' : 'edz-btn--ghost' }}"
    >
        {{ __('order_flow.tracking_tab_rider') }}
    </button>
</div>