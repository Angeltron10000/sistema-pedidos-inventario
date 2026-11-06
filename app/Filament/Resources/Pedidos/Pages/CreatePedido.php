<?php

namespace App\Filament\Resources\Pedidos\Pages;

use App\Filament\Resources\Pedidos\PedidoResource;
use App\Services\PedidoService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePedido extends CreateRecord
{
    protected static string $resource = PedidoResource::class;

    protected static ?string $title = 'Registrar pedido';

    /**
     * En lugar de guardar el formulario tal cual, se usa PedidoService, que genera el folio,
     * copia los precios, calcula el total, descuenta existencias y guarda el historial.
     */
    protected function handleRecordCreation(array $data): Model
    {
        return app(PedidoService::class)->crear($data, auth()->user());
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return "Pedido {$this->record->folio} registrado. Total: $" . number_format((float) $this->record->total, 2);
    }

    /** Al terminar, muestra el detalle del pedido recién creado. */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
