<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Pedido;
use Illuminate\View\View;

/**
 * Comprobante de un pedido listo para imprimir (RF-29):
 * folio, fecha, cliente, productos, cantidades, precios y total.
 * Se abre desde el botón "Comprobante" del pedido.
 */
class ComprobanteController extends Controller
{
    public function __invoke(Pedido $pedido): View
    {
        $pedido->load(['cliente', 'usuario', 'estado', 'detalles.producto']);

        return view('pedidos.comprobante', [
            'pedido'        => $pedido,
            'configuracion' => Configuracion::actual(),
        ]);
    }
}
