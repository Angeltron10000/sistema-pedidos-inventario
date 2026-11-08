<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Pedidos\PedidoResource;
use App\Models\Pedido;
use App\Services\PedidoService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\ValidationException;
use UnitEnum;

/**
 * Tablero de cocina (RF-36): los pedidos activos en tres columnas
 * (Recibido, En preparación y Listo) para irlos preparando en orden de llegada.
 *
 * - Cocina pasa los pedidos a "En preparación" y a "Listo".
 * - Cajero/Mesero entrega los que están "Listo".
 * - El Administrador puede hacer todo.
 * La página se actualiza sola cada 15 segundos.
 */
class TableroCocina extends Page
{
    protected string $view = 'filament.pages.tablero-cocina';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFire;

    protected static ?string $navigationLabel = 'Tablero de cocina';

    protected static ?string $title = 'Tablero de cocina';

    protected static ?string $slug = 'tablero-cocina';

    protected static string|UnitEnum|null $navigationGroup = 'Operación';

    protected static ?int $navigationSort = -1;          // aparece antes que "Pedidos"

    /** Número de pedidos activos junto a "Tablero de cocina" en el menú. */
    public static function getNavigationBadge(): ?string
    {
        $cantidad = Pedido::whereHas('estado', fn ($q) => $q->whereIn('orden', [1, 2]))->count();

        return $cantidad > 0 ? (string) $cantidad : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Pedidos por preparar (Recibido y En preparación)';
    }

    public function getSubheading(): ?string
    {
        return 'Pedidos activos en orden de llegada. Se actualiza solo cada 15 segundos.';
    }

    /**
     * Datos para la vista: tres columnas con sus pedidos (el más antiguo primero).
     */
    protected function getViewData(): array
    {
        $pedidos = Pedido::query()
            ->with(['estado', 'cliente', 'detalles.producto'])
            ->whereHas('estado', fn ($q) => $q->whereIn('orden', [1, 2, 3]))
            ->orderBy('fecha_hora')
            ->get();

        return [
            'columnas' => [
                ['titulo' => 'Recibido',       'color' => 'info',    'pedidos' => $pedidos->where('estado.orden', 1)],
                ['titulo' => 'En preparación', 'color' => 'warning', 'pedidos' => $pedidos->where('estado.orden', 2)],
                ['titulo' => 'Listo',          'color' => 'success', 'pedidos' => $pedidos->where('estado.orden', 3)],
            ],
            'usuario' => auth()->user(),
        ];
    }

    /** Dirección del detalle del pedido (botón "Ver"). */
    public function urlPedido(Pedido $pedido): string
    {
        return PedidoResource::getUrl('view', ['record' => $pedido]);
    }

    /**
     * Botón de cada tarjeta: pasa el pedido al siguiente estado (RN-08, RN-25).
     */
    public function avanzar(int $idPedido): void
    {
        $pedido = Pedido::with('estado')->findOrFail($idPedido);

        try {
            $pedido = app(PedidoService::class)->avanzarEstado($pedido, auth()->user());
        } catch (ValidationException $e) {
            Notification::make()
                ->title(collect($e->errors())->flatten()->first())
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title("Pedido {$pedido->folio}: ahora está \"{$pedido->estado->nombre}\"")
            ->success()
            ->send();
    }
}
