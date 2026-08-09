<?php

namespace App\Filament\Merchant\Resources\ProductVariants\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductVariantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Variant Info')
                ->schema([
                    TextInput::make('sku')
                        ->required()
                        ->disabled(),

                    TextInput::make('price')
                        ->numeric()
                        ->required(),
                    TextInput::make('compare_price')
                        ->numeric()
                        ->required(),
                    TextInput::make('cost_price')
                        ->numeric()
                        ->required(),
                ]),
        ]);
    }
}
