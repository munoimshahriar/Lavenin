<?php

namespace App\Providers\Filament;

use App\Http\Middleware\AdminLocale;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('manage')
            ->login()
            ->brandName('Lavenin')
            ->favicon(asset('favicon.svg'))
            ->colors([
                'primary' => Color::hex('#76A9AD'),
                'danger' => Color::hex('#FF5722'),
            ])
            ->font('Nunito')
            ->navigationGroups([
                NavigationGroup::make('Sales'),
                NavigationGroup::make('Catalog'),
                NavigationGroup::make('Settings')->collapsed(),
            ])
            ->navigationItems([
                NavigationItem::make('View shop')->url('/', shouldOpenInNewTab: true)->icon('heroicon-o-arrow-top-right-on-square')->sort(99),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->plugin(
                BreezyCore::make()
                    ->myProfile(shouldRegisterUserMenu: true, slug: 'profile')
                    ->enableTwoFactorAuthentication(
                        force: fn () => (bool) auth()->user()?->hasRole('owner'), // 2FA mandatory for owners
                    )
            )
            ->databaseNotifications(false)
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
                AdminLocale::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
