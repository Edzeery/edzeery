<?php

namespace App\Domains\Billing\Enums;

use App\Enums\Concerns\InteractsWithStatusPresentation;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatusEnum: string implements HasLabel
{
     use InteractsWithStatusPresentation;

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

}
