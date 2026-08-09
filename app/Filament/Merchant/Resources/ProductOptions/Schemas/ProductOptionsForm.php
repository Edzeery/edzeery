<?php

namespace App\Filament\Merchant\Resources\ProductOptions\Schemas;

use App\Enums\Store\ProductOptionInputType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductOptionsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Select::make('store_id')
                //     ->relationship('store', 'name')
                //     ->searchable()
                //     ->preload()
                //     ->required()
                //     ->live()
                //     ->hintIcon('heroicon-o-shield-check'),

                TextInput::make('name')
                    ->label('Option Name')
                    ->required()
                    ->maxLength(100)
                    ->helperText('e.g. Color, Size, Length'),

                Select::make('type')
                    ->label('Input Type')
                    ->options(ProductOptionInputType::options())
                    ->required()
                    ->hint('How this option will be displayed to customers'),
            ]);
    }
}
