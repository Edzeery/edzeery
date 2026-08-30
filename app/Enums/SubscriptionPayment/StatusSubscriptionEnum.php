<?php

namespace App\Enums\SubscriptionPayment;

use App\Enums\Concerns\InteractsWithStatusKit;
use Filament\Support\Contracts\HasLabel;

enum StatusSubscriptionEnum: string implements HasLabel
{
    use InteractsWithStatusKit;

    protected const GROUP = 'subscription';

    case ACTIVE = 'active';
    case PENDING = 'pending';
    case EXPIRED = 'expired';
    case CANCELED = 'canceled';
    case SUSPENDED = 'suspended';

    public function getLabel(): string
    {
        return $this->label();
        // return __('status.' . $this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => $case->getLabel(),
            ])
            ->toArray();
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->getLabel(),
            'color' => $this->color(),
            'icon' => $this->icon(),
        ];
    }

    public static function api(): array
    {
        return array_map(
            fn ($case) => $case->toArray(),
            self::cases()
        );
    }

    public function color(): string
    {
        return $this->filamentColor();
    }
}
