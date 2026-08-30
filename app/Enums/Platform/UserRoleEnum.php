<?php

declare(strict_types=1);

namespace App\Enums\Platform;

use App\Enums\Concerns\InteractsWithStatusKit;

enum UserRoleEnum: string
{
    use InteractsWithStatusKit; // Auto Detect Group

    protected const GROUP = 'roles';

    protected const STATUS_KIT_GROUP = 'role';
    // Platform
    case SUPER_ADMIN = 'super_admin';   // مالك المنصة
    case ADMIN = 'admin';         // نائب المالك
    case SUPPORT_AGENT = 'support_agent'; // خدمة العملاء
    case TECH_SUPPORT = 'tech_support';  // دعم فني

    // Merchant
    case MERCHANT = 'merchant';      // صاحب الحساب التجاري

    // Public
    case USER = 'user';           // مستخدم عادي

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
