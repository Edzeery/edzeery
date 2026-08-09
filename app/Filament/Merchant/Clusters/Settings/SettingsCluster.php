<?php

namespace App\Filament\Merchant\Clusters\Settings;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SettingsCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;
    protected static string|UnitEnum|null $navigationGroup = 'Account Settings';
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;
    protected static bool $shouldRegisterSubNavigation = false;
    protected static ?int $navigationSort = 10;
}
