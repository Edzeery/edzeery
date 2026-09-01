<?php

namespace App\Filament\SuperAdmin\Resources\Carriers;

use App\Domains\Shipping\Models\Carrier;
use App\Filament\SuperAdmin\Resources\Carriers\Pages\CreateCarrier;
use App\Filament\SuperAdmin\Resources\Carriers\Pages\EditCarrier;
use App\Filament\SuperAdmin\Resources\Carriers\Pages\ListCarriers;
use App\Filament\SuperAdmin\Resources\Carriers\Schemas\CarrierForm;
use App\Filament\SuperAdmin\Resources\Carriers\Tables\CarriersTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CarrierResource extends Resource
{
    protected static ?string $model = Carrier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Delivery';

    protected static ?string $modelLabel = 'Carrier / sub company';
    protected static ?string $pluralModelLabel = 'Carriers / sub companies';

    public static function form(Schema $schema): Schema
    {
        return CarrierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CarriersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListCarriers::route('/'),
            'create' => CreateCarrier::route('/create'),
            'edit'   => EditCarrier::route('/{record}/edit'),
        ];
    }
}