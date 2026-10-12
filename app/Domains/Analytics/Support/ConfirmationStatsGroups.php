<?php

namespace App\Domains\Analytics\Support;

/**
 * Single source of the confirmation-statistics tab's status buckets
 * (docs/plans/confirmation-statistics-redesign.md § 3, decisions D6/D9/D12).
 *
 * Deliberately separate from {@see DashboardStatusGroups}: the dashboard's
 * CONFIRMED group is a wide KPI bucket that also swallows the delivery
 * outcomes, while the confirmation tab counts the desk's own states.
 *
 * PHASE 37-L (D12) split the old DELIVERY_OUTCOMES bucket in two:
 *  - PASSED_TO_DELIVERY — the order left the desk (shipped…unclaimed). These
 *    orders stay "confirmed" for the rate, so shipping an order never lowers
 *    it (the drift the phase fixes).
 *  - OTHER — on_hold/paid/draft, which are neither confirmed nor written off
 *    and therefore never enter the rate; they show as a muted extra group.
 *
 * Anything unmapped — including a store's own custom statuses until D11 links
 * them — lands in UNCLASSIFIED and is shown as such rather than silently
 * dropped.
 */
final class ConfirmationStatsGroups
{
    public const CONFIRMED = 'confirmed';

    public const PENDING = 'pending';

    public const FAILED = 'failed';

    public const CANCELED = 'canceled';

    public const DEFERRED = 'deferred';

    public const DUPLICATE = 'duplicate';

    public const PASSED_TO_DELIVERY = 'passed_to_delivery';

    public const OTHER = 'other';

    public const UNCLASSIFIED = 'unclassified';

    /** @var array<string, string> */
    private const MAP = [
        'confirmed' => self::CONFIRMED,
        'preparing' => self::CONFIRMED,
        'processing' => self::CONFIRMED,

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

        'shipped' => self::PASSED_TO_DELIVERY,
        'in_transit' => self::PASSED_TO_DELIVERY,
        'out_for_delivery' => self::PASSED_TO_DELIVERY,
        'completed' => self::PASSED_TO_DELIVERY,
        'delivered' => self::PASSED_TO_DELIVERY,
        'returned' => self::PASSED_TO_DELIVERY,
        'refunded' => self::PASSED_TO_DELIVERY,
        'undeliverable' => self::PASSED_TO_DELIVERY,
        'unclaimed' => self::PASSED_TO_DELIVERY,

        'on_hold' => self::OTHER,
        'paid' => self::OTHER,
        'draft' => self::OTHER,
    ];

    /**
     * The buckets the numerator of the confirmation rate sums (D1/D12): the
     * desk confirmed it, or it has already left for delivery. A confirmed
     * order that ships stays in the numerator, so shipping never moves the
     * rate; only canceled/failed orders pull it down.
     *
     * @return list<string>
     */
    public const NUMERATOR_GROUPS = [
        self::CONFIRMED,
        self::PASSED_TO_DELIVERY,
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
            self::PASSED_TO_DELIVERY,
            self::OTHER,
            self::UNCLASSIFIED,
        ];
    }

    public static function group(string $statusKey): string
    {
        return self::MAP[$statusKey] ?? self::UNCLASSIFIED;
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
