<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard;

/**
 * Página de inicio del panel (Escritorio) con los indicadores del día (RF-38).
 * Los indicadores están en app/Filament/Widgets.
 */
class Escritorio extends Dashboard
{
    protected static ?string $title = 'Escritorio';

    /** Saludo con el nombre y el rol del usuario. */
    public function getSubheading(): ?string
    {
        $usuario = auth()->user();

        return 'Hola, ' . $usuario->nombre . ' · Rol: ' . $usuario->rol?->nombre . ' · ' . now()->locale('es')->isoFormat('dddd D [de] MMMM, HH:mm');
    }
}
