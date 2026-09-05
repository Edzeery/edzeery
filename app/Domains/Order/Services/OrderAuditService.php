<?php

namespace App\Domains\Order\Services;

use App\Models\Orders\Order;
use App\Models\Orders\OrderEvent;
use App\Models\Stores\Team\StoreMembership;

/**
 * سجل أحداث الطلبية (Order Event Log) — نقطة كتابة واحدة لكل حدث.
 * يكمّل OrderStatusHistory (مصدر الحالة) بالأحداث العامة (تعديل، اتصال، إرسال، تتبع...).
 */
class OrderAuditService
{
    public function log(
        Order $order,
        string $eventType,
        ?string $message = null,
        ?array $payload = [],
        ?StoreMembership $actor = null,
        string $actorType = OrderEvent::ACTOR_MEMBERSHIP,
    ): OrderEvent {
        return OrderEvent::create([
            'store_id'             => $order->store_id,
            'order_id'             => $order->id,
            'actor_membership_id'  => $actor?->id,
            'actor_type'           => $actor ? OrderEvent::ACTOR_MEMBERSHIP : $actorType,
            'event_type'           => $eventType,
            'message'              => $message,
            'payload'              => $payload,
            'occurred_at'          => now(),
        ]);
    }

    public function created(Order $order, ?StoreMembership $actor = null): OrderEvent
    {
        return $this->log($order, 'created', __(
            'order_flow.event_created',
            ['number' => $order->number],
        ), [
            'number' => $order->number,
            'total_amount' => $order->total_amount,
        ], $actor ?? $this->membershipFromOrder($order));
    }

    public function statusChanged(
        Order $order,
        ?string $fromKey,
        string $toKey,
        ?string $reason = null,
        ?StoreMembership $actor = null,
    ): OrderEvent {
        return $this->log($order, 'status', __(
            'order_flow.event_status',
            ['to' => status_label('order', $toKey) ?: $toKey],
        ), [
            'from' => $fromKey,
            'to' => $toKey,
            'reason' => $reason,
        ], $actor);
    }

    public function fieldChanges(Order $order, array $fields, ?StoreMembership $actor = null): ?OrderEvent
    {
        if (empty($fields)) {
            return null;
        }

        return $this->log($order, 'field_changed', __('order_flow.event_fields_changed'), $fields, $actor);
    }

    public function contactAttempt(Order $order, string $outcome, ?StoreMembership $actor = null): OrderEvent
    {
        return $this->log($order, 'contact', __(
            'order_flow.event_contact',
            ['outcome' => $outcome],
        ), ['outcome' => $outcome], $actor);
    }

    public function sentToCarrier(Order $order, ?string $providerName, ?string $trackingNumber, ?StoreMembership $actor = null): OrderEvent
    {
        return $this->log($order, 'sent_to_carrier', __('order_flow.event_sent_to_carrier'), [
            'provider' => $providerName,
            'tracking_number' => $trackingNumber,
        ], $actor);
    }

    public function tracking(Order $order, string $statusKey, ?string $trackingNumber = null, ?StoreMembership $actor = null): OrderEvent
    {
        return $this->log($order, 'tracking', __(
            'order_flow.event_tracking',
            ['status' => status_label('tracking', $statusKey) ?: $statusKey],
        ), [
            'tracking_status' => $statusKey,
            'tracking_number' => $trackingNumber,
        ], $actor);
    }

    public function reassigned(Order $order, ?string $toMembershipName, ?StoreMembership $actor = null): OrderEvent
    {
        return $this->log($order, 'reassigned', __('order_flow.event_reassigned'), [
            'to' => $toMembershipName,
        ], $actor);
    }

    public function note(Order $order, string $message, ?StoreMembership $actor = null): OrderEvent
    {
        return $this->log($order, 'note', $message, [], $actor);
    }

    /** عند الإنشاء اليدوي: الفاعل هو عضو الفريق الذي خلق الطلبية (إن وُجد). */
    protected function membershipFromOrder(Order $order): ?StoreMembership
    {
        if (! $order->created_by_membership_id) {
            return null;
        }

        return StoreMembership::find($order->created_by_membership_id);
    }
}