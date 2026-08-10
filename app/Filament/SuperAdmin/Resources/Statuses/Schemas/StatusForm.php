<?php

namespace App\Filament\SuperAdmin\Resources\Statuses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StatusForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('store_id')
                    ->relationship('store', 'name'),
                TextInput::make('store_scope_id')
                    ->numeric(),
                TextInput::make('type')
                    ->required(),
                TextInput::make('key')
                    ->required(),
                TextInput::make('label')
                    ->required(),
                TextInput::make('color')
                    ->required()
                    ->default('gray'),
                Toggle::make('is_system')
                    ->required(),
                Toggle::make('affects_inventory')
                    ->required(),
                TextInput::make('movement_type'),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
