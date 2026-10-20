<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

/**
 * Categorías de un restaurante familiar de menú general.
 */
class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Desayunos', 'Entradas', 'Sopas', 'Platos fuertes', 'Bebidas', 'Postres'] as $nombre) {
            Categoria::create(['nombre' => $nombre]);
        }
    }
}
