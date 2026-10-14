<?php

namespace App\Domains\Analytics\Support;

/**
 * PHASE 37-N7 — folds the rows of {@see DeliveryStatsQuery} into the tab's
 * numbers. Pure PHP, no queries: the two rate cards and the KPI strip all go
 * through build(), so they can never disagree on what the rate means. Soft-
 * deleted orders are never part of a ratio (D7); they travel in their own
 * column and are reported separately.
 */
final class DeliveryStatsReport
{
    /**
     * @param  string  $kind  DeliveryRate::GROSS or ::NET.
     * @param  list<string>  $excludedKeys  Status keys kept out of the rate.
     */
    public function __construct(
        private readonly string $kind,
        private readonly array $excludedKeys = [],
    ) {}

    /**
     * @param  list<array{status_key: string, total: int, deleted: int}>  $rows
     * @return array{kind: string, delivered: int, returned: int, denominator: int, delivery_rate: int, return_rate: int, excluded: int, deleted: int, groups: array<string, array{total: int, deleted: int}>, outside: array<string, bool>, total_orders: int, trash_orders: int}
     */
    public function build(array $rows): array
    {
        $groups = $this->emptyGroups();
        $counts = [];
        $deleted = 0;

        foreach ($rows as $row) {
            $key = (string) $row['status_key'];
            $group = DeliveryStatsGroups::group($key);
            $groups[$group]['total'] += (int) $row['total'];
            $groups[$group]['deleted'] += (int) $row['deleted'];
            $counts[$key] = ($counts[$key] ?? 0) + (int) $row['total'];
            $deleted += (int) $row['deleted'];
        }

        $rate = DeliveryRate::compute($this->kind, $counts, $this->excludedKeys);

        $outside = [];

        foreach (DeliveryStatsGroups::all() as $group) {
            $outside[$group] = DeliveryRate::groupOutside($this->kind, $group, $this->excludedKeys);
        }

        return $rate + [
            'deleted' => $deleted,
            'groups' => $groups,
            'outside' => $outside,
            'total_orders' => collect($groups)->sum('total'),
            'trash_orders' => $deleted,
        ];
    }

    /**
     * @return array<string, array{total: int, deleted: int}>
     */
    private function emptyGroups(): array
    {
        $groups = [];

        foreach (DeliveryStatsGroups::all() as $group) {
            $groups[$group] = ['total' => 0, 'deleted' => 0];
        }

        return $groups;
    }
}
