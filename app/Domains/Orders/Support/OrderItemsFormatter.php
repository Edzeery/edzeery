<?php

namespace App\Domains\Orders\Support;

use Illuminate\Support\Collection;

/**
 * Presenters over OrderItemsGrouper for merchant panel / label / carrier text.
 * 🔗 CarrierIntegrationContract: use toCompactString() — never hand-format lines.
 */
final class OrderItemsFormatter
{
    public const DEFAULT_MAX = 250;

    public function __construct(private OrderItemsGrouper $grouper)
    {
    }

    /**
     * Compact carrier text — quantity per variant, no SKU, boundary-aware truncation.
     * Empty → fallback "{count} items".
     */
    public function toCompactString(Collection $items, int $maxLength = self::DEFAULT_MAX): string
    {
        if ($items->isEmpty()) {
            return '';
        }

        $products = [];

        foreach ($this->grouper->group($items) as $group) {
            $name = trim((string) $group['product_name']);

            if ($name === '' || $name === '—') {
                continue;
            }

            $descriptors = [];
            $hasLabels = false;

            foreach ($group['variants'] as $variant) {
                $label = trim((string) $variant['option_label']);

                if ($label === '') {
                    $descriptors[] = '×'.$variant['qty'];
                    continue;
                }

                $hasLabels = true;
                $descriptors[] = $label.' ×'.$variant['qty'];
            }

            $products[] = [
                'name' => $name,
                'descriptors' => $descriptors,
                'total' => array_sum(array_column($group['variants'], 'qty')),
                'hasLabels' => $hasLabels,
            ];
        }

        $compact = $this->joinSegments($products, $maxLength);

        return $compact !== '' ? $compact : $items->count().' items';
    }

    /**
     * Printed label — one entry per product, bullets "  • label — SKU ×qty".
     * No options → "Name ×qty" single line. No truncation.
     */
    public function toDetailedLines(Collection $items): array
    {
        $lines = [];

        foreach ($this->grouper->group($items) as $group) {
            $name = trim((string) $group['product_name']);

            if ($name === '' || $name === '—') {
                continue;
            }

            $hasLabels = collect($group['variants'])->contains(
                fn (array $v) => trim((string) $v['option_label']) !== ''
            );

            if (! $hasLabels) {
                $total = array_sum(array_column($group['variants'], 'qty'));
                $lines[] = $name.($total > 1 ? ' ×'.$total : '');
                continue;
            }

            $block = $name;

            foreach ($group['variants'] as $variant) {
                $label = trim((string) $variant['option_label']);
                $sku = trim((string) ($variant['sku'] ?? ''));
                $parts = $label !== '' ? [$label] : [];
                if ($sku !== '') {
                    $parts[] = $sku;
                }
                $block .= "\n  • ".(implode(' — ', $parts) ?: '?').' ×'.$variant['qty'];
            }

            $lines[] = $block;
        }

        return $lines;
    }

    /**
     * Grouped chips for orders table / drawer / modal.
     * Chip: {label, sku, qty, price} — price rides so the drawer can compute
     * line subtotals from the same array the table cell renders.
     */
    public function toTableGroups(Collection $items): Collection
    {
        return $this->grouper->group($items)
            ->map(fn (array $g) => [
                'product_id' => $g['product_id'],
                'product_name' => $g['product_name'],
                'chips' => array_map(fn (array $v) => [
                    'variant_id' => $v['variant_id'],
                    'label' => trim((string) $v['option_label']),
                    'sku' => $v['sku'],
                    'qty' => $v['qty'],
                    'price' => $v['price'],
                ], $g['variants']),
            ])
            ->values();
    }

    /**
     * Flat per-line shape for the edit form and quantity/price columns.
     */
    public function toFlatItems(Collection $items): Collection
    {
        return $this->grouper->group($items)
            ->flatMap(fn (array $g) => collect($g['variants'])->map(fn (array $v) => [
                'variant_id' => $v['variant_id'],
                'product_id' => $g['product_id'],
                'name' => $g['product_name'],
                'sku' => $v['sku'],
                'price' => $v['price'],
                'qty' => $v['qty'],
            ]))
            ->values();
    }

    private function joinSegments(array $products, int $maxLength): string
    {
        $out = '';

        foreach ($products as $product) {
            $sep = $out === '' ? '' : '; ';
            $full = $this->segmentText($product);

            if (mb_strlen($out.$sep.$full) <= $maxLength) {
                $out .= $sep.$full;
                continue;
            }

            $budget = $maxLength - mb_strlen($out) - mb_strlen($sep) - 1;
            $partial = $budget > 0 ? $this->partialSegment($product, $budget) : '';

            if ($partial !== '' && $partial !== $full) {
                $out .= $sep.$partial.'…';
            } elseif ($partial === $full) {
                $out .= $sep.$partial;
            } elseif ($out !== '') {
                $out .= '…';
            }

            break;
        }

        return $out;
    }

    private function segmentText(array $product): string
    {
        if ($product['hasLabels']) {
            return $product['name'].' ('.implode(', ', $product['descriptors']).')';
        }

        return $product['name'].($product['total'] > 1 ? ' ×'.$product['total'] : '');
    }

    private function partialSegment(array $product, int $budget): string
    {
        $name = $product['name'];

        if (! $product['hasLabels']) {
            $candidate = $this->segmentText($product);

            if (mb_strlen($candidate) <= $budget) {
                return $candidate;
            }

            return mb_strlen($name) <= $budget ? $name : '';
        }

        if (mb_strlen($name.' (') > $budget) {
            return mb_strlen($name) <= $budget ? $name : '';
        }

        $built = $name.' (';
        $added = 0;
        $count = count($product['descriptors']);

        foreach ($product['descriptors'] as $descriptor) {
            $trial = $built.($added === 0 ? '' : ', ').$descriptor;

            if (mb_strlen($trial) > $budget) {
                break;
            }

            $built = $trial;
            $added++;
        }

        if ($added === $count && mb_strlen($built.')') <= $budget) {
            return $built.')';
        }

        return $added === 0 ? $name : $built;
    }
}