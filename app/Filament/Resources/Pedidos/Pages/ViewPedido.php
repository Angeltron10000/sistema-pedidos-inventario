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

    /** Botones del detalle: avanzar estado, comprobante, modificar y cancelar. */
    protected function getHeaderActions(): array
    {
        return [
            AccionesPedido::avanzar(),
            AccionesPedido::comprobante(),
            EditAction::make()
                ->label('Modificar')
                ->visible(fn (Pedido $record): bool => auth()->user()?->can('update', $record) ?? false),
            AccionesPedido::cancelar(),
        ];
    }
}
