<?php

declare(strict_types=1);

namespace App\Domains\Order\Support;

/**
 * PHASE 35.3 — order-status distribution stage resolver.
 *
 * `statuses.distribution_stage` classifies an order status by what the
 * distribution engine may do with the order's assignment:
 *
 *  - confirmation: still being worked. The confirmation load counts it as an
 *    open order and the shift-handover sweep may reassign it.
 *  - fulfillment: past confirmation, in the carrier pipeline. Never touched by
 *    the sweep.
 *  - closed: settled or dead-ended outcomes. Never touched by the sweep.
 *
 * Deliberately separate from `statuses.stage` (PHASE 38-C), which is the
 * compensation model's dashboard-KPI bucketing and forbids reuse as a second
 * domain list. Store-custom order statuses resolve to `confirmation` — they are
 * presumed to still need confirmation work until the merchant's workflow moves
 * them into the fulfillment pipeline.
 *
 * Pure function of a status key with no memo, so a long-running worker cannot
 * leak a resolved stage between stores or requests.
 */
final class OrderDistributionStage
{
    public const string CONFIRMATION = 'confirmation';

    public const string FULFILLMENT = 'fulfillment';

    public const string CLOSED = 'closed';

    /**
     * Still being confirmed: the handover sweep may reassign these and they
     * count as open orders in the confirmation load.
     *
     * @return string[]
     */
    public static function confirmationKeys(): array
    {
        return [
            'pending',
            'no_answer_1',
            'no_answer_2',
            'no_answer_3',
            'postponed',
            'on_hold',
        ];
    }

    /**
     * Past confirmation: never reassigned.
     *
     * @return string[]
     */
    public static function fulfillmentKeys(): array
    {
        return [
            'confirmed',
            'preparing',
            'shipped',
            'in_transit',
            'out_for_delivery',
            'unclaimed',
            'undeliverable',
        ];
    }

    /**
     * Settled or dead-ended: never reassigned. `paid` sits here too — a paid
     * order is financially closed even when it still ships.
     *
     * @return string[]
     */
    public static function closedKeys(): array
    {
        return [
            'draft',
            'wrong_number',
            'duplicate',
            'out_of_stock',
            'cancelled',
            'canceled',
            'delivered',
            'returned',
            'completed',
            'refunded',
            'paid',
        ];
    }

    /**
     * @return string[]
     */
    public static function all(): array
    {
        return [self::CONFIRMATION, self::FULFILLMENT, self::CLOSED];
    }

    /**
     * Stage of a status key. Unknown keys (store-custom statuses) default to
     * `confirmation`.
     */
    public static function forKey(string $statusKey): string
    {
        if (in_array($statusKey, self::confirmationKeys(), true)) {
            return self::CONFIRMATION;
        }

        if (in_array($statusKey, self::fulfillmentKeys(), true)) {
            return self::FULFILLMENT;
        }

        if (in_array($statusKey, self::closedKeys(), true)) {
            return self::CLOSED;
        }

        return self::CONFIRMATION;
    }
}
