<?php

namespace App\Enums\Finance;

use App\Enums\Concerns\InteractsWithStatusKit;

enum DebtTypeEnum: string
{
    use InteractsWithStatusKit;

    protected const GROUP = 'debt_type';

    case OWED = 'owed';
    case OWING = 'owing';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return $this->kitLabel();
    }
}
