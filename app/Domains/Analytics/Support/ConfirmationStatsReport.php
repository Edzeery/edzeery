<?php

namespace App\Domains\Analytics\Support;

/**
 * Folds the rows of {@see ConfirmationStatsQuery} into the tab's numbers
 * (docs/plans/confirmation-statistics-redesign.md § 4).
 *
 * Pure PHP with no queries. The headline card, each team row and the total all
 * go through build(), so they can never disagree on what "confirmed" means or
 * what sits under the rate's line — the drift C1 described.
 */
final class ConfirmationStatsReport
{
    /**
     * @param  list<string>  $denominatorGroups  Buckets under the rate's line.
     */
    public function __construct(private readonly array $denominatorGroups) {}

    /**
     * @param  list<array{membership_id: ?string, status_key: string, total: int, deleted: int}>  $rows
     * @return array{groups: array<string, array{total: int, deleted: int}>, confirmed: int, denominator: int, rate: int}
     */
    public function build(array $rows): array
    {
        $groups = $this->emptyGroups();

        foreach ($rows as $row) {
            $group = ConfirmationStatsGroups::group($row['status_key']);
            $groups[$group]['total'] += (int) $row['total'];
            $groups[$group]['deleted'] += (int) $row['deleted'];
        }

        return $this->summarize($groups);
    }

    /**
     * One summary per membership id, with '' holding the unattributed bucket.
     * The team section and the headline card answer with the same shape, so a
     * member row summing into the total is a property of the maths, not a
     * convention to remember.
     *
     * @param  list<array{membership_id: ?string, status_key: string, total: int, deleted: int}>  $rows
     * @return array<string, array{groups: array<string, array{total: int, deleted: int}>, confirmed: int, denominator: int, rate: int}>
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
     * @param  array<string, array{total: int, deleted: int}>  $groups
     * @return array{groups: array<string, array{total: int, deleted: int}>, confirmed: int, denominator: int, rate: int}
     */
    private function summarize(array $groups): array
    {
        // D12: the numerator spans CONFIRMED and PASSED_TO_DELIVERY, so an
        // order that moves from confirmed to shipped stays counted and the
        // rate does not drift when the warehouse acts.
        $confirmed = 0;

        foreach (ConfirmationStatsGroups::NUMERATOR_GROUPS as $group) {
            $confirmed += $groups[$group]['total'] ?? 0;
        }

        $denominator = 0;

        foreach ($this->denominatorGroups as $group) {
            $denominator += $groups[$group]['total'] ?? 0;
        }

        return [
            'groups' => $groups,
            'confirmed' => $confirmed,
            'denominator' => $denominator,
            'rate' => $this->rate($confirmed, $denominator),
        ];
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

    private function rate(int $numerator, int $denominator): int
    {
        if ($denominator <= 0) {
            return 0;
        }

        return max(0, min(100, (int) round(($numerator / $denominator) * 100)));
    }
}
