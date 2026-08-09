<?php

namespace App\Filament\Resources\Stores\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Image;
use Filament\Schemas\Schema;

class StoreInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user_id')
                    ->numeric(),
                TextEntry::make('name'),
                TextEntry::make('slug'),
                ImageEntry::make('logo')
                    ->disk('public')
                    ->visibility('public') 
                    ->default('/img/icons/noimg.png'),

                ImageEntry::make('cover')
                    ->disk('public')
                    ->visibility('public')
                    ->default('/img/icons/noimg.png'),
                TextEntry::make('description')
                    ->placeholder('null')
                    ->columnSpanFull(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
