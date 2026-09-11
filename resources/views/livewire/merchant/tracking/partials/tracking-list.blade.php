{{-- Tracking list (advanced grid) — desktop table + mobile cards + prev/next.
    Columns render from $this->visibleColumns (per-tab persisted prefs); header filter
    buttons per filterable column via tracking-table-header. Shared by both tabs. --}}

{{-- Desktop table — horizontal + vertical scroll inside the card, sticky header,
    row-action menus anchored over the row (fixed, so they escape the scroller). --}}
<div class="hidden md:block edz-card">
    <div class="relative">
        <div class="overflow-x-auto max-h-[calc(100vh-475px)] overflow-y-auto edz-scroll">
            <table class="w-max min-w-full text-sm">
                <thead class="sticky top-0 z-10 bg-surface [&_th]:bg-surface">
                    <tr class="text-start text-xs uppercase tracking-wide text-ink-muted border-b border-surface-border">
                        @foreach ($this->visibleColumns as $colKey)
                            @include('livewire.merchant.tracking.partials.tracking-table-header', ['colKey' => $colKey])
                        @endforeach
                    </tr>
                </thead>
        <tbody class="divide-y divide-surface-border">
            @forelse ($this->shipments as $s)
                @php
                    $rowTint = null;
                    if ($this->tableStyle === 'status') {
                        $rowTint = match ($s['tracking_status'] ?? null) {
                            'delivered' => 'edz-table-row--success',
                            'returned', 'cancelled', 'failed' => 'edz-table-row--danger',
                            default => null,
                        };
                    }
                    $statusKit = $s['tracking_status']
                        ? \Edzeery\MyStatusKit\Facades\Status::for('tracking', $s['tracking_status'])
                        : null;
                @endphp
                <tr class="group hover:bg-surface-secondary/50 transition {{ $rowTint }}" wire:key="row-{{ $s['id'] }}">
                    @foreach ($this->visibleColumns as $colKey)
                        @switch($colKey)
                            @case('number')
                                <td class="px-4 py-3 font-mono font-semibold text-ink">#{{ $s['number'] }}</td>
                                @break

                            @case('customer')
                                <td class="px-4 py-3">
                                    <div class="text-ink">{{ $s['customer'] }}</div>
                                    <div class="text-xs text-ink-muted" dir="ltr">{{ $s['phone'] }}</div>
                                </td>
                                @break

                            @case('city')
                                <td class="px-4 py-3 text-ink-muted text-xs">{{ $s['city'] }}</td>
                                @break

                            @case('state')
                                <td class="px-4 py-3 text-ink-muted text-xs">{{ $s['state'] }}</td>
                                @break

                            @case('assigned_to')
                                <td class="px-4 py-3 text-ink-muted text-xs">
                                    @if (! empty($s['assigned_to']))
                                        <span class="inline-flex items-center gap-1">
                                            <x-edz.icon name="user" class="w-3 h-3 text-ink-muted" />
                                            {{ $s['assigned_to'] }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                @break

                            @case('confirmed_by')
                                <td class="px-4 py-3 text-ink-muted text-xs">{{ $s['confirmed_by'] ?: '—' }}</td>
                                @break

                            @case('notes')
                                <td class="px-4 py-3">
                                    @if (! empty($s['tracking_number']) && ($s['carrier_supports_api_notes'] ?? false))
                                        <x-edz.tooltip label="{{ __('order_flow.carrier_notes') }}">
                                            <button type="button" wire:click="openShipmentNotes('{{ $s['id'] }}')"
                                                class="inline-flex items-center gap-1 text-xs text-ink-muted hover:text-accent-600 transition {{ ! empty($s['latest_note']) ? 'font-semibold text-ink' : '' }}">
                                                <x-edz.icon name="chat-bubble-left-right" class="w-3.5 h-3.5" />
                                                {{ ! empty($s['latest_note']) ? Str::limit($s['latest_note'], 24) : __('order_flow.carrier_notes') }}
                                            </button>
                                        </x-edz.tooltip>
                                    @else
                                        —
                                    @endif
                                </td>
                                @break

                            @case('actions')
                                <td class="px-4 py-3">
                                    <x-edz.tooltip label="{{ __('merchant_panel.actions') }}">
                                        <div class="relative shrink-0" x-data="edzRowMenu($el)" @click.away="close()">
                                            <button type="button" x-ref="trigger" @click.prevent="toggle()"
                                                class="edz-btn edz-btn--ghost edz-btn--xs"
                                                aria-haspopup="menu" :aria-expanded="open">
                                                <x-edz.icon name="ellipsis-vertical" class="w-4 h-4" />
                                            </button>
                                            <x-edz.mobile-bottom-sheet :title="__('merchant_panel.actions')" icon="ellipsis-horizontal" close-expr="close()" sm-width="sm:w-56" sm-pad="sm:p-1.5 sm:pb-1.5">
                                            <button type="button" wire:click="openDrawer('{{ $s['id'] }}')"
                                                @click="open = false"
                                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-ink hover:bg-surface-secondary">
                                                <x-edz.icon name="info-circle" class="w-4 h-4" />
                                                {{ __('merchant.order_details') }}
                                            </button>

                                            @if (($s['is_trashed'] ?? false) && canStore(\App\Enums\Store\StorePermissionEnum::ORDER_DELETE->value))
                                                <button type="button" wire:click="restoreOrder('{{ $s['id'] }}')"
                                                    @click="open = false"
                                                    class="w-full flex items-center gap-2 px-3 py-2 text-sm text-ink hover:bg-surface-secondary">
                                                    <x-edz.icon name="arrow-uturn-left" class="w-4 h-4" />
                                                    {{ __('merchant.restore_order') }}
                                                </button>
                                                <button type="button"
                                                    x-on:click="EdzSwal.confirmAction('{{ __('order_flow.permanent_delete_title') }}', '{{ __('order_flow.permanent_delete_confirm') }}', { confirmText: '{{ __('merchant.delete_permanently') }}', confirmColor: '#ef4444' }).then((ok) => { if (ok) $wire.forceDeleteOrder('{{ $s['id'] }}'); })"
                                                    class="w-full flex items-center gap-2 px-3 py-2 text-sm text-danger-600 hover:bg-surface-secondary">
                                                    <x-edz.icon name="trash" class="w-4 h-4" />
                                                    {{ __('merchant.delete_permanently') }}
                                                </button>
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

                                                <button type="button" wire:click="openLabel('{{ $s['id'] }}')"
                                                    @click="open = false"
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
                                </td>
                                @break

                            @case('total')
                                <td class="px-4 py-3 font-medium text-ink tabular-nums whitespace-nowrap">{{ $s['total'] }}</td>
                                @break

                            @case('tracking_status')
                                <td class="px-4 py-3">
                                    @if ($statusKit)
                                        <x-edz.tooltip label="{{ __('order_flow.tracking_history') }}">
                                            <button type="button" wire:click="openStatusHistory('{{ $s['id'] }}')"
                                                class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full cursor-pointer hover:opacity-80 {{ $statusKit->color() }}">
                                                {!! $statusKit->icon(null, 'w-3.5 h-3.5 shrink-0') !!}
                                                {{ $statusKit->label() }}
                                            </button>
                                        </x-edz.tooltip>
                                    @else
                                        —
                                    @endif
                                </td>
                                @break

                            @case('tracking_number')
                                <td class="px-4 py-3 text-xs" dir="ltr">
                                    <div class="flex items-center gap-2">
                                        @if (! empty($s['tracking_number']))
                                            <x-edz.tooltip label="{{ $s['tracking_number'] }}">
                                                <button
                                                    x-on:click="navigator.clipboard.writeText('{{ $s['tracking_number'] }}').then(() => EdzSwal.success('', '{{ __('order_flow.copy_done') }}'))"
                                                    class="inline-flex min-w-0 items-center gap-1 text-ink-muted font-mono hover:text-accent-600">
                                                    <span class="truncate max-w-[7rem] transition-all group-hover:max-w-none">{{ $s['tracking_number'] }}</span>
                                                    <x-edz.icon name="clipboard" class="w-3 h-3 shrink-0" />
                                                </button>
                                            </x-edz.tooltip>
                                        @else
                                            <span class="font-mono text-ink-muted">—</span>
                                        @endif
                                        @if (($s['delivery_type'] ?? 'home') === 'stopdesk')
                                            <x-edz.tooltip label="{{ __('order_flow.delivery_type_stopdesk') }}">
                                                <span class="ms-auto shrink-0 inline-flex items-center rounded-md bg-accent-surface text-accent-fg text-[10px] font-bold px-1.5 py-0.5 leading-tight">SD</span>
                                            </x-edz.tooltip>
                                        @else
                                            <x-edz.tooltip label="{{ __('order_flow.delivery_type_home') }}">
                                                <span class="ms-auto shrink-0 inline-flex items-center rounded-md bg-surface-tertiary text-ink-muted text-[10px] font-bold px-1.5 py-0.5 leading-tight">HM</span>
                                            </x-edz.tooltip>
                                        @endif
                                    </div>
                                </td>
                                @break

                            @case('provider')
                                <td class="px-4 py-3 text-ink-muted text-xs">{{ $s['provider'] }}</td>
                                @break

                            @case('delivery_rider')
                                <td class="px-4 py-3 text-ink-muted text-xs">{{ $s['delivery_rider'] ?: '—' }}</td>
                                @break

                            @case('shipping_date')
                                <td class="px-4 py-3 text-ink-muted text-xs whitespace-nowrap">
                                    {{ $s['shipped_at'] ? \Carbon\Carbon::parse($s['shipped_at'])->format('Y-m-d H:i') : '—' }}
                                </td>
                                @break

                            @default
                                <td class="px-4 py-3 text-ink-muted text-xs">—</td>
                        @endswitch
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($this->visibleColumns) }}" class="px-4 py-12 text-center text-ink-muted">
                        <x-edz.icon name="truck" class="w-8 h-8 mx-auto mb-2" />
                        {{ __('order_flow.no_tracking_found') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
        </div>
    </div>
</div>

{{-- Mobile cards --}}
<div class="md:hidden space-y-3">
    @forelse ($this->shipments as $s)
        @php
            $mobileStatusKit = $s['tracking_status']
                ? \Edzeery\MyStatusKit\Facades\Status::for('tracking', $s['tracking_status'])
                : null;
        @endphp
        <div class="edz-card p-4" wire:key="card-{{ $s['id'] }}">
            <div class="flex items-center justify-between gap-2">
                <div class="font-mono font-medium text-ink">#{{ $s['number'] }}</div>
                @if ($mobileStatusKit)
                    <x-edz.tooltip label="{{ __('order_flow.tracking_history') }}">
                        <button type="button" wire:click="openStatusHistory('{{ $s['id'] }}')"
                            class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full cursor-pointer hover:opacity-80 {{ $mobileStatusKit->color() }}">
                            {!! $mobileStatusKit->icon(null, 'w-3.5 h-3.5 shrink-0') !!}
                            {{ $mobileStatusKit->label() }}
                        </button>
                    </x-edz.tooltip>
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
                @endif
                <span>•</span>
                <span>{{ $s['provider'] }}</span>
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
                                    <button type="button"
                                        x-on:click="EdzSwal.confirmAction('{{ __('order_flow.permanent_delete_title') }}', '{{ __('order_flow.permanent_delete_confirm') }}', { confirmText: '{{ __('merchant.delete_permanently') }}', confirmColor: '#ef4444' }).then((ok) => { if (ok) $wire.forceDeleteOrder('{{ $s['id'] }}'); })"
                                        class="w-full flex items-center gap-2 px-3 py-2 text-sm text-danger-600 hover:bg-surface-secondary">
                                        <x-edz.icon name="trash" class="w-4 h-4" />
                                        {{ __('merchant.delete_permanently') }}
                                    </button>
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
    @empty
        <div class="edz-card edz-card--padded text-center text-ink-muted py-12">
            <x-edz.icon name="truck" class="w-8 h-8 mx-auto mb-2" />
            {{ __('order_flow.no_tracking_found') }}
        </div>
    @endforelse
</div>

{{-- Prev / Next pagination --}}
@if ($this->page > 1 || count($this->shipments) === $this->perPage)
    <div class="mt-4 flex items-center justify-center gap-2">
        @if ($this->page > 1)
            <button wire:click="previousPage" wire:loading.attr="disabled"
                class="edz-btn edz-btn--ghost edz-btn--sm">
                <x-edz.icon name="chevron-left" class="w-4 h-4" />
                {{ __('pagination.previous') }}
            </button>
        @endif
        <span class="text-xs text-ink-muted tabular-nums">{{ $this->page }}</span>
        @if (count($this->shipments) === $this->perPage)
            <button wire:click="nextPage" wire:loading.attr="disabled"
                class="edz-btn edz-btn--ghost edz-btn--sm">
                {{ __('pagination.next') }}
                <x-edz.icon name="chevron-right" class="w-4 h-4" />
            </button>
        @endif
    </div>
@endif