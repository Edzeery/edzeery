{{-- 31.9 — Items edit modals (products / quantity / price). One Pattern A modal
   per column; only the active kind is rendered. The draft lives in
   $this->form['items'] so the modal's steppers/reducers work unchanged; product
   selection goes through the shared picker (orders-product-picker partial, which
   writes into the same draft). Price is only editable through the C3 gate:
   FORM_EDITED (the store owner) OR (allow_price_edit AND ORDER_EDIT_PRICE), so
   the owner can still override even when the store forbids price editing while
   archived orders stay locked (guarded separately). --}}
@php
    $itemsModalKind = ($itemsModal ?? [])['kind'] ?? null;
    $itemsModalOrderId = ($itemsModal ?? [])['orderId'] ?? null;
    $orderItems = $form['items'] ?? [];
@endphp

@if ($itemsModalKind === 'products')
    <div @edz-modal-closed="$wire.closeItemsModal()">
        <x-edz.modal :is-open="true" size="md" wire:key="items-products-modal-{{ $itemsModalOrderId }}">
            <div class="p-6 space-y-4">
                <div class="flex items-center gap-2">
                    <x-edz.icon name="shopping-bag" class="w-5 h-5 text-ink-muted" />
                    <h3 class="text-base font-bold text-ink">{{ __('merchant_panel.products') }}</h3>
                    <span class="text-xs text-ink-muted">({{ count($orderItems) }})</span>
                </div>

                <div class="max-h-[45vh] overflow-y-auto edz-scroll divide-y divide-surface-border/70 rounded-xl border border-surface-border">
                    @forelse ($orderItems as $idx => $item)
                        <div class="flex items-center gap-3 px-3 py-2.5">
                            <div class="flex-1 min-w-0">
                                @if (!empty($item['name']))
                                    <div class="text-sm font-medium text-ink truncate">{{ $item['name'] }}</div>
                                @else
                                    <div class="text-sm text-ink-muted">
                                        {{ __('merchant_panel.please_select_product') }}
                                    </div>
                                @endif
                                <div class="text-[11px] text-ink-muted truncate" dir="ltr">{{ $item['sku'] ?? '' }}</div>
                            </div>
                            <div class="text-end shrink-0">
                                @if ((float) ($item['price'] ?? 0) > 0)
                                    <div class="text-xs font-semibold text-ink tabular-nums">
                                        {{ currency((float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 1)) }}
                                    </div>
                                    <div class="text-[11px] text-ink-muted tabular-nums">
                                        {{ (int) ($item['quantity'] ?? 1) }} × {{ currency($item['price'] ?? 0) }}
                                    </div>
                                @else
                                    <div class="text-xs text-ink-muted">
                                        {{ __('merchant_panel.please_select_product') }}
                                    </div>
                                @endif
                            </div>
                            <button type="button" wire:click="removeInlineItem({{ $idx }})"
                                class="text-danger-400 hover:text-danger-600 shrink-0 p-1.5 rounded-lg hover:bg-danger-surface transition-colors"
                                title="{{ __('merchant_panel.delete_item') }}">
                                <x-edz.icon name="x-mark" class="w-4 h-4" />
                            </button>
                        </div>
                    @empty
                        <p class="text-sm text-ink-muted py-6 text-center">{{ __('merchant_panel.no_items') }}</p>
                    @endforelse
                </div>

                {{-- Add product — opens the shared picker (writes into the draft) --}}
                <div class="pt-2 border-t border-surface-border" x-data="orderProductPicker()">
                    <button type="button" @click="openProductPicker()" :disabled="isLoadingProducts"
                        class="w-full flex items-center gap-3 px-4 py-3 bg-surface-secondary
                            border border-dashed border-surface-border rounded-xl
                            hover:border-brand-400 hover:bg-brand-50
                            transition-colors text-sm text-ink-muted group disabled:opacity-50">
                        <x-edz.spinner :show="'isLoadingProducts'" class="w-5 h-5 text-brand-500" />
                        <x-edz.icon name="qr-code" x-show="!isLoadingProducts"
                            class="w-5 h-5 text-ink-muted group-hover:text-brand-500 transition-colors" />
                        <span class="flex-1 text-start">{{ __('merchant_panel.search_products_barcode') }}</span>
                        <x-edz.icon name="plus"
                            class="w-4 h-4 text-ink-muted group-hover:text-brand-500 transition-colors" />
                    </button>
                </div>

                @include('livewire.merchant.orders.partials.orders-items-modal-footer')
            </div>
        </x-edz.modal>
    </div>
@endif

@if ($itemsModalKind === 'quantity')
    <div @edz-modal-closed="$wire.closeItemsModal()">
        <x-edz.modal :is-open="true" size="md" wire:key="items-quantity-modal-{{ $itemsModalOrderId }}">
            <div class="p-6 space-y-4">
                <div class="flex items-center gap-2">
                    <x-edz.icon name="list-bullet" class="w-5 h-5 text-ink-muted" />
                    <h3 class="text-base font-bold text-ink">{{ __('merchant_panel.quantity') }}</h3>
                    <span class="text-xs text-ink-muted">({{ count($orderItems) }})</span>
                </div>

                <div class="max-h-[45vh] overflow-y-auto edz-scroll divide-y divide-surface-border/70 rounded-xl border border-surface-border">
                    @forelse ($orderItems as $idx => $item)
                        @php
                            $capReached = ($item['cap'] ?? null) !== null && ($item['quantity'] ?? 0) >= $item['cap'];
                        @endphp
                        <div class="flex items-center gap-3 px-3 py-2.5">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-ink truncate">{{ $item['name'] ?? '—' }}</div>
                                <div class="text-[11px] text-ink-muted truncate" dir="ltr">{{ $item['sku'] ?? '' }}</div>
                            </div>
                            <div class="flex items-center rounded-lg border border-surface-border overflow-hidden shrink-0">
                                <button type="button"
                                    wire:click="updateFormItemQty({{ $idx }}, {{ max(1, (int) ($item['quantity'] ?? 1) - 1) }})"
                                    :disabled="{{ ((int) ($item['quantity'] ?? 1) <= 1) ? 'true' : 'false' }}"
                                    class="w-8 h-8 flex items-center justify-center bg-surface text-ink-muted hover:bg-surface-secondary transition-colors disabled:opacity-30 disabled:cursor-not-allowed text-sm font-medium select-none">
                                    &minus;
                                </button>
                                <input type="number" value="{{ $item['quantity'] ?? 1 }}"
                                    wire:change="updateFormItemQty({{ $idx }}, parseInt($event.target.value))"
                                    min="1" @if (($item['cap'] ?? null) !== null) max="{{ $item['cap'] }}" @endif
                                    class="w-10 h-8 text-center border-x border-surface-border bg-transparent text-sm font-semibold text-ink focus:outline-none focus:ring-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                <button type="button"
                                    wire:click="updateFormItemQty({{ $idx }}, {{ (int) ($item['quantity'] ?? 1) + 1 }})"
                                    :disabled="{{ $capReached ? 'true' : 'false' }}"
                                    class="w-8 h-8 flex items-center justify-center bg-surface text-ink-muted hover:bg-surface-secondary transition-colors disabled:opacity-30 disabled:cursor-not-allowed text-sm font-medium select-none">
                                    &plus;
                                </button>
                            </div>
                            <div class="text-end shrink-0 w-20">
                                <div class="text-xs font-semibold text-ink tabular-nums">
                                    {{ currency((float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 1)) }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-ink-muted py-6 text-center">{{ __('merchant_panel.no_items') }}</p>
                    @endforelse
                </div>

                @include('livewire.merchant.orders.partials.orders-items-modal-footer')
            </div>
        </x-edz.modal>
    </div>
@endif

@if ($itemsModalKind === 'price')
    <div @edz-modal-closed="$wire.closeItemsModal()">
        <x-edz.modal :is-open="true" size="md" wire:key="items-price-modal-{{ $itemsModalOrderId }}">
            <div class="p-6 space-y-4">
                <div class="flex items-center gap-2">
                    <x-edz.icon name="tag" class="w-5 h-5 text-ink-muted" />
                    <h3 class="text-base font-bold text-ink">{{ __('merchant_panel.price') }}</h3>
                    <span class="text-xs text-ink-muted">({{ count($orderItems) }})</span>
                </div>

                <div class="max-h-[45vh] overflow-y-auto edz-scroll divide-y divide-surface-border/70 rounded-xl border border-surface-border">
                    @forelse ($orderItems as $idx => $item)
                        <div class="flex items-center gap-3 px-3 py-2.5">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-ink truncate">{{ $item['name'] ?? '—' }}</div>
                                <div class="text-[11px] text-ink-muted truncate" dir="ltr">{{ $item['sku'] ?? '' }}</div>
                            </div>
                            <input type="number" value="{{ $item['price'] ?? 0 }}"
                                wire:change="updateInlineItemPrice({{ $idx }}, parseFloat($event.target.value))"
                                step="10" min="0" class="edz-input text-sm w-24 text-center py-1.5"
                                placeholder="{{ __('merchant_panel.price') }}">
                            <div class="text-end shrink-0 w-20">
                                <div class="text-xs font-semibold text-ink tabular-nums">
                                    {{ currency((float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 1)) }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-ink-muted py-6 text-center">{{ __('merchant_panel.no_items') }}</p>
                    @endforelse
                </div>

                @include('livewire.merchant.orders.partials.orders-items-modal-footer')
            </div>
        </x-edz.modal>
    </div>
@endif
