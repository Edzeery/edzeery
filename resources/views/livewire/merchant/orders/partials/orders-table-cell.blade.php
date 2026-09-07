{{-- Orders table body cell: renders the <td> for a single column key.
    Receives: $order (array), $colKey (string), $orderId (string), $transitions (array). --}}

@switch($colKey)
    @case('number')
        <td class="px-4 py-3 font-mono font-semibold text-ink">
            <span class="inline-flex items-center">#{{ $order['number'] }}</span>
        </td>
        @break

    @case('source')
        <td class="px-4 py-3 text-xs">
            @if (filled($order['created_by_membership_id']))
                <x-edz.badge tone="neutral" sm>
                    <x-edz.icon name="user" class="w-3 h-3" />
                    {{ __('merchant.delivery_man') }}
                </x-edz.badge>
            @else
                <x-edz.badge tone="accent" sm>
                    <x-edz.icon name="shopping-bag" class="w-3 h-3" />
                    {{ __('merchant_panel.store') }}
                </x-edz.badge>
            @endif
        </td>
        @break

    @case('customer')
        <td class="px-4 py-3">
            @if ($this->editingField === 'order.customer_name' && $this->editingId === $orderId)
                <div class="edz-inline-edit__edit" wire:key="name-inline-{{ $orderId }}">
                    <input type="text" wire:model="nameEditName" wire:keydown.enter="saveOrderName"
                        placeholder="{{ __('merchant_panel.name') }}"
                        class="edz-inline-edit__input @if ($this->editingError) edz-inline-edit__input--error @endif">
                    <div class="edz-inline-edit__actions">
                        <button type="button" class="edz-inline-edit__save" wire:click="saveOrderName"
                            wire:loading.attr="disabled" wire:loading.class="edz-inline-edit__save--loading">
                            <span>{{ __('buttons.save') }}</span>
                        </button>
                        <button type="button" class="edz-inline-edit__cancel"
                            wire:click="cancelOrderNameEdit">{{ __('buttons.cancel') }}</button>
                    </div>
                    @if ($this->editingError)
                        <p class="edz-inline-edit__error">{{ $this->editingError }}</p>
                    @endif
                </div>
            @elseif (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
                <button type="button" class="edz-inline-edit__display"
                    wire:click="startOrderNameEdit('{{ $orderId }}')"
                    title="{{ $order['customer']['name'] ?? '-' }}">
                    <span
                        class="edz-inline-edit__value">{{ \Illuminate\Support\Str::limit($order['customer']['name'] ?? '-', 30) }}</span>
                </button>
            @else
                <div class="text-ink font-medium text-xs max-w-[120px] truncate"
                    title="{{ $order['customer']['name'] ?? '-' }}">
                    {{ $order['customer']['name'] ?? '-' }}</div>
            @endif
            @php
                $dupTone = match ($order['dup_level'] ?? null) {
                    'duplicate' => 'danger',
                    'probable' => 'warning',
                    'repeat' => 'neutral',
                    default => null,
                };
                $dupLabel = match ($order['dup_level'] ?? null) {
                    'duplicate' => __('order_flow.dup_badge_duplicate'),
                    'probable' => __('order_flow.dup_badge_probable'),
                    'repeat' => __('order_flow.dup_badge_repeat'),
                    default => null,
                };
                $dupCount = (int) ($order['duplicate_count'] ?? $order['repeat_count'] ?? 0);
            @endphp
            @if (!$this->showTrash && ($order['status_key'] ?? null) !== 'duplicate' && $dupTone)
                <div class="flex items-center gap-1.5 min-w-0 mt-1">
                    <button type="button" wire:click="openDuplicateScan('{{ $orderId }}')"
                        title="{{ __('order_flow.duplicate_warnings_title') }}"
                        class="edz-badge edz-badge--{{ $dupTone }} edz-badge--sm shrink-0 cursor-pointer transition hover:brightness-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-warning/40">
                        <x-edz.icon name="copy" class="w-3 h-3" />
                        {{ $dupLabel }}@if (($order['dup_level'] ?? null) !== 'repeat')
                            ×{{ min($dupCount, 9) }}{{ $dupCount > 9 ? '+' : '' }}
                        @endif
                    </button>
                </div>
            @endif
            @if (!$this->showTrash && !empty($order['missing']) && canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
                <div class="flex items-center gap-1.5 min-w-0 mt-1">
                    <button type="button" wire:click="startMissingFieldEdit('{{ $orderId }}')"
                        title="{{ __('order_flow.bulk_send_reason_missing', ['fields' => implode('، ', $order['missing'])]) }}"
                        class="edz-badge edz-badge--info edz-badge--sm shrink-0 cursor-pointer transition hover:brightness-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-warning/40">
                        <x-edz.icon name="exclamation-triangle" class="w-3 h-3" />
                        ×{{ min(count($order['missing']), 9) }}{{ count($order['missing']) > 9 ? '+' : '' }}
                    </button>
                </div>
            @endif
        </td>
        @break

    @case('phone')
        <td class="px-4 py-3">
            @if ($this->editingField === 'order.phone' && $this->editingId === $orderId)
                <div class="edz-inline-edit__edit" wire:key="phone-inline-{{ $orderId }}">
                    <input type="tel" wire:model="phoneEditPhone" wire:keydown.enter="saveOrderPhone"
                        placeholder="{{ __('merchant_panel.phone') }}"
                        class="edz-inline-edit__input @if ($this->editingError) edz-inline-edit__input--error @endif">
                    <input type="tel" wire:model="phoneEditSecondary" wire:keydown.enter="saveOrderPhone"
                        placeholder="{{ __('merchant_panel.phone_secondary') }}"
                        class="edz-inline-edit__input @if ($this->editingError) edz-inline-edit__input--error @endif">
                    <div class="edz-inline-edit__actions">
                        <button type="button" class="edz-inline-edit__save" wire:click="saveOrderPhone"
                            wire:loading.attr="disabled" wire:loading.class="edz-inline-edit__save--loading">
                            <span>{{ __('buttons.save') }}</span>
                        </button>
                        <button type="button" class="edz-inline-edit__cancel"
                            wire:click="cancelOrderPhoneEdit">{{ __('buttons.cancel') }}</button>
                    </div>
                    @if ($this->editingError)
                        <p class="edz-inline-edit__error">{{ $this->editingError }}</p>
                    @endif
                </div>
            @elseif (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
                <button type="button" class="edz-inline-edit__display"
                    wire:click="startOrderPhoneEdit('{{ $orderId }}')">
                    <span class="edz-inline-edit__value"
                        dir="ltr">{{ $order['customer']['phone'] ?? '—' }}
                        @if (!empty($order['phone_secondary']))
                            <span class="text-ink-muted/60"> · {{ $order['phone_secondary'] }}</span>
                        @endif
                    </span>
                </button>
            @else
                <span dir="ltr">{{ $order['customer']['phone'] ?? '-' }}
                    @if (!empty($order['phone_secondary']))
                        · {{ $order['phone_secondary'] }}
                    @endif
                </span>
            @endif
        </td>
        @break

    @case('verification')
        <td class="px-4 py-3">
            <span class="inline-flex items-center gap-1 text-ink-muted text-xs"
                title="{{ __('merchant_panel.verification_hint') }}">
                <x-edz.icon name="shield-check" class="w-3.5 h-3.5" />
                —
            </span>
        </td>
        @break

    @case('notes')
        <td class="px-4 py-3 text-xs text-ink-muted max-w-[200px]"
            @if ($this->editingField !== 'order.notes' || $this->editingId !== $orderId)
                title="{{ $order['notes'] ?? '' }}"
            @endif>
            @if ($this->editingField === 'order.notes' && $this->editingId === $orderId)
                <div class="edz-inline-edit__edit" wire:key="notes-inline-{{ $orderId }}">
                    <textarea wire:model="editingValue" wire:keydown.enter="saveOrderNotes"
                        rows="2" placeholder="{{ __('merchant_panel.notes') }}"
                        class="edz-inline-edit__input @if ($this->editingError) edz-inline-edit__input--error @endif"></textarea>
                    <div class="edz-inline-edit__actions">
                        <button type="button" class="edz-inline-edit__save" wire:click="saveOrderNotes"
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
            @elseif (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
                <button type="button" class="edz-inline-edit__display text-left"
                    wire:click="startOrderNotesEdit('{{ $orderId }}')"
                    title="{{ $order['notes'] ?? '' }}">
                    <span
                        class="edz-inline-edit__value break-words">{{ $order['notes'] ? \Illuminate\Support\Str::limit($order['notes'], 40) : '—' }}</span>
                </button>
            @else
                <span class="truncate block" title="{{ $order['notes'] ?? '' }}">
                    {{ $order['notes'] ? \Illuminate\Support\Str::limit($order['notes'], 30) : '-' }}
                </span>
            @endif
        </td>
        @break

    @case('meta')
        @php
            $metaEntries = collect($order['meta'] ?? [])
                ->map(fn($v, $k) => "{$k}: {$v}")
                ->implode(', ');
        @endphp
        <td class="px-4 py-3 text-xs text-ink-muted max-w-[200px] truncate" title="{{ $metaEntries }}">
            {{ $metaEntries ?: '-' }}
        </td>
        @break

    @case('wilaya')
        <td class="px-4 py-3 text-ink-muted text-xs">
            @if ($this->editingField === 'order.wilaya' && $this->editingId === $orderId)
                <div class="edz-inline-edit__edit" wire:key="wilaya-inline-{{ $orderId }}">
                    <select wire:change="saveOrderWilaya($event.target.value)"
                        class="edz-inline-edit__input @if ($this->editingError) edz-inline-edit__input--error @endif">
                        @foreach ($this->allStates as $st)
                            <option value="{{ $st['id'] }}"
                                @if ((string) $this->editingValue === (string) $st['id']) selected @endif>
                                {{ $st['name'] }}
                            </option>
                        @endforeach
                    </select>
                    <div class="edz-inline-edit__actions">
                        <button type="button" class="edz-inline-edit__cancel"
                            @click="$wire.cancelOrderEdit()">Cancel</button>
                    </div>
                    @if ($this->editingError)
                        <p class="edz-inline-edit__error">{{ $this->editingError }}</p>
                    @endif
                </div>
            @elseif (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
                <button type="button" class="edz-inline-edit__display"
                    @click="$wire.startOrderWilayaEdit('{{ $orderId }}')">
                    <span class="edz-inline-edit__value">{{ $order['state']['name'] ?? '—' }}</span>
                </button>
            @else
                {{ $order['state']['name'] ?? '-' }}
            @endif
        </td>
        @break

    @case('products')
        <td class="px-4 py-3 text-xs text-ink-muted max-w-[200px] truncate"
            title="{{ collect($order['items_summary'] ?? [])->map(fn($i) => $i['name'] . ' ×' . $i['qty'])->implode(', ') }}">
            @foreach ($order['items_summary'] ?? [] as $item)
                {{ $item['name'] }} ×{{ $item['qty'] }}@if (!$loop->last),@endif
            @endforeach
        </td>
        @break

    @case('quantity')
        <td class="px-4 py-3 text-xs text-ink-muted tabular-nums text-center">
            @foreach ($order['items_summary'] ?? [] as $item)
                <div>{{ $item['qty'] }}</div>
            @endforeach
        </td>
        @break

    @case('price')
        <td class="px-4 py-3 text-xs text-ink-muted tabular-nums">
            @foreach ($order['items_summary'] ?? [] as $item)
                <div>{{ currency($item['price']) }}</div>
            @endforeach
        </td>
        @break

    @case('total')
        <td class="px-4 py-3 font-semibold text-ink tabular-nums">
            {{ currency($order['display_total'] ?? $order['total_amount'] ?? 0) }}
        </td>
        @break

    @case('discount')
        <td class="px-4 py-3 text-ink-muted text-xs tabular-nums">
            @if ($this->editingField === 'order.discount' && $this->editingId === $orderId)
                <div class="edz-inline-edit__edit edz-inline-edit__edit--wide" wire:key="discount-inline-{{ $orderId }}">
                    <div class="flex flex-col gap-1.5">
                        <x-edz.select wire:model.live="discountEditType" :options="[
                            ['value' => '', 'label' => __('merchant_panel.no_discount')],
                            ['value' => 'amount', 'label' => __('merchant_panel.fixed_amount')],
                            ['value' => 'percent', 'label' => __('merchant_panel.percentage')],
                        ]" size="sm" />
                        @if ($this->discountEditType)
                            <input type="number" step="0.01" min="0" wire:model="discountEditValue"
                                placeholder="{{ $this->discountEditType === 'percent' ? '%' : 'DZD' }}"
                                class="edz-inline-edit__input @if ($this->editingError) edz-inline-edit__input--error @endif">
                            <input type="text" maxlength="255" wire:model="discountEditReason"
                                placeholder="{{ __('merchant_panel.discount_reason') }}"
                                class="edz-inline-edit__input">
                        @endif
                    </div>
                    <div class="edz-inline-edit__actions">
                        <button type="button" class="edz-inline-edit__save" wire:click="saveOrderDiscount"
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
            @elseif (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
                <button type="button" class="edz-inline-edit__display"
                    @click="$wire.startOrderDiscountEdit('{{ $orderId }}')"
                    title="@if (($order['discount_type'] ?? '') === 'percent'){{ $order['discount_value'] ?? '' }}%@elseif (($order['discount_type'] ?? '') === 'amount'){{ currency($order['discount_value'] ?? 0) }}@endif">
                    <span class="edz-inline-edit__value">
                        @if ((float) ($order['discount_amount'] ?? 0) > 0)
                            −{{ currency($order['discount_amount']) }}
                        @else
                            —
                        @endif
                    </span>
                </button>
            @else
                @if ((float) ($order['discount_amount'] ?? 0) > 0)
                    −{{ currency($order['discount_amount']) }}
                @else
                    —
                @endif
            @endif
        </td>
        @break

    @case('shipping_cost')
        <td class="px-4 py-3 text-ink-muted text-xs">
            @if ((float) ($order['shipping_cost'] ?? 0) <= 0)
                <x-edz.badge tone="neutral" sm>
                    <x-edz.icon name="truck" class="w-3 h-3" />
                    {{ __('merchant_panel.shipping_free') }}</x-edz.badge>
            @else
                <span class="tabular-nums">{{ currency((float) ($order['shipping_cost'] ?? 0)) }}</span>
            @endif
        </td>
        @break

    @case('weight')
        <td class="px-4 py-3 text-ink-muted text-xs">
            @if ($this->editingField === 'order.weight' && $this->editingId === $orderId)
                <div class="edz-inline-edit__edit" wire:key="weight-inline-{{ $orderId }}">
                    <input type="number" step="0.01" min="0" wire:model="editingValue" wire:keydown.enter="saveOrderWeight"
                        placeholder="0.00"
                        class="edz-inline-edit__input @if ($this->editingError) edz-inline-edit__input--error @endif">
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
            @elseif (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
                <button type="button" class="edz-inline-edit__display"
                    wire:click="startOrderWeightEdit('{{ $orderId }}')">
                    <span class="edz-inline-edit__value">{{ $order['weight_kg'] ? $order['weight_kg'] . ' kg' : '—' }}</span>
                </button>
            @else
                {{ $order['weight_kg'] ? $order['weight_kg'] . ' kg' : '—' }}
            @endif
        </td>
        @break

    @case('shipment_type')
        @php
            $shipmentLabel = collect($this->editShipmentTypeOptions ?? [])
                ->firstWhere('value', $order['shipment_type'] ?? null);
        @endphp
        <td class="px-4 py-3 text-ink-muted text-xs">
            @if ($this->editingField === 'order.shipment_type' && $this->editingId === $orderId)
                <div class="edz-inline-edit__edit" wire:key="shipment-type-inline-{{ $orderId }}">
                    <x-edz.select wire:model="editingValue" :options="$this->editShipmentTypeOptions" size="sm" />
                    <div class="edz-inline-edit__actions">
                        <button type="button" class="edz-inline-edit__save" wire:click="saveOrderShipmentType"
                            wire:loading.attr="disabled" wire:loading.class="edz-inline-edit__save--loading">
                            <span>{{ __('buttons.save') }}</span>
                        </button>
                        <button type="button" class="edz-inline-edit__cancel"
                            @click="$wire.cancelOrderEdit()">{{ __('buttons.cancel') }}</button>
                    </div>
                    @if ($this->editingError)
                        <p class="edz-inline-edit__error">{{ $this->editingError }}</p>
                    @endif
                </div>
            @elseif (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
                <button type="button" class="edz-inline-edit__display"
                    @click="$wire.startOrderShipmentTypeEdit('{{ $orderId }}')">
                    <span class="edz-inline-edit__value">{{ $shipmentLabel['label'] ?? ($order['shipment_type'] ?? '—') }}</span>
                </button>
            @else
                {{ $shipmentLabel['label'] ?? ($order['shipment_type'] ?? '-') }}
            @endif
        </td>
        @break

    @case('city')
        <td class="px-4 py-3 text-ink-muted text-xs">
            @if ($this->editingField === 'order.city' && $this->editingId === $orderId)
                <div class="edz-inline-edit__edit" wire:key="city-inline-{{ $orderId }}">
                    <select wire:change="saveOrderCity($event.target.value)"
                        class="edz-inline-edit__input @if ($this->editingError) edz-inline-edit__input--error @endif">
                        @foreach ($this->editCityOptions as $ct)
                            <option value="{{ $ct['id'] }}"
                                @if ((string) $this->editingValue === (string) $ct['id']) selected @endif>
                                {{ $ct['name'] }}
                            </option>
                        @endforeach
                    </select>
                    <div class="edz-inline-edit__actions">
                        <button type="button" class="edz-inline-edit__cancel"
                            @click="$wire.cancelOrderEdit()">Cancel</button>
                    </div>
                    @if ($this->editingError)
                        <p class="edz-inline-edit__error">{{ $this->editingError }}</p>
                    @endif
                </div>
            @elseif (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value) && !empty($order['state_id']))
                <button type="button" class="edz-inline-edit__display"
                    @click="$wire.startOrderCityEdit('{{ $orderId }}')">
                    <span class="edz-inline-edit__value">{{ $order['city']['name'] ?? '—' }}</span>
                </button>
            @else
                {{ $order['city']['name'] ?? '-' }}
            @endif
        </td>
        @break

    @case('address')
        <td class="px-4 py-3 text-xs text-ink-muted max-w-[200px] truncate"
            @if ($this->editingField !== 'order.address' || $this->editingId !== $orderId)
                title="{{ $order['address'] ?? '' }}"
            @endif>
            @if ($this->editingField === 'order.address' && $this->editingId === $orderId)
                <div class="edz-inline-edit__edit" wire:key="address-inline-{{ $orderId }}">
                    <input type="text" wire:model="editingValue" wire:keydown.enter="saveOrderAddress"
                        placeholder="{{ __('merchant_panel.address') }}"
                        class="edz-inline-edit__input @if ($this->editingError) edz-inline-edit__input--error @endif">
                    <div class="edz-inline-edit__actions">
                        <button type="button" class="edz-inline-edit__save" wire:click="saveOrderAddress"
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
            @elseif (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
                <button type="button" class="edz-inline-edit__display"
                    wire:click="startOrderAddressEdit('{{ $orderId }}')"
                    title="{{ $order['address'] ?? '' }}">
                    <span
                        class="edz-inline-edit__value">{{ $order['address'] ? \Illuminate\Support\Str::limit($order['address'], 40) : '—' }}</span>
                </button>
            @else
                {{ $order['address'] ? \Illuminate\Support\Str::limit($order['address'], 40) : '-' }}
            @endif
        </td>
        @break

    @case('delivery_type')
        <td class="px-4 py-3 text-ink-muted text-xs">
            @if ($this->editingField === 'order.delivery_type' && $this->editingId === $orderId)
                <div class="edz-inline-edit__edit" wire:key="delivery-type-inline-{{ $orderId }}">
                    <x-edz.select wire:model="editingValue" :options="$this->editDeliveryTypeOptions" size="sm" />
                    <div class="edz-inline-edit__actions">
                        <button type="button" class="edz-inline-edit__save" wire:click="saveOrderDeliveryType"
                            wire:loading.attr="disabled" wire:loading.class="edz-inline-edit__save--loading">
                            <span>{{ __('buttons.save') }}</span>
                        </button>
                        <button type="button" class="edz-inline-edit__cancel"
                            @click="$wire.cancelOrderEdit()">{{ __('buttons.cancel') }}</button>
                    </div>
                    @if ($this->editingError)
                        <p class="edz-inline-edit__error">{{ $this->editingError }}</p>
                    @endif
                </div>
            @elseif (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
                <button type="button" class="edz-inline-edit__display"
                    @click="$wire.startOrderDeliveryTypeEdit('{{ $orderId }}')">
                    <span class="edz-inline-edit__value">{{ $order['delivery_type'] === 'stopdesk' ? __('merchant_panel.stop_desk_label') : ($order['delivery_type'] === 'home' ? __('merchant_panel.home_delivery_label') : $order['delivery_type'] ?? '—') }}</span>
                </button>
            @else
                {{ $order['delivery_type'] === 'stopdesk' ? __('merchant_panel.stop_desk_label') : ($order['delivery_type'] === 'home' ? __('merchant_panel.home_delivery_label') : $order['delivery_type'] ?? '-') }}
            @endif
        </td>
        @break

    @case('shipping_provider')
        <td class="px-4 py-3 text-ink-muted text-xs">
            @if ($this->editingField === 'order.shipping_provider' && $this->editingId === $orderId)
                <div class="edz-inline-edit__edit" wire:key="provider-inline-{{ $orderId }}">
                    <x-edz.select wire:model="editingValue" :options="$this->editProviderOptions" size="sm" search
                        placeholder="{{ __('merchant_panel.shipping_provider') }}" />
                    <div class="edz-inline-edit__actions">
                        <button type="button" class="edz-inline-edit__save" wire:click="saveOrderProvider"
                            wire:loading.attr="disabled" wire:loading.class="edz-inline-edit__save--loading">
                            <span>{{ __('buttons.save') }}</span>
                        </button>
                        <button type="button" class="edz-inline-edit__cancel"
                            @click="$wire.cancelOrderEdit()">{{ __('buttons.cancel') }}</button>
                    </div>
                    @if ($this->editingError)
                        <p class="edz-inline-edit__error">{{ $this->editingError }}</p>
                    @endif
                </div>
            @elseif (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
                <button type="button" class="edz-inline-edit__display"
                    @click="$wire.startOrderProviderEdit('{{ $orderId }}')">
                    <span class="edz-inline-edit__value">{{ $order['shippingProvider']['name'] ?? '—' }}</span>
                </button>
            @else
                {{ $order['shippingProvider']['name'] ?? '-' }}
            @endif
        </td>
        @break

    @case('stopdesk_point')
        <td class="px-4 py-3 text-xs text-ink-muted">
            @if ($this->editingField === 'order.stopdesk_point' && $this->editingId === $orderId)
                <div class="edz-inline-edit__edit" wire:key="stopdesk-inline-{{ $orderId }}">
                    <x-edz.select wire:model="editingValue" :options="$this->editStopdeskOptions" size="sm" search
                        placeholder="{{ __('merchant_panel.stop_desk_label') }}" />
                    <div class="edz-inline-edit__actions">
                        <button type="button" class="edz-inline-edit__save" wire:click="saveOrderStopdesk"
                            wire:loading.attr="disabled" wire:loading.class="edz-inline-edit__save--loading">
                            <span>{{ __('buttons.save') }}</span>
                        </button>
                        <button type="button" class="edz-inline-edit__cancel"
                            @click="$wire.cancelOrderEdit()">{{ __('buttons.cancel') }}</button>
                    </div>
                    @if ($this->editingError)
                        <p class="edz-inline-edit__error">{{ $this->editingError }}</p>
                    @endif
                </div>
            @elseif (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
                <button type="button" class="edz-inline-edit__display"
                    @click="$wire.startOrderStopdeskEdit('{{ $orderId }}')">
                    <span class="edz-inline-edit__value">{{ $order['stopdesk_point']['name'] ?? '—' }}@if (!empty($order['stopdesk_point']['city']['name']))
                            ({{ $order['stopdesk_point']['city']['name'] }})
                        @endif</span>
                </button>
            @else
                {{ $order['stopdesk_point']['name'] ?? '-' }}@if (!empty($order['stopdesk_point']['city']['name']))
                    ({{ $order['stopdesk_point']['city']['name'] }})
                @endif
            @endif
        </td>
        @break

    @case('send_from_carrier_warehouse')
        <td class="px-4 py-3">
            @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value) && !$this->showTrash)
                <button type="button" wire:click="toggleSendFromWarehouse('{{ $orderId }}')"
                    wire:loading.attr="disabled" wire:loading.class="edz-inline-edit__save--loading" wire:target="toggleSendFromWarehouse"
                    title="{{ __('merchant_panel.send_from_carrier_warehouse') }}"
                    class="inline-flex items-center cursor-pointer transition hover:opacity-80">
                    @if ($order['send_from_carrier_warehouse'] ?? false)
                        <x-edz.badge tone="success" sm>
                            <x-edz.icon name="check" class="w-3 h-3" />
                        </x-edz.badge>
                    @else
                        <x-edz.badge tone="neutral" sm>
                            <x-edz.icon name="x-mark" class="w-3 h-3" />
                        </x-edz.badge>
                    @endif
                </button>
            @else
                @if ($order['send_from_carrier_warehouse'] ?? false)
                    <x-edz.badge tone="success" sm>
                        <x-edz.icon name="check" class="w-3 h-3" />
                    </x-edz.badge>
                @else
                    <x-edz.badge tone="neutral" sm>
                        <x-edz.icon name="x-mark" class="w-3 h-3" />
                    </x-edz.badge>
                @endif
            @endif
        </td>
        @break

    @case('status')
        <td class="px-4 py-3">
            <div class="relative" @click.away="open = false">
                <button @click="openStatusMenu()" x-ref="trigger"
                    class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-full cursor-pointer hover:opacity-80 {{ \Edzeery\MyStatusKit\Facades\Status::for('general', $order['status']['color'] ?? 'gray')->color() }}">
                    {!! \Edzeery\MyStatusKit\Facades\Status::for('order', $order['status']['key'] ?? 'default')->icon(
                        null,
                        'w-3 h-3 shrink-0',
                    ) !!}
                    {{ \Edzeery\MyStatusKit\Facades\Status::for('order', $order['status']['key'] ?? 'default')->label() }}
                    <x-edz.icon name="chevron-down" class="w-3 h-3" />
                </button>
                <div x-show="open" x-cloak class="fixed inset-0 z-[205] bg-black/40 backdrop-blur-sm sm:hidden"
                    @click="open = false"></div>
                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-3"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-3" :style="menuStyle"
                    class="fixed inset-x-0 bottom-0 z-[210] w-full rounded-t-2xl border border-b-0 border-surface-border bg-surface
                           p-3 pb-[calc(1rem+env(safe-area-inset-bottom))]
                           sm:inset-x-auto sm:bottom-auto sm:z-[200] sm:w-56 sm:rounded-xl sm:border-b sm:p-1.5 sm:pb-1.5
                           sm:shadow-lg shadow-[0_-16px_48px_-12px_rgba(15,23,42,.25)] max-h-[70vh] overflow-y-auto edz-scroll sm:max-h-64">
                    <span class="pointer-events-none mx-auto mb-2 block h-1 w-10 rounded-full bg-surface-border sm:hidden"></span>
                    <div class="flex items-center justify-between gap-2 px-1 mb-1.5 sm:hidden">
                        <p class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink uppercase tracking-wide">
                            <x-edz.icon name="chevron-down" class="w-3.5 h-3.5 text-ink-muted" />
                            <span>{{ __('merchant_panel.status') }}</span>
                        </p>
                        <button @click="open = false" type="button"
                            class="-m-1 p-1 rounded-lg text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                            title="{{ __('general.close') }}">
                            <x-edz.icon name="x-mark" class="w-4 h-4" />
                        </button>
                    </div>
                    @foreach ($this->allStatuses as $s)
                        @if (in_array($s['key'], $transitions) || $s['id'] == $order['status_id'])
                            <button
                                wire:click="transitionOrder('{{ $orderId }}', '{{ $s['key'] }}')"
                                wire:loading.attr="disabled" @click="open = false"
                                class="w-full text-left flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-tertiary disabled:opacity-50 {{ $s['id'] == $order['status_id'] ? 'font-bold' : '' }}">
                                {!! \Edzeery\MyStatusKit\Facades\Status::for('order', $s['key'] ?? 'default')->icon(null, 'w-3 h-3 shrink-0') !!}
                                <span class="w-2 h-2 rounded-full shrink-0"
                                    style="background: {{ \Edzeery\MyStatusKit\Facades\Status::for('general', $s['color'] ?? 'gray')->hex() }}"></span>
                                {{ \Edzeery\MyStatusKit\Facades\Status::for('order', $s['key'] ?? 'default')->label() }}
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>
        </td>
        @break

    @case('assigned_agent')
        <td class="px-4 py-3 text-xs text-ink-muted">
            @if ($this->editingField === 'order.assigned_agent' && $this->editingId === $orderId)
                <div class="edz-inline-edit__edit" wire:key="agent-inline-{{ $orderId }}">
                    <x-edz.select wire:model="editingValue" :options="$this->editAgentOptions" size="sm" search
                        placeholder="{{ __('merchant_panel.assigned_agent') }}" />
                    <div class="edz-inline-edit__actions">
                        <button type="button" class="edz-inline-edit__save" wire:click="saveOrderAgent"
                            wire:loading.attr="disabled" wire:loading.class="edz-inline-edit__save--loading">
                            <span>{{ __('buttons.save') }}</span>
                        </button>
                        <button type="button" class="edz-inline-edit__cancel"
                            @click="$wire.cancelOrderEdit()">{{ __('buttons.cancel') }}</button>
                    </div>
                    @if ($this->editingError)
                        <p class="edz-inline-edit__error">{{ $this->editingError }}</p>
                    @endif
                </div>
            @elseif (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
                <button type="button" class="edz-inline-edit__display"
                    @click="$wire.startOrderAgentEdit('{{ $orderId }}')">
                    <span class="edz-inline-edit__value">{{ $order['assigned_membership']['user']['name'] ?? __('merchant_panel.unassigned') }}</span>
                </button>
            @else
                {{ $order['assigned_membership']['user']['name'] ?? '—' }}
            @endif
        </td>
        @break

    @case('created_at')
        <td class="px-4 py-3 text-ink-muted text-xs">
            {{ \Carbon\Carbon::parse($order['created_at'])->format('M d, Y') }}
        </td>
        @break

    @case('confirmation_attempts')
        <td class="px-4 py-3 text-ink-muted text-xs">
            {{ $order['confirmation_attempts'] ?? 0 }}
        </td>
        @break

    @case('last_contact')
        <td class="px-4 py-3 text-ink-muted text-xs">
            {{ $order['last_contact_at'] ? \Carbon\Carbon::parse($order['last_contact_at'])->diffForHumans() : '—' }}
        </td>
        @break

    @default
        <td class="px-4 py-3 text-ink-muted text-xs">—</td>
@endswitch