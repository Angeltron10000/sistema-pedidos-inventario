<?php

namespace App\Filament\Resources\Clientes\Pages;

use App\Filament\Actions\CambiarActivoAction;
use App\Filament\Resources\Clientes\ClienteResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCliente extends EditRecord
{
    protected static string $resource = ClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            CambiarActivoAction::make(),    // en lugar de "Eliminar"
        ];
    }
}
