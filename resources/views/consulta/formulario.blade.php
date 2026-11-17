@extends('consulta.layout')

@section('titulo', 'Consulta tu pedido')

@section('contenido')
    @if ($pedidoAbierto)
        <div class="ok">
            Ya tienes abierto el pedido <strong>{{ $pedidoAbierto->folio }}</strong>.
            <a href="{{ route('consulta.pedido') }}">Volver a verlo</a>
        </div>
    @endif

    <div class="tarjeta">
        <h2>Consulta tu pedido</h2>
        <p class="gris">Escribe el folio y el código de consulta que vienen en tu comprobante.</p>

        <form method="POST" action="{{ route('consulta.buscar') }}">
            @csrf

            <label for="folio">Folio del pedido</label>
            <input type="text" id="folio" name="folio" value="{{ old('folio', $folio) }}"
                   placeholder="Ejemplo: PED-0021" autocomplete="off" required autofocus>
            <div class="ayuda">Puedes escribirlo completo (PED-0021) o solo el número (21).</div>

            <label for="codigo">Código de consulta</label>
            <input type="text" id="codigo" name="codigo" maxlength="10"
                   placeholder="Ejemplo: K7P2" autocomplete="off" required
                   style="text-transform: uppercase; letter-spacing: 4px;">
            <div class="ayuda">Son 4 letras y números. Está debajo del folio en tu comprobante.</div>

            @if ($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <button type="submit" class="boton boton-principal">Ver mi pedido</button>
        </form>
    </div>
@endsection
