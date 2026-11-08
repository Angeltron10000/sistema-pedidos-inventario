<?php

namespace App\Filament\Actions;

use App\Models\Pedido;
use App\Services\PedidoService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

/**
 * Botones para cambiar el estado de un pedido.
 * Son el equivalente a los botones "Aprobar" y "Rechazar" del proyecto IDEAS de clase.
 */
class AccionesPedido
{
    /**
     * Botón que pasa el pedido al siguiente estado:
     * Recibido → En preparación → Listo → Entregado.
     */
    public static function avanzar(): Action
    {
        return Action::make('avanzarEstado')
            ->label(fn (Pedido $record): string => 'Pasar a ' . ($record->siguienteEstado()?->nombre ?? ''))
            ->icon('heroicon-o-arrow-right-circle')
            ->color('success')
            ->visible(fn (Pedido $record): bool => auth()->user()?->can('avanzar', $record) ?? false)   // RN-08 y RN-25
            ->requiresConfirmation()
            ->modalHeading(fn (Pedido $record): string => "Cambiar estado del pedido {$record->folio}")
            ->modalDescription(fn (Pedido $record): string => "El pedido pasará de \"{$record->estado->nombre}\" a \"{$record->siguienteEstado()?->nombre}\".")
            ->modalSubmitActionLabel('Sí, cambiar estado')
            ->action(function (Pedido $record): void {
                $pedido = app(PedidoService::class)->avanzarEstado($record, auth()->user());

                Notification::make()
                    ->title("Pedido {$pedido->folio}: ahora está \"{$pedido->estado->nombre}\"")
                    ->success()
                    ->send();
            });
    }

    /**
     * Botón que abre el comprobante del pedido en otra pestaña, listo para imprimir (RF-29).
     */
    public static function comprobante(): Action
    {
        return Action::make('comprobante')
            ->label('Comprobante')
            ->icon('heroicon-o-printer')
            ->color('gray')
            ->url(fn (Pedido $record): string => route('pedidos.comprobante', $record), shouldOpenInNewTab: true);
    }

    /**
     * Botón que cancela el pedido, pide el motivo y regresa las existencias.
     */
    public static function cancelar(): Action
    {
        return Action::make('cancelarPedido')
            ->label('Cancelar')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (Pedido $record): bool => auth()->user()?->can('cancelar', $record) ?? false)  // RN-09 y RN-25
            ->modalHeading(fn (Pedido $record): string => "Cancelar el pedido {$record->folio}")
            ->modalDescription('Los productos del pedido regresarán al inventario.')
            ->schema([
                Textarea::make('motivo')
                    ->label('Motivo de la cancelación')
                    ->placeholder('Ejemplo: el cliente ya no quiso esperar')
                    ->required()
                    ->maxLength(200),
            ])
            ->modalSubmitActionLabel('Cancelar pedido')
            ->modalCancelActionLabel('Regresar')
            ->action(function (Pedido $record, array $data): void {
                $pedido = app(PedidoService::class)->cancelar($record, $data['motivo'], auth()->user());

                Notification::make()
                    ->title("Pedido {$pedido->folio} cancelado")
                    ->body('Las existencias de sus productos se regresaron al inventario.')
                    ->warning()
                    ->send();
            });
    }
}
