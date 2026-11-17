{{--
    Comprobante del pedido (RF-29): se usa en el comprobante para imprimir
    y en la página de consulta del cliente. Recibe $pedido y $configuracion.
--}}
<style>
    .ticket { background: #fff; max-width: 380px; margin: 0 auto; padding: 24px; border: 1px solid #d1d5db; }
    .ticket h1 { font-size: 18px; text-align: center; margin: 0 0 2px; }
    .ticket .centro { text-align: center; }
    .ticket .gris { color: #555; font-size: 12px; }
    .ticket .folio { font-size: 22px; font-weight: bold; text-align: center; margin: 14px 0 4px; letter-spacing: 1px; }
    .ticket .linea { border-top: 1px dashed #999; margin: 12px 0; }
    .ticket table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .ticket th { text-align: left; font-size: 12px; border-bottom: 1px solid #999; padding: 4px 0; }
    .ticket td { padding: 4px 0; vertical-align: top; }
    .ticket .der { text-align: right; }
    .ticket .nota { font-size: 11px; color: #555; }
    .ticket .total { font-size: 18px; font-weight: bold; }
    .ticket .datos td { padding: 2px 0; font-size: 13px; }
    .ticket .cancelado { border: 2px solid #b91c1c; color: #b91c1c; font-weight: bold; text-align: center; padding: 6px; margin: 10px 0; }
    .ticket .codigo { text-align: center; font-size: 13px; margin: 2px 0 6px; }
    .ticket .codigo strong { font-size: 16px; letter-spacing: 3px; border: 1px solid #999; padding: 1px 6px; }
</style>
<div class="ticket">
    <h1>{{ $configuracion->nombre_negocio }}</h1>
    <div class="centro gris">Comprobante de pedido</div>

    <div class="folio">{{ $pedido->folio }}</div>
    @if ($pedido->codigo_consulta)
        <div class="codigo">Código de consulta: <strong>{{ $pedido->codigo_consulta }}</strong></div>
    @endif
    <div class="centro gris">{{ $pedido->fecha_hora->format('d/m/Y H:i') }}</div>

    @if ($pedido->estado?->orden === 0)
        <div class="cancelado">PEDIDO CANCELADO</div>
    @endif

    <div class="linea"></div>

    <table class="datos">
        <tr><td>Cliente:</td><td class="der">{{ $pedido->cliente->nombre }} ({{ $pedido->cliente->numero }})</td></tr>
        <tr><td>Servicio:</td><td class="der">{{ $pedido->tipo_servicio === 'llevar' ? 'Para llevar' : 'Comer aquí' }}</td></tr>
        <tr><td>Estado:</td><td class="der">{{ $pedido->estado->nombre }}</td></tr>
        <tr><td>Atendió:</td><td class="der">{{ $pedido->usuario->getFilamentName() }}</td></tr>
    </table>

    <div class="linea"></div>

    <table>
        <thead>
            <tr>
                <th>Cant.</th>
                <th>Producto</th>
                <th class="der">P. unit.</th>
                <th class="der">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pedido->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->cantidad }}</td>
                    <td>
                        {{ $detalle->producto->nombre }}
                        @if ($detalle->notas)
                            <div class="nota">{{ $detalle->notas }}</div>
                        @endif
                    </td>
                    <td class="der">${{ number_format((float) $detalle->precio_unitario, 2) }}</td>
                    <td class="der">${{ number_format((float) $detalle->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="linea"></div>

    <table>
        <tr>
            <td>Productos: {{ $pedido->detalles->sum('cantidad') }}</td>
            <td class="der total">TOTAL ${{ number_format((float) $pedido->total, 2) }} MXN</td>
        </tr>
    </table>

    @if ($pedido->observaciones)
        <div class="linea"></div>
        <div class="gris">Observaciones: {{ $pedido->observaciones }}</div>
    @endif

    <div class="linea"></div>
    <div class="centro gris">
        @if ($pedido->codigo_consulta)
            Consulte en qué etapa va su pedido en<br><strong>{{ route('consulta.formulario') }}</strong><br>con su folio y su código de consulta.<br><br>
        @endif
        Conserve este comprobante para recoger su pedido.<br>Este documento no es una factura.
    </div>
</div>
