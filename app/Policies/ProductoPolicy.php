<?php

namespace App\Policies;

use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;

/**
 * Permisos de Productos (RN-25).
 * - Administrador: todo (alta, edición, desactivar, entradas y ajustes de inventario).
 * - Cajero/Mesero: solo consulta los productos y sus existencias.
 * - Cocina: no entra a productos.
 */
class ProductoPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->tieneRol(Rol::ADMINISTRADOR, Rol::CAJERO);
    }

    public function view(Usuario $usuario, Producto $producto): bool
    {
        return $usuario->tieneRol(Rol::ADMINISTRADOR, Rol::CAJERO);
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->esAdministrador();
    }

    public function update(Usuario $usuario, Producto $producto): bool
    {
        return $usuario->esAdministrador();
    }

    /** Botón Desactivar / Activar (baja lógica, RN-21). */
    public function desactivar(Usuario $usuario, Producto $producto): bool
    {
        return $usuario->esAdministrador();
    }

    /** Botón "Registrar entrada" (resurtido, RF-16). */
    public function registrarEntrada(Usuario $usuario, Producto $producto): bool
    {
        return $usuario->esAdministrador();
    }

    /** Botón "Ajustar existencia": solo el Administrador (RN-23). */
    public function ajustar(Usuario $usuario, Producto $producto): bool
    {
        return $usuario->esAdministrador();
    }

    public function delete(Usuario $usuario, Producto $producto): bool
    {
        return false;
    }

    public function deleteAny(Usuario $usuario): bool
    {
        return false;
    }
}
