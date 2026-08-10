<?php

namespace App\Filament\SuperAdmin\Resources\Statuses;

use App\Filament\SuperAdmin\Resources\Statuses\Pages\CreateStatus;
use App\Filament\SuperAdmin\Resources\Statuses\Pages\EditStatus;
use App\Filament\SuperAdmin\Resources\Statuses\Pages\ListStatuses;
use App\Filament\SuperAdmin\Resources\Statuses\Schemas\StatusForm;
use App\Filament\SuperAdmin\Resources\Statuses\Tables\StatusesTable;
use App\Models\Status;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StatusResource extends Resource
{
    protected static ?string $model = Status::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'key';
    protected static string|UnitEnum|null $navigationGroup = 'Stores Management';
    public static function form(Schema $schema): Schema
    {
        return StatusForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StatusesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStatuses::route('/'),
            'create' => CreateStatus::route('/create'),
            'edit' => EditStatus::route('/{record}/edit'),
        ];
    }
}
