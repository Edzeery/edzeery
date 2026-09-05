<?php

namespace App\Domains\Order\Support;

/**
 * مجموعات المراحل المعيارية للطلبيات.
 *
 * - BACK_OFFICE: في صفحة «الطلبيات» (قيد المعالجة في المكتب الخلفي).
 * - CARRIER: في صفحة «تتبع الطلبيات» فقط — تختفي من «الطلبيات».
 * - CLOSED: نتائج مغلقة تبقى في «الطلبيات» مع إمكانية الفلترة.
 */
class OrderWorkflow
{
    /** @return string[] */
    public static function backOffice(): array
    {
        return [
            'draft',
            'pending',
            'no_answer_1',
            'no_answer_2',
            'no_answer_3',
            'postponed',
            'wrong_number',
            'out_of_stock',
            'duplicate',
            'on_hold',
            'confirmed',
            'preparing',
            'unclaimed',
            'undeliverable',
        ];
    }

    /** @return string[] */
    public static function carrier(): array
    {
        return [
            'shipped',
            'in_transit',
            'out_for_delivery',
            'delivered',
            'returned',
        ];
    }

    /** @return string[] */
    public static function closed(): array
    {
        return [
            'cancelled',
            'canceled',
            'completed',
            'refunded',
        ];
    }

    public static function isCarrier(string $statusKey): bool
    {
        return in_array($statusKey, self::carrier(), true);
    }
}