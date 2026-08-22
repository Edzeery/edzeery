<?php

namespace App\Domains\Billing\Enums;

use Filament\Support\Contracts\HasLabel;

enum ManualPaymentMethodEnum: string implements HasLabel
{
    case BARIDIMOB = 'baridimob';
    case CCP = 'ccp';
    case BANK_TRANSFER = 'bank_transfer';

    public function getLabel(): string
    {
        return match ($this) {
            self::BARIDIMOB => 'BaridiMob',
            self::CCP => 'CCP (Postal Transfer)',
            self::BANK_TRANSFER => 'Bank Transfer (CIB/BEA/BNA)',
        };
    }
}
