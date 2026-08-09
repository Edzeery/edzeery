<?php

namespace App\Enums\Platform;

enum PlatformPermissionEnum: string
{
    // Users
    case USERS_VIEW   = 'users.view';
    case USERS_CREATE = 'users.create';
    case USERS_UPDATE = 'users.update';
    case USERS_DELETE = 'users.delete';

        // Stores (Platform level)
    case STORES_VIEW    = 'stores.view';
    case STORES_APPROVE = 'stores.approve';

        // Orders (Global)
    case ORDERS_VIEW   = 'orders.view';
    case ORDERS_ASSIGN = 'orders.assign_delivery';

        // System
    case SETTINGS_MANAGE = 'settings.manage';
    case REPORTS_VIEW   = 'reports.view';

    public function group(): string
    {
        return explode('.', $this->value)[0];
    }

    public function label(): string
    {
        return str($this->value)->replace('.', ' ')->title();
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
