<?php

namespace App\Domains\Analytics\Support;

/**
 * The denominator of the confirmation rate (docs/plans/confirmation-statistics-redesign.md § 4.1).
 *
 * The numerator is always the CONFIRMED bucket; this class decides which
 * buckets sit under the line. It is the same list for the headline card, every
 * team row and the total, which is what keeps the three from drifting apart.
 */
final class ConfirmationRateDenominator
{
    /** Settled orders only: the desk either confirmed it, or wrote it off. */
    public const DEFAULT_GROUPS = [
        ConfirmationStatsGroups::CONFIRMED,
        ConfirmationStatsGroups::CANCELED,
        ConfirmationStatsGroups::FAILED,
    ];

    /** "Every order except the duplicates and the deferred ones." */
    public const PRESET_EXCLUDE_DUPLICATE_AND_DEFERRED = [
        ConfirmationStatsGroups::CONFIRMED,
        ConfirmationStatsGroups::PENDING,
        ConfirmationStatsGroups::FAILED,
        ConfirmationStatsGroups::CANCELED,
        ConfirmationStatsGroups::DELIVERY_OUTCOMES,
        ConfirmationStatsGroups::UNCLASSIFIED,
    ];

    /**
     * Keeps only buckets the tab actually knows, so a stale or hand-edited
     * setting can never introduce a phantom bucket or an empty denominator.
     * An empty or wholly unknown list falls back to the recommended default.
     *
     * @param  ?array<int, string>  $configured
     * @return list<string>
     */
    public static function resolve(?array $configured): array
    {
        $valid = array_values(array_intersect($configured ?? [], ConfirmationStatsGroups::all()));

        return $valid === [] ? self::DEFAULT_GROUPS : $valid;
    }
}
