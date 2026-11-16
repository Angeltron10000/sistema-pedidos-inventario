<?php

namespace App\Filament\Resources\Usuarios\Pages;

use App\Filament\Resources\Usuarios\UsuarioResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUsuario extends CreateRecord
{
    protected static string $resource = UsuarioResource::class;

    /** Al terminar, regresa al listado de usuarios. */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
