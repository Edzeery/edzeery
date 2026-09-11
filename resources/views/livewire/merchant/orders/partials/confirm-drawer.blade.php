{{-- Confirmation Drawer (P26) — extracted from index.blade.php --}}
@if ($showConfirmModal)
    <div @edz-modal-closed.window="$wire.closeConfirmModal()">
        <x-edz.modal :is-open="true" size="lg" show-close-button wire:key="confirmation-drawer">
            <div class="p-5">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-lg font-semibold text-ink">
                            {{ __('order_flow.confirm_title') }}
                            @if ($this->confirmSummary)
                                <span class="text-ink-muted font-normal">#{{ $this->confirmSummary['number'] }}</span>
                            @endif
                        </h3>
                        <p class="text-sm text-ink-muted mt-0.5">{{ __('order_flow.confirm_summary') }}</p>
                    </div>
                </div>

                @if ($this->confirmSummary)
                    <dl
                        class="rounded-xl border border-surface-border divide-y divide-surface-border overflow-hidden bg-surface-tertiary/30 text-sm">
                        <div class="flex items-center justify-between gap-3 px-3 py-2">
                            <dt class="text-ink-muted">{{ __('merchant_panel.customer') }}</dt>
                            <dd class="text-ink text-end font-medium">{{ $this->confirmSummary['customer'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 px-3 py-2">
                            <dt class="text-ink-muted">{{ __('merchant_panel.total') }}</dt>
                            <dd class="text-ink text-end font-bold">{{ $this->confirmSummary['total'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 px-3 py-2">
                            <dt class="text-ink-muted">{{ __('merchant_panel.status') }}</dt>
                            <dd class="text-ink text-end">{{ $this->confirmSummary['status'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 px-3 py-2">
                            <dt class="text-ink-muted">{{ __('order_flow.confirm_partner') }}</dt>
                            <dd class="text-ink text-end">{{ $this->confirmSummary['partner'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 px-3 py-2">
                            <dt class="text-ink-muted">{{ __('order_flow.confirm_attempts') }}</dt>
                            <dd class="text-ink text-end font-medium tabular-nums">{{ $this->confirmSummary['attempts'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 px-3 py-2">
                            <dt class="text-ink-muted">{{ __('order_flow.confirm_last_contact') }}</dt>
                            <dd class="text-ink text-end">{{ $this->confirmSummary['last_contact'] ?? '—' }}</dd>
                        </div>
                    </dl>
                @endif

                @if (!empty($this->duplicateWarnings))
                    <div class="mt-4 rounded-xl border border-warning/40 bg-warning/5 p-3">
                        <div class="flex items-center gap-2 text-warning mb-2">
                            <x-edz.icon name="exclamation-triangle" class="w-4 h-4" />
                            <span class="text-sm font-medium">
                                {{ __('order_flow.duplicate_detected', ['count' => count($this->duplicateWarnings)]) }}
                            </span>
                        </div>
                        <ul class="space-y-1.5 text-sm">
                            @foreach ($this->duplicateWarnings as $dup)
                                <li class="flex items-center justify-between gap-2">
                                    <span class="text-ink truncate">
                                        #{{ $dup['number'] }}
                                        <span class="text-ink-muted">• {{ \Carbon\Carbon::parse($dup['created_at'])->diffForHumans() }}</span>
                                    </span>
                                    <span class="shrink-0 text-xs text-ink-muted">
                                        ×{{ $dup['total_overlap_qty'] }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                        @if ($this->confirmOrderId)
                            <button wire:click="markOrderDuplicate('{{ $this->confirmOrderId }}')" type="button"
                                class="mt-3 edz-btn edz-btn--ghost edz-btn--sm">
                                <x-edz.icon name="copy" class="w-3.5 h-3.5" />
                                {{ __('order_flow.mark_as_duplicate') }}
                            </button>
                        @endif
                    </div>
                @elseif ($this->confirmOrderId)
                    <div class="mt-4 flex items-center gap-2 text-xs text-ink-muted">
                        <x-edz.icon name="check-circle" class="w-4 h-4 text-success" />
                        {{ __('order_flow.no_duplicates') }}
                    </div>
                @endif

                <div class="mt-5">
                    <h4 class="text-xs font-semibold text-ink-muted uppercase tracking-wide mb-2">
                        {{ __('order_flow.confirm_partner') }}
                    </h4>
                    @include('livewire.merchant.orders.partials.partner-picker', ['picker' => 'confirm'])
                </div>

                <div class="mt-5 flex items-center justify-between gap-4 rounded-xl border border-surface-border p-3">
                    <div class="flex items-center gap-2 text-sm text-ink">
                        <x-edz.icon name="phone" class="w-4 h-4 text-ink-muted" />
                        {{ __('order_flow.confirm_contacted') }}
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="confirmContacted" class="sr-only peer">
                        <div
                            class="w-10 h-6 bg-surface-tertiary rounded-full peer-checked:bg-accent-600 transition"></div>
                        <div
                            class="absolute left-1 top-0.5 w-5 h-5 bg-white rounded-full shadow transition peer-checked:translate-x-4">
                        </div>
                    </label>
                </div>

                <div class="mt-4">
                    <label for="confirm-note" class="edz-label">
                        {{ __('order_flow.confirm_note') }}
                    </label>
                    <textarea id="confirm-note" wire:model="confirmNote" rows="2"
                        class="edz-input mt-1 w-full resize-none @if ($this->editingError) edz-inline-edit__input--error @endif"
                        placeholder="{{ __('order_flow.confirm_note_placeholder') }}"></textarea>
                </div>

                <div class="mt-6 flex flex-col-reverse sm:flex-row gap-2 justify-end">
                    <button wire:click="closeConfirmModal" type="button"
                        class="edz-btn edz-btn--ghost">
                        {{ __('buttons.cancel') }}
                    </button>
                    @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_CONFIRM->value))
                        <button wire:click="submitConfirmOnly" type="button"
                            class="edz-btn edz-btn--ghost"
                            wire:loading.attr="disabled">
                            <span>{{ __('order_flow.confirm_only') }}</span>
                        </button>
                    @endif
                    @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
                        <button wire:click="submitConfirmAndSend" type="button"
                            class="edz-btn edz-btn--primary"
                            wire:loading.attr="disabled">
                            <x-edz.icon name="truck" class="w-4 h-4" />
                            <span>{{ __('order_flow.confirm_and_send') }}</span>
                        </button>
                    @endif
                </div>
            </div>
        </x-edz.modal>
    </div>
@endif