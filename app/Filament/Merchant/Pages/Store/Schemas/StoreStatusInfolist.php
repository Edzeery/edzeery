<?php

namespace App\Filament\Merchant\Pages\Store\Schemas;

use App\Models\Stores\Store;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;

class StoreStatusInfolist
{
    public static function make(Schema $infolist, Store $store): Schema
    {
        // جلب أحدث حالة للمتجر
        $statusHistory = $store->latestStatus();
        $status = $statusHistory?->status ?? $store->status; // enum
        $reason = $statusHistory?->reason ?? 'No reason provided';

        return $infolist
            ->record($store)
            ->schema([
                Grid::make(2)
                    ->schema(
                        [
                            Section::make('Store Information')
                                ->schema([
                                    TextEntry::make('name')
                                        ->label('Store Name')
                                        ->getStateUsing(fn($record) => $record->name)
                                        ->disabled(),

                                    TextEntry::make('status')
                                        ->label('Status')
                                        ->badge()
                                        ->getStateUsing(fn($record) => $record->latestStatus()?->status?->getLabel()
                                            ?? $record->status?->getLabel() ?? 'Unknown')
                                        ->color(fn($record) => $record->currentStatus()?->filamentColor() ?? 'gray')
                                        ->icon(fn($record) => $record->currentStatus()?->icon() ?? 'heroicon-o-exclamation-circle')
                                        ->iconColor(fn($record) => $record->currentStatus()?->filamentColor() ?? 'gray')
                                        ->disabled(),

                                    TextEntry::make('reason')
                                        ->label('Reason / Comment')
                                        ->getStateUsing(fn($record) => $record->latestStatus()?->reason
                                            ?? 'No reason provided')
                                        ->disabled(),
                                ])->columns(2),


                        ]
                    ),
                RepeatableEntry::make('statusHistories')
                    ->label('Stauts history')
                    ->table([
                        TableColumn::make(__('titles.name'))
                        // ->alignment(Alignment::Center)
                        ,
                        TableColumn::make(__('titles.reason')),
                        TableColumn::make(__('titles.status')),
                    ])
                    ->schema([
                        TextEntry::make('store.name'),
                        TextEntry::make('reason')->placeholder('_'),
                        TextEntry::make('status')
                            ->badge()
                            ->label(fn( $state) => $state->label())
                            ->icon(fn($state) => $state->icon())
                            ->color(fn($state) => $state->filamentColor())
                            ->iconColor(fn($state) => $state->filamentColor())
                            ->placeholder('—'),
                    ])


            ]);
    }
}
