<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comprobante {{ $pedido->folio }}</title>
    <style>
        /* Estilo tipo ticket: angosto, letra sencilla y sin colores para imprimir */
        body { font-family: Arial, Helvetica, sans-serif; background: #f3f4f6; margin: 0; padding: 24px; color: #111; }
        .ticket { background: #fff; max-width: 380px; margin: 0 auto; padding: 24px; border: 1px solid #d1d5db; }
        h1 { font-size: 18px; text-align: center; margin: 0 0 2px; }
        .centro { text-align: center; }
        .gris { color: #555; font-size: 12px; }
        .folio { font-size: 22px; font-weight: bold; text-align: center; margin: 14px 0 4px; letter-spacing: 1px; }
        .linea { border-top: 1px dashed #999; margin: 12px 0; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { text-align: left; font-size: 12px; border-bottom: 1px solid #999; padding: 4px 0; }
        td { padding: 4px 0; vertical-align: top; }
        .der { text-align: right; }
        .nota { font-size: 11px; color: #555; }
        .total { font-size: 18px; font-weight: bold; }
        .datos td { padding: 2px 0; font-size: 13px; }
        .cancelado { border: 2px solid #b91c1c; color: #b91c1c; font-weight: bold; text-align: center; padding: 6px; margin: 10px 0; }
        .botones { max-width: 380px; margin: 16px auto 0; display: flex; gap: 8px; }
        .botones button { flex: 1; padding: 10px; font-size: 14px; border: 0; border-radius: 6px; cursor: pointer; }
        .imprimir { background: #ea580c; color: #fff; }
        .cerrar { background: #e5e7eb; }
        @media print {
            body { background: #fff; padding: 0; }
            .ticket { border: 0; }
            .botones { display: none; }
        }
    </style>
</head>
<body>
    <div class="ticket">
        <h1>{{ $configuracion->nombre_negocio }}</h1>
        <div class="centro gris">Comprobante de pedido</div>

        <div class="folio">{{ $pedido->folio }}</div>
        <div class="centro gris">{{ $pedido->fecha_hora->format('d/m/Y H:i') }}</div>

        @if ($pedido->estado?->orden === 0)
            <div class="cancelado">PEDIDO CANCELADO</div>
        @endif

        <div class="linea"></div>

        <table class="datos">
            <tr><td>Cliente:</td><td class="der">{{ $pedido->cliente->nombre }}</td></tr>
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
        <div class="centro gris">Conserve este comprobante para recoger su pedido.<br>Este documento no es una factura.</div>
    </div>

    <div class="botones">
        <button class="imprimir" onclick="window.print()">Imprimir</button>
        <button class="cerrar" onclick="window.close()">Cerrar</button>
    </div>
</body>
</html>
