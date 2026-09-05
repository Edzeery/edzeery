<?php

namespace App\Support;

use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Orders\Order;
use App\Models\Stores\Team\StoreMembership;

/**
 * Maps an order status key to the fine-grained permission required to move an
 * order to it. Used to gate $transitionOrder (Phase P1) so a member with only
 * confirm/cancel access cannot drive shipping/delivery/follow-up statuses.
 */
class StoreOrderPermissions
{
    /**
     * Statuses belonging to the "confirmation" phase (staff confirm workflow).
     */
    public const CONFIRM_STATUSES = [
        'confirmed',
        'preparing',
        'on_hold',
    ];

    /**
     * Statuses belonging to the "cancellation" phase (staff cancel workflow).
     */
    public const CANCEL_STATUSES = [
        'cancelled',
        'canceled',
        'rejected',
        'no_answer_1',
        'no_answer_2',
        'no_answer_3',
        'wrong_number',
        'out_of_stock',
        'duplicate',
        'postponed',
    ];

    public static function forStatus(string $statusKey): string
    {
        if (in_array($statusKey, self::CONFIRM_STATUSES, true)) {
            return StorePermissionEnum::ORDER_CONFIRM->value;
        }

        if (in_array($statusKey, self::CANCEL_STATUSES, true)) {
            return StorePermissionEnum::ORDER_CANCEL->value;
        }

        return StorePermissionEnum::ORDER_MANAGE->value;
    }

    /**
     * P29.1 — Who may inspect the order event log (audit timeline)?
     *
     * - OWNER / ADMIN  → always
     * - MANAGER        → only orders assigned to their own membership
     * - STAFF / any    → never (hidden, not loaded)
     */
    public static function canViewOrderEventLog(Order $order, StoreMembership $membership): bool
    {
        if ($membership->isOwner() || $membership->isAdmin()) {
            return true;
        }

        if ($membership->isManager()) {
            return filled($order->assigned_to_membership_id)
                && $order->assigned_to_membership_id === $membership->id;
        }

        return false;
    }
}
