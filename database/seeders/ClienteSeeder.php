<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

/**
 * Clientes de prueba. El 1 es el cliente por defecto cuando no se captura uno (RN-26).
 */
class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            ['nombre' => 'Público en general / Mostrador', 'telefono' => null],
            ['nombre' => 'Ana Torres',       'telefono' => '5512345678'],
            ['nombre' => 'Carlos Hernández', 'telefono' => '5587654321'],
            ['nombre' => 'Sofía Martínez',   'telefono' => null],
            ['nombre' => 'Miguel Díaz',      'telefono' => '5544332211'],
        ];

        foreach ($clientes as $cliente) {
            Cliente::create($cliente);
        }
    }
}
