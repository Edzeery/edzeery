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
             | ORDER STATUSES
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
                'key'  => 'paid',
                'label' => 'Paid',
                'color' => 'success',
                'is_system' => true,
                'affects_inventory' => true,
                'movement_type' => InventoryMovementType::SALE->value,
                'sort_order' => 3,
            ],

            [
                'type' => 'order',
                'key'  => 'cancelled',
                'label' => 'Cancelled',
                'color' => 'danger',
                'is_system' => true,
                'affects_inventory' => true,
                'movement_type' => InventoryMovementType::RELEASE->value,
                'sort_order' => 4,
            ],

            [
                'type' => 'order',
                'key'  => 'completed',
                'label' => 'Completed',
                'color' => 'success',
                'is_system' => true,
                'affects_inventory' => false,
                'movement_type' => null,
                'sort_order' => 5,
            ],

            [
                'type' => 'order',
                'key'  => 'refunded',
                'label' => 'Refunded',
                'color' => 'warning',
                'is_system' => true,
                'affects_inventory' => true,
                'movement_type' => InventoryMovementType::RETURN->value,
                'sort_order' => 6,
            ],
        ];

        foreach ($statuses as $status) {
            Status::firstOrCreate(
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
