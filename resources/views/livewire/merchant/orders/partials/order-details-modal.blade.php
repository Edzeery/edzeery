{{-- Order Details Modal (Phase 23): responsive popup with de-duplicated fields --}}
@if ($this->detailsOrderId)
    @php
        $detailsOrder = collect($this->orders['data'] ?? [])->firstWhere('id', $this->detailsOrderId);
    @endphp
    @if ($detailsOrder)
        @php
            $detailsStatus = \Edzeery\MyStatusKit\Facades\Status::for(
                'order',
                $detailsOrder['status']['key'] ?? 'default',
            );
            $detailsTracking = $detailsOrder['tracking'] ?? null;
        @endphp
        <div @edz-modal-closed.window="$wire.closeOrderDetails()">
            <x-edz.modal  :isOpen="true" size="md" wire:key="order-details-modal">
                <div class="p-6">
                    {{-- Header --}}
                    <div class="flex items-start gap-3">
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-accent-surface text-accent-fg-strong shrink-0">
                            <x-edz.icon name="info-circle" class="w-5 h-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                <h3 class="text-base sm:text-lg font-bold text-ink">
                                    {{ $detailsOrder['number'] ? '#' . $detailsOrder['number'] : __('merchant_panel.order_details') }}
                                </h3>
                                <span
                                    class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-0.5 rounded-full {{ $detailsStatus->color() }}">
                                    {!! $detailsStatus->icon(null, 'w-3.5 h-3.5 shrink-0') !!}
                                    <span>{{ $detailsStatus->label() }}</span>
                                </span>
                            </div>
                            <p class="mt-0.5 text-sm font-medium text-ink truncate">
                                {{ $detailsOrder['customer']['name'] ?? '—' }}</p>
                            <p class="text-xs text-ink-muted truncate" dir="ltr">
                                {{ $detailsOrder['customer']['phone'] ?? '—' }}</p>
                        </div>
                    </div>
                    <div
                        class="mt-3 pt-3 flex flex-wrap items-center gap-x-3 gap-y-1 border-t border-surface-border text-xs text-ink-muted">
                        <span>{{ \Carbon\Carbon::parse($detailsOrder['created_at'])->format('M d, Y') }}</span>
                        @if (!empty($detailsOrder['state']['name']))
                            <span class="inline-flex items-center gap-1">
                                <x-edz.icon name="map-pin" class="w-3.5 h-3.5" />
                                {{ $detailsOrder['state']['name'] }}
                            </span>
                        @endif
                    </div>

                    @php
                        $showItems =
                            !in_array('products', $this->visibleColumns) ||
                            !in_array('total', $this->visibleColumns);
                        $showShipping =
                            !in_array('delivery_type', $this->visibleColumns) ||
                            !in_array('shipment_type', $this->visibleColumns) ||
                            !in_array('weight', $this->visibleColumns) ||
                            !in_array('stopdesk_point', $this->visibleColumns) ||
                            !in_array('send_from_carrier_warehouse', $this->visibleColumns);
                        $showContact =
                            !in_array('address', $this->visibleColumns) ||
                            !in_array('city', $this->visibleColumns) ||
                            !in_array('meta', $this->visibleColumns);
                        $showAssignment =
                            !in_array('assigned_agent', $this->visibleColumns) ||
                            !in_array('confirmation_attempts', $this->visibleColumns) ||
                            !in_array('last_contact', $this->visibleColumns) ||
                            !in_array('notes', $this->visibleColumns) ||
                            !in_array('confirmed_by', $this->visibleColumns);
                    @endphp

                    {{-- Items --}}
                    @if ($showItems)
                        <section class="mt-5">
                            <h4
                                class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-2">
                                <x-edz.icon name="bag" class="w-4 h-4" />
                                {{ __('merchant_panel.products') }}
                            </h4>
                            <div
                                class="rounded-xl border border-surface-border divide-y divide-surface-border overflow-hidden bg-surface-tertiary/30 text-sm">
                                @if (!in_array('products', $this->visibleColumns))
                                    @forelse ($detailsOrder['items_summary'] ?? [] as $item)
                                        <div class="flex items-center justify-between gap-3 px-3 py-2">
                                            <span class="min-w-0 flex-1 truncate text-ink">{{ $item['name'] }}
                                                <span class="text-ink-muted">×{{ $item['qty'] }}</span></span>
                                            <span
                                                class="font-medium text-ink shrink-0">{{ currency($item['price'] * $item['qty']) }}</span>
                                        </div>
                                    @empty
                                        <div class="px-3 py-2 text-ink-muted text-xs">
                                            {{ __('merchant_panel.no_orders_found') }}</div>
                                    @endforelse
                                @endif
                                @if (!in_array('total', $this->visibleColumns))
                                    <div
                                        class="flex items-center justify-between gap-3 px-3 py-2.5 bg-surface font-bold text-ink">
                                        <span>{{ __('merchant_panel.total') }}</span>
                                        <span class="tabular-nums">{{ currency($detailsOrder['display_total'] ?? $detailsOrder['total_amount'] ?? 0) }}</span>
                                    </div>
                                @endif
                            </div>
                        </section>
                    @endif

                    {{-- Shipping & Payment --}}
                    @if ($showShipping)
                        <section class="mt-5">
                            <h4
                                class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-2">
                                <x-edz.icon name="credit-card" class="w-4 h-4" />
                                {{ __('merchant_panel.details_shipping') }}
                            </h4>
                            <dl
                                class="rounded-xl border border-surface-border divide-y divide-surface-border overflow-hidden bg-surface-tertiary/30 text-sm">
                                @if (!in_array('delivery_type', $this->visibleColumns))
                                    <div class="flex items-start justify-between gap-3 px-3 py-2">
                                        <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.delivery') }}
                                        </dt>
                                        <dd class="text-ink text-end">
                                            {{ $detailsOrder['delivery_type'] === 'stopdesk' ? __('merchant_panel.stop_desk_label') : ($detailsOrder['delivery_type'] === 'home' ? __('merchant_panel.home_delivery_label') : $detailsOrder['delivery_type'] ?? '—') }}
                                        </dd>
                                    </div>
                                @endif
                                @if (!in_array('shipment_type', $this->visibleColumns))
                                    <div class="flex items-start justify-between gap-3 px-3 py-2">
                                        <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.shipment') }}
                                        </dt>
                                        <dd class="text-ink text-end capitalize">
                                            {{ $detailsOrder['shipment_type'] ?? '—' }}</dd>
                                    </div>
                                @endif
                                <div class="flex items-start justify-between gap-3 px-3 py-2">
                                    <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.payment_method') }}
                                    </dt>
                                    <dd class="text-ink text-end uppercase">
                                        {{ $detailsOrder['payment_method'] ?? '—' }}</dd>
                                </div>
                                @if (!in_array('weight', $this->visibleColumns))
                                    <div class="flex items-start justify-between gap-3 px-3 py-2">
                                        <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.weight') }}
                                        </dt>
                                        <dd class="text-ink text-end">
                                            {{ $detailsOrder['weight_kg'] ? $detailsOrder['weight_kg'] . ' kg' : '—' }}
                                        </dd>
                                    </div>
                                @endif
                                @if (!in_array('stopdesk_point', $this->visibleColumns) && !empty($detailsOrder['stopdesk_point']))
                                    <div class="flex items-start justify-between gap-3 px-3 py-2">
                                        <dt class="text-ink-muted shrink-0">
                                            {{ __('merchant_panel.stopdesk_point') }}</dt>
                                        <dd class="text-ink text-end">
                                            {{ $detailsOrder['stopdesk_point']['name'] ?? '—' }}@if (!empty($detailsOrder['stopdesk_point']['city']['name']))
                                                ({{ $detailsOrder['stopdesk_point']['city']['name'] }})
                                            @endif
                                        </dd>
                                    </div>
                                @endif
                                @if (!in_array('send_from_carrier_warehouse', $this->visibleColumns))
                                    <div class="flex items-start justify-between gap-3 px-3 py-2">
                                        <dt class="text-ink-muted shrink-0">
                                            {{ __('merchant_panel.send_from_carrier_warehouse') }}</dt>
                                        <dd class="text-ink text-end">
                                            @if ($detailsOrder['send_from_carrier_warehouse'] ?? false)
                                                <x-edz.badge tone="success" sm>
                                                    <x-edz.icon name="check" class="w-3 h-3" />
                                                </x-edz.badge>
                                            @else
                                                <x-edz.badge tone="neutral" sm>
                                                    <x-edz.icon name="x-mark" class="w-3 h-3" />
                                                </x-edz.badge>
                                            @endif
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </section>
                    @endif

                    {{-- Contact & Location --}}
                    @if ($showContact)
                        <section class="mt-5">
                            <h4
                                class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-2">
                                <x-edz.icon name="map-pin" class="w-4 h-4" />
                                {{ __('merchant_panel.details_contact') }}
                            </h4>
                            <dl
                                class="rounded-xl border border-surface-border divide-y divide-surface-border overflow-hidden bg-surface-tertiary/30 text-sm">
                                @if (!in_array('phone_secondary', $this->visibleColumns))
                                    <div class="flex items-start justify-between gap-3 px-3 py-2">
                                        <dt class="text-ink-muted shrink-0">
                                            {{ __('merchant_panel.phone_secondary') }}</dt>
                                        <dd class="text-ink text-end" dir="ltr">
                                            {{ $detailsOrder['phone_secondary'] ?? '—' }}</dd>
                                    </div>
                                @endif
                                @if (!in_array('city', $this->visibleColumns))
                                    <div class="flex items-start justify-between gap-3 px-3 py-2">
                                        <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.city') }}</dt>
                                        <dd class="text-ink text-end">{{ $detailsOrder['city']['name'] ?? '—' }}
                                        </dd>
                                    </div>
                                @endif
                                @if (!in_array('address', $this->visibleColumns))
                                    <div class="flex items-start justify-between gap-3 px-3 py-2">
                                        <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.address') }}
                                        </dt>
                                        <dd class="text-ink text-end min-w-0">
                                            {{ $detailsOrder['address'] ? \Illuminate\Support\Str::limit($detailsOrder['address'], 60) : '—' }}
                                        </dd>
                                    </div>
                                @endif
                                @if (!in_array('meta', $this->visibleColumns) && !empty($detailsOrder['meta']))
                                    <div class="flex items-start justify-between gap-3 px-3 py-2">
                                        <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.meta') }}</dt>
                                        <dd class="text-ink text-end min-w-0">
                                            {{ collect($detailsOrder['meta'])->map(fn($v, $k) => "{$k}: {$v}")->implode(', ') }}
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </section>
                    @endif

                    {{-- Assignment --}}
                    @if ($showAssignment)
                        <section class="mt-5">
                            <h4
                                class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-2">
                                <x-edz.icon name="users" class="w-4 h-4" />
                                {{ __('merchant_panel.assignment') }}
                            </h4>
                            <dl
                                class="rounded-xl border border-surface-border divide-y divide-surface-border overflow-hidden bg-surface-tertiary/30 text-sm">
                                @if (!in_array('assigned_agent', $this->visibleColumns))
                                    <div class="flex items-start justify-between gap-3 px-3 py-2">
                                        <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.agent') }}</dt>
                                        <dd class="text-ink text-end">
                                            {{ $detailsOrder['assigned_membership']['user']['name'] ?? '—' }}</dd>
                                    </div>
                                @endif
                                <div class="flex items-start justify-between gap-3 px-3 py-2">
                                    <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.method') }}</dt>
                                    <dd class="text-ink text-end">
                                        {{ $detailsOrder['assignment_method'] ? ucfirst($detailsOrder['assignment_method']) : '—' }}
                                    </dd>
                                </div>
                                <div class="flex items-start justify-between gap-3 px-3 py-2">
                                    <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.created_by') }}
                                    </dt>
                                    <dd class="text-ink text-end">
                                        {{ $detailsOrder['created_by_membership_id'] ? $detailsOrder['created_by_membership']['user']['name'] ?? '—' : '—' }}
                                    </dd>
                                </div>
                                @if (!in_array('confirmed_by', $this->visibleColumns))
                                    <div class="flex items-start justify-between gap-3 px-3 py-2">
                                        <dt class="text-ink-muted shrink-0">
                                            {{ __('merchant_panel.confirmed_by') }}</dt>
                                        <dd class="text-ink text-end">
                                            {{ $detailsOrder['confirmed_by_history']['changed_by']['user']['name'] ?? '—' }}
                                        </dd>
                                    </div>
                                @endif
                                @if (!in_array('confirmation_attempts', $this->visibleColumns))
                                    <div class="flex items-start justify-between gap-3 px-3 py-2">
                                        <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.attempts') }}
                                        </dt>
                                        <dd class="text-ink text-end">
                                            {{ $detailsOrder['confirmation_attempts'] ?? 0 }}</dd>
                                    </div>
                                @endif
                                @if (!in_array('last_contact', $this->visibleColumns))
                                    <div class="flex items-start justify-between gap-3 px-3 py-2">
                                        <dt class="text-ink-muted shrink-0">
                                            {{ __('merchant_panel.last_contact') }}</dt>
                                        <dd class="text-ink text-end">
                                            {{ $detailsOrder['last_contact_at'] ? \Carbon\Carbon::parse($detailsOrder['last_contact_at'])->diffForHumans() : '—' }}
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                            @if (!in_array('notes', $this->visibleColumns) && !empty($detailsOrder['notes']))
                                <div
                                    class="mt-2 p-2.5 rounded-lg bg-surface-tertiary text-sm text-ink-muted italic">
                                    "{{ $detailsOrder['notes'] }}"
                                </div>
                            @endif
                        </section>
                    @endif

                    {{-- Tracking --}}
                    @if (
                        $detailsTracking &&
                            (!in_array('shipping_provider', $this->visibleColumns) ||
                                !empty($detailsTracking['tracking_number']) ||
                                !empty($detailsTracking['shipped_at']) ||
                                !empty($detailsTracking['delivered_at'])))
                        <section class="mt-5">
                            <h4
                                class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-2">
                                <x-edz.icon name="truck" class="w-4 h-4" />
                                {{ __('merchant_panel.tracking') }}
                            </h4>
                            <dl
                                class="rounded-xl border border-surface-border divide-y divide-surface-border overflow-hidden bg-surface-tertiary/30 text-sm">
                                @if (!in_array('shipping_provider', $this->visibleColumns) && !empty($detailsTracking['shipping_provider']))
                                    <div class="flex items-start justify-between gap-3 px-3 py-2">
                                        <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.carrier') }}
                                        </dt>
                                        <dd class="text-ink text-end">{{ $detailsTracking['shipping_provider'] }}
                                        </dd>
                                    </div>
                                @endif
                                @if (!empty($detailsTracking['tracking_number']))
                                    <div class="flex items-start justify-between gap-3 px-3 py-2">
                                        <dt class="text-ink-muted shrink-0">
                                            {{ __('merchant_panel.tracking_number') }}</dt>
                                        <dd class="text-ink text-end font-mono">
                                            {{ $detailsTracking['tracking_number'] }}</dd>
                                    </div>
                                @endif
                                @if (!empty($detailsTracking['shipped_at']))
                                    <div class="flex items-start justify-between gap-3 px-3 py-2">
                                        <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.shipped_at') }}
                                        </dt>
                                        <dd class="text-ink text-end">{{ $detailsTracking['shipped_at'] }}</dd>
                                    </div>
                                @endif
                                @if (!empty($detailsTracking['delivered_at']))
                                    <div class="flex items-start justify-between gap-3 px-3 py-2">
                                        <dt class="text-ink-muted shrink-0">
                                            {{ __('merchant_panel.delivered_at') }}</dt>
                                        <dd class="text-ink text-end">{{ $detailsTracking['delivered_at'] }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </section>
                    @endif
                </div>
            </x-edz.modal>
        </div>
    @endif
@endif