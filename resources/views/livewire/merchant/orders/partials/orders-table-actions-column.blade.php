{{-- Shared order actions (table row). One source of truth for the actions in
    the desktop table and the mobile overflow popover.

    Receives: $orderId, $order, $transitions (array), $showTrash (bool),
    $layout ('compact' = desktop icon buttons | 'list' = mobile full-width rows).

    Loading feedback for every button is handled centrally by
    edz-button-loading.js (ring + content hiding) so no wire:loading swaps live
    here. The events menu stays on the caller side, next to this partial. --}}

@php
    // Mobile rows live inside the orderMoreMenu Alpine scope: a separate
    // @click="close()" mirrors the established confirm/send pattern.
    $icon = 'w-4 h-4 shrink-0';
    $btnClass = $layout === 'list'
        ? 'w-full text-left flex items-center gap-2 px-2.5 min-h-[44px] rounded-lg text-sm hover:bg-surface-tertiary disabled:opacity-50'
        : 'edz-btn edz-btn--ghost edz-btn--xs shrink-0';
    $confirmBtnClass = $layout === 'list'
        ? 'w-full text-left flex items-center gap-2 px-2.5 min-h-[44px] rounded-lg text-sm font-semibold bg-primary-600 text-white hover:bg-primary-700 disabled:opacity-50'
        : 'edz-btn edz-btn--primary edz-btn--xs shrink-0';
@endphp

<div class="{{ $layout === 'list' ? 'flex flex-col gap-0.5' : 'flex items-center justify-end gap-1 flex-nowrap' }}">
    {{-- Desktop icon-only details. On mobile the details button and the
         events menu render on the caller side (next to the popover), so they
         are intentionally skipped for $layout === 'list'. --}}
    @if ($layout === 'compact')
        <x-edz.tooltip label="{{ __('merchant.order_details') }}">
            <button wire:click="openOrderDetails('{{ $orderId }}')"
                class="{{ $btnClass }}"
                aria-label="{{ __('merchant.order_details') }}">
                <x-edz.icon name="info-circle" class="{{ $icon }}" />
            </button>
        </x-edz.tooltip>

        @if ($events ?? false)
            @include('livewire.merchant.orders.partials.order-events-menu', [
                'orderId' => $orderId,
                'order' => $order,
                'canViewEvents' => $order['can_view_events'] ?? false,
            ])
        @endif
    @endif

@if ($layout === 'compact'
    && canStore(\App\Enums\Store\StorePermissionEnum::ORDER_CONFIRM->value)
    && !$showTrash && ($order['can_confirm'] ?? false))
        <x-edz.tooltip label="{{ $layout === 'compact' ? __('order_flow.confirm_title') : '' }}">
        <button wire:click="openConfirmModal('{{ $orderId }}')"
            @if ($layout === 'list') @click="close()" @endif
            class="{{ $confirmBtnClass }}"
            @if ($layout === 'compact') aria-label="{{ __('order_flow.confirm_title') }}" @endif>
            <x-edz.icon name="phone" class="{{ $icon }}" />
            @if ($layout === 'list')
                <span>{{ __('order_flow.confirm_title') }}</span>
            @endif
        </button>
    </x-edz.tooltip>
    @endif

    @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value)
     && !$showTrash && in_array($order['status_key'] ?? null, ['confirmed', 'preparing'], true))
        <x-edz.tooltip label="{{ $layout === 'compact' ? __('order_flow.send_to_carrier') : '' }}">
        <button wire:click="sendConfirmedOrder('{{ $orderId }}')"
            @if ($layout === 'list') @click="close()" @endif
            class="{{ $btnClass }}"
            @if ($layout === 'compact') aria-label="{{ __('order_flow.send_to_carrier') }}" @endif>
            <x-edz.icon name="truck" class="{{ $icon }}" />
            @if ($layout === 'list')
                <span>{{ __('order_flow.send_to_carrier') }}</span>
            @endif
        </button>
    </x-edz.tooltip>
    @endif

    @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value)
     && !$showTrash && in_array($order['status_key'] ?? null, ['shipped', 'in_transit', 'out_for_delivery'], true))
        <x-edz.tooltip label="{{ $layout === 'compact' ? __('order_flow.cancel_shipment') : '' }}">
        <button x-on:click="EdzSwal.confirmAction('{{ __('order_flow.cancel_shipment_title') }}', '{{ __('order_flow.cancel_shipment_confirm') }}', { confirmText: '{{ __('order_flow.cancel_shipment') }}', confirmColor: '#d97706' }).then((ok) => { if (ok) $wire.cancelShipment('{{ $orderId }}'); })"
            @if ($layout === 'list') @click="close()" @endif
            class="{{ $layout === 'list'
                ? $btnClass . ' text-warning-600'
                : $btnClass . ' text-warning-600 hover:text-warning-700' }}"
            @if ($layout === 'compact') aria-label="{{ __('order_flow.cancel_shipment') }}" @endif>
            <x-edz.icon name="x-circle" class="{{ $icon }}" />
            @if ($layout === 'list')
                <span>{{ __('order_flow.cancel_shipment') }}</span>
            @endif
        </button>
    </x-edz.tooltip>
    @endif

    @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value) && !$showTrash)
        @php $editCloser = $layout === 'list' ? '; close()' : ''; @endphp
        <x-edz.tooltip label="{{ $layout === 'compact' ? __('merchant_panel.edit') : '' }}">
        <button @click="$wire.openEditModal('{{ $orderId }}'){{ $editCloser }}"
            class="{{ $btnClass }}"
            @if ($layout === 'compact') aria-label="{{ __('merchant_panel.edit') }}" @endif>
            <x-edz.icon name="edit" class="{{ $icon }}" />
            @if ($layout === 'list')
                <span>{{ __('merchant_panel.edit') }}</span>
            @endif
        </button>
    </x-edz.tooltip>
        <x-edz.tooltip label="{{ $layout === 'compact' ? __('merchant_panel.reassign') : '' }}">
        <button wire:click="openReassignModal('{{ $orderId }}')"
            @if ($layout === 'list') @click="close()" @endif
            class="{{ $btnClass }}"
            @if ($layout === 'compact') aria-label="{{ __('merchant_panel.reassign') }}" @endif>
            <x-edz.icon name="arrows-right-left" class="{{ $icon }}" />
            @if ($layout === 'list')
                <span>{{ __('merchant_panel.reassign') }}</span>
            @endif
        </button>
    </x-edz.tooltip>
    @endif

    @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_DELETE->value))
        @if ($showTrash)
            <x-edz.tooltip label="{{ $layout === 'compact' ? __('merchant.restore_order') : '' }}">
            <button wire:click="restoreOrder('{{ $orderId }}')"
                @if ($layout === 'list') @click="close()" @endif
                class="{{ $btnClass . ' text-success-600' }}"
                @if ($layout === 'compact') aria-label="{{ __('merchant.restore_order') }}" @endif>
                <x-edz.icon name="arrow-uturn-left" class="{{ $icon }}" />
                @if ($layout === 'list')
                    <span>{{ __('merchant.restore_order') }}</span>
                @endif
            </button>
        </x-edz.tooltip>
        @else
            @php $deleteCloser = $layout === 'list' ? 'confirmDelete(); close()' : 'confirmDelete()'; @endphp
            <x-edz.tooltip label="{{ $layout === 'compact' ? __('merchant.delete_permanently') : '' }}">
            <button
                class="{{ $layout === 'list'
                    ? $btnClass . ' text-danger-600'
                    : $btnClass . ' text-danger-600 hover:text-danger-700' }}"
                x-on:click.prevent="{{ $deleteCloser }}" :disabled="deleteLoading"
                :class="deleteLoading ? 'opacity-50' : ''"
                @if ($layout === 'compact') aria-label="{{ __('merchant.delete_permanently') }}" @endif>
                <x-edz.spinner show="deleteLoading" class="w-3.5 h-3.5" />
                <x-edz.icon name="trash" x-show="!deleteLoading" class="{{ $icon }}" />
                @if ($layout === 'list')
                    <span x-show="!deleteLoading">{{ __('merchant.delete_permanently') }}</span>
                @endif
            </button>
        </x-edz.tooltip>
        @endif
    @endif
</div>