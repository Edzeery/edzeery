<?php

declare(strict_types=1);

namespace App\Domains\Order\Support;

use App\Models\Orders\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * PHASE 38-C — first-write-wins capture of the earning-relevant event stamps.
 *
 * Every stamp is applied with an atomic, compare-and-set UPDATE
 * (… WHERE <capture column> IS NULL) executed inside the SAME database
 * transaction as the status transition (see OrderObserver::handleStatusChange),
 * so:
 *
 *   - a re-transition (cancel → re-confirm, backwards moves, copy/retry flows,
 *     reassignment) can never rewrite a stamp;
 *   - two concurrent confirmations of the same order produce exactly one
 *     winner: the losing CAS updates 0 rows and the whole losing transition
 *     rolls back to the state it observed;
 *   - an absent actor leaves the actor column NULL ("unattributed"); the
 *     compensation layer must never silently credit an unattributed stamp.
 *
 * Tracking events are captured separately (OrderTrackingService::startShipment)
 * with the same first-write-wins property: exactly one shipping event per order
 * ever, because a tracking row is only created when no open tracking exists.
 *
 * FUTURE LEDGER (PHASE 38-D): the accounting key will be (store_id, order_id,
 * trigger) with NO membership included — the actor lives on the stamps above,
 * never in a ledger primary key.
 */
final class OrderStatusCapture
{
    /**
     * @param  Order|string  $order  Order (model or ULID).
     */
    private static function orderId(Order|string $order): string
    {
        return $order instanceof Order ? (string) $order->getKey() : (string) $order;
    }

    public static function stampConfirmation(Order|string $order, ?string $membershipId, Carbon|\DateTimeInterface $at): bool
    {
        $membershipId = blank($membershipId) ? null : (string) $membershipId;

        return DB::table('orders')
            ->where('id', self::orderId($order))
            ->whereNull('confirmed_at')
            ->update([
                'confirmed_at' => $at,
                'confirmed_by_membership_id' => $membershipId,
            ]) > 0;
    }

    public static function stampDelivered(Order|string $order, Carbon|\DateTimeInterface $at): bool
    {
        return DB::table('orders')
            ->where('id', self::orderId($order))
            ->whereNull('delivered_at')
            ->update(['delivered_at' => $at]) > 0;
    }

    /**
     * Carrier-sourced delivered evidence (first carrier event that maps to a
     * delivery). Manual order deliveries never write this column.
     */
    public static function stampDeliveryEvidence(Order|string $order, Carbon|\DateTimeInterface $at): bool
    {
        return DB::table('orders')
            ->where('id', self::orderId($order))
            ->whereNull('delivery_evidence_at')
            ->update(['delivery_evidence_at' => $at]) > 0;
    }

    /**
     * @param  string|null  $reasonKey  Key of the store's return-reason list
     *                                  (fault flag lives there, never copied
     *                                  here apart from the key reference).
     */
    public static function stampReturned(Order|string $order, Carbon|\DateTimeInterface $at, ?string $reasonKey = null): bool
    {
        $reasonKey = blank($reasonKey) ? null : (string) $reasonKey;

        return DB::table('orders')
            ->where('id', self::orderId($order))
            ->whereNull('returned_at')
            ->update(array_filter([
                'returned_at' => $at,
                'return_reason_key' => $reasonKey,
            ], static fn (mixed $value): bool => $value !== null)) > 0;
    }

    /**
     * Amount snapshot sent to the carrier (PHASE 38-C tracking capture).
     * Mirrors the carrier payload formula (NoestIntegrationAdapter::sendOrder
     * `montant`): goods subtotal + shipping cost − discount, floored at 0.
     * Null for non-COD orders — there is nothing to collect on delivery.
     */
    public static function codAmount(Order $order): ?string
    {
        if ((string) $order->payment_method !== 'cod') {
            return null;
        }

        $subtotal = (float) $order->items->sum(static fn ($item): float => (float) $item->subtotal);

        return (string) round(
            max(0.0, $subtotal + (float) $order->shipping_cost - (float) $order->discount_amount),
            2,
        );
    }
}
