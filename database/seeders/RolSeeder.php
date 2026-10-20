<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

/**
 * Roles del sistema (RN-25).
 */
class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nombre' => 'Administrador', 'descripcion' => 'Acceso total al sistema'],
            ['nombre' => 'Cajero/Mesero', 'descripcion' => 'Registra y entrega pedidos; consulta productos'],
            ['nombre' => 'Cocina',        'descripcion' => 'Consulta pedidos y los cambia a En preparación y Listo'],
        ];

        foreach ($roles as $rol) {
            Rol::create($rol);
        }
    }
}
