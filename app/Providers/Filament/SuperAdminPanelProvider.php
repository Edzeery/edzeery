<?php

namespace App\Providers\Filament;

use App\Filament\SuperAdmin\Resources\Widgets\DashboardStats;
use App\Http\Middleware\Auth\PlatformRole;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class SuperAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('super-admin')
            ->path('super-admin')
            ->colors([
                'danger' => Color::Red,
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'primary' => Color::Green,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->databaseNotifications()
            ->discoverResources(in: app_path('Filament/SuperAdmin/Resources'), for: 'App\Filament\SuperAdmin\Resources')
            ->discoverPages(in: app_path('Filament/SuperAdmin/Pages'), for: 'App\Filament\SuperAdmin\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/SuperAdmin/Widgets'), for: 'App\Filament\SuperAdmin\Widgets')
            ->widgets([
                AccountWidget::class,
                DashboardStats::class,
                FilamentInfoWidget::class,
            ])
            ->profile(isSimple: false)
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
                PlatformRole::class . ':super_admin,admin,support_agent,tech_support',
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
