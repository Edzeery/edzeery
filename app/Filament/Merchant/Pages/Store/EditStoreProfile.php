<?php

namespace App\Filament\Merchant\Pages\Store;

use Filament\Pages\Tenancy\EditTenantProfile;
use App\Models\Stores\Store;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EditStoreProfile extends EditTenantProfile
{

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $store = $this->tenant;

        return array_merge($data, [
            'settings' => $store->settings ?? [],
            'seo'      => $store->seo ?? [],
            'theme'    => $store->theme ?? [],
        ]);
    }


    public static function getLabel(): string
    {
        return 'Edit My Store';
    }
 
    public function form(Schema $schema): Schema
    {

        $data = currentStore();
        return $schema
            ->components([
                Tabs::make('EditStoreTabs')
                    ->persistTabInQueryString()
                    ->tabs([
                        /* ========= General ========= */
                        Tab::make('General')
                            ->icon('heroicon-o-building-storefront')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('name')
                                        ->required()
                                        ->maxLength(120)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(
                                            fn($state, $set) =>
                                            $set('slug', Str::slug($state))
                                        ),

                                    TextInput::make('slug')
                                        ->required()
                                        ->alphaDash()
                                        ->maxLength(150)
                                        ->unique(ignoreRecord: true),
                                ]),

                                Textarea::make('description')
                                    ->rows(4)
                                    ->columnSpanFull(),

                                Grid::make(2)->schema([
                                    FileUpload::make('logo')
                                        ->disk('public')
                                        ->directory('stores/logos')
                                        ->image()
                                        ->imagePreviewHeight(120)
                                        ->maxSize(2048),

                                    FileUpload::make('cover')
                                        ->disk('public')
                                        ->directory('stores/covers')
                                        ->image()
                                        ->imagePreviewHeight(120)
                                        ->maxSize(4096),
                                ]),
                            ]),

                        /* ========= Settings ========= */
                        Tab::make('Settings')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('settings.currency')
                                        ->default('USD')
                                        ->maxLength(5),

                                    TextInput::make('settings.currency_symbol')
                                        ->default('$')
                                        ->maxLength(5),

                                    Toggle::make('settings.inventory_tracking')
                                        ->default(true),

                                    Toggle::make('settings.guest_checkout')
                                        ->default(false),
                                ]),
                            ]),

                        /* ========= SEO ========= */
                        Tab::make('SEO')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                TextInput::make('seo.meta_title')
                                    ->maxLength(60)
                                    ->helperText('Recommended: 50–60 characters'),

                                Textarea::make('seo.meta_description')
                                    ->maxLength(160)
                                    ->rows(3),

                                TextInput::make('seo.meta_keywords'),

                                FileUpload::make('seo.og_image')
                                    ->disk('public')
                                    ->directory('stores/seo')
                                    ->image()
                                    ->imagePreviewHeight(120),
                            ]),

                        /* ========= Theme ========= */
                        Tab::make('Theme')
                            ->icon('heroicon-o-paint-brush')
                            ->schema([
                                ColorPicker::make('theme.primary_color')
                                    ->default('#16a34a'),

                                ColorPicker::make('theme.secondary_color'),

                                TextInput::make('theme.font_family')
                                    ->placeholder('Inter, Cairo, Poppins…'),
                            ]),
                    ])

            ]);
    }
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Store profile updated successfully';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['settings'] = $data['settings'] ?? [];
        $data['seo'] = $data['seo'] ?? [];
        $data['theme'] = $data['theme'] ?? [];

        return $data;
    }
}
