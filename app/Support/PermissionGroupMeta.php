<?php

namespace App\Support;

use App\Enums\Store\StorePermissionEnum;

/**
 * Static catalogue metadata for the team Permission Hub (Phase 36.1).
 *
 * Organises StorePermissionEnum cases into display groups, provides the
 * icon for each group, flags destructive/ownership-sensitive permissions and
 * captures the prerequisite map the hub enforces while toggling. Titles and
 * descriptions live in resources/lang/{locale}/permission_groups.php.
 */
final class PermissionGroupMeta
{
    /**
     * Ordered group keys (the enum's natural dot-segment groups).
     *
     * @var array<int, string>
     */
    public const GROUP_ORDER = [
        'store',
        'products',
        'order',
        'inventory',
        'team',
        'crm',
        'delivery',
        'accounting',
        'finance',
        'returns',
        'stats',
    ];

    /**
     * Icon name per group — keys must exist in components/edz/icon.blade.php.
     *
     * @var array<string, string>
     */
    public const GROUP_ICON = [
        'store' => 'building-store',
        'products' => 'package',
        'order' => 'cart',
        'inventory' => 'cube',
        'team' => 'users',
        'crm' => 'phone',
        'delivery' => 'truck',
        'accounting' => 'banknotes',
        'finance' => 'credit-card',
        'returns' => 'arrow-uturn-left',
        'stats' => 'trending-up',
    ];

    /**
     * Destructive / ownership-sensitive permissions. Flagged in the hub so a
     * manager delegating permissions never grants them by accident.
     *
     * @var array<int, string>
     */
    public const DANGEROUS = [
        'store.delete.final',
        'store.transfer.ownership',
        'store.billing.manage',
        'store.settings.sensitive',
        'order.delete',
        'team.remove',
        'delivery.riders.delete',
        'finance.debt.delete',
    ];

    /**
     * Prerequisite map: permission => permissions that must be granted first.
     * The hub resolves these transitively when enabling and cascades removals
     * through requiredBy() when disabling.
     *
     * @var array<string, array<int, string>>
     */
    public const DEPENDENCIES = [
        'store.update' => ['store.view'],
        'store.team.manage' => ['store.view'],
        'products.create' => ['products.view'],
        'products.update' => ['products.view'],
        'products.delete' => ['products.view'],
        'order.manage' => ['order.view'],
        'order.confirm' => ['order.view'],
        'order.cancel' => ['order.view'],
        'order.delete' => ['order.view'],
        'order.assign' => ['order.view'],
        'order.edit.price' => ['order.view'],
        'order.dispatch_validate' => ['order.view'],
        'inventory.update' => ['inventory.view'],
        'team.invite' => ['team.view'],
        'team.remove' => ['team.view'],
        'team.manage.own' => ['team.view.own'],
        'crm.orders.track' => ['order.view'],
        'crm.orders.confirm' => ['order.view'],
        'crm.inventory.track' => ['inventory.view'],
        'crm.inventory.manage' => ['crm.inventory.track', 'inventory.view'],
        'delivery.riders.create' => ['delivery.riders.view'],
        'delivery.riders.update' => ['delivery.riders.view'],
        'delivery.riders.delete' => ['delivery.riders.view'],
        'finance.debt.create' => ['finance.debt.view'],
        'finance.debt.update' => ['finance.debt.view'],
        'finance.debt.delete' => ['finance.debt.view'],
        'returns.process' => ['returns.verify.barcode'],
    ];

    public static function order(): array
    {
        return self::GROUP_ORDER;
    }

    public static function icon(string $group): string
    {
        return self::GROUP_ICON[$group] ?? 'grid';
    }

    public static function isDangerous(string $permission): bool
    {
        return in_array($permission, self::DANGEROUS, true);
    }

    public static function dependencies(string $permission): array
    {
        return self::DEPENDENCIES[$permission] ?? [];
    }

    /**
     * Inverted dependency lookup: every permission that lists $permission as
     * a prerequisite. Used to cascade removals when disabling.
     */
    public static function requiredBy(string $permission): array
    {
        $dependents = [];

        foreach (self::DEPENDENCIES as $candidate => $requirements) {
            if (in_array($permission, $requirements, true)) {
                $dependents[] = $candidate;
            }
        }

        return $dependents;
    }

    /**
     * Translated label for a permission value, falling back to the enum's
     * title-cased label when the nested lang key is missing.
     */
    public static function label(string $permission): string
    {
        $key = "permissions.{$permission}";
        $translated = __($key);

        if ($translated !== $key) {
            return $translated;
        }

        return StorePermissionEnum::tryFrom($permission)?->label() ?? $permission;
    }
}