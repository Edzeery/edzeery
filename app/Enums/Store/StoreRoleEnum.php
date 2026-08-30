<?php

namespace App\Enums\Store;

use App\Enums\Concerns\InteractsWithStatusKit;

enum StoreRoleEnum: string
{
    use InteractsWithStatusKit; // Auto Detect Group

    protected const GROUP = 'roles';

    protected const STATUS_KIT_GROUP = 'role';

    case OWNER = 'owner';   // التاجر نفسه (نائب عنه)
    case ADMIN = 'admin';   // نائب التاجر (صلاحيات كبيرة)
    case MANAGER = 'manager'; // يسير فرق (Confirm / Track)
    case STAFF = 'staff';   // موظف تنفيذ

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($role) => [$role->value => $role->label()])
            ->toArray();
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
