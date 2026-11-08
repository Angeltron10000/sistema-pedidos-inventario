<?php

namespace App\Filament\Pages;

use App\Models\EstadoPedido;
use App\Models\Pedido;
use App\Models\Producto;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use UnitEnum;

/**
 * Reportes en pantalla (solo Administrador):
 * - Ventas por día o por periodo (RF-39)
 * - Productos más vendidos (RF-40)
 * - Inventario actual y productos por resurtir (RF-41)
 * - Pedidos por estado, incluidos los cancelados (RF-42)
 * Las ventas no cuentan los pedidos cancelados.
 */
class Reportes extends Page
{
    protected string $view = 'filament.pages.reportes';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Reportes';

    protected static ?string $title = 'Reportes';

    protected static ?string $slug = 'reportes';

    protected static string|UnitEnum|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 1;

    /** Periodo del reporte (se cambia con los campos de fecha de la página). */
    public string $desde = '';

    public string $hasta = '';

    /** En el reporte de inventario, mostrar solo los productos por resurtir. */
    public bool $soloPorResurtir = false;

    /** Solo el Administrador ve los reportes (RN-25). */
    public static function canAccess(): bool
    {
        return auth()->user()?->esAdministrador() ?? false;
    }

    public function mount(): void
    {
        $this->periodo('mes');
    }

    /** Botones rápidos: hoy, últimos 7 días, este mes. */
    public function periodo(string $cual): void
    {
        $this->hasta = today()->toDateString();
        $this->desde = match ($cual) {
            'hoy'   => today()->toDateString(),
            'semana' => today()->subDays(6)->toDateString(),
            default => today()->startOfMonth()->toDateString(),
        };
    }

    public function getSubheading(): ?string
    {
        return 'Elige el periodo. Las ventas no incluyen pedidos cancelados.';
    }

    protected function getViewData(): array
    {
        // Si las fechas están vacías o al revés, se corrigen
        $desde = $this->fecha($this->desde) ?? today()->startOfMonth();
        $hasta = $this->fecha($this->hasta) ?? today();
        if ($desde->gt($hasta)) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        // Pedidos del periodo con su estado
        $pedidos = Pedido::query()
            ->with('estado')
            ->whereDate('fecha_hora', '>=', $desde)
            ->whereDate('fecha_hora', '<=', $hasta)
            ->get();

        $vendidos = $pedidos->filter(fn (Pedido $p) => $p->estado?->orden > 0);    // sin cancelados

        // RF-39: ventas por día
        $ventasPorDia = $vendidos
            ->groupBy(fn (Pedido $p) => $p->fecha_hora->toDateString())
            ->map(fn ($grupo, $dia) => [
                'dia'     => Carbon::parse($dia)->locale('es')->isoFormat('ddd D [de] MMM YYYY'),
                'pedidos' => $grupo->count(),
                'total'   => (float) $grupo->sum('total'),
            ])
            ->sortKeysDesc()
            ->values();

        // RF-40: productos más vendidos (por unidades)
        $masVendidos = DB::table('detalle_pedido as d')
            ->join('pedido as p', 'p.id_pedido', '=', 'd.id_pedido')
            ->join('estado_pedido as e', 'e.id_estado', '=', 'p.id_estado')
            ->join('producto as pr', 'pr.id_producto', '=', 'd.id_producto')
            ->join('categoria as c', 'c.id_categoria', '=', 'pr.id_categoria')
            ->where('e.orden', '>', 0)
            ->whereDate('p.fecha_hora', '>=', $desde)
            ->whereDate('p.fecha_hora', '<=', $hasta)
            ->groupBy('pr.id_producto', 'pr.nombre', 'c.nombre')
            ->selectRaw('pr.nombre as producto, c.nombre as categoria, SUM(d.cantidad) as unidades, SUM(d.subtotal) as importe')
            ->orderByDesc('unidades')
            ->limit(10)
            ->get();

        // RF-42: pedidos por estado (incluidos cancelados)
        $porEstado = EstadoPedido::query()
            ->orderByRaw('CASE WHEN orden = 0 THEN 99 ELSE orden END')
            ->get()
            ->map(fn (EstadoPedido $estado) => [
                'estado'   => $estado->nombre,
                'cantidad' => $pedidos->where('id_estado', $estado->id_estado)->count(),
            ]);

        // RF-41: inventario actual y por resurtir
        $inventario = Producto::query()
            ->with('categoria')
            ->where('activo', true)
            ->when($this->soloPorResurtir, fn ($q) => $q->whereColumn('existencia', '<=', 'stock_minimo'))
            ->orderByRaw('CASE WHEN existencia <= stock_minimo THEN 0 ELSE 1 END')
            ->orderBy('existencia')
            ->orderBy('nombre')
            ->get();

        return [
            'textoPeriodo'   => $desde->locale('es')->isoFormat('D [de] MMMM YYYY') . ' al ' . $hasta->locale('es')->isoFormat('D [de] MMMM YYYY'),
            'totalVendido'   => (float) $vendidos->sum('total'),
            'numeroPedidos'  => $vendidos->count(),
            'promedio'       => $vendidos->count() > 0 ? (float) $vendidos->avg('total') : 0,
            'cancelados'     => $pedidos->count() - $vendidos->count(),
            'ventasPorDia'   => $ventasPorDia,
            'masVendidos'    => $masVendidos,
            'maxUnidades'    => max(1, (int) ($masVendidos->max('unidades') ?? 1)),
            'porEstado'      => $porEstado,
            'totalPedidos'   => max(1, $pedidos->count()),
            'inventario'     => $inventario,
            'porResurtir'    => Producto::stockBajo()->count(),
        ];
    }

    private function fecha(string $texto): ?Carbon
    {
        try {
            return filled($texto) ? Carbon::parse($texto)->startOfDay() : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
