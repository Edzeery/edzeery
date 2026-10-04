<?php

namespace App\Domains\Analytics\Support;

use App\Domains\Status\StatusResolver;
use App\Domains\Status\Support\ResolvedStatus;
use App\Models\Status as StatusModel;
use Illuminate\Support\Str;

final class OrderStatusChartMapper
{
    /**
     * Chart doughnut rows: a label and a slice colour per status key.
     *
     * Labels come from StatusResolver, the same source <x-status> reads, so the
     * legend is translated the way the rest of the UI is. The previous
     * "statuses.order.*" lookup used a namespace that does not exist, which is
     * why an Arabic UI showed a literal English "Pending".
     *
     * @param  \Illuminate\Support\Collection<int, object>  $rows
     * @return \Illuminate\Support\Collection<int, object>
     */
    public function map($rows, ?string $storeId = null)
    {
        // One query for the whole domain. resolve() would query per distinct
        // key, which turns a doughnut into a per-slice N+1.
        $domain = StatusResolver::domain('order', $storeId);

        return $rows->map(function ($row) use ($domain, $storeId) {
            $key = $row->key;
            $resolved = $this->resolveStatus($key, $domain, $storeId);

            $label = $resolved?->label;

            if (empty($label) && $key) {
                // Flat status namespace, e.g. status.pending.
                $label = trans("status.$key");

                if (empty($label) || $label === "status.$key") {
                    $label = null;
                }
            }

            // Stores configure cancelled under either spelling.
            if (empty($label) && $key) {
                $alias = $this->aliasKey($key);

                if ($alias !== null) {
                    $label = trans("status.$alias");

                    if (empty($label) || $label === "status.$alias") {
                        $label = null;
                    }
                }
            }

            if (empty($label) && $key) {
                $label = (string) Str::of($key)->replace('_', ' ')->title();
            }

            return (object) [
                'key' => $key,
                'count' => (int) $row->count,
                'label' => $label,
                // Grey for anything the resolver does not know.
                'hex' => $resolved?->hex ?: '#9ca3af',
            ];
        });
    }

    /**
     * Reproduce exactly what resolve() would return for this key.
     *
     * StatusResolver::domain() is cheaper than resolve() (one query for the
     * whole domain instead of one per key) but it is not self consistent: for a
     * key that has a database row it hands back the raw Status model, while a
     * kit-only key comes back as a ResolvedStatus. A raw row is wrapped with
     * the same factory resolve() uses, so labels and colours stay identical to
     * <x-status> without paying a query per slice.
     */
    private function resolveStatus(?string $key, array $domain, ?string $storeId): ?ResolvedStatus
    {
        if (empty($key)) {
            return null;
        }

        $resolved = $this->normalize($domain[$key] ?? null);

        if ($resolved !== null) {
            return $resolved;
        }

        // Stores configure cancelled under either spelling.
        $alias = $this->aliasKey($key);

        if ($alias !== null) {
            $aliased = $this->normalize($domain[$alias] ?? null);

            if ($aliased !== null) {
                return $aliased;
            }
        }

        return StatusResolver::resolve('order', $alias ?? $key, $storeId);
    }

    private function normalize(mixed $entry): ?ResolvedStatus
    {
        return match (true) {
            $entry instanceof ResolvedStatus => $entry,
            $entry instanceof StatusModel => ResolvedStatus::fromModel($entry),
            default => null,
        };
    }

    /** cancelled/canceled are the same state under two spellings. */
    private function aliasKey(string $key): ?string
    {
        if (str_contains($key, 'cancelled')) {
            return str_replace('cancelled', 'canceled', $key);
        }

        if (str_contains($key, 'canceled')) {
            return str_replace('canceled', 'cancelled', $key);
        }

        return null;
    }
}
