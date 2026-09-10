{{-- Tracking list (Phase A) — desktop table + mobile cards + load-more, extracted from the tracking index. --}}

{{-- Desktop table --}}
<div class="hidden md:block edz-card overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-start text-xs uppercase tracking-wide text-ink-muted border-b border-surface-border">
                <th class="text-start px-4 py-3 font-medium">{{ __('merchant_panel.number') }}</th>
                <th class="text-start px-4 py-3 font-medium">{{ __('merchant_panel.customer') }}</th>
                <th class="text-start px-4 py-3 font-medium">{{ __('merchant_panel.city') }}</th>
                <th class="text-start px-4 py-3 font-medium">{{ __('order_flow.tracking_provider') }}</th>
                <th class="text-start px-4 py-3 font-medium">{{ __('order_flow.tracking_number_copy') }}</th>
                <th class="text-start px-4 py-3 font-medium">{{ __('order_flow.tracking_status') }}</th>
                <th class="text-start px-4 py-3 font-medium">{{ __('merchant_panel.total') }}</th>
                <th class="text-end px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-surface-border">
            @forelse ($this->shipments as $s)
                <tr class="hover:bg-surface-secondary/50 transition">
                    <td class="px-4 py-3 font-medium text-ink">#{{ $s['number'] }}</td>
                    <td class="px-4 py-3">
                        <div class="text-ink">{{ $s['customer'] }}</div>
                        <div class="text-xs text-ink-muted" dir="ltr">{{ $s['phone'] }}</div>
                    </td>
                    <td class="px-4 py-3 text-ink-muted text-xs">{{ $s['city'] }}</td>
                    <td class="px-4 py-3 text-ink-muted text-xs">{{ $s['provider'] }}</td>
                    <td class="px-4 py-3 text-ink-muted text-xs font-mono" dir="ltr">
                        @if (!empty($s['tracking_number']))
                            <button
                                x-on:click="navigator.clipboard.writeText('{{ $s['tracking_number'] }}').then(() => EdzSwal.toast ? EdzSwal.toast('{{ __('order_flow.copy_done') }}') : null)"
                                class="inline-flex items-center gap-1 hover:text-accent-600">
                                {{ $s['tracking_number'] }}
                                <x-edz.icon name="clipboard" class="w-3 h-3" />
                            </button>
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if ($s['tracking_status'])
                            <button type="button" wire:click="openStatusHistory('{{ $s['id'] }}')"
                                title="{{ __('order_flow.tracking_history') }}"
                                class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full cursor-pointer hover:opacity-80 {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', $s['tracking_status'])->color() }}">
                                {!! \Edzeery\MyStatusKit\Facades\Status::for('tracking', $s['tracking_status'])->icon(null, 'w-3.5 h-3.5 shrink-0') !!}
                                {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', $s['tracking_status'])->label() }}
                            </button>
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-4 py-3 font-medium text-ink tabular-nums">{{ $s['total'] }}</td>
                    <td class="px-4 py-3 text-end">
                        <button wire:click="openDrawer('{{ $s['id'] }}')"
                            class="edz-btn edz-btn--ghost edz-btn--xs" title="{{ __('merchant.order_details') }}">
                            <x-edz.icon name="info-circle" class="w-4 h-4" />
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-ink-muted">
                        <x-edz.icon name="truck" class="w-8 h-8 mx-auto mb-2" />
                        {{ __('order_flow.no_tracking_found') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Mobile cards --}}
<div class="md:hidden space-y-3">
    @forelse ($this->shipments as $s)
        <div class="edz-card p-4">
            <div class="flex items-center justify-between gap-2">
                <div class="font-medium text-ink">#{{ $s['number'] }}</div>
                @if ($s['tracking_status'])
                    <button type="button" wire:click="openStatusHistory('{{ $s['id'] }}')"
                        title="{{ __('order_flow.tracking_history') }}"
                        class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full cursor-pointer hover:opacity-80 {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', $s['tracking_status'])->color() }}">
                        {!! \Edzeery\MyStatusKit\Facades\Status::for('tracking', $s['tracking_status'])->icon(null, 'w-3.5 h-3.5 shrink-0') !!}
                        {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', $s['tracking_status'])->label() }}
                    </button>
                @endif
            </div>
            <div class="mt-2 text-sm text-ink">{{ $s['customer'] }}
                <span class="text-xs text-ink-muted" dir="ltr">• {{ $s['phone'] }}</span>
            </div>
            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-ink-muted">
                <span>{{ $s['city'] }}</span>
                <span>•</span>
                <span>{{ $s['provider'] }}</span>
                @if (!empty($s['tracking_number']))
                    <button
                        x-on:click="navigator.clipboard.writeText('{{ $s['tracking_number'] }}').then(() => EdzSwal.toast ? EdzSwal.toast('{{ __('order_flow.copy_done') }}') : null)"
                        class="inline-flex items-center gap-1 font-mono text-accent-600">
                        {{ $s['tracking_number'] }}
                        <x-edz.icon name="clipboard" class="w-3 h-3" />
                    </button>
                @endif
            </div>
            <div class="mt-3 flex items-center justify-between gap-2">
                <span class="font-semibold text-ink">{{ $s['total'] }}</span>
                <button wire:click="openDrawer('{{ $s['id'] }}')"
                    class="edz-btn edz-btn--ghost edz-btn--xs">
                    {{ __('buttons.view') }}
                </button>
            </div>
        </div>
    @empty
        <div class="edz-card edz-card--padded text-center text-ink-muted py-12">
            <x-edz.icon name="truck" class="w-8 h-8 mx-auto mb-2" />
            {{ __('order_flow.no_tracking_found') }}
        </div>
    @endforelse
</div>

@if ($this->page > 1 && count($this->shipments) === $this->perPage)
    <div class="mt-4 flex justify-center">
        <button wire:click="$set('page', {{ $this->page + 1 }}); $wire.loadShipments()"
            class="edz-btn edz-btn--ghost edz-btn--sm">
            {{ __('pagination.next') }}
        </button>
    </div>
@endif