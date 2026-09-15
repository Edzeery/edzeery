{{-- Tracking list (advanced grid) — desktop table + mobile cards + prev/next.
    Columns render from $this->visibleColumns (per-tab persisted prefs); header filter
    buttons per filterable column via tracking-table-header. Row <td> markup lives in
    tracking-row-cell, mobile cards in tracking-mobile-card. Shared by both tabs. --}}

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
                        @include('livewire.merchant.tracking.partials.tracking-row-cell', [
                            's' => $s,
                            'statusKit' => $statusKit,
                            'colKey' => $colKey,
                        ])
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
        @include('livewire.merchant.tracking.partials.tracking-mobile-card', ['s' => $s])
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