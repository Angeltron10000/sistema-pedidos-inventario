<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\Rol;
use App\Models\Usuario;

/**
 * Permisos de Clientes (RN-25).
 * - Administrador y Cajero/Mesero: registran, consultan y editan clientes
 *   (el cajero los necesita al registrar pedidos, RF-23 y RF-24).
 * - Solo el Administrador desactiva clientes.
 * - Cocina: no entra a clientes.
 */
class ClientePolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->tieneRol(Rol::ADMINISTRADOR, Rol::CAJERO);
    }

    public function view(Usuario $usuario, Cliente $cliente): bool
    {
        return $usuario->tieneRol(Rol::ADMINISTRADOR, Rol::CAJERO);
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->tieneRol(Rol::ADMINISTRADOR, Rol::CAJERO);
    }

    public function update(Usuario $usuario, Cliente $cliente): bool
    {
        // El cliente 1 es "Público en general / Mostrador" (RN-26): no se modifica
        return $usuario->tieneRol(Rol::ADMINISTRADOR, Rol::CAJERO) && $cliente->id_cliente !== 1;
    }

    /** Botón Desactivar / Activar (baja lógica). */
    public function desactivar(Usuario $usuario, Cliente $cliente): bool
    {
        return $usuario->esAdministrador() && $cliente->id_cliente !== 1;
    }

    public function delete(Usuario $usuario, Cliente $cliente): bool
    {
        return false;
    }

    public function deleteAny(Usuario $usuario): bool
    {
        return false;
    }
}
