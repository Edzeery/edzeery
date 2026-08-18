<?php

namespace App\Domains\Order\Services;

use App\Models\Orders\Order;
use App\Models\Orders\OrderStatusHistory;
use App\Models\Status;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Transition an order to a new status and record the history.
     */
    public function transition(
        Order $order,
        string $newStatusKey,
        ?string $reason = null,
        ?StoreMembership $changedBy = null,
    ): Order {
        $newStatus = Status::system()
            ->forType('order')
            ->where('key', $newStatusKey)
            ->firstOrFail();

        DB::beginTransaction();

        try {
            $order->update(['status_id' => $newStatus->id]);

            OrderStatusHistory::create([
                'order_id'                 => $order->id,
                'status_id'                => $newStatus->id,
                'changed_by_membership_id' => $changedBy?->id,
                'reason'                   => $reason,
            ]);

            DB::commit();

            return $order->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Confirm an order (pending → confirmed).
     */
    public function confirm(Order $order, ?StoreMembership $changedBy = null): Order
    {
        return $this->transition($order, 'confirmed', null, $changedBy);
    }

    /**
     * Start preparing (confirmed → preparing).
     */
    public function startPreparing(Order $order, ?StoreMembership $changedBy = null): Order
    {
        return $this->transition($order, 'preparing', null, $changedBy);
    }

    /**
     * Ship (preparing → shipped).
     */
    public function ship(Order $order, ?string $reason = null, ?StoreMembership $changedBy = null): Order
    {
        return $this->transition($order, 'shipped', $reason, $changedBy);
    }

    /**
     * Mark delivered (shipped → delivered).
     */
    public function deliver(Order $order, ?StoreMembership $changedBy = null): Order
    {
        return $this->transition($order, 'delivered', null, $changedBy);
    }

    /**
     * Cancel an order.
     */
    public function cancel(Order $order, ?string $reason = null, ?StoreMembership $changedBy = null): Order
    {
        return $this->transition($order, 'cancelled', $reason, $changedBy);
    }

    /**
     * Get available transitions for an order.
     */
    public function availableTransitions(Order $order): array
    {
        $currentKey = $order->status?->key;

        return match ($currentKey) {
            'pending'   => ['confirmed', 'cancelled'],
            'confirmed' => ['preparing', 'cancelled'],
            'preparing' => ['shipped', 'cancelled'],
            'shipped'   => ['delivered', 'returned'],
            default     => [],
        };
    }
}
