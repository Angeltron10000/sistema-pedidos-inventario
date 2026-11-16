<?php

namespace App\Filament\Resources\Pedidos\Pages;

use App\Filament\Actions\AccionesPedido;
use App\Filament\Resources\Pedidos\PedidoResource;
use App\Services\PedidoService;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

/**
 * Modificar un pedido (RF-30, RN-11): solo mientras está en "Recibido".
 * Se pueden cambiar los datos generales y los productos; el inventario se ajusta solo.
 */
class EditPedido extends EditRecord
{
    protected static string $resource = PedidoResource::class;

    public function getTitle(): string|Htmlable
    {
        return "Modificar pedido {$this->record->folio}";
    }

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

    /** Solo se modifican pedidos en "Recibido"; si no, se regresa al detalle. */
    public function mount(int|string $record): void
    {
        parent::mount($record);

        if (! $this->record->sePuedeEditar()) {
            $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
        }
    }

    /** Carga los productos del pedido en la tabla del formulario. */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['productos'] = $this->record->detalles()
            ->orderBy('id_detalle')
            ->get()
            ->map(fn ($detalle) => [
                'id_producto' => $detalle->id_producto,
                'cantidad'    => $detalle->cantidad,
                'notas'       => $detalle->notas,
            ])
            ->all();

        return $data;
    }

    /** Guarda con PedidoService, que ajusta el inventario y recalcula el total. */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(PedidoService::class)->actualizar($record, $data, auth()->user());
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return "Pedido {$this->record->folio} modificado. Nuevo total: $" . number_format((float) $this->record->total, 2);
    }

    protected function afterSave(): void
    {
        CreatePedido::avisarStockBajo($this->record);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
