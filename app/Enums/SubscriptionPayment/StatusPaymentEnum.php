<?php

namespace App\Enums\SubscriptionPayment;

use App\Enums\Concerns\HasFilamentPresentation;
use App\Enums\Concerns\InteractsWithStatusKit;
use Filament\Support\Contracts\HasLabel;

enum StatusPaymentEnum: string implements HasLabel
{
    use HasFilamentPresentation;
    use InteractsWithStatusKit;

    protected const GROUP = 'payment';

    case PENDING   = 'pending';
    case PAID      = 'paid';
    case FAILED    = 'failed';
    case REFUNDED  = 'refunded';
    case CANCELED  = 'canceled';

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
    public function color(): string
    {
        return $this->filamentColor();
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->getLabel(),
            'color' => $this->css(),
            'icon'  => $this->icon(),
        ];
    }
    public static function api(): array
    {
        return array_map(
            fn($case) => $case->toArray(),
            self::cases()
        );
    }
}
