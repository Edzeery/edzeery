@props(['groups' => []])

@if (! empty($groups))
    <div class="flex flex-wrap items-center gap-x-1.5 gap-y-0.5 min-w-0">
        @foreach ($groups as $group)
            @php
                $productName = (string) ($group['product_name'] ?? '');
                $chips = $group['chips'] ?? [];
            @endphp
            <span class="inline-flex items-center gap-1 min-w-0">
                @if (! $loop->first)
                    <span class="text-ink-muted/50 shrink-0">&middot;</span>
                @endif
                @if ($productName !== '' && $productName !== '—')
                    <span class="font-medium text-ink truncate max-w-[130px]">{{ $productName }}</span>
                @endif
                @foreach (array_values($chips) as $chip)
                    @php
                        $chipLabel = trim((string) ($chip['label'] ?? ''));
                        $chipQty   = (int) ($chip['qty'] ?? 1);
                        $chipSku   = trim((string) ($chip['sku'] ?? ''));
                    @endphp
                    @if ($chipLabel !== '')
                        <span
                            title="{{ $chipSku !== '' ? $chipSku : '' }}"
                            class="inline-flex items-center gap-0.5 rounded border border-surface-border bg-surface-tertiary px-1.5 py-px text-[10px] font-medium leading-4 text-ink-muted"
                        >{{ $chipLabel }}<span class="text-ink">&times;{{ $chipQty }}</span></span>
                    @elseif ($chipQty > 1 || count($chips) > 1)
                        <span class="text-ink-muted text-[10px] font-semibold">&times;{{ $chipQty }}</span>
                    @endif
                @endforeach
            </span>
        @endforeach
    </div>
@else
    <span class="text-ink-muted">{{ __('merchant_panel.please_select_product') }}</span>
@endif