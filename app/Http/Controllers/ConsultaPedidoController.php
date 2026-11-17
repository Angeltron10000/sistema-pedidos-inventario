<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Pedido;
use App\Services\PedidoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Consulta del pedido para el CLIENTE (rol "Cliente").
 *
 * El cliente no tiene cuenta ni contraseña: escribe el folio y el código de consulta
 * que vienen en su comprobante (por ejemplo PED-0021 y K7P2). Si coinciden:
 *   - ve su comprobante y en qué etapa va su pedido (se actualiza sola);
 *   - puede cancelarlo mientras sigue en "Recibido".
 * Solo ve SU pedido; no ve nada más del sistema.
 *
 * El pedido que el cliente puede ver se guarda en su sesión del navegador,
 * así no tiene que volver a escribir los datos cada vez que la página se actualiza.
 */
class ConsultaPedidoController extends Controller
{
    /** Llave de la sesión donde se guarda el pedido que el cliente puede ver. */
    private const SESION = 'consulta_pedido';

    /** Segundos entre cada revisión automática del estado. */
    public const SEGUNDOS_ACTUALIZACION = 10;

    /**
     * Pantalla para escribir el folio y el código.
     */
    public function formulario(Request $request): View
    {
        return view('consulta.formulario', [
            'configuracion' => Configuracion::actual(),
            'folio'         => $request->query('folio'),
            'pedidoAbierto' => $this->pedidoDeLaSesion($request),
        ]);
    }

    /**
     * Revisa el folio y el código. Si coinciden, muestra el pedido.
     * Por seguridad no se dice cuál de los dos datos está mal.
     */
    public function buscar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'folio'  => ['required', 'string', 'max:20'],
            'codigo' => ['required', 'string', 'max:10'],
        ], [
            'folio.required'  => 'Escribe el folio de tu pedido.',
            'codigo.required' => 'Escribe el código de consulta.',
        ]);

        $pedido = Pedido::buscarParaConsulta($datos['folio'], $datos['codigo']);

        if (! $pedido) {
            return back()
                ->withInput($request->only('folio'))
                ->withErrors(['codigo' => 'El folio o el código no coinciden. Revísalos en tu comprobante.']);
        }

        $request->session()->regenerate();
        $request->session()->put(self::SESION, $pedido->id_pedido);

        return redirect()->route('consulta.pedido');
    }

    /**
     * Comprobante del pedido y barra con la etapa en que va.
     */
    public function pedido(Request $request): View|RedirectResponse
    {
        $pedido = $this->pedidoDeLaSesion($request);

        if (! $pedido) {
            return redirect()->route('consulta.formulario');
        }

        $pedido->load(['cliente', 'usuario', 'estado', 'detalles.producto']);

        return view('consulta.pedido', [
            'pedido'        => $pedido,
            'configuracion' => Configuracion::actual(),
            'segundos'      => self::SEGUNDOS_ACTUALIZACION,
        ]);
    }

    /**
     * Estado actual del pedido en formato JSON. La página lo pregunta cada pocos
     * segundos y, si cambió, se recarga sola.
     */
    public function estado(Request $request): JsonResponse
    {
        $pedido = $this->pedidoDeLaSesion($request);

        if (! $pedido) {
            return response()->json(['mensaje' => 'Sin pedido'], 404);
        }

        return response()->json([
            'id_estado' => $pedido->id_estado,
            'estado'    => $pedido->estado?->nombre,
        ]);
    }

    /**
     * El cliente cancela su pedido (solo en "Recibido").
     */
    public function cancelar(Request $request, PedidoService $servicio): RedirectResponse
    {
        $pedido = $this->pedidoDeLaSesion($request);

        if (! $pedido) {
            return redirect()->route('consulta.formulario');
        }

        $datos = $request->validate([
            'motivo' => ['nullable', 'string', 'max:120'],
        ]);

        try {
            $servicio->cancelarPorCliente($pedido, $datos['motivo'] ?? null);
        } catch (ValidationException $error) {
            return redirect()->route('consulta.pedido')
                ->with('aviso_error', collect($error->errors())->flatten()->first());
        }

        return redirect()->route('consulta.pedido')
            ->with('aviso_ok', "Tu pedido {$pedido->folio} fue cancelado.");
    }

    /**
     * Cierra la consulta (por ejemplo, para consultar otro pedido).
     */
    public function salir(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESION);

        return redirect()->route('consulta.formulario');
    }

    /**
     * Pedido que este navegador puede ver (el que se abrió con folio y código).
     */
    private function pedidoDeLaSesion(Request $request): ?Pedido
    {
        $id = $request->session()->get(self::SESION);

        return $id ? Pedido::with('estado')->find($id) : null;
    }
}
