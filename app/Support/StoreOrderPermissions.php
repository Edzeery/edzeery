<?php

namespace App\Support;

use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Orders\Order;
use App\Models\Stores\Team\StoreMembership;
use App\Services\Stores\StoreStatusService;

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

    /**
     * الصلاحية المطلوبة للانتقال إلى حالة. اختياريًا تُمعِن الصلبة حاليًا: الحالة
     * المخصصة المرتبطة بحالة تأكيد أصلية (linked_to) تُعامل كأصلها.
     */
    public static function forStatus(string $statusKey, ?string $storeId = null): string
    {
        if ($storeId !== null) {
            $statusKey = app(StoreStatusService::class)->canonicalKey($storeId, $statusKey);
        }

        if (in_array($statusKey, self::CONFIRM_STATUSES, true)) {
            return StorePermissionEnum::ORDER_CONFIRM->value;
        }

        if (in_array($statusKey, self::CANCEL_STATUSES, true)) {
            return StorePermissionEnum::ORDER_CANCEL->value;
        }

        return StorePermissionEnum::ORDER_MANAGE->value;
    }

    /**
     * Phase 36.12.1 — the single gate for moving an order to a new status.
     *
     * Wraps forStatus() without altering it. The first branch is the historical
     * behaviour, byte for byte, so every existing holder of order.confirm /
     * order.cancel / order.manage is unaffected.
     *
     * The second branch is order.status.manage.own, which unlocks a member who
     * has neither order.manage nor the fine-grained confirm/cancel grants — the
     * store-rep case from §1.4 — but only for orders inside their own
     * visibleTo() scope. Per D1 it covers every status key, not just the
     * forStatus() fallback at :61, so a single narrow grant manages the whole
     * lifecycle of the member's own orders: confirming, call outcomes
     * (no_answer_1, postponed, …) and the shipping statuses alike.
     *
     * The $membership !== null guard is load-bearing: scopeVisibleTo() treats a
     * null membership as "no scoping at all", so without it a null membership
     * would let this branch match an arbitrary order in the store.
     */
    public static function canTransitionStatus(string $orderId, string $statusKey, ?StoreMembership $membership, ?string $storeId = null): bool
    {
        $storeId ??= (string) currentStoreId();

        if (canStore(self::forStatus($statusKey, $storeId))) {
            return true;
        }

        if (! canStore(StorePermissionEnum::ORDER_STATUS_MANAGE_OWN->value)) {
            return false;
        }

        if ($membership === null) {
            return false;
        }

        return Order::where('store_id', $storeId)
            ->whereKey($orderId)
            ->visibleTo($membership)
            ->exists();
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

    /**
     * Whether a membership is "restricted" for data-scoping purposes —
     * everyone except OWNER / ADMIN.  Mirrors the unrestricted boundary
     * used by canViewOrderEventLog (the same source of truth for who
     * sees full-store data vs. only their own).
     */
    public static function isRestrictedMembership(StoreMembership $membership): bool
    {
        return ! $membership->isOwner() && ! $membership->isAdmin();
    }
}
