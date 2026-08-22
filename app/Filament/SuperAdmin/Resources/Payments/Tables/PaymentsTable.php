<?php

namespace App\Filament\SuperAdmin\Resources\Payments\Tables;

use App\Domains\Billing\Actions\ActivateSubscriptionAction;
use App\Domains\Billing\Actions\ReviewManualPaymentAction;
use App\Enums\SubscriptionPayment\StatusPaymentEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->limit(8)
                    ->copyable(),

                TextColumn::make('user.name')
                    ->label('Merchant'),

                TextColumn::make('subscription.plan.name')
                    ->label('Plan'),

                TextColumn::make('manual_method')
                    ->label('Method')
                    ->badge()
                    ->placeholder('-'),

                TextColumn::make('reference_number')
                    ->label('Reference')
                    ->limit(15)
                    ->placeholder('-'),

                TextColumn::make('amount')
                    ->money('DZD')
                    ->sortable(),

                TextColumn::make('currency'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => $state instanceof \BackedEnum ? $state->filamentColor() : 'gray')
                    ->label('Status'),

                TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options(StatusPaymentEnum::options()),
            ])
            ->recordActions([
                Action::make('view_proof')
                    ->label('View Proof')
                    ->icon('heroicon-o-eye')
                    ->visible(fn ($record) => $record->proof_file_path !== null)
                    ->modalContent(function ($record) {
                        $url = asset('storage/' . $record->proof_file_path);
                        if (str_ends_with(strtolower($record->proof_file_path), '.pdf')) {
                            return view('filament::components._components.modalConfirmation', [
                                'heading' => 'Proof of Payment',
                                'content' => '<iframe src="' . $url . '" class="w-full h-96 rounded-lg" frameborder="0"></iframe>',
                            ]);
                        }
                        return '<img src="' . $url . '" alt="Proof of Payment" class="w-full rounded-lg" />';
                    }),

                Action::make('approve')
                    ->label('Approve Payment')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status instanceof \BackedEnum && $record->status === StatusPaymentEnum::PENDING_REVIEW)
                    ->requiresConfirmation()
                    ->modalHeading('Approve Manual Payment?')
                    ->modalDescription('This will mark the payment as paid and activate the subscription.')
                    ->action(function ($record) {
                        $reviewer = user();
                        app(ReviewManualPaymentAction::class)->approve($record, $reviewer);

                        Notification::make()
                            ->title('Payment Approved')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Reject Payment')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status instanceof \BackedEnum && $record->status === StatusPaymentEnum::PENDING_REVIEW)
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3)
                            ->placeholder('Enter the reason for rejection...'),
                    ])
                    ->modalHeading('Reject Manual Payment?')
                    ->modalDescription('The merchant will be notified with the rejection reason.')
                    ->action(function ($record, array $data) {
                        $reviewer = user();
                        app(ReviewManualPaymentAction::class)->reject($record, $reviewer, $data['rejection_reason']);

                        Notification::make()
                            ->title('Payment Rejected')
                            ->warning()
                            ->send();
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
