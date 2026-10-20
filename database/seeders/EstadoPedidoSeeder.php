<?php

namespace Database\Seeders;

use App\Models\EstadoPedido;
use Illuminate\Database\Seeder;

/**
 * Estados del pedido (RN-07, RN-08). "orden" indica la secuencia; 0 = Cancelado.
 */
class EstadoPedidoSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            ['nombre' => 'Recibido',       'orden' => 1],
            ['nombre' => 'En preparación', 'orden' => 2],
            ['nombre' => 'Listo',          'orden' => 3],
            ['nombre' => 'Entregado',      'orden' => 4],
            ['nombre' => 'Cancelado',      'orden' => 0],
        ];

        foreach ($estados as $estado) {
            EstadoPedido::create($estado);
        }
    }
}
