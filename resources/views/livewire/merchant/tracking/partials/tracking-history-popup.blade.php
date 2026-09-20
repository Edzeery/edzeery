{{-- Tracking-status popup (P29.4 + Phase 7 stepper): opened by clicking the tracking-status badge in the tracking table/cards. Shows the carrier-stage stepper, optional carrier-note composer, then the shared tracking-history timeline. edz-modal renders it as a centered card on sm+ and a bottom sheet on phones, so the sheet affordances (handle, tight padding) are mobile-only. --}}
@if ($this->statusHistoryFor)
    <x-edz.modal :is-open="$this->statusHistoryFor !== null" @edz-modal-closed="$event.target === $event.currentTarget && $wire.closeStatusHistory()"
        size="md" wire:key="tracking-status-popup-{{ $this->statusHistoryFor }}">
        <div class="p-4 pt-1 sm:p-6 sm:pt-5">
            <span class="edz-modal__handle" aria-hidden="true"></span>
            @if (!empty($this->statusHistoryMeta))
                <p class="mt-2 sm:mt-1 mb-4 flex items-center gap-2 flex-wrap text-base font-bold text-ink">
                    <span class="tabular-nums">#{{ $this->statusHistoryMeta['number'] }}</span>
                    @if (!empty($this->statusHistoryMeta['tracking_number']))
                        <span class="text-xs font-medium text-ink-muted tabular-nums flex items-center gap-1.5">
                            {{ $this->statusHistoryMeta['tracking_number'] }}
                            @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_VIEW->value))
                                <button
                                    x-on:click="navigator.clipboard.writeText('{{ $this->statusHistoryMeta['tracking_number'] }}').then(() => EdzSwal.success('', '{{ __('order_flow.copy_done') }}'))"
                                    class="text-accent-600 hover:text-accent-700"
                                    title="{{ __('order_flow.tracking_number_copy') }}">
                                    <x-edz.icon name="clipboard" class="w-3 h-3 inline-block" />
                                </button>
                            @endif
                        </span>
                    @endif
                </p>
            @endif

            {{-- Phase 7: horizontal carrier-stage stepper --}}
            @include('livewire.merchant.tracking.partials.tracking-status-stepper')

            @if (!empty($this->statusHistoryMeta['public_tracking_url']))
                <a href="{{ $this->statusHistoryMeta['public_tracking_url'] }}" target="_blank" rel="noopener noreferrer"
                    class="inline-flex items-center gap-1 text-xs font-medium text-accent-600 hover:text-accent-700">
                    <x-edz.icon name="external-link" class="w-3.5 h-3.5" />
                    {{ __('order_flow.carrier_track_on_site') }}
                </a>
            @endif

            {{-- Carrier note composer now lives with the tracking history (Phase 7) --}}
            @include('livewire.merchant.tracking.partials.carrier-note-composer')

            @include('livewire.merchant.tracking.partials.tracking-history-timeline', [
                'histories' => $this->statusHistory,
            ])
        </div>
    </x-edz.modal>
@endif