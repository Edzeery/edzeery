{{-- Tracking grid row cell (advanced grid) — one <td> per visible column, in the stored order.
    Receives: $s (row array), $statusKit (nullable MystatusKit status), $colKey (string).
    Rendered from tracking-list via @include per visible column inside the row loop. --}}

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

    @case('products')
        <td class="px-4 py-3 text-xs">
            @php
                $rowProducts = $s['products'] ?? [];
                $rowProductsLabel = $rowProducts
                    ? implode(PHP_EOL, array_map(fn ($p) => $p['name'].' ×'.$p['qty'], $rowProducts))
                    : '';
            @endphp
            @if (empty($rowProducts))
                —
            @endif
            @if (! empty($rowProducts))
                <x-edz.tooltip :label="$rowProductsLabel">
                    <span class="inline-flex items-center gap-1 min-w-0">
                        <span class="truncate max-w-[9rem] font-medium text-ink">{{ $rowProducts[0]['name'] }}</span>
                        @if (count($rowProducts) > 1)
                            <span
                                class="shrink-0 inline-flex items-center rounded-md bg-surface-tertiary text-ink-muted text-[10px] font-bold px-1.5 py-0.5 leading-tight">+{{ count($rowProducts) - 1 }}</span>
                        @endif
                    </span>
                </x-edz.tooltip>
            @endif
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
        <td class="px-4 py-3 text-xs">
            @php
                $pvName   = trim((string) ($s['provider'] ?? ''));
                $pvLetter = mb_strtoupper(mb_substr($pvName ?: '?', 0, 1));
                $pvLogo   = $s['provider_logo'] ?? null;
            @endphp
            <div class="flex items-center gap-2 min-w-0">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-accent-surface text-accent-fg text-[10px] font-semibold shrink-0 overflow-hidden relative">
                    <span class="absolute inset-0 flex items-center justify-center">{{ $pvLetter }}</span>
                    @if (! empty($pvLogo))
                        <img src="{{ asset('storage/' . $pvLogo) }}" alt="" loading="lazy"
                            onerror="this.remove()"
                            class="relative w-6 h-6 rounded-full object-cover" />
                    @endif
                </span>
                <span class="truncate max-w-[10rem]">{{ $pvName ?: '—' }}</span>
            </div>
        </td>
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