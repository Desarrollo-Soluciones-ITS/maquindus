<?php

namespace App\Providers\Filament;

use Illuminate\Contracts\View\View;

use App\Filament\Pages\GlobalSearchPage;
use App\Filament\Pages\ProfileSettings;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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


class DashboardPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('dashboard')
            ->path('')
            ->login()
            ->profile(ProfileSettings::class)
            ->globalSearch(false)
            ->pages([
                GlobalSearchPage::class
            ])
            ->colors([
                'primary' => Color::Blue,
            ])
            
            ->brandLogo(new \Illuminate\Support\HtmlString(
                '<img src="'.asset('images/logo (1).png').'" alt="Maquindus logo" style="display:inline-block;vertical-align:middle;" />'
                .'<span class="fi-brand-title" style="margin-left:.25rem;">Gestor De Archivos Mquindus</span>'
            ))
            ->brandLogoHeight('2rem')
            ->resourceEditPageRedirect('index')
            ->resourceCreatePageRedirect('index')
            ->readOnlyRelationManagersOnResourceViewPagesByDefault(false)
            ->sidebarWidth('15rem')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
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
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->viteTheme('resources/css/filament/dashboard/theme.css');
    }
}
