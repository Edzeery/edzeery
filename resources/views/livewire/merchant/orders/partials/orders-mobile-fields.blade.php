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
            <button type="button" class="edz-inline-edit__display edz-inline-edit__display--touch w-full text-left"
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
                <x-edz.select wire:model="editingValue" :options="$this->editProviderOptions" option-hint="hint" size="sm" search
                    placeholder="{{ __('merchant_panel.shipping_provider') }}" />
                <div class="edz-inline-edit__actions">
                    <button type="button" class="edz-inline-edit__save" wire:click="saveOrderProvider"
                        wire:loading.attr="disabled"><span>{{ __('buttons.save') }}</span></button>
                    <button type="button" class="edz-inline-edit__cancel"
                        @click="$wire.cancelOrderEdit()">{{ __('buttons.cancel') }}</button>
                </div>
            </div>
        @elseif ($canManage)
            <button type="button" class="edz-inline-edit__display edz-inline-edit__display--touch w-full text-left"
                @click="$wire.startOrderProviderEdit('{{ $orderId }}')">
                <x-edz.icon name="truck" class="w-3 h-3 shrink-0 text-ink-muted" />
                @if (!empty($order['shipping_provider']['name']))
                    <span class="edz-inline-edit__value">{{ $order['shipping_provider']['name'] }}</span>
                @elseif (!empty($order['deliveryRider']['name']))
                    <x-edz.badge tone="accent" sm>
                        <x-edz.icon name="user" class="w-3 h-3" />
                        {{ $order['deliveryRider']['name'] }}
                    </x-edz.badge>
                @else
                    <span class="text-warning font-medium">{{ $spHint }}</span>
                @endif
            </button>
        @else
            <span class="inline-flex items-center gap-1">
                <x-edz.icon name="truck" class="w-3 h-3 shrink-0 text-ink-muted" />
                @if (!empty($order['shipping_provider']['name'])) {{ $order['shipping_provider']['name'] }}
                @elseif (!empty($order['deliveryRider']['name']))
                    <x-edz.badge tone="accent" sm>
                        <x-edz.icon name="user" class="w-3 h-3" />
                        {{ $order['deliveryRider']['name'] }}
                    </x-edz.badge>
                @else <span class="text-warning font-medium">{{ $spHint }}</span> @endif
            </span>
        @endif
    @endif

    {{-- city --}}
    @if (in_array('city', $this->visibleColumns))
        @if ($this->editingField === 'order.city' && $this->editingId === $orderId)
            <div class="edz-inline-edit__edit" wire:key="city-mobile-{{ $orderId }}">
                <p class="text-[10px] text-ink-muted/60 mb-1 truncate"
                    title="{{ __('order_flow.order_original_city') }}">
                    {{ __('order_flow.order_original_city') }}: {{ $order['city']['name'] ?? '—' }}
                </p>
                <x-edz.select wire:model="editingValue"
                    :options="$this->editCityOptions" option-value="id"
                    option-label="name"
                    size="sm" search />
                <div class="edz-inline-edit__actions">
                    <button type="button" class="edz-inline-edit__save" wire:click="saveOrderCity"
                        wire:loading.attr="disabled" wire:loading.class="edz-inline-edit__save--loading">
                        <span>{{ __('buttons.save') }}</span></button>
                    <button type="button" class="edz-inline-edit__cancel"
                        @click="$wire.cancelOrderEdit()">{{ __('buttons.cancel') }}</button>
                </div>
            </div>
        @elseif ($canManage && !empty($order['state_id']))
            <button type="button" class="edz-inline-edit__display edz-inline-edit__display--touch w-full text-left"
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
                <x-edz.select wire:model="editingValue" :options="$this->editStopdeskOptions" option-code="code" size="sm" search
                    placeholder="{{ __('merchant_panel.stop_desk_label') }}" />
                <div class="edz-inline-edit__actions">
                    <button type="button" class="edz-inline-edit__save" wire:click="saveOrderStopdesk"
                        wire:loading.attr="disabled"><span>{{ __('buttons.save') }}</span></button>
                    <button type="button" class="edz-inline-edit__cancel"
                        @click="$wire.cancelOrderEdit()">{{ __('buttons.cancel') }}</button>
                </div>
            </div>
        @elseif ($canManage)
            <button type="button" class="edz-inline-edit__display edz-inline-edit__display--touch w-full text-left"
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
            <button type="button" class="edz-inline-edit__display edz-inline-edit__display--touch w-full text-left"
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

    {{-- weight_kg --}}
    @if (in_array('weight', $this->visibleColumns))
        @if ($this->editingField === 'order.weight' && $this->editingId === $orderId)
            <div class="edz-inline-edit__edit" wire:key="weight-mobile-{{ $orderId }}">
                <div class="relative w-full">
                    <input type="number" step="0.01" min="0" wire:model="editingValue"
                        wire:keydown.enter="saveOrderWeight" placeholder="0.00"
                        class="edz-inline-edit__input w-full pe-8 @if ($this->editingError) edz-inline-edit__input--error @endif">
                    <span
                        class="pointer-events-none absolute inset-y-0 end-0 flex items-center pe-2 text-xs text-ink-muted">كغ</span>
                </div>
                <div class="edz-inline-edit__actions">
                    <button type="button" class="edz-inline-edit__save" wire:click="saveOrderWeight"
                        wire:loading.attr="disabled" wire:loading.class="edz-inline-edit__save--loading">
                        <span>{{ __('buttons.save') }}</span>
                    </button>
                    <button type="button" class="edz-inline-edit__cancel"
                        wire:click="cancelOrderEdit">{{ __('buttons.cancel') }}</button>
                </div>
                @if ($this->editingError)
                    <p class="edz-inline-edit__error">{{ $this->editingError }}</p>
                @endif
            </div>
        @elseif ($canManage)
            <button type="button" class="edz-inline-edit__display edz-inline-edit__display--touch w-full text-left"
                wire:click="startOrderWeightEdit('{{ $orderId }}')">
                <x-edz.icon name="cube" class="w-3 h-3 shrink-0 text-ink-muted" />
                <span class="edz-inline-edit__value">{{ $order['weight_kg'] ? $order['weight_kg'] . ' كغ' : '—' }}</span>
            </button>
        @else
            <span class="inline-flex items-center gap-1">
                <x-edz.icon name="cube" class="w-3 h-3 shrink-0 text-ink-muted" />
                {{ $order['weight_kg'] ? $order['weight_kg'] . ' كغ' : '—' }}
            </span>
        @endif
    @endif

    {{-- shipment_type --}}
    @if (in_array('shipment_type', $this->visibleColumns))
        @php
            $shipmentLabelMobile = collect($this->editShipmentTypeOptions ?? [])
                ->firstWhere('value', $order['shipment_type'] ?? null);
        @endphp
        @if ($this->editingField === 'order.shipment_type' && $this->editingId === $orderId)
            <div class="edz-inline-edit__edit" wire:key="shipment-type-mobile-{{ $orderId }}">
                <x-edz.select wire:model="editingValue" :options="$this->editShipmentTypeOptions" size="sm" />
                <div class="edz-inline-edit__actions">
                    <button type="button" class="edz-inline-edit__save" wire:click="saveOrderShipmentType"
                        wire:loading.attr="disabled"
                        wire:loading.class="edz-inline-edit__save--loading"><span>{{ __('buttons.save') }}</span></button>
                    <button type="button" class="edz-inline-edit__cancel"
                        @click="$wire.cancelOrderEdit()">{{ __('buttons.cancel') }}</button>
                </div>
                @if ($this->editingError)
                    <p class="edz-inline-edit__error">{{ $this->editingError }}</p>
                @endif
            </div>
        @elseif ($canManage)
            <button type="button" class="edz-inline-edit__display edz-inline-edit__display--touch w-full text-left"
                @click="$wire.startOrderShipmentTypeEdit('{{ $orderId }}')">
                <x-edz.icon name="tag" class="w-3 h-3 shrink-0 text-ink-muted" />
                <span class="edz-inline-edit__value">{{ $shipmentLabelMobile['label'] ?? ($order['shipment_type'] ?? '—') }}</span>
            </button>
        @else
            <span class="inline-flex items-center gap-1">
                <x-edz.icon name="tag" class="w-3 h-3 shrink-0 text-ink-muted" />
                {{ $shipmentLabelMobile['label'] ?? ($order['shipment_type'] ?? '—') }}
            </span>
        @endif
    @endif

    {{-- send_from_carrier_warehouse (tap-to-toggle pill, no edit mode) --}}
    @if (in_array('send_from_carrier_warehouse', $this->visibleColumns))
        @if ($canManage && ! $this->showTrash)
            <button type="button" wire:click="toggleSendFromWarehouse('{{ $orderId }}')"
                wire:loading.attr="disabled" wire:loading.class="opacity-60 pointer-events-none"
                wire:target="toggleSendFromWarehouse"
                title="{{ __('merchant_panel.send_from_carrier_warehouse') }}"
                class="inline-flex items-center gap-2 min-h-11 w-full text-left cursor-pointer transition hover:opacity-80">
                @if ($order['send_from_carrier_warehouse'] ?? false)
                    <x-edz.badge tone="success" sm>
                        <x-edz.icon name="check" class="w-3 h-3" />
                        {{ __('merchant_panel.send_from_carrier_warehouse') }}
                    </x-edz.badge>
                @else
                    <x-edz.badge tone="neutral" sm>
                        <x-edz.icon name="x-mark" class="w-3 h-3" />
                        {{ __('merchant_panel.send_from_carrier_warehouse') }}
                    </x-edz.badge>
                @endif
            </button>
        @else
            <span class="inline-flex items-center gap-1">
                @if ($order['send_from_carrier_warehouse'] ?? false)
                    <x-edz.badge tone="success" sm>
                        <x-edz.icon name="check" class="w-3 h-3" />
                        {{ __('merchant_panel.send_from_carrier_warehouse') }}
                    </x-edz.badge>
                @else
                    <x-edz.badge tone="neutral" sm>
                        <x-edz.icon name="x-mark" class="w-3 h-3" />
                        {{ __('merchant_panel.send_from_carrier_warehouse') }}
                    </x-edz.badge>
                @endif
            </span>
        @endif
    @endif

    {{-- meta (read-only key: value list) --}}
    @if (in_array('meta', $this->visibleColumns))
        @php
            $metaEntriesMobile = collect($order['meta'] ?? []);
        @endphp
        @if ($metaEntriesMobile->isNotEmpty())
            <div class="space-y-1">
                @foreach ($metaEntriesMobile as $metaKeyMobile => $metaValueMobile)
                    <div class="flex items-baseline gap-2">
                        <span class="shrink-0 text-ink-muted/60">{{ $metaKeyMobile }}:</span>
                        <span class="min-w-0 break-words">{{ $metaValueMobile }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>
