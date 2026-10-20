<?php

namespace Database\Seeders;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Resurtidos de proveedor (entradas) y ajustes manuales de inventario (RF-16, RF-17, RN-23).
 * En los ajustes la cantidad lleva signo: negativa = merma, positiva = sobrante en conteo físico.
 * Todos los registra el administrador (id 1).
 */
class MovimientoInventarioSeeder extends Seeder
{
    public function run(): void
    {
        // [días atrás, hora, id_producto, tipo, cantidad, motivo]
        $movimientos = [
            [3, '07:30', 17, 'entrada', 24, 'Resurtido de proveedor'],     // Refresco
            [3, '07:35', 14, 'entrada', 50, 'Resurtido de proveedor'],     // Café americano
            [2, '22:00', 19, 'ajuste',  -2, 'Merma: rebanadas dañadas'],   // Pastel de chocolate
            [1, '22:00', 4,  'ajuste',  -2, 'Merma: pan caducado'],        // Molletes
            [1, '22:10', 16, 'ajuste',   1, 'Conteo físico: sobrante'],    // Limonada natural
        ];

        foreach ($movimientos as [$dias, $hora, $idProducto, $tipo, $cantidad, $motivo]) {
            [$h, $m] = explode(':', $hora);

            MovimientoInventario::create([
                'id_producto' => $idProducto,
                'id_usuario'  => 1,
                'tipo'        => $tipo,
                'cantidad'    => $cantidad,
                'fecha_hora'  => Carbon::today()->subDays($dias)->setTime((int) $h, (int) $m),
                'motivo'      => $motivo,
            ]);

            // Actualiza la existencia del producto (entrada suma; ajuste suma su cantidad con signo)
            Producto::where('id_producto', $idProducto)->increment('existencia', $cantidad);
        }
    }
}
