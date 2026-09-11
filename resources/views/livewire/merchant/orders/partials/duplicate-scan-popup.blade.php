{{-- Duplicate scan popup (P29.6): lazy — computed on click via openDuplicateScan() --}}
@if ($this->showDuplicateScanModal)
    <div @edz-modal-closed.window="$wire.closeDuplicateScanModal()">
        <x-edz.modal :isOpen="true" size="md" wire:key="duplicate-scan-modal">
            <div class="p-6">
                @php
                    $scanTone = match ($this->duplicateScanLevel) {
                        'duplicate' => 'danger',
                        'probable' => 'warning',
                        'repeat' => 'neutral',
                        default => null,
                    };
                    $scanLabel = match ($this->duplicateScanLevel) {
                        'duplicate' => __('order_flow.dup_badge_duplicate'),
                        'probable' => __('order_flow.dup_badge_probable'),
                        'repeat' => __('order_flow.dup_badge_repeat'),
                        default => null,
                    };
                @endphp
                <div class="flex items-start gap-3">
                    <div
                        class="flex items-center justify-center w-10 h-10 rounded-full bg-warning/10 text-warning shrink-0">
                        <x-edz.icon name="copy" class="w-5 h-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-base sm:text-lg font-bold text-ink">
                            {{ __('order_flow.duplicate_warnings_title') }}
                        </h3>
                        @if ($this->duplicateScanNumber)
                            <p class="mt-0.5 text-xs text-ink-muted" dir="ltr">
                                #{{ $this->duplicateScanNumber }}</p>
                        @endif
                        @if ($scanTone)
                            <span
                                class="mt-2 inline-flex items-center gap-1 edz-badge edz-badge--{{ $scanTone }} edz-badge--sm">
                                <x-edz.icon name="copy" class="w-3 h-3" />
                                {{ $scanLabel }}
                            </span>
                        @endif
                    </div>
                </div>

                @if (!empty($this->duplicateScanResults))
                    <p class="mt-4 text-sm text-ink">
                        {{ __('order_flow.duplicate_detected', ['count' => count($this->duplicateScanResults)]) }}
                    </p>
                    <ul class="mt-2 space-y-1.5 text-sm">
                        @foreach ($this->duplicateScanResults as $dup)
                            <li class="flex items-center justify-between gap-2 rounded-lg border border-surface-border bg-surface-tertiary/40 px-3 py-2">
                                <button type="button"
                                    wire:click="openOrderDetails('{{ $dup['order_id'] }}')"
                                    class="flex items-center gap-2 text-ink hover:text-brand-600 truncate text-start">
                                    <span class="truncate">
                                        #{{ $dup['number'] }}
                                        <span class="text-ink-muted">• {{ \Carbon\Carbon::parse($dup['created_at'])->diffForHumans() }}</span>
                                    </span>
                                </button>
                                <span class="shrink-0 text-xs text-ink-muted">
                                    ×{{ $dup['total_overlap_qty'] }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @elseif (($this->duplicateScanPhoneCount ?? 0) > 0)
                    <div
                        class="mt-4 flex items-start gap-2 rounded-xl border border-surface-border bg-surface-tertiary/40 p-3 text-xs text-ink-muted">
                        <x-edz.icon name="info-circle" class="w-4 h-4 shrink-0 text-brand" />
                        <span>
                            {{ __('order_flow.duplicate_phone_only_orders', ['count' => $this->duplicateScanPhoneCount]) }}
                        </span>
                    </div>
                @elseif (($this->duplicateScanRepeatCount ?? 0) > 0)
                    <div
                        class="mt-4 flex items-start gap-2 rounded-xl border border-surface-border bg-surface-tertiary/40 p-3 text-xs text-ink-muted">
                        <x-edz.icon name="info-circle" class="w-4 h-4 shrink-0 text-brand" />
                        <span>
                            {{ __('order_flow.dup_repeat_carrier_sent', ['count' => $this->duplicateScanRepeatCount]) }}
                        </span>
                    </div>
                @else
                    <div class="mt-4 flex items-center gap-2 text-xs text-ink-muted">
                        <x-edz.icon name="check-circle" class="w-4 h-4 text-success" />
                        {{ __('order_flow.no_duplicates') }}
                    </div>
                @endif

                @if (($this->duplicateScanRepeatCount ?? 0) > 0 && $this->duplicateScanLevel !== 'repeat')
                    <div
                        class="mt-3 flex items-start gap-2 rounded-xl border border-surface-border bg-surface-tertiary/40 p-3 text-xs text-ink-muted">
                        <x-edz.icon name="info-circle" class="w-4 h-4 shrink-0 text-brand" />
                        <span>
                            {{ __('order_flow.dup_repeat_carrier_sent', ['count' => $this->duplicateScanRepeatCount]) }}
                        </span>
                    </div>
                @endif

                <div class="mt-5 flex justify-end">
                    <button type="button" wire:click="closeDuplicateScanModal()"
                        class="edz-btn edz-btn--ghost edz-btn--sm">
                        {{ __('general.close') }}
                    </button>
                </div>
            </div>
        </x-edz.modal>
    </div>
@endif