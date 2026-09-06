{{-- Tracking-status popup (P29.4): opened by clicking the tracking-status badge in the tracking table/cards. --}}
@if ($this->statusHistoryFor)
    <x-edz.modal :is-open="$this->statusHistoryFor !== null" @close="$wire.closeStatusHistory()"
        size="md" wire:key="tracking-status-popup-{{ $this->statusHistoryFor }}">
        <div class="p-6">
            @if (!empty($this->statusHistoryMeta))
                <p class="text-xs font-semibold text-ink-muted mb-3">
                    #{{ $this->statusHistoryMeta['number'] }}
                    @if (!empty($this->statusHistoryMeta['tracking_number']))
                        • {{ $this->statusHistoryMeta['tracking_number'] }}
                    @endif
                </p>
            @endif
            @include('livewire.merchant.tracking.partials.tracking-history-timeline', [
                'histories' => $this->statusHistory,
            ])
        </div>
    </x-edz.modal>
@endif