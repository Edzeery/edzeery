<?php

namespace App\Domains\Analytics\Support;

/**
 * Single source of the confirmation-statistics tab's status buckets
 * (docs/plans/confirmation-statistics-redesign.md § 3, decisions D6/D9/D12 and
 * the PHASE 37-M1 amendment D1/D2).
 *
 * The confirmation view carries no delivery content (D2): any order that moved
 * past confirmation (shipped…unclaimed) stays inside {@see CONFIRMED}, so the
 * "confirmed" card, the doughnut and the rate all count the same rows and the
 * demo store reads 6 confirmed of 7 counted (86%). The desk's own states remain
 * their own groups: pending, failed, canceled, deferred, duplicate and other
 * (on_hold/paid/draft plus any unclassified custom status).
 *
 * PASSED_TO_DELIVERY survives as an internal alias naming the "moved on after
 * confirmation" concept, but it is not a displayed group: it is deliberately
 * absent from {@see all()} and from the map, so no block can render it.
 */
final class ConfirmationStatsGroups
{
    public const CONFIRMED = 'confirmed';

    public const PENDING = 'pending';

    public const FAILED = 'failed';

    public const CANCELED = 'canceled';

    public const DEFERRED = 'deferred';

    public const DUPLICATE = 'duplicate';

    public const OTHER = 'other';

    /**
     * Internal alias for the orders that moved on after confirmation. Carries
     * no bucket of its own — its statuses are mapped to CONFIRMED — so it can
     * never be shown as a card or slice, but the rate code can still name it.
     */
    public const PASSED_TO_DELIVERY = 'passed_to_delivery';

    /** @var array<string, string> */
    private const MAP = [
        'confirmed' => self::CONFIRMED,
        'preparing' => self::CONFIRMED,
        'processing' => self::CONFIRMED,
        'shipped' => self::CONFIRMED,
        'in_transit' => self::CONFIRMED,
        'out_for_delivery' => self::CONFIRMED,
        'completed' => self::CONFIRMED,
        'delivered' => self::CONFIRMED,
        'returned' => self::CONFIRMED,
        'refunded' => self::CONFIRMED,
        'undeliverable' => self::CONFIRMED,
        'unclaimed' => self::CONFIRMED,

        'pending' => self::PENDING,

        'no_answer_1' => self::FAILED,
        'no_answer_2' => self::FAILED,
        'no_answer_3' => self::FAILED,

        'canceled' => self::CANCELED,
        'cancelled' => self::CANCELED,
        'wrong_number' => self::CANCELED,

        'postponed' => self::DEFERRED,
        'out_of_stock' => self::DEFERRED,

        'duplicate' => self::DUPLICATE,

        'on_hold' => self::OTHER,
        'paid' => self::OTHER,
        'draft' => self::OTHER,
    ];

    /**
     * The buckets the numerator of the confirmation rate sums (D1/D12): the
     * desk confirmed it, or it has already moved on. Since D2 folds the
     * moved-on orders into CONFIRMED, the numerator is CONFIRMED alone — a
     * confirmed order that ships stays counted, so shipping never moves the
     * rate; only canceled/failed orders pull it down.
     *
     * @return list<string>
     */
    public const NUMERATOR_GROUPS = [
        self::CONFIRMED,
    ];

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::CONFIRMED,
            self::PENDING,
            self::FAILED,
            self::CANCELED,
            self::DEFERRED,
            self::DUPLICATE,
            self::OTHER,
        ];
    }

    /**
     * The status keys a store may keep out of the rate (PHASE 37-N2, D1): the
     * desk's own states, but never CONFIRMED or CANCELED — the numerator and
     * the net denominator cannot be excluded, or the ratio would have no
     * meaning.
     *
     * @return list<string>
     */
    public static function excludableKeys(): array
    {
        return array_keys(array_filter(
            self::MAP,
            static fn (string $group): bool => $group !== self::CONFIRMED && $group !== self::CANCELED
        ));
    }

    public static function group(string $statusKey): string
    {
        return self::MAP[$statusKey] ?? self::OTHER;
    }

    public static function in(string $statusKey, string $group): bool
    {
        return self::group($statusKey) === $group;
    }

    /**
     * @return list<string>
     */
    public static function keys(string $group): array
    {
        return array_keys(array_filter(
            self::MAP,
            static fn (string $mapped) => $mapped === $group
        ));
    }
}
