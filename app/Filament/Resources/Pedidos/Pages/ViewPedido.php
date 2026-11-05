<?php

namespace App\Filament\Resources\Pedidos\Pages;

use App\Filament\Actions\AccionesPedido;
use App\Filament\Resources\Pedidos\PedidoResource;
use App\Models\Pedido;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPedido extends ViewRecord
{
    protected static string $resource = PedidoResource::class;

    /** Botones del detalle: avanzar estado, editar y cancelar. */
    protected function getHeaderActions(): array
    {
        return [
            AccionesPedido::avanzar(),
            EditAction::make()
                ->visible(fn (Pedido $record): bool => $record->sePuedeEditar()),
            AccionesPedido::cancelar(),
        ];
    }
}
