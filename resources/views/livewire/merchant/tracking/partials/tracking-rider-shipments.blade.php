{{-- Shipments of the expanded rider (Phase D). Uses $this->riderShipments / riderShipmentTotal / selectedRiderId. --}}
<div class="bg-surface-secondary/40 rounded-xl border border-surface-border overflow-hidden">
    <div class="px-4 pt-3 pb-2 flex items-center justify-between gap-2 border-b border-surface-border">
        <p class="text-sm font-semibold text-ink truncate">
            {{ collect($this->riderRiders)->firstWhere('id', $this->selectedRiderId)['name'] ?? '' }}
        </p>
        <span class="text-xs text-ink-muted shrink-0">{{ $this->riderShipmentTotal }}
            {{ __('order_flow.rider_shipments_count') }}</span>
    </div>

    <div class="divide-y divide-surface-border">
        @forelse ($this->riderShipments as $s)
            <div class="px-4 py-3 flex flex-wrap items-center gap-x-3 gap-y-2">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-ink">#{{ $s['number'] }}</span>
                        @if ($s['tracking_status'])
                            <span
                                class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-full {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', $s['tracking_status'])->color() }}">
                                {!! \Edzeery\MyStatusKit\Facades\Status::for('tracking', $s['tracking_status'])->icon(null, 'w-3 h-3 shrink-0') !!}
                                {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', $s['tracking_status'])->label() }}
                            </span>
                        @endif
                    </div>
                    <div class="mt-0.5 text-sm text-ink">{{ $s['customer'] }}
                        <span class="text-xs text-ink-muted" dir="ltr">• {{ $s['phone'] }}</span>
                    </div>
                    <div class="text-xs text-ink-muted truncate">{{ $s['city'] }}
                        @if (! empty($s['tracking_number']))
                            <span class="ms-1 font-mono" dir="ltr">• {{ $s['tracking_number'] }}</span>
                        @endif
                    </div>
                </div>
                <span class="font-semibold text-ink tabular-nums">{{ $s['total'] }}</span>
                <button wire:click="openDrawer('{{ $s['id'] }}')"
                    class="edz-btn edz-btn--ghost edz-btn--xs">
                    {{ __('buttons.view') }}
                </button>
            </div>
        @empty
            <div class="px-4 py-8 text-center text-sm text-ink-muted">
                <x-edz.icon name="truck" class="w-7 h-7 mx-auto mb-2" />
                {{ __('order_flow.rider_no_shipments') }}
            </div>
        @endforelse
    </div>
</div>