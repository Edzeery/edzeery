<?php

namespace App\Enums\Finance;

use App\Enums\Concerns\InteractsWithStatusKit;

enum DebtStatusEnum: string
{
    use InteractsWithStatusKit;

    protected const GROUP = 'debt';

    case ACTIVE = 'active';
    case PARTIAL = 'partial';
    case PAID = 'paid';
    case OVERDUE = 'overdue';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return $this->kitLabel();
    }
}
