<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comprobante {{ $pedido->folio }}</title>
    <style>
        /* Estilo tipo ticket: angosto, letra sencilla y sin colores para imprimir */
        body { font-family: Arial, Helvetica, sans-serif; background: #f3f4f6; margin: 0; padding: 24px; color: #111; }
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
    @include('pedidos.partials.ticket', ['pedido' => $pedido, 'configuracion' => $configuracion])

    <div class="botones">
        <button class="imprimir" onclick="window.print()">Imprimir</button>
        <button class="cerrar" onclick="window.close()">Cerrar</button>
    </div>
</body>
</html>
