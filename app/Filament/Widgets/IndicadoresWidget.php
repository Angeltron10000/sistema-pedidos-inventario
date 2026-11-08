<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\TableroCocina;
use App\Filament\Resources\Pedidos\PedidoResource;
use App\Filament\Resources\Productos\ProductoResource;
use App\Models\Pedido;
use App\Models\Producto;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Indicadores del panel de inicio (RF-38): pedidos del día, pendientes,
 * listos para entregar, ventas del día y productos con stock bajo.
 * Se actualizan solos cada 30 segundos.
 */
class IndicadoresWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '30s';

    protected ?string $heading = 'Indicadores de hoy';

    protected function getColumns(): int
    {
        return 5;
    }

    protected function getStats(): array
    {
        $deHoy = Pedido::whereDate('fecha_hora', today());

        $pedidosHoy = (clone $deHoy)->count();
        $canceladosHoy = (clone $deHoy)->whereHas('estado', fn ($q) => $q->where('orden', 0))->count();
        $ventasHoy = (float) (clone $deHoy)->whereHas('estado', fn ($q) => $q->where('orden', '>', 0))->sum('total');

        $porPreparar = Pedido::whereHas('estado', fn ($q) => $q->whereIn('orden', [1, 2]))->count();
        $listos = Pedido::whereHas('estado', fn ($q) => $q->where('orden', 3))->count();
        $stockBajo = Producto::stockBajo()->count();
        $agotados = Producto::stockBajo()->where('existencia', 0)->count();

        $usuario = auth()->user();
        $puedeVerProductos = $usuario?->can('viewAny', Producto::class) ?? false;

        $indicadores = [
            Stat::make('Pedidos de hoy', $pedidosHoy)
                ->description(match (true) {
                    $canceladosHoy === 0 => 'Ninguno cancelado',
                    $canceladosHoy === 1 => '1 cancelado',
                    default              => "{$canceladosHoy} cancelados",
                })
                ->descriptionIcon('heroicon-o-clipboard-document-list')
                ->color('primary')
                ->url(PedidoResource::getUrl('index')),
            Stat::make('Por preparar', $porPreparar)
                ->description('Recibidos y en preparación')
                ->descriptionIcon('heroicon-o-fire')
                ->color($porPreparar > 0 ? 'warning' : 'gray')
                ->url(TableroCocina::getUrl()),
            Stat::make('Listos para entregar', $listos)
                ->description('Esperando al cliente')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color($listos > 0 ? 'success' : 'gray')
                ->url(TableroCocina::getUrl()),
            Stat::make('Ventas de hoy', '$' . number_format($ventasHoy, 2))
                ->description('Sin contar cancelados')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),
            Stat::make('Productos con stock bajo', $stockBajo)
                ->description(match (true) {
                    $agotados === 0 => 'Ninguno agotado',
                    $agotados === 1 => '1 agotado',
                    default         => "{$agotados} agotados",
                })
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($stockBajo > 0 ? 'danger' : 'gray')
                ->url($puedeVerProductos ? ProductoResource::getUrl('index', ['filters' => ['stock_bajo' => ['isActive' => true]]]) : null),
        ];

        return $indicadores;
    }
}
