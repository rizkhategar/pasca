<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $shieldPlugin = null;

        foreach ([
            \BezhanSalleh\FilamentShield\FilamentShieldPlugin::class,
            \BezhanSalleh\FilamentShield\ShieldPlugin::class,
        ] as $pluginClass) {
            if (class_exists($pluginClass)) {
                $shieldPlugin = $pluginClass::make()
                    ->navigationLabel('Roles')
                    ->navigationGroup('Filament Shield')
                    ->navigationSort(1)
                    ->registerNavigation(true);
                break;
            }
        }

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->favicon(asset('logo_unwnobg.png'))
            ->brandName('Pascasarjana UNW')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->plugins(array_filter([
                $shieldPlugin,
            ]))
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Home')
                    ->collapsed(false),

                NavigationGroup::make()
                    ->label('Profile')
                    ->collapsed(false),

                NavigationGroup::make()
                    ->label('SINTA Integration')
                    ->collapsed(false),

                NavigationGroup::make()
                    ->label('Contacts')
                    ->collapsed(false),

                NavigationGroup::make()
                    ->label('Users')
                    ->collapsed(false),

                NavigationGroup::make()
                    ->label('Filament Shield')
                    ->collapsed(false),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
