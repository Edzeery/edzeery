<div>
    @if ($showDeliveryModal)
        <x-edz.modal :isOpen="true" :showCloseButton="false" :preventClose="false" size="md"
            wire:key="delivery-edit-{{ $deliveryOrderId }}">
            <form wire:submit="saveDeliveryModal">
                <div class="p-6 space-y-5">
                    {{-- Header --}}
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-ink">{{ __('merchant_panel.edit_delivery') }}</h3>
                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                            wire:click="closeDeliveryModal">
                            <x-edz.icon name="x-mark" class="w-5 h-5" />
                        </button>
                    </div>

                    @include('livewire.merchant.orders.partials.order-delivery-cascade')

                    {{-- Submit --}}
                    <div class="flex justify-end gap-2 pt-2 border-t border-surface-border">
                        <button type="button" class="edz-btn edz-btn--ghost" wire:click="closeDeliveryModal">
                            {{ __('buttons.cancel') }}
                        </button>
                        <button type="submit" class="edz-btn edz-btn--primary" wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 pointer-events-none">
                            <span>{{ __('merchant_panel.update') }}</span>
                            <span class="sr-only">{{ __('merchant_panel.update') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </x-edz.modal>
    @endif
</div>
