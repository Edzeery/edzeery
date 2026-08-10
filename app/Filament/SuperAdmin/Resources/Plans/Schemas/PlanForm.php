<?php

namespace App\Filament\SuperAdmin\Resources\Plans\Schemas;

use App\Models\Plans\Plan;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Plan Info')
                    ->schema([

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Textarea::make('description')
                            ->rows(3),

                    ])->columns(2),

                Section::make('Limits & Trial')
                    ->schema([

                        TextInput::make('trial_days')
                            ->numeric()
                            ->default(0)
                            ->suffix('days'),

                        TextInput::make('max_stores')
                            ->numeric()
                            ->default(1),

                        Select::make('upgrade_to_plan_id')
                            ->label('Auto Upgrade To')
                            ->relationship('upgradePlan', 'name')
                            ->searchable()
                            ->preload(),

                    ])->columns(3),

                Section::make('Settings')
                    ->schema([

                        Toggle::make('is_active')
                            ->default(true),

                        Toggle::make('is_default')
                            ->afterStateUpdated(function ($state) {
                                if ($state) {
                                    Plan::where('is_default', true)->update(['is_default' => false]);
                                }
                            }),

                        TextInput::make('currency')
                            ->default('DZD')
                            ->maxLength(10),

                    ])->columns(3),
            ]);
    }
}
