<?php

namespace App\Filament\SuperAdmin\Resources\Subscriptions\Tables;

use App\Domains\Billing\Actions\ActivateSubscriptionAction;
use App\Domains\Billing\Enums\SubscriptionStatusEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name'),
                TextColumn::make('plan.name'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => $state->filamentColor()),

                TextColumn::make('starts_at')->date(),
                TextColumn::make('ends_at')->date(),

                IconColumn::make('is_trial')->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('activate')
                    ->visible(fn($record) => $record->status !== SubscriptionStatusEnum::ACTIVE)
                    ->action(
                        fn($record) =>
                        app(ActivateSubscriptionAction::class)->execute($record)
                    ),
                Action::make('cancel')
                    ->color('danger')
                    ->action(
                        fn($record) =>
                        $record->update([
                            'status' => 'canceled',
                            'canceled_at' => now()
                        ])
                    ),
                Action::make('extend')
                    ->form([
                        TextInput::make('days')->numeric()->required()
                    ])
                    ->action(function ($record, $data) {
                        $record->update([
                            'ends_at' => $record->ends_at->addDays($data['days'])
                        ]);
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
