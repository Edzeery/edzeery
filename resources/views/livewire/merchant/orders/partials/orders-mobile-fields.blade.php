{{-- Mobile card geography + shipping fields.
     Renders required columns that the desktop table shows but the mobile
     card historically omitted: delivery_type, shipping_provider, city,
     stopdesk_point, address, shipping_cost.
     Receives: $order (array), $orderId (string). --}}

@php
    $dtHint = __('order_flow.please_select_delivery_type');
    $spHint = __('order_flow.please_select_shipping_provider');
    $cityHint = __('order_flow.please_select_city');
    $stopdeskHint = __('order_flow.please_select_stopdesk');
    $deliveryTypeLabel = match ($order['delivery_type'] ?? null) {
        'stopdesk' => __('merchant_panel.stop_desk_label'),
        'home'     => __('merchant_panel.home_delivery_label'),
        default    => null,
    };
    $showStopdeskHint = ($order['delivery_type'] ?? null) === 'stopdesk';
    $canManage = canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value);
@endphp

<div class="mt-2 space-y-1.5 text-xs text-ink-muted">
    {{-- delivery_type --}}
    @if (in_array('delivery_type', $this->visibleColumns))
        @if ($this->editingField === 'order.delivery_type' && $this->editingId === $orderId)
            <div class="edz-inline-edit__edit" wire:key="delivery-type-mobile-{{ $orderId }}">
                <x-edz.select wire:model="editingValue" :options="$this->editDeliveryTypeOptions" size="sm" />
                <div class="edz-inline-edit__actions">
                    <button type="button" class="edz-inline-edit__save" wire:click="saveOrderDeliveryType"
                        wire:loading.attr="disabled"><span>{{ __('buttons.save') }}</span></button>
                    <button type="button" class="edz-inline-edit__cancel"
                        @click="$wire.cancelOrderEdit()">{{ __('buttons.cancel') }}</button>
                </div>
            </div>
        @elseif ($canManage)
            <button type="button" class="edz-inline-edit__display w-full text-left"
                @click="$wire.startOrderDeliveryTypeEdit('{{ $orderId }}')">
                <x-edz.icon name="adjustments-horizontal" class="w-3 h-3 shrink-0 text-ink-muted" />
                @if ($deliveryTypeLabel)
                    <span class="edz-inline-edit__value">{{ $deliveryTypeLabel }}</span>
                @else
                    <span class="text-warning font-medium">{{ $dtHint }}</span>
                @endif
            </button>
        @else
            <span class="inline-flex items-center gap-1">
                <x-edz.icon name="adjustments-horizontal" class="w-3 h-3 shrink-0 text-ink-muted" />
                @if ($deliveryTypeLabel) {{ $deliveryTypeLabel }} @else <span class="text-warning font-medium">{{ $dtHint }}</span> @endif
            </span>
        @endif
    @endif

    {{-- shipping_provider --}}
    @if (in_array('shipping_provider', $this->visibleColumns))
        @if ($this->editingField === 'order.shipping_provider' && $this->editingId === $orderId)
            <div class="edz-inline-edit__edit" wire:key="provider-mobile-{{ $orderId }}">
                <x-edz.select wire:model="editingValue" :options="$this->editProviderOptions" size="sm" search
                    placeholder="{{ __('merchant_panel.shipping_provider') }}" />
                <div class="edz-inline-edit__actions">
                    <button type="button" class="edz-inline-edit__save" wire:click="saveOrderProvider"
                        wire:loading.attr="disabled"><span>{{ __('buttons.save') }}</span></button>
                    <button type="button" class="edz-inline-edit__cancel"
                        @click="$wire.cancelOrderEdit()">{{ __('buttons.cancel') }}</button>
                </div>
            </div>
        @elseif ($canManage)
            <button type="button" class="edz-inline-edit__display w-full text-left"
                @click="$wire.startOrderProviderEdit('{{ $orderId }}')">
                <x-edz.icon name="truck" class="w-3 h-3 shrink-0 text-ink-muted" />
                @if (!empty($order['shipping_provider']['name']))
                    <span class="edz-inline-edit__value">{{ $order['shipping_provider']['name'] }}</span>
                @else
                    <span class="text-warning font-medium">{{ $spHint }}</span>
                @endif
            </button>
        @else
            <span class="inline-flex items-center gap-1">
                <x-edz.icon name="truck" class="w-3 h-3 shrink-0 text-ink-muted" />
                @if (!empty($order['shipping_provider']['name'])) {{ $order['shipping_provider']['name'] }}
                @else <span class="text-warning font-medium">{{ $spHint }}</span> @endif
            </span>
        @endif
    @endif

    {{-- city --}}
    @if (in_array('city', $this->visibleColumns))
        @if ($this->editingField === 'order.city' && $this->editingId === $orderId)
            <div class="edz-inline-edit__edit" wire:key="city-mobile-{{ $orderId }}">
                <select wire:change="saveOrderCity($event.target.value)"
                    class="edz-inline-edit__input @if ($this->editingError) edz-inline-edit__input--error @endif">
                    @foreach ($this->editCityOptions as $ct)
                        <option value="{{ $ct['id'] }}"
                            @if ((string) $this->editingValue === (string) $ct['id']) selected @endif>
                            {{ $ct['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
        @elseif ($canManage && !empty($order['state_id']))
            <button type="button" class="edz-inline-edit__display w-full text-left"
                @click="$wire.startOrderCityEdit('{{ $orderId }}')">
                @if (!empty($order['city']['name']))
                    <span class="edz-inline-edit__value">{{ $order['city']['name'] }}</span>
                @else
                    <span class="text-warning font-medium">{{ $cityHint }}</span>
                @endif
            </button>
        @else
            @if (!empty($order['city']['name'])) {{ $order['city']['name'] }}
            @else <span class="text-warning font-medium">{{ $cityHint }}</span> @endif
        @endif
    @endif

    {{-- stopdesk_point (only when delivery_type is stopdesk) --}}
    @if (in_array('stopdesk_point', $this->visibleColumns) && $showStopdeskHint)
        @if ($this->editingField === 'order.stopdesk_point' && $this->editingId === $orderId)
            <div class="edz-inline-edit__edit" wire:key="stopdesk-mobile-{{ $orderId }}">
                <x-edz.select wire:model="editingValue" :options="$this->editStopdeskOptions" size="sm" search
                    placeholder="{{ __('merchant_panel.stop_desk_label') }}" />
                <div class="edz-inline-edit__actions">
                    <button type="button" class="edz-inline-edit__save" wire:click="saveOrderStopdesk"
                        wire:loading.attr="disabled"><span>{{ __('buttons.save') }}</span></button>
                    <button type="button" class="edz-inline-edit__cancel"
                        @click="$wire.cancelOrderEdit()">{{ __('buttons.cancel') }}</button>
                </div>
            </div>
        @elseif ($canManage)
            <button type="button" class="edz-inline-edit__display w-full text-left"
                @click="$wire.startOrderStopdeskEdit('{{ $orderId }}')">
                @if (!empty($order['stopdesk_point']['name']))
                    <span class="edz-inline-edit__value">{{ $order['stopdesk_point']['name'] }}@if (!empty($order['stopdesk_point']['city']['name'])) ({{ $order['stopdesk_point']['city']['name'] }}) @endif</span>
                @else
                    <span class="text-warning font-medium">{{ $stopdeskHint }}</span>
                @endif
            </button>
        @else
            @if (!empty($order['stopdesk_point']['name'])) {{ $order['stopdesk_point']['name'] }}@if (!empty($order['stopdesk_point']['city']['name'])) ({{ $order['stopdesk_point']['city']['name'] }}) @endif
            @else <span class="text-warning font-medium">{{ $stopdeskHint }}</span> @endif
        @endif
    @endif

    {{-- address --}}
    @if (in_array('address', $this->visibleColumns))
        @if ($this->editingField === 'order.address' && $this->editingId === $orderId)
            <div class="edz-inline-edit__edit" wire:key="address-mobile-{{ $orderId }}">
                <input type="text" wire:model="editingValue" wire:keydown.enter="saveOrderAddress"
                    placeholder="{{ __('merchant_panel.address') }}"
                    class="edz-inline-edit__input @if ($this->editingError) edz-inline-edit__input--error @endif">
                <div class="edz-inline-edit__actions">
                    <button type="button" class="edz-inline-edit__save" wire:click="saveOrderAddress"
                        wire:loading.attr="disabled"><span>{{ __('buttons.save') }}</span></button>
                    <button type="button" class="edz-inline-edit__cancel"
                        wire:click="cancelOrderEdit">{{ __('buttons.cancel') }}</button>
                </div>
            </div>
        @elseif ($canManage)
            <button type="button" class="edz-inline-edit__display w-full text-left"
                wire:click="startOrderAddressEdit('{{ $orderId }}')"
                title="{{ $order['address'] ?? '' }}">
                <x-edz.icon name="map-pin" class="w-3 h-3 shrink-0 text-ink-muted" />
                <span class="edz-inline-edit__value">{{ $order['address'] ? \Illuminate\Support\Str::limit($order['address'], 50) : '—' }}</span>
            </button>
        @else
            <span class="inline-flex items-center gap-1">
                <x-edz.icon name="map-pin" class="w-3 h-3 shrink-0 text-ink-muted" />
                {{ $order['address'] ? \Illuminate\Support\Str::limit($order['address'], 50) : '—' }}
            </span>
        @endif
    @endif
</div>
