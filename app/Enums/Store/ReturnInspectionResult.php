<?php

namespace App\Enums\Store;

enum ReturnInspectionResult: string
{
    case GOOD     = 'good';       // sellable as-is, eligible for requeue
    case DAMAGED  = 'damaged';    // not sellable, cannot requeue
    case PARTIAL  = 'partial';    // incomplete items returned
    case LOST     = 'lost';       // carrier reports lost

    public function isRequeueEligible(): bool
    {
        return $this === self::GOOD;
    }

    public function label(): string
    {
        return match ($this) {
            self::GOOD    => __('merchant_panel.return_good'),
            self::DAMAGED => __('merchant_panel.return_damaged'),
            self::PARTIAL => __('merchant_panel.return_partial'),
            self::LOST    => __('merchant_panel.return_lost'),
        };
    }
}
