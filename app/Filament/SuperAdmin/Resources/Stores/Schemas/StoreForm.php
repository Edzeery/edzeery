<?php

namespace App\Filament\SuperAdmin\Resources\Stores\Schemas;

use App\Models\Plans\Plan;
use App\Models\Plans\PlanFeature;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class StoreForm
{
    use InteractsWithForms;
    public ?array $data = [];

    public static function configure(Schema $schema): Schema
    {
        $plans = Plan::with('features')
            ->where('is_active', true)
            ->get();
        return $schema
            ->statePath('data')
            ->schema([
                Wizard::make([
                    /* ================= Step 1 ================= */
                    Step::make('Store Information')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(
                                        fn($state, $set) =>
                                        $set('slug', Str::slug($state))
                                    ),

                                TextInput::make('slug')
                                    ->required()
                                    ->readOnly()
                                    ->unique('stores', 'slug'),
                            ]),


                            Textarea::make('description'),
                            Grid::make(2)->schema([
                                FileUpload::make('logo')
                                    ->image()
                                    ->disk('public')
                                    ->directory('img/stores/logos')
                                    ->visibility('public')
                                    ->maxFiles(1),

                                FileUpload::make('cover')
                                    ->image()
                                    ->disk('public')
                                    ->directory('img/stores/cover')
                                    ->visibility('public')
                                    ->maxFiles(1),

                            ]),

                        ]),

                    /* ================= Step 2 ================= */
                    Step::make('General Settings')
                        ->description('Basic configuration for your store')
                        ->schema([
                            Grid::make(2)->schema([
                                Select::make('currency')
                                    ->options([
                                        'DZD' => 'DZD',
                                        'USD' => 'USD',
                                        'EUR' => 'EUR',
                                    ])
                                    ->default([0])
                                    ->required(),

                                TextInput::make('currency_symbol')
                                    ->default('DA'),

                                Select::make('language')
                                    ->options([
                                        'ar' => 'Arabic',
                                        'en' => 'English',
                                        'fr' => 'French',
                                    ])
                                    ->default('ar'),
                            ]),
                            Grid::make(2)->schema([
                                Toggle::make('inventory_tracking')
                                    ->default(true),

                                Toggle::make('guest_checkout')
                                    ->default(true),
                            ]),

                        ]),

                    /* ================= Step 3 ================= */
                    Step::make('SEO')
                        ->schema([
                            TextInput::make('meta_title'),
                            Textarea::make('meta_description'),
                            TextInput::make('meta_keywords'),
                            FileUpload::make('og_image')
                                ->image()
                                ->disk('public')
                                ->directory('img/stores/og_images')
                                ->visibility('public')
                                ->maxFiles(1),
                        ]),

                    /* ================= Step 4 ================= */
                    Step::make('Design')
                        ->description('Choose how your store looks')
                        ->schema([
                            ColorPicker::make('primary_color')
                                ->default('#000000'),

                            ColorPicker::make('secondary_color')
                                ->default('#ffffff'),

                            Select::make('font_family')
                                ->options([
                                    'Cairo' => 'Cairo',
                                    'Roboto' => 'Roboto',
                                ])
                                ->default('Cairo'),
                        ]),

                    // /* ================= Step 5 ================= */
                    // Hidden::make('plan_id')->required(),
                    // Step::make('Subscription Plan')
                    //     ->description('Choose the plan that fits your business')
                    //     ->schema([
                    //         ToggleButtons::make('billing_period')
                    //             ->options([
                    //                 'monthly' => 'Monthly',
                    //                 'yearly' => 'Yearly (Save more)',
                    //             ])
                    //             ->default('monthly')
                    //             ->required(),

                    //         Hidden::make('plan_id')->required(),

                    //         ViewField::make('plans')
                    //             ->view('filament.components.plan-selector')
                    //             ->viewData([
                    //                 'plans' => Plan::with(['features', 'prices'])
                    //                     ->where('is_active', true)
                    //                     ->get(),
                    //             ])->columns(4),
                    //         ViewField::make('plans')
                    //             ->view('filament.components.plan-selector')
                    //             ->viewData([
                    //                 'plans' => $plans,
                    //             ])->columns(4),   // يمكن تغييره لعرض 2 أو 3 في الصف
                    //     ]),

                ])
                    ->submitAction(
                        Action::make('createStore')
                            ->label('🚀 Launch My Store')
                            ->color('primary')
                            ->size('lg')
                            ->action('createStore')
                    )->columnSpanFull(),

            ]);
    }
}
