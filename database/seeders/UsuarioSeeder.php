<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Usuarios de prueba. Todos tienen la contraseña "Prueba123" (cifrada con bcrypt).
 * El usuario rtorres está desactivado para probar RF-07 y RN-24.
 */
class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $contrasena = Hash::make('Prueba123');

        // [id_rol, nombre, apellidos, usuario, activo]
        $usuarios = [
            [1, 'Laura',   'Méndez Ortiz',   'admin',    true],
            [2, 'Juan',    'Pérez Gómez',    'jperez',   true],
            [2, 'María',   'López Sánchez',  'mlopez',   true],
            [3, 'Ricardo', 'Ramírez Cruz',   'rramirez', true],
            [3, 'Andrea',  'García Flores',  'agarcia',  true],
            [2, 'Roberto', 'Torres Vega',    'rtorres',  false],
        ];

        foreach ($usuarios as [$rol, $nombre, $apellidos, $usuario, $activo]) {
            Usuario::create([
                'id_rol'          => $rol,
                'nombre'          => $nombre,
                'apellidos'       => $apellidos,
                'usuario'         => $usuario,
                'contrasena_hash' => $contrasena,
                'activo'          => $activo,
            ]);
        }
    }
}
