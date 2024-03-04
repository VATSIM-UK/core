<?php

namespace App\Providers\Filament;

use App\Http\Middleware\FilamentAccessMiddleware;
use App\Http\Middleware\TrackInactivity;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                TrackInactivity::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                FilamentAccessMiddleware::class,
            ])
            ->navigationGroups([
                NavigationGroup::make('Technology'),
            ])
            ->navigationItems([
                NavigationItem::make('Legacy Admin Panel')
                    ->url(fn () => route('adm.index')) // This is a closure as routes may not have been registered yet
                    ->icon('heroicon-o-clock')
                    ->visible(fn () => request()->user()->hasPermissionTo('adm')),
                NavigationItem::make('Horizon')
                    ->group('Technology')
                    ->icon('heroicon-o-bars-arrow-down')
                    ->url(fn () => route('horizon.index'))
                    ->visible(fn () => request()->user()->can('viewHorizon')),
                NavigationItem::make('Telescope')
                    ->group('Technology')
                    ->icon('heroicon-o-magnifying-glass')
                    ->url(fn () => route('telescope'))
                    ->visible(fn () => request()->user()->can('viewTelescope')),
            ]);
    }
}
