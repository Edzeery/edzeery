<?php

namespace App\Domains\Analytics\Support;

/**
 * Folds the rows of {@see ConfirmationStatsQuery} into the tab's numbers
 * (docs/plans/confirmation-statistics-redesign.md § 4, PHASE 37-N2).
 *
 * Pure PHP with no queries. The headline card, each team row and the total all
 * go through build(), so they can never disagree on what "confirmed" means or
 * what sits under the rate's line — the drift C1 described. The rate itself
 * comes from the one {@see ConfirmationRate} so the gross basis, the net basis
 * and the store's exclusions can never be read two ways.
 */
final class ConfirmationStatsReport
{
    /**
     * @param  string  $kind  ConfirmationRate::GROSS or ::NET.
     * @param  list<string>  $excludedKeys  Status keys kept out of the rate.
     */
    public function __construct(
        private readonly string $kind,
        private readonly array $excludedKeys = [],
    ) {}

    /**
     * @param  list<array{membership_id: ?string, status_key: string, total: int, deleted: int}>  $rows
     * @return array{
     *     groups: array<string, array{total: int, deleted: int}>,
     *     confirmed: int,
     *     denominator: int,
     *     rate: int,
     *     kind: string,
     *     excluded: int,
     *     outside: array<string, bool>
     * }
     */
    public function build(array $rows): array
    {
        $groups = $this->emptyGroups();
        // The rate needs per-status-key counts (not per-bucket) so an excluded
        // key is subtracted exactly, wherever it sits.
        $counts = [];

        foreach ($rows as $row) {
            $key = (string) $row['status_key'];
            $group = ConfirmationStatsGroups::group($key);
            $groups[$group]['total'] += (int) $row['total'];
            $groups[$group]['deleted'] += (int) $row['deleted'];
            $counts[$key] = ($counts[$key] ?? 0) + (int) $row['total'];
        }

        $rate = ConfirmationRate::compute($this->kind, $counts, $this->excludedKeys);

        $outside = [];

        foreach (ConfirmationStatsGroups::all() as $group) {
            $outside[$group] = ConfirmationRate::groupOutside($this->kind, $group, $this->excludedKeys);
        }

        return [
            'groups' => $groups,
            'confirmed' => $rate['numerator'],
            'denominator' => $rate['denominator'],
            'rate' => $rate['rate'],
            'kind' => $rate['kind'],
            'excluded' => $rate['excluded'],
            'outside' => $outside,
        ];
    }

    /**
     * One summary per membership id, with '' holding the unattributed bucket.
     * The team section and the headline card answer with the same shape, so a
     * member row summing into the total is a property of the maths, not a
     * convention to remember.
     *
     * @param  list<array{membership_id: ?string, status_key: string, total: int, deleted: int}>  $rows
     * @return array<string, array{groups: array<string, array{total: int, deleted: int}>, confirmed: int, denominator: int, rate: int, kind: string, excluded: int, outside: array<string, bool>}>
     */
    public function perMember(array $rows): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $grouped[$row['membership_id'] ?? ''][] = $row;
        }

        $summaries = [];

        foreach ($grouped as $id => $memberRows) {
            $summaries[$id] = $this->build($memberRows);
        }

        return $summaries;
    }

    /**
     * @return array<string, array{total: int, deleted: int}>
     */
    private function emptyGroups(): array
    {
        $groups = [];

        foreach (ConfirmationStatsGroups::all() as $group) {
            $groups[$group] = ['total' => 0, 'deleted' => 0];
        }

        return $groups;
    }
}
