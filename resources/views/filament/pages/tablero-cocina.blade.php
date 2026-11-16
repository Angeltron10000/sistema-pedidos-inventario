{{--
    Tablero de cocina (RF-36). Tres columnas: Recibido, En preparación y Listo.
    wire:poll.15s hace que la página se actualice sola cada 15 segundos.
--}}
<x-filament-panels::page>
    <style>
        .tc-columnas { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; align-items: start; }
        @media (max-width: 1024px) { .tc-columnas { grid-template-columns: 1fr; } }
        .tc-lista { display: flex; flex-direction: column; gap: .75rem; }
        .tc-tarjeta { border: 1px solid rgba(127, 127, 127, .3); border-radius: .75rem; padding: .85rem; }
        .tc-tarjeta.tc-tarde { border: 2px solid rgb(220, 38, 38); }
        .tc-fila { display: flex; justify-content: space-between; align-items: center; gap: .5rem; }
        .tc-folio { font-size: 1.15rem; font-weight: 700; }
        .tc-gris { font-size: .8rem; opacity: .75; }
        .tc-espera { font-size: .85rem; font-weight: 600; white-space: nowrap; }
        .tc-espera.tc-tarde { color: rgb(220, 38, 38); }
        .tc-productos { margin: .6rem 0; padding-left: 0; list-style: none; font-size: .95rem; }
        .tc-productos li { padding: .15rem 0; }
        .tc-cantidad { font-weight: 700; }
        .tc-nota { font-size: .8rem; font-style: italic; opacity: .8; }
        .tc-observaciones { font-size: .8rem; margin-bottom: .5rem; padding: .35rem .5rem; border-radius: .4rem; background: rgba(234, 88, 12, .12); }
        .tc-botones { display: flex; gap: .5rem; flex-wrap: wrap; }
        .tc-vacio { text-align: center; padding: 1.5rem .5rem; opacity: .6; font-size: .9rem; }
    </style>

    <div wire:poll.15s class="tc-columnas">
        @foreach ($columnas as $columna)
            <x-filament::section>
                <x-slot name="heading">
                    {{ $columna['titulo'] }}
                    <x-filament::badge :color="$columna['color']" style="display: inline-flex; margin-left: .35rem;">
                        {{ $columna['pedidos']->count() }}
                    </x-filament::badge>
                </x-slot>

                <div class="tc-lista">
                    @forelse ($columna['pedidos'] as $pedido)
                        @php($tarde = $pedido->minutosDeEspera() > 30)
                        <div class="tc-tarjeta {{ $tarde ? 'tc-tarde' : '' }}" wire:key="pedido-{{ $pedido->id_pedido }}">
                            <div class="tc-fila">
                                <span class="tc-folio">{{ $pedido->folio }}</span>
                                <x-filament::badge :color="$pedido->tipo_servicio === 'llevar' ? 'warning' : 'gray'"
                                    :icon="$pedido->tipo_servicio === 'llevar' ? 'heroicon-o-shopping-bag' : 'heroicon-o-building-storefront'">
                                    {{ $pedido->tipo_servicio === 'llevar' ? 'Para llevar' : 'Comer aquí' }}
                                </x-filament::badge>
                            </div>
                            <div class="tc-fila">
                                <span class="tc-gris">{{ $pedido->cliente->nombre }} · {{ $pedido->fecha_hora->format('H:i') }}</span>
                                <span class="tc-espera {{ $tarde ? 'tc-tarde' : '' }}">⏱ {{ $pedido->tiempoDeEspera() }}</span>
                            </div>

                            <ul class="tc-productos">
                                @foreach ($pedido->detalles as $detalle)
                                    <li>
                                        <span class="tc-cantidad">{{ $detalle->cantidad }} ×</span> {{ $detalle->producto->nombre }}
                                        @if ($detalle->notas)
                                            <div class="tc-nota">↳ {{ $detalle->notas }}</div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>

                            @if ($pedido->observaciones)
                                <div class="tc-observaciones">📝 {{ $pedido->observaciones }}</div>
                            @endif

                            <div class="tc-botones">
                                @if ($pedido->puedeAvanzarPor($usuario))
                                    @php($siguiente = $pedido->siguienteEstado()?->nombre)
                                    <x-filament::button
                                        size="sm"
                                        :color="$siguiente === 'Entregado' ? 'gray' : 'success'"
                                        icon="heroicon-o-arrow-right-circle"
                                        wire:click="avanzar({{ $pedido->id_pedido }})"
                                        wire:confirm="¿Pasar el pedido {{ $pedido->folio }} a «{{ $siguiente }}»?">
                                        {{ $siguiente === 'Entregado' ? 'Entregar' : 'Pasar a ' . $siguiente }}
                                    </x-filament::button>
                                @endif
                                <x-filament::button size="sm" color="gray" outlined tag="a" :href="$this->urlPedido($pedido)">
                                    Ver
                                </x-filament::button>
                            </div>
                        </div>
                    @empty
                        <div class="tc-vacio">Sin pedidos</div>
                    @endforelse
                </div>
            </x-filament::section>
        @endforeach
    </div>
</x-filament-panels::page>
