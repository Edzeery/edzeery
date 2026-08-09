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
                ->except([
                    StorePermissionEnum::STORE_DELETE_FINAL->value,
                    StorePermissionEnum::STORE_TRANSFER_OWNERSHIP->value,
                    StorePermissionEnum::STORE_BILLING_MANAGE->value,
                    StorePermissionEnum::STORE_SETTINGS_SENSITIVE->value,
                ])
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

                // Orders
                StorePermissionEnum::ORDER_VIEW->value,
                StorePermissionEnum::ORDER_MANAGE->value,
                StorePermissionEnum::ORDER_CONFIRM->value,
                StorePermissionEnum::ORDER_CANCEL->value,

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

                // Analytics
                StorePermissionEnum::STATS_TEAM_VIEW->value,
            ],

            /*
            |--------------------------------------------------------------------------
            | STAFF – تنفيذ فقط
            |--------------------------------------------------------------------------
            */
            StoreRoleEnum::STAFF => [
                StorePermissionEnum::STORE_VIEW->value,

                StorePermissionEnum::PRODUCT_VIEW->value,

                StorePermissionEnum::ORDER_VIEW->value,
                StorePermissionEnum::ORDER_CONFIRM->value,

                StorePermissionEnum::INVENTORY_VIEW->value,

                StorePermissionEnum::CRM_ORDER_TRACKING->value,
            ],
        };
    }
}
