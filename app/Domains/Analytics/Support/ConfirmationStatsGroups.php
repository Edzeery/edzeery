<?php

namespace App\Domains\Analytics\Support;

/**
 * Single source of the confirmation-statistics tab's status buckets
 * (docs/plans/confirmation-statistics-redesign.md § 3, decisions D6/D9).
 *
 * Deliberately separate from {@see DashboardStatusGroups}: the dashboard's
 * CONFIRMED group is a wide KPI bucket that also swallows the delivery
 * outcomes, while the confirmation tab counts only the desk's confirmed,
 * preparing and processing states. Anything unmapped — including a store's own
 * custom statuses until D11 links them — lands in UNCLASSIFIED and is shown as
 * such rather than silently dropped.
 */
final class ConfirmationStatsGroups
{
    public const CONFIRMED = 'confirmed';

    public const PENDING = 'pending';

    public const FAILED = 'failed';

    public const CANCELED = 'canceled';

    public const DEFERRED = 'deferred';

    public const DUPLICATE = 'duplicate';

    public const DELIVERY_OUTCOMES = 'delivery_outcomes';

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

        'shipped' => self::DELIVERY_OUTCOMES,
        'in_transit' => self::DELIVERY_OUTCOMES,
        'out_for_delivery' => self::DELIVERY_OUTCOMES,
        'completed' => self::DELIVERY_OUTCOMES,
        'delivered' => self::DELIVERY_OUTCOMES,
        'returned' => self::DELIVERY_OUTCOMES,
        'refunded' => self::DELIVERY_OUTCOMES,
        'on_hold' => self::DELIVERY_OUTCOMES,
        'undeliverable' => self::DELIVERY_OUTCOMES,
        'unclaimed' => self::DELIVERY_OUTCOMES,
        'paid' => self::DELIVERY_OUTCOMES,
        'draft' => self::DELIVERY_OUTCOMES,
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
            self::DELIVERY_OUTCOMES,
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
