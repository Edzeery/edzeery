<?php

namespace App\Support\Platform;

use App\Enums\Platform\PlatformPermissionEnum;
use App\Enums\Platform\UserRoleEnum;

class PlatformRoles
{
    public static function permissions(UserRoleEnum $role): array
    {
        return match ($role) {

            UserRoleEnum::SUPER_ADMIN =>
                PlatformPermissionEnum::values(),

            UserRoleEnum::ADMIN => [
                PlatformPermissionEnum::USERS_VIEW->value,
                PlatformPermissionEnum::STORES_VIEW->value,
                PlatformPermissionEnum::STORES_APPROVE->value,
                PlatformPermissionEnum::ORDERS_VIEW->value,
                PlatformPermissionEnum::ORDERS_ASSIGN->value,
                PlatformPermissionEnum::REPORTS_VIEW->value,
            ],

            UserRoleEnum::SUPPORT_AGENT => [
                PlatformPermissionEnum::USERS_VIEW->value,
                PlatformPermissionEnum::ORDERS_VIEW->value,
            ],

            UserRoleEnum::TECH_SUPPORT => [
                PlatformPermissionEnum::SETTINGS_MANAGE->value,
            ],

            default => [],
        };
    }
}
