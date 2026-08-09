<?php

namespace App\Filament\Merchant\Resources\MyTeams;

use App\Filament\Merchant\Resources\MyTeams\Pages\CreateMyTeam;
use App\Filament\Merchant\Resources\MyTeams\Pages\EditMyTeam;
use App\Filament\Merchant\Resources\MyTeams\Pages\ListMyTeams;
use App\Filament\Merchant\Resources\MyTeams\Schemas\MyTeamForm;
use App\Filament\Merchant\Resources\MyTeams\Tables\MyTeamsTable;
use App\Models\Stores\Team\StoreMembership;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class MyTeamResource extends Resource
{
    protected static ?string $model = StoreMembership::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;
    protected static string|UnitEnum|null $navigationGroup = 'Store Management';
    protected static ?string $navigationLabel = 'Team';
    protected static ?string $title = 'Team';
    protected static ?string $slug = 'team';
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $tenantOwnershipRelationshipName = 'store';

    public static function getRecordTitleAttribute(): ?string
    {
        return parent::getRecordTitleAttribute();
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('titles.store_management');
    }
    public static function getNavigationLabel(): string
    {
        return __('titles.team');
    }
      public static function getModelLabel(): string
    {
        return __('titles.member');
    }
    public static function getTitleCaseModelLabel(): string
    {
        return __('titles.team');
    }

    public static function getTitleCasePluralModelLabel(): string
    {
        return __('titles.team');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->where('store_id', currentStoreId())
            ->where('user_id', '!=', user()->id)
            ->latest('created_at');

        $user = user();

        if (isStoreOwner($user) || isStoreAdmin($user)) {
            return $query;
        }

        if (isStoreManager($user)) {
            return $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('invited_by', $user->id);
            });
        }

        // STAFF يرى نفسه فقط
        return $query->where('user_id', $user->id);
    }


    public static function getNavigationBadge(): ?string
    {
        return optional(currentStore())->memberships()
            ->where('user_id', '!=', user()->id)
            ->count() ?? 0;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return Color::Blue;
    }

    public static function form(Schema $schema): Schema
    {
        return MyTeamForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MyTeamsTable::configure($table);
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
            'index' => ListMyTeams::route('/'),
            'create' => CreateMyTeam::route('/create'),
            'edit' => EditMyTeam::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
