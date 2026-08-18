<?php

namespace App\Enums\Store;

use App\Enums\Concerns\InteractsWithStatusKit;

enum OrderStatus: string
{
    use InteractsWithStatusKit;

    protected const GROUP = 'order';

    case DRAFT            = 'draft';
    case PENDING          = 'pending';
    case PAID             = 'paid';
    case CONFIRMED        = 'confirmed';
    case PREPARING        = 'preparing';
    case SHIPPED          = 'shipped';
    case IN_TRANSIT       = 'in_transit';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERED        = 'delivered';
    case ON_HOLD          = 'on_hold';
    case COMPLETED        = 'completed';
    case CANCELLED        = 'cancelled';
    case CANCELED         = 'canceled';
    case RETURNED         = 'returned';
    case REFUNDED         = 'refunded';

    public static function pendingish(): array
    {
        return [
            self::PENDING,
            self::CONFIRMED,
            self::PREPARING,
        ];
    }

    public static function active(): array
    {
        return [
            self::PAID,
            self::CONFIRMED,
            self::PREPARING,
            self::SHIPPED,
            self::IN_TRANSIT,
            self::OUT_FOR_DELIVERY,
        ];
    }

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::COMPLETED,
            self::DELIVERED,
            self::CANCELLED,
            self::CANCELED,
            self::RETURNED,
            self::REFUNDED,
        ]);
    }
}


