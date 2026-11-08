<?php

namespace App\Filament\Resources\Usuarios\Pages;

use App\Filament\Actions\CambiarActivoAction;
use App\Filament\Resources\Usuarios\UsuarioResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUsuario extends EditRecord
{
    protected static string $resource = UsuarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            CambiarActivoAction::make(),    // en lugar de "Eliminar"
        ];
    }

    /** Al terminar, regresa al listado de usuarios. */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
