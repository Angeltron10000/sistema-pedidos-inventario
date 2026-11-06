<?php

namespace App\Filament\Resources\Productos\Pages;

use App\Filament\Actions\CambiarActivoAction;
use App\Filament\Resources\Productos\ProductoResource;
use App\Models\MovimientoInventario;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProducto extends EditRecord
{
    protected static string $resource = ProductoResource::class;

    /** Existencia que tenía el producto antes de editarlo. */
    protected int $existenciaAnterior = 0;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            CambiarActivoAction::make(),    // en lugar de "Eliminar"
        ];
    }

    protected function beforeSave(): void
    {
        $this->existenciaAnterior = (int) $this->record->existencia;
    }

    /**
     * Si se cambió la existencia a mano, se registra como "ajuste"
     * (positivo = sobrante, negativo = merma) en los movimientos de inventario.
     */
    protected function afterSave(): void
    {
        $diferencia = (int) $this->record->existencia - $this->existenciaAnterior;

        if ($diferencia !== 0) {
            MovimientoInventario::create([
                'id_producto' => $this->record->id_producto,
                'id_usuario'  => auth()->id(),
                'tipo'        => 'ajuste',
                'cantidad'    => $diferencia,
                'fecha_hora'  => now(),
                'motivo'      => 'Ajuste manual desde el formulario de producto',
            ]);
        }
    }

    /** Al terminar, regresa al listado de productos. */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
