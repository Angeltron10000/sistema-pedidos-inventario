<?php

namespace App\Filament\Widgets;

use App\Models\Pedido;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

/**
 * Gráfica de ventas de los últimos 7 días (sin pedidos cancelados). Solo Administrador.
 */
class VentasSemanaWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Ventas de los últimos 7 días';

    protected ?string $maxHeight = '260px';

    public static function canView(): bool
    {
        return auth()->user()?->esAdministrador() ?? false;
    }

    protected function getData(): array
    {
        $dias = collect(range(6, 0))->map(fn (int $atras) => today()->subDays($atras));

        $ventas = Pedido::query()
            ->whereDate('fecha_hora', '>=', $dias->first())
            ->whereHas('estado', fn ($q) => $q->where('orden', '>', 0))
            ->get(['fecha_hora', 'total'])
            ->groupBy(fn (Pedido $p) => $p->fecha_hora->toDateString())
            ->map(fn ($grupo) => round((float) $grupo->sum('total'), 2));

        return [
            'datasets' => [
                [
                    'label' => 'Ventas ($)',
                    'data' => $dias->map(fn (Carbon $dia) => $ventas[$dia->toDateString()] ?? 0)->all(),
                ],
            ],
            'labels' => $dias->map(fn (Carbon $dia) => $dia->locale('es')->isoFormat('ddd D/M'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
