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
            $row['pending'] = 0;
            $row['canceled'] = 0;

            foreach ($work as $entry) {
                $row['assigned'] += (int) $entry['assigned'];
                $row['pending'] += (int) $entry['pending'];
                $row['canceled'] += (int) $entry['canceled'];
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
            $row['delivered'] = (int) $entry['delivered'];
            $row['returned'] = (int) $entry['returned'];
            $row['revenue'] = (float) $entry['revenue'];
            $merged[$id ?? ''] = $row;
        }

        foreach ($work as $entry) {
            $id = $entry['membership_id'];
            $row = $merged[$id ?? ''] ?? $this->blank();
            $row['membership_id'] = $row['membership_id'] ?? $id;
            $row['assigned'] = (int) $entry['assigned'];
            $row['pending'] = (int) $entry['pending'];
            $row['canceled'] = (int) $entry['canceled'];
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

        return [
            'membership_id' => $id,
            'type' => $type,
            'name' => $name,
            'assigned' => $assigned,
            'confirmed' => $confirmed,
            'pending' => $pending,
            'canceled' => $canceled,
            // Work remainder: what was assigned and is no longer pending or
            // canceled, whatever became of it.
            'other' => max(0, $assigned - $pending - $canceled),
            'delivered' => $delivered,
            'returned' => $returned,
            // Credit remainder (§ 11): delivered and returned come out of the
            // confirmed base.
            'in_progress' => max(0, $confirmed - $delivered - $returned),
            'revenue' => (float) $row['revenue'],
            'confirmation_rate' => $this->rate($confirmed, $assigned),
            // Conversion is measured against the confirmed base, not the whole
            // workload: work can outnumber credit and must not dilute it.
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
            foreach (['assigned', 'confirmed', 'pending', 'canceled', 'delivered', 'returned', 'revenue'] as $field) {
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
            'membership_id' => null, 'assigned' => 0, 'confirmed' => 0, 'pending' => 0,
            'canceled' => 0, 'delivered' => 0, 'returned' => 0, 'revenue' => 0,
        ];
    }
}
