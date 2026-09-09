<?php

namespace App\Filament\SuperAdmin\Resources\Carriers\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class CarrierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('platform_id')
                    ->label('Platform (parent company)')
                    ->relationship('platform', 'name')
                    ->nullable()
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->maxLength(100)
                    ->nullable(),
                Repeater::make('credential_fields')
                    ->label('Credential fields shown to merchants')
                    ->schema([
                        TextInput::make('key')
                            ->required()
                            ->maxLength(60),
                        TextInput::make('label')
                            ->required()
                            ->maxLength(120),
                        Select::make('type')
                            ->options([
                                'text' => 'Text',
                                'password' => 'Password / Token',
                            ])
                            ->default('text'),
                        Toggle::make('required')
                            ->default(false),
                    ])
                    ->columns(4)
                    ->default([]),
                Fieldset::make('Carrier capabilities')
                    ->columns(2)
                    ->schema([
                        Toggle::make('supports_delivery')->label('Delivery'),
                        Toggle::make('supports_exchange')->label('Exchange'),
                        Toggle::make('supports_pickup')->label('Pickup'),
                        Toggle::make('supports_free_shipping_mode')->label('Free shipping mode'),
                        Toggle::make('supports_express_economic')->label('Express / Economic'),
                        Toggle::make('supports_api_notes')->label('API notes'),
                        Toggle::make('supports_price_sync')->label('Price sync'),
                    ]),
                Toggle::make('is_active')
                    ->default(true),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
