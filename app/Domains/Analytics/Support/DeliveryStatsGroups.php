<?php

namespace App\Domains\Analytics\Support;

use App\Enums\Store\OrderStatus;

/**
 * PHASE 37-N7 (D2/D3) — the two settled outcomes the delivery rate divides, and
 * (PHASE 37-N8) the wider buckets the status breakdown draws.
 *
 * Unlike the confirmation tab, delivery reads the order's CURRENT status, not
 * an "ever happened" flag: the order lifecycle only ever ends in DELIVERED or
 * RETURNED, and a store may keep any other status key out of its sums.
 *
 * The rate's two terms stay strict: only the `delivered` and `returned` status
 * keys move the line (so the breakdown's two leading cards reconcile with the
 * rate exactly). Every other status falls into a display bucket that never
 * enters the ratio numerator.
 */
final class DeliveryStatsGroups
{
    public const DELIVERED = 'delivered';

    public const RETURNED = 'returned';

    public const IN_PROGRESS = 'in_progress';

    public const PREPARING = 'preparing';

    public const CANCELED = 'canceled';

    public const OTHER = 'other';

    /**
     * The status key → bucket map. Only DELIVERED and RETURNED hold a single
     * status each (they are the rate's terms); the rest group the fulfilment
     * pipeline. Any key missing from the map (a store's custom status) reads as
     * OTHER.
     *
     * @var array<string, string>
     */
    private const MAP = [
        'delivered' => self::DELIVERED,

        'returned' => self::RETURNED,

        'shipped' => self::IN_PROGRESS,
        'in_transit' => self::IN_PROGRESS,
        'out_for_delivery' => self::IN_PROGRESS,

        'confirmed' => self::PREPARING,
        'preparing' => self::PREPARING,
        'processing' => self::PREPARING,

        'canceled' => self::CANCELED,
        'cancelled' => self::CANCELED,
        'wrong_number' => self::CANCELED,
        'undeliverable' => self::CANCELED,
        'unclaimed' => self::CANCELED,
    ];

    /**
     * The buckets the status breakdown draws, in display order: the two the
     * rate measures first, then the fulfilment pipeline.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::DELIVERED,
            self::RETURNED,
            self::IN_PROGRESS,
            self::PREPARING,
            self::CANCELED,
            self::OTHER,
        ];
    }

    public static function group(string $statusKey): string
    {
        return self::MAP[$statusKey] ?? self::OTHER;
    }

    /**
     * The status keys that read as the given bucket. OTHER has no mapped keys
     * (it absorbs the unmapped ones), so it answers an empty list.
     *
     * @return list<string>
     */
    public static function keys(string $group): array
    {
        return array_keys(array_filter(
            self::MAP,
            static fn (string $bucket): bool => $bucket === $group,
        ));
    }

    public static function isDelivered(string $statusKey): bool
    {
        return $statusKey === OrderStatus::DELIVERED->value;
    }

    public static function isReturned(string $statusKey): bool
    {
        return $statusKey === OrderStatus::RETURNED->value;
    }

    /**
     * Every order status is excludable except the two the rate measures, so a
     * store can drop drafts, cancelled, undeliverable… from both terms.
     *
     * @return list<string>
     */
    public static function excludableKeys(): array
    {
        $measured = [OrderStatus::DELIVERED->value, OrderStatus::RETURNED->value];

        return array_values(array_filter(
            array_map(static fn (OrderStatus $status): string => $status->value, OrderStatus::cases()),
            static fn (string $key): bool => ! in_array($key, $measured, true),
        ));
    }
}
