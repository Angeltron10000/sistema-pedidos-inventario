<?php

namespace App\Filament\Resources\Pedidos\Pages;

use App\Filament\Actions\AccionesPedido;
use App\Filament\Resources\Pedidos\PedidoResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPedido extends EditRecord
{
    protected static string $resource = PedidoResource::class;

    /**
     * Un pedido no se elimina: se cancela (así se conserva su historial).
     */
    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            AccionesPedido::cancelar(),
        ];
    }

    /** Solo se editan pedidos en "Recibido"; si no, se regresa al detalle. */
    public function mount(int|string $record): void
    {
        parent::mount($record);

        if (! $this->record->sePuedeEditar()) {
            $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
