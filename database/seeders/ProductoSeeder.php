<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Productos de prueba. Cada producto se crea con su existencia inicial y se
 * registra el movimiento "Inventario inicial" (hace 10 días) a nombre del administrador.
 * Los pedidos y ajustes posteriores dejan:
 *   - Molletes y Malteada de vainilla con stock bajo (RN-20)
 *   - Queso fundido agotado
 *   - Flan napolitano inactivo (baja lógica, RN-19)
 */
class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $fecha = Carbon::today()->subDays(10)->setTime(8, 0);

        // [id_categoria, nombre, descripción, precio, existencia inicial, stock mínimo, activo]
        $productos = [
            [1, 'Chilaquiles verdes con pollo', 'Totopos en salsa verde con pollo, crema y queso', 129, 30, 5, true],
            [1, 'Huevos rancheros', 'Dos huevos estrellados sobre tortilla con salsa roja', 115, 25, 5, true],
            [1, 'Hot cakes con tocino', 'Tres hot cakes con mantequilla, miel y tocino', 105, 20, 5, true],
            [1, 'Molletes', 'Bolillo con frijoles, queso gratinado y pico de gallo', 89, 6, 4, true],
            [2, 'Guacamole con totopos', 'Guacamole preparado al momento', 95, 20, 5, true],
            [2, 'Queso fundido', 'Queso gratinado con chorizo y tortillas de harina', 110, 6, 3, true],
            [3, 'Sopa de tortilla', 'Caldo de jitomate con tortilla frita, aguacate y queso', 85, 30, 6, true],
            [3, 'Crema de elote', 'Crema de elote natural con granos', 79, 20, 5, true],
            [4, 'Enchiladas suizas', 'Tres enchiladas de pollo en salsa verde con queso gratinado', 149, 30, 5, true],
            [4, 'Club sándwich', 'Sándwich de tres pisos con papas a la francesa', 145, 25, 5, true],
            [4, 'Hamburguesa clásica', 'Carne de res, queso, lechuga y jitomate con papas', 159, 20, 5, true],
            [4, 'Arrachera con guarnición', 'Arrachera de 250 g con frijoles, guacamole y cebollitas', 245, 15, 4, true],
            [4, 'Pechuga a la plancha', 'Pechuga de pollo con verduras al vapor', 169, 18, 4, true],
            [5, 'Café americano', 'Taza de café de grano', 45, 100, 20, true],
            [5, 'Jugo de naranja', 'Jugo natural de 400 ml', 55, 40, 10, true],
            [5, 'Limonada natural', 'Limonada natural o mineral de 500 ml', 49, 50, 10, true],
            [5, 'Refresco', 'Refresco de lata 355 ml', 40, 30, 12, true],
            [5, 'Malteada de vainilla', 'Malteada de 400 ml', 75, 6, 5, true],
            [6, 'Pastel de chocolate', 'Rebanada de pastel de chocolate', 85, 15, 4, true],
            [6, 'Pay de queso', 'Rebanada de pay de queso con zarzamora', 79, 12, 3, true],
            [6, 'Flan napolitano', 'Producto descontinuado (baja lógica)', 65, 10, 3, false],
        ];

        foreach ($productos as [$categoria, $nombre, $descripcion, $precio, $existencia, $minimo, $activo]) {
            $producto = Producto::create([
                'id_categoria' => $categoria,
                'nombre'       => $nombre,
                'descripcion'  => $descripcion,
                'precio'       => $precio,
                'existencia'   => $existencia,
                'stock_minimo' => $minimo,
                'activo'       => $activo,
            ]);

            // Movimiento de inventario inicial (RN-22), registrado por el administrador (id 1)
            $producto->movimientos()->create([
                'id_usuario' => 1,
                'tipo'       => 'entrada',
                'cantidad'   => $existencia,
                'fecha_hora' => $fecha,
                'motivo'     => 'Inventario inicial',
            ]);
        }
    }
}
