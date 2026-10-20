<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Carga todos los datos de prueba del sistema.
 * Se ejecuta con: php artisan db:seed   (o php artisan migrate:fresh --seed)
 *
 * El orden importa: primero los catálogos y después las tablas que dependen de ellos.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolSeeder::class,               // 3 roles
            EstadoPedidoSeeder::class,      // 5 estados del pedido
            ClienteSeeder::class,           // 5 clientes (incluye "Público en general")
            UsuarioSeeder::class,           // 6 usuarios (1 inactivo)
            CategoriaSeeder::class,         // 6 categorías
            ProductoSeeder::class,          // 21 productos + inventario inicial
            PedidoSeeder::class,            // 19 pedidos con detalle, historial y movimientos
            MovimientoInventarioSeeder::class, // resurtidos y ajustes de inventario
        ]);
    }
}
