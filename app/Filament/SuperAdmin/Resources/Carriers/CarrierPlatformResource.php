<?php

namespace App\Filament\SuperAdmin\Resources\Carriers;

use App\Domains\Shipping\Models\CarrierPlatform;
use App\Filament\SuperAdmin\Resources\Carriers\Pages\CreateCarrierPlatform;
use App\Filament\SuperAdmin\Resources\Carriers\Pages\EditCarrierPlatform;
use App\Filament\SuperAdmin\Resources\Carriers\Pages\ListCarrierPlatforms;
use App\Filament\SuperAdmin\Resources\Carriers\Schemas\CarrierPlatformForm;
use App\Filament\SuperAdmin\Resources\Carriers\Tables\CarrierPlatformsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CarrierPlatformResource extends Resource
{
    protected static ?string $model = CarrierPlatform::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Delivery';

    protected static ?string $modelLabel = 'Carrier platform';
    protected static ?string $pluralModelLabel = 'Carrier platforms';

    public static function form(Schema $schema): Schema
    {
        return CarrierPlatformForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CarrierPlatformsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListCarrierPlatforms::route('/'),
            'create' => CreateCarrierPlatform::route('/create'),
            'edit'   => EditCarrierPlatform::route('/{record}/edit'),
        ];
    }
}
