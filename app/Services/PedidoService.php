<?php

namespace App\Services;

use App\Models\EstadoPedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Reglas de negocio de los pedidos.
 *
 * Un pedido no es un registro simple: al crearlo hay que generar su folio,
 * copiar los precios, calcular el total, validar y descontar existencias,
 * y guardar el historial. Todo eso se hace aquí, dentro de una transacción,
 * para que si algo falla no quede nada a medias.
 */
class PedidoService
{
    /**
     * Registra un pedido nuevo.
     *
     * @param  array  $datos  id_cliente, tipo_servicio, observaciones y
     *                        productos: [['id_producto' => 1, 'cantidad' => 2, 'notas' => null], ...]
     */
    public function crear(array $datos, Usuario $usuario): Pedido
    {
        $renglones = $this->agruparProductos($datos['productos'] ?? []);

        if ($renglones === []) {
            throw ValidationException::withMessages([
                'data.productos' => 'Agrega al menos un producto al pedido.',
            ]);
        }

        return DB::transaction(function () use ($datos, $renglones, $usuario) {
            // Se leen los productos bloqueándolos para que nadie más cambie su existencia al mismo tiempo
            $productos = Producto::whereIn('id_producto', array_keys($renglones))
                ->lockForUpdate()
                ->get()
                ->keyBy('id_producto');

            // RN-04 y RN-19: solo productos activos y con existencia suficiente
            $errores = [];
            foreach ($renglones as $idProducto => $renglon) {
                $producto = $productos->get($idProducto);

                if (! $producto || ! $producto->activo) {
                    $errores[] = 'Un producto seleccionado ya no está disponible.';
                } elseif ($renglon['cantidad'] > $producto->existencia) {
                    $errores[] = "No hay suficiente {$producto->nombre}: pediste {$renglon['cantidad']} y solo hay {$producto->existencia}.";
                }
            }
            if ($errores !== []) {
                throw ValidationException::withMessages(['data.productos' => implode(' ', $errores)]);
            }

            $recibido = EstadoPedido::where('orden', 1)->firstOrFail();

            $pedido = Pedido::create([
                'folio'         => $this->siguienteFolio(),          // RN-01
                'id_cliente'    => $datos['id_cliente'],
                'id_usuario'    => $usuario->id_usuario,
                'id_estado'     => $recibido->id_estado,
                'fecha_hora'    => now(),
                'tipo_servicio' => $datos['tipo_servicio'] ?? 'local',
                'total'         => 0,
                'observaciones' => $datos['observaciones'] ?? null,
            ]);

            $total = 0;
            foreach ($renglones as $idProducto => $renglon) {
                $producto = $productos->get($idProducto);
                $subtotal = $renglon['cantidad'] * $producto->precio;   // RN-14
                $total += $subtotal;

                $pedido->detalles()->create([
                    'id_producto'     => $idProducto,
                    'cantidad'        => $renglon['cantidad'],
                    'precio_unitario' => $producto->precio,             // RN-13: precio congelado
                    'subtotal'        => $subtotal,
                    'notas'           => $renglon['notas'],
                ]);

                // RN-05: salida de inventario
                $pedido->movimientos()->create([
                    'id_producto' => $idProducto,
                    'id_usuario'  => $usuario->id_usuario,
                    'tipo'        => 'salida',
                    'cantidad'    => $renglon['cantidad'],
                    'fecha_hora'  => now(),
                    'motivo'      => "Salida por pedido {$pedido->folio}",
                ]);
                $producto->decrement('existencia', $renglon['cantidad']);
            }

            $pedido->update(['total' => $total]);                     // RN-15

            // RN-12: historial, primer estado "Recibido"
            $pedido->historial()->create([
                'id_estado'  => $recibido->id_estado,
                'id_usuario' => $usuario->id_usuario,
                'fecha_hora' => now(),
            ]);

            return $pedido;
        });
    }

    /**
     * Pasa el pedido al siguiente estado: Recibido → En preparación → Listo → Entregado (RN-08).
     */
    public function avanzarEstado(Pedido $pedido, Usuario $usuario): Pedido
    {
        $siguiente = $pedido->siguienteEstado();

        if (! $siguiente) {
            throw ValidationException::withMessages([
                'estado' => 'Este pedido ya no puede cambiar de estado.',
            ]);
        }

        DB::transaction(function () use ($pedido, $siguiente, $usuario) {
            $pedido->update(['id_estado' => $siguiente->id_estado]);
            $pedido->historial()->create([
                'id_estado'  => $siguiente->id_estado,
                'id_usuario' => $usuario->id_usuario,
                'fecha_hora' => now(),
            ]);
        });

        return $pedido->refresh();
    }

    /**
     * Cancela el pedido, guarda el motivo y regresa las existencias (RN-09, RF-31).
     */
    public function cancelar(Pedido $pedido, string $motivo, Usuario $usuario): Pedido
    {
        if (! $pedido->sePuedeCancelar()) {
            throw ValidationException::withMessages([
                'estado' => 'Solo se pueden cancelar pedidos en estado Recibido o En preparación.',
            ]);
        }

        DB::transaction(function () use ($pedido, $motivo, $usuario) {
            $cancelado = EstadoPedido::where('orden', 0)->firstOrFail();

            $pedido->update([
                'id_estado'          => $cancelado->id_estado,
                'motivo_cancelacion' => $motivo,
            ]);

            $pedido->historial()->create([
                'id_estado'  => $cancelado->id_estado,
                'id_usuario' => $usuario->id_usuario,
                'fecha_hora' => now(),
            ]);

            // Reintegro: cada producto del pedido regresa al inventario
            foreach ($pedido->detalles as $detalle) {
                $pedido->movimientos()->create([
                    'id_producto' => $detalle->id_producto,
                    'id_usuario'  => $usuario->id_usuario,
                    'tipo'        => 'entrada',
                    'cantidad'    => $detalle->cantidad,
                    'fecha_hora'  => now(),
                    'motivo'      => "Reintegro por cancelación de {$pedido->folio}",
                ]);
                Producto::where('id_producto', $detalle->id_producto)->increment('existencia', $detalle->cantidad);
            }
        });

        return $pedido->refresh();
    }

    /**
     * Folio consecutivo con el formato PED-0001, PED-0002, ... (RN-01).
     */
    private function siguienteFolio(): string
    {
        $ultimo = Pedido::where('folio', 'like', 'PED-%')
            ->pluck('folio')
            ->map(fn (string $folio) => (int) substr($folio, 4))
            ->max() ?? 0;

        return sprintf('PED-%04d', $ultimo + 1);
    }

    /**
     * Si el mismo producto se agregó dos veces, se juntan sus cantidades en un solo renglón.
     */
    private function agruparProductos(array $productos): array
    {
        $renglones = [];
        foreach ($productos as $p) {
            if (empty($p['id_producto']) || (int) ($p['cantidad'] ?? 0) < 1) {
                continue;
            }
            $id = (int) $p['id_producto'];
            $renglones[$id]['cantidad'] = ($renglones[$id]['cantidad'] ?? 0) + (int) $p['cantidad'];
            $notas = trim((string) ($p['notas'] ?? ''));
            $renglones[$id]['notas'] = mb_substr(trim(($renglones[$id]['notas'] ?? '') . ' ' . $notas), 0, 100) ?: null;
        }

        return $renglones;
    }
}
