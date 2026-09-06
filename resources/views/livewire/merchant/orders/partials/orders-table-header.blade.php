{{-- Orders table header (Apple-style): one <th> per visible column, in the stored order.
    Rendered from the component via a single @foreach over $this->visibleColumns. --}}

@php
    /** Column keys that expose a filter dropdown button in the header. */
    $headerFilterKeys = [
        'wilaya' => 'wilaya',
        'shipping_provider' => 'shipping_provider',
        'products' => 'product',
        'total' => 'amount',
        'status' => 'status',
        'assigned_agent' => 'assigned_to',
        'created_at' => 'date',
        'weight' => 'weight',
        'shipment_type' => 'shipment_type',
        'notes' => 'notes',
        'source' => 'source',
        'city' => 'city',
        'address' => 'address',
        'delivery_type' => 'delivery_type',
        'stopdesk_point' => 'stopdesk_point',
        'send_from_carrier_warehouse' => 'send_from_carrier_warehouse',
    ];

    $headerCol = $this->orderColumn($colKey);
    $headerHasFilter = array_key_exists($colKey, $headerFilterKeys);
    $headerFilterKey = $headerHasFilter ? $headerFilterKeys[$colKey] : null;

    $headerFilterActive = false;
    if ($headerFilterKey === 'amount') {
        $headerFilterActive = filled($this->filters['amount_min']) || filled($this->filters['amount_max']);
    } elseif ($headerFilterKey) {
        $headerFilterActive = filled($this->filters[$headerFilterKey] ?? null);
    }
@endphp

@if ($headerCol)
    @if ($headerHasFilter && $headerFilterKey)
        <th class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase relative group">
            <div class="flex items-center gap-1">
                {{ __("merchant_panel.{$headerCol['label_key']}") }}
                <button data-filter-btn
                    @click.stop="$dispatch('edz-filter-open', { key: '{{ $headerFilterKey }}', el: $event.currentTarget })"
                    class="shrink-0 {{ $headerFilterActive ? 'text-accent-500' : 'text-ink-muted/40 group-hover:text-ink-muted' }} transition">
                    <x-edz.icon name="filter" class="w-3 h-3" />
                </button>
                @if ($headerFilterActive)
                    <span class="w-1.5 h-1.5 rounded-full bg-accent-500 shrink-0"></span>
                @endif
            </div>
        </th>
    @else
        <th class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
            {{ __("merchant_panel.{$headerCol['label_key']}") }}
        </th>
    @endif
@endif