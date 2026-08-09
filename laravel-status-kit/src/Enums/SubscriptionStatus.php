<?php

namespace Edzeery\LaravelStatusKit\Enums;

use Edzeery\LaravelStatusKit\Enums\Concerns\InteractsWithStatusPresentation;
use Edzeery\LaravelStatusKit\Enums\Contracts\HasStatusPresentation;

enum SubscriptionStatus: string implements HasStatusPresentation
{
    use InteractsWithStatusPresentation;

    const GROUP = 'subscription';

    case Active = 'active';
    case Pending = 'pending';
    case Expired = 'expired';
    case Canceled = 'canceled';
    case Suspended = 'suspended';

    public static function options(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label(),
            'color' => $this->color(),
            'filament' => $this->filamentColor(),
            'icon' => $this->icon(),
            'hex' => $this->hex(),
        ];
    }
}
