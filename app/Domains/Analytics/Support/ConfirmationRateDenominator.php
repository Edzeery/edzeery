<?php

namespace App\Domains\Analytics\Support;

/**
 * Picks the buckets under the confirmation rate's line
 * (docs/plans/confirmation-statistics-redesign.md § 4, decision D12).
 *
 * The numerator is fixed — CONFIRMED, which since D2 also holds the orders
 * that moved on after confirmation (see
 * {@see ConfirmationStatsGroups::NUMERATOR_GROUPS}) — so shipping a confirmed
 * order never lowers the rate. This class only decides what else counts
 * against it.
 */
final class ConfirmationRateDenominator
{
    /**
     * The settled desk outcomes: the order was confirmed (including any that
     * have already moved on), or it was written off. Pending, deferred,
     * duplicate and "other" stay out: they are not the desk's final word.
     */
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
        ConfirmationStatsGroups::OTHER,
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
