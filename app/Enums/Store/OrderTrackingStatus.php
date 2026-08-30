<?php

namespace App\Enums\Store;

use App\Enums\Concerns\InteractsWithStatusKit;

/**
 * حالة التتبع على مستوى الشحنة (سجل order_trackings المستقل عن الاوردر).
 * نطاق العرض = 'tracking' — منفصل عن نطاق order حتى لا يُحشرا في نموذج واحد.
 */
enum OrderTrackingStatus: string
{
    use InteractsWithStatusKit;

    protected const GROUP = 'tracking';

    case SHIPPED = 'shipped';
    case IN_TRANSIT = 'in_transit';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERED = 'delivered';
    case RETURNED = 'returned';
    case RETURNING = 'returning';
    case FAILED_ATTEMPT = 'failed_attempt';
    case LOST = 'lost';
    case DAMAGED = 'damaged';

    /**
     * الحالات الجارية (الشحنة لم تنتهِ بعد بشكل نهائي).
     */
    public static function open(): array
    {
        return [
            self::SHIPPED,
            self::IN_TRANSIT,
            self::OUT_FOR_DELIVERY,
            self::RETURNING,
        ];
    }

    /**
     * الحالات النهائية (لا يتبعها نشاط آخر على هذا التتبع).
     */
    public static function terminal(): array
    {
        return [
            self::DELIVERED,
            self::RETURNED,
            self::LOST,
            self::DAMAGED,
        ];
    }

    public function isTerminal(): bool
    {
        return in_array($this, self::terminal(), true);
    }

    public function isOpen(): bool
    {
        return ! $this->isTerminal();
    }

    /**
     * خريطة أولية لنص شركة الشحن الخام إلى حالة طبيعية.
     * نقطة توسع: لكل مزوّد شحن خريطة خاصة به تُضاف لاحقًا.
     */
    public static function fromCarrier(?string $raw): ?self
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $value = mb_strtolower(str_replace([' ', '-'], '_', trim($raw)));

        return match (true) {
            str_contains($value, 'delivered') => self::DELIVERED,
            str_contains($value, 'out_for_delivery'),
            str_contains($value, 'on_the_way'),
            str_contains($value, 'arrived_at') => self::OUT_FOR_DELIVERY,
            str_contains($value, 'failed_attempt'),
            str_contains($value, 'attempt_failed'),
            str_contains($value, 'no_answer') => self::FAILED_ATTEMPT,
            str_contains($value, 'returning'),
            str_contains($value, 'return_in_progress') => self::RETURNING,
            str_contains($value, 'returned'),
            str_contains($value, 'return_to_sender') => self::RETURNED,
            str_contains($value, 'lost') => self::LOST,
            str_contains($value, 'damaged'),
            str_contains($value, 'damage') => self::DAMAGED,
            str_contains($value, 'transit'),
            str_contains($value, 'shipped'),
            str_contains($value, 'picked_up') => self::IN_TRANSIT,
            default => null,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::SHIPPED => status_label(self::GROUP, 'shipped'),
            self::IN_TRANSIT => status_label(self::GROUP, 'in_transit'),
            self::OUT_FOR_DELIVERY => status_label(self::GROUP, 'out_for_delivery'),
            self::DELIVERED => status_label(self::GROUP, 'delivered'),
            self::RETURNED => status_label(self::GROUP, 'returned'),
            self::RETURNING => status_label(self::GROUP, 'returning'),
            self::FAILED_ATTEMPT => status_label(self::GROUP, 'failed_attempt'),
            self::LOST => status_label(self::GROUP, 'lost'),
            self::DAMAGED => status_label(self::GROUP, 'damaged'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SHIPPED => status_color(self::GROUP, 'shipped'),
            self::IN_TRANSIT => status_color(self::GROUP, 'in_transit'),
            self::OUT_FOR_DELIVERY => status_color(self::GROUP, 'out_for_delivery'),
            self::DELIVERED => status_color(self::GROUP, 'delivered'),
            self::RETURNED => status_color(self::GROUP, 'returned'),
            self::RETURNING => status_color(self::GROUP, 'returning'),
            self::FAILED_ATTEMPT => status_color(self::GROUP, 'failed_attempt'),
            self::LOST => status_color(self::GROUP, 'lost'),
            self::DAMAGED => status_color(self::GROUP, 'damaged'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::SHIPPED => status_icon(self::GROUP, 'shipped'),
            self::IN_TRANSIT => status_icon(self::GROUP, 'in_transit'),
            self::OUT_FOR_DELIVERY => status_icon(self::GROUP, 'out_for_delivery'),
            self::DELIVERED => status_icon(self::GROUP, 'delivered'),
            self::RETURNED => status_icon(self::GROUP, 'returned'),
            self::RETURNING => status_icon(self::GROUP, 'returning'),
            self::FAILED_ATTEMPT => status_icon(self::GROUP, 'failed_attempt'),
            self::LOST => status_icon(self::GROUP, 'lost'),
            self::DAMAGED => status_icon(self::GROUP, 'damaged'),
        };
    }
}
