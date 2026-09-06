{{-- Tracking-status popup (P29.4): opened by clicking the tracking-status badge in the tracking table/cards. edz-modal renders it as a centered card on sm+ and a bottom sheet on phones, so the sheet affordances (handle, tight padding) are mobile-only. --}}
@if ($this->statusHistoryFor)
    <x-edz.modal :is-open="$this->statusHistoryFor !== null" @close="$wire.closeStatusHistory()"
        size="md" wire:key="tracking-status-popup-{{ $this->statusHistoryFor }}">
        <div class="p-4 pt-1 sm:p-6 sm:pt-5">
            <span class="edz-modal__handle" aria-hidden="true"></span>
            @if (!empty($this->statusHistoryMeta))
                <p class="mt-2 sm:mt-1 mb-4 flex items-baseline gap-2 flex-wrap text-base font-bold text-ink">
                    <span class="tabular-nums">#{{ $this->statusHistoryMeta['number'] }}</span>
                    @if (!empty($this->statusHistoryMeta['tracking_number']))
                        <span class="text-xs font-medium text-ink-muted tabular-nums">
                            {{ $this->statusHistoryMeta['tracking_number'] }}
                        </span>
                    @endif
                </p>
            @endif
            @include('livewire.merchant.tracking.partials.tracking-history-timeline', [
                'histories' => $this->statusHistory,
            ])
        </div>
    </x-edz.modal>
@endif