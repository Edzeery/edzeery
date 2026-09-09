{{-- Carrier note composer (P33.2) — own dedicated section, rendered between Quick actions and Tracking history. --}}
@if (
    canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value)
    && !empty($this->drawerTracking['tracking_id'])
    && ($this->drawerTracking['carrier_supports_api_notes'] ?? false)
)
    <section class="mt-5">
        <h4 class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-2">
            <x-edz.icon name="paper-airplane" class="w-4 h-4" />
            {{ __('order_flow.carrier_note_section') }}
        </h4>
        <div class="rounded-xl border border-surface-border bg-surface-tertiary/30 p-3"
            x-data="{ noteCount: 0 }">
            <textarea wire:model="noteDraft" rows="2" maxlength="255"
                x-init="noteCount = $el.value.length"
                x-on:input="noteCount = $el.value.length"
                class="edz-input text-sm w-full resize-none"
                placeholder="{{ __('order_flow.note_placeholder') }}"></textarea>
            <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
                <p class="text-xs text-ink-muted shrink-0">
                    <span class="tabular-nums"><span x-text="noteCount"></span> / 255</span>
                    @error('noteDraft')
                        <span class="text-danger-500 ms-2">{{ $message }}</span>
                    @enderror
                </p>
                <button type="button"
                    wire:click="sendCarrierNote('{{ $this->drawerTracking['tracking_id'] }}')"
                    :disabled="noteCount === 0"
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
    </section>
@endif