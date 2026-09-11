{{-- Tracking grid header cell (advanced grid) — one <th> per visible column, in the stored order.
    Receives: $colKey (string). Rendered from tracking-list via @include per visible column. --}}

@php
    $headerFilterKeys = [
        'city' => 'city',
        'total' => 'amount',
        'tracking_status' => 'status',
        'provider' => 'provider',
        'delivery_rider' => 'rider',
        'shipping_date' => 'date',
        'assigned_to' => 'assigned',
        'confirmed_by' => 'confirmed',
    ];

    $headerCol = collect($this->trackingColumns())->firstWhere('key', $colKey);
    $headerHasFilter = $headerCol ? array_key_exists($colKey, $headerFilterKeys) : false;
    $headerFilterKey = $headerHasFilter ? $headerFilterKeys[$colKey] : null;

    $headerLabel = $headerCol
        ? (in_array($colKey, ['tracking_status', 'tracking_number'], true)
            ? __("order_flow.{$headerCol['label_key']}")
            : __("merchant_panel.{$headerCol['label_key']}"))
        : $colKey;

    $headerFilterActive = false;
    if ($headerFilterKey === 'amount') {
        $headerFilterActive = filled($this->filters['amount_min'] ?? null) || filled($this->filters['amount_max'] ?? null);
    } elseif ($headerFilterKey === 'status') {
        $headerFilterActive = count($this->filters['tracking_statuses'] ?? []) > 0;
    } elseif ($headerFilterKey === 'date') {
        $headerFilterActive = filled($this->filters['date_from'] ?? null) || filled($this->filters['date_to'] ?? null);
    } elseif ($headerFilterKey) {
        $headerFilterActive = filled($this->filters[$headerFilterKey] ?? null);
    }
@endphp

@if ($headerCol)
    @if ($headerHasFilter)
        <th class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase relative group whitespace-nowrap">
            <span class="inline-flex items-center gap-1">
                {{ $headerLabel }}
                <button data-filter-btn
                    @click.stop="$dispatch('edz-filter-open', { key: '{{ $headerFilterKey }}', el: $event.currentTarget })"
                    class="shrink-0 {{ $headerFilterActive ? 'text-accent-500' : 'text-ink-muted/40 group-hover:text-ink-muted' }} transition">
                    <x-edz.icon name="filter" class="w-3 h-3" />
                </button>
                @if ($headerFilterActive)
                    <span class="w-1.5 h-1.5 rounded-full bg-accent-500 shrink-0"></span>
                @endif
            </span>
        </th>
    @else
        <th class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
            {{ $headerLabel }}
        </th>
    @endif
@endif