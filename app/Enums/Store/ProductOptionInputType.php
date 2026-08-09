<?php

namespace App\Enums\Store;

enum ProductOptionInputType: string
{
    case SELECT   = 'select';
    case RADIO    = 'radio';
    case CHECKBOX = 'checkbox';
    case TEXT     = 'text';

    // public function label(): string
    // {
    //     return match ($this) {
    //         self::SELECT   => 'Select dropdown',
    //         self::RADIO    => 'Radio buttons',
    //         self::CHECKBOX => 'Checkbox',
    //         self::TEXT     => 'Text input',
    //     };
    // }

    public function label(): string
    {
        return __(
             'productoption.' . $this->value
        );
    }


    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
