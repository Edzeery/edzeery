<?php

namespace App\Observers;

use App\Models\Orders\Order;
use App\Models\Orders\OrderStatusHistory;
use App\Models\Status;
use App\Services\InventoryService;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class OrderObserver
{
    /**
     * Before creating order
     */
    public function creating(Order $order): void
    {
        if (! $order->status_id) {
            $status = Status::system()
                ->forType('order')
                ->where('key', 'pending')
                ->first();

            $order->status_id = $status?->id;
        }

        if (! $order->number) {
            $order->number = $this->generateOrderNumber($order);
        }
    }

    /**
     * After updating order
     */
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status_id')) {
            return;
        }

        $this->handleStatusChange($order);
    }

    protected function handleStatusChange(Order $order): void
    {
        $status = $order->status;

        if (! $status) {
            return;
        }

        $meta = Order::popTransitionMeta($order->id) ?? [];

        OrderStatusHistory::create([
            'order_id'                 => $order->id,
            'status_id'                => $status->id,
            'changed_by_membership_id' => $meta['changed_by_membership_id'] ?? null,
            'reason'                   => $meta['reason'] ?? null,
        ]);

        if (! $status->affects_inventory) {
            return;
        }

        DB::transaction(function () use ($order, $status) {
            foreach ($order->items as $item) {
                $variant = $item->variant;

                if (! $variant) {
                    continue;
                }

                InventoryService::apply(
                    variant: $variant,
                    quantity: $item->quantity,
                    type: $status->movement_type,
                    source: $order,
                    user: $order->user
                );
            }
        });
    }

    protected function generateOrderNumber(Order $order): string
    {
        $lastNumber = Order::withTrashed()
            ->where('store_id', $order->store_id)
            ->max('number');

        $nextNumber = $lastNumber ? (int) $lastNumber + 1 : 1;

        return str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
    }
}
