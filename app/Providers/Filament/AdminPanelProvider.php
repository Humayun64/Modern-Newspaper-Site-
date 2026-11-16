<?php

namespace App\Providers\Filament;

// Our own Dashboard subclass, so /admin lands on the newsroom overview.
use App\Filament\Admin\Pages\Dashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
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
        return $panel
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('amarDesh24.news')
            ->colors([
                'primary' => Color::Red,
            ])
            ->sidebarCollapsibleOnDesktop()

            /*
             * A few CSS tweaks to Filament's own chrome, injected at the end
             * of <head> so they land after the panel's stylesheet.
             *
             * A render hook rather than a compiled custom theme on purpose:
             * a theme means a Vite build step, and every future deploy would
             * then need `npm run build` before the admin looked right. This
             * is a handful of overrides — it does not deserve a build.
             *
             * The hook is named by its string, not the PanelsRenderHook
             * constant, because that class has moved between Filament major
             * versions and the string has not.
             */
            ->renderHook('panels::head.end', fn () => view('filament.admin-tweaks'))

            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\Filament\Admin\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\Filament\Admin\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\Filament\Admin\Widgets')
            ->widgets([])
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
