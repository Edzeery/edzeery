{{-- Shipment drawer — extracted from the tracking index (P29.4). Data flows through the Volt component instance. --}}
@if ($this->drawerTracking)
    <div @edz-modal-closed.window="$wire.closeDrawer()">
        <x-edz.modal :isOpen="true" size="lg" wire:key="tracking-drawer">
            <div class="p-6">
                {{-- Header --}}
                <div class="flex items-start gap-3">
                    <div
                        class="flex items-center justify-center w-10 h-10 rounded-full bg-accent-surface text-accent-fg-strong shrink-0">
                        <x-edz.icon name="truck" class="w-5 h-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <h3 class="text-base sm:text-lg font-bold text-ink">#{{ $this->drawerTracking['number'] }}</h3>
                            @if ($this->drawerTracking['tracking_status'])
                                <span
                                    class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-0.5 rounded-full {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', $this->drawerTracking['tracking_status'])->color() }}">
                                    {!! \Edzeery\MyStatusKit\Facades\Status::for('tracking', $this->drawerTracking['tracking_status'])->icon(null, 'w-3.5 h-3.5 shrink-0') !!}
                                    <span>{{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', $this->drawerTracking['tracking_status'])->label() }}</span>
                                </span>
                            @endif
                        </div>
                        <p class="mt-0.5 text-sm font-medium text-ink">{{ $this->drawerTracking['customer'] }}</p>
                        <p class="text-xs text-ink-muted" dir="ltr">{{ $this->drawerTracking['phone'] }}</p>
                    </div>
                </div>

                {{-- Carrier card --}}
                <section class="mt-5">
                    <h4
                        class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-2">
                        <x-edz.icon name="truck" class="w-4 h-4" />
                        {{ __('order_flow.carrier_card') }}
                    </h4>
                    <dl
                        class="rounded-xl border border-surface-border divide-y divide-surface-border overflow-hidden bg-surface-tertiary/30 text-sm">
                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                            <dt class="text-ink-muted shrink-0">{{ __('order_flow.tracking_provider') }}</dt>
                            <dd class="text-ink text-end">{{ $this->drawerTracking['provider'] }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.tracking_number') }}</dt>
                            <dd class="text-ink text-end font-mono">
                                {{ $this->drawerTracking['tracking_number'] ?? '—' }}
                                @if (!empty($this->drawerTracking['tracking_number']))
                                    <button
                                        x-on:click="navigator.clipboard.writeText('{{ $this->drawerTracking['tracking_number'] }}').then(() => EdzSwal.toast ? EdzSwal.toast('{{ __('order_flow.copy_done') }}') : null)"
                                        class="text-accent-600 hover:text-accent-700 ms-1 align-middle"
                                        title="{{ __('order_flow.tracking_number_copy') }}">
                                        <x-edz.icon name="clipboard" class="w-3.5 h-3.5 inline-block" />
                                    </button>
                                @endif
                            </dd>
                        </div>
                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.shipped_at') }}</dt>
                            <dd class="text-ink text-end">
                                {{ $this->drawerTracking['shipped_at'] ? \Carbon\Carbon::parse($this->drawerTracking['shipped_at'])->format('M d, Y H:i') : '—' }}
                            </dd>
                        </div>
                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.delivered_at') }}</dt>
                            <dd class="text-ink text-end">
                                {{ $this->drawerTracking['delivered_at'] ? \Carbon\Carbon::parse($this->drawerTracking['delivered_at'])->format('M d, Y H:i') : '—' }}
                            </dd>
                        </div>
                    </dl>
                </section>

                {{-- Shipment summary --}}
                <section class="mt-5">
                    <h4
                        class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-2">
                        <x-edz.icon name="bag" class="w-4 h-4" />
                        {{ __('order_flow.shipment_summary') }}
                    </h4>
                    <dl
                        class="rounded-xl border border-surface-border divide-y divide-surface-border overflow-hidden bg-surface-tertiary/30 text-sm">
                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.city') }}</dt>
                            <dd class="text-ink text-end">{{ $this->drawerTracking['city'] }}</dd>
                        </div>
                        @if (!empty($this->drawerTracking['address']))
                            <div class="flex items-start justify-between gap-3 px-3 py-2">
                                <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.address') }}</dt>
                                <dd class="text-ink text-end min-w-0">
                                    {{ \Illuminate\Support\Str::limit($this->drawerTracking['address'], 60) }}</dd>
                            </div>
                        @endif
                        <div class="flex items-start justify-between gap-3 px-3 py-2.5 bg-surface font-bold text-ink">
                            <dt>{{ __('merchant_panel.total') }}</dt>
                            <dd class="tabular-nums">{{ $this->drawerTracking['total'] }}</dd>
                        </div>
                    </dl>
                </section>

                {{-- Quick actions --}}
                @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value) && $this->drawerOrderId)
                    <section class="mt-5">
                        <h4 class="text-xs font-semibold text-ink-muted uppercase tracking-wide mb-2">
                            {{ __('merchant_panel.actions') }}
                        </h4>
                        <div class="flex flex-wrap gap-2">
                            <button wire:click="trackingAction('{{ $this->drawerOrderId }}', 'in_transit')"
                                class="edz-btn edz-btn--ghost edz-btn--sm">
                                {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', 'in_transit')->label() }}
                            </button>
                            <button wire:click="trackingAction('{{ $this->drawerOrderId }}', 'out_for_delivery')"
                                class="edz-btn edz-btn--ghost edz-btn--sm">
                                {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', 'out_for_delivery')->label() }}
                            </button>
                            <button wire:click="trackingAction('{{ $this->drawerOrderId }}', 'failed_attempt')"
                                class="edz-btn edz-btn--ghost edz-btn--sm">
                                {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', 'failed_attempt')->label() }}
                            </button>
                            <button wire:click="trackingAction('{{ $this->drawerOrderId }}', 'returning')"
                                class="edz-btn edz-btn--ghost edz-btn--sm">
                                {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', 'returning')->label() }}
                            </button>
                            <button wire:click="trackingAction('{{ $this->drawerOrderId }}', 'delivered')"
                                class="edz-btn edz-btn--primary edz-btn--sm">
                                {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', 'delivered')->label() }}
                            </button>
                            <button wire:click="trackingAction('{{ $this->drawerOrderId }}', 'returned')"
                                class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600">
                                {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', 'returned')->label() }}
                            </button>
                            <button wire:click="trackingAction('{{ $this->drawerOrderId }}', 'lost')"
                                class="edz-btn edz-btn--ghost edz-btn--sm">
                                {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', 'lost')->label() }}
                            </button>
                            <button wire:click="trackingAction('{{ $this->drawerOrderId }}', 'damaged')"
                                class="edz-btn edz-btn--ghost edz-btn--sm">
                                {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', 'damaged')->label() }}
                            </button>
                        </div>
                    </section>
                @endif

                {{-- Carrier note composer (P33.2) — own dedicated section --}}
                @include('livewire.merchant.tracking.partials.carrier-note-composer')

                {{-- Tracking history — shared partial --}}
                @include('livewire.merchant.tracking.partials.tracking-history-timeline', [
                    'histories' => $this->drawerStatusHistories,
                ])

                {{-- Order events timeline (audit log) — shared partial --}}
                @if ($this->canViewDrawerEvents && !empty($this->drawerEvents))
                    @include('livewire.merchant.orders.partials.order-events-timeline', [
                        'events' => $this->drawerEvents,
                        'icon' => 'list-bullet',
                    ])
                @endif
            </div>
        </x-edz.modal>
    </div>
@endif