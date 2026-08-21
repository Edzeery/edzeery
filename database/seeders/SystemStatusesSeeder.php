<?php

namespace Database\Seeders;

use App\Models\Status;
use App\Enums\Store\InventoryMovementType;
use Illuminate\Database\Seeder;

class SystemStatusesSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [

            /* =========================
             | ORDER STATUSES — Confirmation Pipeline
             ========================= */

            [
                'type' => 'order',
                'key'  => 'pending',
                'label' => 'Pending',
                'color' => 'gray',
                'is_system' => true,
                'affects_inventory' => false,
                'movement_type' => null,
                'sort_order' => 1,
            ],

            [
                'type' => 'order',
                'key'  => 'confirmed',
                'label' => 'Confirmed',
                'color' => 'info',
                'is_system' => true,
                'affects_inventory' => true,
                'movement_type' => InventoryMovementType::RESERVE->value,
                'sort_order' => 2,
            ],

            [
                'type' => 'order',
                'key'  => 'no_answer_1',
                'label' => 'No Answer (1st)',
                'color' => 'warning',
                'is_system' => true,
                'affects_inventory' => false,
                'movement_type' => null,
                'sort_order' => 3,
            ],

            [
                'type' => 'order',
                'key'  => 'no_answer_2',
                'label' => 'No Answer (2nd)',
                'color' => 'warning',
                'is_system' => true,
                'affects_inventory' => false,
                'movement_type' => null,
                'sort_order' => 4,
            ],

            [
                'type' => 'order',
                'key'  => 'no_answer_3',
                'label' => 'No Answer (3rd)',
                'color' => 'danger',
                'is_system' => true,
                'affects_inventory' => false,
                'movement_type' => null,
                'sort_order' => 5,
            ],

            [
                'type' => 'order',
                'key'  => 'postponed',
                'label' => 'Postponed',
                'color' => 'warning',
                'is_system' => true,
                'affects_inventory' => false,
                'movement_type' => null,
                'sort_order' => 6,
            ],

            [
                'type' => 'order',
                'key'  => 'wrong_number',
                'label' => 'Wrong Number',
                'color' => 'danger',
                'is_system' => true,
                'affects_inventory' => false,
                'movement_type' => null,
                'sort_order' => 7,
            ],

            [
                'type' => 'order',
                'key'  => 'out_of_stock',
                'label' => 'Out of Stock',
                'color' => 'danger',
                'is_system' => true,
                'affects_inventory' => false,
                'movement_type' => null,
                'sort_order' => 8,
            ],

            [
                'type' => 'order',
                'key'  => 'duplicate',
                'label' => 'Duplicate',
                'color' => 'warning',
                'is_system' => true,
                'affects_inventory' => false,
                'movement_type' => null,
                'sort_order' => 9,
            ],

            [
                'type' => 'order',
                'key'  => 'on_hold',
                'label' => 'On Hold',
                'color' => 'warning',
                'is_system' => true,
                'affects_inventory' => false,
                'movement_type' => null,
                'sort_order' => 10,
            ],

            /* =========================
             | ORDER STATUSES — Fulfillment Pipeline
             ========================= */

            [
                'type' => 'order',
                'key'  => 'preparing',
                'label' => 'Preparing',
                'color' => 'info',
                'is_system' => true,
                'affects_inventory' => false,
                'movement_type' => null,
                'sort_order' => 11,
            ],

            [
                'type' => 'order',
                'key'  => 'shipped',
                'label' => 'Shipped',
                'color' => 'info',
                'is_system' => true,
                'affects_inventory' => false,
                'movement_type' => null,
                'sort_order' => 12,
            ],

            [
                'type' => 'order',
                'key'  => 'in_transit',
                'label' => 'In Transit',
                'color' => 'info',
                'is_system' => true,
                'affects_inventory' => false,
                'movement_type' => null,
                'sort_order' => 13,
            ],

            [
                'type' => 'order',
                'key'  => 'out_for_delivery',
                'label' => 'Out for Delivery',
                'color' => 'info',
                'is_system' => true,
                'affects_inventory' => false,
                'movement_type' => null,
                'sort_order' => 14,
            ],

            [
                'type' => 'order',
                'key'  => 'delivered',
                'label' => 'Delivered',
                'color' => 'success',
                'is_system' => true,
                'affects_inventory' => true,
                'movement_type' => InventoryMovementType::SALE->value,
                'sort_order' => 15,
            ],

            [
                'type' => 'order',
                'key'  => 'returned',
                'label' => 'Returned',
                'color' => 'danger',
                'is_system' => true,
                'affects_inventory' => true,
                'movement_type' => InventoryMovementType::RETURN->value,
                'sort_order' => 16,
            ],

            [
                'type' => 'order',
                'key'  => 'cancelled',
                'label' => 'Cancelled',
                'color' => 'danger',
                'is_system' => true,
                'affects_inventory' => true,
                'movement_type' => InventoryMovementType::RELEASE->value,
                'sort_order' => 17,
            ],

            [
                'type' => 'order',
                'key'  => 'completed',
                'label' => 'Completed',
                'color' => 'success',
                'is_system' => true,
                'affects_inventory' => false,
                'movement_type' => null,
                'sort_order' => 18,
            ],

            [
                'type' => 'order',
                'key'  => 'paid',
                'label' => 'Paid',
                'color' => 'success',
                'is_system' => true,
                'affects_inventory' => true,
                'movement_type' => InventoryMovementType::SALE->value,
                'sort_order' => 19,
            ],

            [
                'type' => 'order',
                'key'  => 'refunded',
                'label' => 'Refunded',
                'color' => 'warning',
                'is_system' => true,
                'affects_inventory' => true,
                'movement_type' => InventoryMovementType::RETURN->value,
                'sort_order' => 20,
            ],

            /* =========================
             | ORDER STATUSES — Alias (canceled → cancelled)
             ========================= */

            [
                'type' => 'order',
                'key'  => 'canceled',
                'label' => 'Canceled',
                'color' => 'danger',
                'is_system' => true,
                'affects_inventory' => true,
                'movement_type' => InventoryMovementType::RELEASE->value,
                'sort_order' => 21,
            ],

            [
                'type' => 'order',
                'key'  => 'draft',
                'label' => 'Draft',
                'color' => 'gray',
                'is_system' => true,
                'affects_inventory' => false,
                'movement_type' => null,
                'sort_order' => 0,
            ],
        ];

        foreach ($statuses as $status) {
            Status::updateOrCreate(
                [
                    'store_id' => null,
                    'type' => $status['type'],
                    'key'  => $status['key'],
                ],
                $status
            );
        }
    }
}
