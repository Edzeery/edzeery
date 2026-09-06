{{-- Full order event-log popup (P29.4), opened from the row dropdown's "show more". --}}
@if ($this->eventsFullOrderId)
    <x-edz.modal :is-open="$this->eventsFullOrderId !== null" @close="$wire.closeOrderEventsModal()"
        size="md" wire:key="order-events-modal-{{ $this->eventsFullOrderId }}">
        <div class="p-6">
            @if ($this->eventsFullLabel)
                <p class="text-xs font-semibold text-ink-muted mb-3">#{{ $this->eventsFullLabel }}</p>
            @endif
            @include('livewire.merchant.orders.partials.order-events-timeline', [
                'events' => $this->eventsFull,
            ])
        </div>
    </x-edz.modal>
@endif