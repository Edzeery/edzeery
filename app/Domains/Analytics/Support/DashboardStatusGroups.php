<?php

namespace App\Domains\Analytics\Support;

use App\Enums\Store\OrderStatus;

/**
 * PHASE 37-K — the status groups every dashboard block reads, defined once.
 *
 * The confirmed list used to be written out twice (summary KPI and team
 * table) and had already drifted apart, so a store could show a confirmation
 * rate the table could not reproduce. Nothing outside this file may spell a
 * list of order statuses: queries ask for a group and resolve it through
 * OrderStatusIdMap, which skips the keys the store has not defined.
 *
 * These groups exist for dashboard KPIs only. Payroll must never reuse the
 * `confirmed` group (see the master plan, compensation model).
 */
final class DashboardStatusGroups
{
    /** Still waiting on the operator — the Confirmation view leads with it. */
    public const PENDING = [OrderStatus::PENDING];

    /**
     * The order made it past the confirmation desk. Shipped, delivered and
     * failed states count as confirmed on purpose (D2): they were confirmed
     * at some point, and excluding them made the rate collapse on a store
     * that ships faster than it marks rows.
     */
    public const CONFIRMED = [
        OrderStatus::CONFIRMED,
        OrderStatus::PREPARING,
        OrderStatus::PROCESSING,
        OrderStatus::SHIPPED,
        OrderStatus::IN_TRANSIT,
        OrderStatus::OUT_FOR_DELIVERY,
        OrderStatus::DELIVERED,
        OrderStatus::COMPLETED,
        OrderStatus::RETURNED,
        OrderStatus::UNDELIVERABLE,
        OrderStatus::UNCLAIMED,
        OrderStatus::REFUNDED,
    ];

    /** Stores configure the cancelled state under either spelling. */
    public const CANCELED = [OrderStatus::CANCELED, OrderStatus::CANCELLED];

    public const DELIVERED = [OrderStatus::DELIVERED];

    public const RETURNED = [OrderStatus::RETURNED];

    /** An order that reached the courier: the Delivery view's pipeline. */
    public const DELIVERY_FLOW = [
        OrderStatus::SHIPPED,
        OrderStatus::IN_TRANSIT,
        OrderStatus::OUT_FOR_DELIVERY,
        OrderStatus::DELIVERED,
        OrderStatus::COMPLETED,
        OrderStatus::RETURNED,
        OrderStatus::UNDELIVERABLE,
        OrderStatus::UNCLAIMED,
    ];

    /**
     * The `statuses.key` values of a group, which is what a GROUP BY reads.
     *
     * @param  array<int, OrderStatus>  $statuses
     * @return array<int, string>
     */
    public static function keys(array $statuses): array
    {
        return array_values(array_map(fn (OrderStatus $status) => $status->value, $statuses));
    }

    /**
     * Keys of the confirmed group: the doughnut collapses exactly these into
     * one slice, so the chart, the KPI and the table agree on the same total.
     *
     * @return array<int, string>
     */
    public static function confirmedKeys(): array
    {
        return self::keys(self::CONFIRMED);
    }

    public static function inConfirmed(string $key): bool
    {
        return in_array($key, self::confirmedKeys(), true);
    }
}
