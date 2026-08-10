<?php

namespace App\Filament\SuperAdmin\Pages\Auth;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class EditProfile extends Page
{
    protected string $view = 'filament.pages.edit-profile';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $title = 'Edit my account';

    protected static string|UnitEnum|null $navigationGroup = 'Account';

    
}
