<?php

namespace App\Filament\Merchant\Resources\MyTeams\Schemas;

use App\Enums\Store\StoreRoleEnum;
use App\Models\Locations\City;
use App\Models\Locations\State;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class MyTeamForm
{

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make("Basic Info")
                    ->icon(Heroicon::User)
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->afterStateHydrated(function ($component, $state, $record) {
                                // جلب اسم المستخدم المرتبط
                                $component->state($record?->user?->name);
                            })
                            ->dehydrateStateUsing(fn($state, $record) => $state), // يمكن حفظه إذا أردت
                        TextInput::make('email')
                            ->label('Email')
                            ->required()
                            ->afterStateHydrated(fn($component, $state, $record) => $component->state($record?->user?->email))
                            ->dehydrateStateUsing(fn($state, $record) => $state),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required(fn($get, $record) => $record === null) // مطلوب فقط عند الإنشاء
                            ->dehydrateStateUsing(fn($state) => $state) // ترك النص كما هو
                            ->hint(fn($get, $record) => $record ? 'Leave blank to keep current password' : null)

                    ])
                    ->columns(2),

                Section::make("Location")
                    ->icon(Heroicon::MapPin)
                    ->schema([
                        Select::make('country_id')
                            ->label('Country')
                            ->options(fn() => countries())
                            ->afterStateHydrated(fn($component, $state, $record) => $component->state($record?->user?->country_id))
                            ->dehydrateStateUsing(fn($state, $record) => $state)
                            ->reactive()
                            ->afterStateUpdated(fn($state, callable $set) => [$set('state_id', null), $set('city_id', null)])
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('state_id')
                            ->label('State / Wilaya')
                            ->options(fn(callable $get) => $get('country_id') ? State::whereCountryId($get('country_id'))->pluck('name', 'id') : [])
                            ->afterStateHydrated(fn($component, $state, $record) => $component->state($record?->user?->state_id))
                            ->dehydrateStateUsing(fn($state, $record) => $state)
                            ->reactive()
                            ->afterStateUpdated(fn($state, callable $set) => $set('city_id', null))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('city_id')
                            ->label('City')
                            ->options(fn(callable $get) => $get('state_id') ? City::whereStateId($get('state_id'))->pluck('name', 'id') : [])
                            ->afterStateHydrated(fn($component, $state, $record) => $component->state($record?->user?->city_id))
                            ->dehydrateStateUsing(fn($state, $record) => $state)
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->columns(3),


                Section::make("Role & Status")
                    ->schema([

                        Select::make('store_role')
                            ->label('Role')
                            ->options(
                                fn() => collect(StoreRoleEnum::options())
                                    ->reject(fn($label, $value) => $value === StoreRoleEnum::OWNER->value)
                                    ->toArray()
                            )
                            ->required()
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if (!$record?->user) return;

                                // جلب الدور الأول للمستخدم بالنسبة للـ guard 'merchant'
                                $role = $record->membershipRole();
                                if ($role) {
                                    $component->state($role->name);
                                }
                            })
                            ->reactive(),
                        Grid::make(4)
                            ->schema(
                                [
                                    Actions::make([
                                        Action::make('select_all_permissions')
                                            ->label('Select All')
                                            ->icon('heroicon-o-check-circle')
                                            ->action(function (callable $get, callable $set) {
                                                $role = $get('store_role');

                                                if (! $role) {
                                                    return;
                                                }

                                                $allPermissions = \App\Support\StoreRoles::permissions(
                                                    StoreRoleEnum::from($role)
                                                );

                                                $set('permissions', $allPermissions);
                                            })->visible(fn(callable $get) => $get('store_role') !== StoreRoleEnum::STAFF->value),

                                        Action::make('deselect_all_permissions')
                                            ->label('Deselect All')
                                            ->icon('heroicon-o-x-circle')
                                            ->color('danger')
                                            ->action(fn(callable $set) => $set('permissions', [])),
                                    ]),

                                    Actions::make([
                                        Action::make('reset_permissions')
                                            ->label('Reset to Role Default')
                                            ->icon('heroicon-o-arrow-path')
                                            ->color('gray')
                                            ->requiresConfirmation()
                                            ->action(function (callable $get, callable $set) {
                                                $role = $get('store_role');

                                                if (! $role) {
                                                    return;
                                                }

                                                $defaultPermissions = \App\Support\StoreRoles::permissions(
                                                    StoreRoleEnum::from($role)
                                                );

                                                $set('permissions', $defaultPermissions);
                                            }),
                                    ]),


                                    Toggle::make('is_active')
                                        ->label('Active')
                                        ->default(true),
                                ]
                            )->columnSpanFull(),
                        Section::make("Permissions")
                            ->schema(function (callable $get) {
                                $role = $get('store_role');
                                if (!$role) return [];

                                // جلب جميع الصلاحيات الخاصة بالدور
                                $allPermissions = \App\Support\StoreRoles::permissions(StoreRoleEnum::from($role));

                                // تقسيم الصلاحيات حسب النوع (نفترض أن الصلاحية تبدأ بالمجموعة: store.create, product.update ...)
                                $groupedPermissions = collect($allPermissions)->groupBy(function ($permission) {
                                    return explode('.', $permission)[0]; // store, product, orders ...
                                });

                                $sections = [];

                                foreach ($groupedPermissions as $groupKey => $permissions) {
                                    $label = ucfirst($groupKey) . " Permissions";

                                    $sections[] = Section::make($label)
                                        ->schema([
                                            CheckboxList::make("permissions_$groupKey")
                                                ->label($label)
                                                ->options($permissions->mapWithKeys(fn($p) => [$p => __("permissions.$p")])->toArray())
                                                ->afterStateHydrated(function ($component, $state, $record) use ($permissions) {
                                                    if (!$record?->user) return;

                                                    // فقط عرض الصلاحيات الحالية التي يمتلكها المستخدم ضمن هذه المجموعة
                                                    $component->state(
                                                        $record->user->getAllPermissions()
                                                            ->pluck('name')
                                                            ->filter(fn($p) => in_array($p, $permissions->toArray()))
                                                            ->toArray()
                                                    );
                                                })
                                                ->reactive(),

                                        ]);
                                }

                                return $sections;
                            })->columns(4)
                            ->columnSpanFull(),

                    ])
                    ->columnSpan(2)
                    ->columns(2),
            ]);
    }
}
