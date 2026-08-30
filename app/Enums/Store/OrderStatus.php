<?php

namespace App\Enums\Store;

use App\Enums\Concerns\InteractsWithStatusKit;

enum OrderStatus: string
{
    use InteractsWithStatusKit;

    protected const GROUP = 'order';

    case DRAFT = 'draft';
    case PENDING = 'pending';
    case PAID = 'paid';
    case CONFIRMED = 'confirmed';
    case PREPARING = 'preparing';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case IN_TRANSIT = 'in_transit';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERED = 'delivered';
    case ON_HOLD = 'on_hold';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case CANCELED = 'canceled';
    case RETURNED = 'returned';
    case REFUNDED = 'refunded';
    case WRONG_NUMBER = 'wrong_number';
    case UNDELIVERABLE = 'undeliverable';
    case UNCLAIMED = 'unclaimed';
    

    public static function pendingish(): array
    {
        return [
            self::PENDING,
            self::CONFIRMED,
            self::PREPARING,
            self::PROCESSING,
        ];
    }

    public static function active(): array
    {
        return [
            self::PAID,
            self::CONFIRMED,
            self::PREPARING,
            self::PROCESSING,
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

    public function isActive(): bool
    {
        return in_array($this, self::active());
    }

    public function isPendingish(): bool
    {
        return in_array($this, self::pendingish());
    }

    public function label(): string
    {

        return match ($this) {
            self::DRAFT => status_label(self::GROUP, 'draft'),
            self::PENDING => status_label(self::GROUP, 'pending'),
            self::PAID => status_label(self::GROUP, 'paid'),
            self::CONFIRMED => status_label(self::GROUP, 'confirmed'),
            self::PREPARING => status_label(self::GROUP, 'preparing'),
            self::PROCESSING => status_label(self::GROUP, 'processing'),
            self::SHIPPED => status_label(self::GROUP, 'shipped'),
            self::IN_TRANSIT => status_label(self::GROUP, 'in_transit'),
            self::OUT_FOR_DELIVERY => status_label(self::GROUP, 'out_for_delivery'),
            self::DELIVERED => status_label(self::GROUP, 'delivered'),
            self::ON_HOLD => status_label(self::GROUP, 'on_hold'),
            self::COMPLETED => status_label(self::GROUP, 'completed'),
            self::CANCELLED => status_label(self::GROUP, 'cancelled'),
            self::CANCELED => status_label(self::GROUP, 'canceled'),
            self::RETURNED => status_label(self::GROUP, 'returned'),
            self::REFUNDED => status_label(self::GROUP, 'refunded'),
            self::WRONG_NUMBER => status_label(self::GROUP, 'wrong_number'),
            self::UNDELIVERABLE => status_label(self::GROUP, 'undeliverable'),
            self::UNCLAIMED => status_label(self::GROUP, 'unclaimed'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => status_color(self::GROUP, 'draft'),
            self::PENDING => status_color(self::GROUP, 'pending'),
            self::PAID => status_color(self::GROUP, 'paid'),
            self::CONFIRMED => status_color(self::GROUP, 'confirmed'),
            self::PREPARING => status_color(self::GROUP, 'preparing'),
            self::PROCESSING => status_color(self::GROUP, 'processing'),
            self::SHIPPED => status_color(self::GROUP, 'shipped'),
            self::IN_TRANSIT => status_color(self::GROUP, 'in_transit'),
            self::OUT_FOR_DELIVERY => status_color(self::GROUP, 'out_for_delivery'),
            self::DELIVERED => status_color(self::GROUP, 'delivered'),
            self::ON_HOLD => status_color(self::GROUP, 'on_hold'),
            self::COMPLETED => status_color(self::GROUP, 'completed'),
            self::CANCELLED => status_color(self::GROUP, 'cancelled'),
            self::CANCELED => status_color(self::GROUP, 'canceled'),
            self::RETURNED => status_color(self::GROUP, 'returned'),
            self::REFUNDED => status_color(self::GROUP, 'refunded'),
            self::WRONG_NUMBER => status_color(self::GROUP, 'wrong_number'),
            self::UNDELIVERABLE => status_color(self::GROUP, 'undeliverable'),
            self::UNCLAIMED => status_color(self::GROUP, 'unclaimed'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DRAFT => status_icon(self::GROUP, 'draft'),
            self::PENDING => status_icon(self::GROUP, 'pending'),
            self::PAID => status_icon(self::GROUP, 'paid'),
            self::CONFIRMED => status_icon(self::GROUP, 'confirmed'),
            self::PREPARING => status_icon(self::GROUP, 'preparing'),
            self::PROCESSING => status_icon(self::GROUP, 'processing'),
            self::SHIPPED => status_icon(self::GROUP, 'shipped'),
            self::IN_TRANSIT => status_icon(self::GROUP, 'in_transit'),
            self::OUT_FOR_DELIVERY => status_icon(self::GROUP, 'out_for_delivery'),
            self::DELIVERED => status_icon(self::GROUP, 'delivered'),
            self::ON_HOLD => status_icon(self::GROUP, 'on_hold'),
            self::COMPLETED => status_icon(self::GROUP, 'completed'),
            self::CANCELLED => status_icon(self::GROUP, 'cancelled'),
            self::CANCELED => status_icon(self::GROUP, 'canceled'),
            self::RETURNED => status_icon(self::GROUP, 'returned'),
            self::REFUNDED => status_icon(self::GROUP, 'refunded'),
            self::WRONG_NUMBER => status_icon(self::GROUP, 'wrong_number'),
            self::UNDELIVERABLE => status_icon(self::GROUP, 'undeliverable'),
            self::UNCLAIMED => status_icon(self::GROUP, 'unclaimed'),
        };
    }
}
