<?php

namespace App\Domains\Billing\Enums;

use App\Enums\Concerns\InteractsWithStatusPresentation;
use Filament\Support\Contracts\HasLabel;

enum SubscriptionStatusEnum: string implements HasLabel
{
    use InteractsWithStatusPresentation;

    protected const GROUP = 'subscription';

    case PENDING   = 'pending';
    case ACTIVE    = 'active';
    case EXPIRED   = 'expired';
    case CANCELED  = 'canceled';
    case SUSPENDED = 'suspended';

    public function getLabel(): string
    {
        return $this->label();
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [
                $case->value => $case->getLabel(),
            ])
            ->toArray();
    }
}
