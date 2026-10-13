<?php

namespace App\Domains\Analytics\Support;

/**
 * PHASE 37-N1 (D2) — "was this order ever confirmed?", defined once.
 *
 * The confirmation view does not ask "is it confirmed now" but "did it ever
 * reach the confirmation desk". Once an order is confirmed it keeps counting as
 * confirmed for the member who confirmed it, whatever the warehouse does later
 * (shipped, delivered, returned, cancelled). The first-write-wins stamp
 * `orders.confirmed_at` holds that fact for every order the real flow
 * confirmed; the store's wide confirmed status group is the fallback for rows
 * that arrived confirmed without the stamp (imports, direct jumps, seeded
 * rows), which is exactly the group {@see DashboardStatusGroups::CONFIRMED}
 * already describes.
 *
 * Every confirmation-view count reads the predicate from here — the cards, the
 * doughnut, the rate, the KPI and the trend — so no two blocks can disagree on
 * what "confirmed" means. A confirmation-group row is counted once, in
 * "confirmed", whatever its status has since become; every other bucket counts
 * only the orders that were never confirmed.
 */
final class ConfirmationEvidence
{
    public function __construct(private readonly OrderStatusIdMap $statusIds) {}

    /**
     * The store's confirmed-group ids, which is what the SQL clause reads.
     *
     * @return array<int, string>
     */
    public function ids(): array
    {
        return $this->statusIds->idsForKeys(DashboardStatusGroups::confirmedKeys());
    }

    /**
     * The condition that is true for an ever-confirmed order. Written against
     * the raw `orders` table because every confirmation read joins or selects
     * from it directly.
     */
    public function condition(): string
    {
        $ids = $this->ids();

        $groupHit = $ids === []
            ? ''
            : ' or orders.status_id in ('.implode(', ', array_fill(0, count($ids), '?')).')';

        return '(orders.confirmed_at is not null'.$groupHit.')';
    }

    public function negatedCondition(): string
    {
        return 'not '.$this->condition();
    }

    /**
     * Bindings for {@see condition()} / {@see negatedCondition()}, in the
     * order their `?` placeholders appear.
     *
     * @return array<int, string>
     */
    public function bindings(): array
    {
        return $this->ids();
    }
}
