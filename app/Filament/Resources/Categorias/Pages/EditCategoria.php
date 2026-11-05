<?php

namespace App\Filament\Resources\Categorias\Pages;

use App\Filament\Actions\CambiarActivoAction;
use App\Filament\Resources\Categorias\CategoriaResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCategoria extends EditRecord
{
    protected static string $resource = CategoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            CambiarActivoAction::make(),    // en lugar de "Eliminar"
        ];
    }
}
