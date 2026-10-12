<?php

namespace App\Domains\Analytics\Support;

use App\Domains\Analytics\DTOs\DashboardFilter;
use Illuminate\Support\Collection;

/**
 * Turns the per-member summaries of {@see ConfirmationStatsReport::perMember}
 * into the rows of the confirmation team section (plan § 6.4, decision D5).
 *
 * Pure PHP with no queries. Each row carries the full group breakdown, so the
 * view can render the same row as a "count" card (all orders + trash) or a
 * "rate" card (group summary + rate bar) without a second scan. Deleted orders
 * travel in their own column and never enter the rate (D7); the members
 * without/with-trash toggle only decides which members are named.
 */
final class DashboardConfirmationTeam
{
    /**
     * @param  array<string, array{groups: array<string, array{total: int, deleted: int}>, confirmed: int, denominator: int, rate: int}>  $summaries  Keyed by membership id; '' holds the unattributed bucket.
     * @param  Collection<int, object{id: string, name: string, deleted?: bool}>  $members
     * @return list<array<string, mixed>>
     */
    public function present(array $summaries, DashboardFilter $filter, Collection $members): array
    {
        $names = $members->keyBy('id');
        $unattributedOnly = $filter->memberUnattributedOnly;
        // The unattributed row is a store-wide bucket: it shows on the
        // unrestricted section and, alone, under the sentinel pick.
        $showsUnattributed = $filter->memberScopeIds === null || $unattributedOnly;
        // One selected member already is the total; a team or an unrestricted
        // store needs the sum so the rows reconcile with the headline above.
        $showsTotal = $filter->memberScopeIds === null || count((array) $filter->memberScopeIds) > 1;

        $shown = [];

        foreach ($summaries as $id => $summary) {
            if ($id === '') {
                if ($showsUnattributed) {
                    $shown[] = $this->row($summary, null, null, 'unattributed', false);
                }

                continue;
            }

            // A membership the user may not see (another store, or one dropped
            // by the without-trash toggle) is omitted rather than named.
            if (! isset($names[$id])) {
                continue;
            }

            $shown[] = $this->row(
                $summary,
                (string) $id,
                $names[$id]->name,
                'member',
                (bool) ($names[$id]->deleted ?? false),
            );
        }

        // The sentinel picked the whole unattributed cohort: only its row.
        if ($unattributedOnly) {
            return array_values(array_filter($shown, static fn (array $row): bool => $row['type'] === 'unattributed'));
        }

        // The member picked in the filter keeps a row even when the period
        // holds nothing for them, so an empty selection reads as zero rather
        // than as a missing member.
        if ($filter->memberId !== null && $filter->memberId !== '' && $filter->memberId !== DashboardFilter::UNATTRIBUTED) {
            $seen = array_column($shown, 'membership_id');

            if (! in_array($filter->memberId, $seen, true)) {
                $name = isset($names[$filter->memberId])
                    ? $names[$filter->memberId]->name
                    : $filter->memberId;

                $shown[] = $this->row($this->blank(), $filter->memberId, $name, 'member', false);
            }
        }

        // Sorted by total orders, then by name; the unattributed row competes
        // on its own numbers like any other row.
        usort($shown, fn (array $a, array $b) => $b['total'] <=> $a['total'] ?: strcmp((string) $a['name'], (string) $b['name']));

        if ($showsTotal) {
            $shown[] = $this->total($shown);
        }

        return $shown;
    }

    /**
     * @param  array{groups: array<string, array{total: int, deleted: int}>, confirmed: int, denominator: int, rate: int}  $summary
     * @return array<string, mixed>
     */
    private function row(array $summary, ?string $id, ?string $name, string $type, bool $deletedMember): array
    {
        $groups = $summary['groups'];
        $total = 0;
        $trash = 0;

        foreach ($groups as $group) {
            $total += (int) $group['total'];
            $trash += (int) $group['deleted'];
        }

        return [
            'membership_id' => $id,
            'type' => $type,
            'name' => $name,
            'deleted_member' => $deletedMember,
            'groups' => $groups,
            'confirmed' => (int) $summary['confirmed'],
            'denominator' => (int) $summary['denominator'],
            'rate' => (int) $summary['rate'],
            'total' => $total,
            'trash' => $trash,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $shown
     * @return array<string, mixed>
     */
    private function total(array $shown): array
    {
        $groups = $this->blankGroups();
        $confirmed = 0;
        $denominator = 0;

        foreach ($shown as $row) {
            foreach ($groups as $key => $_) {
                $groups[$key]['total'] += (int) ($row['groups'][$key]['total'] ?? 0);
                $groups[$key]['deleted'] += (int) ($row['groups'][$key]['deleted'] ?? 0);
            }

            $confirmed += (int) $row['confirmed'];
            $denominator += (int) $row['denominator'];
        }

        return $this->row(
            [
                'groups' => $groups,
                'confirmed' => $confirmed,
                'denominator' => $denominator,
                'rate' => $denominator > 0 ? max(0, min(100, (int) round(($confirmed / $denominator) * 100))) : 0,
            ],
            null,
            null,
            'total',
            false,
        );
    }

    /** @return array{groups: array<string, array{total: int, deleted: int}>, confirmed: int, denominator: int, rate: int} */
    private function blank(): array
    {
        return ['groups' => $this->blankGroups(), 'confirmed' => 0, 'denominator' => 0, 'rate' => 0];
    }

    /** @return array<string, array{total: int, deleted: int}> */
    private function blankGroups(): array
    {
        $groups = [];

        foreach (ConfirmationStatsGroups::all() as $group) {
            $groups[$group] = ['total' => 0, 'deleted' => 0];
        }

        return $groups;
    }
}
