<?php

namespace App\Filament\SuperAdmin\Resources\Subscriptions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),

                Select::make('plan_id')
                    ->relationship('plan', 'name')
                    ->required()
                    ->live(),

                Select::make('plan_price_id')
                    ->options(
                        fn($get) =>
                        \App\Models\Plans\PlanPrice::where('plan_id', $get('plan_id'))
                            ->pluck('billing_period', 'id')
                    )
                    ->required(),

                Toggle::make('is_trial'),

                DateTimePicker::make('starts_at'),

                DateTimePicker::make('ends_at'),

                Select::make('status')
                    ->options(\App\Domains\Billing\Enums\SubscriptionStatusEnum::options())
            ]);
    }
}
