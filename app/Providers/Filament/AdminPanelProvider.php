<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\CambiarContrasena;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Escritorio;
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
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)                       // inicio de sesión con la tabla "usuario"
            ->profile(CambiarContrasena::class, isSimple: false)   // menú del usuario → Cambiar contraseña (RF-08)
            ->brandName('Sistema de Pedidos')
            ->colors([
                'primary' => Color::Orange,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Escritorio::class,                      // inicio con indicadores (RF-38)
            ])
            // Orden de los grupos del menú lateral
            ->navigationGroups([
                'Operación',
                'Catálogos',
                'Inventario',
                'Administración',
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            // Debajo del inicio de sesión: enlace para que el cliente consulte su pedido (rol "Cliente")
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): HtmlString => new HtmlString(
                    '<p style="text-align:center; font-size:0.875rem; margin-top:0.5rem;">¿Eres cliente? '
                    . '<a href="' . e(route('consulta.formulario')) . '" style="color:rgb(234 88 12); font-weight:600;">Consulta tu pedido aquí</a></p>'
                ),
            )
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
