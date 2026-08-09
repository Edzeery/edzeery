<?php

namespace App\Enums\Store;

use App\Enums\Concerns\InteractsWithRolePresentation;

enum StoreRoleEnum: string
{
    use InteractsWithRolePresentation; // Auto Detect Group

    protected const GROUP = 'roles';

    case OWNER   = 'owner';   // التاجر نفسه (نائب عنه)
    case ADMIN   = 'admin';   // نائب التاجر (صلاحيات كبيرة)
    case MANAGER = 'manager'; // يسير فرق (Confirm / Track)
    case STAFF   = 'staff';   // موظف تنفيذ


    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($role) => [$role->value => $role->label()])
            ->toArray();
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
