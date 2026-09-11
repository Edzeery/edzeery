{{-- Carrier-notes popup (notes column) — history + composer, reusing the drawer's
    sendCarrierNote() API so merchant notes travel through the carrier integration. --}}

@if ($this->shipmentNotesFor && $this->shipmentNotesMeta)
    <div class="fixed inset-0 z-[220] flex items-end sm:items-center justify-center"
        @edz-modal-closed.window="$wire.closeShipmentNotes()">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeShipmentNotes"></div>
        <div class="relative w-full sm:max-w-md max-h-[85vh] overflow-y-auto edz-scroll rounded-t-2xl sm:rounded-2xl
            border border-surface-border bg-surface shadow-xl p-5"
            @keydown.escape.window="$wire.closeShipmentNotes()">
            <div class="flex items-start justify-between gap-2 mb-4">
                <div>
                    <h3 class="text-lg font-bold text-ink">{{ __('order_flow.carrier_notes') }}</h3>
                    <p class="text-xs text-ink-muted mt-0.5">
                        #{{ $this->shipmentNotesMeta['number'] }} —
                        <span dir="ltr" class="font-mono">{{ $this->shipmentNotesMeta['tracking_number'] }}</span>
                    </p>
                </div>
                <button type="button" wire:click="closeShipmentNotes" class="edz-modal__close" style="position:static;">
                    <x-edz.icon name="x-mark" class="w-5 h-5" />
                </button>
            </div>

            <div class="rounded-xl border border-surface-border bg-surface-tertiary/30 p-3">
                <textarea wire:model="noteDraft" rows="2" maxlength="255"
                    class="edz-input text-sm w-full resize-none"
                    placeholder="{{ __('order_flow.note_placeholder') }}"></textarea>
                <div class="mt-2 flex items-center justify-between gap-2">
                    <span class="text-xs text-ink-muted">
                        {{ strlen($this->noteDraft) }}/255
                        @error('noteDraft')
                            <span class="text-danger-500 ms-2">{{ $message }}</span>
                        @enderror
                    </span>
                    <button type="button" wire:click="sendCarrierNote('{{ $this->shipmentNotesMeta['tracking_id'] }}')"
                        :disabled="{{ $this->noteDraft === '' ? 'true' : 'false' }}"
                        wire:loading.attr="disabled" wire:loading.class="opacity-50 pointer-events-none"
                        wire:target="sendCarrierNote"
                        class="edz-btn edz-btn--primary edz-btn--sm">
                        <x-edz.spinner wire:target="sendCarrierNote" />
                        <span wire:loading.remove wire:target="sendCarrierNote">
                            <x-edz.icon name="paper-airplane" class="w-4 h-4" />
                        </span>
                        {{ __('buttons.send') }}
                    </button>
                </div>
            </div>

            @if (($this->shipmentNotesMeta['carrier_supports_api_notes'] ?? false) === false)
                <p class="text-xs text-warning-500 mt-2">{{ __('order_flow.carrier_notes_unsupported') }}</p>
            @endif

            <div class="mt-4 space-y-2 max-h-72 overflow-y-auto edz-scroll">
                @forelse ($this->shipmentNotes as $note)
                    <div class="rounded-xl border border-surface-border p-3">
                        <p class="text-sm text-ink whitespace-pre-wrap break-words">{{ $note['notes'] }}</p>
                        <p class="mt-1.5 text-[11px] text-ink-muted flex items-center gap-1.5">
                            <x-edz.icon name="user" class="w-3 h-3" />
                            {{ $note['by'] ?: '—' }}
                            <span>•</span>
                            {{ $note['created_at']?->diffForHumans() }}
                        </p>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center text-sm text-ink-muted">
                        <x-edz.icon name="chat-bubble-left-right" class="w-8 h-8 mx-auto mb-2 text-ink-muted/40" />
                        {{ __('order_flow.no_carrier_notes') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endif