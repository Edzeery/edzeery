<?php

namespace App\Filament\SuperAdmin\Resources\Payments\Tables;

use App\Domains\Billing\Actions\ActivateSubscriptionAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subscription.id'),
                TextColumn::make('amount'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => $state->filamentColor()),

                TextColumn::make('paid_at')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('mark_paid')
                    ->visible(fn($record) => $record->status !== 'paid')
                    ->action(function ($record) {

                        $record->update([
                            'status' => 'paid',
                            'paid_at' => now()
                        ]);

                        app(ActivateSubscriptionAction::class)
                            ->execute($record->subscription);
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
