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
     * Transition an order to a new status by key and record history.
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

        return $this->transitionToStatus($order, $newStatus, $reason, $changedBy);
    }

    /**
     * Transition an order to a status by status_id (supports store-custom statuses).
     */
    public function transitionToStatus(
        Order $order,
        Status $newStatus,
        ?string $reason = null,
        ?StoreMembership $changedBy = null,
    ): Order {
        if (! $this->canTransition($order, $newStatus->key)) {
            throw new \DomainException(
                "Cannot transition order from [{$order->status?->key}] to [{$newStatus->key}]"
            );
        }

        DB::beginTransaction();

        try {
            Order::setTransitionMeta($order->id, $changedBy?->id, $reason);

            $order->update(['status_id' => $newStatus->id]);

            // Inventory is now handled entirely by OrderObserver::handleStatusChange()
            // via each status's movement_type (RESERVE at confirm, SALE at deliver,
            // RELEASE/RETURN at cancel/return). Idempotent per-movement check prevents
            // double-application.

            Order::popTransitionMeta($order->id);

            DB::commit();

            return $order->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function availableTransitions(Order $order): array
    {
        // Use the relationship (lazy-loads if not eager-loaded).
        // This eliminates the N+1 when loadOrders() eager-loads 'status'.
        $currentKey = $order->status?->key;

        $systemTransitions = match ($currentKey) {
            'draft'              => ['pending', 'cancelled'],
            'pending'            => ['confirmed', 'cancelled', 'no_answer_1', 'wrong_number', 'out_of_stock', 'duplicate'],
            'confirmed'          => ['preparing', 'cancelled', 'on_hold'],
            'no_answer_1'        => ['pending', 'no_answer_2', 'cancelled'],
            'no_answer_2'        => ['pending', 'no_answer_3', 'cancelled'],
            'no_answer_3'        => ['cancelled'],
            'postponed'          => ['pending', 'cancelled'],
            'wrong_number'       => ['cancelled'],
            'out_of_stock'       => ['cancelled', 'pending'],
            'duplicate'          => ['cancelled'],
            'on_hold'            => ['confirmed', 'preparing', 'cancelled'],
            'preparing'          => ['shipped', 'cancelled'],
            'shipped'            => ['in_transit', 'out_for_delivery', 'delivered', 'returned'],
            'in_transit'         => ['out_for_delivery', 'delivered', 'returned'],
            'out_for_delivery'   => ['delivered', 'returned'],
            'delivered'          => ['returned', 'completed'],
            'completed'          => [],
            'returned'           => ['refunded', 'cancelled', 'pending'],
            'refunded'           => [],
            'cancelled'          => ['pending'],
            'canceled'           => ['pending'],
            default              => [],
        };

        return $systemTransitions;
    }

    /**
     * Check if a transition to the given status key is allowed.
     */
    public function canTransition(Order $order, string $statusKey): bool
    {
        return in_array($statusKey, $this->availableTransitions($order));
    }

    public function confirm(Order $order, ?StoreMembership $changedBy = null): Order
    {
        return $this->transition($order, 'confirmed', null, $changedBy);
    }

    public function startPreparing(Order $order, ?StoreMembership $changedBy = null): Order
    {
        return $this->transition($order, 'preparing', null, $changedBy);
    }

    public function ship(Order $order, ?string $reason = null, ?StoreMembership $changedBy = null): Order
    {
        return $this->transition($order, 'shipped', $reason, $changedBy);
    }

    public function deliver(Order $order, ?StoreMembership $changedBy = null): Order
    {
        return $this->transition($order, 'delivered', null, $changedBy);
    }

    public function cancel(Order $order, ?string $reason = null, ?StoreMembership $changedBy = null): Order
    {
        return $this->transition($order, 'cancelled', $reason, $changedBy);
    }

    /**
     * Create a manual order from merchant panel data.
     */
    public function createManual(array $data, StoreMembership $createdBy): Order
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $storeId = $createdBy->store_id;

            $status = Status::system()
                ->forType('order')
                ->where('key', 'pending')
                ->first();

            $order = Order::create([
                'store_id' => $storeId,
                'user_id' => $createdBy->user_id,
                'customer_id' => $data['customer_id'] ?? null,
                'status_id' => $status?->id,
                'number' => (new Order(['store_id' => $storeId]))->nextOrderNumber(),
                'total_amount' => $data['total_amount'] ?? 0,
                'state_id' => $data['state_id'] ?? null,
                'city_id' => $data['city_id'] ?? null,
                'address' => $data['address'] ?? null,
                'delivery_type' => $data['delivery_type'] ?? 'home',
                'payment_method' => $data['payment_method'] ?? 'cod',
                'shipping_cost' => $data['shipping_cost'] ?? 0,
                'notes' => $data['notes'] ?? null,
                'phone_secondary' => $data['phone_secondary'] ?? null,
                'weight_kg' => $data['weight_kg'] ?? null,
                'shipment_type' => $data['shipment_type'] ?? 'delivery',
                'created_by_membership_id' => $createdBy->id,
            ]);

            if ($status) {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status_id' => $status->id,
                    'changed_by_membership_id' => $createdBy->id,
                    'reason' => 'Order created manually',
                ]);
            }

            if (! empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $order->items()->create([
                        'store_id' => $storeId,
                        'product_variant_id' => $item['product_variant_id'],
                        'product_id' => $item['product_id'] ?? null,
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $item['quantity'] * $item['price'],
                    ]);
                }
            }

            return $order;
        });
    }

    /**
     * Update an existing order's attributes.
     */
    public function updateOrder(Order $order, array $data): Order
    {
        $order->update($data);

        return $order;
    }
}
