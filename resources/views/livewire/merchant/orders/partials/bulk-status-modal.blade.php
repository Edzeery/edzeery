{{-- Bulk Status Change (P29) --}}
@if ($showBulkStatusModal)
    <div @edz-modal-closed.window="$wire.closeBulkStatusModal()">
    <x-edz.modal :is-open="true" size="md" show-close-button wire:key="bulk-status-modal">
        <div class="p-5">
            <h3 class="text-lg font-semibold text-ink mb-4">{{ __('order_flow.bulk_status_title') }}</h3>

            <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">
                {{ __('order_flow.bulk_status_target') }}
            </label>
            <select wire:model="bulkStatusTarget"
                class="edz-input w-full">
                <option value="">—</option>
                @foreach ($this->allStatuses as $s)
                    @if (!in_array($s['key'] ?? '', ['cancelled', 'canceled', 'confirmed'], true))
                        <option value="{{ $s['key'] }}">
                            {{ \Edzeery\MyStatusKit\Facades\Status::for('order', $s['key'] ?? 'default')->label() }}
                        </option>
                    @endif
                @endforeach
            </select>

            <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mt-4 mb-1.5">
                {{ __('order_flow.bulk_status_reason') }}
            </label>
            <textarea wire:model="bulkStatusReason" rows="2"
                class="edz-input w-full"
                placeholder="{{ __('order_flow.bulk_status_reason_placeholder') }}"></textarea>

            <div class="mt-6 flex justify-end gap-2">
                <button wire:click="closeBulkStatusModal" type="button"
                    class="edz-btn edz-btn--ghost">
                    {{ __('buttons.cancel') }}
                </button>
                <button wire:click="submitBulkStatus" type="button"
                    class="edz-btn edz-btn--primary"
                    wire:loading.attr="disabled">
                    <span>{{ __('buttons.save') }}</span>
                </button>
            </div>
        </div>
    </x-edz.modal>
    </div>
@endif