<?php

namespace App\Filament\SuperAdmin\Resources\Plans;

use App\Filament\SuperAdmin\Resources\Plans\Pages\ListPlans;
use App\Filament\SuperAdmin\Resources\Plans\RelationManagers\PlanFeaturesRelationManager;
use App\Filament\SuperAdmin\Resources\Plans\RelationManagers\PlanPricesRelationManager;
use App\Filament\SuperAdmin\Resources\Plans\Schemas\PlanForm;
use App\Filament\SuperAdmin\Resources\Plans\Tables\PlansTable;
use App\Models\Plans\Plan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'slug';

    protected static ?string $title = 'Billing';
    protected static string|UnitEnum|null $navigationGroup = 'Billing Management';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['prices', 'features']);
    }
    public static function form(Schema $schema): Schema
    {
        return PlanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PlanPricesRelationManager::class,
            PlanFeaturesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
