<?php

namespace App\Filament\Resources\Productos\Pages;

use App\Filament\Resources\Productos\ProductoResource;
use App\Models\MovimientoInventario;
use Filament\Resources\Pages\CreateRecord;

class CreateProducto extends CreateRecord
{
    protected static string $resource = ProductoResource::class;

    /**
     * Después de guardar un producto nuevo, su existencia inicial
     * se registra como una "entrada" en los movimientos de inventario.
     */
    protected function afterCreate(): void
    {
        if ($this->record->existencia > 0) {
            MovimientoInventario::create([
                'id_producto' => $this->record->id_producto,
                'id_usuario'  => auth()->id(),
                'tipo'        => 'entrada',
                'cantidad'    => $this->record->existencia,
                'fecha_hora'  => now(),
                'motivo'      => 'Inventario inicial al registrar el producto',
            ]);
        }
    }

    /** Al terminar, regresa al listado de productos. */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
