<?php

namespace App\Policies;

use App\Models\Categoria;
use App\Models\Usuario;

/**
 * Permisos de Categorías (RN-25).
 * Solo el Administrador consulta y modifica el catálogo de categorías.
 *
 * Filament revisa esta clase solo: si un método devuelve false,
 * oculta el menú, el botón o la página correspondiente.
 */
class CategoriaPolicy
{
    /** Ver el listado (y la opción en el menú). */
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->esAdministrador();
    }

    public function view(Usuario $usuario, Categoria $categoria): bool
    {
        return $usuario->esAdministrador();
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->esAdministrador();
    }

    public function update(Usuario $usuario, Categoria $categoria): bool
    {
        return $usuario->esAdministrador();
    }

    /** Botón Desactivar / Activar (baja lógica, RN-21). */
    public function desactivar(Usuario $usuario, Categoria $categoria): bool
    {
        return $usuario->esAdministrador();
    }

    /** Nadie borra registros: se desactivan (RN-21). */
    public function delete(Usuario $usuario, Categoria $categoria): bool
    {
        return false;
    }

    public function deleteAny(Usuario $usuario): bool
    {
        return false;
    }
}
