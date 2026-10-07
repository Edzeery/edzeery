<?php

declare(strict_types=1);

namespace App\Domains\Status\Support;

use App\Domains\Analytics\Support\DashboardStatusGroups;
use App\Models\Status;

/**
 * PHASE 38-C — order-status lifecycle stage resolver.
 *
 * `statuses.stage` classifies an order status into one of seven lifecycle
 * buckets (pending/confirmed/in_delivery/delivered/returned/canceled/other)
 * so the compensation model never hard-codes a second list of status keys.
 * System status defaults are derived from DashboardStatusGroups — the single
 * source of dashboard groupings (the confirmed group is a dashboard KPI
 * grouping and, per its own docblock, MUST NOT be reused as a payroll list;
 * this resolver only uses it as the bucket source, never as an earnings
 * trigger). Store-custom statuses resolve to `other` until the merchant maps
 * them in PHASE 38-B.
 *
 * Request-safe and store-scoped by construction: this is a pure function of a
 * status key with no static memo, so a long-running worker can never leak a
 * resolved stage between requests, stores or users.
 */
final class OrderStatusStage
{
    public const string PENDING = 'pending';

    public const string CONFIRMED = 'confirmed';

    public const string IN_DELIVERY = 'in_delivery';

    public const string DELIVERED = 'delivered';

    public const string RETURNED = 'returned';

    public const string CANCELED = 'canceled';

    public const string OTHER = 'other';

    /**
     * A.2 — confirmation-mode earnings: the statuses that evidence a confirmed
     * sale before any reversal rule applies. Deliberately a subset of
     * DashboardStatusGroups::CONFIRMED that excludes the non-selling outcomes
     * (returned / undeliverable / unclaimed / refunded are NOT earning
     * triggers; they are reversal/adjustment triggers owned by PHASE 38-D).
     *
     * @return string[]
     */
    public static function earningConfirmationKeys(): array
    {
        return [
            'confirmed',
            'preparing',
            'processing',
            'shipped',
            'in_transit',
            'out_for_delivery',
            'delivered',
            'completed',
        ];
    }

    public static function isEarningConfirmation(string $statusKey): bool
    {
        return in_array($statusKey, self::earningConfirmationKeys(), true);
    }

    /**
     * Stage of a status key. Precedence resolves the group overlaps: terminal
     * outcome buckets (canceled/returned/delivered) win over the carrier
     * pipeline bucket, which wins over the confirmation bucket, which wins
     * over the pending bucket; anything not listed in the dashboard groups
     * falls back to `other` (custom statuses, non-order keys).
     */
    public static function forKey(string $statusKey): string
    {
        if (in_array($statusKey, DashboardStatusGroups::keys(DashboardStatusGroups::CANCELED), true)) {
            return self::CANCELED;
        }

        if (in_array($statusKey, DashboardStatusGroups::keys(DashboardStatusGroups::RETURNED), true)) {
            return self::RETURNED;
        }

        if (in_array($statusKey, DashboardStatusGroups::keys(DashboardStatusGroups::DELIVERED), true)) {
            return self::DELIVERED;
        }

        if (in_array($statusKey, DashboardStatusGroups::keys(DashboardStatusGroups::DELIVERY_FLOW), true)) {
            return self::IN_DELIVERY;
        }

        if (in_array($statusKey, DashboardStatusGroups::keys(DashboardStatusGroups::PENDING), true)) {
            return self::PENDING;
        }

        if (in_array($statusKey, DashboardStatusGroups::keys(DashboardStatusGroups::CONFIRMED), true)) {
            return self::CONFIRMED;
        }

        return self::OTHER;
    }

    public static function forStatus(Status $status): string
    {
        return $status->type === 'order'
            ? self::forKey($status->key)
            : self::OTHER;
    }
}
