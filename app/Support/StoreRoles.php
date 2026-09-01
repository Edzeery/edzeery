<?php

namespace App\Support;

use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;

class StoreRoles
{
    public static function permissions(StoreRoleEnum $role): array
    {
        return match ($role) {

            /*
            |--------------------------------------------------------------------------
            | OWNER – كل شيء
            |--------------------------------------------------------------------------
            */
            StoreRoleEnum::OWNER => StorePermissionEnum::values(),

            /*
            |--------------------------------------------------------------------------
            | ADMIN – كل شيء ما عدا السيادة
            |--------------------------------------------------------------------------
            */
            StoreRoleEnum::ADMIN => collect(StorePermissionEnum::values())
                ->filter(fn(string $perm) => !in_array($perm, [
                    StorePermissionEnum::STORE_DELETE_FINAL->value,
                    StorePermissionEnum::STORE_TRANSFER_OWNERSHIP->value,
                    StorePermissionEnum::STORE_BILLING_MANAGE->value,
                    StorePermissionEnum::STORE_SETTINGS_SENSITIVE->value,
                ]))
                ->values()
                ->toArray(),

            /*
            |--------------------------------------------------------------------------
            | MANAGER – إدارة + فريقه فقط
            |--------------------------------------------------------------------------
            */
            StoreRoleEnum::MANAGER => [
                // Store
                StorePermissionEnum::STORE_VIEW->value,
                StorePermissionEnum::STORE_UPDATE->value,

                // Products
                StorePermissionEnum::PRODUCT_VIEW->value,
                StorePermissionEnum::PRODUCT_CREATE->value,
                StorePermissionEnum::PRODUCT_UPDATE->value,
                StorePermissionEnum::PRODUCT_DELETE->value,

                // Orders
                StorePermissionEnum::ORDER_VIEW->value,
                StorePermissionEnum::ORDER_MANAGE->value,
                StorePermissionEnum::ORDER_CONFIRM->value,
                StorePermissionEnum::ORDER_CANCEL->value,
                StorePermissionEnum::ORDER_ASSIGN->value,

                // Inventory
                StorePermissionEnum::INVENTORY_VIEW->value,
                StorePermissionEnum::INVENTORY_UPDATE->value,

                // Team (Scoped)
                StorePermissionEnum::TEAM_VIEW_OWN->value,
                StorePermissionEnum::TEAM_MANAGE_OWN->value,

                // CRM
                StorePermissionEnum::CRM_ORDER_TRACKING->value,
                StorePermissionEnum::CRM_ORDER_CONFIRMATION->value,
                StorePermissionEnum::CRM_INVENTORY_TRACKING->value,
                StorePermissionEnum::CRM_INVENTORY_MANAGE->value,

                // Returns
                StorePermissionEnum::RETURNS_VERIFY_BARCODE->value,
                StorePermissionEnum::RETURNS_PROCESS->value,

                // Delivery & Accounting
                StorePermissionEnum::DELIVERY_PRICING_MANAGE->value,

                // Finance / Debts
                StorePermissionEnum::FINANCE_DEBT_VIEW->value,
                StorePermissionEnum::FINANCE_DEBT_CREATE->value,
                StorePermissionEnum::FINANCE_DEBT_UPDATE->value,

                // Analytics
                StorePermissionEnum::STATS_TEAM_VIEW->value,
                StorePermissionEnum::STATS_CONFIRMATION->value,
                StorePermissionEnum::STATS_DELIVERY->value,
            ],

            /*
            |--------------------------------------------------------------------------
            | STAFF – تأكيد + تتبع فقط (دون إدارة كاملة)
            |--------------------------------------------------------------------------
            */
            StoreRoleEnum::STAFF => [
                // Store
                StorePermissionEnum::STORE_VIEW->value,

                // Products
                StorePermissionEnum::PRODUCT_VIEW->value,

                // Orders (تأكيد/إلغاء فقط، بلا إدارة)
                StorePermissionEnum::ORDER_VIEW->value,
                StorePermissionEnum::ORDER_CONFIRM->value,
                StorePermissionEnum::ORDER_CANCEL->value,

                // CRM / Operations
                StorePermissionEnum::CRM_ORDER_CONFIRMATION->value,
                StorePermissionEnum::CRM_ORDER_TRACKING->value,

                // Returns
                StorePermissionEnum::RETURNS_VERIFY_BARCODE->value,

                // Inventory
                StorePermissionEnum::INVENTORY_VIEW->value,

                // Analytics
                StorePermissionEnum::STATS_CONFIRMATION->value,
            ],
        };
    }
}
