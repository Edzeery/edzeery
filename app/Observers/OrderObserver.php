<?php

namespace App\Observers;

use App\Models\Orders\Order;
use App\Models\Orders\OrderStatusHistory;
use App\Models\Status;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;

class OrderObserver
{
    /**
     * قبل إنشاء الطلب
     */
    public function creating(Order $order): void
    {
        // إذا لم يتم تحديد status يدويًا
        if (! $order->status_id) {
            $status = Status::system()
                ->forType('order')
                ->where('key', 'pending')
                ->firstOrFail();

            $order->status_id = $status->id;
        }

        // توليد رقم الطلب إن لم يوجد
        if (! $order->number) {
            $order->number = $this->generateOrderNumber($order);
        }
    }

    /**
     * بعد تحديث الطلب
     */
    public function updated(Order $order): void
    {
        // نهتم فقط بتغير الحالة
        if (! $order->wasChanged('status_id')) {
            return;
        }

        $this->handleStatusChange($order);
    }

    /* =========================
     | Logic
     ========================= */

    protected function handleStatusChange(Order $order): void
    {
        $status = $order->status; // eager loaded or fresh

        if (! $status) {
            return;
        }

        // Record status history
        OrderStatusHistory::create([
            'order_id'  => $order->id,
            'status_id' => $status->id,
            'reason'    => null,
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
        return 'ORD-' . now()->format('Ymd') . '-' . str_pad(
            (string) random_int(1, 9999),
            4,
            '0',
            STR_PAD_LEFT
        );
    }
}
