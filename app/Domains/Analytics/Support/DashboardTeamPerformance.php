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
     * @param  list<array<string, mixed>>  $rows
     * @param  Collection<int, object{id: string, name: string}>  $members
     * @return list<array<string, mixed>>
     */
    public function present(array $rows, DashboardFilter $filter, Collection $members): array
    {
        $names = $members->keyBy('id');
        $metric = $filter->memberDimension === 'delivery' ? 'delivered' : 'confirmed';
        $showsUnassigned = $filter->memberScopeIds === null;
        // One selected member already is the total; a team or an unrestricted
        // store needs the sum so the rows reconcile with the KPIs above.
        $showsTotal = $filter->memberScopeIds === null || count($filter->memberScopeIds) > 1;

        $shown = [];

        foreach ($rows as $row) {
            $id = $row['membership_id'];

            if ($id === null) {
                if ($showsUnassigned) {
                    $shown[] = $this->row($row, null, __('dashboard.team_unassigned'), 'unassigned');
                }

                continue;
            }

            // A membership the user may not see (another store, or deactivated
            // since the order was assigned) is dropped rather than named.
            if (! isset($names[$id])) {
                continue;
            }

            $shown[] = $this->row($row, (string) $id, $names[$id]->name, 'member');
        }

        // The member picked in the filter keeps a row even when the period
        // holds nothing for them, so an empty selection reads as zero rather
        // than as a missing member.
        if ($filter->memberId !== null && $filter->memberId !== '') {
            $seen = array_column($shown, 'membership_id');

            if (! in_array($filter->memberId, $seen, true)) {
                $name = isset($names[$filter->memberId])
                    ? $names[$filter->memberId]->name
                    : $filter->memberId;

                $shown[] = $this->row($this->blank(), $filter->memberId, $name, 'member');
            }
        }

        // Sorted by the metric the active view leads with, then by name; the
        // Unassigned row competes on its own numbers like any other row.
        usort($shown, fn (array $a, array $b) => $b[$metric] <=> $a[$metric] ?: strcmp($a['name'], $b['name']));

        if ($showsTotal) {
            $shown[] = $this->total($shown);
        }

        return $shown;
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
            'other' => max(0, $assigned - $confirmed - $pending - $canceled),
            'delivered' => $delivered,
            'returned' => $returned,
            'in_progress' => max(0, $assigned - $delivered - $returned),
            'revenue' => (float) $row['revenue'],
            'confirmation_rate' => $this->rate($confirmed, $assigned),
            'delivery_rate' => $this->rate($delivered, $assigned),
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
