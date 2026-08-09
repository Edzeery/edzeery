<?php

namespace App\Filament\Merchant\Pages\Store\Schemas;

use App\Models\Stores\Store;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Support\Str;

use function PHPUnit\Framework\isString;

class MyStoreInfolist
{
    public static function make(Schema $infolist, Store $store): Schema
    {

        return $infolist
            ->record($store)
            ->schema([
                Tabs::make('StoreTabs')
                    ->persistTabInQueryString()

                    ->tabs([

                        /* ================= General ================= */
                        Tab::make('General')
                            ->schema([
                                Section::make()

                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextEntry::make('name')->label('Store Name'),
                                            TextEntry::make('slug'),
                                        ]),
                                        TextEntry::make('description')->columnSpanFull(),
                                        Grid::make(2)->schema([
                                            ImageEntry::make('logo')->disk('public'),
                                            ImageEntry::make('cover')->disk('public'),
                                        ]),

                                    ]),
                            ]),

                        /* ================= Settings ================= */
                        Tabs\Tab::make('Settings')->schema([
                            Section::make()->schema([
                                Grid::make(2)->schema([
                                    TextEntry::make('settings.currency'),
                                    TextEntry::make('settings.currency_symbol'),
                                    TextEntry::make('settings.language'),
                                    IconEntry::make('settings.inventory_tracking')
                                        ->boolean(),
                                    IconEntry::make('settings.guest_checkout')
                                        ->boolean(),

                                ]),
                            ]),
                        ]),

                        /* ================= SEO ================= */
                        Tab::make('SEO | Design ')->schema([
                            Section::make('SEO')->schema([
                                TextEntry::make('seo.meta_title'),
                                TextEntry::make('seo.meta_description'),
                                TextEntry::make('seo.meta_keywords'),
                                ImageEntry::make('seo.og_image')->disk('public'),
                            ]),

                            Section::make('Theme')->schema([
                                TextEntry::make('theme.theme'),
                                ColorEntry::make('theme.primary_color')
                                    ->copyable()
                                    ->copyMessage('Copied!')
                                    ->copyMessageDuration(1500),
                                ColorEntry::make('theme.secondary_color'),
                                TextEntry::make('theme.font_family'),
                            ])->columns(2),
                        ])->columns(2),

                        /* ================= Subscription ================= */
                        // Tabs\Tab::make('Subscription')->schema([

                        //     Grid::make(2)
                        //         ->schema([
                        //             Section::make('Subscription')
                        //                 ->schema(
                        //                     [
                        //                         Grid::make(3)
                        //                             ->schema([
                        //                                 TextEntry::make('subscription.plan.name')->label(__('titles.plan')),

                        //                                 TextEntry::make('status')
                        //                                     ->label(__('general.status'))
                        //                                     ->badge()
                        //                                     ->getStateUsing(fn($record) => $record->currentStatus()?->getLabel()
                        //                                         ??  'Unknown')
                        //                                     ->color(fn($record) =>  $record->currentStatus()?->filamentColor()
                        //                                         ?? 'gray')
                        //                                     ->icon(fn($record) => $record->currentStatus()?->icon()
                        //                                         ??   'heroicon-o-exclamation-circle')
                        //                                     ->iconColor(fn($record) => $record->currentStatus()?->filamentColor() ?? 'gray')
                        //                                     ->placeholder('—')
                        //                                     ->disabled(),

                        //                                 IconEntry::make('subscription.is_trial')
                        //                                     ->boolean()
                        //                                     ->visible(fn($record) => $record->user->onTrial()),
                        //                             ]),
                        //                         Grid::make(2)
                        //                             ->schema([
                        //                                 TextEntry::make('subscription.starts_at')->label(__('titles.start_at'))->date(),
                        //                                 TextEntry::make('subscription.ends_at')->label(__('titles.expire_at'))->date()->placeholder('—'),
                        //                             ]),

                        //                         Grid::make(4)

                        //                             ->schema([
                        //                                 TextEntry::make('subscription.plan.trial_days')
                        //                                     ->label(__('titles.trial_days'))
                        //                                     ->visible(fn($record) => $record->user?->onTrial())
                        //                                     ->badge(),
                        //                                 TextEntry::make('subscription.plan.max_stores')
                        //                                     ->badge()
                        //                                     ->label('Max Stores'),
                        //                                 TextEntry::make('subscription.planPrice.price')
                        //                                     ->badge()
                        //                                     ->label('Plan Price'),

                        //                                 TextEntry::make('subscription.planPrice.billing_period')
                        //                                     ->formatStateUsing(fn($state) => Str::upper($state)),

                        //                                 TextEntry::make('subscription.planPrice.currency')
                        //                                     ->formatStateUsing(fn($state) => __('currency.' . $state))
                        //                                     ->label('Currency'),

                        //                             ]),

                        //                     ]
                        //                 ),
                        //             Section::make('Plan features')
                        //                 ->schema([
                        //                     // KeyValueEntry::make('meta')->label('Plan features'),


                        //                     RepeatableEntry::make('subscription.plan.features')
                        //                         ->columns(4)
                        //                         ->extraAttributes([
                        //                             'style' => 'max-height: 380px; overflow-y: auto; padding: 1.2rem;',
                        //                         ])
                        //                         ->schema([
                        //                             TextEntry::make('name')
                        //                                 ->label(__('table.name'))
                        //                                 ->formatStateUsing(fn($state) => __($state))

                        //                                 ->columnSpan(2),
                        //                             TextEntry::make('pivot.value')
                        //                                 ->badge()
                        //                                 ->label(__('table.value'))
                        //                                 ->columnSpan(2),
                        //                             TextEntry::make('unit')
                        //                                 ->badge()
                        //                                 ->label(__('table.unit'))
                        //                                 ->placeholder('—'),

                        //                             TextEntry::make('description')
                        //                                 ->label(__('table.description'))
                        //                                 ->placeholder('—')
                        //                                 ->columnSpan(3),

                        //                         ]),
                        //                 ])
                        //         ])

                        // ]),

                        /* ================= Payments ================= */
                        // Tabs\Tab::make('Payments history')->schema([
                        //     Section::make()
                        //         ->schema([
                        //             RepeatableEntry::make('payments')
                        //                 ->label('Payments history')
                        //                 ->table([
                        //                     TableColumn::make(__('table.shop'))
                        //                     // ->alignment(Alignment::Center)
                        //                     ,
                        //                     TableColumn::make(__('table.plan')),
                        //                     TableColumn::make(__('table.gateway')),
                        //                     TableColumn::make(__('table.status')),
                        //                     TableColumn::make(__('table.amount')),
                        //                 ])

                        //                 ->schema([
                        //                     TextEntry::make('store.name'),
                        //                     TextEntry::make('plan.name')->placeholder('_'),
                        //                     TextEntry::make('gateway')
                        //                         // ->getStateUsing(fn($state) => Str::upper($state->gateway))
                        //                         ->badge()
                        //                         ->placeholder('_'),

                        //                     TextEntry::make('status')
                        //                         ->badge()
                        //                         ->label(fn($state) => $state->getLabel())
                        //                         ->icon(fn($state) => $state->icon())
                        //                         ->color(fn($state) => $state->filamentColor())
                        //                         ->iconColor(fn($state) => $state->filamentColor())
                        //                         ->placeholder('—'),
                        //                     TextEntry::make('amount')
                        //                         // ->getStateUsing(fn($state) => Str::upper($state->gateway))
                        //                         ->badge()
                        //                         ->placeholder('_'),
                        //                 ]),
                        //         ]),
                        // ]),


                    ]),
            ]);
    }
}
