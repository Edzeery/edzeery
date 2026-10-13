<?php

namespace App\Domains\Analytics\Support;

use App\Domains\Analytics\DTOs\DashboardFilter;

/**
 * PHASE 37-N2 (D1) — the single place the confirmation rate is computed.
 *
 * The date basis picks the formula:
 *
 *  - `created` (gross): confirmed over every order created in the window, the
 *    denominator the merchant sees as "all orders".
 *  - `last_status_changed` (net): confirmed over the settled desk outcomes,
 *    confirmed + canceled.
 *
 * The numerator is always CONFIRMED, which since PHASE 37-M1/D2 also holds the
 * orders that moved on after confirmation, so shipping an order never moves the
 * line. On top of that, the store may keep a set of status KEYS out of every
 * term ("outside the rate"); those keys are simply dropped from the sums here.
 * The headline card, each team row and the total all call compute(), so they
 * can never disagree about what the rate means.
 */
final class ConfirmationRate
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
     * @param  array<string, int>  $counts  Live orders per raw status key
     *                                      ('confirmed' holds the moved-on orders).
     * @param  list<string>  $excludedKeys  Status keys kept out of every term.
     * @return array{kind: string, numerator: int, denominator: int, rate: int, excluded: int}
     */
    public static function compute(string $kind, array $counts, array $excludedKeys): array
    {
        $excluded = array_fill_keys($excludedKeys, true);

        $numerator = 0;
        $gross = 0;
        $net = 0;
        $excludedCount = 0;

        foreach ($counts as $key => $count) {
            $key = (string) $key;

            if (isset($excluded[$key])) {
                $excludedCount += (int) $count;

                continue;
            }

            $group = ConfirmationStatsGroups::group($key);
            $count = (int) $count;
            $gross += $count;

            if ($group === ConfirmationStatsGroups::CONFIRMED) {
                $numerator += $count;
            }

            if ($group === ConfirmationStatsGroups::CONFIRMED || $group === ConfirmationStatsGroups::CANCELED) {
                $net += $count;
            }
        }

        $denominator = $kind === self::NET ? $net : $gross;

        return [
            'kind' => $kind === self::NET ? self::NET : self::GROSS,
            'numerator' => $numerator,
            'denominator' => $denominator,
            'rate' => self::percent($numerator, $denominator),
            'excluded' => $excludedCount,
        ];
    }

    /**
     * Whether a group contributes nothing to the denominator: the net basis
     * leaves every bucket but confirmed/canceled out, and a group whose every
     * status key was excluded reads the same way on the gross basis.
     *
     * @param  list<string>  $excludedKeys
     */
    public static function groupOutside(string $kind, string $group, array $excludedKeys): bool
    {
        if ($kind === self::NET && $group !== ConfirmationStatsGroups::CONFIRMED && $group !== ConfirmationStatsGroups::CANCELED) {
            return true;
        }

        $keys = ConfirmationStatsGroups::keys($group);

        if ($keys === []) {
            return false;
        }

        return array_diff($keys, $excludedKeys) === [];
    }

    public static function percent(int $numerator, int $denominator): int
    {
        if ($denominator <= 0) {
            return 0;
        }

        return max(0, min(100, (int) round(($numerator / $denominator) * 100)));
    }
}
