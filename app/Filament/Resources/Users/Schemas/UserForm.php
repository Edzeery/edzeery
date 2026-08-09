<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\Platform\UserRoleEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Locations\City;
use App\Models\Locations\State;
use App\Models\Stores\Team\StoreRole;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make("Basic Info")
                    ->icon(Heroicon::User)
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required(),
                        DateTimePicker::make('email_verified_at'),
                        TextInput::make('password')
                            ->password()
                            ->required(),
                    ]),

                Section::make("Location")
                    ->icon(Heroicon::MapPin)
                    ->schema([
                        Select::make("country_id")
                            ->label('Country')
                            ->options(fn() => countries())
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set("state_id", null);
                                $set('city_id', null);
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->reactive()
                            ->hintIcon('heroicon-o-shield-check'),

                        Select::make('state_id')
                            ->label('State / Wilaya')
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('city_id', null);
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->reactive()
                            ->options(function (callable $get) {
                                $country = $get('country_id');
                                if (! $country) {
                                    return [];
                                }
                                return State::whereCountryId($country)->pluck('name', 'id');
                            })
                            ->hintIcon('heroicon-o-shield-check'),

                        Select::make("city_id")
                            ->label('City')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->reactive()
                            ->options(function (callable $get) {
                                $state = $get('state_id');
                                if (! $state) {
                                    return [];
                                }
                                return City::whereStateId($state)->pluck('name', 'id');
                            })
                            ->hintIcon('heroicon-o-shield-check'),

                    ]),
                Section::make("Role & Status")
                    ->schema([
                        Select::make('role.name')
                            ->label('Role')
                            ->options(fn() =>  UserRoleEnum::options())
                            ->reactive() // مهم للتحديث عند تغيير الدور
                            ->searchable()
                            ->preload()

                            ->required(),

                        Placeholder::make('permissions')
                            ->label('Permissions')
                            ->content(fn(callable $get) => $get('store_role_id')
                                ? StoreRole::find($get('store_role_id'))
                                ?->permissions()
                                ->pluck('key') // أو أي حقل يعبر عن الصلاحية
                                ->join(', ')
                                : 'No permissions'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columnSpan(2)
                    ->columns(2),

            ]);
    }
}
