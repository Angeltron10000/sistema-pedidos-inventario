{{--
    Diseño de las páginas de consulta para el cliente (rol "Cliente").
    Es una página sencilla, separada del panel de los empleados.
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo') · {{ $configuracion->nombre_negocio }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; background: #f3f4f6; margin: 0; color: #111; }
        .barra-superior { background: #ea580c; color: #fff; padding: 14px 20px; }
        .barra-superior .nombre { font-size: 18px; font-weight: bold; }
        .barra-superior .sub { font-size: 13px; opacity: .9; }
        .contenido { max-width: 560px; margin: 24px auto; padding: 0 16px 40px; }
        .tarjeta { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; margin-bottom: 16px; }
        h2 { font-size: 20px; margin: 0 0 6px; }
        p { line-height: 1.45; }
        .gris { color: #6b7280; font-size: 13px; }
        label { display: block; font-weight: bold; font-size: 14px; margin: 14px 0 6px; }
        input[type=text], textarea { width: 100%; padding: 10px 12px; font-size: 16px; border: 1px solid #d1d5db; border-radius: 8px; }
        input[type=text]:focus, textarea:focus { outline: 2px solid #fdba74; border-color: #ea580c; }
        .ayuda { font-size: 12px; color: #6b7280; margin-top: 4px; }
        .boton { display: inline-block; border: 0; border-radius: 8px; padding: 11px 18px; font-size: 15px; font-weight: bold; cursor: pointer; text-decoration: none; }
        .boton-principal { background: #ea580c; color: #fff; width: 100%; margin-top: 18px; }
        .boton-principal:hover { background: #c2410c; }
        .boton-gris { background: #e5e7eb; color: #111; }
        .boton-peligro { background: #dc2626; color: #fff; }
        .boton-peligro:hover { background: #b91c1c; }
        .error { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; border-radius: 8px; padding: 10px 12px; font-size: 14px; margin-top: 14px; }
        .ok { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 8px; padding: 10px 12px; font-size: 14px; margin-bottom: 16px; }
        .pie { text-align: center; font-size: 12px; color: #9ca3af; margin-top: 24px; }
        .pie a { color: #9ca3af; }
    </style>
    @stack('estilos')
</head>
<body>
    <div class="barra-superior">
        <div class="nombre">{{ $configuracion->nombre_negocio }}</div>
        <div class="sub">Consulta de pedidos para clientes</div>
    </div>

    <div class="contenido">
        @yield('contenido')

        <div class="pie">
            ¿Trabajas aquí? <a href="{{ url('/admin') }}">Entrar al sistema</a>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
