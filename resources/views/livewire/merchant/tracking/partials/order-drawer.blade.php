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
                                    <x-edz.tooltip label="{{ __('order_flow.tracking_number_copy') }}">
                                        <button
                                            x-on:click="navigator.clipboard.writeText('{{ $this->drawerTracking['tracking_number'] }}').then(() => EdzSwal.success('', '{{ __('order_flow.copy_done') }}'))"
                                            class="text-accent-600 hover:text-accent-700 ms-1 align-middle">
                                            <x-edz.icon name="clipboard" class="w-3.5 h-3.5 inline-block" />
                                        </button>
                                    </x-edz.tooltip>
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

                {{-- Delivery rider (Phase D) — read view + quick assign (only when not sent via a carrier) --}}
                <section class="mt-5">
                    <h4
                        class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-2">
                        <x-edz.icon name="user" class="w-4 h-4" />
                        {{ __('order_flow.rider_assign_section') }}
                    </h4>
                    <dl
                        class="rounded-xl border border-surface-border divide-y divide-surface-border overflow-hidden bg-surface-tertiary/30 text-sm">
                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                            <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.tab_riders') }}</dt>
                            <dd class="text-ink text-end">{{ $this->drawerTracking['rider_name'] ?? '—' }}</dd>
                        </div>
                    </dl>

                    @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_ASSIGN->value) && ! $this->drawerTracking['has_provider'])
                        <div class="mt-2">
                            <x-edz.dropdown align="right" width="280px"
                                trigger-class="edz-btn edz-btn--ghost edz-btn--sm">
                                <x-slot name="trigger">
                                    <x-edz.icon name="user" class="w-4 h-4" />
                                    <span>{{ $this->drawerTracking['rider_id'] ? __('order_flow.rider_change') : __('order_flow.rider_assign') }}</span>
                                    <x-edz.icon name="chevron-down" class="w-3 h-3" />
                                </x-slot>
                                <div class="edz-dropdown__section">
                                    <button type="button" @click="close()"
                                        wire:click="assignRider('{{ $this->drawerTracking['order_id'] }}')"
                                        aria-pressed="{{ empty($this->drawerTracking['rider_id']) ? 'true' : 'false' }}"
                                        class="edz-dropdown__item justify-between {{ empty($this->drawerTracking['rider_id']) ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                                        {{ __('order_flow.rider_unassign') }}
                                        <x-edz.icon name="check"
                                            class="w-3.5 h-3.5 {{ empty($this->drawerTracking['rider_id']) ? 'opacity-100' : 'opacity-0' }}" />
                                    </button>
                                    @foreach ($this->allRiders as $r)
                                        <button type="button" @click="close()"
                                            wire:click="assignRider('{{ $this->drawerTracking['order_id'] }}', '{{ $r['id'] }}')"
                                            aria-pressed="{{ ($this->drawerTracking['rider_id'] ?? null) === $r['id'] ? 'true' : 'false' }}"
                                            class="edz-dropdown__item justify-between {{ ($this->drawerTracking['rider_id'] ?? null) === $r['id'] ? 'bg-accent-surface text-accent-fg font-semibold' : '' }}">
                                            <span class="truncate">{{ $r['name'] }}</span>
                                            <x-edz.icon name="check"
                                                class="w-3.5 h-3.5 {{ ($this->drawerTracking['rider_id'] ?? null) === $r['id'] ? 'opacity-100' : 'opacity-0' }}" />
                                        </button>
                                    @endforeach
                                </div>
                            </x-edz.dropdown>
                        </div>
                    @endif
                </section>

                {{-- Automatic status sync (Phase C) — statuses update only from the carrier API --}}
                @if ($this->drawerTracking && ! empty($this->drawerTracking['tracking_number']))
                    <section class="mt-5">
                        <h4 class="text-xs font-semibold text-ink-muted uppercase tracking-wide mb-2">
                            {{ __('order_flow.tracking_sync_section') }}
                        </h4>
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-ink-10 bg-surface p-3">
                            <div class="min-w-0">
                                <div class="text-sm text-ink">{{ __('order_flow.tracking_last_synced') }}</div>
                                <div class="text-xs text-ink-muted">
                                    {{ $this->drawerTracking['last_synced_at']
                                        ? $this->drawerTracking['last_synced_at']->format('d/m/Y H:i')
                                        : __('order_flow.tracking_never_synced') }}
                                </div>
                            </div>
                            <button wire:click="syncTracking('{{ $this->drawerTracking['tracking_id'] }}')"
                                wire:loading.attr="disabled" wire:target="syncTracking"
                                class="edz-btn edz-btn--primary edz-btn--sm">
                                <x-edz.icon name="arrow-path" wire:loading.remove wire:target="syncTracking" class="w-4 h-4" />
                                <x-edz.spinner wire:target="syncTracking" class="w-4 h-4" />
                                {{ __('order_flow.tracking_sync_now') }}
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-ink-muted">{{ __('order_flow.tracking_sync_hint') }}</p>
                    </section>
                @endif

                {{-- Carrier note composer (P33.2) — own dedicated section --}}
                @include('livewire.merchant.tracking.partials.carrier-note-composer')

                {{-- Dispatch validation (Phase 36) — barcode handover, own section --}}
                @include('livewire.merchant.tracking.partials.dispatch-validate')

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