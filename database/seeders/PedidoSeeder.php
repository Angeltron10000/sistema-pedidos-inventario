<?php

namespace Database\Seeders;

use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * 19 pedidos de prueba (PED-0001 a PED-0019) en todos los estados.
 *
 * Por cada pedido se crean, igual que lo haría el sistema:
 *   - el detalle con el precio "congelado" y su subtotal (RN-13, RN-14)
 *   - el total del pedido (RN-15)
 *   - una salida de inventario por producto y el descuento de existencias (RN-05)
 *   - el historial de cada cambio de estado con usuario y hora (RN-12)
 *   - en los cancelados, el reintegro de existencias (RN-09)
 *   - en el pedido modificado, el reintegro del producto que se quitó (RN-11)
 *
 * Las fechas son relativas al día en que se ejecuta el seeder: hay pedidos de
 * los últimos 6 días y pedidos de "hoy" (de las últimas horas).
 */
class PedidoSeeder extends Seeder
{
    // Identificadores de los estados (ver EstadoPedidoSeeder)
    private const RECIBIDO = 1;
    private const EN_PREPARACION = 2;
    private const LISTO = 3;
    private const ENTREGADO = 4;
    private const CANCELADO = 5;

    // Minutos después de recibido en que el pedido llega a cada estado
    private const MINUTOS_ESTADO = [1 => 0, 2 => 5, 3 => 20, 4 => 30];

    // Usuarios de Cocina que cambian a "En preparación" y "Listo"
    private const COCINA = [4, 5];

    public function run(): void
    {
        /*
         * Cada pedido:
         *   'cuando'    => ['dias', N, 'HH:MM']  (hace N días a esa hora)  o  ['min', N]  (hoy, hace N minutos)
         *   'cliente', 'usuario', 'servicio', 'estado'
         *   'productos' => [[id_producto, cantidad, notas], ...]
         *   'obs'       => observaciones
         *   'cancelado_desde' / 'cancelado_por' => solo en pedidos cancelados
         *   'quitados'  => productos que se quitaron al modificar el pedido [[id_producto, cantidad]]
         */
        $pedidos = [
            ['cuando' => ['dias', 6, '09:10'], 'cliente' => 1, 'usuario' => 2, 'servicio' => 'local', 'estado' => self::ENTREGADO,
             'productos' => [[1, 2, null], [14, 2, null]]],
            ['cuando' => ['dias', 6, '14:30'], 'cliente' => 2, 'usuario' => 6, 'servicio' => 'llevar', 'estado' => self::ENTREGADO,
             'productos' => [[9, 1, null], [16, 1, 'Mineral']]],
            ['cuando' => ['dias', 5, '13:45'], 'cliente' => 1, 'usuario' => 3, 'servicio' => 'local', 'estado' => self::ENTREGADO,
             'productos' => [[12, 2, 'Término medio'], [17, 2, null], [19, 2, null]], 'obs' => 'Mesa 7'],
            ['cuando' => ['dias', 5, '20:00'], 'cliente' => 3, 'usuario' => 2, 'servicio' => 'llevar', 'estado' => self::CANCELADO,
             'productos' => [[11, 1, null], [18, 1, null]], 'obs' => 'Cancelado: el cliente ya no quiso esperar',
             'cancelado_desde' => self::RECIBIDO, 'cancelado_por' => 2],
            ['cuando' => ['dias', 4, '09:30'], 'cliente' => 4, 'usuario' => 6, 'servicio' => 'local', 'estado' => self::ENTREGADO,
             'productos' => [[2, 1, null], [3, 1, null], [15, 2, null], [14, 2, null]], 'obs' => 'Mesa 3'],
            ['cuando' => ['dias', 3, '14:00'], 'cliente' => 1, 'usuario' => 3, 'servicio' => 'local', 'estado' => self::ENTREGADO,
             'productos' => [[7, 3, null], [9, 2, 'Sin crema'], [17, 3, null]], 'obs' => 'Mesa 12'],
            ['cuando' => ['dias', 3, '19:15'], 'cliente' => 2, 'usuario' => 2, 'servicio' => 'llevar', 'estado' => self::ENTREGADO,
             'productos' => [[10, 2, null], [20, 1, null]]],
            ['cuando' => ['dias', 2, '13:20'], 'cliente' => 5, 'usuario' => 3, 'servicio' => 'local', 'estado' => self::CANCELADO,
             'productos' => [[13, 2, null], [8, 2, null], [16, 2, null]], 'obs' => 'Cancelado: el cliente tuvo que retirarse',
             'cancelado_desde' => self::EN_PREPARACION, 'cancelado_por' => 1],
            ['cuando' => ['dias', 2, '15:10'], 'cliente' => 1, 'usuario' => 2, 'servicio' => 'local', 'estado' => self::ENTREGADO,
             'productos' => [[6, 4, null], [5, 1, null], [17, 2, null]], 'obs' => 'Mesa 5'],
            ['cuando' => ['dias', 1, '08:45'], 'cliente' => 3, 'usuario' => 3, 'servicio' => 'local', 'estado' => self::ENTREGADO,
             'productos' => [[1, 1, null], [2, 1, 'Huevos bien cocidos'], [14, 3, null]], 'obs' => 'Mesa 2'],
            ['cuando' => ['dias', 1, '14:20'], 'cliente' => 1, 'usuario' => 2, 'servicio' => 'llevar', 'estado' => self::ENTREGADO,
             'productos' => [[11, 2, 'Una sin cebolla'], [17, 2, null], [19, 1, null]]],
            ['cuando' => ['min', 150], 'cliente' => 1, 'usuario' => 2, 'servicio' => 'local', 'estado' => self::ENTREGADO,
             'productos' => [[1, 2, null], [15, 2, null]], 'obs' => 'Mesa 4'],
            ['cuando' => ['min', 130], 'cliente' => 4, 'usuario' => 3, 'servicio' => 'llevar', 'estado' => self::ENTREGADO,
             'productos' => [[9, 1, null], [16, 1, null]]],
            ['cuando' => ['min', 90], 'cliente' => 2, 'usuario' => 2, 'servicio' => 'local', 'estado' => self::LISTO,
             'productos' => [[6, 2, null], [12, 1, 'Bien cocida'], [17, 1, null]], 'obs' => 'Mesa 9'],
            ['cuando' => ['min', 60], 'cliente' => 1, 'usuario' => 3, 'servicio' => 'local', 'estado' => self::EN_PREPARACION,
             'productos' => [[10, 1, 'Sin mayonesa'], [14, 1, null]], 'obs' => 'Mesa 1'],
            ['cuando' => ['min', 45], 'cliente' => 5, 'usuario' => 2, 'servicio' => 'llevar', 'estado' => self::EN_PREPARACION,
             'productos' => [[13, 1, null], [18, 1, null]]],
            ['cuando' => ['min', 35], 'cliente' => 1, 'usuario' => 3, 'servicio' => 'local', 'estado' => self::CANCELADO,
             'productos' => [[7, 1, null], [17, 1, null]], 'obs' => 'Cancelado: pedido capturado por error',
             'cancelado_desde' => self::RECIBIDO, 'cancelado_por' => 3],
            ['cuando' => ['min', 20], 'cliente' => 3, 'usuario' => 2, 'servicio' => 'local', 'estado' => self::RECIBIDO,
             'productos' => [[3, 2, null], [14, 2, null]], 'obs' => 'Modificado: se quitaron los molletes a petición del cliente',
             'quitados' => [[4, 1]]],
            ['cuando' => ['min', 8], 'cliente' => 1, 'usuario' => 3, 'servicio' => 'llevar', 'estado' => self::RECIBIDO,
             'productos' => [[2, 1, null], [15, 1, null]]],
        ];

        foreach ($pedidos as $i => $datos) {
            $this->crearPedido($i + 1, $datos);
        }
    }

    /**
     * Registra un pedido completo con su detalle, inventario e historial.
     */
    private function crearPedido(int $numero, array $datos): void
    {
        $folio = sprintf('PED-%04d', $numero);        // RN-01: PED-0001, PED-0002, ...
        $recibido = $this->momento($datos['cuando']);

        $pedido = Pedido::create([
            'folio'         => $folio,
            'id_cliente'    => $datos['cliente'],
            'id_usuario'    => $datos['usuario'],
            'id_estado'     => $datos['estado'],
            'fecha_hora'    => $recibido,
            'tipo_servicio' => $datos['servicio'],
            'total'         => 0,
            'observaciones' => $datos['obs'] ?? null,
        ]);

        // Detalle del pedido y salida de inventario por cada producto
        $total = 0;
        foreach ($datos['productos'] as [$idProducto, $cantidad, $notas]) {
            $producto = Producto::findOrFail($idProducto);
            $subtotal = $cantidad * $producto->precio;    // RN-14
            $total += $subtotal;

            $pedido->detalles()->create([
                'id_producto'     => $idProducto,
                'cantidad'        => $cantidad,
                'precio_unitario' => $producto->precio,   // RN-13: precio congelado
                'subtotal'        => $subtotal,
                'notas'           => $notas,
            ]);

            $this->salida($pedido, $idProducto, $cantidad, $recibido);
        }
        $pedido->update(['total' => $total]);            // RN-15

        // Pedido modificado en "Recibido": salida del producto y su reintegro al quitarlo (RN-11)
        foreach ($datos['quitados'] ?? [] as [$idProducto, $cantidad]) {
            $this->salida($pedido, $idProducto, $cantidad, $recibido);
            $this->entrada($pedido, $idProducto, $cantidad, $recibido->copy()->addMinutes(3),
                "Reintegro por modificación de {$folio} (se quitó el producto)", $pedido->id_usuario);
        }

        // Historial de estados (RN-12): Recibido y Entregado los registra el cajero; los demás, Cocina
        $ultimo = $datos['estado'] === self::CANCELADO ? $datos['cancelado_desde'] : $datos['estado'];
        foreach (range(self::RECIBIDO, $ultimo) as $estado) {
            $pedido->historial()->create([
                'id_estado'  => $estado,
                'id_usuario' => in_array($estado, [self::RECIBIDO, self::ENTREGADO])
                    ? $pedido->id_usuario
                    : self::COCINA[$numero % 2],
                'fecha_hora' => $this->momento($datos['cuando'], self::MINUTOS_ESTADO[$estado]),
            ]);
        }

        // Cancelación: se registra el estado y se reintegran las existencias (RN-09)
        if ($datos['estado'] === self::CANCELADO) {
            $cancelacion = $this->momento($datos['cuando'], self::MINUTOS_ESTADO[$ultimo] + 10);
            $pedido->historial()->create([
                'id_estado'  => self::CANCELADO,
                'id_usuario' => $datos['cancelado_por'],
                'fecha_hora' => $cancelacion,
            ]);
            foreach ($datos['productos'] as [$idProducto, $cantidad]) {
                $this->entrada($pedido, $idProducto, $cantidad, $cancelacion,
                    "Reintegro por cancelación de {$folio}", $datos['cancelado_por']);
            }
        }
    }

    /**
     * Salida de inventario: registra el movimiento y descuenta la existencia (RN-05).
     */
    private function salida(Pedido $pedido, int $idProducto, int $cantidad, Carbon $fecha): void
    {
        $pedido->movimientos()->create([
            'id_producto' => $idProducto,
            'id_usuario'  => $pedido->id_usuario,
            'tipo'        => 'salida',
            'cantidad'    => $cantidad,
            'fecha_hora'  => $fecha,
            'motivo'      => "Salida por pedido {$pedido->folio}",
        ]);
        Producto::where('id_producto', $idProducto)->decrement('existencia', $cantidad);
    }

    /**
     * Entrada de inventario por reintegro: registra el movimiento y suma la existencia.
     */
    private function entrada(Pedido $pedido, int $idProducto, int $cantidad, Carbon $fecha, string $motivo, int $usuario): void
    {
        $pedido->movimientos()->create([
            'id_producto' => $idProducto,
            'id_usuario'  => $usuario,
            'tipo'        => 'entrada',
            'cantidad'    => $cantidad,
            'fecha_hora'  => $fecha,
            'motivo'      => $motivo,
        ]);
        Producto::where('id_producto', $idProducto)->increment('existencia', $cantidad);
    }

    /**
     * Calcula la fecha y hora de un evento.
     *   ['dias', N, 'HH:MM'] -> hace N días a esa hora (+ $mas minutos)
     *   ['min', N]           -> hoy, hace N minutos (+ $mas minutos)
     * Si se ejecuta antes de las 03:00, los pedidos de "hoy" se reparten entre la
     * medianoche y la hora actual para que sigan siendo de hoy.
     */
    private function momento(array $cuando, int $mas = 0): Carbon
    {
        if ($cuando[0] === 'dias') {
            [$h, $m] = explode(':', $cuando[2]);

            return Carbon::today()->subDays($cuando[1])->setTime((int) $h, (int) $m)->addMinutes($mas);
        }

        $minutosAtras = $cuando[1] - $mas;
        $ahora = Carbon::now();

        if ($ahora->hour >= 3) {
            return $ahora->copy()->subMinutes($minutosAtras)->startOfMinute();
        }

        $segundosHoy = $ahora->secondsSinceMidnight();

        return Carbon::today()->addSeconds((int) round($segundosHoy * (1 - $minutosAtras / 180)));
    }
}
