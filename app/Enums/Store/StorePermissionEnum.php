<?php

namespace App\Enums\Store;

enum StorePermissionEnum: string
{
    /*
    |--------------------------------------------------------------------------
    | Store Core
    |--------------------------------------------------------------------------
    */
    case STORE_VIEW = 'store.view';
    case STORE_UPDATE = 'store.update';

    /*
    |--------------------------------------------------------------------------
    | Store Sovereignty (OWNER ONLY)
    |--------------------------------------------------------------------------
    */
    case STORE_DELETE_FINAL = 'store.delete.final';
    case STORE_TRANSFER_OWNERSHIP = 'store.transfer.ownership';
    case STORE_BILLING_MANAGE = 'store.billing.manage';
    case STORE_SETTINGS_SENSITIVE = 'store.settings.sensitive';

    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */
    case PRODUCT_VIEW = 'products.view';
    case PRODUCT_CREATE = 'products.create';
    case PRODUCT_UPDATE = 'products.update';
    case PRODUCT_DELETE = 'products.delete';

    /*
    |--------------------------------------------------------------------------
    | Orders
    |--------------------------------------------------------------------------
    */
    case ORDER_VIEW = 'order.view';
    case ORDER_MANAGE = 'order.manage';
    case ORDER_CONFIRM = 'order.confirm';
    case ORDER_CANCEL = 'order.cancel';
    case ORDER_DELETE = 'order.delete';
    case ORDER_ASSIGN = 'order.assign';

    /*
    |--------------------------------------------------------------------------
    | Inventory
    |--------------------------------------------------------------------------
    */
    case INVENTORY_VIEW = 'inventory.view';
    case INVENTORY_UPDATE = 'inventory.update';

    /*
    |--------------------------------------------------------------------------
    | Team (Global)
    |--------------------------------------------------------------------------
    */
    case TEAM_VIEW = 'team.view';
    case TEAM_INVITE = 'team.invite';
    case TEAM_REMOVE = 'team.remove';
    case STORE_TEAM_MANAGE = 'store.team.manage';

    /*
    |--------------------------------------------------------------------------
    | Team (Scoped – MANAGER)
    |--------------------------------------------------------------------------
    */
    case TEAM_VIEW_OWN = 'team.view.own';
    case TEAM_MANAGE_OWN = 'team.manage.own';

    /*
    |--------------------------------------------------------------------------
    | CRM / Operations
    |--------------------------------------------------------------------------
    */
    case CRM_ORDER_TRACKING = 'crm.orders.track';
    case CRM_ORDER_CONFIRMATION = 'crm.orders.confirm';
    case CRM_INVENTORY_TRACKING = 'crm.inventory.track';
    case CRM_INVENTORY_MANAGE = 'crm.inventory.manage';

    /*
    |--------------------------------------------------------------------------
    | Delivery & Accounting
    |--------------------------------------------------------------------------
    */
    case DELIVERY_PRICING_MANAGE = 'delivery.pricing.manage';
    case ACCOUNTING_CONFIRM_TEAM = 'accounting.confirm.team'; // soon

    /*
    |--------------------------------------------------------------------------
    | Finance / Debts
    |--------------------------------------------------------------------------
    */
    case FINANCE_DEBT_VIEW = 'finance.debt.view';
    case FINANCE_DEBT_CREATE = 'finance.debt.create';
    case FINANCE_DEBT_UPDATE = 'finance.debt.update';
    case FINANCE_DEBT_DELETE = 'finance.debt.delete';

    /*
    |--------------------------------------------------------------------------
    | Verification / Returns (Soon)
    |--------------------------------------------------------------------------
    */
    case RETURNS_VERIFY_BARCODE = 'returns.verify.barcode';
    case RETURNS_PROCESS = 'returns.process';

    /*
    |--------------------------------------------------------------------------
    | Analytics / Stats
    |--------------------------------------------------------------------------
    */
    case STATS_CONFIRMATION = 'stats.confirmation';
    case STATS_DELIVERY = 'stats.delivery';
    case STATS_TOP_KPIS = 'stats.top.kpis';
    case STATS_TEAM_VIEW = 'stats.team.view';

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function group(): string
    {
        return explode('.', $this->value)[0];
    }

    public function label(): string
    {
        return str($this->value)
            ->replace('.', ' ')
            ->title();
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
