{{-- Orders actions column (desktop): shared from the table row. Receives $orderId, $order. --}}

<td class="px-4 py-3 text-right">
    <div class="flex items-center justify-end gap-1 flex-nowrap">
        <button wire:click="openOrderDetails('{{ $orderId }}')"
            class="edz-btn edz-btn--ghost edz-btn--xs shrink-0"
            title="{{ __('merchant.order_details') }}" wire:loading.attr="disabled"
            wire:target="openOrderDetails('{{ $orderId }}')">
            <x-edz.spinner wire:target="openOrderDetails('{{ $orderId }}')" class="w-3.5 h-3.5" />
            <x-edz.icon name="info-circle" wire:loading.remove
                wire:target="openOrderDetails('{{ $orderId }}')" class="w-4 h-4 shrink-0" />
        </button>
        @include('livewire.merchant.orders.partials.order-events-menu', [
            'orderId' => $orderId,
            'order' => $order,
            'canViewEvents' => $order['can_view_events'] ?? false,
        ])
        @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_CONFIRM->value)
         && !$this->showTrash && in_array('confirmed', $order['transitions'] ?? [], true))
            <button wire:click="openConfirmModal('{{ $orderId }}')"
                class="edz-btn edz-btn--ghost edz-btn--xs shrink-0"
                title="{{ __('order_flow.confirm_title') }}" wire:loading.attr="disabled"
                wire:target="openConfirmModal('{{ $orderId }}')">
                <x-edz.spinner wire:target="openConfirmModal('{{ $orderId }}')" class="w-3.5 h-3.5" />
                <x-edz.icon name="phone" wire:loading.remove
                    wire:target="openConfirmModal('{{ $orderId }}')" class="w-4 h-4 shrink-0" />
            </button>
        @endif
        @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value)
         && !$this->showTrash && in_array($order['status_key'] ?? null, ['confirmed', 'preparing'], true))
            <button wire:click="sendConfirmedOrder('{{ $orderId }}')"
                class="edz-btn edz-btn--ghost edz-btn--xs shrink-0"
                title="{{ __('order_flow.send_to_carrier') }}" wire:loading.attr="disabled"
                wire:target="sendConfirmedOrder('{{ $orderId }}')">
                <x-edz.spinner wire:target="sendConfirmedOrder('{{ $orderId }}')" class="w-3.5 h-3.5" />
                <x-edz.icon name="truck" wire:loading.remove
                    wire:target="sendConfirmedOrder('{{ $orderId }}')" class="w-4 h-4 shrink-0" />
            </button>
        @endif
        @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value) && !$this->showTrash)
            <button @click="$wire.openDeliveryModal('{{ $orderId }}')"
                class="edz-btn edz-btn--ghost edz-btn--xs shrink-0"
                title="{{ __('merchant_panel.edit_delivery') }}" wire:loading.attr="disabled"
                wire:target="openDeliveryModal('{{ $orderId }}')">
                <x-edz.spinner wire:target="openDeliveryModal('{{ $orderId }}')" class="w-3.5 h-3.5" />
                <x-edz.icon name="truck" wire:loading.remove
                    wire:target="openDeliveryModal('{{ $orderId }}')" class="w-4 h-4 shrink-0" />
            </button>
            <button @click="$wire.openEditModal('{{ $orderId }}')"
                class="edz-btn edz-btn--ghost edz-btn--xs shrink-0"
                title="{{ __('merchant_panel.edit') }}" wire:loading.attr="disabled"
                wire:target="openEditModal('{{ $orderId }}')">
                <x-edz.spinner wire:target="openEditModal('{{ $orderId }}')" class="w-3.5 h-3.5" />
                <x-edz.icon name="edit" wire:loading.remove
                    wire:target="openEditModal('{{ $orderId }}')" class="w-4 h-4 shrink-0" />
            </button>
            <button wire:click="openReassignModal('{{ $orderId }}')"
                wire:loading.attr="disabled" wire:loading.class="opacity-50"
                wire:target="openReassignModal('{{ $orderId }}')"
                class="edz-btn edz-btn--ghost edz-btn--xs shrink-0"
                title="{{ __('merchant_panel.reassign') }}">
                <x-edz.spinner wire:target="openReassignModal('{{ $orderId }}')" class="w-3.5 h-3.5" />
                <x-edz.icon name="arrows-right-left" wire:loading.remove
                    wire:target="openReassignModal('{{ $orderId }}')" class="w-4 h-4 shrink-0" />
            </button>
        @endif
        @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_DELETE->value))
            @if ($this->showTrash)
                <button wire:click="restoreOrder('{{ $orderId }}')"
                    wire:loading.attr="disabled" wire:loading.class="opacity-50"
                    wire:target="restoreOrder('{{ $orderId }}')"
                    class="edz-btn edz-btn--ghost edz-btn--xs shrink-0 text-success-600"
                    title="{{ __('merchant.restore_order') }}">
                    <x-edz.spinner wire:target="restoreOrder('{{ $orderId }}')" class="w-3.5 h-3.5" />
                    <x-edz.icon name="arrow-uturn-left" wire:loading.remove
                        wire:target="restoreOrder('{{ $orderId }}')" class="w-4 h-4 shrink-0" />
                </button>
            @else
                <button
                    class="edz-btn edz-btn--ghost edz-btn--xs text-danger-600 hover:text-danger-700 shrink-0"
                    x-on:click.prevent="confirmDelete()" :disabled="deleteLoading"
                    :class="deleteLoading ? 'opacity-50' : ''"
                    title="{{ __('merchant.delete_permanently') }}">
                    <x-edz.spinner show="deleteLoading" class="w-3.5 h-3.5" />
                    <x-edz.icon name="trash" x-show="!deleteLoading" class="w-4 h-4 shrink-0" />
                </button>
            @endif
        @endif
    </div>
</td>