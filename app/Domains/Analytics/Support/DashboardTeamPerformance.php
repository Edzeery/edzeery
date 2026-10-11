<?php

namespace App\Domains\Analytics\Support;

use App\Domains\Analytics\DTOs\DashboardFilter;
use Illuminate\Support\Collection;

/**
 * Turns the aggregate rows into what the partial renders. Pure PHP with no
 * queries, so every visibility rule can be unit-tested on its own.
 */
final class DashboardTeamPerformance
{
    /**
     * @param  list<array<string, mixed>>  $credit
     * @param  list<array<string, mixed>>  $work
     * @param  Collection<int, object{id: string, name: string}>  $members
     * @return list<array<string, mixed>>
     */
    public function present(array $credit, array $work, DashboardFilter $filter, Collection $members): array
    {
        $names = $members->keyBy('id');
        $metric = $filter->memberDimension === 'delivery' ? 'delivered' : 'confirmed';
        $unattributedOnly = $filter->memberUnattributedOnly;
        // The unattributed row is a store-wide bucket: it shows on the
        // unrestricted table and, alone, under the sentinel pick.
        $showsUnattributed = $filter->memberScopeIds === null || $unattributedOnly;
        // One selected member already is the total; a team or an unrestricted
        // store needs the sum so the rows reconcile with the KPIs above.
        $showsTotal = $filter->memberScopeIds === null || count($filter->memberScopeIds) > 1;

        $merged = $this->merge($credit, $work);

        // The sentinel picked the whole unattributed cohort: one row whose
        // workload is the sum of every work row. Both aggregates were already
        // narrowed to confirmed_by IS NULL, so work exists only inside this
        // cohort.
        if ($unattributedOnly) {
            $row = $merged[null] ?? $this->blank();
            $row['assigned'] = 0;
            $row['assigned_deleted'] = 0;
            $row['pending'] = 0;
            $row['pending_deleted'] = 0;
            $row['canceled'] = 0;
            $row['canceled_deleted'] = 0;

            foreach ($work as $entry) {
                $row['assigned'] += (int) $entry['assigned'];
                $row['assigned_deleted'] += (int) ($entry['assigned_deleted'] ?? 0);
                $row['pending'] += (int) $entry['pending'];
                $row['pending_deleted'] += (int) ($entry['pending_deleted'] ?? 0);
                $row['canceled'] += (int) $entry['canceled'];
                $row['canceled_deleted'] += (int) ($entry['canceled_deleted'] ?? 0);
            }

            return [$this->row($row, null, __('dashboard.team_unattributed'), 'unattributed')];
        }

        $shown = [];

        foreach ($merged as $entry) {
            $id = $entry['membership_id'];

            if ($id === null) {
                if ($showsUnattributed) {
                    $shown[] = $this->row($entry, null, __('dashboard.team_unattributed'), 'unattributed');
                }

                continue;
            }

            // A membership the user may not see (another store, or deactivated
            // since the order was confirmed) is dropped rather than named.
            if (! isset($names[$id])) {
                continue;
            }

            $shown[] = $this->row($entry, (string) $id, $names[$id]->name, 'member');
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

                $shown[] = $this->row($this->blank(), $filter->memberId, $name, 'member');
            }
        }

        // Sorted by the metric the active view leads with, then by name; the
        // Unattributed row competes on its own numbers like any other row.
        usort($shown, fn (array $a, array $b) => $b[$metric] <=> $a[$metric] ?: strcmp($a['name'], $b['name']));

        if ($showsTotal) {
            $shown[] = $this->total($shown);
        }

        return $shown;
    }

    /**
     * Merge credit and work per membership id ('' holds the null bucket). Each
     * side only knows its own keys, so the other side keeps its zero defaults.
     *
     * @param  list<array<string, mixed>>  $credit
     * @param  list<array<string, mixed>>  $work
     * @return array<string, array<string, mixed>>
     */
    private function merge(array $credit, array $work): array
    {
        $merged = [];

        foreach ($credit as $entry) {
            $id = $entry['membership_id'];
            $row = $this->blank();
            $row['membership_id'] = $id;
            $row['confirmed'] = (int) $entry['confirmed'];
            $row['confirmed_deleted'] = (int) ($entry['confirmed_deleted'] ?? 0);
            $row['delivered'] = (int) $entry['delivered'];
            $row['delivered_deleted'] = (int) ($entry['delivered_deleted'] ?? 0);
            $row['returned'] = (int) $entry['returned'];
            $row['returned_deleted'] = (int) ($entry['returned_deleted'] ?? 0);
            $row['revenue'] = (float) $entry['revenue'];
            $merged[$id ?? ''] = $row;
        }

        foreach ($work as $entry) {
            $id = $entry['membership_id'];
            $row = $merged[$id ?? ''] ?? $this->blank();
            $row['membership_id'] = $row['membership_id'] ?? $id;
            $row['assigned'] = (int) $entry['assigned'];
            $row['assigned_deleted'] = (int) ($entry['assigned_deleted'] ?? 0);
            $row['pending'] = (int) $entry['pending'];
            $row['pending_deleted'] = (int) ($entry['pending_deleted'] ?? 0);
            $row['canceled'] = (int) $entry['canceled'];
            $row['canceled_deleted'] = (int) ($entry['canceled_deleted'] ?? 0);
            $merged[$id ?? ''] = $row;
        }

        return $merged;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function row(array $row, ?string $id, string $name, string $type): array
    {
        $assigned = (int) $row['assigned'];
        $confirmed = (int) $row['confirmed'];
        $pending = (int) $row['pending'];
        $canceled = (int) $row['canceled'];
        $delivered = (int) $row['delivered'];
        $returned = (int) $row['returned'];

        $assignedDeleted = (int) ($row['assigned_deleted'] ?? 0);
        $confirmedDeleted = (int) ($row['confirmed_deleted'] ?? 0);
        $pendingDeleted = (int) ($row['pending_deleted'] ?? 0);
        $canceledDeleted = (int) ($row['canceled_deleted'] ?? 0);
        $deliveredDeleted = (int) ($row['delivered_deleted'] ?? 0);
        $returnedDeleted = (int) ($row['returned_deleted'] ?? 0);

        return [
            'membership_id' => $id,
            'type' => $type,
            'name' => $name,
            'assigned' => $assigned,
            'assigned_deleted' => $assignedDeleted,
            'confirmed' => $confirmed,
            'confirmed_deleted' => $confirmedDeleted,
            'pending' => $pending,
            'pending_deleted' => $pendingDeleted,
            'canceled' => $canceled,
            'canceled_deleted' => $canceledDeleted,
            // A trashed sibling for the "everything else" bucket, floored at
            // zero the same way the live figure is.
            'other' => max(0, $assigned - $pending - $canceled),
            'other_deleted' => max(0, $assignedDeleted - $pendingDeleted - $canceledDeleted),
            'delivered' => $delivered,
            'delivered_deleted' => $deliveredDeleted,
            'returned' => $returned,
            'returned_deleted' => $returnedDeleted,
            'in_progress' => max(0, $confirmed - $delivered - $returned),
            'in_progress_deleted' => max(0, $confirmedDeleted - $deliveredDeleted - $returnedDeleted),
            'revenue' => (float) $row['revenue'],
            'confirmation_rate' => $this->rate($confirmed, $assigned),
            'delivery_rate' => $this->rate($delivered, $confirmed),
            'return_rate' => $this->rate($returned, $delivered + $returned),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $shown
     * @return array<string, mixed>
     */
    private function total(array $shown): array
    {
        $sums = [];

        foreach ($shown as $row) {
            foreach ([
                'assigned', 'assigned_deleted', 'confirmed', 'confirmed_deleted',
                'pending', 'pending_deleted', 'canceled', 'canceled_deleted',
                'delivered', 'delivered_deleted', 'returned', 'returned_deleted',
                'revenue',
            ] as $field) {
                $sums[$field] = ($sums[$field] ?? 0) + $row[$field];
            }
        }

        return $this->row(
            array_merge($this->blank(), $sums),
            null,
            __('dashboard.team_total'),
            'total'
        );
    }

    private function rate(int $numerator, int $denominator): int
    {
        if ($denominator <= 0) {
            return 0;
        }

        return max(0, min(100, (int) round(($numerator / $denominator) * 100)));
    }

    /** @return array<string, mixed> */
    private function blank(): array
    {
        return [
            'membership_id' => null,
            'assigned' => 0, 'assigned_deleted' => 0,
            'confirmed' => 0, 'confirmed_deleted' => 0,
            'pending' => 0, 'pending_deleted' => 0,
            'canceled' => 0, 'canceled_deleted' => 0,
            'delivered' => 0, 'delivered_deleted' => 0,
            'returned' => 0, 'returned_deleted' => 0,
            'revenue' => 0,
        ];
    }
}
