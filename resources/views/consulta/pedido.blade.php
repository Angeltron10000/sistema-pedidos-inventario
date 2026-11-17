@extends('consulta.layout')

@section('titulo', 'Pedido ' . $pedido->folio)

@php
    // Etapas que se muestran en la barra de avance (orden del estado: 1 a 4; 0 = cancelado)
    $etapas = [1 => 'Recibido', 2 => 'En preparación', 3 => 'Listo', 4 => 'Entregado'];
    $orden = (int) $pedido->estado?->orden;
    $cancelado = $orden === 0;

    $mensaje = match ($orden) {
        1 => 'Recibimos tu pedido. En cuanto la cocina empiece a prepararlo lo verás aquí.',
        2 => 'La cocina está preparando tu pedido.',
        3 => $pedido->tipo_servicio === 'llevar'
                ? '¡Tu pedido está listo! Pasa a recogerlo a caja con tu comprobante.'
                : '¡Tu pedido está listo! En un momento te lo llevan a tu mesa.',
        4 => 'Tu pedido ya fue entregado. ¡Buen provecho!',
        default => 'Este pedido fue cancelado.',
    };
@endphp

@push('estilos')
    <style>
        .encabezado { display: flex; justify-content: space-between; align-items: baseline; gap: 10px; flex-wrap: wrap; }
        .folio-grande { font-size: 24px; font-weight: bold; letter-spacing: 1px; }
        .etiqueta { display: inline-block; border-radius: 999px; padding: 4px 12px; font-size: 13px; font-weight: bold; }
        .etiqueta-1 { background: #f3f4f6; color: #374151; }
        .etiqueta-2 { background: #fef3c7; color: #92400e; }
        .etiqueta-3 { background: #dcfce7; color: #166534; }
        .etiqueta-4 { background: #dbeafe; color: #1e40af; }
        .etiqueta-0 { background: #fee2e2; color: #b91c1c; }

        /* Barra de avance: un círculo por etapa unido por una línea */
        .avance { display: flex; margin: 22px 0 8px; }
        .paso { flex: 1; text-align: center; position: relative; font-size: 12px; color: #9ca3af; }
        .paso::before { content: ""; position: absolute; top: 15px; left: -50%; width: 100%; height: 4px; background: #e5e7eb; z-index: 0; }
        .paso:first-child::before { display: none; }
        .paso .circulo { position: relative; z-index: 1; width: 34px; height: 34px; line-height: 34px; margin: 0 auto 6px; border-radius: 50%;
                         background: #e5e7eb; color: #6b7280; font-weight: bold; font-size: 14px; }
        .paso.hecho { color: #111; }
        .paso.hecho::before { background: #ea580c; }
        .paso.hecho .circulo { background: #ea580c; color: #fff; }
        .paso.actual .circulo { box-shadow: 0 0 0 5px #fed7aa; }
        .paso.actual { font-weight: bold; }

        .mensaje { font-size: 16px; margin: 14px 0 4px; }
        .aviso-cancelado { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; border-radius: 8px; padding: 12px; margin-top: 12px; }
        .actualizacion { font-size: 12px; color: #9ca3af; margin-top: 10px; }

        details.cancelar summary { list-style: none; }
        details.cancelar summary::-webkit-details-marker { display: none; }
        .confirmar { margin-top: 14px; border-top: 1px solid #e5e7eb; padding-top: 14px; }
        .fila-botones { display: flex; gap: 8px; margin-top: 12px; flex-wrap: wrap; }
    </style>
@endpush

@section('contenido')
    @if (session('aviso_ok'))
        <div class="ok">{{ session('aviso_ok') }}</div>
    @endif
    @if (session('aviso_error'))
        <div class="error" style="margin: 0 0 16px;">{{ session('aviso_error') }}</div>
    @endif

    {{-- 1. En qué etapa va el pedido --}}
    <div class="tarjeta">
        <div class="encabezado">
            <div>
                <div class="gris">Tu pedido</div>
                <div class="folio-grande">{{ $pedido->folio }}</div>
            </div>
            <span class="etiqueta etiqueta-{{ $orden }}">{{ $pedido->estado?->nombre }}</span>
        </div>

        @if ($cancelado)
            <div class="aviso-cancelado">
                <strong>Pedido cancelado.</strong>
                @if ($pedido->motivo_cancelacion)
                    <br>{{ $pedido->motivo_cancelacion }}
                @endif
            </div>
        @else
            <div class="avance">
                @foreach ($etapas as $numero => $nombre)
                    <div class="paso {{ $numero <= $orden ? 'hecho' : '' }} {{ $numero === $orden ? 'actual' : '' }}">
                        <div class="circulo">{{ $numero <= $orden ? '✓' : $numero }}</div>
                        {{ $nombre }}
                    </div>
                @endforeach
            </div>
            <p class="mensaje">{{ $mensaje }}</p>
        @endif

        @if ($orden >= 1 && $orden <= 3)
            <div class="actualizacion" id="actualizacion">
                Esta página se actualiza sola cada {{ $segundos }} segundos.
            </div>
        @endif
    </div>

    {{-- 2. Cancelar: solo mientras el pedido está en "Recibido" --}}
    @if ($pedido->sePuedeCancelarPorCliente())
        <div class="tarjeta">
            <details class="cancelar">
                <summary class="boton boton-gris">Cancelar mi pedido</summary>
                <div class="confirmar">
                    <p><strong>¿Seguro que quieres cancelar el pedido {{ $pedido->folio }}?</strong><br>
                        <span class="gris">Solo se puede mientras la cocina no empiece a prepararlo. Esta acción no se puede deshacer.</span></p>
                    <form method="POST" action="{{ route('consulta.cancelar') }}">
                        @csrf
                        <label for="motivo">¿Por qué lo cancelas? <span class="gris">(opcional)</span></label>
                        <input type="text" id="motivo" name="motivo" maxlength="120" placeholder="Ejemplo: ya no puedo esperar">
                        <div class="fila-botones">
                            <button type="submit" class="boton boton-peligro">Sí, cancelar mi pedido</button>
                        </div>
                    </form>
                </div>
            </details>
        </div>
    @elseif ($orden === 2 || $orden === 3)
        <p class="gris" style="text-align:center;">Tu pedido ya se está preparando y ya no se puede cancelar desde aquí. Si necesitas un cambio, acude a caja.</p>
    @endif

    {{-- 3. Comprobante --}}
    @include('pedidos.partials.ticket', ['pedido' => $pedido, 'configuracion' => $configuracion])

    <form method="POST" action="{{ route('consulta.salir') }}" style="text-align:center; margin-top: 16px;">
        @csrf
        <button type="submit" class="boton boton-gris">Consultar otro pedido</button>
    </form>
@endsection

@if ($orden >= 1 && $orden <= 3)
    @push('scripts')
        <script>
            // Cada pocos segundos pregunta al sistema el estado del pedido.
            // Si cambió (por ejemplo, de "Recibido" a "En preparación"), recarga la página.
            (function () {
                const estadoMostrado = {{ (int) $pedido->id_estado }};
                const aviso = document.getElementById('actualizacion');

                setInterval(async function () {
                    try {
                        const respuesta = await fetch(@json(route('consulta.estado')), { headers: { 'Accept': 'application/json' } });
                        if (! respuesta.ok) return;
                        const datos = await respuesta.json();

                        if (datos.id_estado !== estadoMostrado) {
                            window.location.reload();
                        } else if (aviso) {
                            aviso.textContent = 'Esta página se actualiza sola cada {{ $segundos }} segundos. Última revisión: '
                                + new Date().toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                        }
                    } catch (e) {
                        // Sin conexión por un momento: se vuelve a intentar en la siguiente vuelta
                    }
                }, {{ $segundos * 1000 }});
            })();
        </script>
    @endpush
@endif
