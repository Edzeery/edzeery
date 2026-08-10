<?php

namespace App\Filament\SuperAdmin\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make("Basic Info")
                    ->schema([
                        TextEntry::make('name')
                        ->icon(Heroicon::OutlinedUser),
                        TextEntry::make('email')
                            ->label('Email address')
                            ->icon(Heroicon::OutlinedShieldCheck),
                        TextEntry::make('email_verified_at')
                            ->icon(Heroicon::OutlinedShieldCheck)
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
                Section::make("Location")
                    ->schema([
                        TextEntry::make('country.name')
                            ->label('Country'),
                        TextEntry::make('state.name')
                            ->label('State'),
                        TextEntry::make('city.name')
                            ->label('City'),
                    ])
            ]);
    }
}
