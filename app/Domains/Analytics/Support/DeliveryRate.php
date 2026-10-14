<?php

namespace App\Domains\Analytics\Support;

use App\Domains\Analytics\DTOs\DashboardFilter;

/**
 * PHASE 37-N7 (D3) — the single place the delivery rate is computed.
 *
 * The date basis picks the formula, mirroring the confirmation rate:
 *
 *  - `created` (gross): delivered (and returned) over every order created in
 *    the window — the merchant's "all orders" denominator.
 *  - `last_status_changed` (net): delivered over the settled delivery
 *    outcomes, delivered + returned, so late statuses cannot dilute the line.
 *
 * The store may keep status KEYS out of every term ("outside the rate"); those
 * keys are dropped from the sums here. The headline cards and the per-team
 * footer both call compute(), so they can never disagree about the rate.
 */
final class DeliveryRate
{
    public const GROSS = 'gross';

    public const NET = 'net';

    /** The kind the given date basis asks for. */
    public static function kindForBasis(string $basis): string
    {
        return $basis === DashboardFilter::DATE_BASIS_LAST_STATUS ? self::NET : self::GROSS;
    }

    /**
     * @param  string  $kind  self::GROSS or self::NET.
     * @param  array<string, int>  $counts  Live orders per raw status key.
     * @param  list<string>  $excludedKeys  Status keys kept out of every term.
     * @return array{kind: string, delivered: int, returned: int, denominator: int, delivery_rate: int, return_rate: int, excluded: int}
     */
    public static function compute(string $kind, array $counts, array $excludedKeys): array
    {
        $excluded = array_fill_keys($excludedKeys, true);

        $delivered = 0;
        $returned = 0;
        $gross = 0;
        $excludedCount = 0;

        foreach ($counts as $key => $count) {
            $key = (string) $key;
            $count = (int) $count;

            if (isset($excluded[$key])) {
                $excludedCount += $count;

                continue;
            }

            $gross += $count;

            if (DeliveryStatsGroups::isDelivered($key)) {
                $delivered += $count;
            } elseif (DeliveryStatsGroups::isReturned($key)) {
                $returned += $count;
            }
        }

        $net = $delivered + $returned;
        $denominator = $kind === self::NET ? $net : $gross;

        return [
            'kind' => $kind === self::NET ? self::NET : self::GROSS,
            'delivered' => $delivered,
            'returned' => $returned,
            'denominator' => $denominator,
            'delivery_rate' => self::percent($delivered, $denominator),
            'return_rate' => self::percent($returned, $denominator),
            'excluded' => $excludedCount,
        ];
    }

    public static function percent(int $numerator, int $denominator): int
    {
        if ($denominator <= 0) {
            return 0;
        }

        return max(0, min(100, (int) round(($numerator / $denominator) * 100)));
    }

    /**
     * Whether a bucket contributes nothing to the current basis's denominator,
     * mirroring the confirmation rate: on the net basis only the two measured
     * buckets are inside the ratio, and on the gross basis a bucket is outside
     * once every one of its status keys was excluded.
     *
     * @param  list<string>  $excludedKeys
     */
    public static function groupOutside(string $kind, string $group, array $excludedKeys): bool
    {
        if ($kind === self::NET && $group !== DeliveryStatsGroups::DELIVERED && $group !== DeliveryStatsGroups::RETURNED) {
            return true;
        }

        $keys = DeliveryStatsGroups::keys($group);

        if ($keys === []) {
            return false;
        }

        return array_diff($keys, $excludedKeys) === [];
    }
}
