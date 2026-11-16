<?php

namespace App\Filament\Resources\Productos\Pages;

use App\Filament\Actions\CambiarActivoAction;
use App\Filament\Resources\Productos\ProductoResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

/**
 * Editar un producto: nombre, descripción, categoría, precio, existencia mínima y activo.
 * La existencia ya no se cambia aquí: se usan los botones "Registrar entrada" y
 * "Ajustar existencia" (ver App\Filament\Actions\AccionesInventario).
 */
class EditProducto extends EditRecord
{
    protected static string $resource = ProductoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            CambiarActivoAction::make(),    // en lugar de "Eliminar"
        ];
    }

    /** Al terminar, regresa al listado de productos. */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
