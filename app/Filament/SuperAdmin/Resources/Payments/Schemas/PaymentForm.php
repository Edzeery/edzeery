<?php

namespace App\Filament\SuperAdmin\Resources\Payments\Schemas;

use App\Domains\Billing\Enums\PaymentStatusEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('subscription_id')
                    ->relationship('subscription', 'id')
                    ->searchable(),

                TextInput::make('amount')->numeric()->required(),

                Select::make('status')
                    ->options(PaymentStatusEnum::options())
                    ->required(),

                TextInput::make('gateway')->default('manual'),

                TextInput::make('transaction_id'),
            ]);
    }
}
