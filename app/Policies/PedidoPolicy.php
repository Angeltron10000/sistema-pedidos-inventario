<?php

namespace App\Policies;

use App\Models\Pedido;
use App\Models\Rol;
use App\Models\Usuario;

/**
 * Permisos de Pedidos (RN-25).
 * - Administrador: todo.
 * - Cajero/Mesero: registra, modifica (solo en "Recibido"), cancela y entrega pedidos.
 * - Cocina: consulta pedidos y los pasa a "En preparación" y "Listo".
 *
 * Quién puede avanzar cada estado se decide en Pedido::puedeAvanzarPor().
 */
class PedidoPolicy
{
    /** Todos los roles consultan pedidos. */
    public function viewAny(Usuario $usuario): bool
    {
        return true;
    }

    public function view(Usuario $usuario, Pedido $pedido): bool
    {
        return true;
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->tieneRol(Rol::ADMINISTRADOR, Rol::CAJERO);
    }

    /** Modificar un pedido: solo mientras está en "Recibido" (RN-10, RN-11). */
    public function update(Usuario $usuario, Pedido $pedido): bool
    {
        return $usuario->tieneRol(Rol::ADMINISTRADOR, Rol::CAJERO) && $pedido->sePuedeEditar();
    }

    /** Botón "Pasar a …" (RN-08 y RN-25). */
    public function avanzar(Usuario $usuario, Pedido $pedido): bool
    {
        return $pedido->puedeAvanzarPor($usuario);
    }

    /** Botón "Cancelar" (RN-09). */
    public function cancelar(Usuario $usuario, Pedido $pedido): bool
    {
        return $pedido->puedeCancelarPor($usuario);
    }

    /** Los pedidos nunca se borran: se cancelan y conservan su historial. */
    public function delete(Usuario $usuario, Pedido $pedido): bool
    {
        return false;
    }

    public function deleteAny(Usuario $usuario): bool
    {
        return false;
    }
}
