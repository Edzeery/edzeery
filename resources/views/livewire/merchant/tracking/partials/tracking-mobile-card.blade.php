{{-- Tracking mobile card (advanced grid) — one compact card per shipment, md:hidden.
    Receives: $s (row array). Rendered from tracking-list via @include inside the card loop. --}}

@php
    $mobileStatusKit = $s['tracking_status']
        ? \Edzeery\MyStatusKit\Facades\Status::for('tracking', $s['tracking_status'])
        : null;
    $mobileChecked = in_array($s['id'], $this->selectedShipments, true);
    $bulkEligible = $bulkEligible ?? false;
@endphp
<div class="edz-card p-4 {{ $mobileChecked ? 'bg-accent-surface-subtle' : '' }}" wire:key="card-{{ $s['id'] }}">
    <div class="flex items-center justify-between gap-2">
        <div class="flex items-center gap-2 min-w-0">
            @if ($bulkEligible)
                <x-edz.checkbox size="sm" :checked="$mobileChecked" value="{{ $s['id'] }}"
                    wire:click="toggleSelectOrder('{{ $s['id'] }}')" />
            @endif
            <div class="font-mono font-medium text-ink truncate">#{{ $s['number'] }}</div>
        </div>
        @if ($mobileStatusKit)
            <x-edz.tooltip label="{{ __('order_flow.tracking_history') }}">
                <button type="button" wire:click="openStatusHistory('{{ $s['id'] }}')"
                    class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full cursor-pointer hover:opacity-80 {{ $mobileStatusKit->color() }}">
                    {!! $mobileStatusKit->icon(null, 'w-3.5 h-3.5 shrink-0') !!}
                    {{ $mobileStatusKit->label() }}
                </button>
            </x-edz.tooltip>
        @elseif (! empty($s['tracking_number']))
            <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full bg-surface-tertiary text-ink-muted">
                <x-edz.icon name="clock" class="w-3.5 h-3.5 shrink-0" />
                {{ __('order_flow.tracking_status_unknown') }}
            </span>
        @endif
    </div>
    <div class="mt-2 text-sm text-ink">{{ $s['customer'] }}
        <span class="text-xs text-ink-muted" dir="ltr">• {{ $s['phone'] }}</span>
    </div>
    <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-ink-muted">
        <span>{{ $s['city'] }}</span>
        @if (! empty($s['state']))
            <span>•</span>
            <span>{{ $s['state'] }}</span>
        @endif
        @if ($this->trackingTab === 'rider')
            <span>•</span>
            <span>{{ $s['delivery_rider'] ?: '—' }}</span>
        @elseif (in_array('provider', $this->visibleColumns, true))
            <span>•</span>
            <span>{{ $s['provider'] }}</span>
        @endif
        @if (! empty($s['tracking_number']))
                <x-edz.tooltip label="{{ $s['tracking_number'] }}">
                    <button
                        x-on:click="navigator.clipboard.writeText('{{ $s['tracking_number'] }}').then(() => EdzSwal.success('', '{{ __('order_flow.copy_done') }}'))"
                        class="inline-flex items-center gap-1 font-mono text-accent-600">
                        {{ $s['tracking_number'] }}
                        <x-edz.icon name="clipboard" class="w-3 h-3" />
                    </button>
                </x-edz.tooltip>
                @if (($s['delivery_type'] ?? 'home') === 'stopdesk')
                    <x-edz.tooltip label="{{ __('order_flow.delivery_type_stopdesk') }}">
                        <span class="inline-flex items-center rounded-md bg-accent-surface text-accent-fg text-[10px] font-bold px-1.5 py-0.5 leading-tight">SD</span>
                    </x-edz.tooltip>
                @else
                    <x-edz.tooltip label="{{ __('order_flow.delivery_type_home') }}">
                        <span class="inline-flex items-center rounded-md bg-surface-tertiary text-ink-muted text-[10px] font-bold px-1.5 py-0.5 leading-tight">HM</span>
                    </x-edz.tooltip>
                @endif
            @endif
    </div>
    <div class="mt-3 flex items-center justify-between gap-2">
        <span
            class="font-semibold text-ink tabular-nums">{{ $s['total'] }}
            @if ($s['shipped_at'])
                <span class="ms-1 font-normal text-xs text-ink-muted">• {{ \Carbon\Carbon::parse($s['shipped_at'])->format('Y-m-d H:i') }}</span>
            @endif
        </span>
        <div class="flex items-center gap-1.5">
            <button wire:click="openDrawer('{{ $s['id'] }}')"
                class="edz-btn edz-btn--ghost edz-btn--xs">
                {{ __('buttons.view') }}
            </button>
            <x-edz.tooltip label="{{ __('merchant_panel.actions') }}">
                <div class="relative shrink-0" x-data="edzRowMenu($el)" @click.away="close()">
                    <button type="button" x-ref="trigger" @click.prevent="toggle()"
                        class="edz-btn edz-btn--ghost edz-btn--xs"
                        aria-haspopup="menu" :aria-expanded="open">
                        <x-edz.icon name="ellipsis-horizontal" class="w-4 h-4" />
                    </button>
                <x-edz.mobile-bottom-sheet :title="__('merchant_panel.actions')" icon="ellipsis-horizontal" close-expr="close()" sm-width="sm:w-52" sm-pad="sm:p-1.5 sm:pb-1.5">
                    @if (($s['is_trashed'] ?? false) && canStore(\App\Enums\Store\StorePermissionEnum::ORDER_DELETE->value))
                            <button type="button" wire:click="restoreOrder('{{ $s['id'] }}')"
                                @click="open = false"
                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-ink hover:bg-surface-secondary">
                                <x-edz.icon name="arrow-uturn-left" class="w-4 h-4" />
                                {{ __('merchant.restore_order') }}
                            </button>
                            @if (canFinalDeleteOrders())
                                <button type="button"
                                    x-on:click="EdzSwal.confirmAction('{{ __('order_flow.permanent_delete_title') }}', '{{ __('order_flow.permanent_delete_confirm') }}', { confirmText: '{{ __('merchant.delete_permanently') }}', confirmColor: '#ef4444' }).then((ok) => { if (ok) $wire.forceDeleteOrder('{{ $s['id'] }}'); })"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-sm text-danger-600 hover:bg-surface-secondary">
                                    <x-edz.icon name="trash" class="w-4 h-4" />
                                    {{ __('merchant.delete_permanently') }}
                                </button>
                            @endif
                        @else
                            @if (($s['can_edit_order'] ?? false) && canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value))
                                <button type="button" wire:click="openEditModal('{{ $s['id'] }}')"
                                    @click="open = false"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-sm text-ink hover:bg-surface-secondary">
                                    <x-edz.icon name="pencil-square" class="w-4 h-4" />
                                    {{ __('merchant_panel.edit') }}
                                </button>
                            @endif

                            @if ($s['can_cancel_shipment'] ?? false)
                                <button type="button" @click="open = false"
                                    x-on:click="EdzSwal.confirmAction('{{ __('order_flow.cancel_shipment_title') }}', '{{ __('order_flow.cancel_shipment_confirm') }}', { confirmText: '{{ __('order_flow.cancel_shipment') }}', confirmColor: '#d97706' }).then((ok) => { if (ok) $wire.cancelShipment('{{ $s['id'] }}'); })"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-sm text-warning-600 hover:bg-surface-secondary">
                                    <x-edz.icon name="truck-x-mark" class="w-4 h-4" />
                                    {{ __('order_flow.cancel_shipment') }}
                                </button>
                            @endif

                            @if (canReassignOrders() && $this->trackingTab === 'carrier')
                                <button type="button" wire:click="openTrackingReassignModal('{{ $s['id'] }}')"
                                    @click="open = false"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-sm text-ink hover:bg-surface-secondary">
                                    <x-edz.icon name="arrows-right-left" class="w-4 h-4" />
                                    {{ __('merchant_panel.reassign') }}
                                </button>
                            @endif

                            <button type="button" wire:click="openLabel('{{ $s['id'] }}')" @click="open = false"
                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-ink hover:bg-surface-secondary">
                                <x-edz.icon name="printer" class="w-4 h-4" />
                                {{ __('order_flow.print_label') }}
                            </button>

                            @if (canStore(\App\Enums\Store\StorePermissionEnum::ORDER_DELETE->value))
                                <button type="button"
                                    x-on:click="EdzSwal.confirmAction('{{ __('order_flow.move_to_trash_title') }}', '{{ __('order_flow.move_to_trash_confirm') }}', { confirmText: '{{ __('merchant_panel.delete') }}', confirmColor: '#ef4444' }).then((ok) => { if (ok) $wire.deleteOrder('{{ $s['id'] }}'); })"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-sm text-danger-600 hover:bg-surface-secondary">
                                    <x-edz.icon name="trash" class="w-4 h-4" />
                                    {{ __('merchant_panel.delete') }}
                                </button>
                            @endif
@endif
                    </x-edz.mobile-bottom-sheet>
                </div>
            </x-edz.tooltip>
        </div>
    </div>
</div>