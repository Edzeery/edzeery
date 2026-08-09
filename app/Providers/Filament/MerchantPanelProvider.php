<?php

namespace App\Providers\Filament;

use App\Filament\Merchant\Pages\CreateNewStore;
use App\Filament\Merchant\Pages\Store\EditStoreProfile;
use App\Http\Middleware\Merchant\EnsureMerchantAccess;
use App\Http\Middleware\Merchant\Store\EnsureHasStoreRole;
use App\Http\Middleware\Merchant\Store\EnsureStoreIsActive;
use App\Http\Middleware\Merchant\Store\EnsureStoreMembership;
use App\Http\Middleware\Merchant\Store\EnsureStoreResolved;
use App\Models\Stores\Store;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class MerchantPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('merchant')
            ->path('merchant')
            ->authGuard('merchant')
            ->colors([
                'danger' => Color::Red,
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'primary' => Color::Green,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->font('Poppins')

            ->databaseNotifications()
            ->discoverResources(in: app_path('Filament/Merchant/Resources'), for: 'App\Filament\Merchant\Resources')
            ->discoverPages(in: app_path('Filament/Merchant/Pages'), for: 'App\Filament\Merchant\Pages')
            ->discoverClusters(in: app_path('Filament/Merchant/Clusters'), for: 'App\Filament\Merchant\Clusters')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Merchant/Widgets'), for: 'App\Filament\Merchant\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                EnsureMerchantAccess::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])

            ->profile()
            ->databaseTransactions()
            ->collapsibleNavigationGroups(false)
            ->sidebarCollapsibleOnDesktop()
            ->userMenuItems([
                Action::make('store-switcher')
                    ->label(fn() => currentStore()?->name ?? 'Select Store')
                    ->tooltip(fn() => (user()->stores()->count() ?? 0) <= 1 ? '' : 'Click to change store.')
                    ->icon(fn() => (user()->stores()->count() ?? 0) <= 1 ? 'heroicon-o-building-storefront' :  'heroicon-o-arrows-right-left')
                    ->badge(fn() => (user()->stores()->count() ?? 0) <= 1 ? null :  user()->stores()->count() ?? 0)
                    ->disabled(fn() => (user()->stores()->count() ?? 0) <= 1)
                    ->url(fn() => route('choose-store')),

                Action::make('create-store')
                    ->label('Create Store')
                    ->tooltip('Click to create store.')
                    ->icon('heroicon-o-plus-circle')
                    ->badge(fn() =>  user()->stores()->count() ?? 0)
                    ->visible(
                        fn() =>
                        currentStore()?->canCreateMultiStore()
                    )
                    ->url(fn() => route('filament.merchant.tenant.registration')),
            ])


            ->tenant(Store::class, slugAttribute: 'slug')
            ->tenantRegistration(CreateNewStore::class)
            ->tenantProfile(EditStoreProfile::class)
            ->tenantMenuItems([
                Action::make('create-store')
                    ->label('Create Store')
                    ->tooltip('Click to create store.')
                    ->icon('heroicon-o-plus-circle')
                    ->badge(fn() =>  user()->stores()->count() ?? 0)
                    ->visible(
                        fn() =>
                        currentStore()->canCreateMultiStore()
                    )
                    ->url(fn() => route('filament.merchant.tenant.registration')),

            ])
            ->searchableTenantMenu()
            ->tenantMiddleware(
                [
                    EnsureStoreResolved::class,
                    EnsureStoreMembership::class,
                    EnsureHasStoreRole::class . ':owner,admin,manager,staff',
                    EnsureStoreIsActive::class,
                ]
            )

        ;
    }
}
