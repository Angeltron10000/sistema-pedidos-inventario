{{--
    Reportes (RF-39 a RF-42). Los campos de fecha usan wire:model.live:
    al cambiar una fecha, los reportes se recalculan solos.
--}}
<x-filament-panels::page>
    <style>
        .rp-filtros { display: flex; flex-wrap: wrap; gap: .75rem; align-items: end; }
        .rp-campo label { display: block; font-size: .8rem; font-weight: 600; margin-bottom: .25rem; }
        .rp-tarjetas { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; }
        @media (max-width: 900px) { .rp-tarjetas { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .rp-tarjeta { border: 1px solid rgba(127, 127, 127, .3); border-radius: .75rem; padding: .9rem 1rem; }
        .rp-tarjeta .rp-titulo { font-size: .8rem; opacity: .75; }
        .rp-tarjeta .rp-valor { font-size: 1.5rem; font-weight: 700; margin-top: .2rem; }
        .rp-dos { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; align-items: start; }
        @media (max-width: 1100px) { .rp-dos { grid-template-columns: 1fr; } }
        .rp-tabla { width: 100%; border-collapse: collapse; font-size: .9rem; }
        .rp-tabla th { text-align: left; font-size: .78rem; text-transform: uppercase; letter-spacing: .03em; opacity: .7; padding: .45rem .5rem; border-bottom: 1px solid rgba(127, 127, 127, .35); }
        .rp-tabla td { padding: .45rem .5rem; border-bottom: 1px solid rgba(127, 127, 127, .15); }
        .rp-tabla .rp-der { text-align: right; }
        .rp-barra { height: .55rem; border-radius: 999px; background: rgb(234, 88, 12); }
        .rp-fondo-barra { background: rgba(127, 127, 127, .15); border-radius: 999px; min-width: 6rem; }
        .rp-vacio { text-align: center; padding: 1rem; opacity: .6; }
    </style>

    {{-- Periodo --}}
    <x-filament::section>
        <div class="rp-filtros">
            <div class="rp-campo">
                <label for="desde">Desde</label>
                <x-filament::input.wrapper>
                    <x-filament::input type="date" id="desde" wire:model.live="desde" />
                </x-filament::input.wrapper>
            </div>
            <div class="rp-campo">
                <label for="hasta">Hasta</label>
                <x-filament::input.wrapper>
                    <x-filament::input type="date" id="hasta" wire:model.live="hasta" />
                </x-filament::input.wrapper>
            </div>
            <x-filament::button color="gray" wire:click="periodo('hoy')">Hoy</x-filament::button>
            <x-filament::button color="gray" wire:click="periodo('semana')">Últimos 7 días</x-filament::button>
            <x-filament::button color="gray" wire:click="periodo('mes')">Este mes</x-filament::button>
        </div>
        <div style="margin-top: .6rem; font-size: .85rem; opacity: .75;">Periodo: {{ $textoPeriodo }}</div>
    </x-filament::section>

    {{-- RF-39: resumen de ventas --}}
    <div class="rp-tarjetas">
        <div class="rp-tarjeta">
            <div class="rp-titulo">Total vendido</div>
            <div class="rp-valor">${{ number_format($totalVendido, 2) }}</div>
        </div>
        <div class="rp-tarjeta">
            <div class="rp-titulo">Pedidos vendidos</div>
            <div class="rp-valor">{{ $numeroPedidos }}</div>
        </div>
        <div class="rp-tarjeta">
            <div class="rp-titulo">Promedio por pedido</div>
            <div class="rp-valor">${{ number_format($promedio, 2) }}</div>
        </div>
        <div class="rp-tarjeta">
            <div class="rp-titulo">Pedidos cancelados</div>
            <div class="rp-valor">{{ $cancelados }}</div>
        </div>
    </div>

    <div class="rp-dos">
        {{-- RF-39: ventas por día --}}
        <x-filament::section heading="Ventas por día" icon="heroicon-o-banknotes">
            <table class="rp-tabla">
                <thead>
                    <tr><th>Día</th><th class="rp-der">Pedidos</th><th class="rp-der">Total</th></tr>
                </thead>
                <tbody>
                    @forelse ($ventasPorDia as $fila)
                        <tr>
                            <td>{{ $fila['dia'] }}</td>
                            <td class="rp-der">{{ $fila['pedidos'] }}</td>
                            <td class="rp-der">${{ number_format($fila['total'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="rp-vacio">Sin ventas en este periodo</td></tr>
                    @endforelse
                </tbody>
                @if ($ventasPorDia->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td><strong>Total del periodo</strong></td>
                            <td class="rp-der"><strong>{{ $numeroPedidos }}</strong></td>
                            <td class="rp-der"><strong>${{ number_format($totalVendido, 2) }}</strong></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </x-filament::section>

        {{-- RF-42: pedidos por estado --}}
        <x-filament::section heading="Pedidos por estado" icon="heroicon-o-clipboard-document-list">
            <table class="rp-tabla">
                <thead>
                    <tr><th>Estado</th><th class="rp-der">Pedidos</th><th class="rp-der">%</th></tr>
                </thead>
                <tbody>
                    @foreach ($porEstado as $fila)
                        <tr>
                            <td>
                                <x-filament::badge :color="\App\Filament\Resources\Pedidos\Tables\PedidosTable::colorEstado($fila['estado'])" style="display: inline-flex;">
                                    {{ $fila['estado'] }}
                                </x-filament::badge>
                            </td>
                            <td class="rp-der">{{ $fila['cantidad'] }}</td>
                            <td class="rp-der">{{ number_format($fila['cantidad'] * 100 / $totalPedidos, 1) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-filament::section>
    </div>

    {{-- RF-40: productos más vendidos --}}
    <x-filament::section heading="Productos más vendidos (top 10)" icon="heroicon-o-trophy">
        <table class="rp-tabla">
            <thead>
                <tr><th>#</th><th>Producto</th><th>Categoría</th><th class="rp-der">Unidades</th><th></th><th class="rp-der">Importe</th></tr>
            </thead>
            <tbody>
                @forelse ($masVendidos as $i => $fila)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><strong>{{ $fila->producto }}</strong></td>
                        <td>{{ $fila->categoria }}</td>
                        <td class="rp-der">{{ $fila->unidades }}</td>
                        <td style="width: 30%;">
                            <div class="rp-fondo-barra"><div class="rp-barra" style="width: {{ round($fila->unidades * 100 / $maxUnidades) }}%;"></div></div>
                        </td>
                        <td class="rp-der">${{ number_format((float) $fila->importe, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="rp-vacio">Sin ventas en este periodo</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-filament::section>

    {{-- RF-41: inventario actual y por resurtir --}}
    <x-filament::section icon="heroicon-o-cube">
        <x-slot name="heading">
            Inventario actual
            <x-filament::badge color="danger" style="display: inline-flex; margin-left: .35rem;">{{ $porResurtir }} por resurtir</x-filament::badge>
        </x-slot>
        <x-slot name="afterHeader">
            <label style="display: flex; gap: .4rem; align-items: center; font-size: .85rem;">
                <x-filament::input.checkbox wire:model.live="soloPorResurtir" />
                Solo productos por resurtir
            </label>
        </x-slot>
        <table class="rp-tabla">
            <thead>
                <tr><th>Producto</th><th>Categoría</th><th class="rp-der">Existencia</th><th class="rp-der">Mínimo</th><th>Situación</th></tr>
            </thead>
            <tbody>
                @forelse ($inventario as $producto)
                    <tr>
                        <td><strong>{{ $producto->nombre }}</strong></td>
                        <td>{{ $producto->categoria->nombre }}</td>
                        <td class="rp-der">{{ $producto->existencia }}</td>
                        <td class="rp-der">{{ $producto->stock_minimo }}</td>
                        <td>
                            @if ($producto->existencia === 0)
                                <x-filament::badge color="danger" style="display: inline-flex;">Agotado</x-filament::badge>
                            @elseif ($producto->existencia <= $producto->stock_minimo)
                                <x-filament::badge color="warning" style="display: inline-flex;">Por resurtir</x-filament::badge>
                            @else
                                <x-filament::badge color="success" style="display: inline-flex;">Suficiente</x-filament::badge>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="rp-vacio">No hay productos por resurtir</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-filament::section>
</x-filament-panels::page>
