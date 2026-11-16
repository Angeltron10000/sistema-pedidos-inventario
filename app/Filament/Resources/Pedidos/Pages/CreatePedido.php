<?php

namespace App\Filament\Resources\Pedidos\Pages;

use App\Filament\Resources\Pedidos\PedidoResource;
use App\Models\Configuracion;
use App\Services\PedidoService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class CreatePedido extends CreateRecord
{
    protected static string $resource = PedidoResource::class;

    protected static ?string $title = 'Registrar pedido';

    /** Debajo del título se ve si el negocio está abierto (RN-27). */
    public function getSubheading(): string|Htmlable|null
    {
        $configuracion = Configuracion::actual();

        return $configuracion->estaAbierto()
            ? 'Horario de servicio: ' . $configuracion->textoHorario() . ' · Abierto'
            : 'CERRADO: el horario de servicio es de ' . $configuracion->textoHorario() . '. No se pueden registrar pedidos ahora.';
    }

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

    /** Si algún producto quedó con stock bajo o agotado, se avisa (RF-21, RN-20). */
    protected function afterCreate(): void
    {
        self::avisarStockBajo($this->record);
    }

    /** Aviso amarillo con los productos del pedido que quedaron en stock bajo. */
    public static function avisarStockBajo(Model $pedido): void
    {
        $productos = app(PedidoService::class)->productosConStockBajo($pedido);

        if ($productos->isEmpty()) {
            return;
        }

        Notification::make()
            ->title('Alerta de stock bajo')
            ->body($productos->map(fn ($p) => $p->existencia === 0
                ? "{$p->nombre}: AGOTADO"
                : "{$p->nombre}: quedan {$p->existencia} (mínimo {$p->stock_minimo})")->implode("\n"))
            ->warning()
            ->persistent()
            ->send();
    }

    /** Al terminar, muestra el detalle del pedido recién creado. */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
